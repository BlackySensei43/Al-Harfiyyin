<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'includes/config.php';

echo "<h1>Database Connection Test</h1>";

// Test connection
if ($conn->connect_error) {
    echo "<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Check users table
    $result = $conn->query("SHOW COLUMNS FROM users");
    if ($result) {
        echo "<h2>Users Table Structure</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Check users in the database
        $result = $conn->query("SELECT id, username, full_name, profile_pic FROM users LIMIT 10");
        if ($result->num_rows > 0) {
            echo "<h2>Users in Database (Limited to 10)</h2>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Profile Picture</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['profile_pic'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No users found in the database.</p>";
        }
        
        // Test update query
        if (isset($_POST['test_update'])) {
            $test_id = (int)$_POST['user_id'];
            $test_path = $conn->real_escape_string($_POST['test_path']);
            
            // Try prepared statement
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $test_path, $test_id);
                
                if ($stmt->execute()) {
                    echo "<p style='color:green'>Database update test successful using prepared statement!</p>";
                } else {
                    echo "<p style='color:red'>Failed to update database using prepared statement: " . $stmt->error . "</p>";
                    
                    // Try direct query
                    $direct_query = "UPDATE users SET profile_pic = '$test_path' WHERE id = $test_id";
                    if ($conn->query($direct_query)) {
                        echo "<p style='color:green'>Database update test successful using direct query!</p>";
                    } else {
                        echo "<p style='color:red'>Failed to update database using direct query: " . $conn->error . "</p>";
                    }
                }
            } else {
                echo "<p style='color:red'>Failed to prepare statement: " . $conn->error . "</p>";
            }
            
            // Show the updated record
            $result = $conn->query("SELECT id, username, full_name, profile_pic FROM users WHERE id = $test_id");
            if ($result && $row = $result->fetch_assoc()) {
                echo "<h3>Updated User Record</h3>";
                echo "<p>ID: " . htmlspecialchars($row['id']) . "</p>";
                echo "<p>Username: " . htmlspecialchars($row['username']) . "</p>";
                echo "<p>Profile Picture: " . htmlspecialchars($row['profile_pic'] ?? 'NULL') . "</p>";
            }
        }
    } else {
        echo "<p style='color:red'>Error retrieving table structure: " . $conn->error . "</p>";
    }
}

// Test form
echo "<h2>Test Database Update</h2>";
echo "<form method='POST'>";
echo "<p>User ID: <input type='number' name='user_id' required></p>";
echo "<p>Test Path: <input type='text' name='test_path' value='assets/images/upload/test_path.jpg' required></p>";
echo "<p><button type='submit' name='test_update'>Test Update</button></p>";
echo "</form>";

echo "<p><a href='upload_test.php'>Go to Upload Test</a></p>";
echo "<p><a href='index.php'>Return to Home Page</a></p>";
?> 