<?php
// --- SMS Blast Handler ---
// Handles form submissions from sms_blast.php
// Validates inputs, sends SMS via TextBee API, logs activity, and redirects with status.

require_once 'config.php';

// ✅ TextBee API configuration
define('TEXTBEE_API_KEY', 'b56cefd2-430a-475f-996d-050770d820a6');
define('TEXTBEE_DEVICE_ID', '688ba1be6cd203ecb57d22e0');
define('TEXTBEE_BASE_URL', 'https://api.textbee.dev/api/v1');

// --- Validate form submission ---
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    // ✅ CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: sms_blast.php?status=error_csrf");
        exit;
    }

    $sms_message     = trim($_POST['message'] ?? '');
    $recipients_mode = $_POST['recipients_mode'] ?? 'all';
    $recipient_ids   = $_POST['resident_ids'] ?? [];
    $purok_ids       = $_POST['purok_ids'] ?? [];

    // --- Validate message ---
    if (empty($sms_message)) {
        header("Location: sms_blast.php?status=error_message_empty");
        exit;
    }

    // --- Get recipients based on mode ---
    $recipients_result = null;

    if ($recipients_mode === 'all') {
        $sql_recipients = "
            SELECT id,
                   CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name, ' ', IFNULL(suffix, '')) as fullname,
                   contact_number
            FROM residents
            WHERE contact_number IS NOT NULL
              AND contact_number != ''
              AND status = 'Active'
            ORDER BY last_name ASC, first_name ASC";
        $recipients_result = mysqli_query($link, $sql_recipients);

    } elseif ($recipients_mode === 'purok' && !empty($purok_ids)) {
        $purok_ids = array_map('intval', $purok_ids);
        $placeholders = implode(',', array_fill(0, count($purok_ids), '?'));
        $sql_recipients = "
            SELECT r.id,
                   CONCAT(r.first_name, ' ', IFNULL(r.middle_name, ''), ' ', r.last_name, ' ', IFNULL(r.suffix, '')) as fullname,
                   r.contact_number,
                   p.purok_name
            FROM residents r
            LEFT JOIN puroks p ON r.purok_id = p.id
            WHERE r.purok_id IN ($placeholders)
              AND r.contact_number IS NOT NULL
              AND r.contact_number != ''
              AND r.status = 'Active'
            ORDER BY p.purok_name ASC, r.last_name ASC, r.first_name ASC";

        if ($stmt = mysqli_prepare($link, $sql_recipients)) {
            $types = str_repeat('i', count($purok_ids));
            $params = array_merge([$types], $purok_ids);
            $refs = [];
            foreach ($params as $key => $value) {
                $refs[$key] = &$params[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $refs);
            mysqli_stmt_execute($stmt);
            $recipients_result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
        } else {
            error_log("DB Prepare Error (SMS Recipients): " . mysqli_error($link));
            header("Location: sms_blast.php?status=error_db_prepare");
            exit;
        }

    } else {
        header("Location: sms_blast.php?status=error_no_recipients");
        exit;
    }

    // --- Send SMS ---
    $sentCount = 0;
    $failCount = 0;

    if ($recipients_result && mysqli_num_rows($recipients_result) > 0) {
        while ($recipient = mysqli_fetch_assoc($recipients_result)) {
            $ok = send_sms_via_textbee($recipient['contact_number'], $sms_message);
            if ($ok) {
                $sentCount++;
            } else {
                $failCount++;
            }
        }

        // --- Log activity ---
        $activity_type = 'SMS Blast Sent';
        $message_preview = substr($sms_message, 0, 100) . (strlen($sms_message) > 100 ? '...' : '');

        // Add recipient mode info to activity description
        $recipient_info = '';
        if ($recipients_mode === 'all') {
            $recipient_info = 'to all residents';
        } elseif ($recipients_mode === 'purok') {
            // Get purok names for logging
            $purok_names = [];
            if (!empty($purok_ids)) {
                $placeholders = implode(',', array_fill(0, count($purok_ids), '?'));
                $purok_sql = "SELECT purok_name FROM puroks WHERE id IN ($placeholders)";
                if ($purok_stmt = mysqli_prepare($link, $purok_sql)) {
                    $types = str_repeat('i', count($purok_ids));
                    $params = array_merge([$types], $purok_ids);
                    $refs = [];
                    foreach ($params as $key => $value) {
                        $refs[$key] = &$params[$key];
                    }
                    call_user_func_array([$purok_stmt, 'bind_param'], $refs);
                    mysqli_stmt_execute($purok_stmt);
                    $purok_result = mysqli_stmt_get_result($purok_stmt);
                    while ($purok_row = mysqli_fetch_assoc($purok_result)) {
                        $purok_names[] = $purok_row['purok_name'];
                    }
                    mysqli_stmt_close($purok_stmt);
                }
            }
            $recipient_info = 'to puroks: ' . implode(', ', $purok_names);
        }

        $activity_description = "SMS blast sent {$recipient_info}. Message: '{$message_preview}'. Sent={$sentCount}, Failed={$failCount}.";

        $log_sql = "INSERT INTO activities (activity_description, activity_type) VALUES (?, ?)";
        if ($stmt = mysqli_prepare($link, $log_sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $activity_description, $activity_type);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        header("Location: sms_blast.php?status=success&sent={$sentCount}&failed={$failCount}");
        exit;

    } else {
        header("Location: sms_blast.php?status=error_no_recipients");
        exit;
    }
} else {
    header("Location: sms_blast.php");
    exit;
}

// --- TextBee API helper function ---
function send_sms_via_textbee($phone_number, $message) {
    $phone_number = preg_replace('/[^0-9+]/', '', $phone_number);

    $payload = [
        "recipients" => [$phone_number],
        "message" => $message
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => TEXTBEE_BASE_URL . "/gateway/devices/" . TEXTBEE_DEVICE_ID . "/send-sms",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: " . TEXTBEE_API_KEY
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error || $httpCode >= 400) {
        error_log("TextBee API Error [{$httpCode}]: " . ($error ?: $response));
        return false;
    }
    return true;
}

// --- Close DB connection ---
if (isset($link) && $link instanceof mysqli) {
    $link->close();
}
