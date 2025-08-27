<?php
// --- Certificate Issuance Handler ---
// Processes new certificate issuance form, validates input,
// generates a control number, saves to DB, logs activity,
// then redirects to the print/view page.

require_once 'config.php';

// --- Safe html_escape helper ---
if (!function_exists('html_escape')) {
    function html_escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// --- Check Request Method and Action ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'issue') {

    // --- Enhanced Resident ID Validation ---
    // Handle both regular and business certificate resident selection
    $resident_id = 0;

    // Debug: Log all POST data for troubleshooting
    error_log("Certificate Handler: POST data received: " . print_r($_POST, true));

    // Check regular resident_id field first (this should work for both regular and business certificates now)
    if (!empty($_POST['resident_id'])) {
        $resident_id = (int)$_POST['resident_id'];
        error_log("Certificate Handler: Using resident_id: $resident_id");
    }
    // Fallback: Check business_resident_id_hidden field (for business certificates)
    elseif (!empty($_POST['business_resident_id_hidden'])) {
        $resident_id = (int)$_POST['business_resident_id_hidden'];
        error_log("Certificate Handler: Using business_resident_id_hidden: $resident_id");
    }
    // Fallback: Check business_main_resident_id (hidden main field used by JS toggle)
    elseif (!empty($_POST['business_main_resident_id'])) {
        $resident_id = (int)$_POST['business_main_resident_id'];
        error_log("Certificate Handler: Using business_main_resident_id: $resident_id");
    }
    // Fallback: Check business_resident_id field (legacy visible select)
    elseif (!empty($_POST['business_resident_id'])) {
        $resident_id = (int)$_POST['business_resident_id'];
        error_log("Certificate Handler: Using business_resident_id: $resident_id");
    }

    // Debug: Log which fields were checked
    error_log("Certificate Handler: Resident ID field values - resident_id: '" . ($_POST['resident_id'] ?? 'not set') . "', business_resident_id_hidden: '" . ($_POST['business_resident_id_hidden'] ?? 'not set') . "', business_main_resident_id: '" . ($_POST['business_main_resident_id'] ?? 'not set') . "', business_resident_id: '" . ($_POST['business_resident_id'] ?? 'not set') . "'");

    // Validate resident_id
    if ($resident_id <= 0) {
        error_log("Certificate Handler: Invalid resident_id: $resident_id - redirecting to error page");
        header("Location: issue_certificate_form.php?status=error_missing_resident_id");
        exit;
    }

    // Verify resident exists in database
    $resident_check_sql = "SELECT id, first_name, last_name FROM residents WHERE id = ? AND status = 'Active'";
    if ($stmt_resident = mysqli_prepare($link, $resident_check_sql)) {
        mysqli_stmt_bind_param($stmt_resident, "i", $resident_id);
        mysqli_stmt_execute($stmt_resident);
        $resident_result = mysqli_stmt_get_result($stmt_resident);
        if (!$resident_result || mysqli_num_rows($resident_result) === 0) {
            error_log("Certificate Handler: Resident ID $resident_id not found or inactive");
            header("Location: issue_certificate_form.php?status=error_invalid_resident");
            exit;
        }
        mysqli_stmt_close($stmt_resident);
    }

    // --- Validate Other Required Fields ---
    $certificate_type_id = !empty($_POST['certificate_type_id']) ? (int)$_POST['certificate_type_id'] : 0;
    if ($certificate_type_id <= 0) {
        header("Location: issue_certificate_form.php?status=error_missing_certificate_type_id");
        exit;
    }

    if (empty($_POST['issue_date'])) {
        header("Location: issue_certificate_form.php?status=error_missing_issue_date");
        exit;
    }

    // --- Sanitize Inputs ---
    $issue_date = mysqli_real_escape_string($link, $_POST['issue_date']);

    // --- Business-Specific Fields (for Business Clearance) ---
    $business_name = !empty($_POST['business_name']) ? mysqli_real_escape_string($link, $_POST['business_name']) : null;
    $operator_manager = !empty($_POST['operator_manager']) ? mysqli_real_escape_string($link, $_POST['operator_manager']) : null;

    // --- Purpose Handling (Default for Business Certificates) ---
    $purpose = !empty($_POST['purpose']) ? mysqli_real_escape_string($link, $_POST['purpose']) : 'Business permit application';

    // If this is a business certificate, ensure we have a default purpose
    if ($business_name && empty($_POST['purpose'])) {
        $purpose = 'Business permit application';
    }

    // Debug: Log business fields
    error_log("Certificate Handler: Business Name = " . ($business_name ?: 'NULL'));
    error_log("Certificate Handler: Operator/Manager = " . ($operator_manager ?: 'NULL'));

    // --- Issuing Official Selection (UPDATED TO HANDLE ALL KAGAWADS) ---
    $issuing_official_id = !empty($_POST['signing_official_id']) ? (int)$_POST['signing_official_id'] : null;

    if ($issuing_official_id) {
        // Enhanced query to properly validate officials including all Kagawad positions
        $chk_sql = "
            SELECT id, position, fullname FROM officials 
            WHERE id = ? 
              AND position NOT LIKE 'Ex-%'
              AND position NOT LIKE 'Former %'
              AND (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE())
            LIMIT 1
        ";
        if ($stmt_so = mysqli_prepare($link, $chk_sql)) {
            mysqli_stmt_bind_param($stmt_so, "i", $issuing_official_id);
            mysqli_stmt_execute($stmt_so);
            $so_res = mysqli_stmt_get_result($stmt_so);
            if (!$so_res || mysqli_num_rows($so_res) === 0) {
                // Log why the official was rejected for debugging
                error_log("Certificate Handler: Selected official ID $issuing_official_id not found or inactive");
                $issuing_official_id = null;
            } else {
                $official_data = mysqli_fetch_assoc($so_res);
                error_log("Certificate Handler: Using selected official: " . $official_data['fullname'] . " (" . $official_data['position'] . ")");
            }
            mysqli_stmt_close($stmt_so);
        } else {
            error_log("Certificate Handler: Failed to prepare official check query: " . mysqli_error($link));
            $issuing_official_id = null;
        }
    }

    // Auto-pick official if none was chosen or invalid
    if (!$issuing_official_id) {
        // Enhanced query that properly handles all official types including all Kagawads
        $auto_official_sql = "
            SELECT id, fullname, position FROM officials 
            WHERE position NOT LIKE 'Ex-%'
              AND position NOT LIKE 'Former %'
              AND (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE()) 
            ORDER BY 
                CASE 
                    WHEN position = 'Punong Barangay' THEN 1
                    WHEN position LIKE '%Captain%' THEN 2
                    WHEN position LIKE '%SK%Chairman%' THEN 3
                    WHEN position LIKE '%Kagawad%' THEN 4
                    WHEN position LIKE '%Secretary%' THEN 5
                    WHEN position LIKE '%Treasurer%' THEN 6
                    ELSE 7
                END,
                position ASC,
                fullname ASC
            LIMIT 1
        ";
        $auto_official_res = mysqli_query($link, $auto_official_sql);
        if ($auto_official_res && $auto_row = mysqli_fetch_assoc($auto_official_res)) {
            $issuing_official_id = (int)$auto_row['id'];
            error_log("Certificate Handler: Auto-selected official: " . $auto_row['fullname'] . " (" . $auto_row['position'] . ")");
        } else {
            error_log("Certificate Handler: No active officials found for auto-selection");
        }
    }

    // --- Generate Control Number ---
    $cert_type_sql = "SELECT name FROM certificate_types WHERE id = ?";
    $cert_type_code = 'CERT';

    if ($stmt_cert_type = mysqli_prepare($link, $cert_type_sql)) {
        mysqli_stmt_bind_param($stmt_cert_type, "i", $certificate_type_id);
        mysqli_stmt_execute($stmt_cert_type);
        $cert_type_result = mysqli_stmt_get_result($stmt_cert_type);
        if ($cert_type_result && $row = mysqli_fetch_assoc($cert_type_result)) {
            $words = explode(' ', $row['name']);
            $abbr = '';
            foreach ($words as $word) {
                if (!empty($word)) {
                    $abbr .= strtoupper(substr($word, 0, 1));
                }
            }
            if (!empty($abbr)) {
                $cert_type_code = $abbr;
            }
        }
        mysqli_stmt_close($stmt_cert_type);
    }

    $year_month = date('Y-m', strtotime($issue_date));
    $control_number_prefix = $cert_type_code . '-' . $year_month . '-';

    $seq_sql = "
        SELECT MAX(CAST(SUBSTRING_INDEX(control_number, '-', -1) AS UNSIGNED)) as last_seq 
        FROM issued_certificates 
        WHERE control_number LIKE ?
    ";
    $next_seq = 1;
    if ($stmt_seq = mysqli_prepare($link, $seq_sql)) {
        $like_prefix = $control_number_prefix . '%';
        mysqli_stmt_bind_param($stmt_seq, "s", $like_prefix);
        mysqli_stmt_execute($stmt_seq);
        $seq_result = mysqli_stmt_get_result($stmt_seq);
        if ($seq_result && $seq_row = mysqli_fetch_assoc($seq_result)) {
            $next_seq = (int)$seq_row['last_seq'] + 1;
        }
        mysqli_stmt_close($stmt_seq);
    }

    $control_number = $control_number_prefix . str_pad($next_seq, 4, '0', STR_PAD_LEFT);

    // --- Final validation: ensure we have an issuing official ---
    if (!$issuing_official_id) {
        error_log("Certificate Handler: No issuing official available - cannot issue certificate");
        header("Location: issue_certificate_form.php?status=error_no_officials");
        exit;
    }

    // --- Check if business fields exist in database ---
    $has_business_fields = false;
    $check_columns_sql = "SHOW COLUMNS FROM issued_certificates LIKE 'business_name'";
    $check_result = mysqli_query($link, $check_columns_sql);
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $has_business_fields = true;
    }

    // --- Save Certificate ---
    $stmt = null;

    if ($has_business_fields) {
        // Use full SQL with business fields
        $sql = "
            INSERT INTO issued_certificates
                (resident_id, certificate_type_id, issuing_official_id, control_number, issue_date, purpose, business_name, operator_manager)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare($link, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iiisssss",
                $resident_id,
                $certificate_type_id,
                $issuing_official_id,
                $control_number,
                $issue_date,
                $purpose,
                $business_name,
                $operator_manager
            );
        }
    } else {
        // Use basic SQL without business fields (for backward compatibility)
        error_log("Certificate Handler: Business fields not found in database, using basic insert");
        $sql = "
            INSERT INTO issued_certificates
                (resident_id, certificate_type_id, issuing_official_id, control_number, issue_date, purpose)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare($link, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iiisss",
                $resident_id,
                $certificate_type_id,
                $issuing_official_id,
                $control_number,
                $issue_date,
                $purpose
            );
        }
    }

    // Execute the prepared statement if it was created successfully
    if ($stmt && mysqli_stmt_execute($stmt)) {
        $issued_certificate_id = mysqli_insert_id($link);

        // --- Enhanced Activity Logging ---
        $resident_name = "ID:$resident_id";
        $official_name = "ID:$issuing_official_id";

        // Get resident name
        $res_name_sql = "SELECT first_name, last_name FROM residents WHERE id = ?";
        if ($stmt_res_name = mysqli_prepare($link, $res_name_sql)) {
            mysqli_stmt_bind_param($stmt_res_name, "i", $resident_id);
            mysqli_stmt_execute($stmt_res_name);
            $res_name_result = mysqli_stmt_get_result($stmt_res_name);
            if ($res_name_result && $r_row = mysqli_fetch_assoc($res_name_result)) {
                $resident_name = html_escape($r_row['first_name'] . ' ' . $r_row['last_name']);
            }
            mysqli_stmt_close($stmt_res_name);
        }

        // Get official name
        $off_name_sql = "SELECT fullname, position FROM officials WHERE id = ?";
        if ($stmt_off_name = mysqli_prepare($link, $off_name_sql)) {
            mysqli_stmt_bind_param($stmt_off_name, "i", $issuing_official_id);
            mysqli_stmt_execute($stmt_off_name);
            $off_name_result = mysqli_stmt_get_result($stmt_off_name);
            if ($off_name_result && $o_row = mysqli_fetch_assoc($off_name_result)) {
                $official_name = html_escape($o_row['fullname'] . ' (' . $o_row['position'] . ')');
            }
            mysqli_stmt_close($stmt_off_name);
        }

        $activity_desc = "Issued certificate ($control_number) to $resident_name, signed by $official_name. Purpose: " . html_escape(substr($purpose, 0, 50)) . "...";
        $log_sql = "INSERT INTO activities (activity_description, activity_type) VALUES (?, 'Certificate Issued')";
        if ($stmt_log = mysqli_prepare($link, $log_sql)) {
            mysqli_stmt_bind_param($stmt_log, "s", $activity_desc);
            mysqli_stmt_execute($stmt_log);
            mysqli_stmt_close($stmt_log);
        }

        // Success log
        error_log("Certificate Handler: Successfully issued certificate $control_number to $resident_name");

        // Redirect to view certificate
        $redirect = "view_certificate.php?id=" . $issued_certificate_id;
        if (!empty($issuing_official_id)) {
            $redirect .= "&issuing_official_id=" . (int)$issuing_official_id;
        }
        header("Location: " . $redirect);
        exit;

    } else {
        error_log("Certificate Handler: DB Execute Error: " . ($stmt ? mysqli_stmt_error($stmt) : mysqli_error($link)));
        header("Location: issue_certificate_form.php?status=error_db_execute&msg=" . urlencode($stmt ? mysqli_stmt_error($stmt) : mysqli_error($link)));
        exit;
    }

    // Close the prepared statement
    if ($stmt) {
        mysqli_stmt_close($stmt);
    }

} else {
    // Invalid request method or action
    error_log("Certificate Handler: Invalid request - Method: " . $_SERVER['REQUEST_METHOD'] . ", Action: " . ($_POST['action'] ?? 'none'));
    header("Location: manage_certificates.php");
    exit;
}

// --- Close DB Connection ---
if ($link instanceof mysqli) {
    mysqli_close($link);
}
?>