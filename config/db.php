<?php
/**
 * Database connection configuration
 */

// Database credentials
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'udm_class_record_db';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
    
}