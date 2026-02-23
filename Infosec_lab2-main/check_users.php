<?php
require_once 'db.php';

echo "<h2>Checking Users Table</h2>";

$sql = "SELECT id, username, password, is_active FROM users";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<p>";
        echo "ID: " . $row['id'] . "<br>";
        echo "Username: " . $row['username'] . "<br>";
        echo "Password Hash: " . $row['password'] . "<br>";
        echo "Is Active: " . $row['is_active'] . "<br>";
        
        // Test if password 'admin123' verifies
        if (password_verify('admin123', $row['password'])) {
            echo "<span style='color:green'>✓ Password 'admin123' VERIFIES correctly!</span><br>";
        } else {
            echo "<span style='color:red'>✗ Password 'admin123' does NOT verify</span><br>";
        }
        echo "</p><hr>";
    }
} else {
    echo "No users found in database!";
}

// Also check if the table structure is correct
echo "<h2>Table Structure</h2>";
$structure = mysqli_query($conn, "DESCRIBE users");
echo "<pre>";
while ($col = mysqli_fetch_assoc($structure)) {
    print_r($col);
}
echo "</pre>";
?>