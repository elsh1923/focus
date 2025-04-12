<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
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

// Handle lesson deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $lesson_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ? AND course_id = ?");
    $stmt->bind_param("ii", $lesson_id, $course_id);
    $stmt->execute();
    redirect("manage-lessons.php?id=$course_id");
}

// Handle lesson reordering
if (isset($_POST['reorder'])) {
    $order = json_decode($_POST['order'], true);
    foreach ($order as $position => $lesson_id) {
        $stmt = $conn->prepare("UPDATE lessons SET order_number = ? WHERE id = ? AND course_id = ?");
        $stmt->bind_param("iii", $position, $lesson_id, $course_id);
        $stmt->execute();
    }
    exit('success');
}

// Fetch lessons for this course
$stmt = $conn->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY order_number");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$lessons = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lessons - Geez E-learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
        }
        
        .sidebar .nav-link:hover {
            color: white;
        }
        
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,.1);
        }
        
        .lesson-item {
            cursor: move;
            transition: background-color 0.3s;
        }
        
        .lesson-item:hover {
            background-color: #f8f9fa;
        }
        
        .lesson-item.dragging {
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h4>Admin Panel</h4>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="courses.php">
                                <i class="fas fa-book me-2"></i>Courses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Manage Lessons</h2>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($course['title']); ?></p>
                    </div>
                    <div>
                        <a href="add-lesson.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Lesson
                        </a>
                        <a href="courses.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Courses
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="lessons-list">
                                    <?php while ($lesson = $lessons->fetch_assoc()): ?>
                                    <tr class="lesson-item" data-id="<?php echo $lesson['id']; ?>">
                                        <td>
                                            <i class="fas fa-grip-vertical text-muted"></i>
                                            <?php echo $lesson['order_number']; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                                        <td>
                                            <?php if ($lesson['type'] === 'video'): ?>
                                                <i class="fas fa-video text-primary"></i> Video
                                            <?php elseif ($lesson['type'] === 'pdf'): ?>
                                                <i class="fas fa-file-pdf text-danger"></i> PDF
                                            <?php else: ?>
                                                <i class="fas fa-file-alt text-secondary"></i> Text
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit-lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?id=<?php echo $course_id; ?>&delete=<?php echo $lesson['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this lesson?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script>
        // Initialize sortable
        const list = document.getElementById('lessons-list');
        new Sortable(list, {
            animation: 150,
            onEnd: function() {
                // Get new order
                const order = Array.from(list.children).map((item, index) => ({
                    id: item.dataset.id,
                    position: index + 1
                }));
                
                // Send to server
                fetch('manage-lessons.php?id=<?php echo $course_id; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'reorder=1&order=' + JSON.stringify(order)
                });
            }
        });
    </script>
</body>
</html> 