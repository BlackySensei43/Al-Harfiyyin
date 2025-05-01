<?php
// Script to test upload capabilities and directory permissions
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Upload Test Script</h1>";
echo "<p>This script tests your server's ability to handle file uploads.</p>";

// Check PHP configuration
echo "<h2>PHP Configuration</h2>";
echo "<ul>";
echo "<li>upload_max_filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>post_max_size: " . ini_get('post_max_size') . "</li>";
echo "<li>max_file_uploads: " . ini_get('max_file_uploads') . "</li>";
echo "</ul>";

// Test directories
$directories = [
    'assets/uploads/profile_pics/',
    'assets/uploads/posts/'
];

echo "<h2>Directory Permissions</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Directory</th><th>Exists</th><th>Writable</th><th>Permissions</th></tr>";

foreach ($directories as $dir) {
    $exists = file_exists($dir);
    $writable = is_writable($dir);
    $perms = $exists ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A';
    
    echo "<tr>";
    echo "<td>$dir</td>";
    echo "<td>" . ($exists ? '✅ Yes' : '❌ No') . "</td>";
    echo "<td>" . ($writable ? '✅ Yes' : '❌ No') . "</td>";
    echo "<td>$perms</td>";
    echo "</tr>";
    
    // Try to create a test file
    if ($exists && $writable) {
        $testFile = $dir . '/test_' . time() . '.txt';
        $createTest = @file_put_contents($testFile, 'Test file');
        
        echo "<tr>";
        echo "<td colspan='4'>Test file creation in $dir: ";
        
        if ($createTest !== false) {
            echo "✅ Success - file created and written successfully";
            // Clean up
            @unlink($testFile);
        } else {
            echo "❌ Failed to create test file";
        }
        
        echo "</td></tr>";
    }
}

echo "</table>";

// Test upload form
echo "<h2>Upload Test Form</h2>";
echo "<p>Use this form to test uploading a file:</p>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_file'])) {
    echo "<h3>Upload Results:</h3>";
    echo "<pre>";
    print_r($_FILES['test_file']);
    echo "</pre>";
    
    if ($_FILES['test_file']['error'] == 0) {
        $uploadDir = 'assets/uploads/posts/';
        $testTarget = $uploadDir . basename($_FILES['test_file']['name']);
        
        if (move_uploaded_file($_FILES['test_file']['tmp_name'], $testTarget)) {
            echo "<p style='color:green'>✅ File uploaded successfully to: $testTarget</p>";
            // Show the file
            if (strpos($_FILES['test_file']['type'], 'image/') === 0) {
                echo "<p><img src='$testTarget' style='max-width:300px;'></p>";
            }
            echo "<p><a href='$testTarget' target='_blank'>View uploaded file</a></p>";
        } else {
            echo "<p style='color:red'>❌ Failed to move uploaded file to target directory.</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Upload failed with error code: " . $_FILES['test_file']['error'] . "</p>";
        
        // Error explanations
        $errorMessages = [
            1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
            2 => "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form",
            3 => "The uploaded file was only partially uploaded",
            4 => "No file was uploaded",
            6 => "Missing a temporary folder",
            7 => "Failed to write file to disk",
            8 => "A PHP extension stopped the file upload"
        ];
        
        if (isset($errorMessages[$_FILES['test_file']['error']])) {
            echo "<p>Error explanation: " . $errorMessages[$_FILES['test_file']['error']] . "</p>";
        }
    }
}

?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file" required>
    <button type="submit">Upload Test File</button>
</form>

<p><a href="create_upload_dirs.php">Run the directory creation script</a></p>
<p><a href="index.php">Return to home page</a></p> 