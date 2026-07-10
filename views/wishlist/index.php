<?php
/**
 * Variables available in this view:
 * @var string $title
 * @var array $wishlists
 * @var array $all_wishlists
 * @var int $total_pages
 * @var bool $active
 * @var array $flash
 * @var array $user
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

<h1 class="center"><?= htmlspecialchars($title); ?></h1>
<p class="center" style="margin: 0 0 36px;"><a class="button primary" href="/wishlists/create">Create a New Wish List</a></p>

<div class="wishlist-tabs">
    <a class="wishlist-tab<?php if($active) echo ' active'; ?>" href="/wishlists">Active</a>
    <a class="wishlist-tab<?php if(!$active) echo ' active'; ?>" href="/wishlists/inactive">Inactive</a>
</div>

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
    <!-- Top Pagination controls -->
    <?php 
    $position = 'top';
    $backgroundColorClass = 'no-background';
    include __DIR__ . '/../components/pagination-controls.php'; 
    ?>
<?php endif; ?>

<div class="wishlist-grid">
    <?php
    if(count($wishlists) > 0){
        // Use WishlistRenderService to generate the HTML
        echo \App\Services\WishlistRenderService::generateWishlistsHtml($wishlists);
    }else{
        echo "<p style='grid-column: 1 / -1;' class='center'>It doesn't look like you have any " .  ($active ? "active" : "inactive") . " wish lists right now</p>";
    }
    ?>
</div>

<?php include __DIR__ . '/../components/bulk-action-bar.php'; ?>

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
    <!-- Bottom Pagination controls -->
    <?php 
    $position = 'bottom';
    $total_count = count($all_wishlists);
    $backgroundColorClass = 'no-background';
    include __DIR__ . '/../components/pagination-controls.php'; 
    ?>
<?php endif; ?>

<?php require __DIR__ . '/../components/wishlist-action-popups.php'; ?>

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
<script src="/public/js/pagination.js?v=2.9"></script>
<?php endif; ?>

<script src="/public/js/form-validation.js?v=2.9"></script>
<script src="/public/js/add-alert-message.js?v=2.9"></script>
<script src="/public/js/reload-wishlists.js?v=2.9"></script>
<script src="/public/js/wishlist-action-menu.js?v=2.9"></script>
<script src="/public/js/wishlist-bulk-select.js?v=2.9"></script>

<script>
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    $(document).ready(function(){
        // check for cookie alertMessage and display it if exists, then delete the cookie
        const alertMessage = getCookie('alertMessage');
        if (alertMessage) {
            addAlertMessage(alertMessage);
            // Delete the cookie
            document.cookie = "alertMessage=; path=/wishlists; max-age=0";
        }
    });
</script>