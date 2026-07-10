<?php
// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['success']) . "</label></p>
            </div>
        </div>
    </div>";
}

if (isset($flash['error'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['error']) . "</label></p>
            </div>
        </div>
    </div>";
}
?>

<div class="center reset-password-logo"><?php require(__DIR__ . "/../../public/images/site-images/logo.php"); ?></div>

<form id="login-form" style="max-width: 500px;" method="POST" action="">
    <p><a href="/login">Back to Login</a></p>
    
    <h2 class="center">Forgot Password?</h2>
    <p class="large-input center">Enter your email or username below. If your account is found, you will receive an email with a password reset link.</p>
    
    <?php echo $error_msg ?? ''; ?>
    
    <div class="large-input center" style="max-width: 350px; margin: auto;">
        <label for="identifier">Email or Username: </label><br>
        <input required type="text" value="<?php echo htmlspecialchars($identifier ?? ''); ?>" name="identifier" id="identifier" placeholder="Enter email or username">
    </div>
    
    <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Send Email"></p>
</form>

<script src="/public/js/form-validation.js?v=2.9"></script>
<script>
$(document).ready(function() {
    FormValidator.init('#login-form', {
        identifier: {
            required: true
        }
    });
});
</script>

