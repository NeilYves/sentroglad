<?php
// --- Issue New Certificate Form Page ---
// This script generates a form for issuing new barangay certificates.
// It fetches necessary data like residents, certificate types, and available signing officials.

$page_title = 'Issue New Certificate';
require_once 'includes/header.php';

// --- Data Fetching for Form Dropdowns and Defaults ---

// Fetch all active certificate types.
$cert_types_sql = "SELECT id, name, default_purpose FROM certificate_types WHERE is_active = 1 ORDER BY name ASC";
$cert_types_result = mysqli_query($link, $cert_types_sql);
$certificate_types = [];
if ($cert_types_result) {
    while($row = mysqli_fetch_assoc($cert_types_result)) {
        $certificate_types[] = $row;
    }
}

// Fetch all available signing officials - Punong Barangay, Captain, and ALL Kagawads
$signing_officials = [];
$punong_barangay_available = false;

// First, check if Punong Barangay is available
$punong_barangay_sql = "SELECT id, fullname, position,
                        CASE
                            WHEN (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE())
                            AND position NOT LIKE 'Ex-%'
                            AND position NOT LIKE 'Former %' THEN 1
                            ELSE 0
                        END as is_available
                        FROM officials
                        WHERE (position = 'Punong Barangay' OR position LIKE '%Captain%')
                        ORDER BY
                            CASE WHEN position = 'Punong Barangay' THEN 1 ELSE 2 END,
                            fullname ASC";
$punong_barangay_result = mysqli_query($link, $punong_barangay_sql);
if ($punong_barangay_result) {
    while ($pb_row = mysqli_fetch_assoc($punong_barangay_result)) {
        if ($pb_row['is_available']) {
            $signing_officials[] = [
                'id' => $pb_row['id'],
                'fullname' => $pb_row['fullname'],
                'position' => $pb_row['position'],
                'priority' => 1,
                'is_available' => true
            ];
        }
    }
}

// FIXED: Fetch ALL Kagawads (including those with committee assignments)
$kagawad_sql = "SELECT id, fullname, position,
                CASE
                    WHEN (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE())
                    AND position NOT LIKE 'Ex-%'
                    AND position NOT LIKE 'Former %' THEN 1
                    ELSE 0
                END as is_available
                FROM officials
                WHERE (
                    position LIKE '%Kagawad%'
                    OR position LIKE 'Kagawad%'
                    OR position LIKE '%SK%'
                )
                AND position NOT LIKE 'Ex-%'
                AND position NOT LIKE 'Former %'
                AND (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE())
                ORDER BY
                    CASE
                        WHEN position LIKE '%SK%Chairman%' THEN 1
                        WHEN position LIKE 'Barangay Kagawad - Committee on%' THEN 2
                        WHEN position LIKE 'Kagawad - Committee on%' THEN 3
                        WHEN position LIKE '%Kagawad%' THEN 4
                        WHEN position LIKE 'Kagawad%' THEN 5
                        ELSE 6
                    END,
                    position ASC,
                    fullname ASC";

$kagawad_result = mysqli_query($link, $kagawad_sql);
if ($kagawad_result) {
    while ($row = mysqli_fetch_assoc($kagawad_result)) {
        if ($row['is_available']) {
            $signing_officials[] = [
                'id' => $row['id'],
                'fullname' => $row['fullname'],
                'position' => $row['position'],
                'priority' => 2,
                'is_available' => true
            ];
        }
    }
}

// Also include Secretary and Treasurer if available
$other_officials_sql = "SELECT id, fullname, position,
                       CASE
                           WHEN (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE())
                           AND position NOT LIKE 'Ex-%'
                           AND position NOT LIKE 'Former %' THEN 1
                           ELSE 0
                       END as is_available
                       FROM officials
                       WHERE (position LIKE '%Secretary%' OR position LIKE '%Treasurer%')
                       AND position NOT LIKE 'Ex-%'
                       AND position NOT LIKE 'Former %'
                       AND (term_end_date IS NULL OR term_end_date = '' OR term_end_date >= CURDATE())
                       ORDER BY position ASC, fullname ASC";

$other_result = mysqli_query($link, $other_officials_sql);
if ($other_result) {
    while ($row = mysqli_fetch_assoc($other_result)) {
        if ($row['is_available']) {
            $signing_officials[] = [
                'id' => $row['id'],
                'fullname' => $row['fullname'],
                'position' => $row['position'],
                'priority' => 3,
                'is_available' => true
            ];
        }
    }
}

