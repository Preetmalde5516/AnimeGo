<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$message_sent = false;
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $message_sent = true;
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

        .success-message {
            background-color: #0f0f0fff;
            color: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
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
                        <div class="success-message">
                            <h3>Thank you for your message! We'll get back to you soon.</h3>
                        </div>
                    <?php else: ?>
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