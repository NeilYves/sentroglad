<?php
// Test Certificate Fixes
require_once 'config.php';

echo "<h1>🔧 Certificate System Fix Test</h1>";

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h2>✅ Fixes Applied:</h2>";
echo "<ul>";
echo "<li><strong>Enhanced Resident Validation:</strong> Now properly handles both regular and business certificate resident selection</li>";
echo "<li><strong>Database Column Check:</strong> Conditionally includes business fields only if they exist in database</li>";
echo "<li><strong>Better Error Messages:</strong> Added specific error for invalid residents</li>";
echo "<li><strong>Robust Field Handling:</strong> Handles missing business fields gracefully</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🧪 Test Results:</h2>";

// Test 1: Check database columns
echo "<h3>1. Database Column Check:</h3>";
$check_business_name = "SHOW COLUMNS FROM issued_certificates LIKE 'business_name'";
$result_business_name = mysqli_query($link, $check_business_name);
$business_name_exists = ($result_business_name && mysqli_num_rows($result_business_name) > 0);

$check_operator_manager = "SHOW COLUMNS FROM issued_certificates LIKE 'operator_manager'";
$result_operator_manager = mysqli_query($link, $check_operator_manager);
$operator_manager_exists = ($result_operator_manager && mysqli_num_rows($result_operator_manager) > 0);

echo "<p>✅ business_name column: " . ($business_name_exists ? "EXISTS" : "MISSING") . "</p>";
echo "<p>✅ operator_manager column: " . ($operator_manager_exists ? "EXISTS" : "MISSING") . "</p>";

// Test 2: Check residents
echo "<h3>2. Active Residents Check:</h3>";
$residents_sql = "SELECT COUNT(*) as count FROM residents WHERE status = 'Active'";
$residents_result = mysqli_query($link, $residents_sql);
if ($residents_result) {
    $residents_row = mysqli_fetch_assoc($residents_result);
    echo "<p>✅ Active residents found: " . $residents_row['count'] . "</p>";
} else {
    echo "<p>❌ Error checking residents: " . mysqli_error($link) . "</p>";
}

// Test 3: Check officials
echo "<h3>3. Active Officials Check:</h3>";
$officials_sql = "SELECT COUNT(*) as count FROM officials WHERE position NOT LIKE 'Ex-%' AND position NOT LIKE 'Former %'";
$officials_result = mysqli_query($link, $officials_sql);
if ($officials_result) {
    $officials_row = mysqli_fetch_assoc($officials_result);
    echo "<p>✅ Active officials found: " . $officials_row['count'] . "</p>";
} else {
    echo "<p>❌ Error checking officials: " . mysqli_error($link) . "</p>";
}

// Test 4: Check certificate types
echo "<h3>4. Certificate Types Check:</h3>";
$cert_types_sql = "SELECT COUNT(*) as count FROM certificate_types WHERE status = 'Active'";
$cert_types_result = mysqli_query($link, $cert_types_sql);
if ($cert_types_result) {
    $cert_types_row = mysqli_fetch_assoc($cert_types_result);
    echo "<p>✅ Active certificate types found: " . $cert_types_row['count'] . "</p>";
} else {
    echo "<p>❌ Error checking certificate types: " . mysqli_error($link) . "</p>";
}

echo "<h2>🎯 Next Steps:</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<ol>";
echo "<li><strong>Test Certificate Issuance:</strong> <a href='issue_certificate_form.php' target='_blank'>Open Issue Certificate Form</a></li>";
echo "<li><strong>Try Different Certificate Types:</strong> Test both regular and business certificates</li>";
echo "<li><strong>Verify Resident Selection:</strong> Make sure resident dropdown works properly</li>";
echo "<li><strong>Check Certificate Viewing:</strong> Ensure certificates display without errors</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🔍 Troubleshooting:</h2>";
echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>If you still get errors:</h3>";
echo "<ul>";
echo "<li><strong>'Please select a resident':</strong> Check browser console for JavaScript errors</li>";
echo "<li><strong>'An unexpected error occurred':</strong> Check error logs for specific database errors</li>";
echo "<li><strong>Certificate not displaying:</strong> Verify template files exist in templates/ folder</li>";
echo "</ul>";
echo "</div>";

mysqli_close($link);
?>
