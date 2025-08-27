<?php
// about.php
session_start();
$page_title = "About the System";
require_once 'includes/header.php';

// Role-based access (optional, only allow logged in users)
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
?>

<div class="container mt-4">
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-primary text-white text-center">
            <h2>About Barangay Management System</h2>
        </div>
        <div class="card-body p-4">
            <p>
                The <strong>Barangay Management System (BMS)</strong> is a web-based platform 
                designed to streamline barangay operations and improve public service delivery. 
                This system provides an efficient way to manage residents’ records, households, 
                puroks, and generate official documents.
            </p>

            <h4 class="mt-4 text-primary">Key Features:</h4>
            <ul>
                <li><strong>Resident Management:</strong> Store, update, and search resident information easily.</li>
                <li><strong>Household & Purok Management:</strong> Organize families and residents by purok or household.</li>
                <li><strong>Document Generation:</strong> Create barangay certificates, clearances, and reports in just a few clicks.</li>
                <li><strong>Role-based Access:</strong> Different accounts for Barangay Secretary, Captain, and staff.</li>
                <li><strong>Analytics & Reports:</strong> Generate data-driven insights for better decision-making.</li>
            </ul>

            <h4 class="mt-4 text-success">Message Blast Module:</h4>
            <p>
                The <strong>Message Blast</strong> feature allows the barangay to send bulk SMS 
                or notifications to residents. This ensures quick and reliable communication, 
                especially during emergencies, announcements, and community events.
            </p>
            <ul>
                <li><strong>Emergency Alerts:</strong> Notify all residents about disasters, typhoons, or urgent safety measures.</li>
                <li><strong>Community Announcements:</strong> Send reminders about barangay activities and programs.</li>
                <li><strong>Targeted Messaging:</strong> Send updates to specific groups like senior citizens, youth, or puroks.</li>
            </ul>

            <div class="alert alert-info mt-4">
                <strong>Note:</strong> The Message Blast requires SMS API integration 
                (such as Semaphore, Twilio, or Globe Labs) to send real-time text messages.
            </div>
        </div>
        <div class="card-footer text-center">
            <small>&copy; <?php echo date("Y"); ?> Barangay Management System | All Rights Reserved</small>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
