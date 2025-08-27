<?php
// Comprehensive fix for certificate issuance issues
require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Certificate Issue Fix</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    .highlight { background-color: #ffffcc; }
    pre { background-color: #f5f5f5; padding: 10px; border-radius: 3px; }
    .fix-button { display: inline-block; margin: 10px; padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; }
    .fix-button:hover { background-color: #218838; color: white; text-decoration: none; }
</style></head><body>";

echo "<h1>🔧 Certificate Issue Comprehensive Fix</h1>";
echo "<p>This script diagnoses and fixes the 'Please select residents' error when issuing certificates.</p>";

// Step 1: Check database structure
echo "<div class='section'>";
echo "<h2>🔍 Step 1: Database Structure Check</h2>";

$structure_query = "DESCRIBE issued_certificates";
$structure_result = mysqli_query($link, $structure_query);

$has_business_name = false;
$has_operator_manager = false;
$database_issue = false;

if ($structure_result) {
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
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
    
    if (!$has_business_name || !$has_operator_manager) {
        $database_issue = true;
        echo "<p class='error'>❌ CRITICAL ISSUE: Missing required database columns!</p>";
        echo "<p class='error'>The certificate handler expects business_name and operator_manager columns but they don't exist.</p>";
        
        if (isset($_GET['fix_database']) && $_GET['fix_database'] === '1') {
            echo "<h3>🔧 Fixing Database Structure...</h3>";
            
            $fix_success = true;
            
            if (!$has_business_name) {
                $add_business_name = "ALTER TABLE issued_certificates ADD COLUMN business_name VARCHAR(255) NULL AFTER purpose";
                if (mysqli_query($link, $add_business_name)) {
                    echo "<p class='success'>✅ Added business_name column</p>";
                } else {
                    echo "<p class='error'>❌ Failed to add business_name column: " . mysqli_error($link) . "</p>";
                    $fix_success = false;
                }
            }
            
            if (!$has_operator_manager) {
                $add_operator_manager = "ALTER TABLE issued_certificates ADD COLUMN operator_manager VARCHAR(255) NULL AFTER business_name";
                if (mysqli_query($link, $add_operator_manager)) {
                    echo "<p class='success'>✅ Added operator_manager column</p>";
                } else {
                    echo "<p class='error'>❌ Failed to add operator_manager column: " . mysqli_error($link) . "</p>";
                    $fix_success = false;
                }
            }
            
            if ($fix_success) {
                echo "<p class='success'>🎉 Database structure fixed! Please refresh this page.</p>";
                echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
            }
        } else {
            echo "<a href='?fix_database=1' class='fix-button'>🔧 Fix Database Structure</a>";
        }
    } else {
        echo "<p class='success'>✅ Database structure is correct</p>";
    }
} else {
    echo "<p class='error'>❌ Could not check database structure: " . mysqli_error($link) . "</p>";
    $database_issue = true;
}
echo "</div>";

// Step 2: Check certificate types
echo "<div class='section'>";
echo "<h2>📋 Step 2: Certificate Types Check</h2>";

$cert_types_query = "SELECT id, name, template_file, default_purpose, is_active FROM certificate_types WHERE is_active = 1 ORDER BY name ASC";
$cert_result = mysqli_query($link, $cert_types_query);

if ($cert_result) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Template</th><th>Default Purpose</th></tr>";
    
    $regular_certs = 0;
    $business_certs = 0;
    
    while ($row = mysqli_fetch_assoc($cert_result)) {
        $is_business = stripos($row['name'], 'business') !== false;
        $row_class = $is_business ? 'class="highlight"' : '';
        $type_label = $is_business ? 'Business' : 'Regular';
        
        if ($is_business) {
            $business_certs++;
        } else {
            $regular_certs++;
        }
        
        echo "<tr $row_class>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>$type_label</td>";
        echo "<td>" . htmlspecialchars($row['template_file'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['default_purpose'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p class='info'>📊 Summary: $regular_certs regular certificates, $business_certs business certificates</p>";
    
    if ($regular_certs > 0) {
        echo "<p class='success'>✅ Regular certificates found (these are the ones having issues)</p>";
    }
} else {
    echo "<p class='error'>❌ Could not fetch certificate types</p>";
}
echo "</div>";

// Step 3: Check residents
echo "<div class='section'>";
echo "<h2>👥 Step 3: Residents Check</h2>";

$residents_count_query = "SELECT COUNT(*) as total FROM residents WHERE status = 'Active'";
$residents_count_result = mysqli_query($link, $residents_count_query);

if ($residents_count_result) {
    $count_row = mysqli_fetch_assoc($residents_count_result);
    $active_residents = $count_row['total'];
    
    if ($active_residents > 0) {
        echo "<p class='success'>✅ Found $active_residents active residents</p>";
        
        // Show sample residents
        $sample_query = "SELECT id, CONCAT(last_name, ', ', first_name, ' ', COALESCE(suffix, '')) AS fullname FROM residents WHERE status = 'Active' LIMIT 3";
        $sample_result = mysqli_query($link, $sample_query);
        
        if ($sample_result) {
            echo "<p class='info'>Sample residents:</p>";
            echo "<ul>";
            while ($row = mysqli_fetch_assoc($sample_result)) {
                echo "<li>ID: {$row['id']} - " . htmlspecialchars($row['fullname']) . "</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p class='error'>❌ No active residents found! You need to add residents first.</p>";
    }
} else {
    echo "<p class='error'>❌ Could not check residents</p>";
}
echo "</div>";

// Step 4: Summary and next steps
echo "<div class='section'>";
echo "<h2>📝 Step 4: Issue Summary & Next Steps</h2>";

if ($database_issue) {
    echo "<p class='error'>🚨 <strong>CRITICAL:</strong> Database structure issue must be fixed first!</p>";
    echo "<p>The certificate handler is trying to insert data into columns that don't exist, causing database errors.</p>";
} else {
    echo "<p class='success'>✅ Database structure is correct</p>";
    
    echo "<h3>🧪 Testing Steps:</h3>";
    echo "<ol>";
    echo "<li><strong>Open the certificate form:</strong> <a href='issue_certificate_form.php' target='_blank'>issue_certificate_form.php</a></li>";
    echo "<li><strong>Open browser console:</strong> Press F12 → Console tab</li>";
    echo "<li><strong>Test regular certificates:</strong></li>";
    echo "<ul>";
    echo "<li>Select 'Barangay Clearance'</li>";
    echo "<li>Check that regular resident field appears</li>";
    echo "<li>Search for and select a resident</li>";
    echo "<li>Fill purpose and other fields</li>";
    echo "<li>Submit - should work without 'Please select residents' error</li>";
    echo "</ul>";
    echo "<li><strong>Check console for debug messages</strong></li>";
    echo "</ol>";
    
    echo "<h3>🔧 Applied Fixes:</h3>";
    echo "<ul>";
    echo "<li>✅ Enhanced JavaScript debugging and logging</li>";
    echo "<li>✅ Improved Select2 initialization with proper cleanup</li>";
    echo "<li>✅ Better form validation with detailed error messages</li>";
    echo "<li>✅ Fixed timing issues with jQuery and Select2 loading</li>";
    echo "<li>✅ Ensured proper field visibility management</li>";
    echo "</ul>";
}

echo "</div>";

mysqli_close($link);
echo "</body></html>";
?>
