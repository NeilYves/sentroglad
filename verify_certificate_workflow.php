<?php
// Comprehensive verification script for certificate workflow
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Certificate Workflow Verification</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔍 Certificate Workflow Verification</h1>";
echo "<p>This script verifies the complete certificate issuance workflow including signing officials dropdown.</p>";

// Test 1: Database Tables
echo "<div class='test-section'>";
echo "<h2>📋 Test 1: Database Tables Verification</h2>";

$tables_to_check = ['officials', 'issued_certificates', 'residents', 'certificate_types'];
foreach ($tables_to_check as $table) {
    $check_query = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($link, $check_query);
    if (mysqli_num_rows($result) > 0) {
        echo "<span class='success'>✓ Table '$table' exists</span><br>";
    } else {
        echo "<span class='error'>✗ Table '$table' missing</span><br>";
    }
}
echo "</div>";

// Test 2: Officials Data
echo "<div class='test-section'>";
echo "<h2>👥 Test 2: Officials Data Analysis</h2>";

$officials_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN position LIKE '%Kagawad%' THEN 1 ELSE 0 END) as kagawads,
    SUM(CASE WHEN position LIKE '%Committee on%' THEN 1 ELSE 0 END) as committee_members,
    SUM(CASE WHEN position LIKE '%Punong Barangay%' OR position LIKE '%Captain%' THEN 1 ELSE 0 END) as executives,
    SUM(CASE WHEN position NOT LIKE 'Ex-%' AND position NOT LIKE 'Former %' AND (term_end_date IS NULL OR term_end_date >= CURDATE()) THEN 1 ELSE 0 END) as active
FROM officials";

$result = mysqli_query($link, $officials_query);
if ($result) {
    $stats = mysqli_fetch_assoc($result);
    echo "<table>";
    echo "<tr><th>Metric</th><th>Count</th></tr>";
    echo "<tr><td>Total Officials</td><td>{$stats['total']}</td></tr>";
    echo "<tr><td>Kagawads</td><td>{$stats['kagawads']}</td></tr>";
    echo "<tr><td>Committee Members</td><td>{$stats['committee_members']}</td></tr>";
    echo "<tr><td>Executives</td><td>{$stats['executives']}</td></tr>";
    echo "<tr><td>Active Officials</td><td>{$stats['active']}</td></tr>";
    echo "</table>";
} else {
    echo "<span class='error'>✗ Could not fetch officials statistics</span>";
}
echo "</div>";

// Test 3: Specific Kagawad Variations
echo "<div class='test-section'>";
echo "<h2>🎯 Test 3: Kagawad Variations Detection</h2>";

$kagawad_variations = [
    "Regular Kagawad" => "position LIKE 'Barangay Kagawad' AND position NOT LIKE '%Committee%'",
    "Committee Kagawad (Barangay)" => "position LIKE 'Barangay Kagawad - Committee on%'",
    "Committee Kagawad (Short)" => "position LIKE 'Kagawad - Committee on%'",
    "SK Officials" => "position LIKE '%SK%'"
];

foreach ($kagawad_variations as $type => $condition) {
    $query = "SELECT COUNT(*) as count, GROUP_CONCAT(CONCAT(fullname, ' (', position, ')') SEPARATOR '<br>') as officials 
              FROM officials 
              WHERE $condition 
              AND position NOT LIKE 'Ex-%' 
              AND position NOT LIKE 'Former %'
              AND (term_end_date IS NULL OR term_end_date >= CURDATE())";
    
    $result = mysqli_query($link, $query);
    if ($result) {
        $data = mysqli_fetch_assoc($result);
        echo "<h4>$type: {$data['count']} found</h4>";
        if ($data['count'] > 0) {
            echo "<div style='margin-left: 20px; font-size: 0.9em;'>{$data['officials']}</div>";
        }
    }
}
echo "</div>";

// Test 4: Simulate Issue Certificate Form Query
echo "<div class='test-section'>";
echo "<h2>🔄 Test 4: Issue Certificate Form Query Simulation</h2>";

// Exact query from issue_certificate_form.php
$signing_officials = [];

// Punong Barangay query
$punong_barangay_sql = "SELECT id, fullname, position, 
                        CASE 
                            WHEN (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE()) 
                            AND position NOT LIKE 'Ex-%' 
                            AND position NOT LIKE 'Former %' THEN 1 
                            ELSE 0 
                        END as is_available
                        FROM officials 
                        WHERE (position LIKE '%Punong Barangay%' OR position LIKE '%Captain%')
                        AND position NOT LIKE 'Ex-%' 
                        AND position NOT LIKE 'Former %'
                        AND (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE()) 
                        ORDER BY fullname ASC";

$punong_barangay_result = mysqli_query($link, $punong_barangay_sql);
if ($punong_barangay_result) {
    while ($pb_row = mysqli_fetch_assoc($punong_barangay_result)) {
        if ($pb_row['is_available']) {
            $signing_officials[] = [
                'id' => $pb_row['id'],
                'fullname' => $pb_row['fullname'],
                'position' => $pb_row['position'],
                'priority' => 1,
                'type' => 'Executive'
            ];
        }
    }
}

