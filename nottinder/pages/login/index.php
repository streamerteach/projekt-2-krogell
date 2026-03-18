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
                    <h1>Welcome to NotTinder!</h1>
                    <?php
                    if (isset($_SESSION['message'])) {
                        echo '<div class="message success">' . htmlspecialchars($_SESSION['message']) . '</div>';
                        unset($_SESSION['message']);
                    }
                    if (isset($_SESSION['error'])) {
                        echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                        unset($_SESSION['error']);
                    }
                    ?>
                </article>

                <div class="form-wrapper">
                    <article class="login-form">
                        <h2>Log in</h2>
                        <form id="loginForm" method="POST">
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" name="username" id="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" name="password" id="password" required>
                            </div>
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <button type="submit" class="submit-btn">Log in</button>
                        </form>
                        <div id="loginMessage" class="message" style="display:none;"></div>
                        <p class="form-switch">
                            No account? <a href="<?php echo url('pages/register/'); ?>">Register</a>
                        </p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <?php include '../../templates/footer.php'; ?>
    <script>
        document.getElementById('loginForm')?.addEventListener('submit', function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            let messageDiv = document.getElementById('loginMessage');

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
                            }, 1000);
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
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</body>
</html>