<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors = array();
$success = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
}

if (isset($_POST['add_course'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }
    
    $course_code = strtoupper(trim($_POST['course_code']));
    $course_name = trim($_POST['course_name']);
    $course_description = trim($_POST['course_description']);
    
    if (empty($course_code)) {
        $errors[] = "Course code is required";
    } elseif (!preg_match('/^[A-Z0-9]+$/', $course_code)) {
        $errors[] = "Course code can only contain uppercase letters and numbers";
    }
    
    if (empty($course_name)) {
        $errors[] = "Course name is required";
    }
    
    if (empty($errors)) {
        $checkSql = "SELECT course_id FROM courses WHERE course_code = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $course_code);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $errors[] = "Course code already exists";
        }
        $checkStmt->close();
    }
    
    if (empty($errors)) {
        $sql = "INSERT INTO courses (course_code, course_name, course_description) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $course_code, $course_name, $course_description);
        
        if ($stmt->execute()) {
            $success = "Course added successfully!";
            $_POST = array();
        } else {
            $errors[] = "Failed to add course. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Course</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Add New Course</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success != ''): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label>Course Code:</label>
                <input type="text" name="course_code" 
                       value="<?php echo isset($_POST['course_code']) ? htmlspecialchars($_POST['course_code']) : ''; ?>" 
                       required pattern="[A-Z0-9]+" 
                       title="Uppercase letters and numbers only">
            </div>
            
            <div class="form-group">
                <label>Course Name:</label>
                <input type="text" name="course_name" 
                       value="<?php echo isset($_POST['course_name']) ? htmlspecialchars($_POST['course_name']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label>Course Description:</label>
                <textarea name="course_description" rows="4"><?php echo isset($_POST['course_description']) ? htmlspecialchars($_POST['course_description']) : ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_course">Add Course</button>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>