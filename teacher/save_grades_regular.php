<?php
include '../config/db.php'; // Establishes $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['grades']) && is_array($_POST['grades'])) {
        $class_id_for_redirect = $_POST['class_id'] ?? null;

        foreach ($_POST['grades'] as $enrollment_id_str => $components_data) {
            // Basic validation for enrollment_id
            $enrollment_id = filter_var($enrollment_id_str, FILTER_VALIDATE_INT);
            if ($enrollment_id === false) {
                // Log or handle invalid enrollment_id format
                continue;
            }

            if (!is_array($components_data)) {
                // Log or handle malformed components_data for an enrollment_id
                continue;
            }

            foreach ($components_data as $component_id_str => $submitted_raw_value) {
                // Basic validation for component_id
                $component_id = filter_var($component_id_str, FILTER_VALIDATE_INT);
                if ($component_id === false) {
                    // Log or handle invalid component_id format
                    continue;
                }

                // Determine if the component is attendance-based
                $is_attendance_based = false;
                $stmt_check = $conn->prepare("SELECT is_attendance_based FROM grade_components WHERE component_id = ?");
                if (!$stmt_check) {
                    // Optional: Log $conn->error
                    // echo "Prepare failed (check): " . $conn->error;
                    continue; // Skip this component if prepare fails
                }
                $stmt_check->bind_param("i", $component_id);
                if (!$stmt_check->execute()) {
                    // Optional: Log $stmt_check->error
                    // echo "Execute failed (check): " . $stmt_check->error;
                    $stmt_check->close();
                    continue; // Skip this component
                }
                $result_check = $stmt_check->get_result();
                if ($row = $result_check->fetch_assoc()) {
                    $is_attendance_based = ($row['is_attendance_based'] == 1);
                }
                $stmt_check->close();

                $score_to_save = null;
                $attendance_to_save = null;
                $stmt_upsert = null;

                if ($is_attendance_based) {
                    // For attendance, accepted values are 'A', 'NA'. Empty string means no selection (store as NULL).
                    if ($submitted_raw_value === 'A' || $submitted_raw_value === 'NA') {
                        $attendance_to_save = $submitted_raw_value;
                    } else { // Includes empty string or any other unexpected value
                        $attendance_to_save = null;
                    }
                    $score_to_save = null; // Ensure score is NULL for attendance components

                    $stmt_upsert = $conn->prepare("INSERT INTO student_grades (enrollment_id, component_id, attendance_status, score) VALUES (?, ?, ?, NULL) ON DUPLICATE KEY UPDATE attendance_status = VALUES(attendance_status), score = NULL");
                    if ($stmt_upsert) {
                        $stmt_upsert->bind_param("iis", $enrollment_id, $component_id, $attendance_to_save);
                    } else {
                        // Optional: Log $conn->error
                        // echo "Prepare failed (upsert attendance): " . $conn->error;
                        continue; // Skip
                    }
                } else {
                    // For numeric scores.
                    if ($submitted_raw_value !== '' && is_numeric($submitted_raw_value)) {
                        $score_to_save = (float)$submitted_raw_value;
                    } else { // Empty input field for score, or non-numeric value
                        $score_to_save = null; // Store as NULL if empty or invalid
                    }
                    $attendance_to_save = null; // Ensure attendance_status is NULL for score-based components

                    $stmt_upsert = $conn->prepare("INSERT INTO student_grades (enrollment_id, component_id, score, attendance_status) VALUES (?, ?, ?, NULL) ON DUPLICATE KEY UPDATE score = VALUES(score), attendance_status = NULL");
                    if ($stmt_upsert) {
                        $stmt_upsert->bind_param("iid", $enrollment_id, $component_id, $score_to_save);
                    } else {
                        // Optional: Log $conn->error
                        // echo "Prepare failed (upsert score): " . $conn->error;
                        continue; // Skip
                    }
                }

                if ($stmt_upsert && !$stmt_upsert->execute()) {
                    // Optional: Log $stmt_upsert->error
                    // echo "Execute failed (upsert): " . $stmt_upsert->error;
                }
                if ($stmt_upsert) {
                    $stmt_upsert->close();
                }
            }
        }

        if ($class_id_for_redirect) {
            header("Location: input_grades_numerical.php?class_id=" . urlencode($class_id_for_redirect) . "&success=1");
            exit();
        } else {
            // Fallback if class_id was not part of the POST data for some reason
            echo "Grades saved successfully, but redirection failed due to missing class ID.";
            exit();
        }

    } else {
        echo "No grades data received or data is malformed.";
    }
} else {
    echo "Invalid request method.";
}

// Close the database connection if it's open and not handled by PHP's shutdown
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>