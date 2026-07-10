<?php
/**
 * Variables available in this view:
 * @var array $wishlist
 * @var array $items
 * @var array $all_items
 * @var array $other_wishlists
 * @var array $user
 * @var array $flash
 * @var string $searchTerm
 * @var bool $isAdminView
 * @var bool|int $from_public
 * @var float|null $wishlist_total_price
 */

use App\Services\WishlistRenderService;

// Wishlist data
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
$visibility = $wishlist['visibility'];
$complete = $wishlist['complete'];

// Get background image if theme is set
$background_image = \App\Services\ThemeService::getBackgroundImage($theme_background_id) ?? '';

// All popup messages are now handled by PopupHelper

// Check for copy errors and preserve selected wishlists BEFORE handling flash messages
$copy_from_error = isset($_GET['copy_from_error']) && $_GET['copy_from_error'] == '1';
$copy_from_wishlist_id = null;
if ($copy_from_error && isset($_GET['copy_from_wishlist_id'])) {
    $copy_from_wishlist_id = (int)$_GET['copy_from_wishlist_id'];
}

$copy_to_error = isset($_GET['copy_to_error']) && $_GET['copy_to_error'] == '1';
$copy_to_wishlist_id = null;
if ($copy_to_error && isset($_GET['copy_to_wishlist_id'])) {
    $copy_to_wishlist_id = (int)$_GET['copy_to_wishlist_id'];
}

// Handle all popup messages using the helper, but skip general error popup for copy errors
\App\Helpers\PopupHelper::handleSessionPopups();
if (!$copy_from_error && !$copy_to_error) {
    \App\Helpers\PopupHelper::handleFlashMessages($flash);
} else {
    // Only show success messages, not errors (copy errors are shown in the popup)
    if (isset($flash['success'])) {
        \App\Helpers\PopupHelper::handleFlashMessages(['success' => $flash['success']]);
    }
}

// Initialize copy form variables
$other_wishlist_copy_from = "";
$copy_from_select_all = "Yes";
$other_wishlist_copy_to = "";
$copy_to_select_all = "Yes";
$other_wishlist_options = $other_wishlists;

// Preserve selected wishlist if there's a copy error
if ($copy_from_error && $copy_from_wishlist_id !== null) {
    $other_wishlist_copy_from = $copy_from_wishlist_id;
}
if ($copy_to_error && $copy_to_wishlist_id !== null) {
    $other_wishlist_copy_to = $copy_to_wishlist_id;
}

// Initialize filter variables
$sort_priority = $_SESSION['wisher_sort_priority'] ?? "";
$sort_price = $_SESSION['wisher_sort_price'] ?? "";
$_SESSION['wisher_sort_priority'] = $sort_priority;
$_SESSION['wisher_sort_price'] = $sort_price;

