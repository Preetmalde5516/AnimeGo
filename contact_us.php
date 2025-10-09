<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php'; // Include your database connection

$message_sent = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Basic validation
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $subject, $message);

            if ($stmt->execute()) {
                $message_sent = true;
            } else {
                $error_message = "Sorry, there was an error sending your message. Please try again later.";
            }
            $stmt->close();
        } else {
            $error_message = "Invalid email format.";
        }
    } else {
        $error_message = "Please fill out all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - AnimeGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <?php include "header.php"; ?>

    <main>
        <section class="contact-section">
            <div class="container">
                <div class="contact-wrapper">
                    <div class="contact-info">
                        <h2>Get in Touch</h2>
                        <p>Have a question, suggestion, or a request? We'd love to hear from you. Fill out the form, and we'll get back to you as soon as possible.</p>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Anime World, Internet</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <span>support@animego.com</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <span>+123-456-7890</span>
                        </div>
                    </div>

                    <div class="contact-form">
                        <?php if($message_sent): ?>
                            <div class="message-box success-message">
                                <h3>Thank you for your message!</h3>
                                <p>We'll get back to you soon.</p>
                            </div>
                        <?php else: ?>
                            <?php if(!empty($error_message)): ?>
                                <div class="message-box error-message">
                                    <p><?php echo htmlspecialchars($error_message); ?></p>
                                </div>
                            <?php endif; ?>
                            <form id="contactForm" method="POST" action="contact_us.php">
                                <div class="form-group">
                                    <label for="name">Your Name</label>
                                    <input type="text" id="name" name="name" required placeholder="Enter your name">
                                </div>
                                <div class="form-group">
                                    <label for="email">Your Email</label>
                                    <input type="email" id="email" name="email" required placeholder="name@example.com">
                                </div>
                                <div class="form-group">
                                    <label for="subject">Subject</label>
                                    <input type="text" id="subject" name="subject" required placeholder="What is this about?">
                                </div>
                                <div class="form-group">
                                    <label for="message">Message</label>
                                    <textarea id="message" name="message" required placeholder="Write your message here..."></textarea>
                                </div>
                                <button type="submit" class="submit-btn">Send Message</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include "footer.php"; ?>

</body>
</html>