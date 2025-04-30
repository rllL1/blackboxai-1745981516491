<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Courses - Online Enrollment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Online Enrollment</a>
        <div class="d-flex">
            <a href="../backend/logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3>Manage Courses</h3>
    <button class="btn btn-success mb-3" id="addCourseBtn">Add New Course</button>
    <div id="coursesTable"></div>
</div>

<!-- Modal -->
<div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="courseForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="courseModalLabel">Add/Edit Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" id="courseId" />
          <div class="mb-3">
              <label for="courseCode" class="form-label">Course Code</label>
              <input type="text" class="form-control" id="courseCode" required />
          </div>
          <div class="mb-3">
              <label for="courseName" class="form-label">Course Name</label>
              <input type="text" class="form-control" id="courseName" required />
          </div>
          <div class="mb-3">
              <label for="courseDescription" class="form-label">Description</label>
              <textarea class="form-control" id="courseDescription" rows="3"></textarea>
          </div>
          <div id="message" class="text-danger"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Course</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const courseModal = new bootstrap.Modal(document.getElementById('courseModal'));
const courseForm = document.getElementById('courseForm');
const coursesTable = document.getElementById('coursesTable');
const addCourseBtn = document.getElementById('addCourseBtn');

async function loadCourses() {
    const response = await fetch('../backend/courses.php');
    const courses = await response.json();
    let html = '<table class="table table-bordered"><thead><tr><th>Code</th><th>Name</th><th>Description</th><th>Actions</th></tr></thead><tbody>';
    courses.forEach(course => {
        html += `<tr>
            <td>${course.course_code}</td>
            <td>${course.course_name}</td>
            <td>${course.description || ''}</td>
            <td>
                <button class="btn btn-sm btn-primary edit-btn" data-id="${course.id}" data-code="${course.course_code}" data-name="${course.course_name}" data-description="${course.description || ''}">Edit</button>
                <button class="btn btn-sm btn-danger delete-btn" data-id="${course.id}">Delete</button>
            </td>
        </tr>`;
    });
    html += '</tbody></table>';
    coursesTable.innerHTML = html;

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('courseId').value = btn.getAttribute('data-id');
            document.getElementById('courseCode').value = btn.getAttribute('data-code');
            document.getElementById('courseName').value = btn.getAttribute('data-name');
            document.getElementById('courseDescription').value = btn.getAttribute('data-description');
            document.getElementById('message').textContent = '';
            courseModal.show();
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (confirm('Are you sure you want to delete this course?')) {
                const response = await fetch('../backend/courses.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: btn.getAttribute('data-id') })
                });
                const text = await response.text();
                alert(text);
                loadCourses();
            }
        });
    });
}

addCourseBtn.addEventListener('click', () => {
    document.getElementById('courseId').value = '';
    document.getElementById('courseCode').value = '';
    document.getElementById('courseName').value = '';
    document.getElementById('courseDescription').value = '';
    document.getElementById('message').textContent = '';
    courseModal.show();
});

courseForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('courseId').value;
    const course_code = document.getElementById('courseCode').value.trim();
    const course_name = document.getElementById('courseName').value.trim();
    const description = document.getElementById('courseDescription').value.trim();

    if (!course_code || !course_name) {
        document.getElementById('message').textContent = 'Course code and name are required.';
        return;
    }

    const method = id ? 'PUT' : 'POST';
    const body = id ? { id, course_code, course_name, description } : { course_code, course_name, description };

    const response = await fetch('../backend/courses.php', {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });
    const text = await response.text();
    alert(text);
    courseModal.hide();
    loadCourses();
});

loadCourses();
</script>
</body>
</html>
