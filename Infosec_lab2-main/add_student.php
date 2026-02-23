<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors = array();
$success = '';
$courses = array();

// Fetch courses for dropdown
$coursesResult = mysqli_query($conn, "SELECT course_id, course_code, course_name FROM courses ORDER BY course_code");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
}

if (isset($_POST['add'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }
    
    $student_id = trim($_POST['student_id']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $course_id = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;
    
    if (empty($student_id)) {
        $errors[] = "Student ID is required";
    } elseif (!preg_match('/^[A-Za-z0-9-]+$/', $student_id)) {
        $errors[] = "Student ID can only contain letters, numbers, and hyphens";
    }
    
    if (empty($fullname)) {
        $errors[] = "Full name is required";
    } elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $fullname)) {
        $errors[] = "Full name can only contain letters, spaces, apostrophes, and hyphens";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($errors)) {
        $checkSql = "SELECT id FROM students WHERE student_id = ? OR email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $student_id, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $errors[] = "Student ID or Email already exists";
        }
        $checkStmt->close();
    }
    
    if (empty($errors)) {
        $sql = "INSERT INTO students (student_id, fullname, email, course_id, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $student_id, $fullname, $email, $course_id);
        
        if ($stmt->execute()) {
            $success = "Student added successfully!";
            $_POST = array();
        } else {
            $errors[] = "Failed to add student. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student - Student Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>SMS</h2>
                <p>Student Management</p>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                    <p>Administrator</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="add_student.php" class="active">
                    <i class="fas fa-user-plus"></i> Add Student
                </a>
                <a href="add_course.php">
                    <i class="fas fa-book"></i> Add Course
                </a>
                <a href="logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-user-plus"></i> Add New Student</h1>
                <div class="header-actions">
                    <span class="date-display">
                        <i class="fas fa-calendar"></i> 
                        <?php echo date('F j, Y'); ?>
                    </span>
                </div>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <!-- Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div class="alert-content">
                            <strong>Please fix the following errors:</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success != ''): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div class="alert-content">
                            <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Student Form -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h3><i class="fas fa-graduation-cap"></i> Student Information</h3>
                        <p>Enter the details of the new student</p>
                    </div>
                    
                    <form method="POST" action="" class="modern-form" id="studentForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="student_id">
                                    <i class="fas fa-id-card"></i>
                                    Student ID <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       id="student_id" 
                                       name="student_id" 
                                       value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>" 
                                       required
                                       placeholder="e.g., 2024-0001"
                                       class="form-control">
                                <small class="form-text">Format: Letters, numbers, and hyphens only</small>
                            </div>

                            <div class="form-group">
                                <label for="fullname">
                                    <i class="fas fa-user"></i>
                                    Full Name <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       id="fullname" 
                                       name="fullname" 
                                       value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" 
                                       required
                                       placeholder="e.g., Juan Dela Cruz"
                                       class="form-control">
                                <small class="form-text">Letters, spaces, apostrophes, and hyphens only</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    Email Address <span class="required">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required
                                       placeholder="e.g., juan.delacruz@example.com"
                                       class="form-control">
                                <small class="form-text">We'll never share your email with anyone else</small>
                            </div>

                            <div class="form-group">
                                <label for="course_id">
                                    <i class="fas fa-book-open"></i>
                                    Course
                                </label>
                                <select id="course_id" name="course_id" class="form-control">
                                    <option value="">-- Select a course (optional) --</option>
                                    <?php 
                                    mysqli_data_seek($coursesResult, 0); // Reset pointer
                                    while ($course = mysqli_fetch_assoc($coursesResult)): 
                                    ?>
                                        <option value="<?php echo $course['course_id']; ?>"
                                            <?php echo (isset($_POST['course_id']) && $_POST['course_id'] == $course['course_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="form-text">Leave empty if no course assigned yet</small>
                            </div>
                        </div>

                        <!-- Preview Card -->
                        <div class="preview-card" id="previewCard" style="display: none;">
                            <h4><i class="fas fa-eye"></i> Student Preview</h4>
                            <div class="preview-content">
                                <div class="preview-item">
                                    <span class="preview-label">Student ID:</span>
                                    <span class="preview-value" id="previewId">-</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Full Name:</span>
                                    <span class="preview-value" id="previewName">-</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Email:</span>
                                    <span class="preview-value" id="previewEmail">-</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Course:</span>
                                    <span class="preview-value" id="previewCourse">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="add" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Student
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="button" class="btn btn-info" onclick="previewStudent()">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="reset" class="btn btn-warning" onclick="clearForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Quick Tips -->
                <div class="tips-card">
                    <h4><i class="fas fa-lightbulb"></i> Quick Tips</h4>
                    <ul class="tips-list">
                        <li><i class="fas fa-check-circle"></i> Student ID should be unique and follow the school format</li>
                        <li><i class="fas fa-check-circle"></i> Use the student's complete legal name</li>
                        <li><i class="fas fa-check-circle"></i> Make sure to enter a valid email address</li>
                        <li><i class="fas fa-check-circle"></i> You can assign a course now or later</li>
                        <li><i class="fas fa-check-circle"></i> All fields marked with <span class="required">*</span> are required</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live preview functionality
        function previewStudent() {
            const studentId = document.getElementById('student_id').value;
            const fullname = document.getElementById('fullname').value;
            const email = document.getElementById('email').value;
            const courseSelect = document.getElementById('course_id');
            const courseText = courseSelect.options[courseSelect.selectedIndex]?.text || 'None';
            
            document.getElementById('previewId').textContent = studentId || '-';
            document.getElementById('previewName').textContent = fullname || '-';
            document.getElementById('previewEmail').textContent = email || '-';
            document.getElementById('previewCourse').textContent = courseText || '-';
            
            document.getElementById('previewCard').style.display = 'block';
            
            // Scroll to preview
            document.getElementById('previewCard').scrollIntoView({ behavior: 'smooth' });
        }

        // Clear form function
        function clearForm() {
            if (confirm('Are you sure you want to reset the form?')) {
                document.getElementById('studentForm').reset();
                document.getElementById('previewCard').style.display = 'none';
            }
        }

        // Real-time validation
        document.getElementById('student_id').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
        });

        document.getElementById('fullname').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^a-zA-Z\s\'-]/g, '');
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.animation = 'slideOut 0.5s ease forwards';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Form validation before submit
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            const studentId = document.getElementById('student_id').value;
            const fullname = document.getElementById('fullname').value;
            const email = document.getElementById('email').value;
            
            if (!studentId || !fullname || !email) {
                e.preventDefault();
                alert('Please fill in all required fields!');
            }
        });
    </script>
</body>
</html>