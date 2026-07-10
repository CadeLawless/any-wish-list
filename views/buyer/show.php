<?php
/**
 * Variables available in this view:
 * @var array $wishlist
 * @var array $wishlist_user
 * @var array $items
 * @var array $all_items
 * @var int $pageno
 * @var int $total_pages
 * @var string $sort_priority
 * @var string $sort_price
 * @var array $user_wishlists
 * @var array|null $user
 * @var bool|array $from_friends - false if not from friends, otherwise contains the friend's user info
 * @var string $search - the search term if coming from a search within the wishlist, otherwise empty string
 */

// Buyer view for public wishlist access - matches original structure
$wishlistID = $wishlist['id'];
$wishlist_name_input = $wishlist['wishlist_name'];
$wishlist_name = htmlspecialchars($wishlist_name_input);
$wishlistTitle = $wishlist_name;
$year = $wishlist['year'];
$type = $wishlist['type'];
$duplicate = $wishlist['duplicate'] == 0 ? "" : " ({$wishlist['duplicate']})";
$secret_key = $wishlist['secret_key'];
$theme_background_id = $wishlist['theme_background_id'];
$theme_gift_wrap_id = $wishlist['theme_gift_wrap_id'];

// Get background image if theme is set
$background_image = \App\Services\ThemeService::getBackgroundImage($theme_background_id);

// Get user name from username
$username = $wishlist['username'];
$stmt = \App\Core\Database::query("SELECT name FROM wishlist_users WHERE username = ?", [$username]);
$name_result = $stmt->get_result()->fetch_assoc();
$name = $name_result ? htmlspecialchars($name_result['name']) : $username;

if (isset($flash['success'])) {
	\App\Helpers\PopupHelper::handleFlashMessages(['success' => $flash['success']]);
}
?>

<?php if($background_image): ?>
    <img class='background-theme desktop-background' src="/public/images/site-images/themes/desktop-backgrounds/<?php echo htmlspecialchars($background_image); ?>" />
    <img class='background-theme mobile-background' src="/public/images/site-images/themes/mobile-backgrounds/<?php echo htmlspecialchars($background_image); ?>" />
<?php endif; ?>

<?php if($user !== null && $from_friends !== false): ?>
	<p>
		<a class="button accent" href="<?php echo htmlspecialchars("/" . $wishlist_user['username'] . "/wishlists" . ($search !== '' ? '?search=' . urlencode($search) : '')); ?>">
			Back to <?=  htmlspecialchars($wishlist_user['name']) ?>'s Wish Lists
		</a>
	</p>
<?php endif; ?>

<div class="center">
    <div class="wishlist-header center transparent-background">
        <h1><?php echo $wishlistTitle; ?></h1>
    </div>
</div>

<div class='items-list-container'>
    <?php if(!empty($items)): ?>

        <!-- Sort and Filter Form -->
        <?php 
        $options = [
            'form_class' => 'filter-form center',
            'sort_priority' => $sort_priority,
            'sort_price' => $sort_price,
            'data_attributes' => 'data-base-url="/buyer/' . $secret_key . '"'
        ];
        include __DIR__ . '/../components/sort-filter-form.php';
        ?>
    <?php endif; ?>
    
    <h2 class="transparent-background items-list-title" id='paginate-top' class='center'>All Items</h2>
    
    <?php if(!empty($items)): ?>
        
        <!-- Top Pagination controls -->
        <?php include __DIR__ . '/../components/pagination-controls.php'; ?>
    <?php endif; ?>
    
    <?php if(empty($items)): ?>
        <p style="padding: 0.6rem; border-radius: 4px; background-color: var(--transparent-background);">No items in this wishlist yet.</p>
    <?php else: ?>
        <div class='items-list-sub-container'>
            <div class="items-list main">
                <?php foreach($items as $item): ?>
                    <?php echo \App\Services\ItemRenderService::renderItem(
						item: $item,
						wishlistId: $wishlistID,
						page: $pageno,
						type: 'buyer',
						userWishLists: $user_wishlists
					); ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Pagination controls -->
        <?php 
        $position = 'bottom';
        $total_count = count($all_items);
        $item_label = 'items';
        include __DIR__ . '/../components/pagination-controls.php'; 
        ?>
    <?php endif; ?>
</div>

