<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireAdmin();

$stats = getAdminStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Geez E-learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Admin Navigation -->
            <div class="col-md-2 admin-nav">
                <h4 class="text-white mb-4 px-3">Admin Panel</h4>
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="courses.php">
                    <i class="fas fa-book me-2"></i>Courses
                </a>
                <a href="users.php">
                    <i class="fas fa-users me-2"></i>Users
                </a>
                <a href="enrollments.php">
                    <i class="fas fa-user-graduate me-2"></i>Enrollments
                </a>
                <a href="../logout.php" class="mt-4">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4">Admin Dashboard</h2>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p class="text-muted">Total Users</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon text-success">
                                <i class="fas fa-book"></i>
                            </div>
                            <h3><?php echo $stats['total_courses']; ?></h3>
                            <p class="text-muted">Total Courses</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon text-info">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h3><?php echo $stats['recent_enrollments']; ?></h3>
                            <p class="text-muted">Recent Enrollments (7 days)</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get recent enrollments with user and course details
                        $query = "SELECT u.name as user_name, c.title as course_title, uc.enrolled_at 
                                FROM user_courses uc
                                JOIN users u ON uc.user_id = u.id
                                JOIN courses c ON uc.course_id = c.id
                                ORDER BY uc.enrolled_at DESC
                                LIMIT 10";
                        $result = mysqli_query($conn, $query);
                        
                        if (mysqli_num_rows($result) > 0) {
                            echo '<div class="list-group">';
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<div class="list-group-item">';
                                echo '<div class="d-flex w-100 justify-content-between">';
                                echo '<h6 class="mb-1">' . htmlspecialchars($row['user_name']) . ' enrolled in ' . 
                                     htmlspecialchars($row['course_title']) . '</h6>';
                                echo '<small class="text-muted">' . date('M d, Y H:i', strtotime($row['enrolled_at'])) . '</small>';
                                echo '</div>';
                                echo '</div>';
                            }
                            echo '</div>';
                        } else {
                            echo '<p class="text-muted">No recent activity</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 