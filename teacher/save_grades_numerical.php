<?php
require_once '../config/db.php'; // Adjust path as needed
require_once '../includes/auth.php'; // Adjust path as needed

if (!isLoggedIn()) {
    // Redirect to login if not authenticated, or handle as an error
    header("Location: ../public/login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id']; // Ensure teacher has permission for this class

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = (int)($_POST['class_id'] ?? 0);
    $grades_data = $_POST['grades'] ?? []; // grades[enrollment_id][component_id] = score

    if ($class_id === 0) {
        // Redirect back with an error message (or handle more gracefully)
        header("Location: input_grades_numerical.php?class_id=" . $class_id . "&error=invalid_class_id");
        exit("Error: Invalid Class ID provided.");
    }

    // Optional: Verify that the teacher has permission to modify grades for this class_id
    // This query is similar to the one in input_grades_numerical.php to fetch class info
    $perm_stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_id = ? AND teacher_id = ?");
    $perm_stmt->bind_param("ii", $class_id, $teacher_id);
    $perm_stmt->execute();
    $perm_result = $perm_stmt->get_result();
    if ($perm_result->num_rows === 0) {
        $perm_stmt->close();
        // Redirect or show an error: permission denied or class not found for this teacher
        header("Location: input_grades_numerical.php?class_id=" . $class_id . "&error=permission_denied");
        exit("Error: Permission denied or class not found for this teacher.");
    }
    $perm_stmt->close();


    if (empty($grades_data)) {
        // No grades submitted, maybe redirect with a different message or just back to the form
        header("Location: input_grades_numerical.php?class_id=" . $class_id . "&info=no_grades_submitted");
        exit();
    }

    $conn->begin_transaction(); // Start a transaction for atomic updates

    try {
        // Prepare statement for inserting/updating grades
        // Assumes student_grades table has columns: enrollment_id, component_id, score
        // And a primary or unique key on (enrollment_id, component_id) for ON DUPLICATE KEY UPDATE
        $stmt = $conn->prepare("INSERT INTO student_grades (enrollment_id, component_id, score) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE score = VALUES(score)");

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        foreach ($grades_data as $enrollment_id_str => $components) {
            $enrollment_id = (int)$enrollment_id_str;
            if ($enrollment_id > 0 && is_array($components)) {
                foreach ($components as $component_id_str => $score) {
                    $component_id = (int)$component_id_str;
                    
                    // Sanitize and validate score
                    // For numerical scores (non-attendance based)
                    // For attendance based (A/NA), they are strings.
                    // You might need to fetch component details (max_score, is_attendance_based)
                    // again here for robust validation, or trust the client-side form constraints.
                    // For simplicity, this example directly uses the score.
                    // Ensure empty strings are treated as NULL if your 'score' column allows it,
                    // or handle them appropriately (e.g., skip or set to a default).

                    $trimmed_score = trim((string)$score);

                    if ($trimmed_score === '') {
                        // Option 1: Delete the grade if input is empty
                        // $delete_stmt = $conn->prepare("DELETE FROM student_grades WHERE enrollment_id = ? AND component_id = ?");
                        // $delete_stmt->bind_param("ii", $enrollment_id, $component_id);
                        // $delete_stmt->execute();
                        // $delete_stmt->close();
                        // continue; // Skip to next component

                        // Option 2: Save as NULL (if column allows NULL)
                        // $stmt->bind_param("iis", $enrollment_id, $component_id, null);

                        // Option 3: Skip if you don't want to save empty scores (current behavior if not bound and executed)
                        continue;

                    } else {
                         // Basic validation for numerical scores if you can differentiate them here
                         // if (is_numeric($trimmed_score) && ($trimmed_score < 0 /* || $trimmed_score > MAX_SCORE_FOR_COMPONENT - requires fetching component*/)) {
                         //     // Handle invalid score, perhaps log it and skip, or add to an error list
                         //     continue;
                         // }
                        $stmt->bind_param("iis", $enrollment_id, $component_id, $trimmed_score);
                    }
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Execute failed for enrollment $enrollment_id, component $component_id: " . $stmt->error);
                    }
                }
            }
        }
        $stmt->close();
        $conn->commit(); // Commit transaction if all queries were successful

        // Redirect back to the input page with a success message
        header("Location: input_grades_numerical.php?class_id=" . $class_id . "&success=1");
        exit();

    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        // Log the error $e->getMessage()
        // Redirect back with a generic error message or specific one if safe
        error_log("Grade saving error: " . $e->getMessage()); // Log to server error log
        header("Location: input_grades_numerical.php?class_id=" . $class_id . "&error=save_failed&details=" . urlencode($e->getMessage()));
        exit();
    }

} else {
    // Not a POST request, redirect to dashboard or class selection
    header("Location: ../public/dashboard.php"); // Adjust as needed
    exit();
}
?>