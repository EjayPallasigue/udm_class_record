<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$full_name = $_SESSION['full_name'] ?? 'Teacher';

if (!isset($conn) || $conn === null || (is_object($conn) && property_exists($conn, 'connect_error') && $conn->connect_error)) {
    // Added more detailed error for connection failure
    $db_error = $conn->connect_error ?? 'Unknown error';
    error_log("Database connection failed in your_classes.php: " . $db_error);
    die("Database connection not established. Please check your '../config/db.php' file or database connection failed: " . htmlspecialchars($db_error));
}

function tableExists($conn, $tableName) {
    $query = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $tableName);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $exists = $row['count'] > 0;
        $stmt->close();
        return $exists;
    }
    // Fallback if INFORMATION_SCHEMA query preparation fails
    error_log("Warning: INFORMATION_SCHEMA query failed for tableExists('$tableName'). Error: " . $conn->error . ". Using fallback SHOW TABLES.");
    $escapedTableName = $conn->real_escape_string($tableName);
    $query = "SHOW TABLES LIKE '$escapedTableName'";
    $result = $conn->query($query);
    return $result && $result->num_rows > 0;
}

/**
 * Safely deletes records from a table.
 * Logs detailed errors if deletion fails.
 */
function safeDeleteFromTable($conn, $tableName, $whereClause, $params, $paramTypes) {
    if (!tableExists($conn, $tableName)) {
        error_log("Warning: Table '$tableName' does not exist. Skipping deletion.");
        return true; // Assuming it's okay if an optional table is missing
    }
    $query = "DELETE FROM `$tableName` WHERE $whereClause"; // Added backticks around table name
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare statement for table '$tableName': " . $conn->error . ". Query: $query");
        return false;
    }
    if (!empty($params)) {
        if (!$stmt->bind_param($paramTypes, ...$params)) {
            error_log("Failed to bind parameters for table '$tableName': " . $stmt->error . ". Query: $query. Params: " . json_encode($params) . ". Types: " . $paramTypes);
            $stmt->close();
            return false;
        }
    }
    $success = $stmt->execute();
    if (!$success) {
        // Enhanced error logging for execution failure
        $error_detail = "Error: " . $stmt->error . ". SQL: " . $query . ". Params: " . json_encode($params) . ". Types: " . $paramTypes;
        error_log("Failed to execute delete for table '$tableName'. Details: " . $error_detail);
    }
    $stmt->close();
    return $success;
}

if (isset($_POST['delete_class']) && isset($_POST['class_id'])) {
    $class_id = (int)$_POST['class_id'];
    $conn->autocommit(false); // Start transaction
    try {
        $allSuccessful = true;
        $enrollment_ids = [];

        // Fetch enrollment_ids associated with the class
        // Added check for table existence before querying
        if (tableExists($conn, 'enrollments')) {
            if ($stmt_enrollments = $conn->prepare("SELECT enrollment_id FROM enrollments WHERE class_id = ?")) {
                $stmt_enrollments->bind_param("i", $class_id);
                $stmt_enrollments->execute();
                $result_enrollments = $stmt_enrollments->get_result();
                while ($row = $result_enrollments->fetch_assoc()) {
                    $enrollment_ids[] = $row['enrollment_id'];
                }
                $stmt_enrollments->close();
            } else {
                $allSuccessful = false;
                error_log("Failed to prepare statement for fetching enrollment_ids for class_id $class_id: " . $conn->error);
            }
        } else {
            error_log("Warning: 'enrollments' table does not exist. Skipping deletion of enrollment-related data for class_id $class_id.");
            // Depending on your schema, this might be an error or acceptable.
            // If enrollments are crucial, you might set $allSuccessful = false here.
        }


        // Delete records from tables related by enrollment_id
        if ($allSuccessful && !empty($enrollment_ids)) {
            $in_clause = implode(',', array_fill(0, count($enrollment_ids), '?'));
            $param_types_enrollment = str_repeat('i', count($enrollment_ids));
            
            $enrollment_related_tables = ['grades', 'student_grades', 'final_grades']; 

            foreach ($enrollment_related_tables as $table) {
                if (!safeDeleteFromTable($conn, $table, "enrollment_id IN ($in_clause)", $enrollment_ids, $param_types_enrollment)) {
                    $allSuccessful = false;
                    error_log("Failed to delete records from '$table' for class_id $class_id.");
                    break; 
                }
            }
        }
        
        // Delete records from tables directly related by class_id
        if ($allSuccessful) {
            $direct_delete_tables = [
                ['grade_components', 'class_id = ?', [$class_id], 'i'],
                ['enrollments', 'class_id = ?', [$class_id], 'i'] 
            ];
            foreach ($direct_delete_tables as $item) {
                if (!safeDeleteFromTable($conn, $item[0], $item[1], $item[2], $item[3])) {
                    $allSuccessful = false;
                    error_log("Failed to delete records from '$item[0]' for class_id $class_id.");
                    break;
                }
            }
        }

        // Finally, delete the class itself
        if ($allSuccessful) {
            if (tableExists($conn, 'classes')) {
                $delete_class_sql = "DELETE FROM classes WHERE class_id = ? AND teacher_id = ?";
                if ($stmt = $conn->prepare($delete_class_sql)) {
                    $stmt->bind_param("ii", $class_id, $teacher_id);
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            // Successfully deleted
                        } else {
                            $allSuccessful = false; 
                            $_SESSION['error_message'] = "Class not found under your account, or it was already deleted. No changes made.";
                            error_log("Attempt to delete class_id $class_id by teacher_id $teacher_id resulted in 0 affected rows.");
                        }
                    } else {
                        $allSuccessful = false;
                        error_log("Failed to execute delete for 'classes' table. Class ID: $class_id, Teacher ID: $teacher_id. Error: " . $stmt->error);
                        $_SESSION['error_message'] = "Database error: Failed to delete the main class entry. Please check server logs.";
                    }
                    $stmt->close();
                } else {
                    $allSuccessful = false;
                    error_log("Failed to prepare delete statement for 'classes' table. Class ID: $class_id. Error: " . $conn->error);
                    $_SESSION['error_message'] = "Database error: Failed to prepare to delete the main class entry. Please check server logs.";
                }
            } else {
                $allSuccessful = false;
                error_log("Critical error: 'classes' table does not exist. Cannot complete deletion for class_id $class_id.");
                $_SESSION['error_message'] = "Critical error: The main 'classes' table is missing. Deletion failed.";
            }
        }

        if ($allSuccessful) {
            $conn->commit();
            $_SESSION['success_message'] = "Class deleted successfully!";
        } else {
            $conn->rollback();
            if (!isset($_SESSION['error_message'])) {
                 $_SESSION['error_message'] = "Failed to delete class due to issues with related records or permissions. Please check server logs for details.";
            }
            error_log("Class deletion rolled back for class_id $class_id due to errors.");
        }

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Exception during class deletion for class_id $class_id: " . $e->getMessage());
        $_SESSION['error_message'] = "An unexpected error occurred while deleting the class: " . htmlspecialchars($e->getMessage());
    }
    $conn->autocommit(true); 
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit();
}

