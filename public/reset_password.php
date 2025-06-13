<?php
require_once '../config/db.php';
// The db.php file creates a $conn variable using MySQLi

$error = '';
$success = '';

// Check if token is provided in the URL
if (!isset($_GET['token'])) {
    die("Invalid token. Please request a new password reset link.");
}

$token = $_GET['token'];
$now = date("Y-m-d H:i:s");

// Verify token is valid and not expired
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > ?");
$stmt->bind_param("ss", $token, $now);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Token expired or invalid. Please request a new password reset link.");
}

$record = $result->fetch_assoc();
$email = $record['email'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_pass = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($new_pass !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Update the password
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE teachers SET password_hash = ? WHERE email = ?");
        $update->bind_param("ss", $hashed, $email);
        $update->execute();

        // Delete the used token
        $delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $delete->bind_param("s", $email);
        $delete->execute();

        $success = "Password has been reset successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Universidad De Manila</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
         body {
    background: url('assets/img/udmganda.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

        .reset-container {
            background-color: #fcfbf7; /* Even lighter beige for card */
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
            border: 1px solid #d6d0b8; /* Matching beige border */
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }

        .reset-header {
            background-color: #004d00; /* Slightly darker green for header */
            color: #FFFFFF;
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid #008000; /* Lighter green separator */
        }

        .reset-header .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .reset-header .logo-image {
            max-height: 60px;
            margin-right: 0.75rem;
        }

        .reset-header .logo-text {
            text-align: left;
        }

        .reset-header .uni-name {
            font-weight: 600;
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.2;
        }

        .reset-header .tagline {
            font-weight: 300;
            font-size: 0.8rem;
            margin: 0;
        }

        .reset-body {
            padding: 2rem;
        }

        .form-description {
            color: #555555;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-label {
            font-weight: 500;
            color: #006400; /* Dark green text */
        }

        .form-control {
            border: 1px solid #d6d0b8; /* Matching beige border */
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border-radius: 0.3rem;
            background-color: #ffffff;
        }

        .form-control:focus {
            border-color: #006400;
            box-shadow: 0 0 0 0.25rem rgba(0, 100, 0, 0.25);
        }

        .input-group-text {
            background-color: #e9e5d0; /* Light beige for input group */
            border: 1px solid #d6d0b8;
            color: #006400;
        }

        .btn-primary {
            background-color: #006400; /* Dark green buttons */
            border-color: #006400;
            padding: 0.75rem 1rem;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #004d00; /* Darker green on hover */
            border-color: #004d00;
        }

        .link-group {
            margin-top: 1.5rem;
            text-align: center;
        }

        .link-group a {
            color: #006400; /* Dark green links */
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .link-group a:hover {
            color: #004d00; /* Darker green on hover */
            text-decoration: underline;
        }

        .reset-footer {
            background-color: #e9e5d0; /* Light beige footer */
            padding: 1rem;
            text-align: center;
            font-size: 0.8rem;
            color: #006400; /* Dark green footer text */
            border-top: 1px solid #d6d0b8; /* Matching beige border */
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .reset-body {
                padding: 1.5rem;
            }

            .reset-header {
                padding: 1.25rem;
            }

            .reset-header .logo-image {
                max-height: 50px;
            }

            .reset-header .uni-name {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="reset-container">
    <div class="reset-header">
        <div class="logo-container">
            <img src="assets/img/udm_logo.png" alt="UDM Logo" class="logo-image">
            <div class="logo-text">
                <h5 class="uni-name">UNIVERSIDAD DE MANILA</h5>
                <p class="tagline">Former City College of Manila</p>
            </div>
        </div>
        <h2 class="mt-2 mb-0">Teacher Login</h2>
    </div>

    <div class="reset-body">
        <h3 class="mb-3 text-center" style="color: #006400;">Reset Password</h3>
        <p class="form-description">Please enter your new password below.</p>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $success ?>
                <div class="mt-3">
                    <a href="login.php" class="btn btn-success btn-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Click here to login
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" action="?token=<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-lock-fill me-2"></i>Reset Password
                    </button>
                </div>
            </form>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (empty($success)): ?>
            <div class="link-group">
                <a href="login.php">
                    <i class="bi bi-arrow-left me-1"></i>Back to Login
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="reset-footer">
        &copy; <?= date('Y') ?> Universidad De Manila - IntelliGrade System. All rights reserved.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>