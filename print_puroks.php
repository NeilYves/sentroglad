<?php
require_once 'config.php';

// Check if we're printing a specific purok or all residents
$specific_purok_id = isset($_GET['purok_id']) ? (int)$_GET['purok_id'] : null;
$specific_purok_name = '';

// If printing a specific purok, get its name
if ($specific_purok_id) {
    $purok_name_query = "SELECT purok_name FROM puroks WHERE id = ?";
    $stmt = mysqli_prepare($link, $purok_name_query);
    mysqli_stmt_bind_param($stmt, "i", $specific_purok_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $specific_purok_name = $row['purok_name'];
    }
    mysqli_stmt_close($stmt);
}

// Get total count of residents
$count_query = $specific_purok_id ?
    "SELECT COUNT(*) as total FROM residents WHERE purok_id = ? AND status = 'Active'" :
    "SELECT COUNT(*) as total FROM residents WHERE status = 'Active'";

if ($specific_purok_id) {
    $stmt = mysqli_prepare($link, $count_query);
    mysqli_stmt_bind_param($stmt, "i", $specific_purok_id);
    mysqli_stmt_execute($stmt);
    $count_result = mysqli_stmt_get_result($stmt);
    $total_residents = mysqli_fetch_assoc($count_result)['total'];
    mysqli_stmt_close($stmt);
} else {
    $count_result = mysqli_query($link, $count_query);
    $total_residents = mysqli_fetch_assoc($count_result)['total'];
}

/**
 * Renders a complete list of all residents with comprehensive information
 *
 * @param mysqli $link The database connection object.
 * @param int|null $purok_id Optional purok ID to filter by specific purok
 */
