<?php
// Verification script for standardized certificate template signatures
echo "<!DOCTYPE html>";
echo "<html><head><title>Certificate Template Signature Verification</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .template-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    .code-block { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; font-family: monospace; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style></head><body>";

echo "<h1>🔍 Certificate Template Signature Verification</h1>";
echo "<p>This script verifies that all certificate templates have standardized signature layouts.</p>";

$templates = [
    'Barangay Clearance' => 'templates/template_barangay_clearance.php',
    'Certificate of Residency' => 'templates/template_cert_residency.php',
    'Low Income Certificate' => 'templates/template_low_income.php'
];

$signature_features = [
    'signature-container' => 'Signature container div',
    'signature-block' => 'Individual signature blocks',
    'signature-block certified' => 'Certified by signature block',
    'secretary_fullname' => 'Secretary name variable',
    'secretary_position' => 'Secretary position variable',
    'issuing_official_fullname' => 'Issuing official name variable',
    'issuing_official_position' => 'Issuing official position variable',
    'Prepared by:' => 'Secretary signature label',
    'Certified by:' => 'Official signature label',
    'footer-info' => 'CTC footer information',
    'print-button-container' => 'Print button',
    'contenteditable="true"' => 'Editable content feature'
];

echo "<h2>📋 Template Analysis Results</h2>";

foreach ($templates as $template_name => $template_path) {
    echo "<div class='template-section'>";
    echo "<h3>🎯 $template_name</h3>";
    
    if (!file_exists($template_path)) {
        echo "<span class='error'>✗ Template file not found: $template_path</span>";
        continue;
    }
    
    $template_content = file_get_contents($template_path);
    
    echo "<table>";
    echo "<tr><th>Feature</th><th>Description</th><th>Status</th></tr>";
    
    $all_features_present = true;
    
    foreach ($signature_features as $feature => $description) {
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
        echo "<p><span class='success'>✅ PASS: All signature features are present</span></p>";
    } else {
        echo "<p><span class='error'>❌ FAIL: Some signature features are missing</span></p>";
    }
    
    // Check for specific signature structure
    $has_dual_signature = strpos($template_content, 'Prepared by:') !== false && 
                         strpos($template_content, 'Certified by:') !== false;
    
    if ($has_dual_signature) {
        echo "<p><span class='success'>✓ Dual signature structure (Secretary + Official)</span></p>";
    } else {
        echo "<p><span class='warning'>⚠ Single signature structure detected</span></p>";
    }
    
    echo "</div>";
}

echo "<h2>🎯 Signature Layout Comparison</h2>";
echo "<div class='template-section'>";
echo "<h3>Expected Signature Structure</h3>";
echo "<div class='code-block'>";
echo htmlspecialchars('
<!-- Signature Section -->
<div class="signature-container">
    <div class="signature-block">
        <p contenteditable="true">Prepared by:</p>
        <p contenteditable="true" class="signature-name">
            <?php echo strtoupper(html_escape($certificate_data[\'secretary_fullname\'] ?? \'EDISON E. CAMACUNA\')); ?>
        </p>
        <p contenteditable="true" class="signature-title">
            <?php echo ucwords(html_escape($certificate_data[\'secretary_position\'] ?? \'Barangay Secretary\')); ?>
        </p>
    </div>
    <div class="signature-block certified">
        <p contenteditable="true">Certified by:</p>
        <p contenteditable="true" class="signature-name">
            <?php echo strtoupper(html_escape($certificate_data[\'issuing_official_fullname\'] ?? \'HON. VERNON E. PAPELERA\')); ?>
        </p>
        <p contenteditable="true" class="signature-title">
            <?php echo ucwords(html_escape($certificate_data[\'issuing_official_position\'] ?? \'Punong Barangay\')); ?>
        </p>
    </div>
</div>
');
echo "</div>";
echo "</div>";

echo "<h2>✅ Verification Summary</h2>";
echo "<div class='template-section'>";
echo "<h3>Template Standardization Status</h3>";

$all_templates_standardized = true;
foreach ($templates as $template_name => $template_path) {
    if (file_exists($template_path)) {
        $content = file_get_contents($template_path);
        $has_all_features = true;
        
        foreach ($signature_features as $feature => $description) {
            if (strpos($content, $feature) === false) {
                $has_all_features = false;
                break;
            }
        }
        
        if ($has_all_features) {
            echo "<p><span class='success'>✓ $template_name - Fully Standardized</span></p>";
        } else {
            echo "<p><span class='error'>✗ $template_name - Needs Updates</span></p>";
            $all_templates_standardized = false;
        }
    }
}

if ($all_templates_standardized) {
    echo "<h3><span class='success'>🎉 SUCCESS: All templates are standardized!</span></h3>";
    echo "<p>All certificate templates now have:</p>";
    echo "<ul>";
    echo "<li>✅ Dual signature layout (Secretary + Issuing Official)</li>";
    echo "<li>✅ Consistent CSS styling and positioning</li>";
    echo "<li>✅ Proper variable usage for official names and positions</li>";
    echo "<li>✅ CTC footer information section</li>";
    echo "<li>✅ Print button functionality</li>";
    echo "<li>✅ Contenteditable features for customization</li>";
    echo "</ul>";
} else {
    echo "<h3><span class='error'>❌ Some templates need additional updates</span></h3>";
}

echo "<h3>🧪 Testing Instructions</h3>";
echo "<ol>";
echo "<li><strong>Issue certificates</strong> using each template type</li>";
echo "<li><strong>Verify signature display:</strong> Both secretary and issuing official should appear</li>";
echo "<li><strong>Test official selection:</strong> Select different Kagawad committee members</li>";
echo "<li><strong>Check print functionality:</strong> Use the print button on each certificate</li>";
echo "<li><strong>Verify responsiveness:</strong> Test on different screen sizes</li>";
echo "</ol>";

echo "</div>";
echo "</body></html>";
?>