<!-- Image popup container (global, reused for all items) -->
<div class='popup-container image-popup-container hidden'>
    <div class='popup image-popup'>
        <div class='close-container transparent-background'>
            <a href='#' class='close-button'>
                <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
            </a>
        </div>
        <img class='popup-image' src='' alt='wishlist item image'>
    </div>
</div>

<script src="/public/js/see-more-link.js?v=2.9"></script>
<script>$type = "buyer";</script>
<script>
	$(document).ready(function(){
		// sync quantity inputs into hidden fields before submit
		$(document.body).on('input change', "input[id^='purchase-qty-']", function(){
			var input = this;
			var id = input.id.replace('purchase-qty-','');
			var hidden = $(input).closest('.popup-content').find("input[type='hidden'][name='quantity'][data-bind-from='purchase-qty-"+id+"']");
			if(hidden.length){ hidden.val(input.value || 1); }
		});

		// confetti on mark as purchased and submit form
		$(document.body).on("click", ".purchase-button", function(e){
			e.preventDefault();
			let button = this;
			let form = $(button).closest('form')[0];
			let item_id = $(button).data('item-id');
			setTimeout(function(){
				if(form) { form.submit(); }
			}, 3000);
			button.style.pointerEvents = "none";
			var windowWidth = window.innerWidth;
			var windowHeight = window.innerHeight;
			let position = button.getBoundingClientRect();
			let left = position.left;
			let top = position.top;
			let centerX = left + button.offsetWidth / 2;
			centerX = centerX / windowWidth * 100;
			let centerY = top + button.offsetHeight / 2;
			centerY = centerY / windowHeight * 100;
			button.style.backgroundColor = "var(--accent)";
			confetti("tsparticles", {
				angle: 90,
				count: 200,
				position: {
					x: centerX,
					y: centerY,
				},
				spread: 60,
				startVelocity: 45,
				decay: 0.9,
				gravity: 1,
				drift: 0,
				ticks: 200,
				shapes: ["image"],
				shapeOptions: {
					image: [
						{
							src: '/public/images/site-images/confetti/christmas-confetti-1.png',
							width: 100,
							height: 100,
						},
						{
							src: '/public/images/site-images/confetti/christmas-confetti-2.png',
							width: 100,
							height: 100,
						},
						{
							src: '/public/images/site-images/confetti/christmas-confetti-3.png',
							width: 100,
							height: 100,
						},
						{
							src: '/public/images/site-images/confetti/christmas-confetti-4.png',
							width: 100,
							height: 100,
						},
						{
							src: '/public/images/site-images/confetti/christmas-confetti-5.png',
							width: 100,
							height: 100,
						},
						{
							src: '/public/images/site-images/confetti/christmas-confetti-6.png',
							width: 100,
							height: 100,
						}
					],
				},
				scalar: 3,
				zIndex: 1002,
				disableForReducedMotion: true,
			});
		});

		$(document).on('click', '.add-item-button', function(e){
			e.preventDefault();
			var button = $(this);
			var popup = button.closest('.popup');
			var select = popup.find('select[name="target_wishlist_id"]');
			var wishlistId = select.val();
			var itemId = popup.find('input[name="item_id"]').val();
			if(!wishlistId) {
				if(popup.find('.error-message').length) {
					popup.find('.error-message').remove();
				}
				popup.find('.popup-content').append('<p class="error-message">Please select a wish list.</p>');
				return;
			}
			button.prop('disabled', true);
			$.ajax({
				url: '/buyer/add-item-to-wishlist',
				method: 'POST',
				data: {
					item_id: itemId,
					wishlist_id: wishlistId
				},
				success: function(response) {
					if(response.status === 'error') {
						if(popup.find('.error-message').length) {
							popup.find('.error-message').remove();
						}
						popup.find('.popup-content').append('<p class="error-message">' + (response.message || 'An error occurred.') + '</p>');
						return;
					}
					popup.find('.popup-content').html('<p>The item has been added to your wish list!</p><p class="center" style="margin-top: 1rem;"><a class="button primary" href="/wishlists/'+wishlistId+'">View Updated Wish List</a></p>');
				},
				error: function() {
					if(popup.find('.error-message').length) {
						popup.find('.error-message').remove();
					}
					popup.find('.popup-content').append('<p class="error-message">An error occurred while adding the item. Please try again.</p>');
				},
				complete: function() {
					button.prop('disabled', false);
				}
			});
		})
	});
	
</script>
