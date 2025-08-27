<?php
$page_title = 'System Settings';
require_once 'includes/header.php';

// Function to handle logo uploads
function handle_logo_upload($file_input_name, $setting_key, $link, &$message) {
    $upload_dir = 'images/';
    $allowed_logo_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_logo_size = 2 * 1024 * 1024; // 2MB

    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$file_input_name];

        if (!in_array($file['type'], $allowed_logo_types)) {
            $message = '<div class="alert alert-danger">Invalid file type for ' . html_escape($file_input_name) . '. Only JPG, PNG, and GIF are allowed.</div>';
            return;
        }

        if ($file['size'] > $max_logo_size) {
            $message = '<div class="alert alert-danger">File size for ' . html_escape($file_input_name) . ' exceeds the 2MB limit.</div>';
            return;
        }

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid($setting_key . '_', true) . '.' . $file_extension;
        $destination = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Remove old logo if it exists
            $old_logo_query = mysqli_prepare($link, "SELECT setting_value FROM system_settings WHERE setting_key = ?");
            mysqli_stmt_bind_param($old_logo_query, "s", $setting_key);
            mysqli_stmt_execute($old_logo_query);
            mysqli_stmt_bind_result($old_logo_query, $old_logo_path);
            if (mysqli_stmt_fetch($old_logo_query) && !empty($old_logo_path) && file_exists($old_logo_path)) {
                unlink($old_logo_path);
            }
            mysqli_stmt_close($old_logo_query);

            // Update database with new logo path
            $path_value = mysqli_real_escape_string($link, $destination);
            $upsert_sql = "REPLACE INTO system_settings (setting_key, setting_value) VALUES (?, ?)";
            $upsert_stmt = mysqli_prepare($link, $upsert_sql);
            mysqli_stmt_bind_param($upsert_stmt, "ss", $setting_key, $path_value);
            
            if (mysqli_stmt_execute($upsert_stmt)) {
                $message = '<div class="alert alert-success">Logo uploaded successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Database error on logo update: ' . mysqli_error($link) . '</div>';
            }
            mysqli_stmt_close($upsert_stmt);
        } else {
            $message = '<div class="alert alert-danger">Failed to move uploaded file. Check permissions for ' . $upload_dir . ' directory.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Please choose a file to upload.</div>';
    }
}

// The page is now accessible to staff for password changes.
// We'll use the role to conditionally display content.
$user_id = $_SESSION['id'] ?? 0;
$user_role = $_SESSION['role'] ?? 'staff';

$password_message = '';
$user_message = ''; // For user management messages
$message = ''; // For existing settings messages

// Handle Password Change
if (isset($_POST['change_password'])) {
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $password_message = '<div class="alert alert-warning">Please fill in all password fields.</div>';
    } else {
        $stmt = mysqli_prepare($link, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $current_db_password);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($current_db_password && $old_password === $current_db_password) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) < 8) {
                    $password_message = '<div class="alert alert-danger">Password must be at least 8 characters long.</div>';
                } else {
                    $update_stmt = mysqli_prepare($link, "UPDATE users SET password = ? WHERE id = ?");
                    mysqli_stmt_bind_param($update_stmt, "si", $new_password, $user_id);
                    if (mysqli_stmt_execute($update_stmt)) {
                        $password_message = '<div class="alert alert-success">Password updated successfully.</div>';
                    } else {
                        $password_message = '<div class="alert alert-danger">Error updating password. Please try again.</div>';
                    }
                    mysqli_stmt_close($update_stmt);
                }
            } else {
                $password_message = '<div class="alert alert-danger">New password and confirmation password do not match.</div>';
            }
        } else {
            $password_message = '<div class="alert alert-danger">Incorrect old password.</div>';
        }
    }
}

