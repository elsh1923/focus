<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title) || empty($description)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (title, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $description);

        if ($stmt->execute()) {
            $success = 'Course added successfully!';
            // Clear form
            $title = $description = '';
        } else {
            $error = 'Failed to add course. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Geez E-learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <h2>Add New Course</h2>
                    <a href="courses.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Courses
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="title" class="form-label">Course Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Course Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Course
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="alert alert-info">
                        <h5>Next Steps:</h5>
                        <ol>
                            <li>After creating the course, you can add lessons to it</li>
                            <li>Click on the "Lessons" button in the courses list to manage lessons</li>
                            <li>You can add video lessons, PDF materials, or text content</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 