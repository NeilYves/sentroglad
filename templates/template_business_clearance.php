<?php
// --- Business Clearance Certificate Template ---
// This PHP file serves as the template for generating a Business Clearance certificate.
// It uses HTML for structure, CSS for styling, and PHP to dynamically insert data.
// The necessary data (e.g., $certificate_data, $barangay_name) is expected to be made available
// by the script that includes this template (e.g., view_certificate.php).
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Clearance - <?php echo html_escape($certificate_data['control_number'] ?? 'N/A'); ?></title>
    <style>
        /* --- General Body and Page Styling --- */
        body {
            font-family: 'Times New Roman', Times, serif; /* Traditional font for certificates */
            margin: 0.5in; /* Standard margin for printing */
            font-size: 11pt; /* Good readable size */
        }
        .certificate-container {
            border: 2px solid black; /* Border for the certificate */
            padding: 0.5in; /* Standard inner padding */
            width: 7.5in; /* Standard letter paper width (8.5in) minus 0.5in margins on each side */
            height: 10in; /* Standard letter paper height (11in) minus 0.5in margins on each side */
            margin: auto; /* Center the certificate on the page (for screen view) */
            position: relative; /* For positioning elements like logo and seal */
            box-sizing: border-box;
        }

        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 550px; 
            height: auto;
            opacity: 0.1; 
            z-index: -1; 
            pointer-events: none; 
        }

        /* --- Header Section Styling (Barangay Info) --- */
        .header {
            text-align: center;
            margin-bottom: 0.15in;
        }
        .header .logo-left {
            width: 85px; 
            height: auto;
            position: absolute;
            top: 0.6in;
            left: 0.6in;
        }
        .header .logo-right {
            width: 85px; 
            height: auto;
            position: absolute;
            top: 0.6in;
            right: 0.6in;
        }
        .header-text p {
            margin: 0;
            font-size: 11pt;
        }
        
        .header-text h1 { /* Region */
            font-size: 13pt;
            margin: 3px 0;
            font-weight: bold;
        }

        .office-name {
            font-size: 15pt;
            font-weight: bold;
            margin-top: 15px;
            padding: 4px 0;
            border-bottom: 2px solid black;
            border-top: 2px solid black;
            display: inline-block;
        }

        /* --- Certificate Title Styling --- */
        .title {
            text-align: center;
            font-size: 22pt;
            font-weight: bold;
            margin-top: 0.15in;
            margin-bottom: 0.15in;
            text-transform: uppercase;
            font-family: 'Arial Black', Gadget, sans-serif;
            letter-spacing: 1px;
        }

        /* --- Date Section --- */
        .date-section {
            text-align: right;
            margin-bottom: 0.15in;
            font-size: 11pt;
        }

        /* --- Main Body Content Styling --- */
        .body-content {
            text-align: justify; /* Justified text for a formal look */
            line-height: 1.4; 
            margin-bottom: 0.1in;
            font-size: 10pt;
        }
        .body-content p {
            margin: 0.08in 0;
        }
        .body-content .highlight {
            font-weight: bold; /* For emphasizing key information like names, dates */
            text-decoration: underline;
        }
        .underline {
            text-decoration: underline;
        }

        .business-info {
            text-align: center;
            margin: 0.12in 0;
        }

        .business-info .field {
            margin: 0.08in 0;
            font-size: 10pt;
        }

        .business-info .blank-line {
            border-bottom: 1px solid black;
            display: inline-block;
            min-width: 230px;
            padding: 2px 4px;
        }

        .business-info .label {
            font-style: normal; /* Changed from italic to normal/straight */
            font-size: 9pt;
            margin: 2px 0;
        }

        .compliance-section {
            margin: 0.12in 0;
            line-height: 1.5; /* Better spacing like the image */
            font-size: 9pt;
            text-align: justify;
        }

        .compliance-item {
            margin: 0.1in 0; /* Clean spacing between items */
            text-align: justify;
            display: block;
            text-indent: 0;
            padding-left: 0;
            width: 100%; /* Full width for proper justification */
        }

        .compliance-item .compliance-text {
            display: inline-block;
            text-align: justify;
            width: 450px; /* Fixed width for consistent justification */
            vertical-align: top;
            hyphens: auto; /* Enable hyphenation for better justification */
            word-spacing: normal;
            text-justify: inter-word; /* Better justification method */
            line-height: 1.5;
        }

        .compliance-line {
            display: inline-block;
            width: 60px; /* Line width like in image */
            height: 1px;
            border-bottom: 1px solid black;
            margin-right: 8px; /* Small gap after line */
            vertical-align: baseline; /* Changed to baseline to lower the line */
            margin-bottom: -2px; /* Move line down slightly */
        }

        .compliance-item:nth-child(3) .compliance-text {
            display: inline;
            text-align: left; /* Remove justification for the long text */
            width: auto; /* Remove fixed width */
            vertical-align: top;
            line-height: 1.5;
        }

        /* --- Standardized Signature Section --- */
        .signature-container {
            display: flex;
            justify-content: space-around; /* Move blocks away from edges */
            margin-top: 0.8in;
            clear: both; /* Ensures this container clears the floated footer */
        }
        .signature-block {
            width: 45%;
            text-align: left;
        }
        .signature-block.certified {
            text-align: left;
            margin-top: 20px;
        }
        .signature-block.certified .signature-name,
        .signature-block.certified .signature-title {
            text-align: center; /* Center the name and title only */
        }
        .signature-name {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 60px;
        }
        .signature-title {

        }

        /* --- Footer Information Styling (Control No., OR No., etc.) --- */
        .footer-info {
            width: 45%;
            float: left; /* Move to left side */
            margin-top: 0.15in;
            font-size: 9pt;
            line-height: 1.3;
        }
        .footer-info p {
            margin: 2px 0;
        }

        /* --- Print-Specific Styles --- */
        @media print {
            body {
                margin: 0; /* Remove body margin for printing, @page margin will be used */
                font-size: 10pt; /* Ensure consistent font size */
                -webkit-print-color-adjust: exact !important; /* Ensure colors and backgrounds print */
                color-adjust: exact !important;
            }
            .certificate-container {
                border: none !important; /* Remove border for printing */
                width: 100%;  /* Use full page width within @page margins */
                height: 10in; /* Set a fixed height for printing to help with one-page layout */
                box-shadow: none !important; /* Remove any shadows */
                margin: 0 !important;
                padding: 0.5in !important;
                box-sizing: border-box !important;
            }
            .print-button-container {
                display: none; /* Hide the print button when printing */
            }
            .watermark-logo {
                opacity: 0.1 !important; /* Ensure watermark is visible */
            }
            [contenteditable="true"] {
                outline: none !important;
                border: none !important;
            }
            .office-name {
                 border-top: 2px solid black !important;
                 border-bottom: 2px solid black !important;
            }
            .signature-container {
                margin-top: 0.5in !important;
            }
            .signature-name {
                margin-top: 20px !important;
            }
            .signature-block.certified {
                 margin-top: 20px !important; /* Match the screen style */
            }
            [contenteditable="true"] {
                outline: none !important;
                border: none !important;
            }
            .body-content p {
                margin: 0.05in 0 !important;
            }
            .footer-info {
                margin-top: 0.1in !important;
            }
            .compliance-section {
                line-height: 1.5 !important;
                margin: 0.12in 0 !important;
            }
            .compliance-item {
                margin: 0.1in 0 !important;
                text-align: justify !important;
                width: 100% !important;
            }
            .compliance-item .compliance-text {
                text-align: justify !important;
                width: 450px !important;
                hyphens: auto !important;
                text-justify: inter-word !important;
                line-height: 1.5 !important;
            }
            .compliance-item:nth-child(3) .compliance-text {
                display: inline !important;
                text-align: left !important;
                width: auto !important;
                line-height: 1.5 !important;
            }
            .compliance-line {
                width: 60px !important;
                border-bottom: 1px solid black !important;
                margin-right: 8px !important;
                vertical-align: baseline !important;
                margin-bottom: -2px !important;
            }
            .business-info .blank-line {
                border-bottom: 1px solid black !important;
                min-width: 230px !important;
                padding: 2px 4px !important;
                display: inline-block !important;
            }
            .business-info .field {
                margin: 0.08in 0 !important;
            }
            .business-info .label {
                font-style: normal !important;
                margin: 2px 0 !important;
            }
            * {
                color: black !important;
                background: transparent !important;
            }
            @page {
                margin: 0.5in; /* Define page margins for printing */
                size: letter;  /* Define paper size */
            }
        }

        /* --- Print Button Styling --- */
        .print-button-container {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .print-button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .print-button:hover {
            background-color: #0056b3;
        }

        [contenteditable="true"]:hover {
            outline: 1px dashed #007bff;
        }
        [contenteditable="true"]:focus {
            outline: 1px solid #007bff;
        }
    </style>
</head>
<body>

    <!-- Print Button: This button will be hidden when the page is printed due to @media print styles -->
    <div class="print-button-container">
        <button class="print-button" onclick="window.print();">Print Certificate</button>
    </div>

    <!-- Main Certificate Container -->
    <div class="certificate-container">
        <!-- Watermark/Background Logo -->
        <?php if (!empty($certificate_data['system_settings']['barangay_logo_path']) && file_exists($certificate_data['system_settings']['barangay_logo_path'])): ?>
            <img src="<?php echo html_escape($certificate_data['system_settings']['barangay_logo_path']); ?>" alt="Barangay Watermark" class="watermark-logo">
        <?php endif; ?>

        <!-- Header -->
        <div class="header">
            <?php if (!empty($certificate_data['system_settings']['municipality_logo_path']) && file_exists($certificate_data['system_settings']['municipality_logo_path'])): ?>
                <img src="<?php echo html_escape($certificate_data['system_settings']['municipality_logo_path']); ?>" alt="Midsayap Logo" class="logo-left">
            <?php endif; ?>
            <div class="header-text">
                <p>Republic of the Philippines</p>
                <h1>REGION XII</h1>
                <p>Province of <?php echo html_escape($certificate_data['system_settings']['barangay_address_line2'] ?? 'Cotabato'); ?></p>
                <p>Municipality of Midsayap</p>
                <p><strong><?php echo html_escape($certificate_data['system_settings']['barangay_name'] ?? 'Barangay Central Glad'); ?></strong></p>
            </div>
            <?php if (!empty($certificate_data['system_settings']['barangay_logo_path']) && file_exists($certificate_data['system_settings']['barangay_logo_path'])): ?>
                <img src="<?php echo html_escape($certificate_data['system_settings']['barangay_logo_path']); ?>" alt="Barangay Logo" class="logo-right">
            <?php endif; ?>
            
            <div class="office-name">
                OFFICE OF THE BARANGAY CAPTAIN
            </div>
        </div>

        <!-- Certificate Title -->
        <div class="title">
            BUSINESS CLEARANCE
        </div>

        <!-- Date Section -->
        <div class="date-section">
            <span contenteditable="true"><?php echo date('F d, Y', strtotime($certificate_data['issue_date'] ?? 'now')); ?></span>
        </div>

        <!-- Main Content of the Certificate -->
        <div class="body-content">
            <p contenteditable="true"><strong>TO WHOM IT MAY CONCERN:</strong></p>
            
            <p contenteditable="true" style="text-indent: 0.5in;">THIS IS TO CERTIFY that the business or trade activity described below;</p>

            <!-- Business Information Section -->
            <div class="business-info">
                <div class="field">
                    <span contenteditable="true" class="blank-line">
                        <?php
                        // Debug: Show what business_name data is available
                        $business_name = $certificate_data['business_name'] ?? '';
                        if (empty($business_name)) {
                            echo '[Business Name Not Set]';
                        } else {
                            echo html_escape($business_name);
                        }
                        ?>
                    </span>
                </div>
                <div class="label">
                    (Business Name or Trade Activity)
                </div>

                <div class="field" style="margin-top: 0.1in;">
                    <span contenteditable="true" style="text-decoration: underline;">Central Glad, Midsayap, Cotabato</span>
                </div>
                <div class="label">
                    (Location)
                </div>

                <div class="field" style="margin-top: 0.1in;">
                    <span contenteditable="true" class="blank-line">
                        <?php
                        // Debug: Show what operator_manager data is available
                        $operator_manager = $certificate_data['operator_manager'] ?? '';
                        if (empty($operator_manager)) {
                            echo '[Operator/Manager Not Set]';
                        } else {
                            echo html_escape($operator_manager);
                        }
                        ?>
                    </span>
                </div>
                <div class="label">
                    (Operator/ Manager)
                </div>
            </div>

            <p contenteditable="true" style="text-indent: 0.5in;">Being applied for with the corresponding Mayor's Permit, has been found to be;</p>

            <!-- Compliance Section -->
            <div class="compliance-section">
                <div class="compliance-item">
                    <span class="compliance-line"></span>
                    <span class="compliance-text" contenteditable="true">Complying with the provision of existing Barangay Ordinances, Rules and Regulations being enrolled in this Barangay.</span>
                </div>

                <div class="compliance-item">
                    <span class="compliance-line"></span>
                    <span class="compliance-text" contenteditable="true">Partially Complying with the provision of existing Barangay Ordinances, Rules and Regulations being enforced in this Barangay;</span>
                </div>

                <div class="compliance-item">
                    <span class="compliance-line"></span>
                    <span class="compliance-text" contenteditable="true">Interpose, No Objection for the issuance of the Temporary Mayor's Permit for not more than three (3) years existing Barangay Ordinances, Rules and Regulations on that matter should be totally complied with otherwise this Barangay would take the necessary actions, with legal bounds, to stop its continued operations.</span>
                </div>
            </div>
        </div>
        
        <!-- Footer Information: CTC -->
        <div class="footer-info">
            <p contenteditable="true">Comm. Tax Cert. No. <span style="border-bottom: 1px solid black; display: inline-block; min-width: 120px; padding: 0 4px;"><?php echo html_escape($certificate_data['ctc_no'] ?? ''); ?></span></p>
            <p contenteditable="true">Issued at: <span contenteditable="true">Midsayap, Cotabato</span></p>
            <p contenteditable="true">Issued on: <span style="border-bottom: 1px solid black; display: inline-block; min-width: 90px; padding: 0 4px;" contenteditable="true"><?php echo html_escape($certificate_data['ctc_issued_on'] ?? ''); ?></span></p>
        </div>

        <!-- Signature Section -->
        <div class="signature-container">
            <div class="signature-block">
                <p contenteditable="true">Prepared by:</p>
                <p contenteditable="true" class="signature-name">
                    <?php echo strtoupper(html_escape($certificate_data['secretary_fullname'] ?? 'EDISON E. CAMACUNA')); ?>
                </p>
                <p contenteditable="true" class="signature-title">
                    <?php echo ucwords(html_escape($certificate_data['secretary_position'] ?? 'Barangay Secretary')); ?>
                </p>
            </div>
            <div class="signature-block certified">
                <p contenteditable="true">Certified by:</p>
                <p contenteditable="true" class="signature-name">
                    <?php echo strtoupper(html_escape($certificate_data['issuing_official_fullname'] ?? 'HON. VERNON E. PAPELERA')); ?>
                </p>
                <p contenteditable="true" class="signature-title">
                    <?php echo ucwords(html_escape($certificate_data['issuing_official_position'] ?? 'Punong Barangay')); ?>
                </p>
            </div>
        </div>

    </div> <!-- End of certificate-container -->

</body>
</html>