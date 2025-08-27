<?php
// residents_search.php - AJAX endpoint for Select2 resident search
require_once 'config.php';

header('Content-Type: application/json');

$term = trim($_GET['term'] ?? '');
$results = [];

if ($term !== '') {
    // Prepare search: match last_name or first_name starting with term, case-insensitive
    $like_term = '%' . $term . '%';
    $sql = "SELECT id, CONCAT(last_name, ', ', first_name, ' ', COALESCE(suffix, '')) AS display_name
            FROM residents
            WHERE status = 'Active' AND (last_name LIKE ? OR first_name LIKE ?)
            ORDER BY last_name ASC, first_name ASC
            LIMIT 20"; // limit results for performance
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $like_term, $like_term);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'id' => $row['id'],
            'text' => $row['display_name']
        ];
    }
}

echo json_encode(['results' => $results]);
?> 