// Enhanced Debug: Log what officials were found with detailed breakdown
error_log("=== SIGNING OFFICIALS DEBUG ===");
error_log("Found " . count($signing_officials) . " available signing officials");
error_log("Kagawad SQL Query: " . $kagawad_sql);

$kagawad_count = 0;
$committee_kagawad_count = 0;
$regular_kagawad_count = 0;

foreach ($signing_officials as $official) {
    error_log("Official: " . $official['fullname'] . " - " . $official['position'] . " (Priority: " . $official['priority'] . ")");

    // Count different types of Kagawads
    if (strpos($official['position'], 'Kagawad') !== false) {
        $kagawad_count++;
        if (strpos($official['position'], 'Committee on') !== false) {
            $committee_kagawad_count++;
        } else {
            $regular_kagawad_count++;
        }
    }
}

error_log("Total Kagawads: $kagawad_count (Committee: $committee_kagawad_count, Regular: $regular_kagawad_count)");
error_log("=== END DEBUG ===");

// --- Handle Potential Error Messages ---
$message = '';
if (isset($_GET['status']) && strpos($_GET['status'], 'error_') === 0) {
    $error_type = str_replace('error_', '', $_GET['status']);
    switch ($error_type) {
        case 'no_officials':
            $message = '<div class="alert alert-danger">Error: No active signing officials available. Please contact the administrator.</div>';
            break;
        case 'missing_resident_id':
            $message = '<div class="alert alert-danger">Error: Please select a resident.</div>';
            break;
        case 'invalid_resident':
            $message = '<div class="alert alert-danger">Error: Selected resident is invalid or inactive. Please select a valid resident.</div>';
            break;
        case 'missing_certificate_type_id':
            $message = '<div class="alert alert-danger">Error: Please select a certificate type.</div>';
            break;
        case 'missing_purpose':
            $message = '<div class="alert alert-danger">Error: Please enter the purpose for this certificate.</div>';
            break;
        case 'missing_issue_date':
            $message = '<div class="alert alert-danger">Error: Please enter the issue date.</div>';
            break;
        default:
            $message = '<div class="alert alert-danger">Error: ' . html_escape($error_type) . '</div>';
    }
}

// If there are no certificate types defined, we cannot proceed.
if (empty($certificate_types)) {
    echo '<div class="alert alert-danger"><strong>Setup Error:</strong> No active certificate types found in the database. Please add a certificate type in the `certificate_types` table.</div>';
    require_once 'includes/footer.php';
    exit;
}

// Check if any signing officials are available
$any_officials_available = !empty($signing_officials);
if (!$any_officials_available) {
    echo '<div class="alert alert-danger">
        <strong>Setup Error:</strong> No available signing officials found.
        <br><small>Please ensure there are active officials in the system with positions like "Punong Barangay", "Barangay Captain", or "Barangay Kagawad".</small>
    </div>';
    require_once 'includes/footer.php';
    exit;
}
?>

<!-- Page Header and Back Button -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="dashboard-title"><i class="fas fa-file-signature me-2"></i><?php echo html_escape($page_title); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="manage_certificates.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Certificates List
        </a>
    </div>
</div>

<?php echo $message; ?>

