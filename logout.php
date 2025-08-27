<?php
session_start();

// Get user info before destroying session for personalized message
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Set flag to show logout success modal
$show_logout_modal = true;
$logout_message = "Goodbye, " . htmlspecialchars($username) . "! You have been successfully logged out.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Barangay Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: url('images/logonew.jfif') no-repeat right center;
            background-size: cover;
            background-attachment: fixed;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 90%;
            backdrop-filter: blur(10px);
            display: none; /* Hide by default when modal is showing */
        }

        .logout-container.show-fallback {
            display: block; /* Show only if modal fails to load */
        }

        .logout-container .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 1rem;
            border: 3px solid #007bff;
        }

        .logout-container h2 {
            color: #333;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .logout-message {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .btn-login {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
            color: white;
        }

        /* Modal Styles - copied from login.php */
        .alert-icon-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .alert-icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }

        .alert-icon-circle i {
            font-size: 1.8rem;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .alert-message-container {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <img src="images/logonew.jfif" alt="Barangay Logo" class="logo">
        <h2>SentroGlad</h2>
        <div class="logout-message">
            <i class="fas fa-sign-out-alt text-primary me-2"></i>
            Logging you out...
        </div>
        <a href="login.php" class="btn-login">
            <i class="fas fa-sign-in-alt me-2"></i>
            Back to Login
        </a>
    </div>

    <!-- Logout Success Modal Popup -->
    <?php if ($show_logout_modal): ?>
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <!-- Modal Header with Gradient -->
                <div class="modal-header text-center border-0" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); padding: 2rem 1.5rem 1rem;">
                    <div class="w-100">
                        <!-- Alert Icon with Animation -->
                        <div class="alert-icon-container mb-3">
                            <div class="alert-icon-circle">
                                <i class="fas fa-sign-out-alt text-white"></i>
                            </div>
                        </div>
                        <h4 class="modal-title text-white fw-bold mb-2" id="logoutModalLabel">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout Successful!
                        </h4>
                        <p class="text-white-50 mb-0" style="font-size: 0.9rem;">
                            Thank you for using the system
                        </p>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body text-center" style="padding: 2rem 1.5rem;">
                    <div class="alert-message-container">
                        <div class="mb-3">
                            <i class="fas fa-user-check text-warning" style="font-size: 2rem; opacity: 0.8;"></i>
                        </div>
                        <h5 class="text-dark mb-0"><?php echo htmlspecialchars($logout_message); ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show logout modal if there's a message to display
            <?php if ($show_logout_modal): ?>
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();

            // Auto-dismiss after 3 seconds and redirect to login
            setTimeout(() => {
                logoutModal.hide();
                // Redirect to login page after hiding the modal
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 300);
            }, 3000);
            <?php else: ?>
            // If no modal to show, display the fallback container
            document.querySelector('.logout-container').classList.add('show-fallback');
            <?php endif; ?>
        });
    </script>
</body>
</html>