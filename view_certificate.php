<?php
// --- View Certificate Page ---
// Fetches certificate data and includes the correct template to display it.
// Adds controls for printing and inline editing of the certificate content.

require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Custom error handler to catch and log errors
function certificate_error_handler($errno, $errstr, $errfile, $errline) {
    $error_message = "Certificate View Error: $errstr in $errfile on line $errline";
    error_log($error_message);

    // Display user-friendly error message
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>Certificate Display Error</h3>";
    echo "<p>An error occurred while loading the certificate. Please try again or contact the administrator.</p>";
    echo "<p><strong>Error Details:</strong> $errstr</p>";
    echo "<p><a href='manage_certificates.php' class='btn btn-primary'>Back to Certificates</a></p>";
    echo "</div>";

    return true; // Don't execute PHP internal error handler
}

// Set custom error handler
set_error_handler("certificate_error_handler");

// Wrap main logic in try-catch
try {

// Validate Input: Certificate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Error: Issued Certificate ID not provided.');
}
$issued_certificate_id = (int)$_GET['id'];

// --- Fetch All Necessary Data ---

// Fetch system settings for logos
$system_settings = [];
$settings_query = "SELECT setting_key, setting_value FROM system_settings";
$settings_result = mysqli_query($link, $settings_query);
if ($settings_result) {
    while ($row = mysqli_fetch_assoc($settings_result)) {
        $system_settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Check if business fields exist in the database
$has_business_fields = false;
$check_columns_sql = "SHOW COLUMNS FROM issued_certificates LIKE 'business_name'";
$check_result = mysqli_query($link, $check_columns_sql);
if ($check_result && mysqli_num_rows($check_result) > 0) {
    $has_business_fields = true;
}

// Fetch Issued Certificate, Resident, Type Data, and Issuing Official (conditionally include business fields)
if ($has_business_fields) {
    $sql = "SELECT
                ic.*,
                r.first_name, r.middle_name, r.last_name, r.suffix, r.civil_status, r.gender,
                p.purok_name,
                ct.name as certificate_type_name, ct.template_file,
                o.fullname as issuing_official_name, o.position as issuing_official_position,
                ic.business_name, ic.operator_manager
            FROM issued_certificates ic
            JOIN residents r ON ic.resident_id = r.id
            JOIN certificate_types ct ON ic.certificate_type_id = ct.id
            LEFT JOIN puroks p ON r.purok_id = p.id
            LEFT JOIN officials o ON ic.issuing_official_id = o.id
            WHERE ic.id = ?";
} else {
    $sql = "SELECT
                ic.*,
                r.first_name, r.middle_name, r.last_name, r.suffix, r.civil_status, r.gender,
                p.purok_name,
                ct.name as certificate_type_name, ct.template_file,
                o.fullname as issuing_official_name, o.position as issuing_official_position
            FROM issued_certificates ic
            JOIN residents r ON ic.resident_id = r.id
            JOIN certificate_types ct ON ic.certificate_type_id = ct.id
            LEFT JOIN puroks p ON r.purok_id = p.id
            LEFT JOIN officials o ON ic.issuing_official_id = o.id
            WHERE ic.id = ?";
}

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $issued_certificate_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$certificate = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$certificate) {
    die('Error: Certificate not found.');
}

// Set default values for business fields if they don't exist in database
if (!$has_business_fields) {
    $certificate['business_name'] = null;
    $certificate['operator_manager'] = null;
}

// Fetch Punong Barangay from Officials
$punong_barangay_sql = "SELECT fullname FROM officials WHERE position = 'Punong Barangay' OR position = 'Barangay Captain' ORDER BY position = 'Punong Barangay' DESC LIMIT 1";
$punong_barangay_result = mysqli_query($link, $punong_barangay_sql);
$punong_barangay_row = mysqli_fetch_assoc($punong_barangay_result);
$punong_barangay = $punong_barangay_row ? $punong_barangay_row['fullname'] : 'HON. [Punong Barangay Name Not Set]';

// Fetch Barangay Secretary from Officials
$secretary_sql = "SELECT fullname FROM officials WHERE position = 'Barangay Secretary' LIMIT 1";
$secretary_result = mysqli_query($link, $secretary_sql);
$secretary_row = mysqli_fetch_assoc($secretary_result);
$secretary_name = $secretary_row ? $secretary_row['fullname'] : 'EDISON E. CAMACUNA';

// Determine issuing official - prioritize database record, then URL parameters
$issuing_official_fullname = null;
$issuing_official_position = null;

// First, try to get from database record
if (!empty($certificate['issuing_official_name']) && !empty($certificate['issuing_official_position'])) {
    $issuing_official_fullname = $certificate['issuing_official_name'];
    $issuing_official_position = $certificate['issuing_official_position'];
    error_log("View Certificate: Using official from database: " . $issuing_official_fullname . " (" . $issuing_official_position . ")");
} else {
    // Fallback: Check URL parameters for backward compatibility
    $official_id = null;
    if (isset($_GET['issuing_official_id']) && !empty($_GET['issuing_official_id'])) {
        $official_id = (int)$_GET['issuing_official_id'];
    } elseif (isset($_GET['signing_official_id']) && !empty($_GET['signing_official_id'])) {
        $official_id = (int)$_GET['signing_official_id'];
    }

    if ($official_id) {
        // Enhanced query to handle ALL Kagawad variations including committee assignments
        $official_sql = "SELECT fullname, position FROM officials
                         WHERE id = ?
                         AND position NOT LIKE 'Ex-%'
                         AND position NOT LIKE 'Former %'
                         AND (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE())
                         AND (
                             position LIKE '%Punong Barangay%'
                             OR position LIKE '%Captain%'
                             OR position LIKE '%Kagawad%'
                             OR position LIKE 'Kagawad%'
                             OR position LIKE '%SK%'
                             OR position LIKE '%Secretary%'
                             OR position LIKE '%Treasurer%'
                         )
                         LIMIT 1";
        if ($stmt_o = mysqli_prepare($link, $official_sql)) {
            mysqli_stmt_bind_param($stmt_o, "i", $official_id);
            mysqli_stmt_execute($stmt_o);
            $o_res = mysqli_stmt_get_result($stmt_o);
            if ($o_res && $o_row = mysqli_fetch_assoc($o_res)) {
                $issuing_official_fullname = $o_row['fullname'];
                $issuing_official_position = $o_row['position'];
                error_log("View Certificate: Using official from URL parameter: " . $issuing_official_fullname . " (" . $issuing_official_position . ")");
            }
            mysqli_stmt_close($stmt_o);
        }
    }
}

// --- Prepare Data for the Template ---
$resident_name_parts = array_filter([$certificate['first_name'], $certificate['middle_name'], $certificate['last_name'], $certificate['suffix']]);
$resident_fullname = strtoupper(implode(' ', $resident_name_parts));

$full_address = 'Purok ' . ($certificate['purok_name'] ?? '[Purok Not Set]') . ', Barangay Central Glad';

$issue_date = new DateTime($certificate['issue_date']);

// This array bundles all the data the template will need.
$certificate_data = [
    'resident_name'         => $resident_fullname,
    'resident_civil_status' => $certificate['civil_status'] ?? 'N/A',
    'gender'                => $certificate['gender'] ?? 'N/A',
    'resident_address'      => $full_address,
    'day'                   => $issue_date->format('jS'),
    'month'                 => $issue_date->format('F'),
    'year'                  => $issue_date->format('Y'),
    'punong_barangay'       => $punong_barangay,
    'barangay_logo_path'    => $system_settings['barangay_logo_path'] ?? null,
    'municipality_logo_path'=> $system_settings['municipality_logo_path'] ?? null,
    'system_settings'       => $system_settings, // Pass all settings for template flexibility

    // For template-specific fields that might not be in the DB yet
    'purpose' => $certificate['purpose'] ?? 'any legal purpose',
    'or_number' => $certificate['or_number'] ?? null,
    'fee_paid' => $certificate['amount_paid'] ?? null,
    'control_number' => $certificate['control_number'] ?? 'N/A',
    'resident_birthdate' => $certificate['birthdate'] ?? null,
    'issuing_official_fullname' => $issuing_official_fullname ? $issuing_official_fullname : $punong_barangay,
    'issuing_official_position' => $issuing_official_position ? $issuing_official_position : 'Punong Barangay',
    'issue_date' => $certificate['issue_date'],
    
    // Dynamic officials - automatically detect available officials
    'secretary_fullname' => strtoupper($secretary_name),
    'secretary_position' => 'Barangay Secretary',

    // Business-specific fields (for Business Clearance certificates)
    'business_name' => $certificate['business_name'] ?? null,
    'operator_manager' => $certificate['operator_manager'] ?? null
];

// Determine Template File
$template_file_path = 'templates/' . $certificate['template_file'];

if (!file_exists($template_file_path)) {
    die('Error: Certificate template file not found: ' . html_escape($template_file_path));
}

// --- Render Page ---
// The following is the HTML shell that will contain the certificate template.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Certificate - <?php echo html_escape($certificate['certificate_type_name']); ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { background-color: #e9ecef; }
        .control-panel {
            padding: 15px;
            background-color: #343a40;
            color: white;
            border-radius: 5px;
            margin: 20px auto;
            max-width: 8.5in;
        }
        .editable {
            background-color: #fff8e1;
            padding: 2px 5px;
            border-radius: 3px;
            border: 1px dashed #ffd54f;
            cursor: pointer;
        }
        .editable:focus {
            background-color: #ffffff;
            outline: 2px solid #007bff;
            border-color: transparent;
        }
        @media print {
            .no-print { display: none !important; }
            body { background-color: #fff; }
            .certificate-container { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="control-panel no-print">
        <h5 class="mb-3">Certificate Controls</h5>
        <button onclick="window.print();" class="btn btn-primary">Print Certificate</button>
        <button id="saveChangesBtn" class="btn btn-success">Save Temporary Changes</button>
        <small class="d-block mt-2">To edit, simply click on the highlighted yellow fields in the certificate below. Changes are temporary and for printing purposes only.</small>
    </div>
</div>

<?php
// Include the actual certificate template
include $template_file_path;
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Make specific spans editable
    const editableSpans = document.querySelectorAll('.certificate-container .underline');
    editableSpans.forEach(span => {
        span.setAttribute('contenteditable', 'true');
        span.classList.add('editable');
    });

    // Save changes button logic
    const saveBtn = document.getElementById('saveChangesBtn');
    if(saveBtn) {
        saveBtn.addEventListener('click', function() {
            // This is a placeholder. For now, it just confirms that the command is registered.
            // A full implementation would save this data via AJAX to the server.
            alert('Changes have been noted for this session. They will be reflected on the printout. Note: These changes are not permanently saved to the database.');
            
            // To make the "saved" state visually clear, remove the highlight
            editableSpans.forEach(span => {
                span.classList.remove('editable');
                span.setAttribute('contenteditable', 'false'); // Lock after saving
            });
        });
    }
});
</script>

</body>
</html>
<?php
} catch (Exception $e) {
    // Catch any remaining exceptions
    error_log("Certificate View Exception: " . $e->getMessage());
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>Certificate Display Error</h3>";
    echo "<p>An unexpected error occurred while loading the certificate.</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='manage_certificates.php' class='btn btn-primary'>Back to Certificates</a></p>";
    echo "</div>";
}

// Restore default error handler
restore_error_handler();

mysqli_close($link);
?>