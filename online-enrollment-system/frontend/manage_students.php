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
    <title>Manage Students - Online Enrollment System</title>
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
    <h3>Manage Students</h3>
    <button class="btn btn-success mb-3" id="addStudentBtn">Add New Student</button>
    <div id="studentsTable"></div>
</div>

<!-- Modal -->
<div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="studentForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="studentModalLabel">Add/Edit Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" id="studentId" />
          <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" required />
          </div>
          <div class="mb-3">
              <label for="fullName" class="form-label">Full Name</label>
              <input type="text" class="form-control" id="fullName" required />
          </div>
          <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" required />
          </div>
          <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" />
              <small class="form-text text-muted">Leave blank to keep current password.</small>
          </div>
          <div id="message" class="text-danger"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Student</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));
const studentForm = document.getElementById('studentForm');
const studentsTable = document.getElementById('studentsTable');
const addStudentBtn = document.getElementById('addStudentBtn');

async function loadStudents() {
    const response = await fetch('../backend/students.php');
    const students = await response.json();
    let html = '<table class="table table-bordered"><thead><tr><th>Username</th><th>Full Name</th><th>Email</th><th>Created At</th><th>Actions</th></tr></thead><tbody>';
    students.forEach(student => {
        html += `<tr>
            <td>${student.username}</td>
            <td>${student.full_name}</td>
            <td>${student.email}</td>
            <td>${new Date(student.created_at).toLocaleString()}</td>
            <td>
                <button class="btn btn-sm btn-primary edit-btn" data-id="${student.id}" data-username="${student.username}" data-fullname="${student.full_name}" data-email="${student.email}">Edit</button>
                <button class="btn btn-sm btn-danger delete-btn" data-id="${student.id}">Delete</button>
            </td>
        </tr>`;
    });
    html += '</tbody></table>';
    studentsTable.innerHTML = html;

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('studentId').value = btn.getAttribute('data-id');
            document.getElementById('username').value = btn.getAttribute('data-username');
            document.getElementById('fullName').value = btn.getAttribute('data-fullname');
            document.getElementById('email').value = btn.getAttribute('data-email');
            document.getElementById('password').value = '';
            document.getElementById('message').textContent = '';
            studentModal.show();
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (confirm('Are you sure you want to delete this student?')) {
                const response = await fetch('../backend/students.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: btn.getAttribute('data-id') })
                });
                const text = await response.text();
                alert(text);
                loadStudents();
            }
        });
    });
}

addStudentBtn.addEventListener('click', () => {
    document.getElementById('studentId').value = '';
    document.getElementById('username').value = '';
    document.getElementById('fullName').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('message').textContent = '';
    studentModal.show();
});

studentForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('studentId').value;
    const username = document.getElementById('username').value.trim();
    const full_name = document.getElementById('fullName').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    if (!username || !full_name || !email) {
        document.getElementById('message').textContent = 'Username, full name, and email are required.';
        return;
    }

    let method = 'POST';
    let body = { username, full_name, email };
    if (id) {
        method = 'PUT';
        body.id = id;
        // Password update handled separately
    } else {
        if (!password) {
            document.getElementById('message').textContent = 'Password is required for new student.';
            return;
        }
        body.password = password;
    }

    const response = await fetch('../backend/students.php', {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });
    const text = await response.text();

    if (text.includes('successfully')) {
        if (id && password) {
            // Update password separately
            const passResponse = await fetch('../backend/update_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, password })
            });
            const passText = await passResponse.text();
            alert(passText);
        } else {
            alert(text);
        }
        studentModal.hide();
        loadStudents();
    } else {
        document.getElementById('message').textContent = text;
    }
});

loadStudents();
</script>
</body>
</html>
