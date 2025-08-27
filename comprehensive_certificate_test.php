<?php
// Comprehensive test for certificate form issues
require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Comprehensive Certificate Test</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    .test-link { display: inline-block; margin: 10px; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    .test-link:hover { background-color: #0056b3; color: white; text-decoration: none; }
    .debug-link { background-color: #28a745; }
    .debug-link:hover { background-color: #218838; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .highlight { background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; }
</style></head><body>";

echo "<h1>🔍 Comprehensive Certificate Issue Diagnosis</h1>";

// Test 1: Check system requirements
echo "<div class='section'>";
echo "<h2>✅ System Requirements Check</h2>";

$checks = [
    'Database Connection' => $link ? 'OK' : 'FAILED',
    'Residents Table' => mysqli_query($link, "SHOW TABLES LIKE 'residents'") ? 'OK' : 'FAILED',
    'Certificate Types Table' => mysqli_query($link, "SHOW TABLES LIKE 'certificate_types'") ? 'OK' : 'FAILED',
    'Officials Table' => mysqli_query($link, "SHOW TABLES LIKE 'officials'") ? 'OK' : 'FAILED',
    'Issued Certificates Table' => mysqli_query($link, "SHOW TABLES LIKE 'issued_certificates'") ? 'OK' : 'FAILED'
];

foreach ($checks as $check => $status) {
    $class = $status === 'OK' ? 'success' : 'error';
    echo "<p class='$class'>$check: $status</p>";
}
echo "</div>";

// Test 2: Data availability
echo "<div class='section'>";
echo "<h2>📊 Data Availability</h2>";

// Check residents
$residents_count = 0;
$residents_result = mysqli_query($link, "SELECT COUNT(*) as count FROM residents WHERE status = 'Active'");
if ($residents_result) {
    $residents_count = mysqli_fetch_assoc($residents_result)['count'];
}

// Check certificate types
$cert_types_count = 0;
$cert_types_result = mysqli_query($link, "SELECT COUNT(*) as count FROM certificate_types WHERE is_active = 1");
if ($cert_types_result) {
    $cert_types_count = mysqli_fetch_assoc($cert_types_result)['count'];
}

// Check officials
$officials_count = 0;
$officials_result = mysqli_query($link, "SELECT COUNT(*) as count FROM officials WHERE (term_end_date IS NULL OR term_end_date >= CURDATE()) AND position NOT LIKE 'Ex-%'");
if ($officials_result) {
    $officials_count = mysqli_fetch_assoc($officials_result)['count'];
}

echo "<table>";
echo "<tr><th>Data Type</th><th>Count</th><th>Status</th></tr>";
echo "<tr><td>Active Residents</td><td>$residents_count</td><td class='" . ($residents_count > 0 ? 'success' : 'error') . "'>" . ($residents_count > 0 ? 'OK' : 'NO DATA') . "</td></tr>";
echo "<tr><td>Active Certificate Types</td><td>$cert_types_count</td><td class='" . ($cert_types_count > 0 ? 'success' : 'error') . "'>" . ($cert_types_count > 0 ? 'OK' : 'NO DATA') . "</td></tr>";
echo "<tr><td>Active Officials</td><td>$officials_count</td><td class='" . ($officials_count > 0 ? 'success' : 'error') . "'>" . ($officials_count > 0 ? 'OK' : 'NO DATA') . "</td></tr>";
echo "</table>";

if ($residents_count == 0) {
    echo "<p class='error'>❌ <strong>CRITICAL:</strong> No active residents found! You need to add residents first.</p>";
}
if ($cert_types_count == 0) {
    echo "<p class='error'>❌ <strong>CRITICAL:</strong> No active certificate types found!</p>";
}
if ($officials_count == 0) {
    echo "<p class='error'>❌ <strong>CRITICAL:</strong> No active officials found!</p>";
}

echo "</div>";

// Test 3: API endpoints
echo "<div class='section'>";
echo "<h2>🌐 API Endpoints Test</h2>";

echo "<p>Test these endpoints to ensure they're working:</p>";
echo "<ul>";
echo "<li><a href='residents_search.php?term=a' target='_blank'>residents_search.php?term=a</a> - Should return JSON with residents</li>";
echo "<li><a href='test_resident_search.php' target='_blank'>test_resident_search.php</a> - Detailed resident search test</li>";
echo "</ul>";

echo "</div>";

// Test 4: Form testing
echo "<div class='section'>";
echo "<h2>📝 Form Testing</h2>";

echo "<div class='highlight'>";
echo "<h3>🎯 Step-by-Step Testing Process</h3>";
echo "<p><strong>Follow these steps to identify the issue:</strong></p>";
echo "<ol>";
echo "<li><strong>Open the certificate form</strong> (enhanced with debugging)</li>";
echo "<li><strong>Open browser console</strong> (F12 → Console tab)</li>";
echo "<li><strong>Select a certificate type</strong> (e.g., Barangay Clearance)</li>";
echo "<li><strong>Try to search for residents</strong> - type at least 2 characters</li>";
echo "<li><strong>Check console for errors</strong> - look for Select2 or AJAX errors</li>";
echo "<li><strong>Select a resident</strong> - check console for selection logs</li>";
echo "<li><strong>Fill other fields</strong> and submit</li>";
echo "<li><strong>Check console for form data</strong> - verify resident_id is included</li>";
echo "</ol>";
echo "</div>";

echo "<a href='issue_certificate_form.php' target='_blank' class='test-link'>🔗 Open Certificate Form (Enhanced Debug)</a>";
echo "<a href='debug_certificate_handler.php' target='_blank' class='test-link debug-link'>🔍 Debug Handler (Direct)</a>";

echo "<h3>🔍 What to Look For:</h3>";
echo "<ul>";
echo "<li><strong>Console Errors:</strong> Any JavaScript errors that prevent Select2 from working</li>";
echo "<li><strong>AJAX Errors:</strong> Failed requests to residents_search.php</li>";
echo "<li><strong>Select2 Issues:</strong> Dropdown not populating or not setting values</li>";
echo "<li><strong>Form Data Issues:</strong> resident_id not being included in form submission</li>";
echo "</ul>";

echo "</div>";

// Test 5: Common issues and solutions
echo "<div class='section'>";
echo "<h2>🔧 Common Issues & Solutions</h2>";

echo "<h3>Issue 1: No residents found in search</h3>";
echo "<ul>";
echo "<li><strong>Cause:</strong> No active residents in database</li>";
echo "<li><strong>Solution:</strong> Add residents through the resident management system</li>";
echo "</ul>";

echo "<h3>Issue 2: Select2 not working</h3>";
echo "<ul>";
echo "<li><strong>Cause:</strong> JavaScript/jQuery loading issues</li>";
echo "<li><strong>Solution:</strong> Check browser console for errors</li>";
echo "</ul>";

echo "<h3>Issue 3: Form submits but resident_id is empty</h3>";
echo "<ul>";
echo "<li><strong>Cause:</strong> Select2 value not properly set</li>";
echo "<li><strong>Solution:</strong> Check form data in console before submission</li>";
echo "</ul>";

echo "<h3>Issue 4: Certificate handler receives empty resident_id</h3>";
echo "<ul>";
echo "<li><strong>Cause:</strong> Form field name mismatch or validation issue</li>";
echo "<li><strong>Solution:</strong> Use debug handler to see exact POST data</li>";
echo "</ul>";

echo "</div>";

// Test 6: Quick fixes
echo "<div class='section'>";
echo "<h2>⚡ Quick Diagnostic</h2>";

if ($residents_count > 0 && $cert_types_count > 0 && $officials_count > 0) {
    echo "<p class='success'>✅ <strong>Data Check:</strong> All required data is available</p>";
    echo "<p class='info'>The issue is likely in the form JavaScript or form submission process.</p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Open the certificate form and check browser console for errors</li>";
    echo "<li>Try searching for residents - if no results appear, check residents_search.php</li>";
    echo "<li>If residents appear but form still fails, use the debug handler to see POST data</li>";
    echo "</ol>";
    
} else {
    echo "<p class='error'>❌ <strong>Data Issue:</strong> Missing required data</p>";
    echo "<p class='warning'>You need to add the missing data before the certificate form will work.</p>";
    
    if ($residents_count == 0) {
        echo "<p>• Add residents through the resident management system</p>";
    }
    if ($cert_types_count == 0) {
        echo "<p>• Add certificate types to the certificate_types table</p>";
    }
    if ($officials_count == 0) {
        echo "<p>• Add officials to the officials table</p>";
    }
}

echo "</div>";

mysqli_close($link);
echo "</body></html>";
?>
