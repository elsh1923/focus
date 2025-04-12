<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Fetch user's enrolled courses with progress
$stmt = $conn->prepare("
    SELECT c.*, uc.progress 
    FROM courses c 
    JOIN user_courses uc ON c.id = uc.course_id 
    WHERE uc.user_id = ? 
    ORDER BY uc.enrolled_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$enrolled_courses = $stmt->get_result();

// Fetch available courses (not enrolled)
$stmt = $conn->prepare("
    SELECT c.* 
    FROM courses c 
    WHERE c.id NOT IN (
        SELECT course_id 
        FROM user_courses 
        WHERE user_id = ?
    )
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$available_courses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Geez E-learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .course-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
        }
        
        .progress {
            height: 10px;
        }
        
        .profile-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Geez E-learning</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php">All Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Profile Section -->
        <div class="profile-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
                    <p class="text-muted">Continue your learning journey</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="profile.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Enrolled Courses -->
        <h3 class="mb-4">My Courses</h3>
        <div class="row">
            <?php while ($course = $enrolled_courses->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card course-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                        <div class="mb-3">
                            <small class="text-muted">Progress</small>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $course['progress']; ?>%">
                                    <?php echo $course['progress']; ?>%
                                </div>
                            </div>
                        </div>
                        <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">
                            Continue Learning
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Available Courses -->
        <h3 class="mb-4 mt-5">Available Courses</h3>
        <div class="row">
            <?php while ($course = $available_courses->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card course-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                        <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary">
                            View Course
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 