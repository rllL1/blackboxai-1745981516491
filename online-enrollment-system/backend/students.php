<?php
session_start();
require 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // List all students
        $stmt = $pdo->prepare("SELECT id, username, full_name, email, created_at FROM users WHERE role = 'student'");
        $stmt->execute();
        $students = $stmt->fetchAll();
        header('Content-Type: application/json');
        echo json_encode($students);
        break;

    case 'POST':
        // Add a new student
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['username']) || empty($data['full_name']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo "All fields are required.";
            exit;
        }
        // Check if username or email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['email']]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo "Username or email already exists.";
            exit;
        }
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, 'student')");
        $stmt->execute([$data['username'], $password_hash, $data['full_name'], $data['email']]);
        echo "Student added successfully.";
        break;

    case 'PUT':
        // Update student info
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id']) || empty($data['username']) || empty($data['full_name']) || empty($data['email'])) {
            http_response_code(400);
            echo "ID, username, full name, and email are required.";
            exit;
        }
        // Check if username or email exists for other users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$data['username'], $data['email'], $data['id']]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo "Username or email already exists.";
            exit;
        }
        $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE id = ? AND role = 'student'");
        $stmt->execute([$data['username'], $data['full_name'], $data['email'], $data['id']]);
        echo "Student updated successfully.";
        break;

    case 'DELETE':
        // Delete student
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id'])) {
            http_response_code(400);
            echo "Student ID is required.";
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
        $stmt->execute([$data['id']]);
        echo "Student deleted successfully.";
        break;

    default:
        http_response_code(405);
        echo "Method not allowed.";
        break;
}
?>
