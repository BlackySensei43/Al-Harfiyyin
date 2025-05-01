<?php
// Script to create all necessary upload directories with proper permissions
// Run this script once when setting up the website

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$directories = [
    'assets/uploads/profile_pics',
    'assets/uploads/posts'
];

$success = true;
$messages = [];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            chmod($dir, 0777); // Ensure directory is writable
            $messages[] = "✅ Created directory: $dir";
        } else {
            $success = false;
            $messages[] = "❌ Failed to create directory: $dir";
        }
    } else {
        // Directory exists, ensure it's writable
        if (is_writable($dir)) {
            $messages[] = "✅ Directory already exists and is writable: $dir";
        } else {
            if (chmod($dir, 0777)) {
                $messages[] = "✅ Updated permissions for directory: $dir";
            } else {
                $success = false;
                $messages[] = "❌ Failed to update permissions for directory: $dir";
            }
        }
    }
}

// Output results
echo "<html><head><title>Directory Setup</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h1 { color: #333; }
    .success { color: green; }
    .error { color: red; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
</style>";
echo "</head><body>";
echo "<h1>Upload Directory Setup</h1>";

if ($success) {
    echo "<div class='success'>All directories were successfully created or already exist with proper permissions.</div>";
} else {
    echo "<div class='error'>There were issues creating some directories. See details below.</div>";
}

echo "<pre>";
foreach ($messages as $message) {
    echo $message . "\n";
}
echo "</pre>";

echo "<p>If you're seeing permission errors, you may need to contact your hosting provider for assistance.</p>";

echo "<p><a href='index.php'>Return to homepage</a></p>";
echo "</body></html>";
?> 