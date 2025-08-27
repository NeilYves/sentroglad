<?php
// Debug script to check business certificate data flow
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Business Certificate Debug</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .highlight { background-color: #ffffcc; }
</style></head><body>";

echo "<h1>🔍 Business Certificate Data Flow Debug</h1>";
echo "<p>This script helps debug why business name and operator/manager are not appearing in certificates.</p>";

// Test 1: Check database structure
echo "<div class='debug-section'>";
echo "<h2>📋 Test 1: Database Structure</h2>";

$structure_query = "DESCRIBE issued_certificates";
$structure_result = mysqli_query($link, $structure_query);

if ($structure_result) {
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    
    $has_business_name = false;
    $has_operator_manager = false;
    
    while ($row = mysqli_fetch_assoc($structure_result)) {
        $class = '';
        if ($row['Field'] === 'business_name') {
            $has_business_name = true;
            $class = 'highlight';
        } elseif ($row['Field'] === 'operator_manager') {
            $has_operator_manager = true;
            $class = 'highlight';
        }
        
        echo "<tr class='$class'>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>" . ($row['Default'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($has_business_name && $has_operator_manager) {
        echo "<p class='success'>✅ Business fields exist in database</p>";
    } else {
        echo "<p class='error'>❌ Missing business fields in database</p>";
        if (!$has_business_name) echo "<p class='error'>Missing: business_name</p>";
        if (!$has_operator_manager) echo "<p class='error'>Missing: operator_manager</p>";
        echo "<p><a href='add_business_fields_migration.php'>Run Migration Script</a></p>";
    }
} else {
    echo "<p class='error'>❌ Could not check database structure</p>";
}
echo "</div>";

// Test 2: Check recent business certificates
echo "<div class='debug-section'>";
echo "<h2>📊 Test 2: Recent Business Certificates Data</h2>";

$recent_query = "SELECT id, control_number, business_name, operator_manager, purpose, created_at 
                 FROM issued_certificates 
                 WHERE certificate_type_id IN (SELECT id FROM certificate_types WHERE name LIKE '%business%')
                 ORDER BY created_at DESC 
                 LIMIT 10";

$recent_result = mysqli_query($link, $recent_query);

if ($recent_result) {
    if (mysqli_num_rows($recent_result) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Control #</th><th>Business Name</th><th>Operator/Manager</th><th>Purpose</th><th>Created</th></tr>";
        
        while ($row = mysqli_fetch_assoc($recent_result)) {
            $business_name_status = $row['business_name'] ? $row['business_name'] : '<span class="error">NULL</span>';
            $operator_status = $row['operator_manager'] ? $row['operator_manager'] : '<span class="error">NULL</span>';
            
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['control_number']}</td>";
            echo "<td>$business_name_status</td>";
            echo "<td>$operator_status</td>";
            echo "<td>{$row['purpose']}</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>ℹ No business certificates found yet</p>";
    }
} else {
    echo "<p class='error'>❌ Could not fetch recent certificates: " . mysqli_error($link) . "</p>";
}
echo "</div>";

// Test 3: Simulate certificate data retrieval
echo "<div class='debug-section'>";
echo "<h2>🔍 Test 3: Certificate Data Retrieval Simulation</h2>";

if (isset($_GET['cert_id'])) {
    $cert_id = (int)$_GET['cert_id'];
    
    // Use the same query as view_certificate.php
    $sql = "SELECT
                ic.*,
                r.first_name, r.middle_name, r.last_name, r.suffix, r.civil_status, r.gender,
                p.purok_name,
                ct.name as certificate_type_name, ct.template_file,
                o.fullname as issuing_official_name, o.position as issuing_official_position,
                ic.business_name, ic.operator_manager
            FROM issued_certificates ic
            JOIN residents r ON ic.resident_id = r.id
            JOIN certificate_types ct ON ic.certificate_type_id = ct.id
            LEFT JOIN puroks p ON r.purok_id = p.id
            LEFT JOIN officials o ON ic.issuing_official_id = o.id
            WHERE ic.id = ?";
    
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $cert_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $certificate = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($certificate) {
        echo "<h3>Certificate ID: $cert_id</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th><th>Status</th></tr>";
        
        $fields_to_check = [
            'control_number' => 'Control Number',
            'certificate_type_name' => 'Certificate Type',
            'business_name' => 'Business Name',
            'operator_manager' => 'Operator/Manager',
            'first_name' => 'Resident First Name',
            'purpose' => 'Purpose'
        ];
        
        foreach ($fields_to_check as $field => $label) {
            $value = $certificate[$field] ?? 'NULL';
            $status = '';
            $class = '';
            
            if ($field === 'business_name' || $field === 'operator_manager') {
                if (empty($value) || $value === 'NULL') {
                    $status = '<span class="error">❌ Missing</span>';
                    $class = 'highlight';
                } else {
                    $status = '<span class="success">✅ Present</span>';
                }
            }
            
            echo "<tr class='$class'>";
            echo "<td>$label</td>";
            echo "<td>" . htmlspecialchars($value) . "</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show the certificate_data array that would be passed to template
        echo "<h4>Certificate Data Array (for template):</h4>";
        echo "<pre>";
        $certificate_data = [
            'business_name' => $certificate['business_name'] ?? null,
            'operator_manager' => $certificate['operator_manager'] ?? null,
            'resident_name' => trim($certificate['first_name'] . ' ' . $certificate['last_name']),
            'control_number' => $certificate['control_number'],
            'certificate_type' => $certificate['certificate_type_name']
        ];
        print_r($certificate_data);
        echo "</pre>";
        
    } else {
        echo "<p class='error'>❌ Certificate not found with ID: $cert_id</p>";
    }
} else {
    echo "<p class='info'>ℹ Enter a certificate ID in the URL to test data retrieval</p>";
    echo "<p>Example: <code>debug_business_certificate.php?cert_id=123</code></p>";
    
    // Show available certificate IDs
    $available_query = "SELECT id, control_number, business_name, operator_manager 
                        FROM issued_certificates 
                        ORDER BY created_at DESC LIMIT 5";
    $available_result = mysqli_query($link, $available_query);
    
    if ($available_result && mysqli_num_rows($available_result) > 0) {
        echo "<h4>Available Certificate IDs for testing:</h4>";
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($available_result)) {
            $business_info = $row['business_name'] ? " (Business: {$row['business_name']})" : " (No business data)";
            echo "<li><a href='?cert_id={$row['id']}'>ID {$row['id']} - {$row['control_number']}$business_info</a></li>";
        }
        echo "</ul>";
    }
}
echo "</div>";

// Test 4: Quick fix instructions
echo "<div class='debug-section'>";
echo "<h2>🔧 Test 4: Quick Fix Instructions</h2>";
echo "<h3>If business data is not appearing:</h3>";
echo "<ol>";
echo "<li><strong>Check Database:</strong> Ensure business_name and operator_manager columns exist</li>";
echo "<li><strong>Test New Certificate:</strong> Create a new business certificate to test the fix</li>";
echo "<li><strong>Check Form Submission:</strong> Verify the form is sending business data</li>";
echo "<li><strong>Check Certificate Handler:</strong> Ensure certificate_handler.php is saving business data</li>";
echo "<li><strong>Check View Certificate:</strong> Ensure view_certificate.php is fetching business data</li>";
echo "</ol>";

echo "<h3>🎯 Testing Steps:</h3>";
echo "<ol>";
echo "<li><a href='issue_certificate_form.php' target='_blank'>Go to Issue Certificate Form</a></li>";
echo "<li>Select 'Business Clearance' certificate type</li>";
echo "<li>Fill in all fields including business name and operator/manager</li>";
echo "<li>Submit the form</li>";
echo "<li>Check the generated certificate for business information</li>";
echo "</ol>";
echo "</div>";

mysqli_close($link);
echo "</body></html>";
?>
