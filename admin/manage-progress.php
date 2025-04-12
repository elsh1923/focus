<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get all users with their course progress
$query = "SELECT u.id, u.name, u.email, c.title as course_title, 
          uc.progress, COUNT(l.id) as total_lessons,
          (SELECT COUNT(*) FROM lesson_completion lc 
           WHERE lc.user_id = u.id AND lc.course_id = c.id) as completed_lessons
          FROM users u
          JOIN user_courses uc ON u.id = uc.user_id
          JOIN courses c ON uc.course_id = c.id
          JOIN lessons l ON c.id = l.course_id
          GROUP BY u.id, c.id
          ORDER BY u.name, c.title";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User Progress - Geez E-learning</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .progress-container {
            margin: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .progress-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .progress-table th, .progress-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .progress-table th {
            background-color: #f8f9fa;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background-color: #4CAF50;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="progress-container">
            <h2>User Progress Overview</h2>
            <table class="progress-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Course</th>
                        <th>Progress</th>
                        <th>Completed Lessons</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo ($row['completed_lessons'] / $row['total_lessons'] * 100); ?>%"></div>
                                </div>
                                <?php echo round(($row['completed_lessons'] / $row['total_lessons'] * 100), 1); ?>%
                            </td>
                            <td><?php echo $row['completed_lessons'] . ' / ' . $row['total_lessons']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 