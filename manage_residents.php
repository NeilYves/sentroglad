<?php
// --- Manage Residents Page ---
// This page displays a list of barangay residents, allows searching for residents,
// and provides options to add, edit, or delete resident records.
// It interacts with 'resident_handler.php' for processing actions and 'resident_form.php' for add/edit forms.

// Set the page title, which is used in the header include.
$page_title = 'Manage Residents';
// Include the common header for the page layout and database connection ($link).
require_once 'includes/header.php';

// --- Handle Status Messages from Handler ---
// Check for 'status' GET parameter, typically set by 'resident_handler.php' after an action.
$message = ''; // Initialize an empty message string.

// Session-based message handling
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    $action_type = isset($_SESSION['action_type']) ? $_SESSION['action_type'] : 'archive'; // Default to archive
    $show_success_modal = true;
    unset($_SESSION['success']);
    if (isset($_SESSION['action_type'])) {
        unset($_SESSION['action_type']);
    }
} else {
    $show_success_modal = false;
    $action_type = 'archive';
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo '<i class="fas fa-exclamation-circle me-2"></i>';
    echo html_escape($_SESSION['error']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['error']);
}

// Legacy status handling for backward compatibility
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success_add') {
        $message = '<div class="alert alert-success" role="alert">New resident added successfully!</div>';
    } elseif ($_GET['status'] == 'success_update') {
        $message = '<div class="alert alert-success" role="alert">Resident updated successfully!</div>';
    } elseif ($_GET['status'] == 'success_delete') {
        $message = '<div class="alert alert-success" role="alert">Resident deleted successfully!</div>';
    } elseif ($_GET['status'] == 'error') {
        $message = '<div class="alert alert-danger" role="alert">An error occurred. Please try again.</div>';
    }
}

// --- Fetch Residents Data ---
// Get the search query from GET parameter if set, and sanitize it.
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($link, $_GET['search']) : '';

// Get gender filter from GET parameter if set
$gender_filter = isset($_GET['gender']) ? mysqli_real_escape_string($link, $_GET['gender']) : '';

// Get age group filter from GET parameter if set
$age_group_filter = isset($_GET['age_group']) ? mysqli_real_escape_string($link, $_GET['age_group']) : '';

// Base SQL query components
$base_select = "SELECT r.id, r.first_name, r.middle_name, r.last_name, r.suffix, r.gender, r.civil_status, r.birthdate, r.contact_number, r.registration_date, r.status, r.date_status_changed, r.status_remarks, r.archived_date, r.archived_by, r.archive_reason, p.purok_name";
$base_from = "FROM residents r LEFT JOIN puroks p ON r.purok_id = p.id";

// Build WHERE clause based on filters
$where_conditions = [];
$params = [];
$types = '';

// If a search query is provided, add conditions for first name, last name, etc.
if (!empty($search_query)) {
    $search_term_like = '%' . $search_query . '%';
    $where_conditions[] = "(r.first_name LIKE ? OR r.last_name LIKE ? OR r.contact_number LIKE ?)";
    $params[] = $search_term_like;
    $params[] = $search_term_like;
    $params[] = $search_term_like;
    $types .= 'sss';
}

// If gender filter is provided, add gender condition
if (!empty($gender_filter)) {
    $where_conditions[] = "r.gender = ?";
    $params[] = $gender_filter;
    $types .= 's';
}

// If age group filter is provided, add age condition
if (!empty($age_group_filter)) {
    switch($age_group_filter) {
        case 'child':
            $where_conditions[] = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 0 AND 12";
            break;
        case 'youth':
            $where_conditions[] = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 13 AND 24";
            break;
        case 'adult':
            $where_conditions[] = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 25 AND 59";
            break;
        case 'senior':
            $where_conditions[] = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60";
            break;
    }
}

// Build the base WHERE clause for common filters
$common_where = '';
if (!empty($where_conditions)) {
    $common_where = " AND " . implode(" AND ", $where_conditions);
}

// Separate queries for active and archived residents
$active_sql = $base_select . " " . $base_from . " WHERE r.status != 'Archived'" . $common_where . " ORDER BY r.last_name ASC, r.first_name ASC";
$archived_sql = $base_select . " " . $base_from . " WHERE r.status = 'Archived'" . $common_where . " ORDER BY r.archived_date DESC, r.last_name ASC";

