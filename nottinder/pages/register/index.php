<?php
require_once '../../includes/functions/handy_methods.php';
require_once '../../includes/functions/security.php';
include '../../templates/header.php';
?>

<body>
    <?php include '../../templates/nav.php'; ?>

    <main>
        <section class="login-section">
            <div class="login-container">
                <article class="welcome-message">
                    <h1>Join NotTinder!</h1>
                </article>

                <div class="form-wrapper">
                    <article class="register-form">
                        <h2>Register</h2>
                        <form id="registerForm" method="POST">
                            <div class="form-group">
                                <label for="reg-username">Username:</label>
                                <input type="text" id="reg-username" name="username" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" required>
                            </div>

                            <div class="form-group">
                                <label for="full_name">Full name:</label>
                                <input type="text" id="full_name" name="full_name" required>
                            </div>

                            <div class="form-group">
                                <label for="city">City:</label>
                                <input type="text" id="city" name="city" required>
                            </div>

                            <div class="form-group">
                                <label for="age">Age:</label>
                                <input type="number" id="age" name="age" min="18" max="120" required>
                            </div>

                            <div class="form-group">
                                <label for="bio">About you:</label>
                                <textarea id="bio" name="bio" rows="4" required
                                    placeholder="What are your hobbies, interests, etc..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="annual_salary">Annual salary:</label>
                                <select id="annual_salary" name="annual_salary" required>
                                    <option value="">Select closest option...</option>
                                    <option value="30000">Under 30 000€</option>
                                    <option value="50000">30 000 - 50 000€</option>
                                    <option value="75000">50 000 - 100 000€</option>
                                    <option value="100000">Over 100 000€</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="preference">Looking for:</label>
                                <select id="preference" name="preference" required>
                                    <option value="men">Men</option>
                                    <option value="women">Women</option>
                                    <option value="any">Any</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="reg-password">Password (At least 8 characters):</label>
                                <input type="password" id="reg-password" name="password" required minlength="8">
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm Password:</label>
                                <input type="password" id="confirm_password" name="confirm_password" required
                                    minlength="8">
                            </div>

                            <input type="hidden" name="action" value="register">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <button type="submit" class="submit-btn">Register</button>
                        </form>
                        <div id="registerMessage" class="message" style="display:none;"></div>
                        <p class="form-switch">
                            Already got an account? <a href="<?php echo url('pages/login/'); ?>">Log in</a>
                        </p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <?php include '../../templates/footer.php'; ?>
    <script>
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            let messageDiv = document.getElementById('registerMessage');

            // validate passwords match
            let password = document.getElementById('reg-password').value;
            let confirm = document.getElementById('confirm_password').value;

            if (password !== confirm) {
                messageDiv.style.display = 'block';
                messageDiv.className = 'message error';
                messageDiv.textContent = 'Lösenorden matchar inte!';
                return;
            }

            fetch(BASE_URL + '/includes/handlers/auth_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    messageDiv.style.display = 'block';
                    if (data.success) {
                        messageDiv.className = 'message success';
                        messageDiv.textContent = data.message;
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        }
                    } else {
                        messageDiv.className = 'message error';
                        messageDiv.textContent = data.message;
                    }
                })
                .catch(error => {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'message error';
                    messageDiv.textContent = 'An error occurred. Please try again.';
                });
        });
    </script>
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</body>

</html>