<div class="modal-overlay" id="login-modal" style="display:none;">
    <form class="auth-card" method="POST" autocomplete="off">
        <h2>Welcome back!</h2>
        <?php if (isset($login_error) && $login_error): ?>
            <div style="color:#ff6b6b; margin-bottom:10px; font-weight:bold;"> <?= htmlspecialchars($login_error) ?> </div>
        <?php endif; ?>
        <?php if (isset($login_success) && $login_success): ?>
            <div style="color:#4afc8d; margin-bottom:10px; font-weight:bold;"> <?= htmlspecialchars($login_success) ?> </div>
        <?php endif; ?>
        <div style="text-align:left; margin-bottom: 8px;">
            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
            <input type="email" id="email" name="email" placeholder="name@email.com" required>
        </div>
        <div style="text-align:left; margin-bottom: 8px;">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <input type="password" id="password" name="password" placeholder="Password" required>
        </div>
        <input type="hidden" name="login" value="1">
        <button type="submit" style="margin-top: 10px;">Login</button>
        <div class="switch-link">
            Don't have an account?
            <a href="#" id="register-link">Register</a>
        </div>
    </form>
</div>