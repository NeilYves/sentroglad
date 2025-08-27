<?php
// Simulate the exact form submission from issue_certificate_form.php
require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Simulate Form Submission</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    form { margin: 10px 0; }
    input, select, textarea { margin: 5px; padding: 5px; width: 300px; }
    button { padding: 10px 15px; margin: 5px; }
    label { display: inline-block; width: 150px; }
</style></head><body>";

echo "<h1>🎯 Simulate Actual Form Submission</h1>";

// Get available residents and certificate types
$residents_query = "SELECT id, CONCAT(last_name, ', ', first_name, ' ', COALESCE(suffix, '')) AS fullname FROM residents WHERE status = 'Active' ORDER BY last_name ASC LIMIT 10";
$residents_result = mysqli_query($link, $residents_query);

$cert_types_query = "SELECT id, name FROM certificate_types WHERE is_active = 1 ORDER BY name ASC";
$cert_types_result = mysqli_query($link, $cert_types_query);

$officials_query = "SELECT id, fullname, position FROM officials WHERE (term_end_date IS NULL OR term_end_date >= CURDATE()) ORDER BY fullname ASC";
$officials_result = mysqli_query($link, $officials_query);

echo "<div class='section'>";
echo "<h2>📝 Regular Certificate Form</h2>";
echo "<p>This simulates the exact form submission from issue_certificate_form.php</p>";

echo "<form method='POST' action='certificate_handler.php'>";
echo "<input type='hidden' name='action' value='issue'>";

echo "<label>Certificate Type:</label>";
echo "<select name='certificate_type_id' required>";
echo "<option value=''>-- Select Certificate Type --</option>";
if ($cert_types_result) {
    while ($type = mysqli_fetch_assoc($cert_types_result)) {
        echo "<option value='{$type['id']}'>{$type['name']}</option>";
    }
}
echo "</select><br>";

echo "<label>Resident:</label>";
echo "<select name='resident_id' required>";
echo "<option value=''>-- Select Resident --</option>";
if ($residents_result) {
    while ($resident = mysqli_fetch_assoc($residents_result)) {
        echo "<option value='{$resident['id']}'>" . htmlspecialchars($resident['fullname']) . "</option>";
    }
}
echo "</select><br>";

echo "<label>Signing Official:</label>";
echo "<select name='signing_official_id' required>";
echo "<option value=''>-- Select Signing Official --</option>";
if ($officials_result) {
    while ($official = mysqli_fetch_assoc($officials_result)) {
        echo "<option value='{$official['id']}'>" . htmlspecialchars($official['fullname'] . ' (' . $official['position'] . ')') . "</option>";
    }
}
echo "</select><br>";

echo "<label>Purpose:</label>";
echo "<textarea name='purpose' required>For general certification purposes</textarea><br>";

echo "<label>Issue Date:</label>";
echo "<input type='date' name='issue_date' value='" . date('Y-m-d') . "' required><br>";

echo "<button type='submit'>Submit Regular Certificate</button>";
echo "</form>";
echo "</div>";

// Reset result pointers for business form
mysqli_data_seek($residents_result, 0);
mysqli_data_seek($cert_types_result, 0);
mysqli_data_seek($officials_result, 0);

echo "<div class='section'>";
echo "<h2>🏢 Business Certificate Form</h2>";
echo "<p>This simulates a business certificate submission</p>";

echo "<form method='POST' action='certificate_handler.php'>";
echo "<input type='hidden' name='action' value='issue'>";

echo "<label>Certificate Type:</label>";
echo "<select name='certificate_type_id' required>";
echo "<option value=''>-- Select Certificate Type --</option>";
if ($cert_types_result) {
    while ($type = mysqli_fetch_assoc($cert_types_result)) {
        echo "<option value='{$type['id']}'>{$type['name']}</option>";
    }
}
echo "</select><br>";

echo "<label>Business Name:</label>";
echo "<input type='text' name='business_name' value='Test Business' required><br>";

echo "<label>Resident/Operator:</label>";
echo "<select name='business_resident_id' required>";
echo "<option value=''>-- Select Resident/Operator --</option>";
if ($residents_result) {
    while ($resident = mysqli_fetch_assoc($residents_result)) {
        echo "<option value='{$resident['id']}'>" . htmlspecialchars($resident['fullname']) . "</option>";
    }
}
echo "</select><br>";

echo "<input type='hidden' name='operator_manager' value='Test Manager'>";
echo "<input type='hidden' name='resident_id' value=''>"; // This should be populated by JavaScript

echo "<label>Signing Official:</label>";
echo "<select name='signing_official_id' required>";
echo "<option value=''>-- Select Signing Official --</option>";
if ($officials_result) {
    mysqli_data_seek($officials_result, 0);
    while ($official = mysqli_fetch_assoc($officials_result)) {
        echo "<option value='{$official['id']}'>" . htmlspecialchars($official['fullname'] . ' (' . $official['position'] . ')') . "</option>";
    }
}
echo "</select><br>";

echo "<label>Issue Date:</label>";
echo "<input type='date' name='issue_date' value='" . date('Y-m-d') . "' required><br>";

echo "<button type='submit'>Submit Business Certificate</button>";
echo "</form>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🚨 Error Test Cases</h2>";
echo "<p>These will trigger the missing_resident_id error:</p>";

echo "<h3>Empty resident_id</h3>";
echo "<form method='POST' action='certificate_handler.php'>";
echo "<input type='hidden' name='action' value='issue'>";
echo "<input type='hidden' name='certificate_type_id' value='1'>";
echo "<input type='hidden' name='resident_id' value=''>"; // Empty
echo "<input type='hidden' name='purpose' value='Test purpose'>";
echo "<input type='hidden' name='issue_date' value='" . date('Y-m-d') . "'>";
echo "<input type='hidden' name='signing_official_id' value='1'>";
echo "<button type='submit'>Test Empty resident_id</button>";
echo "</form>";

echo "<h3>Missing resident_id field</h3>";
echo "<form method='POST' action='certificate_handler.php'>";
echo "<input type='hidden' name='action' value='issue'>";
echo "<input type='hidden' name='certificate_type_id' value='1'>";
// No resident_id field
echo "<input type='hidden' name='purpose' value='Test purpose'>";
echo "<input type='hidden' name='issue_date' value='" . date('Y-m-d') . "'>";
echo "<input type='hidden' name='signing_official_id' value='1'>";
echo "<button type='submit'>Test Missing resident_id</button>";
echo "</form>";

echo "</div>";

echo "<div class='section'>";
echo "<h2>📋 Instructions</h2>";
echo "<ul>";
echo "<li>Use the regular certificate form to test normal submission</li>";
echo "<li>Use the business certificate form to test business submission</li>";
echo "<li>Use the error test cases to reproduce the missing_resident_id error</li>";
echo "<li>Check the browser's developer console for JavaScript errors</li>";
echo "<li>Check the error_log.log file for server-side debugging information</li>";
echo "</ul>";
echo "</div>";

echo "<script>";
echo "// Simulate the business resident selection logic";
echo "document.addEventListener('DOMContentLoaded', function() {";
echo "    const businessResidentSelect = document.querySelector('select[name=\"business_resident_id\"]');";
echo "    const hiddenResidentId = document.querySelector('input[name=\"resident_id\"]');";
echo "    ";
echo "    if (businessResidentSelect && hiddenResidentId) {";
echo "        businessResidentSelect.addEventListener('change', function() {";
echo "            hiddenResidentId.value = this.value;";
echo "            console.log('Business resident selected:', this.value);";
echo "        });";
echo "    }";
echo "});";
echo "</script>";

echo "</body></html>";
?>
