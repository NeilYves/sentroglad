<?php
// Comprehensive test script for Business Clearance functionality
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Business Clearance Testing</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    .code-block { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; font-family: monospace; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .highlight { background-color: #ffffcc; }
</style></head><body>";

echo "<h1>🏢 Business Clearance Functionality Test</h1>";
echo "<p>This script tests all aspects of the Business Clearance certificate functionality.</p>";

// Test 1: Database Structure
echo "<div class='test-section'>";
echo "<h2>📋 Test 1: Database Structure</h2>";

$structure_query = "DESCRIBE issued_certificates";
$structure_result = mysqli_query($link, $structure_query);

if ($structure_result) {
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Status</th></tr>";
    
    $has_business_name = false;
    $has_operator_manager = false;
    
    while ($row = mysqli_fetch_assoc($structure_result)) {
        $status = '';
        $class = '';
        
        if ($row['Field'] === 'business_name') {
            $has_business_name = true;
            $status = 'Business Field';
            $class = 'highlight';
        } elseif ($row['Field'] === 'operator_manager') {
            $has_operator_manager = true;
            $status = 'Business Field';
            $class = 'highlight';
        } elseif (in_array($row['Field'], ['resident_id', 'certificate_type_id', 'issuing_official_id', 'purpose'])) {
            $status = 'Core Field';
        }
        
        echo "<tr class='$class'>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($has_business_name && $has_operator_manager) {
        echo "<p class='success'>✅ Database structure is ready for business certificates</p>";
    } else {
        echo "<p class='error'>❌ Missing business fields in database</p>";
        echo "<p><a href='add_business_fields_migration.php' target='_blank'>Run Migration Script</a></p>";
    }
} else {
    echo "<p class='error'>❌ Could not check database structure</p>";
}
echo "</div>";

// Test 2: Certificate Types
echo "<div class='test-section'>";
echo "<h2>🎯 Test 2: Certificate Types</h2>";

$cert_types_query = "SELECT id, name FROM certificate_types WHERE is_active = 1 ORDER BY name";
$cert_types_result = mysqli_query($link, $cert_types_query);

if ($cert_types_result) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Certificate Type</th><th>Business Related</th></tr>";
    
    $has_business_cert = false;
    
    while ($row = mysqli_fetch_assoc($cert_types_result)) {
        $is_business = stripos($row['name'], 'business') !== false;
        if ($is_business) {
            $has_business_cert = true;
        }
        
        $class = $is_business ? 'highlight' : '';
        $business_status = $is_business ? '✅ Yes' : 'No';
        
        echo "<tr class='$class'>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>$business_status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($has_business_cert) {
        echo "<p class='success'>✅ Business certificate type found</p>";
    } else {
        echo "<p class='warning'>⚠ No business certificate type found</p>";
    }
} else {
    echo "<p class='error'>❌ Could not fetch certificate types</p>";
}
echo "</div>";

// Test 3: Template Structure
echo "<div class='test-section'>";
echo "<h2>📄 Test 3: Business Clearance Template</h2>";

$template_path = 'templates/template_business_clearance.php';
if (file_exists($template_path)) {
    $template_content = file_get_contents($template_path);
    
    $template_features = [
        'signature-container' => 'Standardized signature container',
        'secretary_fullname' => 'Secretary name variable',
        'issuing_official_fullname' => 'Official name variable',
        'business_name' => 'Business name field',
        'operator_manager' => 'Operator/Manager field',
        'Prepared by:' => 'Secretary signature label',
        'Certified by:' => 'Official signature label'
    ];
    
    echo "<table>";
    echo "<tr><th>Feature</th><th>Description</th><th>Status</th></tr>";
    
    $all_features_present = true;
    
    foreach ($template_features as $feature => $description) {
        $found = strpos($template_content, $feature) !== false;
        $status = $found ? "<span class='success'>✓ Present</span>" : "<span class='error'>✗ Missing</span>";
        
        if (!$found) {
            $all_features_present = false;
        }
        
        echo "<tr>";
        echo "<td><code>$feature</code></td>";
        echo "<td>$description</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($all_features_present) {
        echo "<p class='success'>✅ Template has all required features</p>";
    } else {
        echo "<p class='error'>❌ Template is missing some features</p>";
    }
} else {
    echo "<p class='error'>❌ Business clearance template not found</p>";
}
echo "</div>";

// Test 4: Form JavaScript Features
echo "<div class='test-section'>";
echo "<h2>🖥️ Test 4: Form JavaScript Features</h2>";

$form_path = 'issue_certificate_form.php';
if (file_exists($form_path)) {
    $form_content = file_get_contents($form_path);
    
    $js_features = [
        'business-fields' => 'Business fields container',
        'business_name' => 'Business name field',
        'operator_manager' => 'Operator/Manager field',
        'businessFields.style.display' => 'Dynamic field visibility',
        'businessNameField.required' => 'Dynamic field validation',
        'selectedData.text' => 'Auto-population logic'
    ];
    
    echo "<table>";
    echo "<tr><th>Feature</th><th>Description</th><th>Status</th></tr>";
    
    foreach ($js_features as $feature => $description) {
        $found = strpos($form_content, $feature) !== false;
        $status = $found ? "<span class='success'>✓ Present</span>" : "<span class='error'>✗ Missing</span>";
        
        echo "<tr>";
        echo "<td><code>$feature</code></td>";
        echo "<td>$description</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ Issue certificate form not found</p>";
}
echo "</div>";

// Test 5: Recent Business Certificates
echo "<div class='test-section'>";
echo "<h2>📊 Test 5: Recent Business Certificates</h2>";

$recent_business_query = "
    SELECT ic.id, ic.control_number, ic.business_name, ic.operator_manager, 
           r.first_name, r.last_name, ct.name as cert_type, ic.created_at
    FROM issued_certificates ic
    LEFT JOIN residents r ON ic.resident_id = r.id
    LEFT JOIN certificate_types ct ON ic.certificate_type_id = ct.id
    WHERE ic.business_name IS NOT NULL OR ic.operator_manager IS NOT NULL
    ORDER BY ic.created_at DESC
    LIMIT 10
";

$recent_result = mysqli_query($link, $recent_business_query);

if ($recent_result && mysqli_num_rows($recent_result) > 0) {
    echo "<table>";
    echo "<tr><th>Control #</th><th>Business Name</th><th>Operator/Manager</th><th>Resident</th><th>Date</th></tr>";
    
    while ($row = mysqli_fetch_assoc($recent_result)) {
        echo "<tr>";
        echo "<td>{$row['control_number']}</td>";
        echo "<td>" . ($row['business_name'] ?: 'N/A') . "</td>";
        echo "<td>" . ($row['operator_manager'] ?: 'N/A') . "</td>";
        echo "<td>{$row['first_name']} {$row['last_name']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p class='success'>✅ Found " . mysqli_num_rows($recent_result) . " business certificates</p>";
} else {
    echo "<p class='info'>ℹ No business certificates found yet</p>";
}
echo "</div>";

// Testing Instructions
echo "<div class='test-section'>";
echo "<h2>🧪 Manual Testing Instructions</h2>";
echo "<h3>Step-by-Step Test:</h3>";
echo "<ol>";
echo "<li><strong>Open Form:</strong> <a href='issue_certificate_form.php' target='_blank'>Issue Certificate Form</a></li>";
echo "<li><strong>Select Certificate Type:</strong> Choose 'Business Clearance' from dropdown</li>";
echo "<li><strong>Verify Fields Appear:</strong> Business Name and Operator/Manager fields should become visible</li>";
echo "<li><strong>Select Resident:</strong> Choose any resident from the search dropdown</li>";
echo "<li><strong>Check Auto-Population:</strong> Operator/Manager field should auto-fill with resident name</li>";
echo "<li><strong>Enter Business Name:</strong> Type a business name or trade activity</li>";
echo "<li><strong>Select Official:</strong> Choose a signing official (test with Kagawad committee members)</li>";
echo "<li><strong>Submit Form:</strong> Complete and submit the certificate</li>";
echo "<li><strong>Verify Certificate:</strong> Check that business information and dual signatures appear</li>";
echo "</ol>";

echo "<h3>🎯 Expected Results:</h3>";
echo "<ul>";
echo "<li>✅ Business fields appear/hide dynamically based on certificate type</li>";
echo "<li>✅ Operator/Manager auto-populates with selected resident name</li>";
echo "<li>✅ Form validation prevents submission without required business fields</li>";
echo "<li>✅ Certificate displays business name and operator/manager</li>";
echo "<li>✅ Certificate shows both secretary and issuing official signatures</li>";
echo "<li>✅ Selected Kagawad committee members appear correctly on certificate</li>";
echo "</ul>";
echo "</div>";

mysqli_close($link);
echo "</body></html>";
?>
