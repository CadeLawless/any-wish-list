<?php
/**
 * Variables available in this view:
 * @var bool $account_created
 */

if($account_created){
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . "/../../public/images/site-images/menu-close.php");
                echo "</a>
            </div>
            <div class='popup-content'>
                <p>Welcome to Wish List! Please check your email to complete your account registration.</p>
            </div>
        </div>
    </div>";
}
?>
<div class="big-buttons-container">
    <a class="big-button create-wish-list" href="/wishlists/create"><?php require(__DIR__ . "/../../public/images/site-images/icons/plus.php"); ?>Create Wish List</a>
    <a class="big-button view-wish-list" href="/wishlists"><?php require(__DIR__ . "/../../public/images/site-images/icons/search.php"); ?>View Wish Lists</a>
</div>

<script src="js/popup.js?v=2.9"></script>
