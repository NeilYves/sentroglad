<?php
// Verify Certificate System is Working
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Certificate System Verification</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .btn { display: inline-block; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
</style></head><body>";

echo "<h1>🔧 Certificate System Verification</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection</h2>";
if ($link) {
    echo "<div class='success'>✅ Database connection successful</div>";
} else {
    echo "<div class='error'>❌ Database connection failed: " . mysqli_connect_error() . "</div>";
    exit;
}

// Test 2: Required Tables
echo "<h2>2. Required Tables</h2>";
$required_tables = ['residents', 'certificate_types', 'officials', 'issued_certificates'];
foreach ($required_tables as $table) {
    $check_table = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($link, $check_table);
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<div class='success'>✅ Table '$table' exists</div>";
    } else {
        echo "<div class='error'>❌ Table '$table' missing</div>";
    }
}

// Test 3: Business Fields
echo "<h2>3. Business Certificate Fields</h2>";
$business_fields = ['business_name', 'operator_manager'];
foreach ($business_fields as $field) {
    $check_field = "SHOW COLUMNS FROM issued_certificates LIKE '$field'";
    $result = mysqli_query($link, $check_field);
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<div class='success'>✅ Column '$field' exists</div>";
    } else {
        echo "<div class='info'>ℹ️ Column '$field' missing (will be handled gracefully)</div>";
    }
}

// Test 4: Sample Data
echo "<h2>4. Sample Data</h2>";

// Check residents
$residents_sql = "SELECT COUNT(*) as count FROM residents WHERE status = 'Active' LIMIT 5";
$residents_result = mysqli_query($link, $residents_sql);
if ($residents_result) {
    $residents_row = mysqli_fetch_assoc($residents_result);
    if ($residents_row['count'] > 0) {
        echo "<div class='success'>✅ Active residents found: " . $residents_row['count'] . "</div>";
        
        // Show sample residents
        $sample_residents = "SELECT id, first_name, last_name FROM residents WHERE status = 'Active' LIMIT 3";
        $sample_result = mysqli_query($link, $sample_residents);
        if ($sample_result) {
            echo "<table><tr><th>ID</th><th>Name</th></tr>";
            while ($row = mysqli_fetch_assoc($sample_result)) {
                echo "<tr><td>" . $row['id'] . "</td><td>" . $row['first_name'] . " " . $row['last_name'] . "</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<div class='error'>❌ No active residents found</div>";
    }
}

// Check certificate types
$cert_types_sql = "SELECT COUNT(*) as count FROM certificate_types WHERE status = 'Active'";
$cert_types_result = mysqli_query($link, $cert_types_sql);
if ($cert_types_result) {
    $cert_types_row = mysqli_fetch_assoc($cert_types_result);
    if ($cert_types_row['count'] > 0) {
        echo "<div class='success'>✅ Active certificate types found: " . $cert_types_row['count'] . "</div>";
    } else {
        echo "<div class='error'>❌ No active certificate types found</div>";
    }
}

// Check officials
$officials_sql = "SELECT COUNT(*) as count FROM officials WHERE position NOT LIKE 'Ex-%' AND position NOT LIKE 'Former %'";
$officials_result = mysqli_query($link, $officials_sql);
if ($officials_result) {
    $officials_row = mysqli_fetch_assoc($officials_result);
    if ($officials_row['count'] > 0) {
        echo "<div class='success'>✅ Active officials found: " . $officials_row['count'] . "</div>";
    } else {
        echo "<div class='error'>❌ No active officials found</div>";
    }
}

// Test 5: Recent Certificates
echo "<h2>5. Recent Certificates</h2>";
$recent_certs_sql = "SELECT ic.id, ic.control_number, r.first_name, r.last_name, ct.name as cert_type, ic.issue_date 
                     FROM issued_certificates ic 
                     JOIN residents r ON ic.resident_id = r.id 
                     JOIN certificate_types ct ON ic.certificate_type_id = ct.id 
                     ORDER BY ic.id DESC LIMIT 5";
$recent_result = mysqli_query($link, $recent_certs_sql);
if ($recent_result && mysqli_num_rows($recent_result) > 0) {
    echo "<div class='success'>✅ Recent certificates found</div>";
    echo "<table><tr><th>ID</th><th>Control Number</th><th>Resident</th><th>Type</th><th>Date</th><th>Action</th></tr>";
    while ($row = mysqli_fetch_assoc($recent_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['control_number'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['cert_type'] . "</td>";
        echo "<td>" . $row['issue_date'] . "</td>";
        echo "<td><a href='view_certificate.php?id=" . $row['id'] . "' target='_blank' class='btn'>View</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='info'>ℹ️ No certificates found yet</div>";
}

echo "<h2>6. Action Items</h2>";
echo "<div class='info'>";
echo "<h3>✅ Fixes Applied:</h3>";
echo "<ul>";
echo "<li>Enhanced resident validation in certificate_handler.php</li>";
echo "<li>Conditional business field handling in view_certificate.php</li>";
echo "<li>Better error handling and user feedback</li>";
echo "<li>Robust database column checking</li>";
echo "</ul>";

echo "<h3>🎯 Test the System:</h3>";
echo "<a href='issue_certificate_form.php' target='_blank' class='btn'>Issue New Certificate</a>";
echo "<a href='manage_certificates.php' target='_blank' class='btn'>Manage Certificates</a>";
echo "</div>";

mysqli_close($link);
echo "</body></html>";
?>
