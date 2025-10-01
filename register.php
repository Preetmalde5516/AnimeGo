<div class="register-modal" id="register-modal" style="display:none;">
        <form class="register-card" method="POST" autocomplete="off">
            <h2>Create Account</h2>
            <?php if (isset($reg_error) && $reg_error): ?>
                <div style="color:#ff6b6b; margin-bottom:10px; font-weight:bold;"> <?= htmlspecialchars($reg_error) ?> </div>
            <?php endif; ?>
            <?php if (isset($reg_success) && $reg_success): ?>
                <div style="color:#4afc8d; margin-bottom:10px; font-weight:bold;"> <?= $reg_success ?> </div>
            <?php endif; ?>
            <div style="text-align:left; margin-bottom: 8px;">
                <label for="reg-username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="reg-username" name="username" placeholder="Username" required>
            </div>
            <div style="text-align:left; margin-bottom: 8px;">
                <label for="reg-email"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" id="reg-email" name="email" placeholder="name@email.com" required>
            </div>
            <div style="text-align:left; margin-bottom: 8px;">
                <label for="reg-password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="reg-password" name="password" placeholder="Password" required>
            </div>
            <div style="text-align:left; margin-bottom: 8px;">
                <label for="reg-confirm"><i class="fas fa-lock"></i> Confirm Password</label>
                <input type="password" id="reg-confirm" name="confirm" placeholder="Confirm Password" required>
            </div>
            <input type="hidden" name="register" value="1">
            <button type="submit">Register</button>
            <div class="login-link">
                Already have an account?
                <a href="#" id="login-link2">Login</a>
            </div>
        </form>
    </div>