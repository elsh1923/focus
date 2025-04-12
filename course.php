<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get course ID from URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id <= 0) {
    redirect('courses.php');
}

// Fetch course details
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    redirect('courses.php');
}

// Fetch lessons for this course
$stmt = $conn->prepare("SELECT l.*, 
                       (SELECT COUNT(*) FROM lesson_completion lc 
                        WHERE lc.lesson_id = l.id AND lc.user_id = ?) as is_completed
                       FROM lessons l 
                       WHERE l.course_id = ? 
                       ORDER BY l.order_number");
$stmt->bind_param("ii", $_SESSION['user_id'], $course_id);
$stmt->execute();
$lessons = $stmt->get_result();

// Check if user is enrolled
$stmt = $conn->prepare("SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $course_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

// Handle enrollment if not already enrolled
if (!$enrollment && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $stmt = $conn->prepare("INSERT INTO user_courses (user_id, course_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_SESSION['user_id'], $course_id);
    $stmt->execute();
    $enrollment = ['progress' => 0];
}

// Handle lesson completion via AJAX
if ($enrollment && isset($_POST['complete_lesson'])) {
    $lesson_id = (int)$_POST['lesson_id'];
    $data = [
        'lesson_id' => $lesson_id,
        'course_id' => $course_id
    ];
    
    $ch = curl_init('complete-lesson.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);
    
    echo $response;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Geez E-learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .lesson-content {
            min-height: 400px;
        }
        
        .lesson-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .lesson-item {
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .lesson-item:hover {
            background-color: #f8f9fa;
        }
        
        .lesson-item.active {
            background-color: #e9ecef;
        }
        
        .progress {
            height: 10px;
        }

        .lesson-item.completed {
            background-color: #d4edda;
        }

        .lesson-item.completed::after {
            content: "✓";
            float: right;
            color: #28a745;
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
        <div class="row">
            <!-- Course Info -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                        
                        <?php if ($enrollment): ?>
                            <div class="mb-3">
                                <small class="text-muted">Progress</small>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $enrollment['progress']; ?>%">
                                        <?php echo $enrollment['progress']; ?>%
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="">
                                <button type="submit" name="enroll" class="btn btn-primary">
                                    Enroll in Course
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($enrollment): ?>
            <!-- Course Content -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Lessons</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush lesson-list">
                            <?php 
                            $lessons->data_seek(0); // Reset pointer
                            while ($lesson = $lessons->fetch_assoc()): 
                            ?>
                            <a href="#lesson-<?php echo $lesson['id']; ?>" 
                               class="list-group-item list-group-item-action lesson-item <?php echo $lesson['is_completed'] ? 'completed' : ''; ?>"
                               data-bs-toggle="list"
                               role="tab">
                                <?php echo htmlspecialchars($lesson['title']); ?>
                                <?php if ($lesson['type'] === 'video'): ?>
                                    <i class="fas fa-video ms-2 text-muted"></i>
                                <?php elseif ($lesson['type'] === 'pdf'): ?>
                                    <i class="fas fa-file-pdf ms-2 text-muted"></i>
                                <?php else: ?>
                                    <i class="fas fa-file-alt ms-2 text-muted"></i>
                                <?php endif; ?>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content">
                            <?php 
                            $lessons->data_seek(0); // Reset pointer
                            while ($lesson = $lessons->fetch_assoc()): 
                            ?>
                            <div class="tab-pane fade lesson-content" id="lesson-<?php echo $lesson['id']; ?>" role="tabpanel">
                                <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                
                                <?php if ($lesson['type'] === 'video'): ?>
                                    <div class="ratio ratio-16x9 mb-4">
                                        <video controls>
                                            <source src="<?php echo htmlspecialchars($lesson['content_url']); ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                <?php elseif ($lesson['type'] === 'pdf'): ?>
                                    <div class="mb-4">
                                        <iframe src="<?php echo htmlspecialchars($lesson['content_url']); ?>" 
                                                width="100%" 
                                                height="500px" 
                                                style="border: none;">
                                        </iframe>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-4">
                                        <?php echo nl2br(htmlspecialchars($lesson['content_url'])); ?>
                                    </div>
                                <?php endif; ?>

                                <button type="button" 
                                        class="btn btn-success complete-lesson-btn <?php echo $lesson['is_completed'] ? 'disabled' : ''; ?>"
                                        data-lesson-id="<?php echo $lesson['id']; ?>"
                                        data-course-id="<?php echo $course_id; ?>">
                                    <i class="fas fa-check me-2"></i>
                                    <?php echo $lesson['is_completed'] ? 'Completed' : 'Mark as Complete'; ?>
                                </button>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activate first lesson by default
        document.addEventListener('DOMContentLoaded', function() {
            const firstLesson = document.querySelector('.lesson-item');
            if (firstLesson) {
                firstLesson.classList.add('active');
                const firstContent = document.querySelector('.tab-pane');
                if (firstContent) {
                    firstContent.classList.add('show', 'active');
                }
            }

            // Handle lesson completion
            document.querySelectorAll('.complete-lesson-btn').forEach(button => {
                button.addEventListener('click', function() {
                    if (this.classList.contains('disabled')) return;

                    const lessonId = this.dataset.lessonId;
                    const courseId = this.dataset.courseId;
                    const button = this;

                    fetch('complete-lesson.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `lesson_id=${lessonId}&course_id=${courseId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            button.classList.add('disabled');
                            button.innerHTML = '<i class="fas fa-check me-2"></i>Completed';
                            
                            // Update lesson item in sidebar
                            const lessonItem = document.querySelector(`[href="#lesson-${lessonId}"]`);
                            if (lessonItem) {
                                lessonItem.classList.add('completed');
                            }

                            // Update progress bar
                            const progressBar = document.querySelector('.progress-bar');
                            if (progressBar) {
                                const currentProgress = parseInt(progressBar.style.width);
                                const newProgress = Math.min(100, currentProgress + (100 / document.querySelectorAll('.lesson-item').length));
                                progressBar.style.width = `${newProgress}%`;
                                progressBar.textContent = `${Math.round(newProgress)}%`;
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
</body>
</html> 