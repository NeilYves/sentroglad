<?php
// Debug version of certificate handler to see what data is being received
require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Certificate Handler Debug</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .debug { background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin: 10px 0; }
    .error { color: red; font-weight: bold; }
    .success { color: green; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    pre { background-color: #f5f5f5; padding: 10px; border-radius: 3px; }
</style></head><body>";

echo "<h1>🔍 Certificate Handler Debug</h1>";

echo "<div class='debug'>";
echo "<h2>📋 Request Information</h2>";
echo "<p><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p><strong>Action:</strong> " . ($_POST['action'] ?? 'NOT SET') . "</p>";
echo "</div>";

echo "<div class='debug'>";
echo "<h2>📝 POST Data Received</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";
echo "</div>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'issue') {
    
    echo "<div class='debug'>";
    echo "<h2>🔍 Resident ID Analysis</h2>";
    
    // Check resident_id (handle both regular and business certificate cases)
    $resident_id = 0;
    
    echo "<p><strong>Raw resident_id from POST:</strong> ";
    if (isset($_POST['resident_id'])) {
        echo "'" . $_POST['resident_id'] . "' (type: " . gettype($_POST['resident_id']) . ")";
    } else {
        echo "<span class='error'>NOT SET</span>";
    }
    echo "</p>";
    
    echo "<p><strong>Raw business_resident_id from POST:</strong> ";
    if (isset($_POST['business_resident_id'])) {
        echo "'" . $_POST['business_resident_id'] . "' (type: " . gettype($_POST['business_resident_id']) . ")";
    } else {
        echo "NOT SET";
    }
    echo "</p>";
    
    echo "<p><strong>Empty check results:</strong></p>";
    echo "<ul>";
    echo "<li>empty(\$_POST['resident_id']): " . (empty($_POST['resident_id']) ? 'TRUE' : 'FALSE') . "</li>";
    echo "<li>empty(\$_POST['business_resident_id']): " . (empty($_POST['business_resident_id']) ? 'TRUE' : 'FALSE') . "</li>";
    echo "</ul>";
    
    if (!empty($_POST['resident_id'])) {
        $resident_id = (int)$_POST['resident_id'];
        echo "<p class='success'>✅ Using regular resident_id: $resident_id</p>";
    } elseif (!empty($_POST['business_resident_id'])) {
        $resident_id = (int)$_POST['business_resident_id'];
        echo "<p class='success'>✅ Using business_resident_id: $resident_id</p>";
    } else {
        echo "<p class='error'>❌ No resident ID found in either field</p>";
    }
    
    echo "<p><strong>Final resident_id value:</strong> $resident_id</p>";
    
    if ($resident_id <= 0) {
        echo "<p class='error'>❌ VALIDATION FAILED: resident_id is $resident_id (must be > 0)</p>";
        echo "<p class='error'>This would trigger the 'missing_resident_id' error</p>";
    } else {
        echo "<p class='success'>✅ VALIDATION PASSED: resident_id is valid ($resident_id)</p>";
    }
    
    echo "</div>";
    
    // Check other required fields
    echo "<div class='debug'>";
    echo "<h2>📋 Other Required Fields</h2>";
    
    $fields_to_check = [
        'certificate_type_id' => 'Certificate Type ID',
        'issue_date' => 'Issue Date',
        'purpose' => 'Purpose',
        'signing_official_id' => 'Signing Official ID'
    ];
    
    foreach ($fields_to_check as $field => $label) {
        echo "<p><strong>$label:</strong> ";
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            echo "<span class='success'>'" . htmlspecialchars($_POST[$field]) . "'</span>";
        } else {
            echo "<span class='error'>NOT SET or EMPTY</span>";
        }
        echo "</p>";
    }
    
    echo "</div>";
    
    // Check if this would be a business certificate
    echo "<div class='debug'>";
    echo "<h2>🏢 Business Certificate Check</h2>";
    
    $business_name = !empty($_POST['business_name']) ? $_POST['business_name'] : null;
    $operator_manager = !empty($_POST['operator_manager']) ? $_POST['operator_manager'] : null;
    
    echo "<p><strong>Business Name:</strong> " . ($business_name ? "'" . htmlspecialchars($business_name) . "'" : "NOT SET") . "</p>";
    echo "<p><strong>Operator/Manager:</strong> " . ($operator_manager ? "'" . htmlspecialchars($operator_manager) . "'" : "NOT SET") . "</p>";
    
    if ($business_name) {
        echo "<p class='info'>ℹ This appears to be a business certificate</p>";
    } else {
        echo "<p class='info'>ℹ This appears to be a regular certificate</p>";
    }
    
    echo "</div>";
    
} else {
    echo "<div class='debug'>";
    echo "<h2>❌ Invalid Request</h2>";
    echo "<p class='error'>This is not a valid certificate issuance request.</p>";
    echo "<p>Expected: POST request with action='issue'</p>";
    echo "</div>";
}

echo "<div class='debug'>";
echo "<h2>🔗 Actions</h2>";
echo "<p><a href='issue_certificate_form.php'>← Back to Certificate Form</a></p>";
echo "<p><strong>To test:</strong> Fill out the certificate form and submit it. The form should POST to certificate_handler.php, but you can temporarily change the form action to 'debug_certificate_handler.php' to see what data is being sent.</p>";
echo "</div>";

echo "</body></html>";
?>
