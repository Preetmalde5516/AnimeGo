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
    <style>
        .contact-section {
            padding: 60px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 140px); /* Adjust based on header/footer height */
        }

        .contact-container {
            width: 100%;
            max-width: 800px;
            background-color: #2a2a2a;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        }

        .contact-container h2 {
            text-align: center;
            color: #ff6b6b;
            font-size: 2.5rem;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #cccccc;
            font-size: 1rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            background-color: #1a1a1a;
            border: 1px solid #333;
            color: #fff;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b6b;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #ff6b6b;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #e55a5a;
        }

        .message-box {
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .success-message {
            background-color: #4CAF50;
            color: white;
        }
        .error-message {
            background-color: #e55a5a;
            color: white;
        }
    </style>
</head>
<body>

    <?php include "header.php"; ?>

    <main>
        <section class="contact-section">
            <div class="container">
                <div class="contact-container">
                    <h2>Contact Us</h2>

                    <?php if($message_sent): ?>
                        <div class="message-box success-message">
                            <h3>Thank you for your message! We'll get back to you soon.</h3>
                        </div>
                    <?php else: ?>
                        <?php if(!empty($error_message)): ?>
                            <div class="message-box error-message">
                                <p><?php echo htmlspecialchars($error_message); ?></p>
                            </div>
                        <?php endif; ?>
                        <form id="contactForm" method="POST" action="contact_us.php">
                            <div class="form-group">
                                <label for="name"><i class="fas fa-user"></i> Your Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Your Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="subject"><i class="fas fa-file-alt"></i> Subject</label>
                                <input type="text" id="subject" name="subject" required>
                            </div>
                            <div class="form-group">
                                <label for="message"><i class="fas fa-comment-dots"></i> Message</label>
                                <textarea id="message" name="message" required></textarea>
                            </div>
                            <button type="submit" class="submit-btn">Send Message</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include "footer.php"; ?>

</body>
</html>