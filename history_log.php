<?php
$page_title = 'System Activity Log';
require_once 'includes/header.php';
require_once 'includes/permissions.php';

// Check if user has permission to access reports
if (!hasPermission('reports', $link)) {
    $_SESSION['error_message'] = 'You do not have permission to access this page.';
    header("Location: index.php");
    exit;
}

// Pagination settings
$records_per_page = isset($_GET['per_page']) && in_array($_GET['per_page'], [10, 25, 50, 100]) ? (int)$_GET['per_page'] : 25;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// View mode - 'pagination' or 'load_more'
$view_mode = isset($_GET['view_mode']) && in_array($_GET['view_mode'], ['pagination', 'load_more']) ? $_GET['view_mode'] : 'pagination';

// Filters
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($link, $_GET['search']) : '';
$filter_type = isset($_GET['type']) ? mysqli_real_escape_string($link, $_GET['type']) : '';
$filter_date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($link, $_GET['date_from']) : '';
$filter_date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($link, $_GET['date_to']) : '';

// Quick date filters
$quick_filter = isset($_GET['quick']) ? $_GET['quick'] : '';
if ($quick_filter) {
    switch($quick_filter) {
        case 'today':
            $filter_date_from = $filter_date_to = date('Y-m-d');
            break;
        case 'yesterday':
            $filter_date_from = $filter_date_to = date('Y-m-d', strtotime('-1 day'));
            break;
        case 'week':
            $filter_date_from = date('Y-m-d', strtotime('-7 days'));
            $filter_date_to = date('Y-m-d');
            break;
        case 'month':
            $filter_date_from = date('Y-m-d', strtotime('-30 days'));
            $filter_date_to = date('Y-m-d');
            break;
    }
}

// Build query conditions
$conditions = [];
$params = [];
$param_types = '';

if (!empty($search_query)) {
    $conditions[] = "activity_description LIKE ?";
    $params[] = "%$search_query%";
    $param_types .= 's';
}
if (!empty($filter_type)) {
    $conditions[] = "activity_type = ?";
    $params[] = $filter_type;
    $param_types .= 's';
}
if (!empty($filter_date_from)) {
    $conditions[] = "DATE(timestamp) >= ?";
    $params[] = $filter_date_from;
    $param_types .= 's';
}
if (!empty($filter_date_to)) {
    $conditions[] = "DATE(timestamp) <= ?";
    $params[] = $filter_date_to;
    $param_types .= 's';
}

$where_sql = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get total count for pagination
$count_sql = "SELECT COUNT(id) as total FROM activities $where_sql";
$total_stmt = mysqli_prepare($link, $count_sql);
if ($total_stmt && !empty($params)) {
    mysqli_stmt_bind_param($total_stmt, $param_types, ...$params);
}
if ($total_stmt) {
    mysqli_stmt_execute($total_stmt);
    $count_result = mysqli_stmt_get_result($total_stmt);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
    mysqli_stmt_close($total_stmt);
} else {
    $total_records = 0;
}

$total_pages = ceil($total_records / $records_per_page);

// Get activities with pagination
$sql = "SELECT id, activity_description, activity_type, timestamp FROM activities $where_sql ORDER BY timestamp DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($link, $sql);

if ($stmt) {
    $current_params = $params;
    $current_param_types = $param_types;
    $current_params[] = $records_per_page;
    $current_params[] = $offset;
    $current_param_types .= 'ii';
    
    mysqli_stmt_bind_param($stmt, $current_param_types, ...$current_params);
    mysqli_stmt_execute($stmt);
    $activities_result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    $activities_result = false;
}

// Get distinct activity types for filter dropdown
$activity_types_sql = "SELECT DISTINCT activity_type FROM activities ORDER BY activity_type ASC";
$activity_types_result = mysqli_query($link, $activity_types_sql);

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="dashboard-title"><i class="fas fa-history me-2"></i><?php echo html_escape($page_title); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="scrollToTop()">
                <i class="fas fa-arrow-up me-1"></i>Top
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="scrollToBottom()">
                <i class="fas fa-arrow-down me-1"></i>Bottom
            </button>
        </div>
    </div>
</div>

