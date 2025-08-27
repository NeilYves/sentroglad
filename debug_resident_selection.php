<?php
// Debug script for resident selection issues
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Debug Resident Selection</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .demo-link { background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
    .demo-link:hover { background: #0056b3; color: white; text-decoration: none; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔍 Debug Resident Selection Issue</h1>";
echo "<p>This script helps diagnose the 'Please select a resident' error.</p>";

echo "<div class='test-section'>";
echo "<h2>📊 Database Status Check</h2>";

// Check residents table
$residents_count_query = "SELECT COUNT(*) as total, 
                         COUNT(CASE WHEN status = 'Active' THEN 1 END) as active
                         FROM residents";
$residents_result = mysqli_query($link, $residents_count_query);

if ($residents_result) {
    $residents_stats = mysqli_fetch_assoc($residents_result);
    echo "<h3>👥 Residents Statistics:</h3>";
    echo "<ul>";
    echo "<li><strong>Total Residents:</strong> {$residents_stats['total']}</li>";
    echo "<li><strong>Active Residents:</strong> {$residents_stats['active']}</li>";
    echo "</ul>";
    
    if ($residents_stats['active'] == 0) {
        echo "<p class='error'>❌ No active residents found! This could cause the selection issue.</p>";
    } else {
        echo "<p class='success'>✅ Active residents available for selection.</p>";
    }
} else {
    echo "<p class='error'>❌ Could not check residents table: " . mysqli_error($link) . "</p>";
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🔍 Sample Residents Data</h2>";

// Show sample residents that should appear in search
$sample_residents_query = "SELECT id, first_name, middle_name, last_name, suffix, status,
                          CONCAT(last_name, ', ', first_name, ' ', COALESCE(suffix, '')) AS display_name
                          FROM residents 
                          WHERE status = 'Active' 
                          ORDER BY last_name ASC, first_name ASC 
                          LIMIT 10";

$sample_result = mysqli_query($link, $sample_residents_query);

if ($sample_result && mysqli_num_rows($sample_result) > 0) {
    echo "<h3>📋 Sample Active Residents (First 10):</h3>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Display Name</th><th>First Name</th><th>Last Name</th><th>Status</th></tr>";
    
    while ($row = mysqli_fetch_assoc($sample_result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['display_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p class='success'>✅ These residents should appear in the search dropdown.</p>";
} else {
    echo "<p class='error'>❌ No active residents found for search dropdown.</p>";
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🧪 Test Residents Search API</h2>";

// Test the residents_search.php endpoint
echo "<h3>🔍 Testing residents_search.php with sample queries:</h3>";

$test_terms = ['a', 'de', 'cruz', 'santos'];
foreach ($test_terms as $term) {
    $search_url = "residents_search.php?term=" . urlencode($term);
    
    // Use cURL to test the endpoint
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/" . $search_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h4>Search term: '$term'</h4>";
    if ($http_code == 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['results'])) {
            echo "<p class='success'>✅ API Response: " . count($data['results']) . " results found</p>";
            if (count($data['results']) > 0) {
                echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
            }
        } else {
            echo "<p class='error'>❌ Invalid JSON response</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    } else {
        echo "<p class='error'>❌ HTTP Error: $http_code</p>";
    }
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>📝 Form Validation Test</h2>";

// Simulate form submission data
echo "<h3>🧪 Testing Form Validation Logic:</h3>";

$test_cases = [
    ['resident_id' => '', 'description' => 'Empty string'],
    ['resident_id' => '0', 'description' => 'Zero value'],
    ['resident_id' => 'abc', 'description' => 'Non-numeric'],
    ['resident_id' => '1', 'description' => 'Valid ID'],
    ['resident_id' => '999999', 'description' => 'Non-existent ID']
];

foreach ($test_cases as $test) {
    $resident_id = !empty($test['resident_id']) ? (int)$test['resident_id'] : 0;
    $is_valid = $resident_id > 0;
    
    echo "<h4>Test Case: {$test['description']} ('{$test['resident_id']}')</h4>";
    echo "<ul>";
    echo "<li><strong>Input:</strong> '{$test['resident_id']}'</li>";
    echo "<li><strong>Converted to int:</strong> $resident_id</li>";
    echo "<li><strong>Validation Result:</strong> " . ($is_valid ? '<span class="success">✅ Valid</span>' : '<span class="error">❌ Invalid</span>') . "</li>";
    echo "</ul>";
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🔧 Common Issues & Solutions</h2>";

echo "<h3>❌ Possible Causes of 'Please select a resident' Error:</h3>";
echo "<ul>";
echo "<li><strong>No Active Residents:</strong> Database has no residents with status = 'Active'</li>";
echo "<li><strong>JavaScript Error:</strong> Select2 not initializing properly</li>";
echo "<li><strong>AJAX Failure:</strong> residents_search.php not returning data</li>";
echo "<li><strong>Form Field Issues:</strong> resident_id field not submitting value</li>";
echo "<li><strong>Validation Logic:</strong> Server-side validation too strict</li>";
echo "</ul>";

echo "<h3>✅ Solutions to Try:</h3>";
echo "<ol>";
echo "<li><strong>Check Browser Console:</strong> Look for JavaScript errors</li>";
echo "<li><strong>Test Search API:</strong> Manually test residents_search.php</li>";
echo "<li><strong>Verify Residents:</strong> Ensure residents exist with status = 'Active'</li>";
echo "<li><strong>Clear Cache:</strong> Clear browser cache and refresh</li>";
echo "<li><strong>Check Network:</strong> Verify AJAX requests are successful</li>";
echo "</ol>";
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>🧪 Interactive Test</h2>";
echo "<h3>Test the Issue Certificate Form:</h3>";

echo "<a href='issue_certificate_form.php' target='_blank' class='demo-link'>📝 Open Issue Certificate Form</a>";

echo "<h3>🎯 Testing Steps:</h3>";
echo "<ol>";
echo "<li><strong>Open Form:</strong> Click the link above</li>";
echo "<li><strong>Check Console:</strong> Open browser developer tools (F12)</li>";
echo "<li><strong>Select Certificate Type:</strong> Choose any certificate type</li>";
echo "<li><strong>Try Resident Search:</strong> Type in the resident search field</li>";
echo "<li><strong>Check Network Tab:</strong> Verify AJAX requests to residents_search.php</li>";
echo "<li><strong>Select Resident:</strong> Choose a resident from dropdown</li>";
echo "<li><strong>Submit Form:</strong> Try to submit and see if error persists</li>";
echo "</ol>";

echo "<h3>🔍 What to Look For:</h3>";
echo "<ul>";
echo "<li><strong>JavaScript Errors:</strong> Any errors in browser console</li>";
echo "<li><strong>AJAX Requests:</strong> Successful calls to residents_search.php</li>";
echo "<li><strong>Dropdown Population:</strong> Residents appearing in search results</li>";
echo "<li><strong>Form Submission:</strong> resident_id value being sent</li>";
echo "</ul>";
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>📋 Summary</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
echo "<h3 style='color: #155724; margin-top: 0;'>✅ Validation Logic Updated</h3>";
echo "<p><strong>Changes Made to Fix the Issue:</strong></p>";
echo "<ul>";
echo "<li>✅ Improved resident_id validation in certificate_handler.php</li>";
echo "<li>✅ Better handling of empty/invalid resident selections</li>";
echo "<li>✅ Enhanced purpose validation for business vs regular certificates</li>";
echo "<li>✅ More specific error messages for debugging</li>";
echo "</ul>";
echo "<p><strong>The 'Please select a resident' error should now be resolved with better validation logic!</strong></p>";
echo "</div>";
echo "</div>";

mysqli_close($link);
echo "</body></html>";
?>
