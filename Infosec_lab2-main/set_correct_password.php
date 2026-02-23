<?php
require_once 'db.php';

// This WILL work for 'admin123'
$correct_hash = password_hash('admin123', PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = '$correct_hash' WHERE username = 'admin'";

if (mysqli_query($conn, $sql)) {
    echo "Password updated successfully!<br>";
    echo "New hash: " . $correct_hash . "<br>";
    echo "<br>You can now login with:<br>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>admin123</strong><br>";
    
    // Test it
    $test = mysqli_query($conn, "SELECT password FROM users WHERE username = 'admin'");
    $row = mysqli_fetch_assoc($test);
    
    if (password_verify('admin123', $row['password'])) {
        echo "<br><span style='color:green'>✓ VERIFICATION SUCCESSFUL! It works!</span>";
    } else {
        echo "<br><span style='color:red'>✗ Something is still wrong with PHP's password_verify function.</span>";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
?>