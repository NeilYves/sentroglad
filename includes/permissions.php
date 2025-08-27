<?php
/**
 * User Permissions Helper Functions
 * Manages access control for the Barangay Management System
 */

require_once 'config.php';

/**
 * Check if user has permission to access a specific module
 * @param string $permission The permission to check (e.g., 'residents', 'officials', etc.)
 * @param mysqli $link Database connection
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($permission, $link) {
    // If user is not logged in, deny access
    if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
        return false;
    }

    $user_role = $_SESSION['role'];
    
    // Settings (password change) is automatically available to ALL logged-in users
    if ($permission === 'settings' || $permission === 'password_change' || $permission === 'system_settings') {
        return true;
    }
    
    // Barangay Secretary has full access to everything
    if ($user_role === 'Barangay Secretary') {
        return true;
    }
    
    // For other roles, check their specific permissions
    $user_id = $_SESSION['id'];
    $stmt = mysqli_prepare($link, "SELECT access_permissions FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $permissions_json);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    if ($permissions_json) {
        $permissions = json_decode($permissions_json, true);
        if (is_array($permissions) && in_array($permission, $permissions)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if user has any of the specified permissions
 * @param array $permissions Array of permissions to check
 * @param mysqli $link Database connection
 * @return bool True if user has at least one permission, false otherwise
 */
function hasAnyPermission($permissions, $link) {
    foreach ($permissions as $permission) {
        if (hasPermission($permission, $link)) {
            return true;
        }
    }
    return false;
}

/**
 * Get all permissions for the current user
 * @param mysqli $link Database connection
 * @return array Array of user permissions
 */
function getUserPermissions($link) {
    if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
        return [];
    }

    $user_role = $_SESSION['role'];
    
    // Barangay Secretary has all permissions
    if ($user_role === 'Barangay Secretary') {
        return ['dashboard', 'residents', 'officials', 'certificates', 'puroks', 'households', 'announcements', 'sms_blast', 'reports', 'settings'];
    }
    
    // For other roles, get their specific permissions and add settings automatically
    $user_id = $_SESSION['id'];
    $stmt = mysqli_prepare($link, "SELECT access_permissions FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $permissions_json);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    $permissions = [];
    if ($permissions_json) {
        $decoded_permissions = json_decode($permissions_json, true);
        if (is_array($decoded_permissions)) {
            $permissions = $decoded_permissions;
        }
    }
    
    // Always add settings permission for all users
    if (!in_array('settings', $permissions)) {
        $permissions[] = 'settings';
    }
    
    return $permissions;
}

/**
 * Redirect user if they don't have permission
 * @param string $permission The permission required
 * @param mysqli $link Database connection
 * @param string $redirect_url Where to redirect if no permission (default: index.php)
 */
function requirePermission($permission, $link, $redirect_url = 'index.php') {
    if (!hasPermission($permission, $link)) {
        $_SESSION['error_message'] = 'You do not have permission to access this page.';
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Check if user is admin (Barangay Secretary)
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Barangay Secretary';
}

/**
 * Get user role display name with badge styling
 * @return string HTML formatted role badge
 */
function getUserRoleBadge() {
    if (!isset($_SESSION['role'])) {
        return '<span class="badge bg-secondary">Guest</span>';
    }
    
    $role = $_SESSION['role'];
    
    if ($role === 'Barangay Secretary') {
        return '<span class="badge bg-primary">Administrator</span>';
    } elseif ($role === 'staff') {
        return '<span class="badge bg-info">Staff</span>';
    } else {
        return '<span class="badge bg-success">' . html_escape($role) . '</span>';
    }
}

/**
 * Get navigation menu items based on user permissions
 * @param mysqli $link Database connection
 * @return array Array of menu items the user can access
 */
function getAccessibleMenuItems($link) {
    $menu_items = [];
    
    // Dashboard should only be accessible if user has dashboard permission
    if (hasPermission('dashboard', $link)) {
        $menu_items['dashboard'] = [
            'title' => 'Dashboard',
            'url' => 'index.php',
            'icon' => 'fas fa-tachometer-alt'
        ];
    }
    
    // Check permissions for each menu item
    if (hasPermission('residents', $link)) {
        $menu_items['residents'] = [
            'title' => 'Residents',
            'url' => 'manage_residents.php',
            'icon' => 'fas fa-users'
        ];
    }
    
    if (hasPermission('officials', $link)) {
        $menu_items['officials'] = [
            'title' => 'Officials',
            'url' => 'manage_officials.php',
            'icon' => 'fas fa-user-tie'
        ];
    }
    
    if (hasPermission('certificates', $link)) {
        $menu_items['certificates'] = [
            'title' => 'Certificates',
            'url' => 'manage_certificates.php',
            'icon' => 'fas fa-certificate'
        ];
    }
    
    if (hasPermission('puroks', $link)) {
        $menu_items['puroks'] = [
            'title' => 'Puroks',
            'url' => 'manage_puroks.php',
            'icon' => 'fas fa-map-marker-alt'
        ];
    }
    
    if (hasPermission('households', $link)) {
        $menu_items['households'] = [
            'title' => 'Households',
            'url' => 'manage_households.php',
            'icon' => 'fas fa-home'
        ];
    }
    
    /*
    if (hasPermission('announcements', $link)) {
        $menu_items['announcements'] = [
            'title' => 'Announcements',
            'url' => 'manage_announcements.php',
            'icon' => 'fas fa-bullhorn'
        ];
    }
    */
    
    if (hasPermission('sms_blast', $link)) {
        $menu_items['sms'] = [
            'title' => 'SMS Blast',
            'url' => 'sms_blast.php',
            'icon' => 'fas fa-sms'
        ];
    }
    
    if (hasPermission('reports', $link)) {
        $menu_items['reports'] = [
            'title' => 'Reports & History Log',
            'url' => 'history_log.php',
            'icon' => 'fas fa-chart-bar'
        ];
    }
    
    // Settings is automatically accessible to ALL logged-in users (for password changes)
    $menu_items['settings'] = [
        'title' => 'Settings',
        'url' => 'system_settings.php',
        'icon' => 'fas fa-cogs'
    ];
    
    return $menu_items;
}
?>