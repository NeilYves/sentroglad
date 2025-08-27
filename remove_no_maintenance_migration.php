<?php
// Database migration script to remove no_maintenance column from residents table
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Remove No Maintenance Field Migration</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .migration-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style></head><body>";

echo "<h1>🗑️ Remove No Maintenance Field Migration</h1>";
echo "<p>This script removes the no_maintenance column from the residents table and reorganizes the form structure.</p>";

// Check current table structure
echo "<div class='migration-section'>";
echo "<h2>📋 Current Table Structure</h2>";

$structure_query = "DESCRIBE residents";
$structure_result = mysqli_query($link, $structure_query);

if ($structure_result) {
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Status</th></tr>";
    
    $has_no_maintenance = false;
    $has_maintenance_medicine = false;
    
    while ($row = mysqli_fetch_assoc($structure_result)) {
        $status = '';
        $class = '';
        
        if ($row['Field'] === 'no_maintenance') {
            $has_no_maintenance = true;
            $status = 'TO BE REMOVED';
            $class = 'style="background-color: #ffebee;"';
        } elseif ($row['Field'] === 'maintenance_medicine') {
            $has_maintenance_medicine = true;
            $status = 'KEPT (MOVED TO NEW POSITION)';
            $class = 'style="background-color: #e8f5e8;"';
        }
        
        echo "<tr $class>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>" . ($row['Default'] ?: 'NULL') . "</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Migration Status:</h3>";
    if (!$has_no_maintenance) {
        echo "<p class='success'>✅ no_maintenance column already removed!</p>";
        echo "<p>No migration needed.</p>";
    } else {
        echo "<p class='warning'>⚠ no_maintenance column found - will be removed</p>";
        
        if ($has_maintenance_medicine) {
            echo "<p class='success'>✅ maintenance_medicine column exists - will be kept</p>";
        } else {
            echo "<p class='error'>❌ maintenance_medicine column missing - this may cause issues</p>";
        }
        
        // Perform migration
        echo "<h3>🔧 Performing Migration...</h3>";
        
        // Check if there's any data in no_maintenance column
        $data_check_query = "SELECT COUNT(*) as total, 
                            SUM(CASE WHEN no_maintenance = 'Yes' THEN 1 ELSE 0 END) as no_maintenance_yes,
                            SUM(CASE WHEN no_maintenance = 'No' THEN 1 ELSE 0 END) as no_maintenance_no
                            FROM residents";
        $data_result = mysqli_query($link, $data_check_query);
        
        if ($data_result) {
            $data_stats = mysqli_fetch_assoc($data_result);
            echo "<h4>📊 Data Analysis Before Removal:</h4>";
            echo "<table>";
            echo "<tr><th>Metric</th><th>Count</th></tr>";
            echo "<tr><td>Total Residents</td><td>{$data_stats['total']}</td></tr>";
            echo "<tr><td>No Maintenance = 'Yes'</td><td>{$data_stats['no_maintenance_yes']}</td></tr>";
            echo "<tr><td>No Maintenance = 'No'</td><td>{$data_stats['no_maintenance_no']}</td></tr>";
            echo "</table>";
            
            if ($data_stats['no_maintenance_yes'] > 0) {
                echo "<p class='warning'>⚠ {$data_stats['no_maintenance_yes']} residents have 'No Maintenance' = 'Yes'</p>";
                echo "<p class='info'>ℹ These residents will now have empty maintenance_medicine field (which means no maintenance medicine)</p>";
            }
        }
        
        // Remove the column
        $drop_column_sql = "ALTER TABLE residents DROP COLUMN no_maintenance";
        if (mysqli_query($link, $drop_column_sql)) {
            echo "<p class='success'>✅ Successfully removed no_maintenance column</p>";
        } else {
            echo "<p class='error'>❌ Failed to remove no_maintenance column: " . mysqli_error($link) . "</p>";
        }
    }
} else {
    echo "<p class='error'>❌ Could not check table structure: " . mysqli_error($link) . "</p>";
}
echo "</div>";

// Show updated table structure
echo "<div class='migration-section'>";
echo "<h2>📋 Updated Table Structure</h2>";

$updated_structure_query = "DESCRIBE residents";
$updated_structure_result = mysqli_query($link, $updated_structure_query);

if ($updated_structure_result) {
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    while ($row = mysqli_fetch_assoc($updated_structure_result)) {
        $highlight = ($row['Field'] === 'maintenance_medicine') ? 'style="background-color: #e8f5e8;"' : '';
        echo "<tr $highlight>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>" . ($row['Default'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

// Summary of changes
echo "<div class='migration-section'>";
echo "<h2>📝 Summary of Changes</h2>";
echo "<h3>✅ Form Structure Changes:</h3>";
echo "<ul>";
echo "<li><strong>Removed:</strong> 'No Maintenance Medicine' field from Family Planning section</li>";
echo "<li><strong>Moved:</strong> 'Maintenance Medicine' field to Family Planning section (replacing No Maintenance)</li>";
echo "<li><strong>Enhanced:</strong> Added more maintenance medicine options (Heart Disease, Asthma)</li>";
echo "<li><strong>Simplified:</strong> Single field approach - empty = no maintenance, selected = has maintenance</li>";
echo "</ul>";

echo "<h3>✅ Database Changes:</h3>";
echo "<ul>";
echo "<li><strong>Removed:</strong> no_maintenance column from residents table</li>";
echo "<li><strong>Kept:</strong> maintenance_medicine column (now serves both purposes)</li>";
echo "<li><strong>Logic:</strong> Empty maintenance_medicine = no maintenance medicine needed</li>";
echo "</ul>";

echo "<h3>✅ Benefits:</h3>";
echo "<ul>";
echo "<li><strong>Simplified Form:</strong> One field instead of two for maintenance medicine</li>";
echo "<li><strong>Better UX:</strong> More intuitive - select condition or leave empty</li>";
echo "<li><strong>Cleaner Data:</strong> Eliminates redundant yes/no field</li>";
echo "<li><strong>More Options:</strong> Added Heart Disease and Asthma to conditions</li>";
echo "</ul>";
echo "</div>";

// Testing instructions
echo "<div class='migration-section'>";
echo "<h2>🧪 Testing Instructions</h2>";
echo "<ol>";
echo "<li><strong>Navigate to:</strong> <a href='resident_form.php?action=add' target='_blank'>Add New Resident</a></li>";
echo "<li><strong>Check Form Layout:</strong> Verify maintenance medicine field is in Family Planning section</li>";
echo "<li><strong>Test Options:</strong> Verify all maintenance medicine options are available</li>";
echo "<li><strong>Test 'Other' Option:</strong> Select 'Other' and verify text input appears</li>";
echo "<li><strong>Test Empty Selection:</strong> Leave maintenance medicine empty (means no maintenance)</li>";
echo "<li><strong>Submit Form:</strong> Verify form submits successfully</li>";
echo "<li><strong>Edit Existing Resident:</strong> Check that existing data displays correctly</li>";
echo "</ol>";

echo "<h3>🎯 Expected Results:</h3>";
echo "<ul>";
echo "<li>✅ Maintenance Medicine field appears in Family Planning section</li>";
echo "<li>✅ Default option is 'No Maintenance Medicine'</li>";
echo "<li>✅ All condition options are available (Hypertension, Diabetes, etc.)</li>";
echo "<li>✅ 'Other' option shows text input for custom conditions</li>";
echo "<li>✅ Form validation works correctly</li>";
echo "<li>✅ Data saves and retrieves properly</li>";
echo "</ul>";
echo "</div>";

echo "<div class='migration-section'>";
echo "<h2>📝 SQL Commands Used</h2>";
echo "<pre>";
echo "-- Remove no_maintenance column\n";
echo "ALTER TABLE residents DROP COLUMN no_maintenance;\n";
echo "</pre>";
echo "</div>";

mysqli_close($link);
echo "</body></html>";
?>