// Execute active residents query
$active_residents_result = null;
if (!empty($params)) {
    $active_stmt = mysqli_prepare($link, $active_sql);
    if ($active_stmt) {
        mysqli_stmt_bind_param($active_stmt, $types, ...$params);
        mysqli_stmt_execute($active_stmt);
        $active_residents_result = mysqli_stmt_get_result($active_stmt);
    }
} else {
    $active_residents_result = mysqli_query($link, $active_sql);
}

// Execute archived residents query
$archived_residents_result = null;
if (!empty($params)) {
    $archived_stmt = mysqli_prepare($link, $archived_sql);
    if ($archived_stmt) {
        mysqli_stmt_bind_param($archived_stmt, $types, ...$params);
        mysqli_stmt_execute($archived_stmt);
        $archived_residents_result = mysqli_stmt_get_result($archived_stmt);
    }
} else {
    $archived_residents_result = mysqli_query($link, $archived_sql);
}

// Count totals
$active_count = $active_residents_result ? mysqli_num_rows($active_residents_result) : 0;
$archived_count = $archived_residents_result ? mysqli_num_rows($archived_residents_result) : 0;
$total_count = $active_count + $archived_count;

?>

<!-- Page Header and Add Button -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="dashboard-title">
        <?php 
        echo html_escape($page_title);
        
        // Show active filters in the title
        $title_filters = [];
        if (!empty($gender_filter)) {
            $title_filters[] = ucfirst($gender_filter);
        }
        if (!empty($age_group_filter)) {
            $age_group_names = [
                'child' => 'Children (0-12)',
                'youth' => 'Youth (13-24)', 
                'adult' => 'Adults (25-59)',
                'senior' => 'Seniors (60+)'
            ];
            $title_filters[] = $age_group_names[$age_group_filter];
        }
        if (!empty($title_filters)) {
            echo ' - ' . implode(' & ', $title_filters);
        }
        ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <!-- Link to the form for adding a new resident -->
        <a href="resident_form.php?action=add" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Add New Resident
        </a>
    </div>
</div>

<?php echo $message; // Display any success or error messages here ?>

<!-- Residents Statistics Summary -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center border-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title text-success mb-0">Active Residents</h5>
                        <h2 class="text-success"><?php echo $active_count; ?></h2>
                        <small class="text-muted">Currently active</small>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title text-info mb-0">Archived</h5>
                        <h2 class="text-info"><?php echo $archived_count; ?></h2>
                        <small class="text-muted">In archive</small>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-archive fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title text-primary mb-0">Total</h5>
                        <h2 class="text-primary"><?php echo $total_count; ?></h2>
                        <small class="text-muted">All residents</small>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-chart-bar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Filters Display -->
