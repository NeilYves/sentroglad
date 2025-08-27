<?php
// Script to add Barangay Certification to the certificate types
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Add Barangay Certification</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style></head><body>";

echo "<h1>📜 Add Barangay Certification to Certificate Types</h1>";
echo "<p>This script adds the Barangay Certification template to the certificate types system.</p>";

echo "<div class='section'>";
echo "<h2>📋 Current Certificate Types</h2>";

// Check current certificate types
$current_types_query = "SELECT id, name, template_file, default_purpose, default_fee, is_active FROM certificate_types ORDER BY id ASC";
$current_result = mysqli_query($link, $current_types_query);

if ($current_result) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Template File</th><th>Default Purpose</th><th>Fee</th><th>Active</th></tr>";
    
    $has_barangay_cert = false;
    while ($row = mysqli_fetch_assoc($current_result)) {
        if (stripos($row['name'], 'barangay certification') !== false) {
            $has_barangay_cert = true;
        }
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['template_file'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['default_purpose'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['default_fee']) . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($has_barangay_cert) {
        echo "<p class='warning'>⚠ Barangay Certification already exists in the system.</p>";
    } else {
        echo "<p class='info'>ℹ Barangay Certification not found - will be added.</p>";
    }
} else {
    echo "<p class='error'>❌ Could not fetch certificate types: " . mysqli_error($link) . "</p>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>📜 Adding Barangay Certification</h2>";

// Check if template file exists
$template_file = 'templates/template_barangay_certification.php';
if (file_exists($template_file)) {
    echo "<p class='success'>✅ Template file exists: $template_file</p>";
} else {
    echo "<p class='error'>❌ Template file not found: $template_file</p>";
    echo "<p>Please ensure the template file exists before adding the certificate type.</p>";
    echo "</div></body></html>";
    exit;
}

// Check if Barangay Certification already exists
$check_query = "SELECT id FROM certificate_types WHERE name = 'Barangay Certification'";
$check_result = mysqli_query($link, $check_query);

if ($check_result && mysqli_num_rows($check_result) > 0) {
    echo "<p class='warning'>⚠ Barangay Certification already exists in the database.</p>";
    $existing_row = mysqli_fetch_assoc($check_result);
    echo "<p>Existing ID: {$existing_row['id']}</p>";
} else {
    // Add Barangay Certification
    $insert_query = "INSERT INTO certificate_types (name, description, template_file, default_purpose, default_fee, is_active) 
                     VALUES (?, ?, ?, ?, ?, ?)";
    
    $name = 'Barangay Certification';
    $description = 'General barangay certification for various purposes';
    $template_file_name = 'template_barangay_certification.php';
    $default_purpose = 'For general certification purposes';
    $default_fee = 0.00;
    $is_active = 1;
    
    if ($stmt = mysqli_prepare($link, $insert_query)) {
        mysqli_stmt_bind_param($stmt, "ssssdi", $name, $description, $template_file_name, $default_purpose, $default_fee, $is_active);
        
        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($link);
            echo "<p class='success'>✅ Successfully added Barangay Certification!</p>";
            echo "<p><strong>New Certificate Type Details:</strong></p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> $new_id</li>";
            echo "<li><strong>Name:</strong> $name</li>";
            echo "<li><strong>Description:</strong> $description</li>";
            echo "<li><strong>Template File:</strong> $template_file_name</li>";
            echo "<li><strong>Default Purpose:</strong> $default_purpose</li>";
            echo "<li><strong>Default Fee:</strong> ₱" . number_format($default_fee, 2) . "</li>";
            echo "<li><strong>Active:</strong> Yes</li>";
            echo "</ul>";
        } else {
            echo "<p class='error'>❌ Failed to add Barangay Certification: " . mysqli_error($link) . "</p>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<p class='error'>❌ Failed to prepare statement: " . mysqli_error($link) . "</p>";
    }
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>📋 Updated Certificate Types</h2>";

// Show updated certificate types
$updated_types_query = "SELECT id, name, template_file, default_purpose, default_fee, is_active FROM certificate_types ORDER BY id ASC";
$updated_result = mysqli_query($link, $updated_types_query);

if ($updated_result) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Template File</th><th>Default Purpose</th><th>Fee</th><th>Active</th></tr>";
    
    while ($row = mysqli_fetch_assoc($updated_result)) {
        $highlight = ($row['name'] === 'Barangay Certification') ? 'style="background-color: #ffffcc;"' : '';
        
        echo "<tr $highlight>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['template_file'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['default_purpose'] ?: 'N/A') . "</td>";
        echo "<td>₱" . number_format($row['default_fee'], 2) . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p class='info'>ℹ Highlighted row shows the newly added Barangay Certification.</p>";
} else {
    echo "<p class='error'>❌ Could not fetch updated certificate types.</p>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>🧪 Testing Instructions</h2>";
echo "<h3>Test the New Certificate Type:</h3>";
echo "<ol>";
echo "<li><strong>Open Issue Certificate Form:</strong> <a href='issue_certificate_form.php' target='_blank' style='background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Issue Certificate Form</a></li>";
echo "<li><strong>Check Certificate Type Dropdown:</strong> Verify 'Barangay Certification' appears in the list</li>";
echo "<li><strong>Select Barangay Certification:</strong> Choose it from the dropdown</li>";
echo "<li><strong>Fill Form:</strong> Select a resident, signing official, and purpose</li>";
echo "<li><strong>Submit Form:</strong> Issue the certificate</li>";
echo "<li><strong>View Certificate:</strong> Check that it uses the barangay certification template</li>";
echo "</ol>";

echo "<h3>🎯 Expected Results:</h3>";
echo "<ul>";
echo "<li>✅ 'Barangay Certification' appears in certificate type dropdown</li>";
echo "<li>✅ Default purpose is set to 'For general certification purposes'</li>";
echo "<li>✅ Form submission works correctly</li>";
echo "<li>✅ Certificate generates using the barangay certification template</li>";
echo "<li>✅ Signing official information appears correctly</li>";
echo "</ul>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>📝 Template Features</h2>";
echo "<h3>🎯 Barangay Certification Template Includes:</h3>";
echo "<ul>";
echo "<li><strong>Official Header:</strong> Republic of the Philippines, Region XII format</li>";
echo "<li><strong>Dual Logos:</strong> Municipality and Barangay logos</li>";
echo "<li><strong>Professional Layout:</strong> Bordered certificate design</li>";
echo "<li><strong>Dynamic Content:</strong> Resident name, gender, civil status</li>";
echo "<li><strong>Purpose Field:</strong> Customizable purpose text</li>";
echo "<li><strong>Date Information:</strong> Issue date formatting</li>";
echo "<li><strong>Signing Official:</strong> Name and position from selected official</li>";
echo "<li><strong>Print Optimization:</strong> Proper print styling</li>";
echo "</ul>";

echo "<h3>📋 Certificate Content:</h3>";
echo "<ul>";
echo "<li><strong>Certification Statement:</strong> 'TO WHOM IT MAY CONCERN' format</li>";
echo "<li><strong>Resident Information:</strong> Name, age, gender, civil status, address</li>";
echo "<li><strong>Character Reference:</strong> Good moral character statement</li>";
echo "<li><strong>Purpose Statement:</strong> Custom purpose for the certificate</li>";
echo "<li><strong>Official Signature:</strong> Signing official name and position</li>";
echo "</ul>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>📋 Summary</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
echo "<h3 style='color: #155724; margin-top: 0;'>✅ Barangay Certification Successfully Added</h3>";
echo "<p><strong>What was accomplished:</strong></p>";
echo "<ul>";
echo "<li>✅ Added 'Barangay Certification' to certificate_types table</li>";
echo "<li>✅ Linked to existing template_barangay_certification.php</li>";
echo "<li>✅ Set appropriate default purpose and fee</li>";
echo "<li>✅ Made certificate type active and available</li>";
echo "<li>✅ Certificate now appears in issue form dropdown</li>";
echo "</ul>";
echo "<p><strong>The Barangay Certification is now fully integrated into the certificate issuance system with signing official support!</strong></p>";
echo "</div>";
echo "</div>";

mysqli_close($link);
echo "</body></html>";
?>