// Function to determine the URL for inputting grades based on grading system type
function getGradesInputUrl($class_id, $grading_system_type) {
    // Ensure the path is correct based on your directory structure
    return "../teacher/" . ($grading_system_type === 'numerical' ? "input_grades_numerical.php" : "input_grades_final_only.php") . "?class_id=" . $class_id;
}

// Fetch classes for the logged-in teacher
$classes = [];
if (tableExists($conn, 'classes') && tableExists($conn, 'subjects') && tableExists($conn, 'sections')) {
    $sql = "SELECT c.class_id, s.subject_code, s.subject_name, sec.section_name, sec.academic_year, sec.semester, c.grading_system_type 
            FROM classes c 
            JOIN subjects s ON c.subject_id = s.subject_id 
            JOIN sections sec ON c.section_id = sec.section_id 
            WHERE c.teacher_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $classes = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Failed to get result for fetching classes: " . $stmt->error);
            $_SESSION['error_message'] = "Database error: Unable to retrieve class data.";
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement for getting classes: " . $conn->error);
        $_SESSION['error_message'] = "Database error: Unable to prepare for retrieving classes.";
    }
} else {
    $_SESSION['error_message'] = "Database tables essential for displaying classes are missing. Please ensure your database is properly set up (classes, subjects, or sections might be missing).";
    error_log("One or more critical tables (classes, subjects, sections) are missing.");
}

// Fetch student grades if a class is selected for viewing
$students_with_grades = [];
$grading_system_type = 'N/A'; 
$selected_class_id = null; // Initialize selected_class_id

