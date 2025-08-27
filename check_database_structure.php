<?php
// Check current database structure
require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Database Structure Check</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .highlight { background-color: #ffffcc; }
</style></head><body>";

echo "<h1>🔍 Database Structure Check</h1>";

// Check issued_certificates table structure
echo "<h2>📋 issued_certificates Table Structure</h2>";

$structure_query = "DESCRIBE issued_certificates";
$structure_result = mysqli_query($link, $structure_query);

if ($structure_result) {
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $has_business_name = false;
    $has_operator_manager = false;
    
    while ($row = mysqli_fetch_assoc($structure_result)) {
        $highlight = ($row['Field'] === 'business_name' || $row['Field'] === 'operator_manager') ? 'class="highlight"' : '';
        echo "<tr $highlight>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
        
        if ($row['Field'] === 'business_name') {
            $has_business_name = true;
        }
        if ($row['Field'] === 'operator_manager') {
            $has_operator_manager = true;
        }
    }
    echo "</table>";
    
    echo "<h3>Business Fields Status:</h3>";
    if ($has_business_name && $has_operator_manager) {
        echo "<p class='success'>✅ Both business_name and operator_manager columns exist!</p>";
    } else {
        echo "<p class='error'>❌ Missing business fields:</p>";
        if (!$has_business_name) {
            echo "<p class='error'>✗ business_name column missing</p>";
        }
        if (!$has_operator_manager) {
            echo "<p class='error'>✗ operator_manager column missing</p>";
        }
        
        echo "<h3>🔧 Fix Required:</h3>";
        echo "<p>The certificate handler expects these columns to exist. You need to run the migration:</p>";
        echo "<pre>";
        echo "ALTER TABLE issued_certificates ADD COLUMN business_name VARCHAR(255) NULL AFTER purpose;\n";
        echo "ALTER TABLE issued_certificates ADD COLUMN operator_manager VARCHAR(255) NULL AFTER business_name;\n";
        echo "</pre>";
        
        echo "<p><strong>Or run the migration script:</strong> <a href='add_business_fields_migration.php' target='_blank'>add_business_fields_migration.php</a></p>";
    }
} else {
    echo "<p class='error'>❌ Could not check table structure: " . mysqli_error($link) . "</p>";
}

// Check certificate types
echo "<h2>📋 Certificate Types</h2>";
$cert_types_query = "SELECT id, name, template_file, default_purpose, is_active FROM certificate_types WHERE is_active = 1 ORDER BY name ASC";
$cert_result = mysqli_query($link, $cert_types_query);

if ($cert_result) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Template</th><th>Default Purpose</th></tr>";
    
    while ($row = mysqli_fetch_assoc($cert_result)) {
        $is_business = stripos($row['name'], 'business') !== false;
        $row_class = $is_business ? 'class="highlight"' : '';
        
        echo "<tr $row_class>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['template_file'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['default_purpose'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ Could not fetch certificate types</p>";
}

// Check residents table
echo "<h2>📋 Sample Residents (for testing)</h2>";
$residents_query = "SELECT id, CONCAT(last_name, ', ', first_name, ' ', COALESCE(suffix, '')) AS fullname, status FROM residents WHERE status = 'Active' LIMIT 5";
$residents_result = mysqli_query($link, $residents_query);

if ($residents_result) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Full Name</th><th>Status</th></tr>";
    
    while ($row = mysqli_fetch_assoc($residents_result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ Could not fetch residents</p>";
}

mysqli_close($link);
echo "</body></html>";
?>
