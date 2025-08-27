<?php
// Quick test to verify officials data for dropdown testing
require_once 'config.php';

echo "<h2>🔍 Quick Officials Dropdown Test</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .highlight { background-color: #ffffcc; }
</style>";

// Test the exact queries used in issue_certificate_form.php
echo "<h3>📋 Testing Exact Dropdown Queries</h3>";

// 1. Punong Barangay Query
echo "<h4>1. Executives (Punong Barangay/Captain)</h4>";
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

$pb_result = mysqli_query($link, $punong_barangay_sql);
if ($pb_result) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Status</th></tr>";
    $pb_count = 0;
    while ($row = mysqli_fetch_assoc($pb_result)) {
        if ($row['is_available']) {
            $pb_count++;
            echo "<tr class='highlight'>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['fullname']}</td>";
            echo "<td>{$row['position']}</td>";
            echo "<td class='success'>Available</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    echo "<p><strong>Available Executives: $pb_count</strong></p>";
} else {
    echo "<p class='error'>Query failed: " . mysqli_error($link) . "</p>";
}

// 2. Kagawad Query
echo "<h4>2. Kagawads (All Variations)</h4>";
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
                ORDER BY 
                    CASE 
                        WHEN position LIKE '%SK%Chairman%' THEN 1
                        WHEN position LIKE 'Barangay Kagawad - Committee on%' THEN 2
                        WHEN position LIKE 'Kagawad - Committee on%' THEN 3
                        WHEN position LIKE '%Kagawad%' THEN 4
                        WHEN position LIKE 'Kagawad%' THEN 5
                        ELSE 6
                    END,
                    position ASC,
                    fullname ASC";

$kagawad_result = mysqli_query($link, $kagawad_sql);
if ($kagawad_result) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Type</th><th>Status</th></tr>";
    
    $committee_count = 0;
    $regular_count = 0;
    $sk_count = 0;
    
    while ($row = mysqli_fetch_assoc($kagawad_result)) {
        if ($row['is_available']) {
            $type = 'Other';
            $class = '';
            
            if (strpos($row['position'], 'SK') !== false) {
                $type = 'SK Official';
                $sk_count++;
                $class = 'highlight';
            } elseif (strpos($row['position'], 'Committee on') !== false) {
                $type = 'Committee Kagawad';
                $committee_count++;
                $class = 'highlight';
            } elseif (strpos($row['position'], 'Kagawad') !== false) {
                $type = 'Regular Kagawad';
                $regular_count++;
                $class = 'highlight';
            }
            
            echo "<tr class='$class'>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['fullname']}</td>";
            echo "<td>{$row['position']}</td>";
            echo "<td><strong>$type</strong></td>";
            echo "<td class='success'>Available</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    echo "<p><strong>Available Kagawads:</strong></p>";
    echo "<ul>";
    echo "<li>Committee Kagawads: $committee_count</li>";
    echo "<li>Regular Kagawads: $regular_count</li>";
    echo "<li>SK Officials: $sk_count</li>";
    echo "<li><strong>Total: " . ($committee_count + $regular_count + $sk_count) . "</strong></li>";
    echo "</ul>";
} else {
    echo "<p class='error'>Query failed: " . mysqli_error($link) . "</p>";
}

// 3. Simulate the complete dropdown
echo "<h3>🎯 Complete Dropdown Simulation</h3>";
$all_signing_officials = [];

// Add executives
mysqli_data_seek($pb_result, 0);
while ($row = mysqli_fetch_assoc($pb_result)) {
    if ($row['is_available']) {
        $all_signing_officials[] = [
            'id' => $row['id'],
            'fullname' => $row['fullname'],
            'position' => $row['position'],
            'priority' => 1,
            'type' => 'Executive'
        ];
    }
}

// Add kagawads
mysqli_data_seek($kagawad_result, 0);
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
        
        $all_signing_officials[] = [
            'id' => $row['id'],
            'fullname' => $row['fullname'],
            'position' => $row['position'],
            'priority' => 2,
            'type' => $type
        ];
    }
}

echo "<h4>Dropdown Preview (Total: " . count($all_signing_officials) . " officials)</h4>";
echo "<select style='width: 100%; padding: 10px; font-size: 14px;' disabled>";
echo "<option>-- Select Signing Official --</option>";

// Group officials
$grouped = [];
foreach ($all_signing_officials as $official) {
    if ($official['type'] === 'Executive') {
        $grouped['Executives'][] = $official;
    } elseif ($official['type'] === 'SK Official') {
        $grouped['SK Officials'][] = $official;
    } elseif ($official['type'] === 'Committee Kagawad') {
        $grouped['Committee Kagawads'][] = $official;
    } elseif ($official['type'] === 'Regular Kagawad') {
        $grouped['Regular Kagawads'][] = $official;
    } else {
        $grouped['Other Officials'][] = $official;
    }
}

foreach ($grouped as $group_name => $group_officials) {
    if (!empty($group_officials)) {
        echo "<optgroup label='$group_name'>";
        foreach ($group_officials as $official) {
            echo "<option value='{$official['id']}'>{$official['fullname']} ({$official['position']})</option>";
        }
        echo "</optgroup>";
    }
}
echo "</select>";

echo "<h3>✅ Testing Instructions</h3>";
echo "<ol>";
echo "<li><strong>Navigate to:</strong> <a href='issue_certificate_form.php' target='_blank'>Issue Certificate Form</a></li>";
echo "<li><strong>Find the 'Signing Official' dropdown</strong> and click it</li>";
echo "<li><strong>Verify you see the same " . count($all_signing_officials) . " officials</strong> listed above</li>";
echo "<li><strong>Check grouping:</strong> Officials should be organized by type (Executives, Committee Kagawads, etc.)</li>";
echo "<li><strong>Test selection:</strong> Select a Committee Kagawad and submit a test certificate</li>";
echo "<li><strong>Verify result:</strong> Check that the selected official appears on the final certificate</li>";
echo "</ol>";

echo "<h3>🔍 Troubleshooting</h3>";
echo "<ul>";
echo "<li>If dropdown is empty, check database connection and officials table</li>";
echo "<li>If Committee Kagawads are missing, verify position names contain 'Committee on'</li>";
echo "<li>If selection doesn't work, check certificate_handler.php for errors</li>";
echo "<li>Check error logs for detailed debugging information</li>";
echo "</ul>";

mysqli_close($link);
?>
