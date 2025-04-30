<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_role = $_SESSION['role'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Online Enrollment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Online Enrollment</a>
        <div class="d-flex">
            <span class="navbar-text me-3">Hello, <?php echo htmlspecialchars($username); ?></span>
            <a href="../backend/logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if ($user_role === 'admin'): ?>
        <h3>Admin Panel</h3>
        <p>Manage courses, students, and enrollments.</p>
        <a href="manage_courses.php" class="btn btn-primary mb-3">Manage Courses</a>
        <a href="manage_enrollments.php" class="btn btn-secondary mb-3">Manage Enrollments</a>
        <a href="manage_students.php" class="btn btn-info mb-3">Manage Students</a>
    <?php else: ?>
        <h3>Enroll in a Course</h3>
        <form id="enrollForm" class="mb-4">
            <div class="mb-3">
                <label for="courseSelect" class="form-label">Select Course</label>
                <select id="courseSelect" class="form-select" required></select>
            </div>
            <div class="mb-3">
                <label for="enrollmentType" class="form-label">Enrollment Type</label>
                <select id="enrollmentType" class="form-select" required>
                    <option value="new">New Student</option>
                    <option value="old">Old Student</option>
                    <option value="transferee">Transferee</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="academicYear" class="form-label">Academic Year</label>
                <select id="academicYear" class="form-select" required>
                    <option value="1st">1st Year</option>
                    <option value="2nd">2nd Year</option>
                    <option value="3rd">3rd Year</option>
                    <option value="4th">4th Year</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="semester" class="form-label">Semester</label>
                <select id="semester" class="form-select" required>
                    <option value="1st">1st Semester</option>
                    <option value="2nd">2nd Semester</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit Enrollment</button>
        </form>

        <h3>Your Enrolled Courses</h3>
        <div id="enrolledCourses" class="row"></div>

        <script>
        async function loadCourses() {
            const response = await fetch('../backend/courses.php');
            const courses = await response.json();
            const courseSelect = document.getElementById('courseSelect');
            courseSelect.innerHTML = '<option value="">Select a course</option>';
            courses.forEach(course => {
                const option = document.createElement('option');
                option.value = course.id;
                option.textContent = course.course_name + ' (' + course.course_code + ')';
                courseSelect.appendChild(option);
            });
        }

        async function loadEnrolledCourses() {
            const response = await fetch('../backend/enrollments.php');
            const enrollments = await response.json();
            const container = document.getElementById('enrolledCourses');
            container.innerHTML = '';
        enrollments.filter(e => e.status === 'approved').forEach(enrollment => {
                const card = document.createElement('div');
                card.className = 'col-md-4 mb-3';
                card.innerHTML = `
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">${enrollment.course_name}</h5>
                            <p class="card-text">Enrollment Type: ${enrollment.enrollment_type}</p>
                            <p class="card-text">Academic Year: ${enrollment.academic_year}</p>
                            <p class="card-text">Semester: ${enrollment.semester}</p>
                            <p class="card-text">Status: ${enrollment.status}</p>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        document.getElementById('enrollForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const courseId = document.getElementById('courseSelect').value;
            const enrollmentType = document.getElementById('enrollmentType').value;
            const academicYear = document.getElementById('academicYear').value;
            const semester = document.getElementById('semester').value;
            if (!courseId || !enrollmentType || !academicYear || !semester) {
                alert('Please select a course, enrollment type, academic year, and semester.');
                return;
            }
            const response = await fetch('../backend/enrollments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ course_id: courseId, enrollment_type: enrollmentType, academic_year: academicYear, semester: semester })
            });
            const text = await response.text();
            alert(text);
            loadEnrolledCourses();
        });

        loadCourses();
        loadEnrolledCourses();
        </script>
    <?php endif; ?>
</div>

<script>
<?php if ($user_role !== 'admin'): ?>
async function loadCourses() {
    const response = await fetch('../backend/courses.php');
    const courses = await response.json();
    const container = document.getElementById('coursesList');
    container.innerHTML = '';
    courses.forEach(course => {
        const card = document.createElement('div');
        card.className = 'col-md-4 mb-3';
        card.innerHTML = `
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">${course.course_name}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">${course.course_code}</h6>
                    <p class="card-text">${course.description || ''}</p>
                    <button class="btn btn-success enroll-btn" data-id="${course.id}">Enroll</button>
                </div>
            </div>
        `;
        container.appendChild(card);
    });

    document.querySelectorAll('.enroll-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const courseId = button.getAttribute('data-id');
            const response = await fetch('../backend/enrollments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ course_id: courseId })
            });
            const text = await response.text();
            alert(text);
        });
    });
}

loadCourses();
<?php endif; ?>
</script>

</body>
</html>
