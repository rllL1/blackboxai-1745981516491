<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>St. Dominic Savio College - Online Enrollment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">St. Dominic Savio College</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#courses">Courses</a></li>
                <li class="nav-item"><a class="nav-link" href="#enrollment">Enrollment</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
            </ul>
        </div>
    </div>
</nav>

<header id="home" class="bg-light text-center py-5">
    <div class="container">
        <h1>Welcome to St. Dominic Savio College</h1>
        <p class="lead">Your pathway to quality education and online enrollment.</p>
    </div>
</header>

<section id="courses" class="py-5">
    <div class="container">
        <h2 class="mb-4">Our Courses</h2>
        <div id="coursesList" class="row"></div>
    </div>
</section>

<section id="enrollment" class="bg-light py-5">
    <div class="container">
        <h2 class="mb-4">Enrollment Information</h2>
        <p>Enroll online easily through our system. Register an account and select your desired courses.</p>
        <a href="register.php" class="btn btn-primary">Get Started</a>
    </div>
</section>

<section id="about" class="py-5">
    <div class="container">
        <h2 class="mb-4">About St. Dominic Savio College</h2>
        <p>St. Dominic Savio College is committed to providing quality education and fostering academic excellence. Our online enrollment system makes it easy for students to apply and manage their courses.</p>
    </div>
</section>

<section id="contact" class="bg-light py-5">
    <div class="container">
        <h2 class="mb-4">Contact Us</h2>
        <p>Email: info@stdominicsavio.edu</p>
        <p>Phone: +1 234 567 8900</p>
        <p>Address: 123 College Avenue, City, Country</p>
    </div>
</section>

<footer class="bg-primary text-white text-center py-3">
    <div class="container">
        &copy; 2024 St. Dominic Savio College. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

loadCourses();
</script>
</body>
</html>
