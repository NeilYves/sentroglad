<?php
// --- Enhanced Resident Data Handler ---
// This script processes requests for adding, editing, and deleting resident records.
// It interacts with the database and redirects the user with status messages.

// Include the database configuration file, which establishes $link connection.
require_once 'config.php';

// Start session to use session messages
session_start();

// Test database connection and table structure
if (!$link) {
    error_log("Database connection failed in resident_handler.php");
    $_SESSION['error'] = 'Database connection failed. Please try again later.';
    header("Location: resident_form.php?action=add");
    exit;
}

// Check if residents table exists and has the expected structure
$table_check = mysqli_query($link, "DESCRIBE residents");
if (!$table_check) {
    error_log("Residents table structure check failed: " . mysqli_error($link));
    $_SESSION['error'] = 'Database table structure error. Please contact administrator.';
    header("Location: resident_form.php?action=add");
    exit;
}

// Check if required columns exist
$required_columns = ['first_name', 'last_name', 'gender', 'civil_status', 'purok_id'];
$existing_columns = [];
while ($row = mysqli_fetch_assoc($table_check)) {
    $existing_columns[] = $row['Field'];
}

$missing_columns = array_diff($required_columns, $existing_columns);
if (!empty($missing_columns)) {
    error_log("Missing required columns in residents table: " . implode(', ', $missing_columns));
    $_SESSION['error'] = 'Database table structure error. Please contact administrator.';
    header("Location: resident_form.php?action=add");
    exit;
}

// Function to send JSON response for AJAX requests (e.g., validation feedback)
function sendJsonResponse($status, $message, $errors = []) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message, 'errors' => $errors]);
    exit;
}

// Initialize the action variable.
$action = '';

// --- Determine Action ---
// The script determines the requested action (add, edit, delete) from POST or GET parameters.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Action from a POST request (typically from a form submission for add/edit).
    $action = $_POST['action'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    // Action from a GET request (typically from a link for delete).
    $action = $_GET['action'];
}

// --- Process Actions ---
// A conditional block to handle different actions based on the $action variable.

// Debug: Log basic request information
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Action: " . $action);
error_log("POST data count: " . count($_POST));

