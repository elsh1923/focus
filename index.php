<?php
require_once 'config.php';

// Fetch featured courses
$stmt = $conn->prepare("SELECT * FROM courses ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$courses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geez E-learning - Learn Geez Language Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        
        .course-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .cta-button {
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 30px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Geez E-learning</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#courses">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Learn Geez Language Online</h1>
            <p class="lead mb-4">Discover the ancient language of Ethiopia through our comprehensive online courses</p>
            <a href="register.php" class="btn btn-light btn-lg cta-button">Start Learning Now</a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Our Platform?</h2>
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <i class="fas fa-video feature-icon"></i>
                    <h3>Video Lessons</h3>
                    <p>Learn through high-quality video lessons from expert instructors</p>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <i class="fas fa-book feature-icon"></i>
                    <h3>Comprehensive Materials</h3>
                    <p>Access to extensive learning materials and resources</p>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <i class="fas fa-chart-line feature-icon"></i>
                    <h3>Progress Tracking</h3>
                    <p>Monitor your learning progress and achievements</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Courses Section -->
    <section id="courses" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Featured Courses</h2>
            <div class="row">
                <?php while ($course = $courses->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card course-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                            <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">View Course</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-4">
                <a href="courses.php" class="btn btn-outline-primary">View All Courses</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Geez E-learning</h5>
                    <p>Your gateway to learning the ancient Geez language</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> Geez E-learning. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 