// Build SQL order clause based on filters
$priority_order = $sort_priority ? "priority ASC, " : "";
$price_order = $sort_price ? "price {$sort_price}, " : "";
?>
<input type="hidden" id="wishlist_type" value="<?php echo strtolower($type); ?>" />
<input type="hidden" id="wishlist_show_wishlist_id" value="<?php echo $wishlistID; ?>" />
            <?php
            // All popups are now handled by PopupHelper at the top of the file
            ?>

            <?php if($theme_background_id != 0 && $background_image){ ?>
                <img class='background-theme desktop-background' src="/public/images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
                <img class='background-theme mobile-background' src="/public/images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
            <?php } ?>
            <p>
                <a class="button accent" href="<?php echo isset($isAdminView) && $isAdminView ? '/admin/wish-lists' : ($from_public !== false ? '/' . $user['username'] . '/wishlists' : '/wishlists'); ?>">
                    Back to All Wish Lists
                </a>
            </p>

            <div class="center">
                <div class="wishlist-header center transparent-background">
                    <h1>
                        <span class="wishlist-title"><?= $wishlistTitle; ?></span>
                        <?= WishlistRenderService::generateWishListActionMenu(true, $wishlist_name_input, $wishlistID, $complete === 'No', $visibility === 'Public', $visibility, $items, $other_wishlist_options); ?>
                    </h1>
                    <div class="flex-row">
                        <div><strong>Status:</strong> <span id="wish-list-status"><?php echo $complete == "Yes" ? "Inactive" : "Active"; ?></span></div>
                        <div class="visibility-container <?= $complete == "Yes" ? "hidden" : "" ?>"><strong>Visibility:</strong> <span id="wish-list-visibility"><?php echo htmlspecialchars($visibility); ?></span></div>
                    </div>
                    <div class="copy-link">
                        <?php
                        // Build the full buyer URL
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                        $host = $_SERVER['HTTP_HOST'];
                        $buyerUrl = $protocol . $host . '/buyer/' . $secret_key;
                        ?>
                        <a class="button primary" href="#" data-copy-text="<?php echo $buyerUrl; ?>"><?php require(__DIR__ . '/../../public/images/site-images/icons/copy-link.php'); ?><span style="color: inherit;" class="copy-link-text">Copy Link to Public Wish List</span></a>
                    </div>
                </div>
            </div>
                                                    
            <?php if(isset($wishlist_total_price)): ?>
                <div class="center">
                    <div class="wishlist-total">
                        <strong>Total Price: </strong><span>$<span class="total-price-value"><?= htmlspecialchars($wishlist_total_price); ?></span></span>
                    </div>
                </div>
            <?php endif; ?>

                    <!-- Sort/Filter section -->
                    <?php if(count($items) > 0): ?>
                    <div class="sort-filters">
                        <?php 
                        $baseUrl = isset($isAdminView) && $isAdminView ? "/admin/wish-lists/view?id={$wishlistID}" : "/wishlists/{$wishlistID}";
                        $options = [
                            'form_action' => "/wishlists/{$wishlistID}/filter",
                            'form_class' => 'filter-form center',
                            'sort_priority' => $filters['sort_priority'] ?? '',
                            'sort_price' => $filters['sort_price'] ?? '',
                            'data_attributes' => 'data-base-url="' . $baseUrl . '"'
                        ];
                        include __DIR__ . '/../components/sort-filter-form.php';
                        ?>
                    </div>
                    <?php endif; ?>

            <div class='items-list-container'>
                <h2 class='transparent-background items-list-title' id='paginate-top'>
                    All Items
                    <a href='/wishlists/<?php echo $wishlistID; ?>/item/add' class='icon-container add-item'>
                        <?php require(__DIR__ . '/../../public/images/site-images/icons/plus.php'); ?>
                        <div class='inline-label'>Add</div>
                    </a>
                </h2>
                
                <!-- Search input for items -->
                <?php if(count($items) > 0 || !empty($searchTerm ?? '')): ?>
                <?php
                $options = [
                    'placeholder' => 'Search items by name, price, link, or notes...',
                    'input_id' => 'items-search',
                    'input_class' => 'items-search-input',
                    'container_class' => 'center',
                    'search_term' => $searchTerm ?? ''
                ];
                include __DIR__ . '/../components/admin-table-search.php';
                ?>
                <?php endif; ?>
                
                <!-- Container for pagination and items (loading overlay covers this area) -->
                <div class='items-content-container'>
                    <!-- Top Pagination controls -->
                    <?php 
                    $position = 'top';
                    $total_count = count($all_items);
                    include __DIR__ . '/../components/pagination-controls.php'; 
                    ?>
                    
                    <div class='items-list-sub-container'>
                    <div class="items-list main">
                        <?php if(count($items) > 0): ?>
                            <?php 
                            // Use the same item generation service as AJAX pagination
                            foreach($items as $item): 
                                echo \App\Services\ItemRenderService::renderItem($item, $wishlistID, $pageno, 'wisher', $searchTerm ?? '');
                            endforeach;
                            ?>
                        <?php else: ?>
                            <a class='item-container add-placeholder' href='/wishlists/<?php echo $wishlist['id']; ?>/item/add'>
                                <div class='item-image-container'>
                                    <img class='item-image' src='/public/images/site-images/default-photo.png' alt='wishlist item image'>
                                </div>
                                <div class='item-description'></div>
                                <div class='add-label'>
                                    <?php require(__DIR__ . '/../../public/images/site-images/icons/plus.php'); ?>
                                    Add Item
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                            <!-- Pagination controls -->
                            <?php 
                            $position = 'bottom';
                            $total_count = count($all_items);
                            $item_label = 'items';
                            include __DIR__ . '/../components/pagination-controls.php'; 
                            ?>
                    </div>
                </div>
            </div>

            <?= \App\Helpers\ThemePopupHelper::renderThemePopup(strtolower($type), true); ?>

            <?php if(count($other_wishlist_options) > 0): ?>
                <div id="wish-list-show-copy-from-popup" class='popup-container first center-items<?php echo ($copy_from_error ? '' : ' hidden'); ?>'>
                    <div class='popup'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>
                            <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                            </a>
                        </div>
                        <div class='popup-content'>
                            <h2>Copy Items From Another Wish List</h2>
                            <?php if ($copy_from_error && isset($flash['error'])): ?>
                                <div class="validation-error" style="display: block; margin-bottom: 15px;">
                                    <span class="error-item" style="color: #e74c3c;"><?php echo htmlspecialchars($flash['error']); ?></span>
                                </div>
                            <?php endif; ?>
                            <form method="POST" action="/wishlists/<?php echo $wishlistID; ?>/copy-from">
                                <label for="other_wishlist_copy_from">Choose Wish List:</label><br />
                                <select id="other_wishlist_copy_from" class="copy-select" name="other_wishlist_copy_from" data-base-url="<?php echo isset($isAdminView) && $isAdminView ? "/admin/wish-lists/view?id={$wishlistID}" : "/wishlists/{$wishlistID}"; ?>" required>
                                    <option value="" disabled <?php if($other_wishlist_copy_from == "") echo "selected"; ?>>Select an option</option>
                                    <?php
                                    foreach($other_wishlist_options as $opt){
                                        $other_id = $opt['id'];
                                        $other_name = htmlspecialchars($opt['wishlist_name']);
                                        $item_count = isset($opt['item_count']) ? (int)$opt['item_count'] : 0;
                                        $item_count_text = $item_count === 1 ? "1 item" : "$item_count items";
                                        echo "<option value='$other_id'";
                                        if($item_count === 0) {
                                            echo " disabled";
                                        } else if($other_id == $other_wishlist_copy_from) {
                                            echo " selected";
                                        }
                                        echo ">$other_name ($item_count_text)</option>";
                                    }
                                    ?>
                                </select>
                                <div class="other-items copy-from<?php if($other_wishlist_copy_from == "") echo " hidden"; ?>">
                                    <label>Select Items:</label><br />
                                    <div class="item-checkboxes">
                                        <!-- Items will be loaded via AJAX -->
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(count($items) > 0): ?>
                <div id="wish-list-show-copy-to-popup" class='popup-container first center-items<?php echo ($copy_to_error ? '' : ' hidden'); ?>'>
                    <div class='popup'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>
                            <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                            </a>
                        </div>
                        <div class='popup-content'>
                            <h2>Copy Items to Another Wish List</h2>
                            <?php if ($copy_to_error && isset($flash['error'])): ?>
                                <div class="validation-error" style="display: block; margin-bottom: 15px;">
                                    <span class="error-item" style="color: #e74c3c;"><?php echo htmlspecialchars($flash['error']); ?></span>
                                </div>
                            <?php endif; ?>
                            <form method="POST" action="/wishlists/<?php echo $wishlistID; ?>/copy-to">
                                <label for="other_wishlist_copy_to">Choose Wish List:</label><br />
                                <select id="other_wishlist_copy_to" class="copy-select" name="other_wishlist_copy_to" data-base-url="<?php echo isset($isAdminView) && $isAdminView ? "/admin/wish-lists/view?id={$wishlistID}" : "/wishlists/{$wishlistID}"; ?>" required>
                                    <option value="" disabled <?php if($other_wishlist_copy_to == "") echo "selected"; ?>>Select an option</option>
                                    <?php
                                    foreach($other_wishlist_options as $opt){
                                        $other_id = $opt['id'];
                                        $other_name = htmlspecialchars($opt['wishlist_name']);
                                        echo "<option value='$other_id'";
                                        if($other_id == $other_wishlist_copy_to) echo " selected";
                                        echo ">$other_name</option>";
                                    }
                                    ?>
                                </select>
                                <div class="other-items copy-to<?php if($other_wishlist_copy_to == "") echo " hidden"; ?>">
                                    <label>Select Items:</label><br />
                                    <div class="item-checkboxes">
                                        <!-- Items will be loaded via AJAX -->
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    
    <!-- Image Popup Container -->
    <div class='popup-container image-popup-container hidden'>
        <div class='popup image-popup'>
            <div class='close-container'>
                <a href='#' class='close-button'>
                    <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                </a>
            </div>
            <div class='popup-content'>
                <img class='popup-image' src='' alt='Full size item image'>
                <div class="change-image-container">
                    <p class="center" style="margin-bottom: 0;">
                        <a class="change-image-button button secondary">Choose New Item Image</a>
                        <input type="file" name="new_item_image" class="hidden" id="newItemImage" accept=".png, .jpg, .jpeg, .webp">
                        <input type="text" placeholder="Or paste a new image here..." id="paste-new-image" />
                        <div class="loading-spinner"></div>
                    </p>
                </div>
            </div>
        </div>
    </div>

    

    <?php require __DIR__ . '/../components/wishlist-action-popups.php'; ?>