if (isset($_GET['view_class_id']) && !empty($_GET['view_class_id'])) {
    $selected_class_id = (int)$_GET['view_class_id']; // Keep track of the selected class

    if (tableExists($conn, 'classes') && tableExists($conn, 'students') && tableExists($conn, 'enrollments')) {
        // First, get the grading system type for the selected class
        if ($stmt_grading = $conn->prepare("SELECT grading_system_type FROM classes WHERE class_id = ? AND teacher_id = ?")) {
            $stmt_grading->bind_param("ii", $selected_class_id, $teacher_id);
            $stmt_grading->execute();
            $class_info_result = $stmt_grading->get_result();
            $class_info = $class_info_result->fetch_assoc();
            $stmt_grading->close();

            if ($class_info) {
                $grading_system_type = $class_info['grading_system_type'];
                
                // SQL to fetch students and their grades for the selected class
                // Ensure all involved tables (student_grades, final_grades) exist or handle their absence gracefully
                $grades_sql = "SELECT s.student_id, s.student_number, s.last_name, s.first_name, s.middle_name, 
                                      MAX(sg.attendance_status_prelim) AS prelim_grade, 
                                      MAX(sg.attendance_status_midterm) AS midterm_grade, 
                                      fg.overall_final_grade 
                               FROM enrollments e 
                               JOIN students s ON e.student_id = s.student_id ";
                
                // Conditionally join student_grades if it exists
                if (tableExists($conn, 'student_grades')) {
                    $grades_sql .= "LEFT JOIN student_grades sg ON e.enrollment_id = sg.enrollment_id ";
                } else {
                    // If student_grades table is missing, we can't fetch prelim/midterm grades from it.
                    // We might select NULLs or handle this as an error/warning.
                    // For now, we'll proceed, and columns will be NULL if table is missing from JOIN.
                    error_log("Warning: 'student_grades' table not found. Prelim/Midterm grades might be unavailable.");
                }

                // Conditionally join final_grades if it exists
                if (tableExists($conn, 'final_grades')) {
                    $grades_sql .= "LEFT JOIN final_grades fg ON e.enrollment_id = fg.enrollment_id ";
                } else {
                    error_log("Warning: 'final_grades' table not found. Overall final grades might be unavailable.");
                }
                               
                $grades_sql .= "WHERE e.class_id = ? 
                                GROUP BY s.student_id, s.student_number, s.last_name, s.first_name, s.middle_name, fg.overall_final_grade 
                                ORDER BY s.last_name, s.first_name";

                if ($stmt_grades = $conn->prepare($grades_sql)) {
                    $stmt_grades->bind_param("i", $selected_class_id);
                    $stmt_grades->execute();
                    $students_result = $stmt_grades->get_result();
                    if($students_result){
                        $students_with_grades = $students_result->fetch_all(MYSQLI_ASSOC);
                    } else {
                        error_log("Failed to get result for fetching student grades for class_id $selected_class_id: " . $stmt_grades->error);
                        $_SESSION['error_message'] = "Database error: Unable to retrieve student grade data.";
                    }
                    $stmt_grades->close();
                } else {
                    error_log("Failed to prepare statement for fetching student grades for class_id $selected_class_id: " . $conn->error . " SQL: " . $grades_sql);
                    $_SESSION['error_message'] = "Database error: Unable to prepare for retrieving student grades. Check SQL query and table/column names in server logs.";
                }
            } else {
                $_SESSION['error_message'] = "Class not found or you don't have permission to view it.";
                error_log("Attempt to view grades for non-existent or unauthorized class_id $selected_class_id by teacher_id $teacher_id.");
            }
        } else {
            error_log("Failed to prepare statement for getting grading type for class_id $selected_class_id: " . $conn->error);
            $_SESSION['error_message'] = "Database error: Unable to retrieve class grading type.";
        }
    } else {
        $_SESSION['error_message'] = "Database tables essential for viewing student grades are missing. Please check setup.";
        error_log("One or more critical tables (classes, students, enrollments) are missing when trying to view student grades.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Classes - Universidad De Manila</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f3e1; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background-color: #006400; color: #E7E7E7; padding: 0; position: fixed; top: 0; left: 0; height: 100vh; z-index: 1030; overflow-y: auto; transition: width 0.3s ease; display: flex; flex-direction: column; }
        .sidebar-header { padding: 1rem; border-bottom: 1px solid #008000; display: flex; align-items: center; justify-content: flex-start; min-height: 70px; background-color: #004d00; }
        .logo-image { max-height: 40px; }
        .logo-text { overflow: hidden; }
        .logo-text h5.uni-name { margin: 0; font-size: 0.9rem; font-weight: 600; color: #FFFFFF; line-height: 1.1; white-space: nowrap; }
        .logo-text p.tagline { margin: 0; font-size: 0.7rem; font-weight: 300; color: #E7E7E7; line-height: 1; white-space: nowrap; }
        .sidebar .nav-menu { padding: 1rem; flex-grow: 1; display: flex; flex-direction: column; }
        .sidebar .nav-link { color: #E7E7E7; padding: 0.85rem 1.25rem; font-size: 0.95rem; border-radius: 0.3rem; margin-bottom: 0.25rem; transition: background-color 0.2s ease, color 0.2s ease; display: flex; align-items: center; white-space: nowrap; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #FFFFFF; background-color: #008000; }
        .sidebar .nav-link .bi { margin-right: 0.85rem; font-size: 1.1rem; vertical-align: middle; width: 20px; text-align: center; }
        .sidebar .nav-link span { flex-grow: 1; overflow: hidden; text-overflow: ellipsis; }
        .sidebar .logout-item { margin-top: auto; }
        .sidebar .logout-item hr { border-color: #008000; margin-top: 1rem; margin-bottom:1rem; }
        .content-area { margin-left: 280px; flex-grow: 1; padding: 2.5rem; width: calc(100% - 280px); transition: margin-left 0.3s ease, width 0.3s ease; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid #d6d0b8; }
        .page-header h2 { margin: 0; font-weight: 500; font-size: 1.75rem; color: #006400; }
        .card { border: 1px solid #d6d0b8; box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05); border-radius: 0.5rem; background-color: #fcfbf7; }
        .card-header { background-color: #e9e5d0; border-bottom: 1px solid #d6d0b8; padding: 1rem 1.25rem; font-weight: 500; color: #006400; }
        .table th { background-color: #e9e5d0; font-weight: 500; color: #006400; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .table td { vertical-align: middle; font-size: 0.95rem; background-color: #fcfbf7; }
        .table .btn-action-group .btn { margin-right: 0.3rem; }
        .table .btn-action-group .btn:last-child { margin-right: 0; }
        .btn-primary { background-color: #006400; border-color: #006400; }
        .btn-primary:hover { background-color: #004d00; border-color: #004d00; }
        .btn-outline-primary { color: #006400; border-color: #006400; }
        .btn-outline-primary:hover { background-color: #006400; border-color: #006400; color: white; }
        .btn-outline-secondary, .btn-outline-success, .btn-outline-info { color: #006400; border-color: #006400; }
        .btn-outline-secondary:hover, .btn-outline-success:hover, .btn-outline-info:hover { background-color: #006400; border-color: #006400; color: white; }
        .btn-outline-warning { color: #856404; border-color: #856404; }
        .btn-outline-warning:hover { background-color: #856404; border-color: #856404; color: white; }
        .btn-outline-danger { color: #dc3545; border-color: #dc3545; }
        .btn-outline-danger:hover { background-color: #dc3545; border-color: #dc3545; color: white; }
        .alert-info { background-color: #e7f3e7; border-color: #d0ffd0; color: #006400; }
        .footer { padding: 1.5rem 0; margin-top: 2rem; font-size: 0.875rem; color: #006400; border-top: 1px solid #d6d0b8; }
        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar .logo-text { display: none; }
            .sidebar .sidebar-header { justify-content: center; padding: 1.25rem 0.5rem; }
            .sidebar .logo-image { margin-right: 0; }
            .sidebar .nav-link span { display: none; }
            .sidebar .nav-link .bi { margin-right: 0; display: block; text-align: center; font-size: 1.5rem; }
            .sidebar:hover { width: 280px; }
            .sidebar:hover .logo-text { display: block; }
            .sidebar:hover .sidebar-header { justify-content: flex-start; padding: 1rem; }
            .sidebar:hover .logo-image { margin-right: 0.5rem; }
            .sidebar:hover .nav-link span { display: inline; }
            .sidebar:hover .nav-link .bi { margin-right: 0.85rem; display: inline-block; text-align: center; font-size: 1.1rem; }
            .content-area { margin-left: 80px; width: calc(100% - 80px); }
            .sidebar:hover + .content-area { margin-left: 280px; width: calc(100% - 280px); }
        }
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: static; z-index: auto; flex-direction: column; }
            .sidebar .logo-text { display: block; }
            .sidebar .sidebar-header { justify-content: flex-start; padding: 1rem; }
            .sidebar .logo-image { margin-right: 0.5rem; }
            .sidebar .nav-link span { display: inline; }
            .sidebar .nav-link .bi { margin-right: 0.85rem; font-size: 1.1rem; display: inline-block; text-align: center; }
            .sidebar .nav-menu { flex-grow: 0; }
            .sidebar .logout-item { margin-top: 1rem; }
            .content-area { margin-left: 0; width: 100%; padding: 1.5rem; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .page-header h2 { font-size: 1.5rem; }
            .page-header .btn { margin-top: 1rem; }
            .table-responsive { overflow-x: auto; }
            .btn-action-group { white-space: nowrap; }
             }

        /* Chatbot specific styles */
        .chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050; /* Ensure it's above other elements like modals */
        }

        .btn-chatbot {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .popover {
            max-width: 350px; /* This limits the popover width */
        }

        .popover-header {
            background-color: #006400; /* Dark green header */
            color: white;
            font-weight: bold;
        }

        .popover-body {
            /* Existing padding */
            padding: 15px;
            /* Added styles to constrain popover body's height */
            max-height: 400px; /* Adjust this value as needed */
            overflow-y: auto; /* Adds scrollbar to popover body if content exceeds max-height */
        }

        .chatbot-messages {
            height: 200px; /* Fixed height for the message area */
            overflow-y: auto; /* Enable vertical scrolling */
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
        }

        /* Message containers */
        .message-container {
            display: flex;
            margin-bottom: 8px;
            max-width: 90%; /* Limit message width */
        }

        .user-message {
            align-self: flex-end; /* Align user messages to the right */
            background-color: #e0f7fa; /* Light blue for user messages */
            border-radius: 15px 15px 0 15px;
            padding: 8px 12px;
            margin-left: auto; /* Push to the right */
        }

        .isla-message {
            align-self: flex-start; /* Align Isla messages to the left */
            background-color: #e7f3e7; /* Light green for Isla messages */
            border-radius: 15px 15px 15px 0;
            padding: 8px 12px;
            margin-right: auto; /* Push to the left */
        }

        .message-container strong {
            font-weight: bold;
            margin-bottom: 2px;
            display: block; /* Make sender name a block to separate from message */
        }
        .user-message strong {
             color: #0056b3; /* Darker blue for user name */
        }
        .isla-message strong {
             color: #006400; /* Darker green for Isla name */
        }

        .message-container p {
            margin: 0;
            line-height: 1.4;
            /* Added styles for robust text wrapping */
            word-wrap: break-word; /* Ensures long words break and wrap */
            white-space: pre-wrap; /* Preserves whitespace and wraps text */
        }

        /* Typing indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background-color: #f0f0f0;
            border-radius: 15px 15px 15px 0;
            max-width: fit-content;
            align-self: flex-start;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background-color: #888;
            border-radius: 50%;
            margin: 0 2px;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
        .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
        </style>
</head>
<body>

<div class="main-wrapper">
    <nav class="sidebar">
        <div class="sidebar-header">
            <img src="../public/assets\img\udm_logo.png" alt="UDM Logo" class="logo-image me-2">
            <div class="logo-text">
                <h5 class="uni-name mb-0">UNIVERSIDAD DE MANILA</h5>
                <p class="tagline mb-0">Former City College of Manila</p>
            </div>
        </div>
        <ul class="nav flex-column nav-menu">
            <li class="nav-item"><a class="nav-link" href="../public/dashboard.php"><i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span></a></li>
            <li class="nav-item"><a class="nav-link" href="../teacher/create_class.php"><i class="bi bi-plus-square-dotted"></i> <span>Create New Class</span></a></li>
            <li class="nav-item"><a class="nav-link active" aria-current="page" href="your_classes.php"><i class="bi bi-person-workspace"></i> <span>Your Classes</span></a></li>
            <li class="nav-item"><a class="nav-link" href="../public/manage_backup.php"><i class="bi bi-cloud-arrow-down-fill"></i> <span>Manage Backup</span></a></li>
            <li class="nav-item"><a class="nav-link" href="../public/gradingsystem.php"><i class="bi bi-calculator"></i> <span>Grading System</span></a></li>
            <li class="nav-item logout-item"><hr><a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </nav>

    <main class="content-area">
        <header class="page-header"><h2>Your Classes</h2></header>

        <?php if (isset($_SESSION['success_message'])) { echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'; unset($_SESSION['success_message']); } ?>
        <?php if (isset($_SESSION['error_message'])) { echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'; unset($_SESSION['error_message']); } ?>

        <?php if (empty($classes)): ?>
            <div class="card text-center shadow-sm">
                <div class="card-body p-5">
                    <i class="bi bi-info-circle-fill text-success" style="font-size: 3rem; margin-bottom: 1rem; color: #006400;"></i>
                    <h5 class="card-title" style="color: #006400;">No Classes Yet</h5>
                    <p class="card-text text-muted">You have not created or been assigned to any classes. <br>Get started by creating your first class.</p>
                    <a href="../teacher/create_class.php" class="btn btn-lg btn-primary mt-3"><i class="bi bi-plus-circle-fill"></i> Create Your First Class</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex align-items-center"><i class="bi bi-list-task me-2"></i> Class Overview</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-book me-1"></i> Subject</th>
                                    <th><i class="bi bi-people me-1"></i> Section</th>
                                    <th><i class="bi bi-bar-chart-steps me-1"></i> Grading Type</th>
                                    <th><i class="bi bi-gear me-1"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($class['subject_code']) ?> - <?= htmlspecialchars($class['subject_name']) ?></td>
                                        <td><?= htmlspecialchars($class['section_name']) ?> (<?= htmlspecialchars($class['academic_year']) ?> - <?= htmlspecialchars($class['semester']) ?>)</td>
                                        <td><?= htmlspecialchars($class['grading_system_type'] === 'numerical' ? 'Numerical' : 'A/NA-Based') ?></td>
                                        <td class="btn-action-group" style="white-space: nowrap;">
                                            <a href="../teacher/enroll_students.php?class_id=<?= $class['class_id'] ?>" class="btn btn-sm btn-outline-primary" title="Enroll Students"><i class="bi bi-person-plus-fill"></i> <span class="d-none d-lg-inline">Enroll</span></a>
                                            <a href="../teacher/manage_components.php?class_id=<?= $class['class_id'] ?>" class="btn btn-sm btn-outline-secondary" title="Manage Components"><i class="bi bi-sliders"></i> <span class="d-none d-lg-inline">Components</span></a>
                                            <a href="<?= getGradesInputUrl($class['class_id'], $class['grading_system_type']) ?>" class="btn btn-sm btn-outline-success" title="Input Grades"><i class="bi bi-pencil-square"></i> <span class="d-none d-lg-inline">Grades</span></a>
                                            <a href="<?= ($class['grading_system_type'] === 'numerical') ? '../teacher/class_record_numerical_computed.php?class_id=' . $class['class_id'] : '../teacher/view_class_record.php?class_id=' . $class['class_id'] ?>" class="btn btn-sm btn-outline-info" title="View Class Record"><i class="bi bi-eye-fill"></i> <span class="d-none d-lg-inline">View</span></a>
                                            <a href="../teacher/edit_class.php?class_id=<?= $class['class_id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit Class"><i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Edit</span></a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Class" onclick="confirmDelete(<?= $class['class_id'] ?>, '<?= htmlspecialchars($class['subject_code'] . ' - ' . $class['subject_name'] . ' (' . $class['section_name'] . ')', ENT_QUOTES) ?>')"><i class="bi bi-trash"></i> <span class="d-none d-lg-inline">Delete</span></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header d-flex align-items-center"><i class="bi bi-person-lines-fill me-2"></i> View Students by Subject</div>
                <div class="card-body">
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="GET" class="mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label for="subjectSelect" class="form-label">Select Subject:</label>
                                <select class="form-select" id="subjectSelect" name="view_class_id" onchange="this.form.submit()">
                                    <option value="">-- Choose a Class --</option>
                                    <?php foreach ($classes as $class_item): // Renamed to avoid conflict with $class in outer loop ?>
                                        <option value="<?= $class_item['class_id'] ?>" <?= (isset($selected_class_id) && $selected_class_id == $class_item['class_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($class_item['subject_code'] . ' - ' . $class_item['subject_name'] . ' (' . $class_item['section_name'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <?php if (isset($selected_class_id) && !empty($students_with_grades)): ?>
                                    <h6 class="mt-3 mt-md-0">Grading System: <span class="badge bg-primary"><?= htmlspecialchars( ($grading_system_type === 'numerical' ) ? 'Numerical' : 'A/NA-Based' ) ?></span></h6>
                                <?php elseif (isset($selected_class_id) && $grading_system_type !== 'N/A'): ?>
                                     <h6 class="mt-3 mt-md-0">Grading System: <span class="badge bg-primary"><?= htmlspecialchars( ($grading_system_type === 'numerical' ) ? 'Numerical' : 'A/NA-Based' ) ?></span></h6>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <?php if (isset($selected_class_id) && !empty($selected_class_id)): // Check if a class was actually selected for viewing ?>
                        <?php if (empty($students_with_grades)): ?>
                            <div class="alert alert-info" role="alert">No students enrolled or grades entered for this class yet, or dependent grade tables are missing. Check server logs if grades are expected.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Student No.</th>
                                            <th>Student Name</th>
                                            <th>Prelim</th>
                                            <th>Midterms</th>
                                            <th>Final Computed Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students_with_grades as $student): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($student['student_number'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php
                                                    $fullName = htmlspecialchars($student['first_name'] ?? '');
                                                    if (!empty($student['middle_name'])) {
                                                        $fullName .= ' ' . htmlspecialchars($student['middle_name'][0]) . '.';
                                                    }
                                                    $fullName .= ' ' . htmlspecialchars($student['last_name'] ?? '');
                                                    echo trim($fullName) ?: 'N/A';
                                                    ?>
                                                </td>
                                                <td><?= isset($student['prelim_grade']) ? htmlspecialchars($student['prelim_grade']) : 'N/A' ?></td>
                                                <td><?= isset($student['midterm_grade']) ? htmlspecialchars($student['midterm_grade']) : 'N/A' ?></td>
                                                <td><?= isset($student['overall_final_grade']) ? htmlspecialchars($student['overall_final_grade']) : 'N/A' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info" role="alert">Please select a subject from the dropdown to view student grades.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <footer class="footer text-center">&copy; <?= date('Y') ?> Universidad De Manila - Teacher Portal. All rights reserved.</footer>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning-subtle">
        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Would you like to save the database before logging out?</p>
      </div>
      <div class="modal-footer">
        <form action="export_db.php" method="POST" class="d-inline"><button type="submit" class="btn btn-success">Save Database</button></form>
        <a href="../public/logout.php" class="btn btn-danger" id="logoutButton">Logout</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger-subtle">
                <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-danger"><strong>⚠️ Warning:</strong> This action is critical and will permanently delete:</p>
                <ul>
                    <li>The class itself.</li>
                    <li>All student enrollments in this class.</li>
                    <li>All associated grades (prelim, midterm, final).</li>
                    <li>All defined grade components for this class.</li>
                    <li>All other class-related data.</li>
                </ul>
                <p>This action cannot be undone.</p>
                <p>Are you sure you want to delete the class: <strong id="deleteClassName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteClassForm" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="d-inline">
                    <input type="hidden" name="class_id" id="deleteClassId">
                    <button type="submit" name="delete_class" class="btn btn-danger">Yes, Delete This Class</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(classId, className) {
    document.getElementById('deleteClassId').value = classId;
    document.getElementById('deleteClassName').textContent = className;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}
</script>

<div class="chatbot-container">
    <button type="button" class="btn btn-primary btn-chatbot" id="chatbotToggle" data-bs-toggle="popover" data-bs-placement="top" title="UDM Isla">
        <i class="bi bi-chat-dots-fill"></i>
    </button>

    <div id="chatbotPopoverContent" style="display: none;">
        <div class="chatbot-messages"></div>
        <div class="input-group mb-2">
            <input type="text" id="chatbotInput" class="form-control" placeholder="Type your question...">
            <button class="btn btn-primary" type="button" id="chatbotSend">Send</button>
        </div>
        <button class="btn btn-success w-100" type="button" id="chatbotSaveDbButton" style="display: none;"><i class="bi bi-download"></i> Save Database Now</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotPopoverContentTemplate = document.getElementById('chatbotPopoverContent');

    let chatbotMessages, chatbotInput, chatbotSend, chatbotSaveDbButton, typingIndicatorElement;
    const CHAT_STORAGE_KEY = 'udm_isla_conversation';

    const popover = new bootstrap.Popover(chatbotToggle, {
        html: true,
        content: () => {
            const contentClone = chatbotPopoverContentTemplate.cloneNode(true);
            contentClone.style.display = 'block';
            return contentClone.innerHTML; // Use innerHTML as it's a direct copy of the template
        },
        sanitize: false // Important if your template contains specific structures you want preserved
    });

    chatbotToggle.addEventListener('shown.bs.popover', function () {
        // Popover instance is popover, its element is activePopoverEl
        const activePopoverEl = document.getElementById(chatbotToggle.getAttribute('aria-describedby'));
        if (!activePopoverEl) return;


        // Adjust popover position slightly to the left if needed, this can be finicky
        // Example: activePopoverEl.style.left = `${parseFloat(window.getComputedStyle(activePopoverEl).left || 0) - 70}px`;

        chatbotMessages = activePopoverEl.querySelector('.chatbot-messages');
        chatbotInput = activePopoverEl.querySelector('#chatbotInput');
        chatbotSend = activePopoverEl.querySelector('#chatbotSend');
        chatbotSaveDbButton = activePopoverEl.querySelector('#chatbotSaveDbButton');

        loadConversation();

        if (chatbotSend) chatbotSend.addEventListener('click', sendMessage);
        if (chatbotInput) chatbotInput.addEventListener('keypress', handleKeyPress);
        if (chatbotSaveDbButton) chatbotSaveDbButton.addEventListener('click', saveDatabaseFromChatbot);
        
        chatbotInput && chatbotInput.focus();
        chatbotMessages && (chatbotMessages.scrollTop = chatbotMessages.scrollHeight);
    });

    function handleKeyPress(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent default form submission if input is in a form
            sendMessage();
        }
    }

    function showTypingIndicator() {
        if (!chatbotMessages) return;
        // Remove existing typing indicator if any
        const existingIndicator = chatbotMessages.querySelector('.typing-indicator');
        if (existingIndicator) {
            existingIndicator.remove();
        }

        typingIndicatorElement = document.createElement('div');
        typingIndicatorElement.classList.add('message-container', 'typing-indicator'); // Ensure it's styled like other messages but identifiable
        typingIndicatorElement.innerHTML = `<span></span><span></span><span></span>`;
        chatbotMessages.appendChild(typingIndicatorElement);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function hideTypingIndicator() {
        const indicator = chatbotMessages ? chatbotMessages.querySelector('.typing-indicator') : null;
        if (indicator) {
            indicator.remove();
        }
        typingIndicatorElement = null; // Clear reference
    }

    function sendMessage() {
        if (!chatbotInput || !chatbotMessages) {
            console.error('Chatbot input or messages container not found. Popover not ready?');
            return;
        }

        const userMessage = chatbotInput.value.trim();
        if (userMessage === '') return;

        appendMessage('You', userMessage);
        const currentMessageForProcessing = userMessage; // Capture message before input is cleared
        chatbotInput.value = '';
        chatbotInput.disabled = true; 
        if(chatbotSend) chatbotSend.disabled = true;
        if (chatbotSaveDbButton) chatbotSaveDbButton.style.display = 'none';

        showTypingIndicator();

        // Handle specific commands locally first
        if (currentMessageForProcessing.toLowerCase() === 'clear chat') {
            hideTypingIndicator();
            clearChat(); // Clears display and localStorage
            appendMessage('Isla', "Chat history cleared!", false); // Add a new message from Isla
            chatbotInput.disabled = false;
            if(chatbotSend) chatbotSend.disabled = false;
            chatbotInput.focus();
            // saveConversation() is called by appendMessage, so no need to call it explicitly here for "Chat history cleared!"
            return;
        }

        if (currentMessageForProcessing.toLowerCase().includes('save database')) {
            hideTypingIndicator();
            if (chatbotSaveDbButton) {
                chatbotSaveDbButton.style.display = 'block';
                appendMessage('Isla', "Click the 'Save Database Now' button below to save your database.", false);
            } else {
                appendMessage('Isla', "I can't offer a direct save button right now. Please look for the button on the dashboard or main menu.", false);
            }
            chatbotInput.disabled = false;
            if(chatbotSend) chatbotSend.disabled = false;
            chatbotInput.focus();
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            // saveConversation() called by appendMessage
            return;
        }

        const deleteNoteMatch = currentMessageForProcessing.toLowerCase().match(/^delete note (\d+)$/);
        if (deleteNoteMatch) {
            hideTypingIndicator(); // Hide before calling, as it will append its own messages
            deleteNoteFromChatbot(parseInt(deleteNoteMatch[1]));
            // Re-enable input after processing
            chatbotInput.disabled = false;
            if(chatbotSend) chatbotSend.disabled = false;
            chatbotInput.focus();
            return;
        }
        
        // If not a local command, send to server
        fetch('../public/chatbot_response.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'query=' + encodeURIComponent(currentMessageForProcessing) // Use captured message
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            hideTypingIndicator();
            appendMessage('Isla', data.response, true); // 'true' for typing effect
        })
        .catch(error => {
            console.error('Error fetching chatbot response:', error);
            hideTypingIndicator();
            appendMessage('Isla', "Sorry, I'm having trouble connecting right now. Please try again later.", false);
        })
        .finally(() => {
            chatbotInput.disabled = false;
            if(chatbotSend) chatbotSend.disabled = false;
            chatbotInput.focus();
            // saveConversation() is called by appendMessage
        });
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight; // Scroll after adding user message
    }

    function saveDatabaseFromChatbot() {
        if (!chatbotMessages || !chatbotInput || !chatbotSaveDbButton || !chatbotSend) {
            console.error('Chatbot elements not found for saveDatabaseFromChatbot.');
            return;
        }

        appendMessage('Isla', "Saving your database...", false); // Isla's acknowledgment
        chatbotInput.disabled = true;
        chatbotSend.disabled = true;
        chatbotSaveDbButton.disabled = true;
        chatbotSaveDbButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';


        fetch('../public/export_db.php', { method: 'POST' })
        .then(response => {
            if (response.ok) {
                // Try to guess filename if Content-Disposition is present
                const disposition = response.headers.get('Content-Disposition');
                let filename = 'database_backup.sql'; // Default
                if (disposition && disposition.indexOf('attachment') !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }
                appendMessage('Isla', `Database backup initiated. If download doesn't start, check your browser settings. File: ${filename}`, false);
                return response.blob(); // Get the blob for download
            } else {
                return response.text().then(text => { throw new Error(`Database save failed: ${text || response.statusText}`); });
            }
        })
        .then(blob => { // This part handles the file download
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            // Extract filename from response header if possible, otherwise use a default
            const disposition = lastResponseHeaders ? lastResponseHeaders.get('Content-Disposition') : null; // Assuming lastResponseHeaders is stored
            let filename = 'database_backup.sql';
             if (disposition && disposition.indexOf('attachment') !== -1) {
                const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                const matches = filenameRegex.exec(disposition);
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            }
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        })
        .catch(error => {
            console.error('Error saving database:', error);
            appendMessage('Isla', `Failed to save database: ${error.message}. Please try again.`, false);
        })
        .finally(() => {
            chatbotInput.disabled = false;
            chatbotSend.disabled = false;
            if (chatbotSaveDbButton) {
                chatbotSaveDbButton.disabled = false;
                chatbotSaveDbButton.style.display = 'none'; // Hide it again
                chatbotSaveDbButton.innerHTML = '<i class="bi bi-download"></i> Save Database Now';
            }
            chatbotInput.focus();
            // saveConversation() called by appendMessage
        });
    }
    // Placeholder for lastResponseHeaders, to be populated by fetch if needed for filename
    let lastResponseHeaders = null; 
    // Modify fetch in saveDatabaseFromChatbot to store headers:
    // .then(response => { lastResponseHeaders = response.headers; if (response.ok) {...} ...})


    function deleteNoteFromChatbot(noteNumber) {
        if (!chatbotMessages || !chatbotInput || !chatbotSend) {
            console.error('Chatbot elements not found for deleteNoteFromChatbot.');
            return;
        }
        appendMessage('Isla', `Attempting to delete note number ${noteNumber}...`, false);
        chatbotInput.disabled = true; chatbotSend.disabled = true;

        fetch('../public/dashboard.php', { // Assuming dashboard.php handles note deletion
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `delete_note=1&note_number=${noteNumber}` // Ensure dashboard.php expects these params
        })
        .then(response => {
            if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
            return response.json(); // Expecting JSON response like {status: 'success', message: '...'}
        })
        .then(data => {
            appendMessage('Isla', data.message || (data.status === 'success' ? "Note deleted." : "Could not delete note."), false);
        })
        .catch(error => {
            console.error('Error deleting note:', error);
            appendMessage('Isla', "Sorry, I couldn't delete the note due to an error. Please try again later.", false);
        })
        .finally(() => {
            chatbotInput.disabled = false; chatbotSend.disabled = false;
            chatbotInput.focus();
            // saveConversation() called by appendMessage
        });
    }


    function appendMessage(sender, message, withTypingEffect = false) {
        if (!chatbotMessages) {
            console.error('Chatbot messages container not found in appendMessage.');
            return;
        }

        const messageContainer = document.createElement('div');
        messageContainer.classList.add('message-container', sender === 'You' ? 'user-message' : 'isla-message');
        
        const senderElement = document.createElement('strong');
        senderElement.textContent = `${sender}:`;
        
        const messageTextElement = document.createElement('p');
        // Sanitize message before displaying to prevent XSS if message content can be manipulated
        // For simplicity, assuming messages are plain text or trusted HTML from server
        // If typing effect, message is set char by char. If not, set it directly.

        messageContainer.appendChild(senderElement); // Add sender first
        messageContainer.appendChild(messageTextElement); // Then paragraph for message
        chatbotMessages.appendChild(messageContainer);

        if (withTypingEffect && sender === 'Isla') { // Typing effect only for Isla
            let i = 0;
            const typingSpeed = 30; // milliseconds
            messageTextElement.innerHTML = ""; // Start with empty message for typing
            function typeWriter() {
                if (i < message.length) {
                    messageTextElement.innerHTML += message.charAt(i);
                    i++;
                    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                    setTimeout(typeWriter, typingSpeed);
                } else {
                    saveConversation(); // Save after full message is typed
                }
            }
            setTimeout(typeWriter, 100); // Initial delay before typing starts
        } else {
            messageTextElement.textContent = message; // Direct set for user messages or non-typed Isla messages
            saveConversation(); // Save immediately
        }
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight; // Scroll after adding message
    }


    const saveConversation = () => {
        if (chatbotMessages) {
            localStorage.setItem(CHAT_STORAGE_KEY, chatbotMessages.innerHTML);
        }
    };
    
    const loadConversation = () => {
        if (!chatbotMessages) return;
        const savedConversation = localStorage.getItem(CHAT_STORAGE_KEY);
        if (savedConversation) {
            chatbotMessages.innerHTML = savedConversation;
        } else {
            // Initial greeting if no conversation is saved
            appendMessage('Isla', "Hi there! How can I help you today?", false);
        }
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    };

    const clearChat = () => {
        if (!chatbotMessages) return;
        chatbotMessages.innerHTML = ''; // Clear display
        localStorage.removeItem(CHAT_STORAGE_KEY); // Clear storage
        // Optionally, add back the initial greeting
        // appendMessage('Isla', "Hi there! How can I help you today?", false); 
        // This will be handled by sendMessage if it calls clearChat and then appends a message.
    };

    // Corrected ID for the logout button used in this file
    const logoutButtonInModal = document.querySelector('#logoutModal .btn-danger#logoutButton'); // More specific selector
    if (logoutButtonInModal) {
        logoutButtonInModal.addEventListener('click', () => {
            localStorage.removeItem(CHAT_STORAGE_KEY); // Clear chat on logout
        });
    }
});
</script>
</body>
</html>