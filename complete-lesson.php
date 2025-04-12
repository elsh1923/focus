<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = intval($_POST['lesson_id']);
    $course_id = intval($_POST['course_id']);
    $user_id = $_SESSION['user_id'];

    // Check if the user is enrolled in the course
    $enrollment_check = "SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?";
    $stmt = mysqli_prepare($conn, $enrollment_check);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Mark lesson as completed
        $query = "INSERT INTO lesson_completion (user_id, course_id, lesson_id) 
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE completed_at = CURRENT_TIMESTAMP";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iii", $user_id, $course_id, $lesson_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update overall course progress
            $progress_query = "UPDATE user_courses uc
                             SET progress = (
                                 SELECT ROUND((COUNT(lc.lesson_id) / COUNT(l.id)) * 100)
                                 FROM lessons l
                                 LEFT JOIN lesson_completion lc ON l.id = lc.lesson_id 
                                 AND lc.user_id = uc.user_id
                                 WHERE l.course_id = uc.course_id
                             )
                             WHERE user_id = ? AND course_id = ?";
            
            $stmt = mysqli_prepare($conn, $progress_query);
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
            mysqli_stmt_execute($stmt);
            
            echo json_encode(['success' => true, 'message' => 'Lesson marked as completed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error marking lesson as completed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User is not enrolled in this course']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 