// Handle User Management (Only for Barangay Secretary)
if ($user_role === 'Barangay Secretary' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Add User
    if (isset($_POST['add_user'])) {
        $new_username = trim($_POST['new_username']);
        $new_password = trim($_POST['new_password']);
        $new_role = trim($_POST['new_role']);
        $access_permissions = isset($_POST['access_permissions']) ? $_POST['access_permissions'] : [];
        
        if (empty($new_username) || empty($new_password) || empty($new_role)) {
            $user_message = '<div class="alert alert-warning">Please fill in all required fields.</div>';
        } elseif (strlen($new_password) < 8) {
            $user_message = '<div class="alert alert-danger">Password must be at least 8 characters long.</div>';
        } else {
            // Check if username already exists
            $check_stmt = mysqli_prepare($link, "SELECT id FROM users WHERE username = ?");
            mysqli_stmt_bind_param($check_stmt, "s", $new_username);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            
            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $user_message = '<div class="alert alert-danger">Username already exists. Please choose a different username.</div>';
            } else {
                // Auto-grant essential permissions
                $auto_granted_permissions = ['dashboard', 'residents', 'households', 'puroks'];
                $access_permissions = array_unique(array_merge($access_permissions, $auto_granted_permissions));

                // Create access permissions JSON
                $permissions_json = json_encode($access_permissions);

                // Insert new user
                $insert_stmt = mysqli_prepare($link, "INSERT INTO users (username, password, role, access_permissions) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($insert_stmt, "ssss", $new_username, $new_password, $new_role, $permissions_json);
                
                if (mysqli_stmt_execute($insert_stmt)) {
                    $user_message = '<div class="alert alert-success">User "' . html_escape($new_username) . '" created successfully with role "' . html_escape($new_role) . '"!<br><small><i class="fas fa-info-circle me-1"></i><strong>Auto-granted permissions:</strong> Dashboard Access, Residents Management, Household Management, Puroks Management, and Settings for password changes.</small></div>';
                    
                    // Log the activity
                    $activity_desc = "New user created: " . $new_username . " with role: " . $new_role;
                    $activity_stmt = mysqli_prepare($link, "INSERT INTO activities (activity_description, activity_type, user_id) VALUES (?, 'User Management', ?)");
                    mysqli_stmt_bind_param($activity_stmt, "si", $activity_desc, $user_id);
                    mysqli_stmt_execute($activity_stmt);
                    mysqli_stmt_close($activity_stmt);
                } else {
                    $user_message = '<div class="alert alert-danger">Error creating user. Please try again.</div>';
                }
                mysqli_stmt_close($insert_stmt);
            }
            mysqli_stmt_close($check_stmt);
        }
    }
    
    // Handle Delete User
    if (isset($_POST['delete_user'])) {
        $delete_user_id = intval($_POST['delete_user_id']);
        
        // Prevent deleting own account
        if ($delete_user_id === $user_id) {
            $user_message = '<div class="alert alert-danger">You cannot delete your own account.</div>';
        } else {
            // Get username for logging
            $username_stmt = mysqli_prepare($link, "SELECT username FROM users WHERE id = ?");
            mysqli_stmt_bind_param($username_stmt, "i", $delete_user_id);
            mysqli_stmt_execute($username_stmt);
            mysqli_stmt_bind_result($username_stmt, $deleted_username);
            mysqli_stmt_fetch($username_stmt);
            mysqli_stmt_close($username_stmt);
            
            // Delete user
            $delete_stmt = mysqli_prepare($link, "DELETE FROM users WHERE id = ?");
            mysqli_stmt_bind_param($delete_stmt, "i", $delete_user_id);
            
            if (mysqli_stmt_execute($delete_stmt)) {
                $user_message = '<div class="alert alert-success">User "' . html_escape($deleted_username) . '" deleted successfully!</div>';
                
                // Log the activity
                $activity_desc = "User deleted: " . $deleted_username;
                $activity_stmt = mysqli_prepare($link, "INSERT INTO activities (activity_description, activity_type, user_id) VALUES (?, 'User Management', ?)");
                mysqli_stmt_bind_param($activity_stmt, "si", $activity_desc, $user_id);
                mysqli_stmt_execute($activity_stmt);
                mysqli_stmt_close($activity_stmt);
            } else {
                $user_message = '<div class="alert alert-danger">Error deleting user. Please try again.</div>';
            }
            mysqli_stmt_close($delete_stmt);
        }
    }
    
    // Handle existing settings updates
    if (isset($_POST['upload_barangay_logo'])) {
        handle_logo_upload('barangay_logo', 'barangay_logo_path', $link, $message);
    } elseif (isset($_POST['upload_municipality_logo'])) {
        handle_logo_upload('municipality_logo', 'municipality_logo_path', $link, $message);
    } elseif (isset($_POST['save_settings'])) {
        $allowed_keys = ['barangay_name', 'barangay_address_line1', 'barangay_address_line2', 'current_punong_barangay_id', 'default_certificate_fee', 'barangay_seal_text', 'municipality_seal_text'];
        $errors = [];
        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $value = mysqli_real_escape_string($link, $_POST[$key]);
                $upsert_sql = "REPLACE INTO system_settings (setting_key, setting_value) VALUES ('$key', '$value')";
                if (!mysqli_query($link, $upsert_sql)) {
                    $errors[] = "Error updating $key: " . mysqli_error($link);
                }
            }
        }
        if (empty($errors)) {
            $message = '<div class="alert alert-success" role="alert">System settings updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger" role="alert">Error updating settings: <br>' . implode('<br>', $errors) . '</div>';
        }
    }
}

