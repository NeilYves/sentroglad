<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php'; 
require_once __DIR__ . '/permissions.php'; 

// Determine current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch system settings
$system_settings = [];
$settings_query = "SELECT setting_key, setting_value FROM system_settings";
$settings_result = mysqli_query($link, $settings_query);
if ($settings_result) {
    while ($row = mysqli_fetch_assoc($settings_result)) {
        $system_settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Barangay info
$barangay_name = $system_settings['barangay_name'] ?? 'Barangay Management System';
$logo_path = !empty($system_settings['barangay_logo_path']) && file_exists($system_settings['barangay_logo_path']) 
    ? $system_settings['barangay_logo_path'] 
    : rtrim(dirname($_SERVER['PHP_SELF']), '/\\\\') . '/images/barangay-logo.png';

// User info
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Barangay Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo rtrim(dirname($_SERVER['PHP_SELF']), '/\\') ?: ''; ?>/styles.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0 sidebar" id="sidebar">
            <div class="text-center py-4 sidebar-header">
                <img src="<?php echo $logo_path; ?>" alt="<?php echo htmlspecialchars($barangay_name); ?>" class="sidebar-logo">
                <h5 class="mt-2 text-white"><?php echo htmlspecialchars($barangay_name); ?></h5>
                <!-- User info -->
                <div class="mt-3 px-3">
                    <div class="user-info-card bg-dark rounded p-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle text-light me-2" style="font-size: 1.5em;"></i>
                            <div class="text-start">
                                <div class="text-white fw-bold" style="font-size: 0.85em;"><?php echo htmlspecialchars($username); ?></div>
                                <div style="font-size: 0.75em;"><?php echo getUserRoleBadge(); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <div class="sidebar-menu">

                <!-- Dashboard -->
                <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>

                <!-- Households -->
                <?php if (hasPermission('households', $link)): ?>
                    <a href="manage_households.php" class="<?php echo ($current_page == 'manage_households.php') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Households
                    </a>
                <?php endif; ?>

                <!-- Residents -->
                <?php if (hasPermission('residents', $link)): ?>
                    <a href="manage_residents.php" class="<?php echo ($current_page == 'manage_residents.php') ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Residents
                    </a>
                <?php endif; ?>

                <!-- Puroks -->
                <?php if (hasPermission('puroks', $link)): ?>
                    <a href="manage_puroks.php" class="<?php echo ($current_page == 'manage_puroks.php') ? 'active' : ''; ?>">
                        <i class="fas fa-map-marker-alt"></i> Puroks
                    </a>
                <?php endif; ?>

                <!-- Purok Details -->
                <?php if (hasPermission('residents', $link)): ?>
                    <a href="purok_details.php" class="<?php echo ($current_page == 'purok_details.php') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i> Purok Details
                    </a>
                <?php endif; ?>

                <!-- Officials -->
                <?php if (hasPermission('officials', $link)): ?>
                    <a href="manage_officials.php" class="<?php echo ($current_page == 'manage_officials.php') ? 'active' : ''; ?>">
                        <i class="fas fa-user-tie"></i> Officials
                    </a>
                <?php endif; ?>

                <!-- Certificates -->
                <?php if (hasPermission('certificates', $link)): ?>
                    <a href="manage_certificates.php" class="<?php echo in_array($current_page, ['manage_certificates.php','issue_certificate_form.php','certificate_handler.php']) ? 'active' : ''; ?>">
                        <i class="fas fa-certificate"></i> Certificates
                    </a>
                <?php endif; ?>

                <!-- SMS Blast -->
                <?php if (hasPermission('sms_blast', $link)): ?>
                    <a href="sms_blast.php" class="<?php echo ($current_page == 'sms_blast.php') ? 'active' : ''; ?>">
                        <i class="fas fa-comment-dots"></i> SMS Blast
                    </a>
                <?php endif; ?>

                <!-- Reports -->
                

                <!-- History Log -->
                <?php if (hasPermission('reports', $link) && isAdmin()): ?>
                    <a href="history_log.php" class="<?php echo ($current_page == 'history_log.php') ? 'active' : ''; ?>">
                        <i class="fas fa-history"></i> History Log
                    </a>
                <?php endif; ?>

                <!-- Settings -->
                <?php if (hasPermission('settings', $link)): ?>
                    <a href="system_settings.php" class="<?php echo ($current_page == 'system_settings.php') ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                <?php endif; ?>
                <?php if (hasPermission('reports', $link)): ?>
                    <a href="about.php" class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                        <i class="fas fa-question"></i> About
                <?php endif; ?>

                <!-- Logout -->
                <a href="logout.php" class="sidebar-logout-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>

            </div>
        </div>

            <!-- Main Content Start -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">

                <!-- Global Loading Overlay -->
                <div id="globalLoadingOverlay" class="loading-overlay">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                
                <!-- Display success/error messages if they exist -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <?php 
                        echo html_escape($_SESSION['success_message']);
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <?php 
                        echo html_escape($_SESSION['error_message']);
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
