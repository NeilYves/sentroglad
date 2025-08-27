<?php
// Simple test for certificate form functionality
require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Certificate Form Test</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    .test-link { display: inline-block; margin: 10px; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    .test-link:hover { background-color: #0056b3; color: white; text-decoration: none; }
    pre { background-color: #f5f5f5; padding: 10px; border-radius: 3px; }
</style></head><body>";

echo "<h1>🧪 Certificate Form Test</h1>";

// Test 1: Check if form files exist
echo "<div class='section'>";
echo "<h2>📁 File Existence Check</h2>";

$files_to_check = [
    'issue_certificate_form.php' => 'Certificate Form',
    'certificate_handler.php' => 'Certificate Handler',
    'residents_search.php' => 'Resident Search API'
];

foreach ($files_to_check as $file => $description) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $description ($file) exists</p>";
    } else {
        echo "<p class='error'>❌ $description ($file) missing</p>";
    }
}
echo "</div>";

// Test 2: Database connectivity and structure
echo "<div class='section'>";
echo "<h2>🗄️ Database Check</h2>";

// Check database connection
if ($link) {
    echo "<p class='success'>✅ Database connection successful</p>";
    
    // Check required tables
    $tables = ['residents', 'certificate_types', 'issued_certificates', 'officials'];
    foreach ($tables as $table) {
        $check_query = "SHOW TABLES LIKE '$table'";
        $result = mysqli_query($link, $check_query);
        if (mysqli_num_rows($result) > 0) {
            echo "<p class='success'>✅ Table '$table' exists</p>";
        } else {
            echo "<p class='error'>❌ Table '$table' missing</p>";
        }
    }
    
    // Check for business fields in issued_certificates
    $check_business_fields = "SHOW COLUMNS FROM issued_certificates LIKE 'business_name'";
    $business_result = mysqli_query($link, $check_business_fields);
    if (mysqli_num_rows($business_result) > 0) {
        echo "<p class='success'>✅ Business fields exist in database</p>";
    } else {
        echo "<p class='warning'>⚠ Business fields missing (will use backward compatibility mode)</p>";
    }
    
} else {
    echo "<p class='error'>❌ Database connection failed</p>";
}
echo "</div>";

// Test 3: Data availability
echo "<div class='section'>";
echo "<h2>📊 Data Availability Check</h2>";

// Check residents
$residents_query = "SELECT COUNT(*) as count FROM residents WHERE status = 'Active'";
$residents_result = mysqli_query($link, $residents_query);
if ($residents_result) {
    $residents_count = mysqli_fetch_assoc($residents_result)['count'];
    if ($residents_count > 0) {
        echo "<p class='success'>✅ $residents_count active residents available</p>";
    } else {
        echo "<p class='error'>❌ No active residents found</p>";
    }
}

// Check certificate types
$cert_types_query = "SELECT COUNT(*) as count FROM certificate_types WHERE is_active = 1";
$cert_types_result = mysqli_query($link, $cert_types_query);
if ($cert_types_result) {
    $cert_types_count = mysqli_fetch_assoc($cert_types_result)['count'];
    if ($cert_types_count > 0) {
        echo "<p class='success'>✅ $cert_types_count active certificate types available</p>";
    } else {
        echo "<p class='error'>❌ No active certificate types found</p>";
    }
}

// Check officials
$officials_query = "SELECT COUNT(*) as count FROM officials WHERE (term_end_date IS NULL OR term_end_date >= CURDATE()) AND position NOT LIKE 'Ex-%'";
$officials_result = mysqli_query($link, $officials_query);
if ($officials_result) {
    $officials_count = mysqli_fetch_assoc($officials_result)['count'];
    if ($officials_count > 0) {
        echo "<p class='success'>✅ $officials_count active officials available</p>";
    } else {
        echo "<p class='error'>❌ No active officials found</p>";
    }
}
echo "</div>";

// Test 4: Form testing instructions
echo "<div class='section'>";
echo "<h2>🧪 Manual Testing Instructions</h2>";

echo "<a href='issue_certificate_form.php' target='_blank' class='test-link'>🔗 Open Certificate Form</a>";

echo "<h3>Testing Steps:</h3>";
echo "<ol>";
echo "<li><strong>Open the form</strong> using the link above</li>";
echo "<li><strong>Open browser console</strong> (F12 → Console tab)</li>";
echo "<li><strong>Test regular certificates:</strong></li>";
echo "<ul>";
echo "<li>Select 'Barangay Clearance' or 'Certificate of Residency'</li>";
echo "<li>Verify the regular resident field appears</li>";
echo "<li>Type at least 2 characters to search for residents</li>";
echo "<li>Select a resident from the dropdown</li>";
echo "<li>Fill in the purpose field</li>";
echo "<li>Select a signing official</li>";
echo "<li>Click 'Issue Certificate & Proceed to Print'</li>";
echo "</ul>";
echo "<li><strong>Expected result:</strong> Should redirect to certificate view without 'Please select residents' error</li>";
echo "</ol>";

echo "<h3>Debug Information to Check:</h3>";
echo "<p>In the browser console, you should see messages like:</p>";
echo "<pre>";
echo "Certificate type changed: [ID] [Name]
Selected certificate type: [Name]
Is business certificate: false
Showing regular certificate fields
jQuery loaded, initializing Select2...
Select2 initialized successfully
Form submission validation started
Validating regular certificate fields
Regular resident field visible: true
Resident select value: [selected_id]
Form validation passed, submitting...";
echo "</pre>";
echo "</div>";

// Test 5: Common issues and solutions
echo "<div class='section'>";
echo "<h2>🔧 Common Issues & Solutions</h2>";

echo "<h3>Issue: 'Please select residents' error</h3>";
echo "<p><strong>Possible causes:</strong></p>";
echo "<ul>";
echo "<li>Select2 not properly initialized</li>";
echo "<li>JavaScript timing issues</li>";
echo "<li>Form validation checking wrong field</li>";
echo "<li>Database connection issues</li>";
echo "</ul>";

echo "<h3>Issue: Database errors</h3>";
echo "<p><strong>Possible causes:</strong></p>";
echo "<ul>";
echo "<li>Missing business_name/operator_manager columns</li>";
echo "<li>Invalid foreign key references</li>";
echo "<li>Missing required data (residents, officials, certificate types)</li>";
echo "</ul>";

echo "<h3>Applied Fixes:</h3>";
echo "<ul>";
echo "<li>✅ Enhanced JavaScript debugging</li>";
echo "<li>✅ Improved Select2 initialization with cleanup</li>";
echo "<li>✅ Better form validation with detailed logging</li>";
echo "<li>✅ Database backward compatibility for missing business fields</li>";
echo "<li>✅ Proper error handling in certificate handler</li>";
echo "</ul>";
echo "</div>";

mysqli_close($link);
echo "</body></html>";
?>