<?php if (!empty($gender_filter) || !empty($age_group_filter)): ?>
<div class="mb-3">
    <div class="alert alert-info d-flex align-items-center">
        <i class="fas fa-filter me-2"></i>
        <div>
            <strong>Active Filters:</strong>
            <?php
            $active_filters = [];
            if (!empty($gender_filter)) {
                $active_filters[] = "Gender: " . ucfirst($gender_filter);
            }
            if (!empty($age_group_filter)) {
                $age_group_names = [
                    'child' => 'Children (0-12 years)',
                    'youth' => 'Youth (13-24 years)', 
                    'adult' => 'Adults (25-59 years)',
                    'senior' => 'Seniors (60+ years)'
                ];
                $active_filters[] = "Age Group: " . $age_group_names[$age_group_filter];
            }
            echo implode(' | ', $active_filters);
            ?>
        </div>
        <a href="manage_residents.php" class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="fas fa-times"></i> Clear All Filters
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Search Form -->
<form method="GET" action="manage_residents.php" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Search by name or contact number..." value="<?php echo html_escape(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i> Search</button>
        <?php if (!empty($_GET['search'])): ?>
            <a href="manage_residents.php" class="btn btn-outline-danger"><i class="fas fa-times"></i> Clear</a>
        <?php endif; ?>
    </div>
</form>

<!-- Residents List Card -->
<div class="card officials-card">
    <div class="card-header bg-success text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Active Residents
                <span class="badge bg-light text-success ms-2"><?php echo $active_count; ?></span>
            </h5>
            <?php if ($archived_count > 0): ?>
            <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#archivedSection" aria-expanded="false" aria-controls="archivedSection">
                <i class="fas fa-archive me-1"></i>
                View Archived (<?php echo $archived_count; ?>)
            </button>
            <?php endif; ?>
        </div>
        <small class="d-block mt-1">Currently active residents in the barangay</small>
    </div>
    <div class="card-body">
        <div class="table-responsive"> <!-- Ensures table is scrollable on small screens -->
            <table class="table table-hover"> <!-- Hover effect for table rows -->
                <thead class="table-light"> <!-- Light background for table header -->
                    <tr>
                        <th>ID</th>
                        <th>Fullname</th>
                        <th>Gender</th>
                        <th>Civil Status</th>
                        <th>Age</th>
                        <th>Purok</th>
                        <th>Status</th>
                        <th>Contact</th>
                        <th>Registration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php // Check if there are any residents to display
                    if ($active_residents_result && mysqli_num_rows($active_residents_result) > 0): ?>
                        <?php // Loop through each resident record and display it in a table row
                        while($resident = mysqli_fetch_assoc($active_residents_result)): ?>
                            <tr>
                                <!-- Display resident data, escaping all output to prevent XSS -->
                                <td><?php echo html_escape($resident['id']); ?></td>
                                <td>
                                    <strong>
                                        <?php 
                                        // Assemble the full name from parts
                                        $fullname_parts = [$resident['first_name'], $resident['middle_name'], $resident['last_name'], $resident['suffix']];
                                        echo html_escape(implode(' ', array_filter($fullname_parts))); 
                                        ?>
                                    </strong>
                                </td>
                                <td>
                                    <i class="fas <?php echo ($resident['gender'] == 'Male') ? 'fa-male text-primary' : (($resident['gender'] == 'Female') ? 'fa-female text-danger' : 'fa-user text-muted'); ?>"></i>
                                    <?php echo html_escape($resident['gender']); ?>
                                </td>
                                <td><?php echo html_escape($resident['civil_status']); ?></td>
                                <!-- Calculate and display age -->
                                <td><?php 
                                    if (!empty($resident['birthdate'])) {
                                        $age = date_diff(date_create($resident['birthdate']), date_create('today'))->y;
                                        echo html_escape($age . ' years');
                                        echo '<br><small class="text-muted">' . html_escape(date('M d, Y', strtotime($resident['birthdate']))) . '</small>';
                                    } else {
                                        echo 'N/A';
                                    }
                                ?></td>
                                <!-- Display Purok with badge -->
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo html_escape($resident['purok_name'] ?? 'Unassigned'); ?>
                                    </span>
                                </td>
                                <!-- Display Status with appropriate badge -->
                                <td>
                                    <span class="badge <?php 
                                        echo match($resident['status']) {
                                            'Active' => 'bg-success',
                                            'Deceased' => 'bg-dark',
                                            'Moved Out' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                    ?>">
                                        <?php echo html_escape($resident['status']); ?>
                                    </span>
                                    <?php if (!empty($resident['date_status_changed']) && $resident['status'] != 'Active'): ?>
                                        <br><small class="text-muted">
                                            Changed: <?php echo date("M j, Y", strtotime($resident['date_status_changed'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <!-- Contact -->
                                <td>
                                    <?php if (!empty($resident['contact_number'])): ?>
                                        <i class="fas fa-phone me-1"></i><?php echo html_escape($resident['contact_number']); ?>
                                    <?php endif; ?>
                                </td>
                                <!-- Registration Date -->
                                <td><?php echo html_escape(date('M d, Y', strtotime($resident['registration_date']))); ?></td>
                                <td>
                                    <!-- Actions buttons in button group -->
                                    <div class="btn-group btn-group-sm">
                                        <!-- View Info button: links to view_resident.php with resident ID -->
                                        <a href="view_resident.php?id=<?php echo html_escape($resident['id']); ?>" class="btn btn-outline-info" title="View Complete Information">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Edit button: links to resident_form.php with action=edit and resident ID -->
                                        <a href="resident_form.php?action=edit&id=<?php echo html_escape($resident['id']); ?>" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Archive button: archives the resident -->
                                        <a href="resident_handler.php?action=archive&id=<?php echo html_escape($resident['id']); ?>" 
                                           class="btn btn-outline-secondary" 
                                           title="Archive Resident" 
                                           onclick="return confirm('Are you sure you want to archive this resident? You can restore them later from the archive.');">
                                            <i class="fas fa-archive"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: // If no residents are found (or search yields no results) ?>
                        <tr><td colspan="9" class="text-center">No residents found<?php echo !empty($search_query) ? ' matching your search' : ''; // Tailor message if search was active ?>.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Enhanced Table Footer with Actions -->
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        <?php 
                        echo "Showing $active_count active resident" . ($active_count != 1 ? 's' : '');
                        if (!empty($search_query) || !empty($gender_filter) || !empty($age_group_filter)) {
                            echo " (filtered)";
                        }
                        ?>
                    </small>
                </div>
                <div class="btn-group btn-group-sm">
                    <a href="purok_details.php" class="btn btn-outline-primary">
                        <i class="fas fa-map-marker-alt me-1"></i>View by Purok
                    </a>
                    <a href="resident_form.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add New
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Archived Residents List Card -->
<?php if ($archived_residents_result && mysqli_num_rows($archived_residents_result) > 0): ?>
<div class="collapse mt-4" id="archivedSection">
    <div class="card archive-card shadow-sm" style="border: none; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
        <div class="card-header archive-header" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); border: none;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="archive-icon-container me-3">
                        <i class="fas fa-archive text-white" style="font-size: 1.5rem; opacity: 0.9;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-white">
                            <span class="fw-bold">Archived Residents</span>
                            <span class="badge bg-light text-dark ms-2 px-3 py-1" style="font-size: 0.85rem;">
                                <?php echo $archived_count; ?> Archived
                            </span>
                        </h5>
                        <small class="text-light d-block mt-1" style="opacity: 0.8;">
                            <i class="fas fa-info-circle me-1"></i>
                            Temporarily archived residents - can be restored anytime
                        </small>
                    </div>
                </div>
                <button class="btn btn-outline-light btn-sm archive-toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target="#archivedSection" aria-expanded="true" aria-controls="archivedSection">
                    <i class="fas fa-eye-slash me-1"></i>
                    <span>Hide Archive</span>
                </button>
            </div>
        </div>
        <div class="card-body p-0" style="background-color: #f8f9fa;">
            <div class="archive-intro-banner p-3 mb-0" style="background: linear-gradient(90deg, #e3f2fd 0%, #f3e5f5 100%); border-bottom: 1px solid #dee2e6;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="archive-status-icon me-3">
                                <i class="fas fa-clock text-info" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-dark">
                                    <i class="fas fa-box-open text-secondary me-1"></i>
                                    Archive Status Overview
                                </h6>
                                <small class="text-muted">
                                    These residents have been temporarily moved to archive. All data is preserved and can be restored instantly.
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="archive-stats">
                            <span class="badge bg-secondary px-3 py-2">
                                <i class="fas fa-users me-1"></i>
                                <?php echo $archived_count; ?> Total
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover mb-0 archive-table">
                    <thead style="background: linear-gradient(135deg, #495057 0%, #6c757d 100%); color: white;">
                        <tr>
                            <th class="border-0" style="font-weight: 600;">
                                <i class="fas fa-id-card me-1"></i>ID
                            </th>
                            <th class="border-0" style="font-weight: 600;">
                                <i class="fas fa-user me-1"></i>Resident Info
                            </th>
                            <th class="border-0" style="font-weight: 600;">
                                <i class="fas fa-venus-mars me-1"></i>Gender
                            </th>
                            <th class="border-0" style="font-weight: 600;">
                                <i class="fas fa-birthday-cake me-1"></i>Age
                            </th>
                            <th class="border-0" style="font-weight: 600;">
                                <i class="fas fa-map-marker-alt me-1"></i>Purok
                            </th>
                            <th class="border-0" style="font-weight: 600;">
                                <i class="fas fa-archive me-1"></i>Archive Details
                            </th>
                            <th class="border-0" style="font-weight: 600;">
                                <i class="fas fa-phone me-1"></i>Contact
                            </th>
                            <th class="border-0 text-center" style="font-weight: 600;">
                                <i class="fas fa-cogs me-1"></i>Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($archived_resident = mysqli_fetch_assoc($archived_residents_result)): ?>
                            <tr class="archive-row" style="background-color: #ffffff; border-left: 4px solid #6c757d;">
                                <!-- ID with Archive Badge -->
                                <td class="py-3">
                                    <div class="d-flex flex-column align-items-start">
                                        <span class="fw-bold text-dark"><?php echo html_escape($archived_resident['id']); ?></span>
                                        <span class="badge bg-secondary" style="font-size: 0.7rem;">
                                            <i class="fas fa-archive me-1"></i>ARCHIVED
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Resident Info with Archive Indicator -->
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="archive-avatar me-3">
                                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-1">
                                                <?php 
                                                $fullname_parts = [$archived_resident['first_name'], $archived_resident['middle_name'], $archived_resident['last_name'], $archived_resident['suffix']];
                                                echo html_escape(implode(' ', array_filter($fullname_parts))); 
                                                ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-box text-info me-1"></i>
                                                <span class="fst-italic">In Archive</span>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Gender with Icon -->
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas <?php echo ($archived_resident['gender'] == 'Male') ? 'fa-male text-primary' : (($archived_resident['gender'] == 'Female') ? 'fa-female text-danger' : 'fa-user text-secondary'); ?> me-2"></i>
                                        <span class="text-dark"><?php echo html_escape($archived_resident['gender']); ?></span>
                                    </div>
                                </td>
                                
                                <!-- Age Information -->
                                <td class="py-3">
                                    <div class="text-dark">
                                        <?php 
                                        if (!empty($archived_resident['birthdate'])) {
                                            $age = date_diff(date_create($archived_resident['birthdate']), date_create('today'))->y;
                                            echo '<span class="fw-bold">' . html_escape($age) . '</span> years';
                                            echo '<br><small class="text-muted">' . html_escape(date('M d, Y', strtotime($archived_resident['birthdate']))) . '</small>';
                                        } else {
                                            echo '<span class="text-muted">N/A</span>';
                                        }
                                        ?>
                                    </div>
                                </td>
                                
                                <!-- Purok with Enhanced Badge -->
                                <td class="py-3">
                                    <span class="badge bg-light text-dark border" style="font-size: 0.8rem; padding: 0.5rem 0.75rem;">
                                        <i class="fas fa-home me-1 text-info"></i>
                                        <?php echo html_escape($archived_resident['purok_name'] ?? 'Unassigned'); ?>
                                    </span>
                                </td>
                                
                                <!-- Archive Details with Enhanced Styling -->
                                <td class="py-3">
                                    <div class="archive-details p-2 rounded" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                                        <div class="mb-1">
                                            <i class="fas fa-calendar-alt text-info me-1"></i>
                                            <small class="fw-bold text-dark">Archived:</small>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo $archived_resident['archived_date'] ? date('M j, Y', strtotime($archived_resident['archived_date'])) : 'Unknown'; ?>
                                            </small>
                                        </div>
                                        <div class="mb-1">
                                            <i class="fas fa-user-cog text-success me-1"></i>
                                            <small class="fw-bold text-dark">By:</small>
                                            <br>
                                            <small class="text-muted"><?php echo html_escape($archived_resident['archived_by'] ?? 'System'); ?></small>
                                        </div>
                                        <?php if (!empty($archived_resident['archive_reason'])): ?>
                                            <div>
                                                <i class="fas fa-sticky-note text-warning me-1"></i>
                                                <small class="fw-bold text-dark">Reason:</small>
                                                <br>
                                                <small class="text-muted fst-italic">
                                                    <?php echo html_escape($archived_resident['archive_reason']); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <!-- Contact Information -->
                                <td class="py-3">
                                    <?php if (!empty($archived_resident['contact_number'])): ?>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-phone text-success me-2"></i>
                                            <span class="text-dark"><?php echo html_escape($archived_resident['contact_number']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="fas fa-phone-slash me-1"></i>Not provided
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Enhanced Action Buttons -->
                                <td class="py-3">
                                    <div class="btn-group-vertical" role="group">
                                        <!-- View Button -->
                                        <a href="view_resident.php?id=<?php echo html_escape($archived_resident['id']); ?>" 
                                           class="btn btn-outline-info btn-sm mb-1" 
                                           title="View Complete Information"
                                           style="border-radius: 8px;">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        
                                        <!-- Restore Button (Primary Action) -->
                                        <a href="resident_handler.php?action=restore&id=<?php echo html_escape($archived_resident['id']); ?>" 
                                           class="btn btn-success btn-sm" 
                                           title="Restore from Archive" 
                                           onclick="return confirm('Are you sure you want to restore this resident from the archive?');"
                                           style="border-radius: 8px; font-weight: 600;">
                                            <i class="fas fa-undo me-1"></i>Restore
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Enhanced Archive Footer -->
            <div class="card-footer archive-footer" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-top: 1px solid #dee2e6;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-shield-alt text-success" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-dark">
                                    <i class="fas fa-database me-1 text-primary"></i>
                                    Data Protection Active
                                </h6>
                                <small class="text-muted">
                                    All <?php echo $archived_count; ?> archived residents are safely stored and can be restored instantly.
                                    Data integrity is maintained with full audit trail.
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="archive-actions">
                            <small class="text-muted d-block mb-2">Quick Actions:</small>
                            <button class="btn btn-outline-secondary btn-sm me-2" type="button" data-bs-toggle="collapse" data-bs-target="#archivedSection">
                                <i class="fas fa-eye-slash me-1"></i>Hide Archive
                            </button>
                            <a href="resident_form.php?action=add" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Add New
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<?php if ($archived_count == 0): ?>
<div class="card mt-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #e8f5e8 0%, #f0f9ff 100%);">
    <div class="card-body text-center py-5">
        <div class="empty-archive-illustration mb-3">
            <i class="fas fa-archive text-success" style="font-size: 3rem; opacity: 0.7;"></i>
        </div>
        <h5 class="text-success mb-2">
            <i class="fas fa-check-circle me-2"></i>
            Archive is Empty
        </h5>
        <p class="text-muted mb-3">
            No residents have been archived yet. Use the archive button (📦) on any resident to move them here.
        </p>
        <div class="archive-benefits mt-3">
            <small class="text-muted">
                <i class="fas fa-lightbulb text-warning me-1"></i>
                <strong>Tip:</strong> Archive residents temporarily instead of deleting them to preserve data and enable easy restoration.
            </small>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Help Section -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>How to Use Residents Management
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Quick Actions:</h6>
                        <ul class="mb-0">
                            <li><strong><i class="fas fa-eye text-info"></i> View Info:</strong> See complete resident details including all personal, contact, and household information</li>
                            <li><strong><i class="fas fa-edit text-warning"></i> Edit:</strong> Modify resident information</li>
                            <li><strong><i class="fas fa-archive text-secondary"></i> Archive:</strong> Move resident to archive (like Facebook/Messenger) - can be restored later</li>
                            <li><strong><i class="fas fa-undo text-success"></i> Restore:</strong> Bring back archived residents to active status</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Features:</h6>
                        <ul class="mb-0">
                            <li><strong>Archive System:</strong> Temporarily store residents without losing data - restore instantly when needed</li>
                            <li><strong>Search & Filter:</strong> Find residents by name, gender, or age group</li>
                            <li><strong>Status Tracking:</strong> Active, Deceased, Moved Out, and Archived status</li>
                            <li><strong>Statistics Dashboard:</strong> View counts of active vs archived residents</li>
                            <li><strong>Purok Organization:</strong> Organized by barangay subdivisions</li>
                            <li><strong>Activity Logging:</strong> All archive/restore actions are tracked</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal Popup -->
<?php if ($show_success_modal): ?>
<?php 
// Set gradient colors based on action type
$header_gradient = '';
$modal_title = '';
$modal_subtitle = '';
$modal_icon = '';
$modal_body_icon = '';

switch($action_type) {
    case 'restore':
        $header_gradient = '#28a745 0%, #20c997 100%';
        $modal_title = 'Restore Successful!';
        $modal_subtitle = 'Resident has been restored from archive';
        $modal_icon = 'fa-undo';
        $modal_body_icon = 'fa-user-check text-success';
        break;
    case 'add':
        $header_gradient = '#28a745 0%, #20c997 100%';
        $modal_title = 'Add Successful!';
        $modal_subtitle = 'New resident has been added successfully';
        $modal_icon = 'fa-user-plus';
        $modal_body_icon = 'fa-user-check text-success';
        break;
    case 'edit':
        $header_gradient = '#17a2b8 0%, #007bff 100%';
        $modal_title = 'Update Successful!';
        $modal_subtitle = 'Resident information has been updated';
        $modal_icon = 'fa-edit';
        $modal_body_icon = 'fa-user-edit text-info';
        break;
    default: // archive
        $header_gradient = '#17a2b8 0%, #007bff 100%';
        $modal_title = 'Archive Successful!';
        $modal_subtitle = 'Resident has been moved to archive';
        $modal_icon = 'fa-archive';
        $modal_body_icon = 'fa-user-clock text-info';
        break;
}
?>
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <!-- Modal Header with Gradient -->
            <div class="modal-header text-center border-0" style="background: linear-gradient(135deg, <?php echo $header_gradient; ?>); padding: 2rem 1.5rem 1rem;">
                <div class="w-100">
                    <!-- Success Icon with Animation -->
                    <div class="success-icon-container mb-3">
                        <div class="success-icon-circle">
                            <i class="fas <?php echo $modal_icon; ?> text-white"></i>
                        </div>
                    </div>
                    <h4 class="modal-title text-white fw-bold mb-2" id="successModalLabel">
                        <i class="fas <?php echo $modal_icon; ?> me-2"></i>
                        <?php echo $modal_title; ?>
                    </h4>
                    <p class="text-white-50 mb-0" style="font-size: 0.9rem;">
                        <?php echo $modal_subtitle; ?>
                    </p>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body text-center" style="padding: 2rem 1.5rem;">
                <div class="success-message-container">
                    <div class="mb-3">
                        <i class="fas <?php echo $modal_body_icon; ?>" style="font-size: 2rem; opacity: 0.8;"></i>
                    </div>
                    <h5 class="text-dark mb-0"><?php echo html_escape($success_message); ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php // Include the common footer for the page.
require_once 'includes/footer.php'; 
?>

<style>
/* Archive-specific styling enhancements */
.archive-card {
    border-radius: 15px !important;
    overflow: hidden;
    transition: all 0.3s ease;
}

.archive-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.archive-header {
    border-radius: 15px 15px 0 0 !important;
}

.archive-toggle-btn {
    border-radius: 25px !important;
    padding: 8px 16px;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.archive-toggle-btn:hover {
    background-color: rgba(255,255,255,0.2) !important;
    transform: scale(1.05);
}

.archive-intro-banner {
    border-radius: 0;
    backdrop-filter: blur(5px);
}

.archive-table {
    border-radius: 0;
}

.archive-table thead th {
    border: none !important;
    padding: 1rem 0.75rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.archive-row {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f0f0f0 !important;
}

.archive-row:hover {
    background-color: #f8f9fa !important;
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #28a745 !important;
}

.archive-avatar {
    transition: all 0.3s ease;
}

.archive-row:hover .archive-avatar {
    transform: scale(1.1);
}

.archive-details {
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%) !important;
}

.archive-row:hover .archive-details {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%) !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.btn-group-vertical .btn {
    transition: all 0.3s ease;
    border-width: 2px;
}

.btn-group-vertical .btn:hover {
    transform: translateX(3px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.archive-footer {
    border-radius: 0 0 15px 15px;
}

.archive-stats .badge {
    transition: all 0.3s ease;
}

.archive-stats .badge:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* Collapsible animation enhancement */
#archivedSection {
    transition: all 0.5s ease;
}

#archivedSection.show {
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Empty archive state styling */
.empty-archive-illustration i {
    transition: all 0.3s ease;
}

.empty-archive-illustration:hover i {
    transform: scale(1.1) rotate(5deg);
    color: #28a745 !important;
}

/* Responsive enhancements */
@media (max-width: 768px) {
    .archive-intro-banner {
        text-align: center;
    }
    
    .archive-intro-banner .col-md-4 {
        margin-top: 1rem;
    }
    
    .btn-group-vertical {
        width: 100%;
    }
    
    .btn-group-vertical .btn {
        margin-bottom: 0.25rem;
    }
    
    .archive-details {
        font-size: 0.8rem;
    }
}

/* Badge pulse animation for archived items */
.badge.bg-secondary {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(108, 117, 125, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(108, 117, 125, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(108, 117, 125, 0);
    }
}

/* Archive icon container animation */
.archive-icon-container i {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-5px);
    }
    100% {
        transform: translateY(0px);
    }
}

/* Success button special styling */
.btn-success {
    position: relative;
    overflow: hidden;
}

.btn-success::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-success:hover::before {
    left: 100%;
}

/* Table row slide-in animation */
.archive-row {
    animation: slideInRight 0.5s ease-out;
    animation-fill-mode: both;
}

.archive-row:nth-child(1) { animation-delay: 0.1s; }
.archive-row:nth-child(2) { animation-delay: 0.2s; }
.archive-row:nth-child(3) { animation-delay: 0.3s; }
.archive-row:nth-child(4) { animation-delay: 0.4s; }
.archive-row:nth-child(5) { animation-delay: 0.5s; }

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Success Modal Styling */
#successModal .modal-content {
    border: none;
    box-shadow: 0 15px 35px rgba(0,0,0,0.3) !important;
}

#successModal .modal-dialog {
    max-width: 500px;
}

.success-icon-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.success-icon-circle {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    animation: successPulse 2s ease-in-out infinite;
    backdrop-filter: blur(10px);
}

.success-icon-circle i {
    font-size: 2.5rem;
    animation: successCheckmark 0.8s ease-out;
}

@keyframes successPulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 15px rgba(255, 255, 255, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
    }
}

@keyframes successCheckmark {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.success-message-container {
    animation: fadeInUp 0.6s ease-out 0.3s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.success-details {
    text-align: left;
    line-height: 1.6;
}

#successModal .btn {
    transition: all 0.3s ease;
    border-radius: 25px;
}

#successModal .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
</style>

<script>
// Enhanced archive section interactions
document.addEventListener('DOMContentLoaded', function() {
    // Archive toggle button text change
    const toggleButtons = document.querySelectorAll('[data-bs-target="#archivedSection"]');
    const archivedSection = document.getElementById('archivedSection');
    
    if (archivedSection && toggleButtons.length > 0) {
        archivedSection.addEventListener('shown.bs.collapse', function() {
            toggleButtons.forEach(button => {
                const icon = button.querySelector('i');
                const span = button.querySelector('span');
                if (icon) icon.className = 'fas fa-eye-slash me-1';
                if (span) span.textContent = 'Hide Archive';
            });
        });
        
        archivedSection.addEventListener('hidden.bs.collapse', function() {
            toggleButtons.forEach(button => {
                const icon = button.querySelector('i');
                const span = button.querySelector('span');
                if (icon) icon.className = 'fas fa-archive me-1';
                if (span) span.textContent = 'View Archive';
            });
        });
    }
    
    // Smooth hover effects for archive rows
    const archiveRows = document.querySelectorAll('.archive-row');
    archiveRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
            this.style.zIndex = '10';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.zIndex = 'auto';
        });
    });
    
    // Archive statistics counter animation
    const counters = document.querySelectorAll('.archive-stats .badge');
    counters.forEach(counter => {
        counter.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.15) rotate(2deg)';
        });
        
        counter.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    });
    
    // Show success modal if there's a success message
    <?php if ($show_success_modal): ?>
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        successModal.hide();
    }, 2100);
    <?php endif; ?>
})

// Function to show archive section
function showArchive() {
    const archivedSection = document.getElementById('archivedSection');
    if (archivedSection) {
        const bsCollapse = new bootstrap.Collapse(archivedSection, {
            show: true
        });
        
        // Scroll to archive section after a short delay
        setTimeout(() => {
            archivedSection.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }, 300);
    }
}
</script>
