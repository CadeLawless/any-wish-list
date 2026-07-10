<?php
/**
 * Variables available in this view:
 * @var array $user
 * @var string $username
 * @var bool $remember_me
 * @var string $name
 * @var string $email
 * @var string $current_password
 * @var string $new_password
 * @var string $confirm_password
 * @var string $name_error_msg
 * @var string $email_error_msg
 * @var string $password_error_msg
 * @var string $password_confirmation
 */

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
<form id="login-form" method="POST" action="">
    <p><a href="/login">Back to Login</a></p>
    <?php if(isset($error_msg)) echo $error_msg?>
    <div class="large-input center">
        <label for="name">First Name: </label><br>
        <input required type="text" name="name" placeholder="Dwight" value="<?php echo $name?>" id="name" maxlength="50">
    </div>
    <div class="large-input center">
        <label for="username">Username: </label><br>
        <input required type="text" name="username" placeholder="belsnickel123" value="<?php echo $username?>" id="username">
    </div>
    <div class="large-input center">
        <label for="email">Email: </label><br>
        <input required type="email" name="email" placeholder="dwightkschrute@dundermifflin.com" value="<?php echo $email?>" id="email">
    </div>
    <div class="large-input">
        <div style="margin: 0 0 22px;">
            Password Requirements:
            <ul>
                <li>Must be at least 8 characters long</li>
                <li>Must contain at least one uppercase letter</li>
                <li>Must contain at least one lowercase letter</li>
                <li>Must contain at least one number</li>
            </ul>
        </div>
    </div>
    <div class="large-input center">
        <label for="password">Password: </label><br>
        <div class="password-input">
            <input required type="password" name="password" id="password" value="<?php echo $password?>" pattern="^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(.+){8,}$">
            <span class="password-view hide-password hidden"><?php require(__DIR__ . "/../../public/images/site-images/icons/hide-view.php"); ?></span>
            <span class="password-view view-password"><?php require(__DIR__ . "/../../public/images/site-images/icons/view.php"); ?></span>
        </div>
    </div>
    <div class="large-input center">
        <label for="password_confirmation">Confirm Password: </label><br>
        <input required type="password" name="password_confirmation" value="<?php echo $password_confirmation?>" id="password_confirmation">
    </div>
    <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Start Wishing"></p>
</form>

<script src="/public/js/form-validation.js?v=2.9"></script>
<script>
$(document).ready(function(){
    // Initialize form validation
    FormValidator.init('#login-form', {
        name: {
            required: true,
            minLength: 2,
            maxLength: 50
        },
        username: {
            required: true,
            minLength: 3,
            maxLength: 50,
            checkUnique: '/api/check-username',
            charactersNotAllowed: [' ', '/', '\\', '?', '@', '%', '#', '&', '*', '<', '>', '"', '\'']
        },
        email: {
            required: true,
            email: true,
            checkUnique: '/api/check-email'
        },
        password: {
            required: true,
            password: true
        },
        password_confirmation: {
            required: true,
            confirmPassword: '#password'
        }
    });

    $(".view-password").on("click", function(){
        $("#password, #password_confirmation").attr("type", "text");
        $(this).addClass("hidden");
        $(".hide-password").removeClass("hidden");
    });
    $(".hide-password").on("click", function(){
        $("#password, #password_confirmation").attr("type", "password");
        $(this).addClass("hidden");
        $(".view-password").removeClass("hidden");
    });
});
</script>