if ($action == 'add' || $action == 'edit') {
    $errors = [];

    // Debug: Log received data
    error_log("Received POST data: " . print_r($_POST, true));
    error_log("POST keys: " . implode(', ', array_keys($_POST)));

    // Test basic form data reception
    if (!isset($_POST['first_name']) || !isset($_POST['last_name']) || !isset($_POST['gender'])) {
        error_log("Missing basic required form fields");
        $_SESSION['error'] = 'Missing required form fields. Please try again.';
        header("Location: resident_form.php?action=add");
        exit;
    }

    // Sanitize and validate inputs
    $resident_id = ($action == 'edit' && isset($_POST['resident_id'])) ? mysqli_real_escape_string($link, $_POST['resident_id']) : null;
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $gender_other = trim($_POST['gender_other'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $spouse_name = trim($_POST['spouse_name'] ?? '');
    $maintenance_medicine = trim($_POST['maintenance_medicine'] ?? '');
    $other_medicine = trim($_POST['other_medicine'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $age = !empty($_POST['age']) ? intval($_POST['age']) : 0;
    $educational_attainment = trim($_POST['educational_attainment'] ?? '');
    $family_planning = trim($_POST['family_planning'] ?? 'Not Applicable');
    $water_source = trim($_POST['water_source'] ?? '');
    $toilet_facility = trim($_POST['toilet_facility'] ?? '');
    $pantawid_4ps = trim($_POST['pantawid_4ps'] ?? 'No');
    $backyard_gardening = trim($_POST['backyard_gardening'] ?? 'No');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $status = trim($_POST['status'] ?? 'Active');
    $purok_id = !empty($_POST['purok_id']) ? intval($_POST['purok_id']) : 0;
    $date_status_changed = trim($_POST['date_status_changed'] ?? '');
    $status_remarks = trim($_POST['status_remarks'] ?? '');
    $household_id = !empty($_POST['household_id']) ? intval($_POST['household_id']) : null;

    // Server-side Validation
    if (empty($first_name)) {
        $errors['first_name'] = 'First Name is required.';
    }
    if (empty($last_name)) {
        $errors['last_name'] = 'Last Name is required.';
    }
    if (empty($gender)) {
        $errors['gender'] = 'Gender is required.';
    } elseif (!in_array($gender, ['Male', 'Female', 'Other'])) {
        $errors['gender'] = 'Invalid Gender selected.';
    }
    if (empty($civil_status)) {
        $errors['civil_status'] = 'Civil Status is required.';
    } elseif (!in_array($civil_status, ['Single', 'Married', 'Widow/er', 'Separated'])) {
        $errors['civil_status'] = 'Invalid Civil Status selected.';
    }
    // Only validate water_source if it's provided (make it optional)
    if (!empty($water_source) && !in_array($water_source, ['Level 0 - Deepwell', 'Level 1 - Point Source', 'Level 2 - Communal Faucet', 'Level 3 - Individual Connection', 'Others'])) {
        $errors['water_source'] = 'Invalid Water Source selected.';
    }
    if (empty($purok_id) || $purok_id <= 0) {
        $errors['purok_id'] = 'Purok is required.';
    }
    
    // Check if purok_id exists in puroks table (foreign key constraint)
    if ($purok_id > 0) {
        $purok_check = mysqli_query($link, "SELECT id FROM puroks WHERE id = $purok_id");
        if (!$purok_check || mysqli_num_rows($purok_check) == 0) {
            $errors['purok_id'] = 'Selected Purok does not exist.';
        }
    }
    
    if ($civil_status === 'Married' && empty($spouse_name)) {
        $errors['spouse_name'] = 'Spouse name is required for married residents.';
    }

    // Enhanced contact number validation (must be 11 digits starting with 09)
    if (!empty($contact_number)) {
        if (!preg_match('/^09\d{9}$/', $contact_number)) {
            $errors['contact_number'] = 'Phone number must be exactly 11 digits and start with 09 (e.g., 09123456789).';
        }
    }

    // Validate status and educational attainment against allowed values (if applicable)
    $allowed_statuses = ['Active', 'Deceased', 'Moved Out'];
    if (!in_array($status, $allowed_statuses)) {
        $errors['status'] = 'Invalid status selected.';
    }
    $allowed_education = ['No Formal Education', 'Elementary', 'Elementary Graduate', 'High School', 'High School Graduate', 'Vocational', 'College', 'College Graduate', 'Post Graduate'];
    if (!empty($educational_attainment) && !in_array($educational_attainment, $allowed_education)) {
        $errors['educational_attainment'] = 'Invalid educational attainment selected.';
    }

    // If there are validation errors, redirect back or send JSON response
    if (!empty($errors)) {
        $_SESSION['error'] = 'Please correct the errors in the form.';
        $_SESSION['form_data'] = $_POST; // Persist form data
        $_SESSION['validation_errors'] = $errors; // Store errors
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // AJAX request
            sendJsonResponse('error', 'Validation failed.', $errors);
        } else {
            // Regular form submission
            $redirect_url = ($action == 'edit') ? "resident_form.php?action=edit&id=$resident_id" : "resident_form.php?action=add";
            header("Location: " . $redirect_url);
            exit;
        }
    }

    // All inputs are valid, proceed with database operations
    if ($action == 'add') {
        try {
            // Check database connection before proceeding
            if (!$link || mysqli_ping($link) === false) {
                error_log("Database connection lost before INSERT operation");
                $_SESSION['error'] = 'Database connection lost. Please try again.';
                header("Location: resident_form.php?action=add");
                exit;
            }
            
            // SQL query to insert a new resident - specify only the columns we want to insert
            $sql = "INSERT INTO residents (
                first_name, middle_name, last_name, suffix, gender, civil_status, purok_id, status, 
                contact_number, educational_attainment, family_planning, water_source, toilet_facility,
                maintenance_medicine, other_medicine, birthdate
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Debug: Log the SQL and parameters
            error_log("INSERT SQL: " . $sql);
            error_log("Parameters to bind: " . print_r([$first_name, $middle_name, $last_name, $suffix, $gender, $civil_status, $purok_id, $status, $contact_number, $educational_attainment, $family_planning, $water_source, $toilet_facility, $maintenance_medicine, $other_medicine, $birthdate], true));
            
            // Debug: Count placeholders and parameters
            $placeholder_count = substr_count($sql, '?');
            $param_count = 16; // We know we have 16 parameters
            error_log("Placeholder count: $placeholder_count, Parameter count: $param_count");
            
            // Prepare the SQL statement
            if ($stmt = mysqli_prepare($link, $sql)) {
                // Bind parameters (16 parameters total)
                mysqli_stmt_bind_param($stmt, "sssssssissssssss", 
                    $first_name, $middle_name, $last_name, $suffix, $gender, $civil_status, $purok_id, $status, 
                    $contact_number, $educational_attainment, $family_planning, $water_source, $toilet_facility,
                    $maintenance_medicine, $other_medicine, $birthdate
                );
                
                // Execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    // Record activity
                    $activity_desc = "Added new resident: " . htmlspecialchars($first_name . ' ' . $last_name, ENT_QUOTES, 'UTF-8');
                    $activity_type = "New Resident";
                    $activity_sql = "INSERT INTO activities (activity_description, activity_type) VALUES (?, ?)";
                    if ($activity_stmt = mysqli_prepare($link, $activity_sql)) {
                        mysqli_stmt_bind_param($activity_stmt, "ss", $activity_desc, $activity_type);
                        mysqli_stmt_execute($activity_stmt);
                        mysqli_stmt_close($activity_stmt);
                    }
                    $_SESSION['success'] = 'Resident added successfully!';
                    $_SESSION['action_type'] = 'add';
                    header("Location: manage_residents.php");
                    exit;
                } else {
                    $_SESSION['error'] = 'Database error: Could not add resident.';
                    error_log("Resident Add DB Error: " . mysqli_error($link));
                    error_log("Resident Add SQL: " . $sql);
                    error_log("Resident Add Parameters: " . print_r([$first_name, $middle_name, $last_name, $suffix, $gender, $civil_status, $purok_id, $status, $contact_number, $educational_attainment, $family_planning, $water_source, $toilet_facility, $maintenance_medicine, $other_medicine, $birthdate], true));
                    header("Location: resident_form.php?action=add");
                    exit;
                }
                mysqli_stmt_close($stmt);
            } else {
                $_SESSION['error'] = 'Database error: Could not prepare statement for add.';
                error_log("Resident Add Prepare Error: " . mysqli_error($link));
                header("Location: resident_form.php?action=add");
                exit;
            }
        } catch (Exception $e) {
            error_log("Resident Add Exception: " . $e->getMessage());
            $_SESSION['error'] = 'An unexpected error occurred: ' . $e->getMessage();
            header("Location: resident_form.php?action=add");
            exit;
        }
    } elseif ($action == 'edit') {
        // --- EDIT EXISTING RESIDENT --- //
        // Check if resident_id is provided for edit action
        if (is_null($resident_id)) {
            $_SESSION['error'] = 'Resident ID is missing for edit.';
            header("Location: manage_residents.php");
            exit;
        }

        // SQL query to update existing resident with all enhanced fields
        $sql = "UPDATE residents SET
            first_name = ?, middle_name = ?, last_name = ?, suffix = ?, gender = ?, gender_other = ?, civil_status = ?, spouse_name = ?, maintenance_medicine = ?, other_medicine = ?,
            birthdate = ?, age = ?, educational_attainment = ?, family_planning = ?,
            water_source = ?, toilet_facility = ?, pantawid_4ps = ?, backyard_gardening = ?,
            contact_number = ?, status = ?, purok_id = ?,
            date_status_changed = ?, status_remarks = ?, household_id = ?
            WHERE id = ?";

        // Prepare the SQL statement
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Handle NULL values for household_id
            if ($household_id === null) {
                // Use a different approach for NULL values
                $sql = "UPDATE residents SET
                    first_name = ?, middle_name = ?, last_name = ?, suffix = ?, gender = ?, gender_other = ?, civil_status = ?, spouse_name = ?, maintenance_medicine = ?, other_medicine = ?,
                    birthdate = ?, age = ?, educational_attainment = ?, family_planning = ?,
                    water_source = ?, toilet_facility = ?, pantawid_4ps = ?, backyard_gardening = ?,
                    contact_number = ?, status = ?, purok_id = ?,
                    date_status_changed = ?, status_remarks = ?, household_id = NULL
                    WHERE id = ?";
                
                if ($stmt = mysqli_prepare($link, $sql)) {
                    // Bind parameters (23 parameters total - 22 fields + 1 for WHERE clause, household_id is NULL in SQL)
                    mysqli_stmt_bind_param($stmt, "ssssssssssisssssssssissi",
                        $first_name, $middle_name, $last_name, $suffix, $gender, $gender_other, $civil_status, $spouse_name, $maintenance_medicine, $other_medicine, $birthdate, $age,
                        $educational_attainment, $family_planning, $water_source, $toilet_facility,
                        $pantawid_4ps, $backyard_gardening, $contact_number, $status, $purok_id,
                        $date_status_changed, $status_remarks, $resident_id
                    );
                }
            } else {
                // Original binding for non-NULL household_id
                mysqli_stmt_bind_param($stmt, "ssssssssssisssssssssissii",
                    $first_name, $middle_name, $last_name, $suffix, $gender, $gender_other, $civil_status, $spouse_name, $maintenance_medicine, $other_medicine, $birthdate, $age,
                    $educational_attainment, $family_planning, $water_source, $toilet_facility,
                    $pantawid_4ps, $backyard_gardening, $contact_number, $status, $purok_id,
                    $date_status_changed, $status_remarks, $household_id, $resident_id
                );
            }
            
            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Record activity
                $activity_desc = "Updated resident details for ID: " . htmlspecialchars($resident_id, ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($first_name . ' ' . $last_name, ENT_QUOTES, 'UTF-8') . ")";
                $activity_type = "Update Resident";
                $activity_sql = "INSERT INTO activities (activity_description, activity_type) VALUES (?, ?)";
                if ($activity_stmt = mysqli_prepare($link, $activity_sql)) {
                    mysqli_stmt_bind_param($activity_stmt, "ss", $activity_desc, $activity_type);
                    mysqli_stmt_execute($activity_stmt);
                    mysqli_stmt_close($activity_stmt);
                }
                $_SESSION['success'] = 'Resident updated successfully!';
                $_SESSION['action_type'] = 'edit';
                header("Location: manage_residents.php");
                exit; // Add explicit exit after redirect
            } else {
                $_SESSION['error'] = 'Database error: Could not update resident.';
                error_log("Resident Update DB Error: " . mysqli_error($link));
                header("Location: resident_form.php?action=edit&id=$resident_id");
                exit; // Add explicit exit after redirect
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error'] = 'Database error: Could not prepare statement for edit.';
            error_log("Resident Update Prepare Error: " . mysqli_error($link));
            header("Location: resident_form.php?action=edit&id=$resident_id");
            exit; // Add explicit exit after redirect
        }
    }

} elseif ($action == 'archive') {
    // --- ARCHIVE RESIDENT ---
    if (isset($_GET['id'])) {
        $resident_id = mysqli_real_escape_string($link, $_GET['id']);
        
        // Get resident info for logging
        $info_sql = "SELECT first_name, last_name FROM residents WHERE id = ?";
        $resident_name = "ID: $resident_id";
        if ($info_stmt = mysqli_prepare($link, $info_sql)) {
            mysqli_stmt_bind_param($info_stmt, "i", $resident_id);
            mysqli_stmt_execute($info_stmt);
            $info_result = mysqli_stmt_get_result($info_stmt);
            if ($info_row = mysqli_fetch_assoc($info_result)) {
                $resident_name = $info_row['first_name'] . ' ' . $info_row['last_name'];
            }
            mysqli_stmt_close($info_stmt);
        }
        
        // Archive the resident
        $archive_sql = "UPDATE residents SET 
                       status = 'Archived', 
                       archived_date = NOW(), 
                       archived_by = ?, 
                       archive_reason = 'Archived via management interface' 
                       WHERE id = ?";
        
        if ($stmt = mysqli_prepare($link, $archive_sql)) {
            $archived_by = $_SESSION['username'] ?? 'System';
            mysqli_stmt_bind_param($stmt, "si", $archived_by, $resident_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Log the activity
                $activity_desc = "Archived resident: " . htmlspecialchars($resident_name, ENT_QUOTES, 'UTF-8');
                $activity_sql = "INSERT INTO activities (activity_description, activity_type) VALUES (?, 'Archive Resident')";
                if ($activity_stmt = mysqli_prepare($link, $activity_sql)) {
                    mysqli_stmt_bind_param($activity_stmt, "s", $activity_desc);
                    mysqli_stmt_execute($activity_stmt);
                    mysqli_stmt_close($activity_stmt);
                }
                
                $_SESSION['success'] = "Resident '$resident_name' has been archived successfully!";
                $_SESSION['action_type'] = 'archive';
                header("Location: manage_residents.php");
            } else {
                $_SESSION['error'] = "Error archiving resident. Please try again.";
                header("Location: manage_residents.php");
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error'] = "Database error. Please try again.";
            header("Location: manage_residents.php");
        }
    } else {
        $_SESSION['error'] = "No resident ID provided for archiving.";
        header("Location: manage_residents.php");
    }
    exit;
    
} elseif ($action == 'restore') {
    // --- RESTORE RESIDENT FROM ARCHIVE ---
    if (isset($_GET['id'])) {
        $resident_id = mysqli_real_escape_string($link, $_GET['id']);
        
        // Get resident info for logging
        $info_sql = "SELECT first_name, last_name FROM residents WHERE id = ?";
        $resident_name = "ID: $resident_id";
        if ($info_stmt = mysqli_prepare($link, $info_sql)) {
            mysqli_stmt_bind_param($info_stmt, "i", $resident_id);
            mysqli_stmt_execute($info_stmt);
            $info_result = mysqli_stmt_get_result($info_stmt);
            if ($info_row = mysqli_fetch_assoc($info_result)) {
                $resident_name = $info_row['first_name'] . ' ' . $info_row['last_name'];
            }
            mysqli_stmt_close($info_stmt);
        }
        
        // Restore the resident
        $restore_sql = "UPDATE residents SET 
                       status = 'Active', 
                       archived_date = NULL, 
                       archived_by = NULL, 
                       archive_reason = NULL 
                       WHERE id = ?";
        
        if ($stmt = mysqli_prepare($link, $restore_sql)) {
            mysqli_stmt_bind_param($stmt, "i", $resident_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Log the activity
                $activity_desc = "Restored resident from archive: " . htmlspecialchars($resident_name, ENT_QUOTES, 'UTF-8');
                $activity_sql = "INSERT INTO activities (activity_description, activity_type) VALUES (?, 'Restore Resident')";
                if ($activity_stmt = mysqli_prepare($link, $activity_sql)) {
                    mysqli_stmt_bind_param($activity_stmt, "s", $activity_desc);
                    mysqli_stmt_execute($activity_stmt);
                    mysqli_stmt_close($activity_stmt);
                }
                
                $_SESSION['success'] = "Resident '$resident_name' has been restored from archive successfully!";
                $_SESSION['action_type'] = 'restore';
                header("Location: manage_residents.php");
            } else {
                $_SESSION['error'] = "Error restoring resident. Please try again.";
                header("Location: manage_residents.php");
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error'] = "Database error. Please try again.";
            header("Location: manage_residents.php");
        }
    } else {
        $_SESSION['error'] = "No resident ID provided for restoring.";
        header("Location: manage_residents.php");
    }
    exit;

} elseif ($action == 'delete') {
    // --- DELETE RESIDENT --- //
    // Check if the 'id' of the resident to be deleted is provided via GET request.
    if (isset($_GET['id'])) {
        $resident_id = mysqli_real_escape_string($link, $_GET['id']);

        // Optional: Fetch resident's name before deleting for a more descriptive activity log.
        // Using prepared statement for improved security
        $resident_name_for_log = "ID: " . htmlspecialchars($resident_id, ENT_QUOTES, 'UTF-8'); // Default log message part
        $name_sql = "SELECT first_name, last_name FROM residents WHERE id = ?";
        if ($name_stmt = mysqli_prepare($link, $name_sql)) {
            mysqli_stmt_bind_param($name_stmt, "i", $resident_id);
            mysqli_stmt_execute($name_stmt);
            mysqli_stmt_bind_result($name_stmt, $first_name_del, $last_name_del);
            if (mysqli_stmt_fetch($name_stmt)) {
                $resident_name_for_log = htmlspecialchars($first_name_del . ' ' . $last_name_del, ENT_QUOTES, 'UTF-8'); // Use actual name if found
            }
            mysqli_stmt_close($name_stmt);
        }

        // SQL query to delete a resident by ID.
        $sql = "DELETE FROM residents WHERE id = ?";

        // Prepare the SQL statement.
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind the resident ID parameter. 'i' denotes an integer.
            mysqli_stmt_bind_param($stmt, "i", $resident_id);
            
            // Execute the statement.
            if (mysqli_stmt_execute($stmt)) {
                // Record this activity using prepared statement for improved security.
                $activity_desc = "Deleted resident: " . $resident_name_for_log;
                $activity_type = "Delete Resident";
                $activity_sql = "INSERT INTO activities (activity_description, activity_type) VALUES (?, ?)";
                if ($activity_stmt = mysqli_prepare($link, $activity_sql)) {
                    mysqli_stmt_bind_param($activity_stmt, "ss", $activity_desc, $activity_type);
                    mysqli_stmt_execute($activity_stmt);
                    mysqli_stmt_close($activity_stmt);
                }
                // Redirect with a success message.
                $_SESSION['success'] = 'Resident deleted successfully!';
                header("Location: manage_residents.php");
            } else {
                $_SESSION['error'] = 'Database error: Could not delete resident.';
                error_log("Resident Delete DB Error: " . mysqli_error($link));
                header("Location: manage_residents.php");
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error'] = 'Database error: Could not prepare statement for delete.';
            error_log("Resident Delete Prepare Error: " . mysqli_error($link));
            header("Location: manage_residents.php");
        }
    } else {
        // Redirect if resident ID is missing.
        $_SESSION['error'] = 'Resident ID is missing for delete.';
        header("Location: manage_residents.php");
    }

} else {
    // If no valid action is specified, redirect to a default page or show an error.
    $_SESSION['error'] = 'Invalid action specified.';
    header("Location: manage_residents.php");
}

// Close database connection
mysqli_close($link);
exit;

?>
