<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT s.*, c.course_code, c.course_name, c.course_description 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.course_id 
        ORDER BY s.created_at DESC";
$result = mysqli_query($conn, $sql);

// Get statistics
$total_students = mysqli_query($conn, "SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_courses = mysqli_query($conn, "SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Student Management System</title>
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
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="add_student.php">
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
                <h1>Dashboard</h1>
                <div class="header-actions">
                    <span class="date-display">
                        <i class="fas fa-calendar"></i> 
                        <?php echo date('F j, Y'); ?>
                    </span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $total_courses; ?></h3>
                        <p>Total Courses</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Active Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-details">
                        <h3>12</h3>
                        <p>This Month</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div class="action-buttons">
                    <a href="add_student.php" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Add New Student</span>
                    </a>
                    <a href="add_course.php" class="action-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add New Course</span>
                    </a>
                </div>
            </div>

            <!-- Student List -->
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-list"></i> Student List</h3>
                    <div class="table-search">
                        <input type="text" id="searchInput" placeholder="Search students..." onkeyup="searchTable()">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <table class="modern-table" id="studentTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><span class="badge-id">#<?php echo htmlspecialchars($row['id']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($row['student_id']); ?></strong></td>
                                    <td>
                                        <div class="student-info">
                                            <i class="fas fa-user-circle"></i>
                                            <span><?php echo htmlspecialchars($row['fullname']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="email-link">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo htmlspecialchars($row['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($row['course_code']): ?>
                                            <span class="course-badge">
                                                <i class="fas fa-book"></i>
                                                <?php echo htmlspecialchars($row['course_code']); ?>
                                            </span>
                                            <span class="course-name"><?php echo htmlspecialchars($row['course_name']); ?></span>
                                        <?php else: ?>
                                            <span class="course-badge no-course">
                                                <i class="fas fa-times-circle"></i> No Course
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <a href="#" class="action-icon view-btn" onclick="viewStudent(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="delete_student.php?id=<?php echo (int)$row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this student?')"
                                           class="action-icon delete-btn">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <!-- Table Footer with Info -->
                    <div class="table-footer">
                        <span class="table-info">
                            Showing <?php echo mysqli_num_rows($result); ?> of <?php echo $total_students; ?> students
                        </span>
                        <div class="table-pagination">
                            <button class="page-btn" disabled><i class="fas fa-chevron-left"></i></button>
                            <span class="page-info">Page 1 of 1</span>
                            <button class="page-btn" disabled><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users fa-4x"></i>
                        <h3>No Students Found</h3>
                        <p>Get started by adding your first student!</p>
                        <a href="add_student.php" class="btn btn-primary">Add Student</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- View Student Modal -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Student Details</h3>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        function searchTable() {
            let input = document.getElementById('searchInput');
            let filter = input.value.toUpperCase();
            let table = document.getElementById('studentTable');
            let rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j]) {
                        let textValue = cells[j].textContent || cells[j].innerText;
                        if (textValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                if (found) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }

        // Modal functions
        function viewStudent(id) {
            // You can implement AJAX here to load student details
            alert('View student details for ID: ' + id + ' (Feature coming soon!)');
        }

        function closeModal() {
            document.getElementById('studentModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            let modal = document.getElementById('studentModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>