<!-- Wishlist-specific scripts -->
<script src="/public/js/copy-link.js?v=2.9"></script>
<script src="/public/js/copy-select.js?v=2.9"></script>
<script src="/public/js/checkbox-selection.js?v=2.9"></script>
<script src="/public/js/wishlist-filters.js?v=2.9"></script>
<script src="/public/js/pagination.js?v=2.9"></script>
<script src="/public/js/admin-table-search.js?v=2.9"></script>
<script src="/public/js/choose-theme.js?v=2.9"></script>
<script src="/public/scripts/autosize-master/autosize-master/dist/autosize.js"></script>
<script src="/public/js/form-validation.js?v=2.9"></script>
<script src="/public/js/item-form.js?v=2.9.1"></script>
<script src="/public/js/add-alert-message.js?v=2.9"></script>
<script src="/public/js/wishlist-action-menu.js?v=2.9"></script>
<script src="/public/js/see-more-link.js?v=2.9"></script>
<script>$type = "wisher"; $key_url = "";</script>
<script>
    $(document).ready(function() {
        // Initialize form validation for rename form
        const renameForm = $('form[action*="/rename"]');
        if (renameForm.length) {
            FormValidator.init('form[action*="/rename"]', {
                wishlist_name: {
                    required: true,
                    minLength: 1,
                    maxLength: 100
                }
            });
        }
        
        // Auto-open popups if there's a rename error
        <?php if (isset($flash['rename_error'])): ?>
        // Open settings popup first, then rename popup
        const settingsButton = $('.button.primary.flex-button.popup-button');
        if (settingsButton.length) {
            // Click settings button to open settings popup
            settingsButton.trigger('click');
                        
            // Wait for settings popup to open, then open rename popup
            setTimeout(function() {
                // Find the rename button within the wishlist-options section
                const renameButton = $('.wishlist-options .icon-container.popup-button').first();
                if (renameButton.length) {
                    renameButton.trigger('click');
                }
            }, 150);
        }
        <?php endif; ?>
        
        // Reusable function to load items when copy error occurs and wishlist is preselected
        function loadItemsForCopyError(selectId, hasError) {
            if (hasError) {
                setTimeout(function() {
                    const select = $(selectId);
                    if (select.length && select.val()) {
                        select.trigger('change');
                    }
                }, 100);
            }
        }
        
        // Load items for copy-from if error and wishlist was selected
        loadItemsForCopyError('#other_wishlist_copy_from', <?php echo $copy_from_error && $other_wishlist_copy_from != "" ? 'true' : 'false'; ?>);
        
        // Load items for copy-to if error and wishlist was selected
        loadItemsForCopyError('#other_wishlist_copy_to', <?php echo $copy_to_error && $other_wishlist_copy_to != "" ? 'true' : 'false'; ?>);

        function cancelEditForm() {
            $('.edit-item-button').removeClass('disabled-icon disabled-showing');
            $('.item-form').removeClass('active-form').html('').hide();
            $('.item-information').show();
            $('.view-on-website-link').removeClass('invisible');
            $('.item-actions').show();
            $('.item-form-actions').hide();
        }

        $(document.body).on('click', '.edit-item-button', function(e) {
            e.preventDefault();
            $editButton = $(this);
            var $activeDescription = $('.item-form.active-form').closest('.item-description');
            if ($activeDescription.length > 0) {
                $activeDescription.css('height', $activeDescription.outerHeight() + 'px')
            }
            cancelEditForm();
            $('.edit-item-button').addClass('disabled-icon');
            $itemContainer = $editButton.closest('.item-container');
            var itemId = $itemContainer.data('item-id');
            var wishListId = $('#wishlist_show_wishlist_id').val();
            
            $.ajax({
                url: `/wishlists/${wishListId}/fetch-item-form`,
                type: 'POST',
                data: { id: itemId },
                success: function(response) {
                    if (response.status === 'error') {
                        addAlertMessage(response.message);
                        cancelEditForm();
                        $('.item-description').css('height', 'auto');
                        return;
                    }
                    $('.edit-item-button').removeClass('disabled-icon');
                    $itemContainer.find('.view-on-website-link').addClass('invisible');
                    $itemContainer.find('.item-actions').hide();
                    $itemContainer.find('.item-form-actions').show();
                    $editButton.addClass('disabled-icon disabled-showing');
                    $itemContainer.find('.item-information').hide();
                    $itemContainer.find('.item-form').addClass('active-form').html(response.formHTML).show();
                    if ($activeDescription.length > 0) {
                        $activeDescription.css('height', 'auto');
                    }
                    initializeItemForm();
                    scrollToElement($itemContainer.find('.item-form'), 50);
                },
                error: function() {
                    addAlertMessage('Failed to load edit form for item');
                    cancelEditForm();
                    $('.item-description').css('height', 'auto');
                }
            });
        });

        $(document.body).on('click', '.edit-item-submit', function(e) {
            e.preventDefault();
            $itemContainer = $(this).closest('.item-container');
            $itemContainer.find('.edit-item-submit').css('pointer-events', 'none');
            const formData = new FormData($itemContainer.find('#item-form')[0]);
            var itemId = $itemContainer.data('item-id');
            var wishListId = $('#wishlist_show_wishlist_id').val();

            $.ajax({
                url: `/wishlists/${wishListId}/item/${itemId}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('.total-price-value').html(response.wishListTotalPrice);
                        $itemContainer.find('.item-information').html(response.newItemInformation);
                        $itemContainer.find('.submit-error').remove();
                        cancelEditForm();
                        addAlertMessage('Item updated successfully');
                        $itemContainer.find('.edit-item-submit').css('pointer-events', '');
                        if (typeof checkEllipsis === "function") {
                            checkEllipsis();
                        }
                        scrollToElement($itemContainer, 20);
                    } else {
                        $itemContainer.find('.submit-error').remove();
                        $itemContainer.find('#item-form').prepend(response.errors || '<div class="submit-error">Unable to update item. Please try again.</div>');
                        $itemContainer.find('.edit-item-submit').css('pointer-events', '');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.error 
                        ? xhr.responseJSON.error 
                        : 'Error updating item. Please try again.';
                    $itemContainer.find('.submit-error').remove();
                    $itemContainer.find('#item-form').prepend(`<div class="submit-error">${errorMsg}</div>`);
                    $itemContainer.find('.edit-item-submit').css('pointer-events', '');
                }
            });
        });

        $(document.body).on('click', '.cancel-form-button', function(e) {
            e.preventDefault();
            cancelEditForm();
        });

            // File input click handler
        $('.change-image-button').on('click', function(e) {
            e.preventDefault();
            const $fileInput = $('#newItemImage');
            if ($fileInput.length) {
                $fileInput.trigger('click');
            }
        });

        function changeItemImage(formData) {
            var wishListId = $('#wishlist_show_wishlist_id').val();
            const itemId = $('#newItemImage').data('item-id');
            const $changeImageContainer = $('.change-image-container');
            $changeImageContainer.css('height', $changeImageContainer.outerHeight() + 'px').addClass('loading');
            
            $.ajax({
                url: `/wishlists/${wishListId}/item/${itemId}/change-image`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('.image-popup-container .popup .popup-image').attr("src", response.newImage);
                        $(`.item-container[data-item-id="${itemId}"]`).find(".item-image").attr("src", response.newImage);
                        addAlertMessage('Item image changed successfully');
                        $('.change-image-button').css('pointer-events', '');
                        $('.change-image-container').css('height', '').removeClass('loading');
                    } else {
                        $changeImageContainer.find('.submit-error').remove();
                        $changeImageContainer.prepend(response.errors || '<div class="submit-error">Unable to change item image. Please try again.</div>');
                        $('.change-image-button').css('pointer-events', '');
                        $('.change-image-container').css('height', '').removeClass('loading');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.error 
                        ? xhr.responseJSON.error 
                        : 'Error updating item. Please try again.';
                    $changeImageContainer.find('.submit-error').remove();
                    $changeImageContainer.prepend(`<div class="submit-error">${errorMsg}</div>`);
                    $('.change-image-button').css('pointer-events', '');
                    $('.change-image-container').css('height', '').removeClass('loading');
                }
            });
        }

        // On file select, upload file immediately
        $('#newItemImage').on('change', function() {
            var $newImageInput = $(this);
            $('.change-image-button').css('pointer-events', 'none');
            const file = this.files && this.files[0];
        
            if (!file) {
                addAlertMessage('Please select an image first.');
                return;
            }
            
            const formData = new FormData();
            formData.append('item_image', file);
            
            changeItemImage(formData);
        });
        
        // Handle URL input in paste image field
        $(document.body).on("paste", "#paste-new-image", function(e) {
            e.preventDefault();
            const clipboardData = e.originalEvent.clipboardData;
            
            if (clipboardData && clipboardData.items) {
                for (let i = 0; i < clipboardData.items.length; i++) {
                    const item = clipboardData.items[i];
                    
                    if (item.type.indexOf('image') !== -1) {
                        const file = item.getAsFile();
                        const reader = new FileReader();
                        
                        reader.onload = function(event) {
                            const formData = new FormData();
                            formData.append('paste_image', event.target.result);
                            
                            changeItemImage(formData);
                        };

                        reader.readAsDataURL(file);
                    }
                }
            }
        });

        $(document.body).on("keydown", "#paste-new-image", function(e) {
            // Allow Ctrl/Cmd shortcuts (copy, paste, select all, etc.)
            if (e.ctrlKey || e.metaKey) {
                return;
            }

            // Allow navigation/editing keys
            const allowedKeys = [
                "Backspace",
                "Delete",
                "ArrowLeft",
                "ArrowRight",
                "ArrowUp",
                "ArrowDown",
                "Home",
                "End",
                "Tab"
            ];

            if (allowedKeys.includes(e.key)) {
                return;
            }

            // Block everything else (typing)
            e.preventDefault();
        });
    });
</script>