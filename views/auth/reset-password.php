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

<form id="reset-password-form" method="POST" action="">
    <input type="hidden" name="key" value="<?php echo htmlspecialchars($key ?? ''); ?>" />
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" />
    
    <h2 class="center">Reset Your Password</h2>
    
    <?php echo $error_msg ?? ''; ?>
    
    <div class="flex form-flex">
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
        
        <div class="large-input">
            <label for="password">New Password: </label><br>
            <div class="password-input">
                <input required type="password" name="password" id="password" value="<?php echo htmlspecialchars($password ?? ''); ?>" pattern="^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(.+){8,}$">
                <span class="password-view hide-password hidden"><?php require(__DIR__ . '/../../public/images/site-images/icons/hide-view.php'); ?></span>
                <span class="password-view view-password"><?php require(__DIR__ . '/../../public/images/site-images/icons/view.php'); ?></span>
            </div>
        </div>
        
        <div class="large-input">
            <label for="password_confirmation">Confirm New Password: </label><br>
            <input required type="password" name="password_confirmation" value="<?php echo htmlspecialchars($password_confirmation ?? ''); ?>" id="password_confirmation">
        </div>
        
        <p class="large-input center"><input type="submit" class="button text" id="password_submit_button" name="password_submit_button" value="Change Password"></p>
    </div>
</form>

<script src="/public/js/form-validation.js?v=2.9"></script>
<script>
$(document).ready(function() {
    FormValidator.init('#reset-password-form', {
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