<!-- Certificate Issuance Form Card -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0" id="certificate-title">Issue New Certificate</h5>
    </div>
    <div class="card-body">
        <form action="certificate_handler.php" method="POST" id="issueCertificateForm">
            <input type="hidden" name="action" value="issue">

            <!-- Certificate Type Selection -->
            <div class="mb-3 row">
                <label for="certificate_type_id" class="col-sm-3 col-form-label">Certificate Type <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <select class="form-select" id="certificate_type_id" name="certificate_type_id" required>
                        <option value="">-- Select Certificate Type --</option>
                        <?php foreach ($certificate_types as $type): ?>
                            <option value="<?php echo html_escape($type['id']); ?>" data-purpose="<?php echo html_escape($type['default_purpose']); ?>">
                                <?php echo html_escape($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Resident Selection Dropdown (Required) - Hidden for Business Clearance -->
            <div class="mb-3 row" id="regular-resident-field">
                <label for="resident_id" class="col-sm-3 col-form-label">Select Resident <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <select class="form-select" id="resident_id" name="resident_id" required>
                        <option value="">-- Select Resident --</option>
                    </select>
                </div>
            </div>

            <!-- Signing Official Selection (Required) -->
            <div class="mb-3 row">
                <label for="signing_official_id" class="col-sm-3 col-form-label">
                    Signing Official <span class="text-danger">*</span>
                </label>
                <div class="col-sm-9">
                    <select class="form-select" id="signing_official_id" name="signing_official_id" required>
                        <option value="">-- Select Signing Official --</option>
                        <?php
                        // Sort officials by priority and position
                        usort($signing_officials, function($a, $b) {
                            if ($a['priority'] !== $b['priority']) {
                                return $a['priority'] - $b['priority'];
                            }
                            return strcmp($a['position'], $b['position']);
                        });

                        // Group officials by type for better organization
                        $grouped_officials = [];
                        foreach ($signing_officials as $official) {
                            if (strpos($official['position'], 'Punong Barangay') !== false || strpos($official['position'], 'Captain') !== false) {
                                $grouped_officials['Punong Barangay'][] = $official;
                            } elseif (strpos($official['position'], 'SK') !== false) {
                                $grouped_officials['SK Officials'][] = $official;
                            } elseif (strpos($official['position'], 'Committee on') !== false) {
                                $grouped_officials['Committee Kagawads'][] = $official;
                            } elseif (strpos($official['position'], 'Kagawad') !== false) {
                                $grouped_officials['Regular Kagawads'][] = $official;
                            } else {
                                $grouped_officials['Other Officials'][] = $official;
                            }
                        }

                        // Display grouped options
                        foreach ($grouped_officials as $group_name => $group_officials):
                            if (!empty($group_officials)): ?>
                                <optgroup label="<?php echo html_escape($group_name); ?>">
                                    <?php foreach ($group_officials as $official): ?>
                                        <option value="<?php echo html_escape($official['id']); ?>"
                                                data-priority="<?php echo html_escape($official['priority']); ?>"
                                                data-position="<?php echo html_escape($official['position']); ?>">
                                            <?php echo html_escape($official['fullname'] . ' (' . $official['position'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif;
                        endforeach; ?>
                    </select>

                    <small class="form-text text-info">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Available Officials (<?php echo count($signing_officials); ?>):</strong> Select from available Punong Barangay, Captain, or Kagawads.
                    </small>

                    <small class="form-text text-muted d-block mt-1">
                        <strong>Current Available Officials (<?php echo count($signing_officials); ?>):</strong>
                        <br>
                        <?php
                        $official_groups = [];
                        foreach ($signing_officials as $official) {
                            $position_type = $official['position'];
                            if (strpos($position_type, 'Punong Barangay') !== false || strpos($position_type, 'Captain') !== false) {
                                $position_type = 'Punong Barangay';
                            } elseif (strpos($position_type, 'Kagawad') !== false) {
                                $position_type = 'Kagawads';
                            } elseif (strpos($position_type, 'SK') !== false) {
                                $position_type = 'SK Officials';
                            } else {
                                $position_type = 'Other Officials';
                            }

                            if (!isset($official_groups[$position_type])) {
                                $official_groups[$position_type] = [];
                            }
                            $official_groups[$position_type][] = $official['fullname'] . ' (' . $official['position'] . ')';
                        }

                        foreach ($official_groups as $group => $officials) {
                            echo '<strong>' . $group . ':</strong> ' . implode(', ', $officials) . '<br>';
                        }
                        ?>
                    </small>
                </div>
            </div>

            <!-- Business-Specific Fields (Hidden by default, shown when Business Clearance is selected) -->
            <div id="business-fields" style="display: none;">
                <!-- Business Name Field -->
                <div class="mb-3 row">
                    <label for="business_name" class="col-sm-3 col-form-label">Business Name or Trade Activity <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="business_name" name="business_name" placeholder="Enter business name or trade activity">
                        <small class="form-text text-muted">Enter the name of the business or describe the trade activity.</small>
                    </div>
                </div>

                <!-- Combined Resident/Operator Field for Business Clearance -->
                <div class="mb-3 row">
                    <label for="business_resident_id" class="col-sm-3 col-form-label">Resident/Operator/Manager <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <select class="form-control" id="business_resident_id" name="business_resident_id">
                            <option value="">-- Search and Select Resident/Operator --</option>
                        </select>
                        <small class="form-text text-muted">Search and select the resident who is the operator/manager of this business.</small>
                        <!-- Hidden field to store the selected resident name for operator_manager -->
                        <input type="hidden" id="operator_manager" name="operator_manager">
                        <!-- Hidden field to store the business resident ID for form submission -->
                        <input type="hidden" id="business_resident_id_hidden" name="business_resident_id_hidden">
                        <!-- Main resident_id field for business certificates -->
                        <input type="hidden" id="business_main_resident_id" name="resident_id">
                    </div>
                </div>
            </div>

            <!-- Purpose Textarea (Required) - Hidden for Business Clearance -->
            <div class="mb-3 row" id="purpose-field">
                <label for="purpose" class="col-sm-3 col-form-label">Purpose <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="purpose" name="purpose" rows="3" required></textarea>
                    <small class="form-text text-muted">Default purpose is set. You can modify it if needed.</small>
                </div>
            </div>

            <!-- Date of Issue Input (Required) -->
            <div class="mb-3 row">
                <label for="issue_date" class="col-sm-3 col-form-label">Date of Issue <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <input type="date" class="form-control" id="issue_date" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="row">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle me-2"></i>Issue Certificate & Proceed to Print
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
// Wait for jQuery and Bootstrap to be fully loaded from footer
document.addEventListener('DOMContentLoaded', function() {
    const certTypeDropdown = document.getElementById('certificate_type_id');
    const purposeTextarea = document.getElementById('purpose');
    const titleElement = document.getElementById('certificate-title');
    const signingOfficialSelect = document.getElementById('signing_official_id');
    const businessFields = document.getElementById('business-fields');
    const businessNameField = document.getElementById('business_name');
    const operatorManagerField = document.getElementById('operator_manager');
    const residentSelect = document.getElementById('resident_id');
    const regularResidentField = document.getElementById('regular-resident-field');
    const purposeField = document.getElementById('purpose-field');
    const businessResidentSelect = document.getElementById('business_resident_id');


    // Ensure only one input named "resident_id" is submitted
    function setResidentNameMode(mode) {
        const regular = document.getElementById('resident_id');
        const businessMain = document.getElementById('business_main_resident_id');
        try {
            if (mode === 'business') {
                // Disable regular field name, enable business hidden field as resident_id
                if (regular) regular.removeAttribute('name');
                if (businessMain) businessMain.setAttribute('name', 'resident_id');
                console.log('Name mode set to BUSINESS: regular[name]=removed, business_main_resident_id[name]=resident_id');
            } else {
                // Enable regular field as resident_id, disable business hidden field name
                if (regular) regular.setAttribute('name', 'resident_id');
                if (businessMain) businessMain.setAttribute('name', 'business_main_resident_id');
                console.log('Name mode set to REGULAR: regular[name]=resident_id, business_main_resident_id[name]=business_main_resident_id');
            }
        } catch (err) {
            console.error('Error toggling resident_id names:', err);
        }
    }

    // Wait for jQuery to be fully loaded
    function waitForJQuery(callback) {
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined') {
            callback();
        } else {
            setTimeout(() => waitForJQuery(callback), 100);
        }
    }

    certTypeDropdown.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        console.log('Certificate type changed:', selectedOption.value, selectedOption.text);

        if (selectedOption.value) {
            const purpose = selectedOption.getAttribute('data-purpose');
            const typeName = selectedOption.text;
            titleElement.textContent = 'Issue ' + typeName;

            console.log('Selected certificate type:', typeName);
            console.log('Is business certificate:', typeName.toLowerCase().includes('business'));

            // Show/hide fields based on certificate type
            if (typeName.toLowerCase().includes('business')) {
                console.log('Showing business fields');
                // Business Clearance: Show business fields, hide regular resident and purpose
                businessFields.style.display = 'block';
                regularResidentField.style.display = 'none';
                purposeField.style.display = 'none';
                // Ensure correct resident_id name mapping for business mode
                setResidentNameMode('business');

                // Set requirements
                businessNameField.required = true;
                businessResidentSelect.required = true;
                residentSelect.required = false;
                purposeTextarea.required = false;

                // Clear regular fields and Select2 selections
                residentSelect.value = '';
                if (typeof jQuery !== 'undefined' && $('#resident_id').hasClass('select2-hidden-accessible')) {
                    $('#resident_id').val(null).trigger('change');
                }
                purposeTextarea.value = 'Business permit application'; // Default purpose for business

                // Initialize business resident select2 if not already done
                waitForJQuery(() => {
                    if (!$('#business_resident_id').hasClass('select2-hidden-accessible')) {
                        $('#business_resident_id').select2({
                            placeholder: '-- Search and Select Resident/Operator --',
                            minimumInputLength: 2,
                            ajax: {
                                url: 'residents_search.php',
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        term: params.term
                                    };
                                },
                                processResults: function (data) {
                                    return data;
                                },
                                cache: true
                            },
                            width: '100%'
                        }).on('select2:select', function (e) {
                            // Auto-populate operator/manager hidden field and business resident ID
                            const selectedData = e.params.data;
                            if (selectedData && selectedData.text && selectedData.id) {
                                const selectedId = selectedData.id;
                                const selectedText = selectedData.text;

                                console.log('Business resident selection - ID:', selectedId, 'Text:', selectedText);

                                // Update operator/manager field
                                operatorManagerField.value = selectedText;

                                // Update both hidden fields
                                document.getElementById('business_resident_id_hidden').value = selectedId;
                                document.getElementById('business_main_resident_id').value = selectedId;

                                // Ensure the underlying select element is updated
                                $(this).empty().append(new Option(selectedText, selectedId, true, true));
                                this.value = selectedId;
                                $(this).val(selectedId).trigger('change');

                                // Verify all fields are set correctly
                                console.log('Business resident sync verification:');
                                console.log('- Hidden field:', document.getElementById('business_resident_id_hidden').value);
                                console.log('- Main resident field:', document.getElementById('business_main_resident_id').value);
                                console.log('- Select element value:', this.value);
                                console.log('- jQuery val():', $(this).val());
                                console.log('- Operator/Manager:', operatorManagerField.value);

                                console.log('✅ Business resident fields updated successfully');
                            } else {
                                console.error('Invalid selection data for business resident:', selectedData);
                            }
                        }).on('select2:unselect', function (e) {
                            console.log('Business resident unselected');
                            operatorManagerField.value = '';
                            document.getElementById('business_resident_id_hidden').value = '';
                            document.getElementById('business_main_resident_id').value = '';
                            $(this).empty().append(new Option('-- Search and Select Resident/Operator --', '', true, true));
                            this.value = '';
                            $(this).val('').trigger('change');
                        });
                    }
                });
            } else {
                console.log('Showing regular certificate fields');
                // Regular certificates: Show regular fields, hide business fields
                businessFields.style.display = 'none';
                regularResidentField.style.display = 'block';
                purposeField.style.display = 'block';
                // Ensure correct resident_id name mapping for regular mode
                setResidentNameMode('regular');

                // Set requirements
                businessNameField.required = false;
                businessResidentSelect.required = false;
                residentSelect.required = true;
                purposeTextarea.required = true;

                // Set purpose and clear business fields
                purposeTextarea.value = purpose || 'For general certification purposes';
                businessNameField.value = '';
                operatorManagerField.value = '';
                businessResidentSelect.value = '';
                // Clear the hidden business resident ID fields
                document.getElementById('business_resident_id_hidden').value = '';
                document.getElementById('business_main_resident_id').value = '';
                // Clear Select2 selection for business resident
                if (typeof jQuery !== 'undefined' && $('#business_resident_id').hasClass('select2-hidden-accessible')) {
                    $('#business_resident_id').val(null).trigger('change');
                }


            }
        } else {
            console.log('No certificate type selected, showing default fields');
            // No certificate type selected: Show regular fields, hide business fields
            purposeTextarea.value = '';
            titleElement.textContent = 'Issue New Certificate';
            businessFields.style.display = 'none';
            regularResidentField.style.display = 'block';
            purposeField.style.display = 'block';
            // Ensure correct resident_id name mapping for regular mode (default)
            setResidentNameMode('regular');

            // Reset requirements
            businessNameField.required = false;
            businessResidentSelect.required = false;
            residentSelect.required = true;
            purposeTextarea.required = true;
        }
    });

    // Initialize Select2 after jQuery is loaded
    waitForJQuery(function() {
        try {
            console.log('jQuery loaded, initializing Select2...');
            // Load Select2 dynamically if not already loaded
            if (typeof jQuery.fn.select2 === 'undefined') {
                console.log('Loading Select2 library...');
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
                script.onload = function() {
                    console.log('Select2 library loaded');
                    initializeSelect2();
                };
                document.head.appendChild(script);
            } else {
                console.log('Select2 library already available');
                initializeSelect2();
            }
        } catch (error) {
            console.error('Error loading Select2:', error);
        }
    });

    // Ensure form fields are properly set up on page load
    console.log('Page loaded, setting up initial form state...');
    // Show regular fields by default
    businessFields.style.display = 'none';
    regularResidentField.style.display = 'block';
    purposeField.style.display = 'block';
    // Ensure correct resident_id name mapping for initial page load (regular)
    setResidentNameMode('regular');

    function initializeSelect2() {
        try {
            console.log('Initializing Select2...');

            // Destroy existing Select2 instances if they exist
            if ($('#resident_id').hasClass('select2-hidden-accessible')) {
                $('#resident_id').select2('destroy');
            }

            // Initialize Select2 for resident search
            $('#resident_id').select2({
                placeholder: '-- Search Resident --',
                minimumInputLength: 2,
                ajax: {
                    url: 'residents_search.php',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            term: params.term // search term
                        };
                    },
                    processResults: function (data) {
                        console.log('Resident search results:', data);
                        return data;
                    },
                    cache: true
                },
                width: '100%'
            }).on('select2:select', function (e) {
                console.log('Resident selected:', e.params.data);
                console.log('Selected resident ID:', e.params.data.id);
                console.log('Selected resident text:', e.params.data.text);

                // Proper Select2 value synchronization
                const selectedId = e.params.data.id;
                const selectedText = e.params.data.text;

                console.log('Processing selection - ID:', selectedId, 'Text:', selectedText);

                try {
                    // Step 1: Ensure the option exists in the select element
                    const $select = $(this);
                    const existingOption = $select.find('option[value="' + selectedId + '"]');

                    if (existingOption.length === 0) {
                        // Add the option if it doesn't exist
                        $select.append(new Option(selectedText, selectedId, false, false));
                        console.log('Added new option to select element');
                    }

                    // Step 2: Set the value using jQuery (this is the most reliable method)
                    $select.val(selectedId);

                    // Step 3: Update the DOM element directly as backup
                    const domElement = document.getElementById('resident_id');
                    domElement.value = selectedId;

                    // Step 4: Verify the value was set correctly
                    const finalValue = domElement.value;
                    const jqueryValue = $select.val();

                    console.log('Value verification - DOM:', finalValue, 'jQuery:', jqueryValue);

                    if (finalValue !== selectedId || jqueryValue !== selectedId) {
                        console.error('Value sync failed! Expected:', selectedId);
                        console.error('DOM value:', finalValue, 'jQuery value:', jqueryValue);
                        // Force set both values
                        domElement.value = selectedId;
                        $select.val(selectedId);
                    } else {
                        console.log('✅ Value sync successful!');
                    }

                } catch (error) {
                    console.error('Error in Select2 select handler:', error);
                    // Fallback: direct DOM manipulation
                    document.getElementById('resident_id').value = selectedId;
                }

                // Auto-populate operator/manager field when resident is selected
                const selectedData = e.params.data;
                if (selectedData && selectedData.text) {
                    operatorManagerField.value = selectedData.text;
                }
            }).on('select2:unselect', function (e) {
                console.log('Resident unselected');

                // Clear both jQuery and DOM values
                $(this).val('');
                document.getElementById('resident_id').value = '';
                operatorManagerField.value = '';

                console.log('Cleared resident selection');
            });

            // Initialize Select2 for signing official selection
            if (!$('#signing_official_id').hasClass('select2-hidden-accessible')) {
                $('#signing_official_id').select2({
                    placeholder: '-- Select Signing Official --',
                    width: '100%'
                });
            }

            console.log('Select2 initialized successfully');
        } catch (error) {
            console.error('Error initializing Select2:', error);
            // Fallback to regular select if Select2 fails
            document.getElementById('resident_id').style.width = '100%';
            document.getElementById('signing_official_id').style.width = '100%';
        }
    }

    // Form validation before submit
    document.getElementById('issueCertificateForm').addEventListener('submit', function(e) {
        console.log('Form submission validation started');

        if (!signingOfficialSelect.value) {
            e.preventDefault();
            alert('Please select a signing official. This field is required.');
            signingOfficialSelect.focus();
            return false;
        }

        // Validate business fields if they are visible
        if (businessFields.style.display !== 'none') {
            console.log('Validating business certificate fields');
            if (!businessNameField.value.trim()) {
                e.preventDefault();
                alert('Please enter the business name or trade activity.');
                businessNameField.focus();
                return false;
            }
            // Check both the visible select and the hidden fields
            const businessResidentHidden = document.getElementById('business_resident_id_hidden');
            const businessMainResidentId = document.getElementById('business_main_resident_id');
            if (!businessResidentSelect.value && !businessResidentHidden.value && !businessMainResidentId.value) {
                e.preventDefault();
                alert('Please select a resident/operator/manager for this business. Current values: visible=' + businessResidentSelect.value + ', hidden=' + businessResidentHidden.value + ', main=' + businessMainResidentId.value);
                businessResidentSelect.focus();
                return false;
            }
            // Ensure the hidden field is populated if the visible field has a value
            if (businessResidentSelect.value && !businessResidentHidden.value) {
                businessResidentHidden.value = businessResidentSelect.value;
                console.log('Auto-synced business resident ID to hidden field:', businessResidentHidden.value);
            }
            if (!operatorManagerField.value.trim()) {
                e.preventDefault();
                alert('Please select a resident to auto-populate the operator/manager field.');
                businessResidentSelect.focus();
                return false;
            }
        } else {
            // Validate regular fields for non-business certificates
            console.log('Validating regular certificate fields');
            console.log('Regular resident field visible:', regularResidentField.style.display !== 'none');
            console.log('Resident select value:', residentSelect.value);
            console.log('Purpose textarea value:', purposeTextarea.value.trim());

            if (!residentSelect.value) {
                console.error('Form validation failed: No resident selected');
                console.error('Resident select element:', residentSelect);
                console.error('Resident select value:', residentSelect.value);

                // Check if Select2 is initialized and has a value
                let select2Value = '';
                let select2Text = '';
                if (typeof jQuery !== 'undefined' && $('#resident_id').hasClass('select2-hidden-accessible')) {
                    select2Value = $('#resident_id').val();
                    const select2Data = $('#resident_id').select2('data');
                    if (select2Data && select2Data.length > 0) {
                        select2Text = select2Data[0].text || '';
                    }
                    console.error('Select2 value:', select2Value);
                    console.error('Select2 data:', select2Data);

                    if (select2Value && select2Value !== '') {
                        // Select2 has a value but the regular select doesn't - sync them
                        console.log('Attempting to sync Select2 value to regular select:', select2Value);

                        // Add the option if it doesn't exist
                        if (!$('#resident_id').find('option[value="' + select2Value + '"]').length) {
                            $('#resident_id').append(new Option(select2Text, select2Value, true, true));
                        } else {
                            $('#resident_id').val(select2Value);
                        }

                        // Update the DOM element directly
                        residentSelect.value = select2Value;
                        console.log('Successfully synced. New value:', residentSelect.value);

                        // Continue with form submission
                        return true;
                    }
                }

                e.preventDefault();
                alert('Please select a resident from the dropdown. Make sure to click on a resident from the search results.\n\nCurrent form value: "' + residentSelect.value + '"\nSelect2 value: "' + select2Value + '"');

                // Focus on the Select2 element
                if (typeof jQuery !== 'undefined' && $('#resident_id').hasClass('select2-hidden-accessible')) {
                    $('#resident_id').select2('open');
                } else {
                    residentSelect.focus();
                }
                return false;
            }
            if (!purposeTextarea.value.trim()) {
                e.preventDefault();
                alert('Please enter the purpose for this certificate.');
                purposeTextarea.focus();
                return false;
            }
        }

        console.log('Form validation passed, submitting...');

        // CRITICAL: Ensure Select2 values are synced before submission
        // CRITICAL FIX: Ensure resident_id is properly set before form submission
        console.log('=== PRE-SUBMISSION RESIDENT ID SYNC ===');

        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            // For regular certificates, ensure the select element has the correct value
            if (regularResidentField.style.display !== 'none') {
                console.log('Processing regular certificate resident sync...');
                const regularSelect = $('#resident_id');
                const regularSelectValue = regularSelect.val();
                const select2Data = regularSelect.select2('data');

                console.log('Regular select value:', regularSelectValue);
                console.log('Regular select2 data:', select2Data);

                let selectedId = null;

                // Try to get the selected ID from multiple sources
                if (select2Data && select2Data.length > 0 && select2Data[0].id) {
                    selectedId = select2Data[0].id;
                    console.log('Got ID from select2 data:', selectedId);
                } else if (regularSelectValue) {
                    selectedId = regularSelectValue;
                    console.log('Got ID from select value:', selectedId);
                }

                if (selectedId) {
                    // Ensure the underlying select has the correct value and option
                    if (!regularSelect.find('option[value="' + selectedId + '"]').length) {
                        const optionText = select2Data && select2Data[0] ? select2Data[0].text : 'Selected Resident';
                        regularSelect.append(new Option(optionText, selectedId, true, true));
                        console.log('Added missing option for regular select');
                    }

                    // Set the value in multiple ways to ensure it sticks
                    regularSelect.val(selectedId);
                    residentSelect.value = selectedId;

                    console.log('Final regular resident sync - DOM value:', residentSelect.value, 'jQuery value:', regularSelect.val());
                } else {
                    console.error('No resident ID found for regular certificate!');
                }
            }

            // For business certificates, ensure ALL resident ID fields are properly set
            if (businessFields.style.display !== 'none') {
                console.log('Processing business certificate resident sync...');
                const businessSelect = $('#business_resident_id');
                const hiddenField = document.getElementById('business_resident_id_hidden');
                const mainResidentField = document.getElementById('business_main_resident_id');
                const businessSelectValue = businessSelect.val();
                const select2Data = businessSelect.select2('data');

                console.log('Business select value:', businessSelectValue);
                console.log('Business select2 data:', select2Data);
                console.log('Hidden field value:', hiddenField.value);
                console.log('Main resident field value:', mainResidentField.value);

                let selectedId = null;

                // Try to get the selected ID from multiple sources
                if (select2Data && select2Data.length > 0 && select2Data[0].id) {
                    selectedId = select2Data[0].id;
                    console.log('Got business ID from select2 data:', selectedId);
                } else if (businessSelectValue) {
                    selectedId = businessSelectValue;
                    console.log('Got business ID from select value:', selectedId);
                } else if (hiddenField.value) {
                    selectedId = hiddenField.value;
                    console.log('Got business ID from hidden field:', selectedId);
                } else if (mainResidentField.value) {
                    selectedId = mainResidentField.value;
                    console.log('Got business ID from main resident field:', selectedId);
                }

                if (selectedId) {
                    // Set ALL the business resident fields to ensure one of them works
                    hiddenField.value = selectedId;
                    mainResidentField.value = selectedId;
                    businessSelect.val(selectedId);

                    // Ensure the underlying select has the correct option
                    if (!businessSelect.find('option[value="' + selectedId + '"]').length) {
                        const optionText = select2Data && select2Data[0] ? select2Data[0].text : 'Selected Resident';
                        businessSelect.append(new Option(optionText, selectedId, true, true));
                        console.log('Added missing option for business select');
                    }

                    console.log('Final business resident sync - Hidden:', hiddenField.value, 'Main:', mainResidentField.value, 'Select:', businessSelect.val());
                } else {
                    console.error('No resident ID found for business certificate!');
                }
            }
        }

        console.log('=== END PRE-SUBMISSION SYNC ===');

        // FINAL VERIFICATION: Check that we have a resident_id value before submission
        const formData = new FormData(this);
        let hasResidentId = false;
        let residentIdValue = '';

        // Check all possible resident_id fields
        for (let [key, value] of formData.entries()) {
            if (key === 'resident_id' && value && value.trim() !== '') {
                hasResidentId = true;
                residentIdValue = value;
                break;
            }
        }

        // If no resident_id found, prevent submission and show error
        if (!hasResidentId) {
            e.preventDefault();
            console.error('CRITICAL ERROR: No resident_id found in form data!');

            // Show detailed error message
            let errorMsg = 'Error: No resident selected. Please select a resident before submitting.\n\n';
            errorMsg += 'Debug info:\n';

            if (regularResidentField.style.display !== 'none') {
                const regularValue = document.getElementById('resident_id').value;
                const select2Value = typeof jQuery !== 'undefined' ? $('#resident_id').val() : 'N/A';
                errorMsg += `Regular resident field - DOM: "${regularValue}", Select2: "${select2Value}"\n`;
            }

            if (businessFields.style.display !== 'none') {
                const hiddenValue = document.getElementById('business_resident_id_hidden').value;
                const mainValue = document.getElementById('business_main_resident_id').value;
                const selectValue = typeof jQuery !== 'undefined' ? $('#business_resident_id').val() : 'N/A';
                errorMsg += `Business fields - Hidden: "${hiddenValue}", Main: "${mainValue}", Select: "${selectValue}"\n`;
            }

            alert(errorMsg);
            return false;
        }

        console.log('✅ Resident ID verification passed:', residentIdValue);

        // Log all form data before submission
        console.log('=== FORM DATA BEING SUBMITTED ===');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': "' + value + '"');
        }
        console.log('=== END FORM DATA ===');
    });
});
</script>
<?php
require_once 'includes/footer.php';
?>