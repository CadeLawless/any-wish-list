<?php
/**
 * Variables available in this view:
 * @var array $flash
 * @var string $error_msg
 * @var string $wishlist_type
 * @var int $theme_background_id
 * @var int $theme_gift_wrap_id
 * @var string $wishlist_name
 * @var array $user
 */

use App\Helpers\ThemePopupHelper;
?>
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
<div>
    <div class="form-container">
        <h1>Create Wish List</h1>
        <form method="POST" action="/wishlists">
            <?php echo $error_msg ?? ""; ?>
            <div class="flex form-flex">
                <div class="large-input">
                    <label for="wishlist_type">Type:<br/></label>
                    <select required name="wishlist_type" id="wishlist_type">
                        <option value="" disabled <?php if(($wishlist_type ?? '') == "") echo "selected"; ?>>Select an option</option>
                        <option value="Birthday" <?php if(($wishlist_type ?? '') == "Birthday") echo "selected"; ?>>Birthday</option>
                        <option value="Christmas" <?php if(($wishlist_type ?? '') == "Christmas") echo "selected"; ?>>Christmas</option>
                    </select>
                </div>
                <div class="large-input">
                    <label for="theme">Theme:</label><br />
                    <a style="margin-bottom: 10px;" class="choose-theme-button button primary popup-button<?php if(($wishlist_type ?? '') == "") echo " disabled"; ?>" href="#">Choose a theme...<span class="inline-popup<?php if(($wishlist_type ?? '') != "") echo " hidden"; ?>">Please select a type</span></a>
                    <?php
                    // Include theme popup functionality using MVC helper
                    echo ThemePopupHelper::renderThemePopup("birthday");
                    echo ThemePopupHelper::renderThemePopup("christmas");
                    ?>
                    <input type="hidden" id="theme_background_id" name="theme_background_id" value="<?php echo $theme_background_id ?? ''; ?>" />
                    <input type="hidden" id="theme_gift_wrap_id" name="theme_gift_wrap_id" value="<?php echo $theme_gift_wrap_id ?? ''; ?>" />
                    <div class="theme-results">
                        <div class="theme-background-display desktop-background-display"></div>
                        <div class="theme-background-display mobile-background-display"></div>
                        <div class="theme-gift-wrap-display"></div>
                    </div>
                </div>
                <div class="large-input">
                    <label for="wishlist_name">Name:<br/></label>
                    <input required type="text" id="wishlist_name" autocapitalize="words" name="wishlist_name" value="<?php echo htmlspecialchars($wishlist_name ?? ''); ?>" />
                </div>
                <div class="large-input">
                    <p class="center"><input type="submit" class="button text" name="submit_button" id="submitButton" value="Create" /></p>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .form-container {
        margin: 0 auto 30px;
        background-color: var(--background-darker);
        max-width: 500px;
    }
</style>

<script src="/public/js/form-validation.js?v=2.9"></script>
<script src="/public/js/choose-theme.js?v=2.9"></script>
<script>
$(document).ready(function() {
    FormValidator.init('form', {
        wishlist_type: {
            required: true
        },
        theme_background_id: {
            required: true,
            requiredMsg: 'Please choose a theme'
        },
        wishlist_name: {
            required: true,
            minLength: 1,
            maxLength: 100
        }
    });
    
    let name_input = document.querySelector("#wishlist_name");
    name_input.addEventListener("focus", function(){
        this.select();
    });
    
    let type_select = document.querySelector("#wishlist_type");
    type_select.addEventListener("change", function(){
        let current_year = new Date().getFullYear();
        if(this.value == "Birthday"){
            name_input.value = "<?php echo $user['name']; ?>'s " + current_year + " Birthday Wish List";
            $(".popup-container.birthday").insertBefore(".popup-container.christmas");
        }else if(this.value == "Christmas"){
            name_input.value = "<?php echo $user['name']; ?>'s " + current_year + " Christmas Wish List";
            $(".popup-container.christmas").insertBefore(".popup-container.birthday");
        }
        if(this.value != ""){
            document.querySelector(".choose-theme-button").classList.remove("disabled");
        }else{
            document.querySelector(".choose-theme-button").classList.add("disabled");
        }
    });
});
</script>