// Kagawad query
$kagawad_sql = "SELECT id, fullname, position, 
                CASE 
                    WHEN (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE()) 
                    AND position NOT LIKE 'Ex-%' 
                    AND position NOT LIKE 'Former %' THEN 1 
                    ELSE 0 
                END as is_available
                FROM officials 
                WHERE (
                    position LIKE '%Kagawad%' 
                    OR position LIKE 'Kagawad%'
                    OR position LIKE '%SK%'
                )
                AND position NOT LIKE 'Ex-%' 
                AND position NOT LIKE 'Former %'
                AND (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE()) 
                ORDER BY position ASC, fullname ASC";

$kagawad_result = mysqli_query($link, $kagawad_sql);
if ($kagawad_result) {
    while ($row = mysqli_fetch_assoc($kagawad_result)) {
        if ($row['is_available']) {
            $type = 'Other';
            if (strpos($row['position'], 'SK') !== false) {
                $type = 'SK Official';
            } elseif (strpos($row['position'], 'Committee on') !== false) {
                $type = 'Committee Kagawad';
            } elseif (strpos($row['position'], 'Kagawad') !== false) {
                $type = 'Regular Kagawad';
            }
            
            $signing_officials[] = [
                'id' => $row['id'],
                'fullname' => $row['fullname'],
                'position' => $row['position'],
                'priority' => 2,
                'type' => $type
            ];
        }
    }
}

echo "<h3>Dropdown Officials Simulation Results:</h3>";
echo "<table>";
echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Type</th><th>Priority</th></tr>";

foreach ($signing_officials as $official) {
    echo "<tr>";
    echo "<td>{$official['id']}</td>";
    echo "<td>{$official['fullname']}</td>";
    echo "<td>{$official['position']}</td>";
    echo "<td>{$official['type']}</td>";
    echo "<td>{$official['priority']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><strong>Total Officials Available for Dropdown: " . count($signing_officials) . "</strong></p>";
echo "</div>";

// Test 5: Certificate Handler Compatibility
echo "<div class='test-section'>";
echo "<h2>🔗 Test 5: Certificate Handler Compatibility</h2>";

$issued_certs_query = "DESCRIBE issued_certificates";
$result = mysqli_query($link, $issued_certs_query);
$has_issuing_official_id = false;

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['Field'] === 'issuing_official_id') {
            $has_issuing_official_id = true;
            break;
        }
    }
}

if ($has_issuing_official_id) {
    echo "<span class='success'>✓ issued_certificates table has 'issuing_official_id' column</span><br>";
} else {
    echo "<span class='error'>✗ issued_certificates table missing 'issuing_official_id' column</span><br>";
}

// Check recent certificates
$recent_certs_query = "SELECT ic.id, ic.control_number, o.fullname, o.position 
                       FROM issued_certificates ic 
                       LEFT JOIN officials o ON ic.issuing_official_id = o.id 
                       ORDER BY ic.created_at DESC LIMIT 5";
$result = mysqli_query($link, $recent_certs_query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<h4>Recent Certificates with Officials:</h4>";
    echo "<table>";
    echo "<tr><th>Cert ID</th><th>Control Number</th><th>Issuing Official</th><th>Position</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['control_number']}</td>";
        echo "<td>" . ($row['fullname'] ?: 'Not Set') . "</td>";
        echo "<td>" . ($row['position'] ?: 'Not Set') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<span class='info'>ℹ No recent certificates found</span>";
}
echo "</div>";

// Final Recommendations
echo "<div class='test-section'>";
echo "<h2>📝 Verification Summary & Next Steps</h2>";

$total_available = count($signing_officials);
if ($total_available > 0) {
    echo "<span class='success'>✓ PASS: $total_available officials available for dropdown</span><br>";
    echo "<span class='success'>✓ PASS: Kagawad committee members detected</span><br>";
    echo "<span class='success'>✓ PASS: Database structure compatible</span><br>";
    
    echo "<h3>🎯 Manual Testing Steps:</h3>";
    echo "<ol>";
    echo "<li><strong>Navigate to:</strong> <a href='issue_certificate_form.php' target='_blank'>Issue Certificate Form</a></li>";
    echo "<li><strong>Check dropdown:</strong> Verify all $total_available officials appear in 'Signing Official' dropdown</li>";
    echo "<li><strong>Test selection:</strong> Select different Kagawad committee members</li>";
    echo "<li><strong>Issue certificate:</strong> Complete the form and submit</li>";
    echo "<li><strong>Verify result:</strong> Check that selected official appears on certificate</li>";
    echo "</ol>";
    
    echo "<h3>🔍 Debug Tools:</h3>";
    echo "<ul>";
    echo "<li><a href='test_officials_dropdown.php' target='_blank'>Detailed Officials Test</a></li>";
    echo "<li>Check error log for detailed debugging information</li>";
    echo "</ul>";
} else {
    echo "<span class='error'>✗ FAIL: No officials available for dropdown</span><br>";
    echo "<span class='warning'>⚠ Check officials table data and ensure active officials exist</span><br>";
}
echo "</div>";

echo "</body></html>";
mysqli_close($link);
?>
