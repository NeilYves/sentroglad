<?php 
$page_title = 'Dashboard'; // Set the page title for the header
require_once 'includes/header.php'; // Include the header which starts the session
require_once 'includes/permissions.php'; // Include permissions helper

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true){
    header("location: login.php");
    exit;
}


// Check if user has permission to access dashboard
if (!hasPermission('dashboard', $link)) {
    $_SESSION['error_message'] = 'You do not have permission to access the dashboard.';
    header("location: system_settings.php"); // Redirect to settings page (which all users can access)
    exit;
}

// Database queries remain the same as they are specific to the dashboard

// Fetch Total Population
// Fetch Total Residents
$total_residents_query = "SELECT COUNT(id) AS total FROM residents";
$total_residents_result = mysqli_query($link, $total_residents_query);
if (!$total_residents_result) {
    error_log("Database query error: " . mysqli_error($link) . " in query: " . $total_residents_query);
}
$total_residents_row = mysqli_fetch_assoc($total_residents_result);
$total_residents = $total_residents_row['total'] ?? 0;

// Fetch Total Officials (excluding Ex- officials)
$total_officials_query = "SELECT COUNT(id) AS total FROM officials WHERE position NOT LIKE 'Ex-%'";
$total_officials_result = mysqli_query($link, $total_officials_query);
if (!$total_officials_result) {
    error_log("Database query error: " . mysqli_error($link) . " in query: " . $total_officials_query);
}
$total_officials_row = mysqli_fetch_assoc($total_officials_result);
$total_officials = $total_officials_row['total'] ?? 0;

// Calculate Total Population (Residents + Officials)
$total_population = $total_residents + $total_officials;

// Debugging - For troubleshooting only, remove in production
// error_log("DEBUG: Residents: {$total_residents}, Officials: {$total_officials}, Total: {$total_population}");

// Fetch Male Population
$male_population_result = mysqli_query($link, "SELECT COUNT(id) AS total_male FROM residents WHERE gender = 'Male'");
$male_population_row = mysqli_fetch_assoc($male_population_result);
$male_population = $male_population_row['total_male'] ?? 0;

// Fetch Female Population
$female_population_result = mysqli_query($link, "SELECT COUNT(id) AS total_female FROM residents WHERE gender = 'Female'");
$female_population_row = mysqli_fetch_assoc($female_population_result);
$female_population = $female_population_row['total_female'] ?? 0;

// Fetch Age Demographics
// Child: 0-12 years
$child_population_result = mysqli_query($link, "SELECT COUNT(id) AS total_child FROM residents WHERE TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 0 AND 12");
$child_population_row = mysqli_fetch_assoc($child_population_result);
$child_population = $child_population_row['total_child'] ?? 0;

// Youth: 13-24 years
$youth_population_result = mysqli_query($link, "SELECT COUNT(id) AS total_youth FROM residents WHERE TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 13 AND 24");
$youth_population_row = mysqli_fetch_assoc($youth_population_result);
$youth_population = $youth_population_row['total_youth'] ?? 0;

// Adult: 25-59 years
$adult_population_result = mysqli_query($link, "SELECT COUNT(id) AS total_adult FROM residents WHERE TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 25 AND 59");
$adult_population_row = mysqli_fetch_assoc($adult_population_result);
$adult_population = $adult_population_row['total_adult'] ?? 0;

// Senior: 60+ years
$senior_population_result = mysqli_query($link, "SELECT COUNT(id) AS total_senior FROM residents WHERE TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60");
$senior_population_row = mysqli_fetch_assoc($senior_population_result);
$senior_population = $senior_population_row['total_senior'] ?? 0;

// Fetch Total Households
$total_households_result = mysqli_query($link, "SELECT COUNT(id) AS total FROM households");
$total_households_row = mysqli_fetch_assoc($total_households_result);
$total_households = $total_households_row['total'] ?? 0;

// Fetch Archived Residents Count
$archived_residents_result = mysqli_query($link, "SELECT COUNT(id) AS total FROM residents WHERE status = 'Archived'");
$archived_residents_row = mysqli_fetch_assoc($archived_residents_result);
$archived_residents = $archived_residents_row['total'] ?? 0;

// Fetch System Settings
$all_settings_query = "SELECT setting_key, setting_value FROM system_settings";
$all_settings_result = mysqli_query($link, $all_settings_query);

