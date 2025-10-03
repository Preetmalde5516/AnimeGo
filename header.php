<?php
// --- SESSION AND DATABASE INITIALIZATION ---
// Always start the session at the very beginning of the script.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection script once to avoid multiple connections.
include_once 'db_connect.php';


// --- FORM STATE AND ERROR HANDLING INITIALIZATION ---
// These variables will be used to display messages and control modal visibility.
$login_error = '';
$reg_error = '';
$reg_success = '';
$show_login_modal_on_load = false;
$show_register_modal_on_load = false;


// --- GLOBAL LOGIN FORM PROCESSING ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // Prepare statement to prevent SQL injection and fetch admin status.
        $stmt = $conn->prepare("SELECT id, username, email, password, is_admin FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify the submitted password against the stored hash.
            if (password_verify($password, $user['password'])) {
                // Password is correct, store user data in the session.
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['username'],
                    'is_admin' => (bool)$user['is_admin']
                ];
                // Redirect to the same page to prevent form resubmission on refresh.
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            } else {
                $login_error = "Invalid email or password.";
            }
        } else {
            $login_error = "No account found with that email.";
        }
        $stmt->close();
    } else {
        $login_error = "Please fill in all fields.";
    }
    // If there was a login error, set flag to re-open the modal.
    if (!empty($login_error)) {
        $show_login_modal_on_load = true;
    }
}


// --- GLOBAL REGISTRATION FORM PROCESSING ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $reg_error = "Passwords do not match.";
    } else {
        // Check if the username or email is already taken.
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $reg_error = "Username or email is already taken.";
        } else {
            // Hash the password for secure storage.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                $reg_success = "Registration successful! You can now log in.";
            } else {
                $reg_error = "An error occurred. Please try again.";
            }
        }
        $stmt->close();
    }
    // If there was a registration error or success, set flag to re-open the modal.
    if (!empty($reg_error) || !empty($reg_success)) {
        $show_register_modal_on_load = true;
    }
}


// --- DYNAMIC NAVIGATION HELPER ---
// Get the name of the current PHP script to set the active navigation link.
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header>
    <div class="container">
        <div class="logo">
            <h1>AnimeGo</h1>
        </div>
        <nav>
            <ul>
                <!-- Dynamically set the 'active' class based on the current page -->
                <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Home</a></li>
                <li><a href="series.php" class="<?= ($current_page == 'series.php') ? 'active' : '' ?>">Series</a></li>
                <li><a href="movies.php" class="<?= ($current_page == 'movies.php') ? 'active' : '' ?>">Movies</a></li>
                <li><a href="contact_us.php" class="<?= ($current_page == 'contact_us.php') ? 'active' : '' ?>">Contact US</a></li>
                <li>
    <?php if (isset($_SESSION['user_id'])): ?><a href="watchlist.php" class="<?= ($current_page == 'watchlist.php') ? 'active' : '' ?>">watchlist</a>    <?php endif; ?></li>
        <?php if (isset($_SESSION['user'])): ?>
            <li><a href="watchlist.php" class="<?= ($current_page == 'watchlist.php') ? 'active' : '' ?>">Watchlist</a></li>
        <?php endif; ?>
            </ul>
        </nav>
        <div class="search-box">
            <input type="text" placeholder="Search anime...">
            <button><i class="fas fa-search"></i></button>
        </div>
        <div class="profile">
            <?php if (isset($_SESSION['user'])): ?>
                <?php if (!empty($_SESSION['user']['is_admin'])): ?>
                    <!-- Displayed only for admin users -->
                    <a href="admin.php" style="background: #4afc8d; color: #1a1a1a; border-radius: 4px; padding: 6px 16px; font-size: 1rem; text-decoration:none; font-weight:bold;">Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" style="margin-left: 10px; background: #ff6b6b; color: #fff; border-radius: 4px; padding: 6px 16px; font-size: 1rem; text-decoration:none;">Logout</a>
            <?php else: ?>
                <!-- Displayed for guests (not logged in) -->
                <a href="#" class="login-trigger" style="background: #ff6b6b; color: #fff; border-radius: 4px; padding: 6px 16px; font-size: 1rem; text-decoration:none; display:inline-block;">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- The Login and Register modals are only included in the HTML if the user is not logged in. -->
<?php if (!isset($_SESSION['user'])): ?>
    <?php include "login.php" ?>
    <?php include "register.php" ?>
<?php endif; ?>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- MODAL INTERACTIVITY SCRIPT ---

    // Find modal elements in the DOM.
    const loginTrigger = document.querySelector('.login-trigger');
    const loginModal = document.getElementById('login-modal');
    const registerModal = document.getElementById('register-modal');

    // This script only runs if the modals exist on the page (i.e., user is a guest).
    if (loginTrigger && loginModal && registerModal) {
        const registerLinkFromLogin = document.getElementById('register-link');
        const loginLinkFromRegister = document.getElementById('login-link2');

        // --- Event Listeners for Opening and Switching Modals ---
        loginTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.style.display = 'flex';
        });

        registerLinkFromLogin.addEventListener('click', (e) => {
            e.preventDefault();
            loginModal.style.display = 'none';
            registerModal.style.display = 'flex';
        });

        loginLinkFromRegister.addEventListener('click', (e) => {
            e.preventDefault();
            registerModal.style.display = 'none';
            loginModal.style.display = 'flex';
        });

        // Add event listener to close modals when clicking on the dark background overlay.
        [loginModal, registerModal].forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // --- Logic to auto-open modals on page load if a form submission had an error ---
        <?php if ($show_login_modal_on_load): ?>
            loginModal.style.display = 'flex';
        <?php endif; ?>

        <?php if ($show_register_modal_on_load): ?>
            registerModal.style.display = 'flex';
        <?php endif; ?>
    }
});
</script>
