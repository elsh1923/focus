<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
          (SELECT COUNT(*) FROM user_courses WHERE course_id = c.id) as enrollment_count
          FROM courses c";

$params = [];
$types = '';

if (!empty($search)) {
    $query .= " WHERE c.title LIKE ? OR c.description LIKE ?";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$query .= " ORDER BY c.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$courses = $stmt->get_result();

// Get user's enrolled courses
$stmt = $conn->prepare("SELECT course_id FROM user_courses WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$enrolled_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$enrolled_course_ids = array_column($enrolled_courses, 'course_id');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Courses - Geez E-learning</title>
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
        
        .search-box {
            max-width: 500px;
            margin: 0 auto 30px;
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="courses.php">All Courses</a>
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
        <h1 class="text-center mb-4">All Courses</h1>

        <!-- Search Box -->
        <div class="search-box">
            <form method="GET" action="" class="d-flex">
                <input type="text" name="search" class="form-control me-2" 
                       placeholder="Search courses..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <!-- Courses Grid -->
        <div class="row">
            <?php while ($course = $courses->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card course-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <i class="fas fa-book me-1"></i>
                                <?php echo $course['lesson_count']; ?> lessons
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>
                                <?php echo $course['enrollment_count']; ?> students
                            </small>
                        </div>

                        <?php if (in_array($course['id'], $enrolled_course_ids)): ?>
                            <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary w-100">
                                Continue Learning
                            </a>
                        <?php else: ?>
                            <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary w-100">
                                View Course
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if ($courses->num_rows === 0): ?>
            <div class="text-center py-5">
                <h3>No courses found</h3>
                <p class="text-muted">Try adjusting your search or check back later for new courses.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 