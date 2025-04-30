<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

switch ($method) {
    case 'GET':
        // Get enrollments for the logged-in user
        if ($role === 'admin') {
            // Admin can see all enrollments with optional filters
            $filters = [];
            $params = [];

            if (!empty($_GET['academic_year'])) {
                $filters[] = "e.academic_year = ?";
                $params[] = $_GET['academic_year'];
            }
            if (!empty($_GET['semester'])) {
                $filters[] = "e.semester = ?";
                $params[] = $_GET['semester'];
            }
            if (!empty($_GET['status'])) {
                $filters[] = "e.status = ?";
                $params[] = $_GET['status'];
            }

            $where = '';
            if ($filters) {
                $where = 'WHERE ' . implode(' AND ', $filters);
            }

            $sql = "SELECT e.id, u.full_name, c.course_name, e.enrollment_type, e.academic_year, e.semester, e.enrollment_date, e.status 
                    FROM enrollments e
                    JOIN users u ON e.user_id = u.id
                    JOIN courses c ON e.course_id = c.id
                    $where";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $enrollments = $stmt->fetchAll();
        } else {
            // Student sees only their enrollments
            $stmt = $pdo->prepare("SELECT e.id, c.course_name, e.enrollment_type, e.academic_year, e.semester, e.enrollment_date, e.status 
                                   FROM enrollments e
                                   JOIN courses c ON e.course_id = c.id
                                   WHERE e.user_id = ?");
            $stmt->execute([$user_id]);
            $enrollments = $stmt->fetchAll();
        }
        header('Content-Type: application/json');
        echo json_encode($enrollments);
        break;

    case 'POST':
        // Student enrolls in a course
        if ($role !== 'student') {
            http_response_code(403);
            echo "Only students can enroll.";
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['course_id']) || empty($data['enrollment_type']) || empty($data['academic_year']) || empty($data['semester'])) {
            http_response_code(400);
            echo "Course ID, enrollment type, academic year, and semester are required.";
            exit;
        }
        // Check if already enrolled
        $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$user_id, $data['course_id']]);
        if ($stmt->fetch()) {
            echo "Already enrolled in this course.";
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, enrollment_type, academic_year, semester) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $data['course_id'], $data['enrollment_type'], $data['academic_year'], $data['semester']]);
        echo "Enrollment request submitted.";
        break;

    case 'PUT':
        // Admin approves or rejects enrollment
        if ($role !== 'admin') {
            http_response_code(403);
            echo "Only admin can update enrollment status.";
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id']) || empty($data['status']) || !in_array($data['status'], ['pending', 'approved', 'rejected'])) {
            http_response_code(400);
            echo "Valid enrollment ID and status are required.";
            exit;
        }
        $stmt = $pdo->prepare("UPDATE enrollments SET status = ? WHERE id = ?");
        $stmt->execute([$data['status'], $data['id']]);
        echo "Enrollment status updated.";
        break;

    case 'DELETE':
        // Delete enrollment (student or admin)
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id'])) {
            http_response_code(400);
            echo "Enrollment ID is required.";
            exit;
        }
        // Check ownership or admin
        if ($role === 'student') {
            $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE id = ? AND user_id = ?");
            $stmt->execute([$data['id'], $user_id]);
            if (!$stmt->fetch()) {
                http_response_code(403);
                echo "You can only delete your own enrollments.";
                exit;
            }
        }
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo "Enrollment deleted.";
        break;

    default:
        http_response_code(405);
        echo "Method not allowed.";
        break;
}
?>
