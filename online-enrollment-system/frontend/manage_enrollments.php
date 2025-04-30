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
    <title>Manage Enrollments - Online Enrollment System</title>
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
    <h3>Manage Enrollments</h3>
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="filterAcademicYear" class="form-label">Academic Year</label>
            <select id="filterAcademicYear" class="form-select">
                <option value="">All</option>
                <option value="1st">1st Year</option>
                <option value="2nd">2nd Year</option>
                <option value="3rd">3rd Year</option>
                <option value="4th">4th Year</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterSemester" class="form-label">Semester</label>
            <select id="filterSemester" class="form-select">
                <option value="">All</option>
                <option value="1st">1st Semester</option>
                <option value="2nd">2nd Semester</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterStatus" class="form-label">Status</label>
            <select id="filterStatus" class="form-select">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button id="filterBtn" class="btn btn-primary w-100">Filter</button>
        </div>
    </div>
    <table class="table table-bordered" id="enrollmentsTable">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Course Name</th>
                <th>Enrollment Type</th>
                <th>Academic Year</th>
                <th>Semester</th>
                <th>Enrollment Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
async function loadEnrollments(filters = {}) {
    const params = new URLSearchParams(filters);
    const response = await fetch('../backend/enrollments.php?' + params.toString());
    const enrollments = await response.json();
    const tbody = document.querySelector('#enrollmentsTable tbody');
    tbody.innerHTML = '';
    enrollments.forEach(enrollment => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${enrollment.full_name || ''}</td>
            <td>${enrollment.course_name}</td>
            <td>${enrollment.enrollment_type}</td>
            <td>${enrollment.academic_year}</td>
            <td>${enrollment.semester}</td>
            <td>${new Date(enrollment.enrollment_date).toLocaleString()}</td>
            <td>${enrollment.status}</td>
            <td>
                <button class="btn btn-sm btn-success approve-btn" data-id="${enrollment.id}">Approve</button>
                <button class="btn btn-sm btn-danger reject-btn" data-id="${enrollment.id}">Reject</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', () => updateStatus(btn.getAttribute('data-id'), 'approved'));
    });
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', () => updateStatus(btn.getAttribute('data-id'), 'rejected'));
    });
}

document.getElementById('filterBtn').addEventListener('click', () => {
    const filters = {
        academic_year: document.getElementById('filterAcademicYear').value,
        semester: document.getElementById('filterSemester').value,
        status: document.getElementById('filterStatus').value
    };
    loadEnrollments(filters);
});

async function updateStatus(id, status) {
    const response = await fetch('../backend/enrollments.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status })
    });
    const text = await response.text();
    alert(text);
    loadEnrollments();
}

loadEnrollments();
</script>

</body>
</html>