function render_all_residents_table($link, $purok_id = null) {
    // Build query based on whether we're filtering by purok or showing all
    if ($purok_id) {
        $residents_query = "SELECT r.id, r.first_name, r.middle_name, r.last_name, r.suffix,
                                  r.gender, r.birthdate, r.civil_status, r.contact_number,
                                  r.educational_attainment, r.family_planning, r.maintenance_medicine,
                                  r.water_source, r.toilet_facility, r.pantawid_4ps, r.backyard_gardening,
                                  p.purok_name
                           FROM residents r
                           LEFT JOIN puroks p ON r.purok_id = p.id
                           WHERE r.purok_id = ? AND r.status = 'Active'
                           ORDER BY r.last_name ASC, r.first_name ASC, r.middle_name ASC";
        $stmt = mysqli_prepare($link, $residents_query);
        mysqli_stmt_bind_param($stmt, "i", $purok_id);
        mysqli_stmt_execute($stmt);
        $residents_result = mysqli_stmt_get_result($stmt);
    } else {
        $residents_query = "SELECT r.id, r.first_name, r.middle_name, r.last_name, r.suffix,
                                  r.gender, r.birthdate, r.civil_status, r.contact_number,
                                  r.educational_attainment, r.family_planning, r.maintenance_medicine,
                                  r.water_source, r.toilet_facility, r.pantawid_4ps, r.backyard_gardening,
                                  p.purok_name
                           FROM residents r
                           LEFT JOIN puroks p ON r.purok_id = p.id
                           WHERE r.status = 'Active'
                           ORDER BY r.last_name ASC, r.first_name ASC, r.middle_name ASC";
        $residents_result = mysqli_query($link, $residents_query);
    }

    if ($residents_result && mysqli_num_rows($residents_result) > 0) {
        $counter = 1;
        while ($resident = mysqli_fetch_assoc($residents_result)) {
            // Format full name as "Last Name, First Name Middle Name"
            $fullname = html_escape($resident['last_name']);
            if (!empty($resident['first_name'])) {
                $fullname .= ', ' . html_escape($resident['first_name']);
                if (!empty($resident['middle_name'])) {
                    $fullname .= ' ' . html_escape($resident['middle_name']);
                }
            }
            if (!empty($resident['suffix'])) {
                $fullname .= ' ' . html_escape($resident['suffix']);
            }

            // Calculate age
            $age = 'N/A';
            if (!empty($resident['birthdate']) && $resident['birthdate'] != '0000-00-00') {
                $birthDate = new DateTime($resident['birthdate']);
                $today = new DateTime('today');
                $age = $birthDate->diff($today)->y;
            }

            // Format birthdate
            $birthdate_formatted = 'N/A';
            if (!empty($resident['birthdate']) && $resident['birthdate'] != '0000-00-00') {
                $birthdate_formatted = date('M j, Y', strtotime($resident['birthdate']));
            }

            echo '<tr>';
            echo '<td class="text-center">' . $counter . '</td>';
            echo '<td>' . $fullname . '</td>';
            echo '<td class="text-center">' . html_escape($resident['gender'] ?: 'N/A') . '</td>';
            echo '<td class="text-center">' . $age . '</td>';
            echo '<td class="text-center">' . $birthdate_formatted . '</td>';
            echo '<td class="text-center">' . html_escape($resident['civil_status'] ?: 'N/A') . '</td>';
            echo '<td>' . html_escape($resident['educational_attainment'] ?: 'N/A') . '</td>';
            echo '<td class="text-center">' . html_escape($resident['family_planning'] ?: 'N/A') . '</td>';
            echo '<td>' . html_escape($resident['maintenance_medicine'] ?: 'None') . '</td>';
            echo '<td>' . html_escape($resident['water_source'] ?: 'N/A') . '</td>';
            echo '<td>' . html_escape($resident['toilet_facility'] ?: 'N/A') . '</td>';
            echo '<td class="text-center">' . html_escape($resident['pantawid_4ps'] ?: 'No') . '</td>';
            echo '<td class="text-center">' . html_escape($resident['backyard_gardening'] ?: 'No') . '</td>';
            echo '<td>' . html_escape($resident['purok_name'] ?: 'Not Assigned') . '</td>';
            echo '<td>' . html_escape($resident['contact_number'] ?: 'N/A') . '</td>';
            echo '</tr>';
            $counter++;
        }
    } else {
        echo '<tr><td colspan="15" class="text-center text-muted">No residents found.</td></tr>';
    }

    if ($purok_id && isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
}
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Additional WHERE conditions
$extra_condition = "";

switch ($filter) {
    case 'male':
        $extra_condition = " AND r.gender = 'Male'";
        break;
    case 'female':
        $extra_condition = " AND r.gender = 'Female'";
        break;
    case 'child':
        $extra_condition = " AND TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) < 13";
        break;
    case 'youth':
        $extra_condition = " AND TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) BETWEEN 13 AND 30";
        break;
    case 'adult':
        $extra_condition = " AND TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) BETWEEN 31 AND 59";
        break;
    case 'senior':
        $extra_condition = " AND TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) >= 60";
        break;
    case 'all':
    default:
        $extra_condition = "";
        break;
}
// Secretary
$secretary_name = "________________";
$sql = "SELECT fullname FROM officials WHERE position = 'Barangay Secretary' ORDER BY term_start_date DESC LIMIT 1";
$result = mysqli_query($link, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $secretary_name = $row['fullname'];
}

// Captain (Punong Barangay)
$captain_name = "________________";
$sql = "SELECT fullname FROM officials WHERE position = 'Punong Barangay' ORDER BY term_start_date DESC LIMIT 1";
$result = mysqli_query($link, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $captain_name = $row['fullname'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List of Residents - Barangay Central Glad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body {
                font-size: 9pt;
                margin: 0;
                padding: 0;
                background: white !important;
            }
            .no-print { display: none !important; }
            .print-container {
                width: 100% !important;
                max-width: none !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 10px !important;
            }
            .header-section {
                margin-bottom: 15px !important;
            }
            .logo {
                width: 60px !important;
                height: 60px !important;
            }
            .main-title {
                font-size: 16pt !important;
                font-weight: bold !important;
                margin: 8px 0 !important;
            }
            .sub-title {
                font-size: 12pt !important;
                margin: 3px 0 !important;
            }
            .summary-info {
                margin-bottom: 10px !important;
                padding: 8px !important;
                font-size: 9pt !important;
            }
            .residents-table {
                font-size: 7pt !important;
                margin-top: 10px !important;
                width: 100% !important;
            }
            .residents-table th {
                background-color: #f8f9fa !important;
                font-weight: bold !important;
                padding: 3px 2px !important;
                border: 1px solid #000 !important;
                font-size: 7pt !important;
                text-align: center !important;
            }
            .residents-table td {
                padding: 2px 1px !important;
                border: 1px solid #000 !important;
                font-size: 6pt !important;
                line-height: 1.2 !important;
            }
            .print-info {
                margin-top: 20px !important;
                font-size: 8pt !important;
            }
            /* Landscape orientation for better fit */
            @page {
                size: landscape;
                margin: 0.5in;
            }
        }

        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }

        .print-container {
            max-width: 1400px;
            margin: 1rem auto;
            padding: 1.5rem;
            background: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .header-section {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 20px;
        }

        .logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border: 2px solid #ddd;
            border-radius: 50%;
            padding: 5px;
            background: white;
        }

        .main-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sub-title {
            font-size: 18px;
            color: #34495e;
            margin: 5px 0;
        }

        .location-info {
            font-size: 14px;
            color: #7f8c8d;
            font-style: italic;
            margin-top: 10px;
        }

        .residents-table {
            width: 100%;
            margin-top: 25px;
            font-size: 11px;
            table-layout: fixed;
        }

        .residents-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 8px 4px;
            border: 1px solid #0056b3;
            font-size: 10px;
            line-height: 1.2;
        }

        .residents-table td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            font-size: 10px;
            line-height: 1.3;
            word-wrap: break-word;
        }

        .residents-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .residents-table tbody tr:hover {
            background-color: #e3f2fd;
        }

        .print-info {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #6c757d;
            text-align: center;
        }

        .summary-info {
            background-color: #e8f4fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
.logo {
    width: 100px;
    height: 100px;
    object-fit: contain;
    border: 2px solid #ddd;
    border-radius: 50%;
    padding: 5px;
    background: white;
    margin-left: 90px;   /* indent from left */
    margin-right: 90px;  /* indent from right */
}

/* Print-friendly adjustments */
@media print {
    .logo {
        width: 60px !important;
        height: 60px !important;
        border: 1px solid #000;
        margin-left: 50px ;   /* smaller indent when printing */
        margin-right: 50px ;
    }
    
}
/* Default screen view */
.signatories {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
}
.signatories .sig {
    flex: 1;
    text-align: center;
}

/* Print view */
@media print {
    .signatories {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-around !important;
        margin-top: 80px !important;
    }
    .signatories .sig {
        width: 45% !important;
        text-align: center !important;
        font-size: 12pt !important;
    }
}



    </style>
</head>
<body>

<div class="print-container">
    <!-- Print Button (Hidden when printing) -->
    <div class="text-center mb-4 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <i class="fas fa-print"></i> Print Residents List
        </button>
        <a href="manage_residents.php" class="btn btn-secondary btn-lg ms-2">
            <i class="fas fa-arrow-left"></i> Back to Residents
        </a>
    </div>

    <!-- Header Section with Logos -->
  <!-- Header Section with Logos -->
<div class="header-section d-flex justify-content-between align-items-center">
    <!-- Barangay Logo (Left Side) -->
    <div class="text-start">
        <img src="images/barangay_logo_path_684cdba5162442.67680959.png" alt="Barangay Logo" class="logo"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
        
    </div>

    <!-- Title (Center) -->
    <div class="text-center flex-grow-1">
        <div class="main-title">List of Residents in Barangay Central Glad</div>
        <div class="sub-title">Municipality of Midsayap</div>
        <div class="sub-title">Province of North Cotabato</div>
        <div class="location-info">
            <?php echo $specific_purok_id ? 'Purok: ' . html_escape($specific_purok_name) : 'All Puroks'; ?>
        </div>
    </div>

    <!-- Municipality Logo (Right Side) -->
    <div class="text-end">
        <img src="images/municipality_logo_path_684cdac128d731.01757220.png" alt="Municipality Logo" class="logo"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
        
    </div>
</div>


        
    </div>

    <!-- Summary Information -->
    <div class="summary-info">
        <div class="no-print mb-3 text-center">
    <a href="?filter=all<?php echo $specific_purok_id ? '&purok_id=' . $specific_purok_id : ''; ?>" class="btn btn-secondary btn-sm">Show All</a>
    <a href="?filter=male<?php echo $specific_purok_id ? '&purok_id=' . $specific_purok_id : ''; ?>" class="btn btn-primary btn-sm">Male</a>
    <a href="?filter=female<?php echo $specific_purok_id ? '&purok_id=' . $specific_purok_id : ''; ?>" class="btn btn-danger btn-sm">Female</a>
    <a href="?filter=child<?php echo $specific_purok_id ? '&purok_id=' . $specific_purok_id : ''; ?>" class="btn btn-info btn-sm">Child (&lt;13)</a>
    <a href="?filter=youth<?php echo $specific_purok_id ? '&purok_id=' . $specific_purok_id : ''; ?>" class="btn btn-success btn-sm">Youth (13-30)</a>
    <a href="?filter=adult<?php echo $specific_purok_id ? '&purok_id=' . $specific_purok_id : ''; ?>" class="btn btn-warning btn-sm">Adult (31-59)</a>
    <a href="?filter=senior<?php echo $specific_purok_id ? '&purok_id=' . $specific_purok_id : ''; ?>" class="btn btn-dark btn-sm">Senior (60+)</a>
</div>

        <div class="row">
            <div class="col-md-6">
                <strong>Total Residents:</strong> <?php echo number_format($total_residents); ?>
            </div>
            <div class="col-md-6 text-end">
                <strong>Report Generated:</strong> <?php echo date('F j, Y \a\t g:i A'); ?>
            </div>
        </div>
    </div>

    <!-- Residents Table -->
    <table class="table table-bordered residents-table">
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 15%;">Full Name</th>
                <th style="width: 5%;">Gender</th>
                <th style="width: 4%;">Age</th>
                <th style="width: 8%;">Birthday</th>
                <th style="width: 7%;">Civil Status</th>
                <th style="width: 10%;">Educational Attainment</th>
                <th style="width: 6%;">Family Planning</th>
                <th style="width: 8%;">Maintenance Medicine</th>
                <th style="width: 8%;">Water Source</th>
                <th style="width: 6%;">Toilet Facility</th>
                <th style="width: 5%;">4Ps</th>
                <th style="width: 6%;">Backyard Gardening</th>
                <th style="width: 8%;">Purok</th>
                <th style="width: 11%;">Contact Number</th>
            </tr>
        </thead>
        <tbody>
            <?php render_all_residents_table($link, $specific_purok_id); ?>
</tbody>


    </table>

    <!-- Print Information -->
    <!-- Print Information -->
<div class="print-info">
    <div class="row text-center signatories">
        <!-- Prepared By -->
        <div class="col-md-4 sig">
            <small>
                <strong>Prepared by:</strong><br><br>
                <u><?php echo htmlspecialchars($secretary_name); ?></u>
                <br>
                Barangay Secretary<br>
            </small>
        </div>

        <!-- Noted By -->
        <div class="col-md-4 sig">
            <small>
                <strong>Noted by:</strong><br><br>
                <u><?php echo htmlspecialchars($captain_name); ?></u>
                <br>Barangay Captain<br>
            </small>
        </div>

        <!-- Date -->
        
    </div>
</div>


</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>