$db_settings = [];
if ($all_settings_result) {
    while ($row = mysqli_fetch_assoc($all_settings_result)) {
        $db_settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Initialize settings with defaults
$settings = [
    'barangay_name' => !empty($db_settings['barangay_name']) ? $db_settings['barangay_name'] : 'Barangay Name Not Set',
    'logo_path' => !empty($db_settings['barangay_logo_path']) ? $db_settings['barangay_logo_path'] : 'assets/images/default_logo.png', // Default logo
    'barangay_seal_text' => !empty($db_settings['barangay_seal_text']) ? $db_settings['barangay_seal_text'] : 'OFFICIAL SEAL',
    'municipality_seal_text' => !empty($db_settings['municipality_seal_text']) ? $db_settings['municipality_seal_text'] : 'MUNICIPALITY SEAL'
];

// Construct full address
$address_parts = [];
if (!empty($db_settings['barangay_address_line1'])) {
    $address_parts[] = $db_settings['barangay_address_line1'];
}
if (!empty($db_settings['barangay_address_line2'])) { // This key might contain city, province as per your SQL dump
    $address_parts[] = $db_settings['barangay_address_line2'];
}
// You might have other keys like 'barangay_city', 'barangay_province' if you decide to separate them later
// For now, using barangay_address_line1 and barangay_address_line2

$settings['full_address'] = !empty($address_parts) ? implode(', ', $address_parts) : 'Address Not Set';

// Final check for logo path to ensure it's not empty and defaults if necessary
if (empty($settings['logo_path'])) {
    $settings['logo_path'] = 'assets/images/default_logo.png';
}
// error_log('Fetched settings: ' . print_r($settings, true)); // For debugging

// Fetch Barangay Officials
$officials_result = mysqli_query($link, "SELECT fullname, position FROM officials ORDER BY display_order ASC, fullname ASC LIMIT 5");

// Fetch Recent Activities
$activities_result = mysqli_query($link, "SELECT activity_description, activity_type, timestamp FROM activities ORDER BY timestamp DESC LIMIT 5");

// Fetch Announcements
$announcements_result = mysqli_query($link, "SELECT id, title, content, event_date, publish_date FROM announcements WHERE is_active = 1 ORDER BY publish_date DESC LIMIT 3");

// Fetch system settings for display
$settings_data = [];
$settings_query = "SELECT setting_key, setting_value FROM system_settings";
$settings_result = mysqli_query($link, $settings_query);
if ($settings_result) {
    while ($row = mysqli_fetch_assoc($settings_result)) {
        $settings_data[$row['setting_key']] = $row['setting_value'];
    }
}

// Format full address
$address_line1 = $settings_data['barangay_address_line1'] ?? '';
$address_line2 = $settings_data['barangay_address_line2'] ?? '';
$full_address = trim($address_line1 . (!empty($address_line1) && !empty($address_line2) ? ', ' : '') . $address_line2);
// Fetch announcements
$query = "SELECT * FROM announcements ORDER BY id DESC";
$result = mysqli_query($link, $query);

$announcements_result = mysqli_query($link, "SELECT * FROM announcements ORDER BY publish_date DESC");

// Store message and clear it after displaying
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
$showAnnouncement = $_SESSION['show_announcement_section'] ?? false;
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['show_announcement_section']);


?>
<!-- The HTML body, sidebar, and start of main content are now in header.php -->
                 <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="dashboard-title">Dashboard</h1>
                    <div class="container">
  <!-- Flash Messages -->
<?php if (!empty($successMessage)): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo $successMessage; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (!empty($errorMessage)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo $errorMessage; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>





<!-- Add Announcement Button -->
<div style="flex: 1; text-align:right; ;">
<button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#announcementsModal">
     Announcement
</button>
</div>


<!-- Announcements Section (initially hidden) -->
<!-- Modal -->
<div class="modal fade" id="announcementsModal" tabindex="-1" aria-labelledby="announcementsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="announcementsModalLabel">
                    <i class="fas fa-bullhorn me-1"></i> Announcements & Agenda
                </h5>
                <button class="btn btn-dark btn-sm ms-auto" onclick="openAddAnnouncementModalProperly()">
                    <i class="fas fa-plus"></i> Add
                </button>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
        <div class="row" id="announcementList">
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-6 col-lg-4 mb-4 d-flex">
              <div class="card shadow-sm w-100 h-100">
                <div class="card-body d-flex flex-column">Title
                  <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                  
                  <?php if (!empty($row['event_date'])): ?>
                    <p class="mb-1"><i class="far fa-calendar-alt me-1"></i> <strong>When:</strong> <?= date("F j, Y, g:i A", strtotime($row['event_date'])) ?></p>
                  <?php endif; ?>
                  <p class="mb-1"><i class="fas fa-info-circle me-1"></i> <strong>What:</strong><br><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                  <?php if (!empty($row['location'])): ?>
                    <p class="mb-1">Location: <?= htmlspecialchars($row['location']) ?></p>
                  <?php endif; ?>
                  <p class="text-muted small mt-auto"><i class="far fa-clock me-1"></i> Posted: <?= date("F j, Y", strtotime($row['publish_date'])) ?></p>
                  <form method="POST" action="announcement_handler.php" onsubmit="return confirm('Delete this announcement?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="announcement_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn btn-link text-danger p-0 mb-2"><i class="fas fa-trash-alt"></i></button>
                  </form>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="downloadAnnouncementAsPDF()">
          <i class="fas fa-file-pdf me-1"></i> Download PDF
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<!-- Add Announcement Modal -->
<!-- Localized Modal Container (insert this INSIDE your dashboard container only) -->
<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="announcement_handler.php">
      <div class="modal-header">
        <h5 class="modal-title" id="addAnnouncementModalLabel">Add Announcement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="add">
        <div class="mb-3">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Content</label>
          <textarea name="content" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Event Date (optional)</label>
          <input type="datetime-local" name="event_date" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Location (optional)</label>
          <input type="text" name="location" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>


    
                  <div id="realtime" style="font-family: sans-serif; color: #333; font-size: 14px;"></div>
                </div>
                </div> 

                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo html_escape($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo html_escape($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <div id="realtime" style="font-family: sans-serif; color: #333; font-size: 14px;"></div>
                <?php unset($_SESSION['error']); endif; ?>

                <!-- Barangay Info Card -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card barangay-info-card">
                            <div class="card-body d-flex align-items-center">
                                <img src="<?php echo !empty($settings_data['barangay_logo_path']) && file_exists($settings_data['barangay_logo_path']) ? html_escape($settings_data['barangay_logo_path']) : 'images/barangay-logo.png'; ?>" 
                                     alt="<?php echo html_escape($settings_data['barangay_name'] ?? 'Barangay'); ?> Seal" 
                                     class="brgy-logo me-4" style="max-width: 70px; max-height: 70px; object-fit: contain; border-radius: 50%;">
                                <div>
                                    <h4 class="brgy-name"><?php echo html_escape($settings_data['barangay_name'] ?? 'Barangay Management System'); ?></h4>
                                    <p class="brgy-location"><?php echo html_escape($full_address); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-4">
                        <div class="simple-stat-box">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div class="stat-number"><?php echo html_escape($total_population); ?></div>
                            <div class="stat-label">Total Population</div>
                            <a href="manage_residents.php" class="stat-link">View Details</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="simple-stat-box">
                            <div class="stat-icon"><i class="fas fa-male"></i></div>
                            <div class="stat-number"><?php echo html_escape($male_population); ?></div>
                            <div class="stat-label">Male Population</div>
                            <a href="manage_residents.php?gender=Male" class="stat-link">View Details</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="simple-stat-box">
                            <div class="stat-icon"><i class="fas fa-female"></i></div>
                            <div class="stat-number"><?php echo html_escape($female_population); ?></div>
                            <div class="stat-label">Female Population</div>
                            <a href="manage_residents.php?gender=Female" class="stat-link">View Details</a>
                        </div>
                    </div>
                </div>

                <!-- Age Demographics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <div class="simple-stat-box">
                            <div class="stat-icon"><i class="fas fa-baby"></i></div>
                            <div class="stat-number"><?php echo html_escape($child_population); ?></div>
                            <div class="stat-label">Child Population (0-12)</div>
                            <a href="manage_residents.php?age_group=child" class="stat-link">View Details</a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="simple-stat-box">
                            <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                            <div class="stat-number"><?php echo html_escape($youth_population); ?></div>
                            <div class="stat-label">Youth Population (13-24)</div>
                            <a href="manage_residents.php?age_group=youth" class="stat-link">View Details</a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="simple-stat-box">
                            <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
                            <div class="stat-number"><?php echo html_escape($adult_population); ?></div>
                            <div class="stat-label">Adult Population (25-59)</div>
                            <a href="manage_residents.php?age_group=adult" class="stat-link">View Details</a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="simple-stat-box">
                            <div class="stat-icon"><i class="fas fa-walking"></i></div>
                            <div class="stat-number"><?php echo html_escape($senior_population); ?></div>
                            <div class="stat-label">Senior Population (60+)</div>
                            <a href="manage_residents.php?age_group=senior" class="stat-link">View Details</a>
                        </div>
                    </div>
                </div>

                <!-- Population Distribution Summary -->
                <div class="row mb-4">
                    <div class="col-lg-8 mb-4">
                        <div class="card demographics-summary-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Population Demographics Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Gender Distribution</h6>
                                        <div class="progress-item mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-sm font-weight-bold">Male</span>
                                                <span class="text-sm"><?php echo $male_population; ?> (<?php echo $total_residents > 0 ? number_format(($male_population / $total_residents) * 100, 1) : '0'; ?>%)</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-male" role="progressbar" style="width: <?php echo $total_residents > 0 ? ($male_population / $total_residents) * 100 : 0; ?>%" aria-valuenow="<?php echo $total_residents > 0 ? ($male_population / $total_residents) * 100 : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="progress-item mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-sm font-weight-bold">Female</span>
                                                <span class="text-sm"><?php echo $female_population; ?> (<?php echo $total_residents > 0 ? number_format(($female_population / $total_residents) * 100, 1) : '0'; ?>%)</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-female" role="progressbar" style="width: <?php echo $total_residents > 0 ? ($female_population / $total_residents) * 100 : 0; ?>%" aria-valuenow="<?php echo $total_residents > 0 ? ($female_population / $total_residents) * 100 : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Age Distribution</h6>
                                        <div class="progress-item mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-sm font-weight-bold">Children (0-12)</span>
                                                <span class="text-sm"><?php echo $child_population; ?> (<?php echo $total_residents > 0 ? number_format(($child_population / $total_residents) * 100, 1) : '0'; ?>%)</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-child" role="progressbar" style="width: <?php echo $total_residents > 0 ? ($child_population / $total_residents) * 100 : 0; ?>%" aria-valuenow="<?php echo $total_residents > 0 ? ($child_population / $total_residents) * 100 : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="progress-item mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-sm font-weight-bold">Youth (13-24)</span>
                                                <span class="text-sm"><?php echo $youth_population; ?> (<?php echo $total_residents > 0 ? number_format(($youth_population / $total_residents) * 100, 1) : '0'; ?>%)</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-youth" role="progressbar" style="width: <?php echo $total_residents > 0 ? ($youth_population / $total_residents) * 100 : 0; ?>%" aria-valuenow="<?php echo $total_residents > 0 ? ($youth_population / $total_residents) * 100 : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="progress-item mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-sm font-weight-bold">Adults (25-59)</span>
                                                <span class="text-sm"><?php echo $adult_population; ?> (<?php echo $total_residents > 0 ? number_format(($adult_population / $total_residents) * 100, 1) : '0'; ?>%)</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-adult" role="progressbar" style="width: <?php echo $total_residents > 0 ? ($adult_population / $total_residents) * 100 : 0; ?>%" aria-valuenow="<?php echo $total_residents > 0 ? ($adult_population / $total_residents) * 100 : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="progress-item mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-sm font-weight-bold">Seniors (60+)</span>
                                                <span class="text-sm"><?php echo $senior_population; ?> (<?php echo $total_residents > 0 ? number_format(($senior_population / $total_residents) * 100, 1) : '0'; ?>%)</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-senior" role="progressbar" style="width: <?php echo $total_residents > 0 ? ($senior_population / $total_residents) * 100 : 0; ?>%" aria-valuenow="<?php echo $total_residents > 0 ? ($senior_population / $total_residents) * 100 : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Enhanced Announcements and Activities -->
<div class="card h-100 recent-activities-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Recent Activities</h6>
        <button class="btn btn-sm btn-outline-secondary btn-toggle-activities" onclick="toggleActivities()">
            <i class="fas fa-eye me-1"></i>Show/Hide
        </button>
    </div>

    <div class="card-body" id="recentActivitiesContent" style="display: none;">
        <ul class="list-group list-group-flush activity-list">
            <?php if (mysqli_num_rows($activities_result) > 0): ?>
                <?php while($activity = mysqli_fetch_assoc($activities_result)): ?>
                    <li class="list-group-item">
                        <div class="d-flex">
                            <div class="activity-icon 
                                <?php 
                                    if (stripos($activity['activity_type'], 'New') !== false) echo 'bg-primary';
                                    elseif (stripos($activity['activity_type'], 'Update') !== false) echo 'bg-warning';
                                    elseif (stripos($activity['activity_type'], 'Delete') !== false) echo 'bg-danger';
                                    elseif (stripos($activity['activity_type'], 'Issue') !== false) echo 'bg-success';
                                    else echo 'bg-info'; 
                                ?>">
                                <i class="fas 
                                    <?php 
                                        if (stripos($activity['activity_type'], 'Resident') !== false) echo 'fa-user-plus';
                                        elseif (stripos($activity['activity_type'], 'Certificate') !== false) echo 'fa-file-alt';
                                        elseif (stripos($activity['activity_type'], 'SMS') !== false) echo 'fa-sms';
                                        else echo 'fa-bell'; 
                                    ?>">
                                </i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars($activity['activity_description']); ?></h6>
                                <small class="text-muted">
                                    <?php echo date("F j, Y, g:i a", strtotime($activity['timestamp'])); ?>
                                </small>
                            </div>
                        </div>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="list-group-item text-center">No recent activities.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="card-footer text-center">
        <a href="history_log.php" class="btn btn-outline-primary btn-view-all">View All Activities</a>
    </div>
</div>

<script>
    function toggleActivities() {
        const content = document.getElementById('recentActivitiesContent');
        content.style.display = content.style.display === 'none' ? 'block' : 'none';
    }
</script>

<style>
    .recent-activities-card {
        max-width: 300px; /* Smaller width */
        margin-right: auto;
        margin-left: 0;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .recent-activities-card .card-header,
    .recent-activities-card .card-footer {
        padding: 0.5rem 1rem;
    }

    .recent-activities-card .card-body {
        padding: 0.75rem 1rem;
    }

    .btn-toggle-activities {
        font-size: 0.85rem;
        padding: 3px 10px;
    }

    .btn-view-all {
        font-size: 0.85rem;
        padding: 4px 12px;
    }

    .activity-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .card {
        background: #fff;
    }
     #realtime {
    font-size: 0.95rem;
    color: black;
  }
</style>

                <!-- Enhanced Officials Organizational Chart -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card officials-org-card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-sitemap me-2"></i>
                                        Barangay Officials Organizational Chart
                                    </h5>
                                    <a href="manage_officials.php" class="btn btn-sm btn-outline-light">
                                        <i class="fas fa-cog me-1"></i>Manage Officials
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Professional Organizational Chart Template -->
                                <div class="professional-org-chart green-theme">
                                    <!-- Header with Barangay Name and Logos -->
                                    <div class="org-header">
                                        <div class="header-content">
                                            <div class="logo-left">
                                                <img src="<?php echo !empty($settings_data['barangay_logo_path']) && file_exists($settings_data['barangay_logo_path']) ? html_escape($settings_data['barangay_logo_path']) : 'images/barangay-logo.png'; ?>" 
                                                     alt="Barangay Seal" class="barangay-seal">
                                                <div class="seal-text"><?php echo strtoupper(html_escape($settings_data['barangay_seal_text'] ?? 'OFFICIAL SEAL')); ?></div>
                                            </div>
                                            <div class="header-title">
                                                <h2 class="barangay-title"><?php echo strtoupper(html_escape($settings_data['barangay_name'] ?? 'BARANGAY MANAGEMENT SYSTEM')); ?></h2>
                                                <h4 class="barangay-location"><?php echo strtoupper(html_escape($full_address)); ?></h4>
                                             </div>
                                            <div class="logo-right">
                                                <img src="<?php echo !empty($settings_data['municipality_logo_path']) && file_exists($settings_data['municipality_logo_path']) ? html_escape($settings_data['municipality_logo_path']) : (!empty($settings_data['barangay_logo_path']) && file_exists($settings_data['barangay_logo_path']) ? html_escape($settings_data['barangay_logo_path']) : 'images/barangay-logo.png'); ?>" 
                                                     alt="Municipality Seal" class="municipality-seal">
                                                <div class="seal-text"><?php echo strtoupper(html_escape($settings_data['municipality_seal_text'] ?? 'MUNICIPALITY SEAL')); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    // Re-fetch officials for organizational chart (excluding Ex- officials)
                                    $org_officials_result = mysqli_query($link, "SELECT * FROM officials WHERE position NOT LIKE 'Ex-%' ORDER BY display_order ASC, position ASC, fullname ASC");
                                    
                                    $officials_by_level = [];
                                    
                                    // Organize officials by hierarchy levels
                                    if ($org_officials_result) {
                                        while($official = mysqli_fetch_assoc($org_officials_result)) {
                                            $position_lower = strtolower($official['position']);
                                            $level = $official['display_order'] ?? 1;
                                            
                                            // Assign hierarchy level based on position and display_order
                                            if (strpos($position_lower, 'captain') !== false || strpos($position_lower, 'punong') !== false) {
                                                $official['hierarchy_level'] = 1;
                                            } elseif (strpos($position_lower, 'secretary') !== false) {
                                                $official['hierarchy_level'] = 2;
                                            } elseif (strpos($position_lower, 'treasurer') !== false) {
                                                $official['hierarchy_level'] = 2;
                                            } else {
                                                // Other officials use display_order or default to level 3
                                                $official['hierarchy_level'] = max(3, $level);
                                            }
                                            
                                            $officials_by_level[$official['hierarchy_level']][] = $official;
                                        }
                                    }
                                    
                                    // Sort levels
                                    ksort($officials_by_level);
                                    ?>

                                    <!-- Hierarchical Organizational Chart -->
                                    <?php if (!empty($officials_by_level)): ?>
                                        <?php foreach ($officials_by_level as $level => $officials): ?>
                                            <?php if ($level > 1): ?>
                                                <!-- Connecting Lines -->
                                                <div class="org-connector">
                                                    <div class="connector-line"></div>
                                                    <div class="connector-hub"></div>
                                                    <div class="connector-branches"></div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="hierarchy-level level-<?php echo $level; ?>">
                                                <?php foreach ($officials as $official): ?>
                                                    <?php
                                                    $position_lower = strtolower($official['position']);
                                                    $card_class = 'council-position';
                                                    if (strpos($position_lower, 'captain') !== false || strpos($position_lower, 'punong') !== false) {
                                                        $card_class = 'captain-position';
                                                    } elseif (strpos($position_lower, 'secretary') !== false) {
                                                        $card_class = 'secretary-position';
                                                    } elseif (strpos($position_lower, 'treasurer') !== false) {
                                                        $card_class = 'treasurer-position';
                                                    }
                                                    ?>
                                                    <div class="professional-official-card <?php echo $card_class; ?>">
                                                        <div class="official-photo-frame">
                                                            <img src="<?php    echo !empty($official['image_path']) && file_exists($official['image_path']) ? html_escape($official['image_path']) : 'assets/images/default-avatar.png'; ?>" 
                                                                 alt="<?php echo html_escape($official['fullname']); ?>" class="official-photo">
                                                            <div class="official-frame-border"></div>
                                                        </div>
                                                        <div class="official-details">
                                                            <h3 class="official-name"><?php echo strtoupper(html_escape($official['fullname'])); ?></h3>
                                                            <h4 class="official-position"><?php echo html_escape($official['position']); ?></h4>
                                                            <?php if (!empty($official['contact_number'])): ?>
                                                            <p class="official-contact">📱 <?php echo html_escape($official['contact_number']); ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <!-- Add Official Message if no officials exist -->
                                    <?php if (empty($officials_by_level)): ?>
                                    <div class="no-officials-message">
                                        <div class="text-center py-5">
                                            <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">No officials added yet</h5>
                                            <p class="text-muted">Start building your barangay organizational chart</p>
                                            <a href="official_form.php?action=add" class="btn btn-primary btn-lg">
                                                <i class="fas fa-plus me-2"></i>Add First Official
                                            </a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

    <!-- Global Loading Overlay -->
    <div id="globalLoadingOverlay" class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    

   

</body>
</html>

<?php 
require_once 'includes/footer.php'; // Include the footer
// The closing of body, html tags, and script includes are now in footer.php
// The mysqli_close($link) is also handled in footer.php
?>
<script>
   function showAnnouncementAndAgenda() {
    // Show the hidden announcements section
    const announcementsSection = document.getElementById('announcementsSection');
    if (announcementsSection) {
        announcementsSection.style.display = 'block';
    }

    // Show the hidden agenda section
    const agendaSection = document.getElementById('agendaSection');
    if (agendaSection) {
        agendaSection.style.display = 'block';
    }

    
}

const showBtn = document.getElementById('showAnnouncementsBtn');
    const announcementSection = document.getElementById('announcementsSection');

    showBtn.addEventListener('click', function () {
        announcementSection.style.display = 'block'; // Show the announcement list only
    });

    function closeAnnouncementForm() {
        const overlay = document.getElementById('announcementOverlay');
        overlay.style.display = 'none';
    }
function openAddAnnouncementModalProperly() {
    // Close the Announcements modal first
    const announcementsModal = bootstrap.Modal.getInstance(document.getElementById('announcementsModal'));
    announcementsModal.hide();

    // Then show the Add Announcement modal after a small delay
    setTimeout(() => {
      const addModal = new bootstrap.Modal(document.getElementById('addAnnouncementModal'));
      addModal.show();
    }, 500);
  }

// Download as PDF
async function downloadAnnouncementAsPDF() {
    const element = document.getElementById('announcementsSection');
    if (!element) return;

    const canvas = await html2canvas(element, {
        scale: 2,
        backgroundColor: "#ffffff"
    });

    const imgData = canvas.toDataURL('image/png');
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'pt',
        format: 'a4'
    });

    const pageWidth = pdf.internal.pageSize.getWidth();
    const imgProps = pdf.getImageProperties(imgData);
    const imgWidth = pageWidth - 40;
    const imgHeight = (imgProps.height * imgWidth) / imgProps.width;

    pdf.addImage(imgData, 'PNG', 20, 20, imgWidth, imgHeight);
    pdf.save("announcement_list.pdf");
}
// Animation: Counters
function animateCounters() {
    const counters = document.querySelectorAll('.count-number');
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));

                let count = 0;
                const increment = target / 100;
                const timer = setInterval(() => {
                    count += increment;
                    if (count >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(count);
                    }
                }, 20);

                observer.unobserve(counter);
            }
        });
    }, observerOptions);

    counters.forEach(counter => {
        observer.observe(counter);
    });
}

