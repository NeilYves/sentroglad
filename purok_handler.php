<?php
// --- Purok Handler ---
// This script processes form submissions for adding, editing, and deleting puroks
// It handles validation, database operations, and redirects with appropriate status messages

require_once 'config.php';

// Check if action parameter is provided
if (!isset($_GET['action']) && !isset($_POST['action'])) {
    header("Location: manage_puroks.php?status=error");
    exit;
}

// Get the action from GET or POST
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

if ($action == 'add') {
    // --- ADD NEW PUROK ---
    if (isset($_POST['purok_name'])) {
        // Sanitize input data
        $purok_name = mysqli_real_escape_string($link, trim($_POST['purok_name']));
        $purok_leader = !empty($_POST['purok_leader']) ? mysqli_real_escape_string($link, trim($_POST['purok_leader'])) : NULL;
        $description = !empty($_POST['description']) ? mysqli_real_escape_string($link, trim($_POST['description'])) : NULL;

        // Basic validation
        if (empty($purok_name)) {
            header("Location: manage_puroks.php?status=error");
            exit;
        }

        // Check if purok name already exists
        $check_sql = "SELECT id FROM puroks WHERE purok_name = ?";
        if ($check_stmt = mysqli_prepare($link, $check_sql)) {
            mysqli_stmt_bind_param($check_stmt, "s", $purok_name);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Purok name already exists
                header("Location: purok_form.php?action=add&error=duplicate");
                exit;
            }
            mysqli_stmt_close($check_stmt);
        }

        // Insert new purok
        $sql = "INSERT INTO puroks (purok_name, purok_leader, description) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $purok_name, $purok_leader, $description);
            
            if (mysqli_stmt_execute($stmt)) {
                // Log activity
                $activity_desc = "Added new purok: " . $purok_name;
                $activity_type = "New Purok";
                $activity_sql = "INSERT INTO activities (activity_description, activity_type) VALUES (?, ?)";
                if ($activity_stmt = mysqli_prepare($link, $activity_sql)) {
                    mysqli_stmt_bind_param($activity_stmt, "ss", $activity_desc, $activity_type);
                    mysqli_stmt_execute($activity_stmt);
                    mysqli_stmt_close($activity_stmt);
                }
                
                header("Location: manage_puroks.php?status=success_add");
            } else {
                header("Location: manage_puroks.php?status=error");
            }
            mysqli_stmt_close($stmt);
        } else {
            header("Location: manage_puroks.php?status=error");
        }
    } else {
        header("Location: manage_puroks.php?status=error");
    }

} elseif ($action == 'edit') {
    // --- EDIT EXISTING PUROK ---
    if (isset($_POST['purok_id'], $_POST['purok_name'])) {
        // Sanitize input data
        $purok_id = intval($_POST['purok_id']);
        $purok_name = mysqli_real_escape_string($link, trim($_POST['purok_name']));
        $purok_leader = !empty($_POST['purok_leader']) ? mysqli_real_escape_string($link, trim($_POST['purok_leader'])) : NULL;
        $description = !empty($_POST['description']) ? mysqli_real_escape_string($link, trim($_POST['description'])) : NULL;

        // Basic validation
        if (empty($purok_id)) {
            header("Location: manage_puroks.php?status=error");
            exit;
        }

        // Get current purok name for logging (since we're not updating it)
        $current_name_sql = "SELECT purok_name FROM puroks WHERE id = ?";
        if ($name_stmt = mysqli_prepare($link, $current_name_sql)) {
            mysqli_stmt_bind_param($name_stmt, "i", $purok_id);
            mysqli_stmt_execute($name_stmt);
            $name_result = mysqli_stmt_get_result($name_stmt);
            if ($name_row = mysqli_fetch_assoc($name_result)) {
                $purok_name = $name_row['purok_name']; // Use current name for logging
            }
            mysqli_stmt_close($name_stmt);
        }

        // Update purok (excluding purok_name since it's now read-only)
        $sql = "UPDATE puroks SET purok_leader = ?, description = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssi", $purok_leader, $description, $purok_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Log activity
                $activity_desc = "Updated purok: " . $purok_name;
                $activity_type = "Update Purok";
                $activity_sql = "INSERT INTO activities (activity_description, activity_type) VALUES (?, ?)";
                if ($activity_stmt = mysqli_prepare($link, $activity_sql)) {
                    mysqli_stmt_bind_param($activity_stmt, "ss", $activity_desc, $activity_type);
                    mysqli_stmt_execute($activity_stmt);
                    mysqli_stmt_close($activity_stmt);
                }
                
                header("Location: manage_puroks.php?status=success_edit");
            } else {
                header("Location: manage_puroks.php?status=error");
            }
            mysqli_stmt_close($stmt);
        } else {
            header("Location: manage_puroks.php?status=error");
        }
    } else {
        header("Location: manage_puroks.php?status=error");
    }

} elseif ($action == 'delete') {
    // --- DELETE PUROK DISABLED ---
    // Purok deletion has been disabled for data integrity
    header("Location: manage_puroks.php?status=error&message=delete_disabled");
    exit;

} else {
    // Invalid action
    header("Location: manage_puroks.php?status=error");
}

// Close database connection
mysqli_close($link);
?> 