<?php
// --- Barangay Clearance Certificate Template ---
// This PHP file serves as the template for generating a Barangay Clearance certificate.
// It uses HTML for structure, CSS for styling, and PHP to dynamically insert data.
// The necessary data (e.g., $certificate_data, $barangay_name) is expected to be made available
// by the script that includes this template (e.g., view_certificate.php).
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Clearance - <?php echo html_escape($certificate_data['control_number']); // Dynamically set title with control number ?></title>
    <style>
        /* --- General Body and Page Styling --- */
        body {
            font-family: 'Times New Roman', Times, serif; /* Traditional font for certificates */
            margin: 0.5in; /* Standard margin for printing */
            font-size: 12pt; /* Standard readable font size */
        }
        .certificate-container {
            border: 2px solid black; /* Border for the certificate */
            padding: 0.5in; /* Inner padding */
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
            width: 600px; 
            height: auto;
            opacity: 0.1; 
            z-index: -1; 
            pointer-events: none; 
        }

        /* --- Header Section Styling (Barangay Info) --- */
        .header {
            text-align: center;
            margin-bottom: 0.2in;
        }
        .header .logo-left {
            width: 90px; /* Adjust logo size as needed */
            height: auto;
            position: absolute;
            top: 0.6in;
            left: 0.6in;
        }
        .header .logo-right {
            width: 90px; /* Adjust logo size as needed */
            height: auto;
            position: absolute;
            top: 0.6in;
            right: 0.6in;
        }
        .header-text p {
            margin: 0;
            font-size: 12pt;
        }
        
        .header-text h1 { /* Region */
            font-size: 14pt;
            margin: 5px 0;
            font-weight: bold;
        }

        .office-name {
            font-size: 16pt;
            font-weight: bold;
            margin-top: 20px;
            padding-bottom: 5px;
            border-bottom: 2px solid black;
            border-top: 2px solid black;
            display: inline-block;
        }

        /* --- Certificate Title Styling --- */
        .title {
            text-align: center;
            font-size: 28pt;
            font-weight: bold;
            margin-top: 0.3in;
            margin-bottom: 0.3in;
            text-transform: uppercase;
            font-family: 'Arial Black', Gadget, sans-serif;
        }

        /* --- Main Body Content Styling --- */
        .body-content {
            text-align: justify; /* Justified text for a formal look */
            line-height: 1.6; /* Tighter line height for compact layout */
            margin-bottom: 0.2in;
        }
        .body-content p {
            margin: 0.15in 0; /* Reduced spacing between paragraphs */
        }
        .body-content .highlight {
            font-weight: bold; /* For emphasizing key information like names, dates */
            text-decoration: underline;
        }
        .underline {
            text-decoration: underline;
        }
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
            float: right; /* Position the CTC info to the right */
            margin-top: 0.2in;
            font-size: 10pt;
        }
        .footer-info p {
            margin: 3px 0;
        }

        /* --- Print-Specific Styles --- */
        @media print {
            body {
                margin: 0; /* Remove body margin for printing, @page margin will be used */
                font-size: 12pt; /* Ensure consistent font size */
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
            .signature-container {
                margin-top: 0.5in !important; 
            }
            .signature-name {
                margin-top: 20px !important;
            }
            .signature-block.certified {
                 margin-top: 20px !important; /* Match the screen style */
            }
            .body-content p {
                margin: 0.1in 0 !important;
            }
            .footer-info {
                margin-top: 0.1in !important;
            }
            [contenteditable="true"] {
                outline: none !important;
                border: none !important;
            }
            .office-name {
                 border-top: 2px solid black !important;
                 border-bottom: 2px solid black !important;
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

        .seal {
            position: absolute;
            bottom: 2.5in; /* Position from bottom */
            left: 50%;
            transform: translateX(-50%);
            width: 120px;  /* Adjust as needed */
            height: 120px;
            /* border: 1px solid #ccc; */ /* Placeholder border for seal area */
            text-align: center;
            line-height: 100px; /* Vertically center text */
            font-style: italic;
            color: #aaa;
            opacity: 0.2;
            z-index: -1;
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
            BARANGAY CLEARANCE
        </div>

        <!-- Main Content of the Certificate -->
        <div class="body-content">
            <p contenteditable="true"><strong>TO WHOM IT MAY CONCERN:</strong></p>
            
            <p contenteditable="true" style="text-indent: 0.5in;">This is to certify that
                <span class="highlight">
                    <?php echo strtoupper(html_escape($certificate_data['resident_name'] ?? '___________________________')); ?>
                </span>of legal age,
                <span class="highlight">
                    <?php echo strtolower(html_escape($certificate_data['gender'] ?? 'male/female')); ?>
                </span>,
                <span class="highlight">
                    <?php echo strtolower(html_escape($certificate_data['resident_civil_status'] ?? 'single/married/widow')); ?>
                </span> a resident of Barangay Central Glad, Midsayap, Cotabato is a law-abiding citizen.
            </p>

            <p contenteditable="true" style="text-indent: 0.5in;">Records of this office show that he/she has no pending case filed against his/her until the issuance of this certification.</p>

            <p contenteditable="true" style="text-indent: 0.5in;">This Barangay Clearance is being issued upon his/her request for 
            <span class="highlight"><?php echo html_escape($certificate_data['purpose'] ?? '________________________'); ?></span> and whatever legal purpose it will serve her/his best.</p>
            
            <p contenteditable="true" style="text-indent: 0.5in;">Issued this <span class="highlight"><?php echo date('j', strtotime($certificate_data['issue_date'] ?? 'now')); ?></span> day of 
            <span class="highlight"><?php echo date('F', strtotime($certificate_data['issue_date'] ?? 'now')); ?></span>,
            <span class="highlight"><?php echo date('Y', strtotime($certificate_data['issue_date'] ?? 'now')); ?></span>
            at Barangay Central Glad, Midsayap, Cotabato, Philippines.</p>
        </div>
        
        <!-- Footer Information: CTC -->
        <div class="footer-info" contenteditable="true">
            <?php if(!empty($certificate_data['ctc_no'])): ?>
                <p>Comm. Tax Cert. No.: <?php echo html_escape($certificate_data['ctc_no']); ?></p>
            <?php else: ?>
                <p>Comm. Tax Cert. No.: ____________</p>
            <?php endif; ?>
            
            <?php if(!empty($certificate_data['ctc_issued_at'])): ?>
                <p>Issued at: <?php echo html_escape($certificate_data['ctc_issued_at']); ?></p>
            <?php else: ?>
                <p>Issued at: MTO - MIDSAYAP</p>
            <?php endif; ?>

            <?php if(!empty($certificate_data['ctc_issued_on'])): ?>
                <p>Issued on: <?php echo html_escape(date('m/d/Y', strtotime($certificate_data['ctc_issued_on']))); ?></p>
            <?php else: ?>
                <p>Issued on: ____________</p>
            <?php endif; ?>
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