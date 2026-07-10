<?php
/**
 * Variables available in this view:
 * @var array $wishlists
 * @var array $public_user
 * @var array $user
 */
?>
<p>
    <a class="button accent" href="/friends<?= isset($_GET['search']) ? '?search=' . htmlspecialchars(urlencode($_GET['search'])) : ''; ?>">
        Back to Friends
    </a>
</p>

<?php
$friendList = [];
$receivedInvitations = [];
$newFriends = [$public_user];
$type = 'public';
require __DIR__ . '/../../views/components/friends-results.php';
?>

<h2 class="center"><?php echo htmlspecialchars($public_user['name']); ?>'s Wish Lists</h2>

<div class="wishlist-grid">
    <?php
    if(count($wishlists) > 0){
        // Use WishlistRenderService to generate the HTML
        echo \App\Services\WishlistRenderService::generateWishlistsHtml($wishlists, 'public', $_GET['search'] ?? '', $user);
    }else{
        echo "<p style='grid-column: 1 / -1;' class='center'>It doesn't look like " . htmlspecialchars($public_user['name']) . " has any wish lists created yet!</p>";
    }
    ?>
</div>

<?php include __DIR__ . '/../components/bulk-action-bar.php'; ?>

<?php require __DIR__ . '/../components/wishlist-action-popups.php'; ?>

<script src="/public/js/form-validation.js?v=2.9"></script>
<script src="/public/js/add-alert-message.js?v=2.9"></script>
<script src="/public/js/reload-wishlists.js?v=2.9"></script>
<script src="/public/js/wishlist-action-menu.js?v=2.9"></script>
<script src="/public/js/wishlist-bulk-select.js?v=2.9"></script>