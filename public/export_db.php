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
    echo "<script>alert('You must be logged in to perform this action.'); window.history.back();</script>";
    exit();
}

$teacher_id = $_SESSION['teacher_id']; // Get teacher ID from session for logging

// --- Original Code Starts Here ---
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'udm_class_record_db';

// Attempt to detect Google Drive "My Drive" folder
$driveLetters = range('C', 'Z');
$googleDriveFolder = null;

// Normalize the folder name to support "My Drive" or "Google Drive/My Drive"
foreach ($driveLetters as $letter) {
    $basePath = $letter . ':/';
    $pathsToCheck = [
        $basePath . 'My Drive',
        $basePath . 'Google Drive/My Drive',
        $basePath . 'Google Drive' // fallback
    ];
    
    foreach ($pathsToCheck as $path) {
        if (is_dir($path)) {
            $googleDriveFolder = $path;
            break 2; // Exit both loops
        }
    }
}

if (!$googleDriveFolder) {
    $logMessage = 'Google Drive folder not found. Please ensure it is synced locally.';
    logBackupAction($conn, $teacher_id, 'export', 'N/A', 'failed', $logMessage);
    echo "<script>alert('$logMessage'); window.history.back();</script>";
    exit;
}

// Ensure the backup directory exists
$backupDir = $googleDriveFolder . '/classrecorddb';
if (!is_dir($backupDir)) {
    if (!mkdir($backupDir, 0777, true)) {
        $logMessage = 'Failed to create backup directory in Google Drive.';
        logBackupAction($conn, $teacher_id, 'export', 'N/A', 'failed', $logMessage);
        echo "<script>alert('$logMessage'); window.history.back();</script>";
        exit;
    }
}

// Create the backup file name
$fileName = 'backup_' . $dbname . '_' . date('Y-m-d_H-i-s') . '.sql';
$backupFile = $backupDir . '/' . $fileName;


// Run mysqldump
// IMPORTANT: Removed --ignore-table for backup_history from the dump to include it
$mysqldumpPath = "C:\\xampp\\mysql\\bin\\mysqldump";
$command = escapeshellarg($mysqldumpPath) .
           " --user=" . escapeshellarg($user) .
           " --password=" . escapeshellarg($pass) .
           " --host=" . escapeshellarg($host) .
           " --databases " . escapeshellarg($dbname) .
           // The following line has been removed to include backup_history:
           // " --ignore-table=" . escapeshellarg($dbname . '.backup_history') . // Exclude the history table
           " --routines --events --triggers --single-transaction > " . escapeshellarg($backupFile);

system($command, $result);

// Show result and log the action
if ($result === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
    $logMessage = "Database successfully exported to Google Drive: " . $backupFile;
    logBackupAction($conn, $teacher_id, 'export', $fileName, 'success', $logMessage);
    echo "<script>alert('Database successfully exported to Google Drive: $backupFile'); window.location.href = document.referrer;</script>";
} else {
    $errorMessage = "Export failed. ";
    if ($result !== 0) {
        $errorMessage .= "mysqldump exited with code " . $result . ". ";
    }
    if (!file_exists($backupFile)) {
        $errorMessage .= "Backup file was not created. ";
    } elseif (file_exists($backupFile) && filesize($backupFile) === 0) {
        $errorMessage .= "Backup file is empty. ";
    }
    $errorMessage .= "Check mysqldump path or folder permissions.";

    logBackupAction($conn, $teacher_id, 'export', $fileName, 'failed', $errorMessage);
    echo "<script>alert('$errorMessage'); window.history.back();</script>";
}

?>