// Add access_permissions column to users table if it doesn't exist
$check_column = mysqli_query($link, "SHOW COLUMNS FROM users LIKE 'access_permissions'");
if (mysqli_num_rows($check_column) == 0) {
    mysqli_query($link, "ALTER TABLE users ADD COLUMN access_permissions TEXT NULL AFTER role");
}

// Update existing Barangay Secretary users to have full permissions including dashboard
mysqli_query($link, "UPDATE users SET access_permissions = '[\"dashboard\", \"residents\", \"officials\", \"certificates\", \"puroks\", \"households\", \"announcements\", \"sms_blast\", \"reports\"]'
WHERE role = 'Barangay Secretary' AND (access_permissions IS NULL OR access_permissions = '' OR access_permissions NOT LIKE '%dashboard%')");

// Update all existing users to have essential permissions (dashboard, residents, households, puroks)
$essential_permissions_query = "
UPDATE users
SET access_permissions = CASE
    WHEN access_permissions IS NULL OR access_permissions = '' THEN
        '[\"dashboard\", \"residents\", \"households\", \"puroks\"]'
    ELSE
        (SELECT DISTINCT JSON_MERGE_PRESERVE(
            access_permissions,
            '[\"dashboard\", \"residents\", \"households\", \"puroks\"]'
        ))
END
WHERE role != 'Barangay Secretary'
AND (access_permissions IS NULL
     OR access_permissions = ''
     OR access_permissions NOT LIKE '%dashboard%'
     OR access_permissions NOT LIKE '%residents%'
     OR access_permissions NOT LIKE '%households%'
     OR access_permissions NOT LIKE '%puroks%')";

// For MySQL versions that don't support JSON_MERGE_PRESERVE, use a simpler approach
$users_to_update = mysqli_query($link, "SELECT id, access_permissions FROM users WHERE role != 'Barangay Secretary'");
if ($users_to_update) {
    while ($user = mysqli_fetch_assoc($users_to_update)) {
        $current_permissions = json_decode($user['access_permissions'], true) ?: [];
        $essential = ['dashboard', 'residents', 'households', 'puroks'];
        $updated_permissions = array_unique(array_merge($current_permissions, $essential));
        $updated_json = json_encode($updated_permissions);

        $update_stmt = mysqli_prepare($link, "UPDATE users SET access_permissions = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "si", $updated_json, $user['id']);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    }
}

// Fetch settings data (needed for both roles for layout, but only editable by secretary)
$settings_data = [];
$settings_sql = "SELECT setting_key, setting_value FROM system_settings";
$settings_result = mysqli_query($link, $settings_sql);
if ($settings_result) {
    while ($row = mysqli_fetch_assoc($settings_result)) {
        $settings_data[$row['setting_key']] = $row['setting_value'];
    }
}

// Fetch officials for Punong Barangay dropdown
$officials_sql = "SELECT id, fullname, position FROM officials WHERE position LIKE '%Punong Barangay%' OR position LIKE '%Captain%' ORDER BY fullname ASC";
$officials_result = mysqli_query($link, $officials_sql);

// Fetch all users for user management
$users_sql = "SELECT id, username, role, access_permissions, created_at FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($link, $users_sql);

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="dashboard-title"><i class="fas fa-cogs me-2"></i><?php echo html_escape($page_title); ?></h1>
</div>

<?php 
if ($user_role === 'Barangay Secretary') {
    echo $message; // Display settings update messages
    echo $user_message; // Display user management messages
}
echo $password_message; // Display password change messages 
?>

<!-- Change Password Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Your Password</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="system_settings.php">
            <div class="mb-3 row">
                <label for="old_password" class="col-sm-3 col-form-label">Old Password</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" id="old_password" name="old_password" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label for="new_password" class="col-sm-3 col-form-label">New Password</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    <small class="form-text text-muted">Must be at least 8 characters long.</small>
                </div>
            </div>
            <div class="mb-3 row">
                <label for="confirm_password" class="col-sm-3 col-form-label">Confirm New Password</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" name="change_password" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($user_role === 'Barangay Secretary'): ?>
    <!-- User Management Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>User Management</h5>
        </div>
        <div class="card-body">
            <!-- Add New User Form -->
            <h6 class="card-subtitle mb-3 text-muted">Add New User</h6>
            <form method="POST" action="system_settings.php">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="new_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="new_username" name="new_username" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="form-text text-muted">Minimum 8 characters</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="new_role" class="form-label">Role</label>
                            <select class="form-select" id="new_role" name="new_role" required>
                                <option value="">Select Role</option>
                                <option value="Barangay Officials">Barangay Officials</option>
                                <option value="Barangay Staff">Barangay Staff</option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Officials:</strong> Elected/appointed barangay officials<br>
                                <strong>Staff:</strong> Administrative and support staff
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Access Permissions</label>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Auto-granted permissions:</strong> All users automatically receive <strong>Dashboard Access</strong>, <strong>Residents Management</strong>, <strong>Household Management</strong>, <strong>Puroks Management</strong>, and <strong>Settings</strong> access. These are essential permissions for basic system functionality.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="access_permissions[]" value="dashboard" id="access_dashboard" checked>
                                <label class="form-check-label" for="access_dashboard">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard Access <span class="badge bg-success ms-1">Auto-granted</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="access_permissions[]" value="residents" id="access_residents" checked>
                                <label class="form-check-label" for="access_residents">
                                    <i class="fas fa-users me-1"></i>Residents Management <span class="badge bg-success ms-1">Auto-granted</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="access_permissions[]" value="officials" id="access_officials">
                                <label class="form-check-label" for="access_officials">
                                    <i class="fas fa-user-tie me-1"></i>Officials Management
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="access_permissions[]" value="certificates" id="access_certificates">
                                <label class="form-check-label" for="access_certificates">
                                    <i class="fas fa-certificate me-1"></i>Certificates Management
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="access_permissions[]" value="puroks" id="access_puroks" checked>
                                <label class="form-check-label" for="access_puroks">
                                    <i class="fas fa-map-marker-alt me-1"></i>Puroks Management <span class="badge bg-success ms-1">Auto-granted</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="access_permissions[]" value="households" id="access_households" checked>
                                <label class="form-check-label" for="access_households">
                                    <i class="fas fa-home me-1"></i>Household Management <span class="badge bg-success ms-1">Auto-granted</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="access_permissions[]" value="announcements" id="access_announcements">
                                <label class="form-check-label" for="access_announcements">
                                    <i class="fas fa-bullhorn me-1"></i>Announcements
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="access_permissions[]" value="sms_blast" id="access_sms">
                                <label class="form-check-label" for="access_sms">
                                    <i class="fas fa-sms me-1"></i>SMS Blast
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="access_permissions[]" value="reports" id="access_reports">
                                <label class="form-check-label" for="access_reports">
                                    <i class="fas fa-chart-bar me-1"></i>Reports & History Log
                                </label>
                            </div>
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked disabled>
                                    <label class="form-check-label text-muted">
                                        <i class="fas fa-cogs me-1"></i>Settings (Auto-granted)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" name="add_user" class="btn btn-success">
                        <i class="fas fa-user-plus me-2"></i>Add User
                    </button>
                </div>
            </form>

            <!-- Role Information -->
            <div class="alert alert-light mt-3">
                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Role Descriptions</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><span class="badge bg-warning text-dark me-2">Barangay Officials</span></p>
                        <small class="text-muted">Elected or appointed barangay officials (Kagawads, SK Chairman, etc.). Typically have broader access to management functions.</small>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><span class="badge bg-info me-2">Barangay Staff</span></p>
                        <small class="text-muted">Administrative and support staff (Secretary assistants, clerks, etc.). Usually have focused access to specific functions.</small>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Existing Users List -->
            <h6 class="card-subtitle mb-3 text-muted">Existing Users</h6>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Access Permissions</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users_result && mysqli_num_rows($users_result) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo html_escape($user['username']); ?></strong>
                                        <?php if ($user['id'] == $user_id): ?>
                                            <span class="badge bg-primary ms-1">You</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $role_badge_class = 'bg-secondary';
                                        if ($user['role'] === 'Barangay Secretary') {
                                            $role_badge_class = 'bg-primary';
                                        } elseif ($user['role'] === 'Barangay Officials') {
                                            $role_badge_class = 'bg-warning text-dark';
                                        } elseif ($user['role'] === 'Barangay Staff') {
                                            $role_badge_class = 'bg-info';
                                        }
                                        ?>
                                        <span class="badge <?php echo $role_badge_class; ?>"><?php echo html_escape($user['role']); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $permissions = json_decode($user['access_permissions'], true);
                                        $auto_granted = ['dashboard', 'residents', 'households', 'puroks'];

                                        if (is_array($permissions) && !empty($permissions)) {
                                            foreach ($permissions as $permission) {
                                                $badge_class = in_array($permission, $auto_granted) ? 'bg-success' : 'bg-info';
                                                $title = in_array($permission, $auto_granted) ? 'title="Auto-granted essential permission"' : '';
                                                echo '<span class="badge ' . $badge_class . ' me-1" ' . $title . '>' . html_escape(ucfirst($permission)) . '</span>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">No specific permissions</span>';
                                        }
                                        // Always show settings as auto-granted
                                        echo '<span class="badge bg-secondary me-1" title="Automatically granted to all users">Settings (Auto)</span>';
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['id'] != $user_id): ?>
                                            <form method="POST" action="system_settings.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">Current User</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Barangay Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="system_settings.php" enctype="multipart/form-data">
                <div class="mb-3 row">
                    <label for="barangay_name" class="col-sm-3 col-form-label">Barangay Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="barangay_name" name="barangay_name" value="<?php echo html_escape($settings_data['barangay_name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="barangay_address_line1" class="col-sm-3 col-form-label">Address Line 1</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="barangay_address_line1" name="barangay_address_line1" value="<?php echo html_escape($settings_data['barangay_address_line1'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="barangay_address_line2" class="col-sm-3 col-form-label">Address Line 2</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="barangay_address_line2" name="barangay_address_line2" value="<?php echo html_escape($settings_data['barangay_address_line2'] ?? ''); ?>" placeholder="e.g., City, Province">
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="barangay_logo" class="col-sm-3 col-form-label">Barangay Logo</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" id="barangay_logo" name="barangay_logo">
                        <?php if (!empty($settings_data['barangay_logo_path'])): ?>
                            <small class="form-text text-muted mt-2 d-block">
                                Current: <img src="<?php echo html_escape($settings_data['barangay_logo_path']); ?>?t=<?php echo time(); ?>" alt="Current Logo" style="max-height: 50px; margin-left: 10px; vertical-align: middle;">
                            </small>
                        <?php endif; ?>
                        <button type="submit" name="upload_barangay_logo" class="btn btn-sm btn-outline-primary mt-2">Upload Logo</button>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="municipality_logo" class="col-sm-3 col-form-label">Municipality Logo</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" id="municipality_logo" name="municipality_logo">
                        <?php if (!empty($settings_data['municipality_logo_path'])): ?>
                            <small class="form-text text-muted mt-2 d-block">
                                Current: <img src="<?php echo html_escape($settings_data['municipality_logo_path']); ?>?t=<?php echo time(); ?>" alt="Current Municipality Logo" style="max-height: 50px; margin-left: 10px; vertical-align: middle;">
                            </small>
                        <?php endif; ?>
                        <button type="submit" name="upload_municipality_logo" class="btn btn-sm btn-outline-primary mt-2">Upload Municipality Logo</button>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="barangay_seal_text" class="col-sm-3 col-form-label">Barangay Seal Text</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="barangay_seal_text" name="barangay_seal_text" value="<?php echo html_escape($settings_data['barangay_seal_text'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="municipality_seal_text" class="col-sm-3 col-form-label">Municipality Seal Text</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="municipality_seal_text" name="municipality_seal_text" value="<?php echo html_escape($settings_data['municipality_seal_text'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="current_punong_barangay_id" class="col-sm-3 col-form-label">Current Punong Barangay</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="current_punong_barangay_id" name="current_punong_barangay_id">
                            <option value="">Select Punong Barangay</option>
                            <?php 
                            if ($officials_result && mysqli_num_rows($officials_result) > 0) {
                                while ($official = mysqli_fetch_assoc($officials_result)) {
                                    $selected = (isset($settings_data['current_punong_barangay_id']) && $settings_data['current_punong_barangay_id'] == $official['id']) ? 'selected' : '';
                                    echo '<option value="' . html_escape($official['id']) . '" ' . $selected . '>' . html_escape($official['fullname']) . ' (' . html_escape($official['position']) . ')</option>';
                                }
                            }
                            ?>
                        </select>
                        <small class="form-text text-muted">Select the currently active Punong Barangay. This will be used as the signatory on certificates.</small>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="default_certificate_fee" class="col-sm-3 col-form-label">Default Certificate Fee</label>
                    <div class="col-sm-9">
                        <input type="number" step="0.01" class="form-control" id="default_certificate_fee" name="default_certificate_fee" value="<?php echo html_escape($settings_data['default_certificate_fee'] ?? '0.00'); ?>">
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" name="save_settings" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
