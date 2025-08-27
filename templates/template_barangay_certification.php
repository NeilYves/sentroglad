<?php
// --- Barangay Certification Template ---
// This PHP file serves as the template for generating a Barangay Certification.
// It uses HTML for structure, CSS for styling, and PHP to dynamically insert data.
// The necessary data (e.g., $certificate_data, $barangay_name) is expected to be made available
// by the script that includes this template (e.g., view_certificate.php).
?>
<!DOCTYPE html>
<html lang="en">   
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Certification - <?php echo html_escape($certificate_data['control_number'] ?? ''); ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0.5in;
            font-size: 12pt;
        }
        .certificate-container {
            border: 2px solid black;
            padding: 0.5in;
            width: 7.5in;
            height: 10in;
            margin: auto;
            position: relative;
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
        .header {
            text-align: center;
            margin-bottom: 0.2in;
        }
        .header .logo-left {
            width: 90px;
            height: auto;
            position: absolute;
            top: 0.6in;
            left: 0.6in;
        }
        .header .logo-right {
            width: 90px;
            height: auto;
            position: absolute;
            top: 0.6in;
            right: 0.6in;
        }
        .header-text p {
            margin: 0;
            font-size: 12pt;
        }
        .header-text h1 {
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
        .title {
            text-align: center;
            font-size: 28pt;
            font-weight: bold;
            margin-top: 0.3in;
            margin-bottom: 0.3in;
            text-transform: uppercase;
            font-family: 'Arial Black', Gadget, sans-serif;
        }
        .body-content {
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 0.2in;
        }
        .body-content p {
            margin: 0.15in 0;
        }
        .body-content .highlight {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-container {
            display: flex;
            justify-content: space-around;
            margin-top: 1in;
            clear: both;
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
            text-align: center;
        }
        .signature-name {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 60px;
        }
        .signature-title {
            margin-top: -5px;
        }
        @media print {
            body {
                margin: 0;
                font-size: 12pt;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .certificate-container {
                border: none !important;
                width: 100%;
                height: 10in;
                margin: 0 !important;
                padding: 0.5in !important;
                box-sizing: border-box !important;
            }
            .print-button-container {
                display: none;
            }
            .watermark-logo {
                opacity: 0.1 !important;
            }
            .signature-container {
                margin-top: 0.5in !important;
            }
            .signature-name {
                margin-top: 20px !important;
            }
            .signature-block.certified {
                 margin-top: 20px !important;
            }
            * {
                color: black !important;
                background: transparent !important;
            }
            @page {
                margin: 0.5in;
                size: letter;
            }
        }
        .print-button-container {
            text-align: center;
            margin: 20px 0;
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
    </style>
</head>
<body>

    <div class="print-button-container">
        <button class="print-button" onclick="window.print();">Print Certificate</button>
    </div>

    <div class="certificate-container">
        <?php if (!empty($certificate_data['system_settings']['barangay_logo_path']) && file_exists($certificate_data['system_settings']['barangay_logo_path'])): ?>
            <img src="<?php echo html_escape($certificate_data['system_settings']['barangay_logo_path']); ?>" class="watermark-logo" alt="Watermark">
        <?php endif; ?>

        <div class="header">
            <?php if (!empty($certificate_data['system_settings']['municipality_logo_path']) && file_exists($certificate_data['system_settings']['municipality_logo_path'])): ?>
                <img src="<?php echo html_escape($certificate_data['system_settings']['municipality_logo_path']); ?>" class="logo-left" alt="Municipality Logo">
            <?php endif; ?>
            <div class="header-text">
                <p>Republic of the Philippines</p>
                <h1>REGION XII</h1>
                <p>Province of <?php echo html_escape($certificate_data['system_settings']['barangay_address_line2'] ?? 'Cotabato'); ?></p>
                <p>Municipality of Midsayap</p>
                <p><strong><?php echo html_escape($certificate_data['system_settings']['barangay_name'] ?? 'Barangay Central Glad'); ?></strong></p>
            </div>
            <?php if (!empty($certificate_data['system_settings']['barangay_logo_path']) && file_exists($certificate_data['system_settings']['barangay_logo_path'])): ?>
                <img src="<?php echo html_escape($certificate_data['system_settings']['barangay_logo_path']); ?>" class="logo-right" alt="Barangay Logo">
            <?php endif; ?>
            
            <div class="office-name">
                OFFICE OF THE BARANGAY CAPTAIN
            </div>
        </div>

        <div class="title">
            BARANGAY CERTIFICATION
        </div>

        <div class="body-content">
            <p><strong>TO WHOM IT MAY CONCERN:</strong></p>
            
            <p style="text-indent: 0.5in;">THIS IS TO CERTIFY that 
                <span class="highlight"><?php echo strtoupper(html_escape($certificate_data['resident_name'] ?? '___________________________')); ?></span>, 
                of legal age, 
                <span class="highlight"><?php echo strtolower(html_escape($certificate_data['gender'] ?? 'male/female')); ?></span>, 
                <span class="highlight"><?php echo strtolower(html_escape($certificate_data['resident_civil_status'] ?? 'single/married/widow')); ?></span>, 
                a resident of Barangay Central Glad, Midsayap, Cotabato.
            </p>

            <p style="text-indent: 0.5in;">This further certifies that he/she is known as a person of good moral character, a law-abiding citizen, and has never violated any law, ordinance, or rule duly implemented by the government authorities.</p>

            <p style="text-indent: 0.5in;">This Barangay Certification is issued upon request of the above-named person for 
                <span class="highlight"><?php echo html_escape($certificate_data['purpose'] ?? '________________________'); ?></span> 
                and whatever legal purposes it may serve him/her best.
            </p>

            <p style="text-indent: 0.5in;">Issued this 
                <span class="highlight"><?php echo date('j', strtotime($certificate_data['issue_date'] ?? 'now')); ?></span> day of 
                <span class="highlight"><?php echo date('F', strtotime($certificate_data['issue_date'] ?? 'now')); ?></span>, 
                <span class="highlight"><?php echo date('Y', strtotime($certificate_data['issue_date'] ?? 'now')); ?></span> 
                at the office of the Barangay Captain, Barangay Central Glad, Midsayap, Cotabato.
            </p>
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
