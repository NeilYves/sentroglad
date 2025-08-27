<?php
// Test script to verify business form data submission
echo "<!DOCTYPE html>";
echo "<html><head><title>Business Form Data Test</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    .form-container { background: #f8f9fa; padding: 20px; border-radius: 5px; }
</style></head><body>";

echo "<h1>🧪 Business Form Data Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div class='test-section'>";
    echo "<h2>📋 Form Data Received</h2>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Value</th><th>Status</th></tr>";
    
    $expected_fields = [
        'resident_id' => 'Resident ID',
        'certificate_type_id' => 'Certificate Type ID',
        'signing_official_id' => 'Signing Official ID',
        'business_name' => 'Business Name',
        'operator_manager' => 'Operator/Manager',
        'purpose' => 'Purpose',
        'issue_date' => 'Issue Date'
    ];
    
    foreach ($expected_fields as $field => $label) {
        $value = $_POST[$field] ?? 'NOT SENT';
        $status = '';
        $class = '';
        
        if ($field === 'business_name' || $field === 'operator_manager') {
            if ($value === 'NOT SENT' || empty($value)) {
                $status = '<span class="error">❌ Missing</span>';
                $class = 'style="background-color: #ffebee;"';
            } else {
                $status = '<span class="success">✅ Present</span>';
                $class = 'style="background-color: #e8f5e8;"';
            }
        } else {
            $status = $value !== 'NOT SENT' ? '<span class="success">✅ Present</span>' : '<span class="error">❌ Missing</span>';
        }
        
        echo "<tr $class>";
        echo "<td>$label</td>";
        echo "<td>" . htmlspecialchars($value) . "</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>🔍 Complete POST Data:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Test if this would work with certificate handler
    if (!empty($_POST['business_name']) && !empty($_POST['operator_manager'])) {
        echo "<p class='success'>✅ Business data is properly submitted and would be processed by certificate handler</p>";
    } else {
        echo "<p class='error'>❌ Business data is missing - certificate handler would save NULL values</p>";
    }
    
    echo "</div>";
} else {
    echo "<div class='test-section'>";
    echo "<h2>🎯 Test Business Form Submission</h2>";
    echo "<p>This form simulates the business certificate submission to test data flow.</p>";
    
    echo "<div class='form-container'>";
    echo "<form method='POST' action=''>";
    echo "<h3>Simulate Business Certificate Form</h3>";
    
    echo "<div style='margin: 10px 0;'>";
    echo "<label>Resident ID:</label><br>";
    echo "<input type='number' name='resident_id' value='1' required>";
    echo "</div>";
    
    echo "<div style='margin: 10px 0;'>";
    echo "<label>Certificate Type ID:</label><br>";
    echo "<input type='number' name='certificate_type_id' value='3' required>";
    echo "</div>";
    
    echo "<div style='margin: 10px 0;'>";
    echo "<label>Signing Official ID:</label><br>";
    echo "<input type='number' name='signing_official_id' value='1' required>";
    echo "</div>";
    
    echo "<div style='margin: 10px 0; background: #ffffcc; padding: 10px; border-radius: 3px;'>";
    echo "<label><strong>Business Name or Trade Activity:</strong></label><br>";
    echo "<input type='text' name='business_name' value='Sample Business Store' style='width: 100%; padding: 5px;' required>";
    echo "</div>";
    
    echo "<div style='margin: 10px 0; background: #ffffcc; padding: 10px; border-radius: 3px;'>";
    echo "<label><strong>Operator/Manager:</strong></label><br>";
    echo "<input type='text' name='operator_manager' value='John Doe' style='width: 100%; padding: 5px;' required>";
    echo "</div>";
    
    echo "<div style='margin: 10px 0;'>";
    echo "<label>Purpose:</label><br>";
    echo "<textarea name='purpose' style='width: 100%; padding: 5px;'>Business permit application</textarea>";
    echo "</div>";
    
    echo "<div style='margin: 10px 0;'>";
    echo "<label>Issue Date:</label><br>";
    echo "<input type='date' name='issue_date' value='" . date('Y-m-d') . "' required>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Form Submission</button>";
    echo "</div>";
    
    echo "</form>";
    echo "</div>";
    echo "</div>";
}

echo "<div class='test-section'>";
echo "<h2>🔧 Troubleshooting Steps</h2>";
echo "<h3>If business data is not appearing in certificates:</h3>";
echo "<ol>";
echo "<li><strong>Test Form Data:</strong> Use the form above to verify data is being sent</li>";
echo "<li><strong>Check Database:</strong> Run <a href='add_business_fields_migration.php'>migration script</a> to ensure columns exist</li>";
echo "<li><strong>Check Certificate Handler:</strong> Look for business data in error logs</li>";
echo "<li><strong>Check View Certificate:</strong> Ensure view_certificate.php fetches business data</li>";
echo "<li><strong>Test Real Form:</strong> <a href='issue_certificate_form.php'>Issue Certificate Form</a></li>";
echo "</ol>";

echo "<h3>🎯 Expected Flow:</h3>";
echo "<ol>";
echo "<li>Form sends business_name and operator_manager fields</li>";
echo "<li>Certificate handler saves them to database</li>";
echo "<li>View certificate fetches them from database</li>";
echo "<li>Template displays them in the certificate</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
