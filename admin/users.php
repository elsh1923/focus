<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireAdmin();

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // First, delete user's enrollments
    $query = "DELETE FROM user_courses WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    // Then delete the user
    $query = "DELETE FROM users WHERE id = ? AND role = 'user'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    header('Location: users.php?message=User deleted successfully');
    exit();
}

// Get all users
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM user_courses WHERE user_id = u.id) as enrolled_courses
          FROM users u 
          WHERE u.role = 'user'
          ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Geez E-learning</title>
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
                <a href="users.php" class="active">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Users</h2>
                </div>

                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Enrolled Courses</th>
                                        <th>Joined Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo $user['enrolled_courses']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#userModal<?php echo $user['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" 
                                                            name="delete_user" 
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this user?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- User Details Modal -->
                                        <div class="modal fade" id="userModal<?php echo $user['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">User Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        // Get user's enrolled courses
                                                        $courses_query = "SELECT c.title, uc.enrolled_at, uc.progress
                                                                        FROM user_courses uc
                                                                        JOIN courses c ON uc.course_id = c.id
                                                                        WHERE uc.user_id = ?
                                                                        ORDER BY uc.enrolled_at DESC";
                                                        $stmt = mysqli_prepare($conn, $courses_query);
                                                        mysqli_stmt_bind_param($stmt, "i", $user['id']);
                                                        mysqli_stmt_execute($stmt);
                                                        $courses_result = mysqli_stmt_get_result($stmt);
                                                        ?>
                                                        
                                                        <h6>Enrolled Courses:</h6>
                                                        <?php if (mysqli_num_rows($courses_result) > 0): ?>
                                                            <ul class="list-group">
                                                                <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                                                                    <li class="list-group-item">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <span><?php echo htmlspecialchars($course['title']); ?></span>
                                                                            <span class="badge bg-primary">
                                                                                <?php echo $course['progress']; ?>% Complete
                                                                            </span>
                                                                        </div>
                                                                        <small class="text-muted">
                                                                            Enrolled: <?php echo date('M d, Y', strtotime($course['enrolled_at'])); ?>
                                                                        </small>
                                                                    </li>
                                                                <?php endwhile; ?>
                                                            </ul>
                                                        <?php else: ?>
                                                            <p class="text-muted">No enrolled courses</p>
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