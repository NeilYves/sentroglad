<?php
// --- Certificate of Residency Template ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Residency</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0.5in;
            font-size: 12pt; /* Standard document font size */
        }
        .certificate-container {
            border: 2px solid black;
            padding: 0.5in;
            width: 100%; /* Make it responsive */
            max-width: 8.5in; /* Set maximum width */
            min-height: 11in; /* Set minimum height */
            margin: auto;
            position: relative;
            box-sizing: border-box; /* Include padding in width calculation */
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
            text-align: justify;
            line-height: 1.6; /* Adjusted line spacing */
            margin-bottom: 0.3in;
        }
        .body-content p {
            margin: 0.15in 0; /* Adjusted paragraph spacing */
        }
        .salutation {
            font-weight: bold;
            margin-bottom: 0.2in;
        }

        /* --- Signature Section Styling --- */
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

        /* --- Watermark/Background Logo --- */
        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px; /* Increased size for larger watermark */
            height: auto;
            opacity: 0.1; /* Very transparent */
            z-index: -1; /* Behind all content */
            pointer-events: none; /* Don't interfere with text selection */
        }

        /* --- Print Styles --- */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .certificate-container {
                border: none !important;
                background: white !important;
                box-shadow: none !important;
                margin: 0;
                padding: 0.5in;
                width: 100%;
                max-width: none;
                min-height: auto;
            }
            
            .office-name {
                border-top: 2px solid black !important;
                border-bottom: 2px solid black !important;
                background: white !important;
            }
            
            /* Ensure watermark prints properly and is visible */
            .watermark-logo {
                opacity: 0.1 !important; /* Reduced visibility but still printable */
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                filter: grayscale(0%) !important; /* Ensure colors print */
                display: block !important; /* Force display */
                visibility: visible !important; /* Force visibility */
                z-index: 0 !important; /* Bring slightly forward but still behind text */
            }
            
            /* Remove any backgrounds but keep content clean */
            *, *::before, *::after {
                background: white !important;
                background-color: white !important;
                box-shadow: none !important;
                border: none !important; /* Remove all borders except specific ones */
            }
            
            /* Specifically target common elements that might have backgrounds */
            .header, .header-text, .title, .body-content, .signature-container, .signature-block,
            p, div, span, h1, h2, h3, h4, h5, h6 {
                background: white !important;
                background-color: white !important;
                border: none !important;
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
            .footer-info {
                margin-top: 0.1in !important;
            }
            .print-button-container {
                display: none; /* Hide the print button when printing */
            }
            [contenteditable="true"] {
                outline: none !important;
                border: none !important;
            }
            
            /* Keep only essential borders */
            .office-name {
                border-top: 2px solid black !important;
                border-bottom: 2px solid black !important;
            }
            
            /* Force all text to be black */
            * {
                color: black !important;
            }
            
            /* Remove page margins and hide page title */
            @page {
                margin: 0.5in;
                background: white;
                /* Hide browser page title/header */
                @top-left { content: ""; }
                @top-center { content: ""; }
                @top-right { content: ""; }
                @bottom-left { content: ""; }
                @bottom-center { content: ""; }
                @bottom-right { content: ""; }
            }
            
            /* Hide any browser-generated headers/footers */
            body::before,
            body::after {
                display: none !important;
            }
        }

    </style>
</head>
<body>

    <!-- Print Button: This button will be hidden when the page is printed due to @media print styles -->
    <div class="print-button-container">
        <button class="print-button" onclick="window.print();">Print Certificate</button>
    </div>

    <div class="certificate-container">
        <!-- Watermark/Background Logo -->
        <?php if (!empty($certificate_data['barangay_logo_path']) && file_exists($certificate_data['barangay_logo_path'])): ?>
            <img src="<?php echo html_escape($certificate_data['barangay_logo_path']); ?>" alt="Barangay Watermark" class="watermark-logo">
        <?php endif; ?>
        
        <div class="header">
            <?php if (!empty($certificate_data['municipality_logo_path']) && file_exists($certificate_data['municipality_logo_path'])): ?>
                <img src="<?php echo html_escape($certificate_data['municipality_logo_path']); ?>" alt="Midsayap Logo" class="logo-left">
            <?php endif; ?>
            <div class="header-text">
                <p>Republic of the Philippines</p>
                <h1>REGION XII</h1>
                <p>Province of Cotabato</p>
                <p>Municipality of Midsayap</p>
                <p><b>Barangay Central Glad</b></p>
            </div>
            <?php if (!empty($certificate_data['barangay_logo_path']) && file_exists($certificate_data['barangay_logo_path'])): ?>
                <img src="<?php echo html_escape($certificate_data['barangay_logo_path']); ?>" alt="Barangay Logo" class="logo-right">
            <?php endif; ?>
            
            <div class="office-name">
                OFFICE OF THE BARANGAY CAPTAIN
            </div>
        </div>

        <div class="title">
            CERTIFICATE OF RESIDENCY
        </div>

        <div class="body-content">
            <p class="salutation underline">TO WHOM IT MAY CONCERN:</p>
            
            <p style="text-indent: 0.5in;" class="underline">THIS IS TO CERTIFY that <span style="font-weight:bold;"><?php echo html_escape($certificate_data['resident_name']); ?></span> of legal age, <?php echo html_escape($certificate_data['gender']); ?>/<?php echo html_escape($certificate_data['resident_civil_status']); ?>, Filipino citizen, is a bonafide resident of Brgy. Central Glad, Midsayap, Cotabato.</p>

            <p style="text-indent: 0.5in;" class="underline">This further certifies based on the records of this office, he/she has been residing at Purok Calamansi Barangay Central Glad, Midsayap, Cotabato.</p>

            <p style="text-indent: 0.5in;" class="underline">This Certification is being issued upon his/her request of the above-named person for whatever legal purpose it may serve.</p>
            
            <p style="text-indent: 0.5in;" class="underline">Issued this <?php echo html_escape($certificate_data['day']); ?> day of <?php echo html_escape($certificate_data['month']); ?>, <?php echo html_escape($certificate_data['year']); ?>, at the office of the Barangay Captain, Barangay Central Glad, Midsayap, Cotabato.</p>
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
        
    </div>

</body>
</html>