// Animation: Cards
function enhanceCardAnimations() {
    const cards = document.querySelectorAll('.stat-card, .official-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-12px) scale(1.02)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0) scale(1)';
        });
    });
}
 function updateDateTime() {
    const now = new Date();
    const options = {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    };
    document.getElementById('realtime').textContent = now.toLocaleString('en-US', options);
  }

  // Update every second
  setInterval(updateDateTime, 1000);
  updateDateTime(); // Initial call

// Ripple Effect CSS (injecting directly)
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(rippleStyle);
document.addEventListener('DOMContentLoaded', function () {
    const addBtn = document.querySelector('[data-bs-target="#addAnnouncementModal"]');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            const section = document.getElementById('announcementsSection');
            if (section) section.style.display = 'block';
        });
    }
});

function openAddAnnouncementModalProperly() {
    const announcementsModal = bootstrap.Modal.getInstance(document.getElementById('announcementsModal'));
    announcementsModal.hide();

    const addModal = new bootstrap.Modal(document.getElementById('addAnnouncementModal'));
    setTimeout(() => {
        addModal.show();
    }, 500); // delay ensures the first modal is hidden
}
function openAddAnnouncementModalProperly() {
    // First, close the current modal if open
    const currentModal = bootstrap.Modal.getInstance(document.getElementById('announcementsModal'));
    if (currentModal) {
      currentModal.hide();
    }

    // Then open the Add Announcement modal
    const addModal = new bootstrap.Modal(document.getElementById('addAnnouncementModal'));
    addModal.show();
  }

</script>

<!-- Required JS files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