<!-- Quick Filter Buttons -->
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <small class="text-success me-2">Quick Filters:</small>
            <a href="?quick=today&<?php echo http_build_query(array_filter(['search' => $search_query, 'type' => $filter_type, 'view_mode' => $view_mode, 'per_page' => $records_per_page])); ?>" 
               class="btn btn-sm <?php echo $quick_filter === 'today' ? 'btn-primary' : 'btn-outline-success'; ?>">Today</a>
            <a href="?quick=yesterday&<?php echo http_build_query(array_filter(['search' => $search_query, 'type' => $filter_type, 'view_mode' => $view_mode, 'per_page' => $records_per_page])); ?>" 
               class="btn btn-sm <?php echo $quick_filter === 'yesterday' ? 'btn-primary' : 'btn-outline-success'; ?>">Yesterday</a>
            <a href="?quick=week&<?php echo http_build_query(array_filter(['search' => $search_query, 'type' => $filter_type, 'view_mode' => $view_mode, 'per_page' => $records_per_page])); ?>" 
               class="btn btn-sm <?php echo $quick_filter === 'week' ? 'btn-primary' : 'btn-outline-success'; ?>">Last 7 Days</a>
            <a href="?quick=month&<?php echo http_build_query(array_filter(['search' => $search_query, 'type' => $filter_type, 'view_mode' => $view_mode, 'per_page' => $records_per_page])); ?>" 
               class="btn btn-sm <?php echo $quick_filter === 'month' ? 'btn-primary' : 'btn-outline-success'; ?>">Last 30 Days</a>
            <a href="history_log.php" class="btn btn-sm btn-outline-success">Clear All</a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Advanced Filters & Options</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="history_log.php" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search Description</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Enter keyword..." value="<?php echo html_escape($search_query); ?>">
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label">Activity Type</label>
                <select name="type" id="type" class="form-select">
                    <option value="">All Types</option>
                    <?php 
                    if ($activity_types_result && mysqli_num_rows($activity_types_result) > 0) {
                        while($type_row = mysqli_fetch_assoc($activity_types_result)) {
                            $selected = ($filter_type == $type_row['activity_type']) ? 'selected' : '';
                            echo '<option value="'.html_escape($type_row['activity_type']).'" '.$selected.'>'.html_escape($type_row['activity_type']).'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo html_escape($filter_date_from); ?>">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo html_escape($filter_date_to); ?>">
            </div>
            <div class="col-md-1">
                <label for="per_page" class="form-label">Per Page</label>
                <select name="per_page" id="per_page" class="form-select">
                    <option value="10" <?php echo $records_per_page == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?php echo $records_per_page == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo $records_per_page == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $records_per_page == 100 ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button class="btn btn-primary w-100" type="submit"><i class="fas fa-search me-1"></i> Filter</button>
            </div>
            
            <!-- Hidden field to preserve view mode -->
            <input type="hidden" name="view_mode" value="<?php echo $view_mode; ?>">
        </form>
        
        <!-- View Mode and Export Options -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="view_mode_toggle" id="pagination_mode" <?php echo $view_mode === 'pagination' ? 'checked' : ''; ?>>
                    <label class="btn btn-outline-primary" for="pagination_mode" onclick="changeViewMode('pagination')">
                        <i class="fas fa-list me-1"></i>Pagination
                    </label>
                    
                    <input type="radio" class="btn-check" name="view_mode_toggle" id="load_more_mode" <?php echo $view_mode === 'load_more' ? 'checked' : ''; ?>>
                    <label class="btn btn-outline-primary" for="load_more_mode" onclick="changeViewMode('load_more')">
                        <i class="fas fa-plus-circle me-1"></i>Load More
                    </label>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <a href="export_history_pdf.php?<?php echo http_build_query(array_filter(['search' => $search_query, 'type' => $filter_type, 'date_from' => $filter_date_from, 'date_to' => $filter_date_to])); ?>" 
                   class="btn btn-danger btn-sm" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i>Export PDF
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>Activity Records</h5>
        <small class="text-muted">
            Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?> records
        </small>
    </div>
    <div class="card-body" id="activities-container">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>Activity Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody id="activities-tbody">
                    <?php if ($activities_result && mysqli_num_rows($activities_result) > 0): ?>
                        <?php while($activity = mysqli_fetch_assoc($activities_result)): ?>
                            <tr>
                                <td><?php echo html_escape($activity['id']); ?></td>
                                <td><?php echo html_escape(date('M d, Y h:i:s A', strtotime($activity['timestamp']))); ?></td>
                                <td><span class="badge bg-secondary"><?php echo html_escape($activity['activity_type']); ?></span></td>
                                <td><?php echo html_escape($activity['activity_description']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">No activities found<?php echo (!empty($search_query) || !empty($filter_type) || !empty($filter_date_from) || !empty($filter_date_to)) ? ' matching your criteria' : ''; ?>.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($view_mode === 'pagination' && $total_pages > 1): ?>
            <!-- Standard Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=1&<?php echo http_build_query(array_filter(['search' => $search_query, 'type' => $filter_type, 'date_from' => $filter_date_from, 'date_to' => $filter_date_to, 'per_page' => $records_per_page, 'view_mode' => $view_mode])); ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter(['search' => $search_query, 'type' => $filter_type, 'date_from' => $filter_date_from, 'date_to' => $filter_date_to, 'per_page' => $records_per_page, 'view_mode' => $view_mode])); ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter(['search' => $search_query, 'type' => $filter_type, 'date_from' => $filter_date_from, 'date_to' => $filter_date_to, 'per_page' => $records_per_page, 'view_mode' => $view_mode])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter(['search' => $search_query, 'type' => $filter_type, 'date_from' => $filter_date_from, 'date_to' => $filter_date_to, 'per_page' => $records_per_page, 'view_mode' => $view_mode])); ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?>&<?php echo http_build_query(array_filter(['search' => $search_query, 'type' => $filter_type, 'date_from' => $filter_date_from, 'date_to' => $filter_date_to, 'per_page' => $records_per_page, 'view_mode' => $view_mode])); ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </li>
                </ul>
                
                <!-- Page Jump -->
                <div class="text-center mt-2">
                    <form method="GET" class="d-inline-flex align-items-center gap-2">
                        <small>Jump to page:</small>
                        <input type="number" name="page" class="form-control form-control-sm" style="width: 80px;" min="1" max="<?php echo $total_pages; ?>" value="<?php echo $page; ?>">
                        <input type="hidden" name="search" value="<?php echo html_escape($search_query); ?>">
                        <input type="hidden" name="type" value="<?php echo html_escape($filter_type); ?>">
                        <input type="hidden" name="date_from" value="<?php echo html_escape($filter_date_from); ?>">
                        <input type="hidden" name="date_to" value="<?php echo html_escape($filter_date_to); ?>">
                        <input type="hidden" name="per_page" value="<?php echo $records_per_page; ?>">
                        <input type="hidden" name="view_mode" value="<?php echo $view_mode; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Go</button>
                    </form>
                </div>
            </nav>
        <?php elseif ($view_mode === 'load_more' && $page < $total_pages): ?>
            <!-- Load More Button -->
            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary" id="load-more-btn" data-page="<?php echo $page + 1; ?>">
                    <i class="fas fa-plus-circle me-2"></i>Load More Activities
                    <span class="badge bg-light text-dark ms-2"><?php echo min($records_per_page, $total_records - ($page * $records_per_page)); ?> more</span>
                </button>
                <div class="mt-2">
                    <small class="text-muted">Loaded <?php echo min($page * $records_per_page, $total_records); ?> of <?php echo $total_records; ?> records</small>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Loading indicator for Load More -->
<div id="loading-indicator" class="text-center mt-3" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Loading more activities...</p>
</div>

<script>
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function scrollToBottom() {
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
}

function changeViewMode(mode) {
    const url = new URL(window.location);
    url.searchParams.set('view_mode', mode);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}

// Load More functionality
document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadingIndicator = document.getElementById('loading-indicator');
    const activitiesTbody = document.getElementById('activities-tbody');
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const nextPage = this.getAttribute('data-page');
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('page', nextPage);
            urlParams.set('ajax', '1');
            
            loadMoreBtn.style.display = 'none';
            loadingIndicator.style.display = 'block';
            
            fetch('?' + urlParams.toString())
                .then(response => response.text())
                .then(data => {
                    // Parse the response to extract just the table rows
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data, 'text/html');
                    const newRows = doc.querySelectorAll('#activities-tbody tr');
                    
                    newRows.forEach(row => {
                        activitiesTbody.appendChild(row);
                    });
                    
                    // Update button for next page or hide if no more pages
                    const currentPage = parseInt(nextPage);
                    const totalPages = <?php echo $total_pages; ?>;
                    
                    if (currentPage < totalPages) {
                        loadMoreBtn.setAttribute('data-page', currentPage + 1);
                        loadMoreBtn.style.display = 'block';
                        
                        // Update remaining count
                        const remaining = Math.min(<?php echo $records_per_page; ?>, <?php echo $total_records; ?> - (currentPage * <?php echo $records_per_page; ?>));
                        loadMoreBtn.querySelector('.badge').textContent = remaining + ' more';
                        
                        // Update loaded count
                        const loaded = Math.min(currentPage * <?php echo $records_per_page; ?>, <?php echo $total_records; ?>);
                        loadMoreBtn.parentElement.querySelector('small').innerHTML = 'Loaded ' + loaded + ' of <?php echo $total_records; ?> records';
                    }
                    
                    loadingIndicator.style.display = 'none';
                })
                .catch(error => {
                    console.error('Error loading more activities:', error);
                    loadMoreBtn.style.display = 'block';
                    loadingIndicator.style.display = 'none';
                    alert('Error loading more activities. Please try again.');
                });
        });
    }
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'Home':
                e.preventDefault();
                scrollToTop();
                break;
            case 'End':
                e.preventDefault();
                scrollToBottom();
                break;
        }
    }
});
</script>

<style>
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
    background: white;
}

.btn-check:checked + .btn-outline-primary {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

#loading-indicator {
    padding: 2rem;
}

.page-link {
    border-radius: 0.375rem;
    margin: 0 2px;
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
</style>

<?php 
// Handle AJAX requests for Load More
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    // Return only the table rows for AJAX requests
    if ($activities_result && mysqli_num_rows($activities_result) > 0) {
        while($activity = mysqli_fetch_assoc($activities_result)) {
            echo '<tr>';
            echo '<td>' . html_escape($activity['id']) . '</td>';
            echo '<td>' . html_escape(date('M d, Y h:i:s A', strtotime($activity['timestamp']))) . '</td>';
            echo '<td><span class="badge bg-secondary">' . html_escape($activity['activity_type']) . '</span></td>';
            echo '<td>' . html_escape($activity['activity_description']) . '</td>';
            echo '</tr>';
        }
    }
    exit;
}

require_once 'includes/footer.php'; 
?>
