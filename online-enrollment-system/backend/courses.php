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
        // List all courses
        $stmt = $pdo->query("SELECT * FROM courses");
        $courses = $stmt->fetchAll();
        header('Content-Type: application/json');
        echo json_encode($courses);
        break;

    case 'POST':
        // Add a new course
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['course_code']) || empty($data['course_name'])) {
            http_response_code(400);
            echo "Course code and name are required.";
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_name, description) VALUES (?, ?, ?)");
        $stmt->execute([$data['course_code'], $data['course_name'], $data['description'] ?? '']);
        echo "Course added successfully.";
        break;

    case 'PUT':
        // Update a course
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id']) || empty($data['course_code']) || empty($data['course_name'])) {
            http_response_code(400);
            echo "Course ID, code and name are required.";
            exit;
        }
        $stmt = $pdo->prepare("UPDATE courses SET course_code = ?, course_name = ?, description = ? WHERE id = ?");
        $stmt->execute([$data['course_code'], $data['course_name'], $data['description'] ?? '', $data['id']]);
        echo "Course updated successfully.";
        break;

    case 'DELETE':
        // Delete a course
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id'])) {
            http_response_code(400);
            echo "Course ID is required.";
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo "Course deleted successfully.";
        break;

    default:
        http_response_code(405);
        echo "Method not allowed.";
        break;
}
?>
