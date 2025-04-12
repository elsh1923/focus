<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireAdmin();

// Get all enrollments with user and course details
$query = "SELECT uc.*, 
          u.name as user_name, u.email as user_email,
          c.title as course_title
          FROM user_courses uc
          JOIN users u ON uc.user_id = u.id
          JOIN courses c ON uc.course_id = c.id
          ORDER BY uc.enrolled_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Enrollments - Geez E-learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-nav {
            background: #343a40;
            min-height: 100vh;
            padding-top: 20px;
        }
        .admin-nav a {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            transition: background 0.3s;
        }
        .admin-nav a:hover {
            background: #495057;
        }
        .admin-nav a.active {
            background: #007bff;
        }
        .progress {
            height: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Admin Navigation -->
            <div class="col-md-2 admin-nav">
                <h4 class="text-white mb-4 px-3">Admin Panel</h4>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="courses.php">
                    <i class="fas fa-book me-2"></i>Courses
                </a>
                <a href="users.php">
                    <i class="fas fa-users me-2"></i>Users
                </a>
                <a href="enrollments.php" class="active">
                    <i class="fas fa-user-graduate me-2"></i>Enrollments
                </a>
                <a href="../logout.php" class="mt-4">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Enrollments</h2>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Course</th>
                                        <th>Enrolled Date</th>
                                        <th>Progress</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($enrollment = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td>
                                                <div><?php echo htmlspecialchars($enrollment['user_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($enrollment['user_email']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($enrollment['course_title']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $enrollment['progress']; ?>%">
                                                        <?php echo $enrollment['progress']; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#enrollmentModal<?php echo $enrollment['user_id'] . '_' . $enrollment['course_id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Enrollment Details Modal -->
                                        <div class="modal fade" id="enrollmentModal<?php echo $enrollment['user_id'] . '_' . $enrollment['course_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Enrollment Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        // Get completed lessons
                                                        $lessons_query = "SELECT l.title, lc.completed_at
                                                                        FROM lesson_completion lc
                                                                        JOIN lessons l ON lc.lesson_id = l.id
                                                                        WHERE lc.user_id = ? AND lc.course_id = ?
                                                                        ORDER BY lc.completed_at DESC";
                                                        $stmt = mysqli_prepare($conn, $lessons_query);
                                                        mysqli_stmt_bind_param($stmt, "ii", $enrollment['user_id'], $enrollment['course_id']);
                                                        mysqli_stmt_execute($stmt);
                                                        $lessons_result = mysqli_stmt_get_result($stmt);
                                                        ?>
                                                        
                                                        <h6>Completed Lessons:</h6>
                                                        <?php if (mysqli_num_rows($lessons_result) > 0): ?>
                                                            <ul class="list-group">
                                                                <?php while ($lesson = mysqli_fetch_assoc($lessons_result)): ?>
                                                                    <li class="list-group-item">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <span><?php echo htmlspecialchars($lesson['title']); ?></span>
                                                                            <small class="text-muted">
                                                                                Completed: <?php echo date('M d, Y', strtotime($lesson['completed_at'])); ?>
                                                                            </small>
                                                                        </div>
                                                                    </li>
                                                                <?php endwhile; ?>
                                                            </ul>
                                                        <?php else: ?>
                                                            <p class="text-muted">No completed lessons yet</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
</body>
</html> 