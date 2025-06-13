<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and authentication files
// These are necessary for logging to the database and checking user login status.
require_once '../config/db.php'; // Assumes this file establishes a $conn variable for MySQLi connection
require_once '../includes/auth.php'; // Assumes this file provides isLoggedIn() function

// --- Logging Function Start ---
/**
 * Logs backup and restore actions to the 'backup_history' table.
 *
 * @param mysqli $conn The database connection object.
 * @param int $teacher_id The ID of the teacher performing the action.
 * @param string $action_type The type of action ('export' or 'import').
 * @param string $file_name The name of the file involved in the action.
 * @param string|null $status The status of the action ('success' or 'failed').
 * @param string|null $message An optional message providing more details.
 */
function logBackupAction($conn, $teacher_id, $action_type, $file_name, $status, $message = null) {
    // Check if the backup_history table exists before attempting to log
    $tableExistsQuery = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'backup_history'";
    $tableExistsResult = $conn->query($tableExistsQuery);

    if ($tableExistsResult && $tableExistsResult->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO backup_history (teacher_id, action_timestamp, action_type, file_name, status, message) VALUES (?, NOW(), ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("issss", $teacher_id, $action_type, $file_name, $status, $message);
            $stmt->execute();
            $stmt->close();
        } else {
            error_log("Failed to prepare backup history log statement: " . $conn->error);
        }
    } else {
        error_log("backup_history table does not exist. Skipping logging for action: " . $action_type);
    }
}
// --- Logging Function End ---

// Check if the user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit();
}

$teacher_id = $_SESSION['teacher_id']; // Get teacher ID from session for logging

// --- Original Code Starts Here ---
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'udm_class_record_db';

$status = 'error'; // Default status for JSON response
$message = 'An unknown error occurred.'; // Default message for JSON response
$uploadedFileName = 'N/A'; // Default for logging if file name isn't available

if (isset($_FILES['sql_file']) && $_FILES['sql_file']['error'] === UPLOAD_ERR_OK) {
    $uploadedFileName = basename($_FILES['sql_file']['name']);
    $filename_tmp = $_FILES['sql_file']['tmp_name'];
    $destinationDir = 'uploads/';
    $destination = $destinationDir . $uploadedFileName;

    // Create uploads directory if it doesn't exist
    if (!is_dir($destinationDir)) {
        if (!mkdir($destinationDir, 0777, true)) {
            $message = 'Failed to create uploads directory.';
            logBackupAction($conn, $teacher_id, 'import', $uploadedFileName, 'failed', $message);
            echo json_encode(['status' => 'error', 'message' => $message]);
            exit();
        }
    }

    if (move_uploaded_file($filename_tmp, $destination)) {
        // Use escapeshellarg for security with parameters
        $mysqlPath = "C:\\xampp\\mysql\\bin\\mysql";
        $command = escapeshellarg($mysqlPath) .
                   " --user=" . escapeshellarg($user) .
                   " --password=" . escapeshellarg($pass) .
                   " --host=" . escapeshellarg($host) .
                   " " . escapeshellarg($dbname) .
                   " < " . escapeshellarg($destination);
        
        $output = [];
        $result_code = 0;
        exec($command, $output, $result_code);

        if ($result_code === 0) {
            $status = 'success';
            $message = 'Database imported successfully.';
        } else {
            $message = 'Import failed. mysql exited with code ' . $result_code . '. Command output: ' . implode("\n", $output);
        }

        // Clean up the uploaded file after import attempt
        if (file_exists($destination)) {
            unlink($destination);
        }

    } else {
        $message = 'Failed to move uploaded file.';
    }
} else {
    // Handle specific upload errors for better logging
    switch ($_FILES['sql_file']['error'] ?? -1) { // Use -1 if $_FILES['sql_file'] is not set
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $message = 'Uploaded file exceeds max file size.';
            break;
        case UPLOAD_ERR_PARTIAL:
            $message = 'File upload was only partially completed.';
            break;
        case UPLOAD_ERR_NO_FILE:
            $message = 'No file was uploaded.';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $message = 'Missing a temporary folder.';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $message = 'Failed to write file to disk.';
            break;
        case UPLOAD_ERR_EXTENSION:
            $message = 'A PHP extension stopped the file upload.';
            break;
        default:
            $message = 'No file selected or an unknown file upload error occurred.';
            break;
    }
}

// Log the final status of the import attempt
logBackupAction($conn, $teacher_id, 'import', $uploadedFileName, $status, $message);

// Output the JSON response
echo json_encode(['status' => $status, 'message' => $message]);

?>