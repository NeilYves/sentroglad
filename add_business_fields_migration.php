<?php
// Database migration script to add business fields to issued_certificates table
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Business Fields Migration</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .migration-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔧 Business Fields Database Migration</h1>";
echo "<p>This script adds business_name and operator_manager columns to the issued_certificates table.</p>";

// Check current table structure
echo "<div class='migration-section'>";
echo "<h2>📋 Current Table Structure</h2>";

$structure_query = "DESCRIBE issued_certificates";
$structure_result = mysqli_query($link, $structure_query);

if ($structure_result) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $has_business_name = false;
    $has_operator_manager = false;
    
    while ($row = mysqli_fetch_assoc($structure_result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
        
        if ($row['Field'] === 'business_name') {
            $has_business_name = true;
        }
        if ($row['Field'] === 'operator_manager') {
            $has_operator_manager = true;
        }
    }
    echo "</table>";
    
    echo "<h3>Migration Status:</h3>";
    if ($has_business_name && $has_operator_manager) {
        echo "<p class='success'>✅ Both business_name and operator_manager columns already exist!</p>";
        echo "<p>No migration needed.</p>";
    } else {
        echo "<p class='warning'>⚠ Missing columns detected:</p>";
        if (!$has_business_name) {
            echo "<p class='error'>✗ business_name column missing</p>";
        }
        if (!$has_operator_manager) {
            echo "<p class='error'>✗ operator_manager column missing</p>";
        }
        
        // Perform migration
        echo "<h3>🔧 Performing Migration...</h3>";
        
        $migration_success = true;
        
        if (!$has_business_name) {
            $add_business_name = "ALTER TABLE issued_certificates ADD COLUMN business_name VARCHAR(255) NULL AFTER purpose";
            if (mysqli_query($link, $add_business_name)) {
                echo "<p class='success'>✅ Added business_name column</p>";
            } else {
                echo "<p class='error'>❌ Failed to add business_name column: " . mysqli_error($link) . "</p>";
                $migration_success = false;
            }
        }
        
        if (!$has_operator_manager) {
            $add_operator_manager = "ALTER TABLE issued_certificates ADD COLUMN operator_manager VARCHAR(255) NULL AFTER business_name";
            if (mysqli_query($link, $add_operator_manager)) {
                echo "<p class='success'>✅ Added operator_manager column</p>";
            } else {
                echo "<p class='error'>❌ Failed to add operator_manager column: " . mysqli_error($link) . "</p>";
                $migration_success = false;
            }
        }
        
        if ($migration_success) {
            echo "<h3 class='success'>🎉 Migration Completed Successfully!</h3>";
        } else {
            echo "<h3 class='error'>❌ Migration Failed</h3>";
            echo "<p>Please check the error messages above and fix any issues.</p>";
        }
    }
} else {
    echo "<p class='error'>❌ Could not check table structure: " . mysqli_error($link) . "</p>";
}
echo "</div>";

// Show updated table structure
echo "<div class='migration-section'>";
echo "<h2>📋 Updated Table Structure</h2>";

$updated_structure_query = "DESCRIBE issued_certificates";
$updated_structure_result = mysqli_query($link, $updated_structure_query);

if ($updated_structure_result) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    while ($row = mysqli_fetch_assoc($updated_structure_result)) {
        $highlight = ($row['Field'] === 'business_name' || $row['Field'] === 'operator_manager') ? 'style="background-color: #ffffcc;"' : '';
        echo "<tr $highlight>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

// Test the business certificate workflow
echo "<div class='migration-section'>";
echo "<h2>🧪 Testing Instructions</h2>";
echo "<ol>";
echo "<li><strong>Navigate to:</strong> <a href='issue_certificate_form.php' target='_blank'>Issue Certificate Form</a></li>";
echo "<li><strong>Select Certificate Type:</strong> Choose 'Business Clearance'</li>";
echo "<li><strong>Verify Business Fields:</strong> Check that 'Business Name' and 'Operator/Manager' fields appear</li>";
echo "<li><strong>Select Resident:</strong> Choose a resident (should auto-populate Operator/Manager field)</li>";
echo "<li><strong>Fill Business Name:</strong> Enter a business name or trade activity</li>";
echo "<li><strong>Submit Form:</strong> Complete and submit the certificate</li>";
echo "<li><strong>Check Certificate:</strong> Verify business information appears on the generated certificate</li>";
echo "</ol>";

echo "<h3>🎯 Expected Results:</h3>";
echo "<ul>";
echo "<li>✅ Business fields appear when Business Clearance is selected</li>";
echo "<li>✅ Operator/Manager field auto-populates with selected resident name</li>";
echo "<li>✅ Form validation requires both business fields when visible</li>";
echo "<li>✅ Certificate displays business name and operator/manager information</li>";
echo "<li>✅ Certificate has standardized dual signature layout (Secretary + Official)</li>";
echo "</ul>";
echo "</div>";

echo "<div class='migration-section'>";
echo "<h2>📝 SQL Commands Used</h2>";
echo "<pre>";
echo "-- Add business_name column\n";
echo "ALTER TABLE issued_certificates ADD COLUMN business_name VARCHAR(255) NULL AFTER purpose;\n\n";
echo "-- Add operator_manager column\n";
echo "ALTER TABLE issued_certificates ADD COLUMN operator_manager VARCHAR(255) NULL AFTER business_name;\n";
echo "</pre>";
echo "</div>";

mysqli_close($link);
echo "</body></html>";
?>
