<?php
// --- Manage Barangay Officials Page ---
// This page displays a list of barangay officials, allows searching for officials,
// and provides options to add, edit, or delete official records.
// It interacts with 'official_handler.php' for processing actions and 'official_form.php' for add/edit forms.

// Set the page title, which is used in the header include.
$page_title = 'Manage Barangay Officials';
// Include the common header for the page layout and database connection ($link).
require_once 'includes/header.php';

// Role-based access control
if ($_SESSION['role'] !== 'Barangay Secretary') {
    // Redirect to dashboard if not a secretary
    header("Location: index.php");
    exit;
}

require_once 'config.php'; // Ensure config.php is included for database connection and utilities

// --- Handle Status Messages from Handler ---
// Check for 'status' GET parameter, typically set by 'official_handler.php' after an action.
// Display messages using session variables for better practice
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo '<i class="fas fa-check-circle me-2"></i>';
    echo html_escape($_SESSION['success']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo '<i class="fas fa-exclamation-circle me-2"></i>';
    echo html_escape($_SESSION['error']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['error']);
}

// Handle URL-based status messages for backward compatibility
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    $message = '';
    $alert_type = 'info';
    
    switch ($status) {
        case 'success_add':
            $message = 'Official added successfully! Previous officials with the same position were automatically moved to "Ex-" status if applicable.';
            $alert_type = 'success';
            break;
        case 'success_update':
            $message = 'Official updated successfully! Previous officials with the same position were automatically moved to "Ex-" status if applicable.';
            $alert_type = 'success';
            break;
        case 'success_delete':
            $message = 'Official deleted successfully.';
            $alert_type = 'success';
            break;
        case 'error_validation':
            $message = 'Error: Please fill in all required fields correctly.';
            $alert_type = 'danger';
            break;
        case 'error_position_limit':
            $message = 'Error: Unable to process position limits. Please try again or contact administrator.';
            $alert_type = 'danger';
            break;
        case 'error_db':
            $message = 'Error: Database operation failed. Please try again.';
            $alert_type = 'danger';
            break;
        case 'error_prepare':
            $message = 'Error: Database query preparation failed. Please try again.';
            $alert_type = 'danger';
            break;
        case 'error_missing_fields':
            $message = 'Error: Required information is missing.';
            $alert_type = 'danger';
            break;
        case 'error_missing_id':
            $message = 'Error: Official ID is missing.';
            $alert_type = 'danger';
            break;
        case 'error_notfound':
            $message = 'Error: Official not found.';
            $alert_type = 'danger';
            break;
        case 'error_invalid_file_type':
            $message = 'Error: Invalid file type. Please upload JPG, PNG, or GIF images only.';
            $alert_type = 'danger';
            break;
        case 'error_file_too_large':
            $message = 'Error: File too large. Maximum file size is 5MB.';
            $alert_type = 'danger';
            break;
        case 'error_upload_failed':
            $message = 'Error: File upload failed. Please try again.';
            $alert_type = 'danger';
            break;
    }
    
    if (!empty($message)) {
        echo '<div class="alert alert-' . $alert_type . ' alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-' . ($alert_type === 'success' ? 'check-circle' : 'exclamation-circle') . ' me-2"></i>';
        echo html_escape($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// Pagination settings
$limit = 10; // Number of officials per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter settings
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$position_filter = isset($_GET['position']) ? trim($_GET['position']) : '';
$term_status_filter = isset($_GET['term_status']) ? trim($_GET['term_status']) : '';

$where_clauses = [];
$params = [];
$param_types = '';

if (!empty($search_query)) {
    $where_clauses[] = "(fullname LIKE ? OR position LIKE ?)";
    $params[] = "%" . $search_query . "%";
    $params[] = "%" . $search_query . "%";
    $param_types .= 'ss';
}

if (!empty($position_filter)) {
    $where_clauses[] = "position = ?";
    $params[] = $position_filter;
    $param_types .= 's';
}

if (!empty($term_status_filter)) {
    if ($term_status_filter == 'Active') {
        $where_clauses[] = "(term_start_date <= CURDATE() AND term_end_date >= CURDATE())";
    } elseif ($term_status_filter == 'Expired') {
        $where_clauses[] = "term_end_date < CURDATE()";
    } elseif ($term_status_filter == 'Future') {
        $where_clauses[] = "term_start_date > CURDATE()";
    } elseif ($term_status_filter == 'No Term Set') {
        $where_clauses[] = "(term_start_date IS NULL OR term_start_date = '0000-00-00' OR term_end_date IS NULL OR term_end_date = '0000-00-00')";
    }
}

$where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Determine if we're filtering by Ex- position
$filtering_ex_position = !empty($position_filter) && strpos($position_filter, 'Ex-') === 0;
$filtering_current_position = !empty($position_filter) && strpos($position_filter, 'Ex-') !== 0;

// Build WHERE clauses for current and ex-officials separately
$current_where_clauses = [];
$ex_where_clauses = [];
$current_params = [];
$ex_params = [];
$current_param_types = '';
$ex_param_types = '';

// Add search condition to both if present
if (!empty($search_query)) {
    $current_where_clauses[] = "(fullname LIKE ? OR position LIKE ?)";
    $current_params[] = "%" . $search_query . "%";
    $current_params[] = "%" . $search_query . "%";
    $current_param_types .= 'ss';
    
    $ex_where_clauses[] = "(fullname LIKE ? OR position LIKE ?)";
    $ex_params[] = "%" . $search_query . "%";
    $ex_params[] = "%" . $search_query . "%";
    $ex_param_types .= 'ss';
}

// Add position filter appropriately
if ($filtering_current_position) {
    $current_where_clauses[] = "position = ?";
    $current_params[] = $position_filter;
    $current_param_types .= 's';
} elseif ($filtering_ex_position) {
    $ex_where_clauses[] = "position = ?";
    $ex_params[] = $position_filter;
    $ex_param_types .= 's';
}

// Add term status filter to both if present
if (!empty($term_status_filter)) {
    $term_condition = '';
    if ($term_status_filter == 'Active') {
        $term_condition = "(term_start_date <= CURDATE() AND term_end_date >= CURDATE())";
    } elseif ($term_status_filter == 'Expired') {
        $term_condition = "term_end_date < CURDATE()";
    } elseif ($term_status_filter == 'Future') {
        $term_condition = "term_start_date > CURDATE()";
    } elseif ($term_status_filter == 'No Term Set') {
        $term_condition = "(term_start_date IS NULL OR term_start_date = '0000-00-00' OR term_end_date IS NULL OR term_end_date = '0000-00-00')";
    }
    
    if (!empty($term_condition)) {
        $current_where_clauses[] = $term_condition;
        $ex_where_clauses[] = $term_condition;
    }
}

// Build the final WHERE clauses
$current_where_sql = count($current_where_clauses) > 0 ? ' AND ' . implode(' AND ', $current_where_clauses) : '';
$ex_where_sql = count($ex_where_clauses) > 0 ? ' AND ' . implode(' AND ', $ex_where_clauses) : '';

// Get total number of officials for pagination (only count current officials unless filtering Ex-)
if ($filtering_ex_position) {
    $total_officials_query = "SELECT COUNT(id) AS total FROM officials WHERE position LIKE 'Ex-%'{$ex_where_sql}";
    $total_stmt = mysqli_prepare($link, $total_officials_query);
    if ($total_stmt) {
        if (!empty($ex_params)) {
            mysqli_stmt_bind_param($total_stmt, $ex_param_types, ...$ex_params);
        }
        mysqli_stmt_execute($total_stmt);
        $total_result = mysqli_stmt_get_result($total_stmt);
        $total_row = mysqli_fetch_assoc($total_result);
        $total_officials = $total_row['total'];
        mysqli_stmt_close($total_stmt);
    } else {
        $total_officials = 0;
        error_log("Failed to prepare total officials query: " . mysqli_error($link));
    }
} else {
    $total_officials_query = "SELECT COUNT(id) AS total FROM officials WHERE position NOT LIKE 'Ex-%'{$current_where_sql}";
    $total_stmt = mysqli_prepare($link, $total_officials_query);
    if ($total_stmt) {
        if (!empty($current_params)) {
            mysqli_stmt_bind_param($total_stmt, $current_param_types, ...$current_params);
        }
        mysqli_stmt_execute($total_stmt);
        $total_result = mysqli_stmt_get_result($total_stmt);
        $total_row = mysqli_fetch_assoc($total_result);
        $total_officials = $total_row['total'];
        mysqli_stmt_close($total_stmt);
    } else {
        $total_officials = 0;
        error_log("Failed to prepare total officials query: " . mysqli_error($link));
    }
}

// Fetch officials with search, filter, and pagination - separate current and ex-officials
$current_officials_sql = "SELECT id, fullname, position, term_start_date, term_end_date, contact_number, display_order, image_path FROM officials WHERE position NOT LIKE 'Ex-%'{$current_where_sql} ORDER BY display_order ASC, fullname ASC LIMIT ? OFFSET ?";
$ex_officials_sql = "SELECT id, fullname, position, term_start_date, term_end_date, contact_number, display_order, image_path FROM officials WHERE position LIKE 'Ex-%'{$ex_where_sql} ORDER BY fullname ASC";

// Prepare and execute current officials query (only if not filtering by Ex- position)
$current_officials_result = false;
if (!$filtering_ex_position) {
    $stmt = mysqli_prepare($link, $current_officials_sql);
    if ($stmt) {
        $final_current_params = $current_params;
        $final_current_param_types = $current_param_types;

        // Add limit and offset parameters
        $final_current_params[] = $limit;
        $final_current_params[] = $offset;
        $final_current_param_types .= 'ii';

        if (!empty($final_current_params)) {
            mysqli_stmt_bind_param($stmt, $final_current_param_types, ...$final_current_params);
        }
        mysqli_stmt_execute($stmt);
        $current_officials_result = mysqli_stmt_get_result($stmt);
    } else {
        error_log("Failed to prepare current officials query: " . mysqli_error($link));
    }
}

// Execute ex-officials query (only if not filtering by current position)
$ex_officials_result = false;
if (!$filtering_current_position) {
    $ex_stmt = mysqli_prepare($link, $ex_officials_sql);
    if ($ex_stmt) {
        if (!empty($ex_params)) {
            mysqli_stmt_bind_param($ex_stmt, $ex_param_types, ...$ex_params);
        }
        mysqli_stmt_execute($ex_stmt);
        $ex_officials_result = mysqli_stmt_get_result($ex_stmt);
        
        // Debug logging
        if (!empty($search_query)) {
            error_log("Ex-officials search query: " . $ex_officials_sql);
            error_log("Ex-officials search params: " . print_r($ex_params, true));
            error_log("Ex-officials search results: " . mysqli_num_rows($ex_officials_result));
        }
        
        mysqli_stmt_close($ex_stmt);
    } else {
        error_log("Failed to prepare ex-officials query: " . mysqli_error($link));
        // Fallback query if prepare fails
        if (!empty($search_query)) {
            $search_like = '%' . mysqli_real_escape_string($link, $search_query) . '%';
            $ex_officials_result = mysqli_query($link, "SELECT id, fullname, position, term_start_date, term_end_date, contact_number, display_order, image_path FROM officials WHERE position LIKE 'Ex-%' AND (fullname LIKE '$search_like' OR position LIKE '$search_like') ORDER BY fullname ASC");
            error_log("Used fallback ex-officials query");
        } else {
            $ex_officials_result = mysqli_query($link, "SELECT id, fullname, position, term_start_date, term_end_date, contact_number, display_order, image_path FROM officials WHERE position LIKE 'Ex-%' ORDER BY fullname ASC");
        }
    }
}

// Count current and ex-officials separately
$current_count_sql = "SELECT COUNT(*) as count FROM officials WHERE position NOT LIKE 'Ex-%'{$current_where_sql}";
$ex_count_sql = "SELECT COUNT(*) as count FROM officials WHERE position LIKE 'Ex-%'{$ex_where_sql}";

$current_officials_count = 0;
if (!$filtering_ex_position) {
    $current_stmt = mysqli_prepare($link, $current_count_sql);
    if ($current_stmt) {
        if (!empty($current_params)) {
            mysqli_stmt_bind_param($current_stmt, $current_param_types, ...$current_params);
        }
        mysqli_stmt_execute($current_stmt);
        $current_result = mysqli_stmt_get_result($current_stmt);
        $current_row = mysqli_fetch_assoc($current_result);
        $current_officials_count = $current_row['count'];
        mysqli_stmt_close($current_stmt);
    }
}

$ex_officials_count = 0;
if (!$filtering_current_position) {
    $ex_count_stmt = mysqli_prepare($link, $ex_count_sql);
    if ($ex_count_stmt) {
        if (!empty($ex_params)) {
            mysqli_stmt_bind_param($ex_count_stmt, $ex_param_types, ...$ex_params);
        }
        mysqli_stmt_execute($ex_count_stmt);
        $ex_result = mysqli_stmt_get_result($ex_count_stmt);
        $ex_row = mysqli_fetch_assoc($ex_result);
        $ex_officials_count = $ex_row['count'];
        mysqli_stmt_close($ex_count_stmt);
    }
}

// Update total pages calculation
if ($filtering_ex_position) {
    $total_pages = ceil($ex_officials_count / $limit);
} else {
    $total_pages = ceil($current_officials_count / $limit);
}

// Fetch distinct positions for filter dropdown
$distinct_positions_query = "SELECT DISTINCT position FROM officials ORDER BY position ASC";
$distinct_positions_result = mysqli_query($link, $distinct_positions_query);
$official_positions = [];
if ($distinct_positions_result) {
    while ($row = mysqli_fetch_assoc($distinct_positions_result)) {
        $official_positions[] = $row['position'];
    }
}

// Function to get position category and styling
function getPositionStyling($position) {
    $position_lower = strtolower($position);
    
    if (strpos($position_lower, 'captain') !== false || strpos($position_lower, 'punong') !== false) {
        return ['icon' => 'fa-crown', 'color' => 'text-warning', 'badge' => 'bg-warning'];
    } elseif (strpos($position_lower, 'secretary') !== false) {
        return ['icon' => 'fa-file-alt', 'color' => 'text-info', 'badge' => 'bg-info'];
    } elseif (strpos($position_lower, 'treasurer') !== false) {
        return ['icon' => 'fa-coins', 'color' => 'text-success', 'badge' => 'bg-success'];
    } elseif (strpos($position_lower, 'kagawad') !== false) {
        return ['icon' => 'fa-users', 'color' => 'text-primary', 'badge' => 'bg-primary'];
    } elseif (strpos($position_lower, 'sk') !== false) {
        return ['icon' => 'fa-graduation-cap', 'color' => 'text-warning', 'badge' => 'bg-warning'];
    } elseif (strpos($position_lower, 'tanod') !== false) {
        return ['icon' => 'fa-shield-alt', 'color' => 'text-danger', 'badge' => 'bg-danger'];
    } else {
        return ['icon' => 'fa-user-tie', 'color' => 'text-secondary', 'badge' => 'bg-secondary'];
    }
}

// Function to determine term status
function getTermStatus($start_date, $end_date) {
    if (empty($start_date) || $start_date === '0000-00-00' || empty($end_date) || $end_date === '0000-00-00') {
        return ['status' => 'No Term Set', 'class' => 'bg-secondary'];
    }
    
    $today = new DateTime();
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    
    if ($today < $start) {
        return ['status' => 'Future', 'class' => 'bg-info'];
    } elseif ($today > $end) {
        return ['status' => 'Expired', 'class' => 'bg-danger'];
    } else {
        return ['status' => 'Active', 'class' => 'bg-success'];
    }
}

?>

<!-- Page Header and Add Button -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="dashboard-title"><?php echo html_escape($page_title); // Display the escaped page title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <!-- Link to the form for adding a new official -->
        <a href="official_form.php?action=add" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Add New Official
        </a>
    </div>
</div>

<!-- Status Messages Displayed via Session -->

<!-- Officials Statistics Summary -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title text-primary mb-0">Current Officials</h5>
                        <h2 class="text-primary"><?php echo $current_officials_count; ?></h2>
                        <small class="text-muted">Active positions</small>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-user-tie fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title text-danger mb-0">Former Officials</h5>
                        <h2 class="text-danger"><?php echo $ex_officials_count; ?></h2>
                        <small class="text-muted">Ex- positions</small>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-history fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title text-success mb-0">Total Officials</h5>
                        <h2 class="text-success"><?php echo ($current_officials_count + $ex_officials_count); ?></h2>
                        <small class="text-muted">All records</small>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Officials</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="manage_officials.php" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Search Name/Position</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo html_escape($search_query); ?>" placeholder="e.g., Juan Dela Cruz, Kagawad">
            </div>
            <div class="col-md-3">
                <label for="position" class="form-label">Filter by Position</label>
                <select id="position" name="position" class="form-select">
                    <option value="">All Positions</option>
                    <?php foreach ($official_positions as $position): ?>
                        <option value="<?php echo html_escape($position); ?>" <?php echo ($position_filter == $position) ? 'selected' : ''; ?>><?php echo html_escape($position); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="term_status" class="form-label">Filter by Term Status</label>
                <select id="term_status" name="term_status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Active" <?php echo ($term_status_filter == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Expired" <?php echo ($term_status_filter == 'Expired') ? 'selected' : ''; ?>>Expired</option>
                    <option value="Future" <?php echo ($term_status_filter == 'Future') ? 'selected' : ''; ?>>Future</option>
                    <option value="No Term Set" <?php echo ($term_status_filter == 'No Term Set') ? 'selected' : ''; ?>>No Term Set</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Apply Filters</button>
            </div>
            <?php if (!empty($search_query) || !empty($position_filter) || !empty($term_status_filter)): ?>
            <div class="col-md-12 text-end mt-2">
                <a href="manage_officials.php" class="btn btn-outline-danger btn-sm"><i class="fas fa-times me-1"></i>Clear Filters</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Officials List Card -->
<?php if (!$filtering_ex_position): ?>
<div class="card officials-card">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-user-tie me-2"></i>
                Current Active Officials
                <span class="badge bg-light text-primary ms-2"><?php echo $current_officials_count; ?></span>
            </h5>
            <?php if ($ex_officials_count > 0): ?>
            <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#exOfficialsSection" aria-expanded="false" aria-controls="exOfficialsSection">
                <i class="fas fa-history me-1"></i>
                Show Former Officials (<?php echo $ex_officials_count; ?>)
            </button>
            <?php endif; ?>
        </div>
        <small class="d-block mt-1">
            <?php if (!empty($search_query)): ?>
                Current officials matching your search for "<?php echo html_escape($search_query); ?>"
            <?php else: ?>
                Officials currently holding active positions in the barangay
            <?php endif; ?>
        </small>
    </div>
    <div class="card-body">
        <div class="table-responsive"> <!-- Ensures table is scrollable on small screens -->
            <table class="table table-hover"> <!-- Hover effect for table rows -->
                <thead class="table-light"> <!-- Light background for table header -->
                    <tr>
                        <th>Photo</th>
                        <th>Official Information</th>
                        <th>Position</th>
                        <th>Term Status</th>
                        <th>Contact</th>
                        <th>Display Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php // Check if there are any officials to display
                    if ($current_officials_result && mysqli_num_rows($current_officials_result) > 0): ?>
                        <?php // Loop through each official record and display it in a table row
                        while($official = mysqli_fetch_assoc($current_officials_result)): 
                            $position_styling = getPositionStyling($official['position']);
                            $term_status = getTermStatus($official['term_start_date'], $official['term_end_date']);
                        ?>
                            <tr>
                                <!-- Official Photo -->
                                <td>
                                    <img src="<?php echo !empty($official['image_path']) && file_exists($official['image_path']) ? html_escape($official['image_path']) : 'assets/images/default-avatar.png'; ?>" 
                                         alt="<?php echo html_escape($official['fullname']); ?>" 
                                         class="rounded-circle" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                
                                <!-- Official Information -->
                                <td>
                                    <strong><?php echo html_escape($official['fullname']); ?></strong>
                                    <br><small class="text-muted">ID: <?php echo html_escape($official['id']); ?></small>
                                </td>
                                
                                <!-- Position with Icon -->
                                <td>
                                    <i class="fas <?php echo $position_styling['icon']; ?> <?php echo $position_styling['color']; ?> me-2"></i>
                                    <?php echo html_escape($official['position']); ?>
                                </td>
                                
                                <!-- Term Status -->
                                <td>
                                    <span class="badge <?php echo $term_status['class']; ?> mb-1">
                                        <?php echo $term_status['status']; ?>
                                    </span>
                                    <?php if (!empty($official['term_start_date']) && !empty($official['term_end_date'])): ?>
                                        <br><small class="text-muted">
                                            <?php echo date('M Y', strtotime($official['term_start_date'])); ?> - 
                                            <?php echo date('M Y', strtotime($official['term_end_date'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Contact Information -->
                                <td>
                                    <?php if (!empty($official['contact_number'])): ?>
                                        <i class="fas fa-phone me-1"></i><?php echo html_escape($official['contact_number']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Display Order -->
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo html_escape($official['display_order'] ?? 'Not set'); ?>
                                    </span>
                                </td>
                                
                                <!-- Actions -->
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- View Info button: links to view_official.php with official ID -->
                                        <a href="view_official.php?id=<?php echo html_escape($official['id']); ?>" class="btn btn-outline-info" title="View Complete Information">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Edit button: links to official_form.php with action=edit and official ID -->
                                        <a href="official_form.php?action=edit&id=<?php echo html_escape($official['id']); ?>" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: // If no officials are found (or search yields no results) ?>
                        <tr><td colspan="7" class="text-center">
                            <?php if (!empty($search_query)): ?>
                                No current officials found matching "<?php echo html_escape($search_query); ?>". 
                                <small class="text-muted d-block mt-1">Check the Former Officials section below for Ex- positions.</small>
                            <?php else: ?>
                                No officials found.
                            <?php endif; ?>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo html_escape($search_query); ?>&position=<?php echo html_escape($position_filter); ?>&term_status=<?php echo html_escape($term_status_filter); ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo html_escape($search_query); ?>&position=<?php echo html_escape($position_filter); ?>&term_status=<?php echo html_escape($term_status_filter); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo html_escape($search_query); ?>&position=<?php echo html_escape($position_filter); ?>&term_status=<?php echo html_escape($term_status_filter); ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Enhanced Table Footer with Actions -->
        <div class="card-footer text-muted text-center">
            Displaying <?php echo $current_officials_result ? mysqli_num_rows($current_officials_result) : 0; ?> of <?php echo $current_officials_count; ?> officials.
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Ex-Officials List Card -->
<?php if ($ex_officials_result && mysqli_num_rows($ex_officials_result) > 0): ?>
<div class="<?php echo ($filtering_ex_position || !empty($search_query)) ? '' : 'collapse'; ?> mt-4" id="exOfficialsSection">
    <div class="card officials-card" style="border-left: 4px solid #dc3545;">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2 text-danger"></i>
                    Former Officials (Ex-Officials)
                    <span class="badge bg-danger ms-2"><?php echo mysqli_num_rows($ex_officials_result); ?></span>
                </h5>
                <?php if (!$filtering_ex_position && empty($search_query)): ?>
                <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#exOfficialsSection" aria-expanded="true" aria-controls="exOfficialsSection">
                    <i class="fas fa-eye-slash me-1"></i>
                    Hide Former Officials
                </button>
                <?php endif; ?>
            </div>
            <small class="text-muted d-block mt-1">
                <?php if (!empty($search_query)): ?>
                    Former officials matching your search for "<?php echo html_escape($search_query); ?>"
                <?php else: ?>
                    Officials who previously held positions and have been moved to Ex- status
                <?php endif; ?>
            </small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Photo</th>
                            <th>Former Official Information</th>
                            <th>Previous Position</th>
                            <th>Term Status</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($ex_official = mysqli_fetch_assoc($ex_officials_result)): 
                            $position_styling = getPositionStyling($ex_official['position']);
                            $term_status = getTermStatus($ex_official['term_start_date'], $ex_official['term_end_date']);
                            
                            // Remove Ex- prefix for display and styling
                            $original_position = str_replace('Ex-', '', $ex_official['position']);
                            $original_styling = getPositionStyling($original_position);
                        ?>
                            <tr class="table-secondary" style="opacity: 0.8;">
                                <!-- Ex-Official Photo -->
                                <td>
                                    <div class="position-relative">
                                        <img src="<?php echo !empty($ex_official['image_path']) && file_exists($ex_official['image_path']) ? html_escape($ex_official['image_path']) : 'assets/images/default-avatar.png'; ?>" 
                                             alt="<?php echo html_escape($ex_official['fullname']); ?>" 
                                             class="rounded-circle" 
                                             style="width: 45px; height: 45px; object-fit: cover; filter: grayscale(20%);">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6em;">
                                            EX
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Ex-Official Information -->
                                <td>
                                    <strong class="text-muted"><?php echo html_escape($ex_official['fullname']); ?></strong>
                                    <br><small class="text-muted">ID: <?php echo html_escape($ex_official['id']); ?></small>
                                    <br><small class="text-danger"><i class="fas fa-history me-1"></i>Former Official</small>
                                </td>
                                
                                <!-- Previous Position with Icon -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas <?php echo $original_styling['icon']; ?> text-muted me-2"></i>
                                        <div>
                                            <span class="text-danger fw-bold">Ex-</span><?php echo html_escape($original_position); ?>
                                            <br><small class="text-muted">Previously: <?php echo html_escape($original_position); ?></small>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Term Status -->
                                <td>
                                    <span class="badge bg-secondary mb-1">
                                        Former
                                    </span>
                                    <?php if (!empty($ex_official['term_start_date']) && !empty($ex_official['term_end_date'])): ?>
                                        <br><small class="text-muted">
                                            <?php echo date('M Y', strtotime($ex_official['term_start_date'])); ?> - 
                                            <?php echo date('M Y', strtotime($ex_official['term_end_date'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Contact Information -->
                                <td>
                                    <?php if (!empty($ex_official['contact_number'])): ?>
                                        <i class="fas fa-phone me-1 text-muted"></i>
                                        <span class="text-muted"><?php echo html_escape($ex_official['contact_number']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Actions -->
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- View Info button -->
                                        <a href="view_official.php?id=<?php echo html_escape($ex_official['id']); ?>" class="btn btn-outline-secondary btn-sm" title="View Complete Information">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Edit button -->
                                        <a href="official_form.php?action=edit&id=<?php echo html_escape($ex_official['id']); ?>" class="btn btn-outline-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Ex-Officials Footer -->
            <div class="card-footer bg-light text-muted text-center">
                <i class="fas fa-info-circle me-2"></i>
                <?php if ($filtering_ex_position): ?>
                    Displaying <?php echo mysqli_num_rows($ex_officials_result); ?> of <?php echo $ex_officials_count; ?> former officials matching your filter.
                <?php elseif (!empty($search_query)): ?>
                    Found <?php echo mysqli_num_rows($ex_officials_result); ?> former officials matching "<?php echo html_escape($search_query); ?>"
                <?php else: ?>
                    Total <?php echo $ex_officials_count; ?> former officials in the system.
                    <small>These officials previously held active positions and were automatically moved to Ex- status when new officials were appointed.</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php elseif ($filtering_ex_position): ?>
<div class="card mt-4" style="border-left: 4px solid #dc3545;">
    <div class="card-body text-center text-muted">
        <i class="fas fa-search me-2 text-danger"></i>
        <strong>No Former Officials Found</strong><br>
        <small>No former officials match your current filter criteria.</small>
    </div>
</div>
<?php elseif (!empty($search_query)): ?>
<div class="card mt-4" style="border-left: 4px solid #dc3545;">
    <div class="card-body text-center text-muted">
        <i class="fas fa-search me-2 text-danger"></i>
        <strong>No Former Officials Found</strong><br>
        <small>No former officials match your search for "<?php echo html_escape($search_query); ?>"</small>
    </div>
</div>
<?php else: ?>
<div class="card mt-4" style="border-left: 4px solid #28a745;">
    <div class="card-body text-center text-muted">
        <i class="fas fa-check-circle me-2 text-success"></i>
        <strong>No Former Officials</strong><br>
        <small>There are currently no officials with Ex- status in the system.</small>
    </div>
</div>
<?php endif; ?>

<?php 
// Close prepared statements properly
if (isset($stmt) && $stmt) {
    mysqli_stmt_close($stmt);
}
mysqli_close($link); // Close the database connection
require_once 'includes/footer.php'; // Include the footer
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle toggle button text change for ex-officials section
    const exOfficialsSection = document.getElementById('exOfficialsSection');
    const toggleButtons = document.querySelectorAll('[data-bs-target="#exOfficialsSection"]');
    
    if (exOfficialsSection && toggleButtons.length > 0) {
        exOfficialsSection.addEventListener('shown.bs.collapse', function() {
            toggleButtons.forEach(button => {
                const icon = button.querySelector('i');
                const text = button.querySelector('i').nextSibling;
                if (icon) icon.className = 'fas fa-eye-slash me-1';
                if (text) text.textContent = button.textContent.includes('Show') ? ' Hide Former Officials (<?php echo $ex_officials_count; ?>)' : ' Hide Former Officials';
            });
        });
        
        exOfficialsSection.addEventListener('hidden.bs.collapse', function() {
            toggleButtons.forEach(button => {
                const icon = button.querySelector('i');
                const text = button.querySelector('i').nextSibling;
                if (icon) icon.className = 'fas fa-history me-1';
                if (text) text.textContent = button.textContent.includes('Hide') ? ' Show Former Officials (<?php echo $ex_officials_count; ?>)' : ' Show Former Officials';
            });
        });
    }
});
</script>
