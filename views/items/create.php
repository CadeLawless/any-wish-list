<?php
/**
 * Variables available in this view:
 * @var array $wishlist
 * @var array $user
 * @var string $item_name
 * @var string $price
 * @var string $quantity
 * @var string $unlimited
 * @var string $link
 * @var string $filename
 * @var string $notes
 * @var string $priority
 * @var string[] $priority_options
 * @var bool $fetch_error
 * @var string $fetch_error_message
 * @var bool $has_partial_data
 */

// Get wishlist data
$wishlistID = $wishlist['id'];
$wishlistTitle = htmlspecialchars($wishlist['wishlist_name']);
$background_image = $wishlist['background_image'] ?? '';

// Initialize form field variables
$item_name = $item_name ?? '';
$price = $price ?? '';
$quantity = $quantity ?? '1';
$unlimited = $unlimited ?? 'No';
$link = $link ?? '';
$filename = $filename ?? '';
$notes = $notes ?? '';
$priority = $priority ?? '1';
$priority_options = ["1", "2", "3", "4"];
?>
<?php if($background_image != ""){ ?>
    <img class='background-theme desktop-background' src="/public/images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
    <img class='background-theme mobile-background' src="/public/images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
<?php } ?>
<p><a class="button accent" href="/wishlists/<?php echo $wishlistID; ?>">Back to List</a></p>
<div class="center">
    <div class="wishlist-header center transparent-background">
        <h1><?php echo $wishlistTitle; ?></h1>
    </div>
</div>
<div class="form-container">
    <h2>Add Item</h2>
    <?php if(isset($fetch_error) && $fetch_error): ?>
        <div class="fetch-error-notice">
            <p><?php echo htmlspecialchars($fetch_error_message); ?></p>
        </div>
    <?php elseif(isset($has_partial_data) && $has_partial_data): ?>
        <div class="partial-data-notice">
            <p>Some product details were found and filled in automatically. Please complete the remaining fields manually.</p>
        </div>
    <?php endif; ?>
    <?php if(isset($error_msg)) echo $error_msg?>
    <form method="POST" action="/wishlists/<?php echo $wishlistID; ?>/item" enctype="multipart/form-data" id="item-form" data-wishlist-id="<?php echo $wishlistID; ?>">
        <div class="flex form-flex">
            <?php
            $add = true;
            include __DIR__ . '/_form.php';
            ?>
            <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Add Item"></p>
        </div>
    </form>
</div>

<script src="/public/scripts/autosize-master/autosize-master/dist/autosize.js"></script>
<script src="/public/js/form-validation.js?v=2.9"></script>
<script src="/public/js/item-form.js?v=2.9.1"></script>
