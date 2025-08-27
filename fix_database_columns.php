<?php
// Fix Database Columns for Business Certificate Support
require_once 'config.php';

echo "<h1>Database Column Fix</h1>";

// Check if business_name column exists
$check_business_name = "SHOW COLUMNS FROM issued_certificates LIKE 'business_name'";
$result_business_name = mysqli_query($link, $check_business_name);

// Check if operator_manager column exists
$check_operator_manager = "SHOW COLUMNS FROM issued_certificates LIKE 'operator_manager'";
$result_operator_manager = mysqli_query($link, $check_operator_manager);

$business_name_exists = ($result_business_name && mysqli_num_rows($result_business_name) > 0);
$operator_manager_exists = ($result_operator_manager && mysqli_num_rows($result_operator_manager) > 0);

echo "<h2>Current Status:</h2>";
echo "<p>business_name column exists: " . ($business_name_exists ? "YES" : "NO") . "</p>";
echo "<p>operator_manager column exists: " . ($operator_manager_exists ? "YES" : "NO") . "</p>";

// Add missing columns
if (!$business_name_exists) {
    echo "<h3>Adding business_name column...</h3>";
    $alter_sql1 = "ALTER TABLE issued_certificates ADD COLUMN business_name VARCHAR(255) NULL AFTER purpose";
    if (mysqli_query($link, $alter_sql1)) {
        echo "<p style='color: green;'>✅ Successfully added business_name column</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding business_name column: " . mysqli_error($link) . "</p>";
    }
}

if (!$operator_manager_exists) {
    echo "<h3>Adding operator_manager column...</h3>";
    $alter_sql2 = "ALTER TABLE issued_certificates ADD COLUMN operator_manager VARCHAR(255) NULL AFTER " . ($business_name_exists || !$business_name_exists ? "business_name" : "purpose");
    if (mysqli_query($link, $alter_sql2)) {
        echo "<p style='color: green;'>✅ Successfully added operator_manager column</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding operator_manager column: " . mysqli_error($link) . "</p>";
    }
}

if ($business_name_exists && $operator_manager_exists) {
    echo "<h3 style='color: green;'>✅ All required columns already exist!</h3>";
}

// Verify final structure
echo "<h2>Final Database Structure:</h2>";
$describe_sql = "DESCRIBE issued_certificates";
$describe_result = mysqli_query($link, $describe_sql);

if ($describe_result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($describe_result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($link);
?>
