<?php

namespace App\Services;

use App\Core\Constants;

class ItemRenderService
{
    public static function renderItem(array $item, int $wishlistId, int $page, string $type = 'wisher', string $searchTerm = '', array $userWishLists = []): string
    {
        $itemId = $item['id'];
        $itemName = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');   
        $link = htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8');
        $imagePath = htmlspecialchars("/public/images/item-images/{$wishlistId}/{$item['image']}?t=" . time(), ENT_QUOTES, 'UTF-8');
        $dateAdded = date("n/j/Y g:i A", strtotime($item['date_added']));
        $dateModified = $item['date_modified'] ? date("n/j/Y g:i A", strtotime($item['date_modified'])) : '';
        
        // Get wisher's name for personalized messages
        $wisherName = 'User';
        if ($type === 'buyer') {
            // For buyer view, get the wisher's name from the wishlist
            $wisherName = \App\Models\Wishlist::getWisherName($wishlistId) ?? 'User';
            $wisherName = htmlspecialchars($wisherName, ENT_QUOTES, 'UTF-8');
        } else {
            // For wisher view, get current user's name
            $wisherName = \App\Services\SessionManager::get('name', 'User');
        }
        
        $itemLinkLabel = "View on Store Website";
        
        // Check if this is a purchased item in buyer view
        $isPurchasedInBuyerView = $type === 'buyer' && $item['purchased'] === 'Yes';

        // Use output buffering to capture the PHP includes
        ob_start();
        ?>
        <div class='item-container' data-item-id="<?= $itemId ?>">
            <?php if($isPurchasedInBuyerView): ?>
                <?php
                // Get the wishlist's gift wrap theme
                $wishlist = \App\Models\Wishlist::find($wishlistId);
                $giftWrapImage = null;
                if ($wishlist && $wishlist['theme_gift_wrap_id']) {
                    $giftWrapImage = \App\Services\ThemeService::getGiftWrapImage($wishlist['theme_gift_wrap_id']);
                }
                
                // Use default gift wrap if no theme is set
                if (!$giftWrapImage) {
                    $giftWrapImage = 'default';
                }
                
                // Determine available wrap count and select deterministic wrap number within range
                $wrapCount = \App\Services\ThemeService::getGiftWrapFileCount($giftWrapImage);
                if ($wrapCount < 1) { $wrapCount = 1; }
                
                // Compute this purchased item's position among all purchased items in this wishlist
                $position = 1;
                if (!empty($item['id'])) {
                    $position = \App\Models\Item::getPurchasedPosition($wishlistId, (int)$item['id']);
                }
                
                // Assign wrap number based on purchase order, cycling through available wraps
                $giftWrapNumber = (($position - 1) % $wrapCount) + 1;
                ?>
                <img src='/public/images/site-images/themes/gift-wraps/<?php echo $giftWrapImage; ?>/<?php echo $giftWrapNumber; ?>.png' class='gift-wrap' alt='gift wrap'>
            <?php endif; ?>
            <div class='item-image-container image-popup-button'>
                <img class='item-image' src='<?php echo $imagePath; ?>' alt='wishlist item image'>
                <?php if ($type === 'wisher'): ?>
                    <a class="action-icon edit-image-button" href='#'>
                        <?php require(__DIR__ . '/../../public/images/site-images/icons/edit.php'); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class='item-description' <?php if($isPurchasedInBuyerView) echo "style='flex-grow: 0;'"; ?>>
                <?php if(!$isPurchasedInBuyerView): ?>
                    <div class="line link-line<?= $type == "buyer" ? " justify-center" : "" ?>">
                        <?php if($type === 'buyer' && $item['unlimited'] !== 'Yes'): ?>
                            <div>
                                <a class='button secondary view-on-website-link popup-button' href='#'>
                                <span><?= $itemLinkLabel ?></span>
                                <?php require(__DIR__ . '/../../public/images/site-images/icons/open-in-new-window.php'); ?>
                                </a>
                                <div class='popup-container hidden'>
                                    <div class='popup'>
                                        <div class='close-container'>
                                            <a href='#' class='close-button'>
                                                <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                            </a>
                                        </div>
                                        <div class='popup-content'>
                                            <h2 style='margin-top: 0;'>Purchase Reminder</h2>
                                            <p>Please make sure to come back and mark this item as purchased if you buy it. <?php echo $wisherName; ?> will not see that you purchased it.</p>
                                            <div style='margin: 16px 0;' class='center'>
                                                <a class='button primary' href='<?php echo $link; ?>' target='_blank'>View Item on Store Website</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a class='button secondary view-on-website-link' href='<?php echo $link; ?>' target='_blank'>
                                <span><?= $itemLinkLabel ?></span>
                                <?php require(__DIR__ . '/../../public/images/site-images/icons/open-in-new-window.php'); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($type == 'wisher'): ?>
                            <div class="item-actions">
                                <a class="action-icon edit-item-button" href='#'>
                                    <?php require(__DIR__ . '/../../public/images/site-images/icons/edit.php'); ?>
                                </a>
                                <a class='action-icon popup-button' href='#'>
                                    <?php require(__DIR__ . '/../../public/images/site-images/icons/delete-trashcan.php'); ?>
                                </a>
                                <div class='popup-container hidden'>
                                    <div class='popup'>
                                        <div class='close-container'>
                                            <a href='#' class='close-button'>
                                                <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                            </a>
                                        </div>
                                        <div class='popup-content'>
                                            <?php 
                                            $copyId = $item['copy_id'] ?? '';
                                            $purchased = $item['purchased'] ?? 'No';
                                            
                                            if(empty($copyId)): 
                                                // Not a copied item - simple confirmation
                                            ?>
                                                <label>Are you sure you want to delete this item?</label>
                                                <p><?php echo $itemName; ?></p>
                                                <div style='margin: 16px 0;' class='delete-popup-buttons center'>
                                                    <a class='button secondary no-button' href='#'>No</a>
                                                    <?php if($purchased == 'Yes'): ?>
                                                        <a class='button primary popup-button' href='#'>Yes</a>
                                                        <div class='popup-container first hidden'>
                                                            <div class='popup'>
                                                                <div class='close-container'>
                                                                    <a href='#' class='close-button'>
                                                                        <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                                                    </a>
                                                                </div>
                                                                <div class='popup-content'>
                                                                    <p><strong>NOTE: This item has already been marked as purchased.</strong></p>
                                                                    <label>Are you REALLY sure you want to delete this item?</label>
                                                                    <p><?php echo $itemName; ?></p>
                                                                    <div style="margin: 1rem 0;" class='delete-popup-buttons center'>
                                                                        <a class='button secondary no-button double-no' href='#'>No</a>
                                                                        <form method="POST" action="/wishlists/<?php echo $wishlistId; ?>/item/<?php echo $itemId; ?>?pageno=<?php echo $page; ?>" style="display: inline;">
                                                                            <input type="hidden" name="_method" value="DELETE">
                                                                            <button type="submit" class='button primary'>Yes</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <form method="POST" action="/wishlists/<?php echo $wishlistId; ?>/item/<?php echo $itemId; ?>?pageno=<?php echo $page; ?>" style="display: inline;">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button type="submit" class='button primary'>Yes</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: 
                                                // Copied item - show options
                                            ?>
                                                <label>This item has been copied to or from other wish list(s). Do you want to delete it from this list only or from ALL lists?</label>
                                                <p><?php echo $itemName; ?></p>
                                                <div style='margin: 16px 0;' class='delete-popup-buttons center'>
                                                    <a class='button secondary popup-button' href='#'>Delete from this list only</a>
                                                    <div class='popup-container hidden'>
                                                        <div class='popup'>
                                                            <div class='close-container'>
                                                                <a href='#' class='close-button'>
                                                                    <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                                                </a>
                                                            </div>
                                                            <div class='popup-content'>
                                                                <label>Are you sure you want to delete this item from this wish list only?</label>
                                                                <p><?php echo $itemName; ?></p>
                                                                <div style="margin: 1rem 0;" class='delete-popup-buttons center'>
                                                                    <a class='button secondary no-button double-no' href='#'>No</a>
                                                                    <?php if($purchased == 'Yes'): ?>
                                                                        <a class='button primary popup-button' href='#'>Yes</a>
                                                                        <div class='popup-container first hidden'>
                                                                            <div class='popup'>
                                                                                <div class='close-container'>
                                                                                    <a href='#' class='close-button'>
                                                                                        <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                                                                    </a>
                                                                                </div>
                                                                                <div class='popup-content'>
                                                                                    <p><strong>NOTE: This item has already been marked as purchased.</strong></p>
                                                                                    <label>Are you REALLY sure you want to delete this item from this wish list only?</label>
                                                                                    <p><?php echo $itemName; ?></p>
                                                                                    <div style="margin: 1rem 0;" class='center'>
                                                                                        <a class='button secondary no-button double-no' href='#'>No</a>
                                                                                        <form method="POST" action="/wishlists/<?php echo $wishlistId; ?>/item/<?php echo $itemId; ?>?pageno=<?php echo $page; ?>" style="display: inline;">
                                                                                            <input type="hidden" name="_method" value="DELETE">
                                                                                            <button type="submit" class='button primary'>Yes</button>
                                                                                        </form>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <form method="POST" action="/wishlists/<?php echo $wishlistId; ?>/item/<?php echo $itemId; ?>?pageno=<?php echo $page; ?>" style="display: inline;">
                                                                            <input type="hidden" name="_method" value="DELETE">
                                                                            <button type="submit" class='button primary'>Yes</button>
                                                                        </form>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a class='button secondary popup-button' href='#'>Delete from ALL lists</a>
                                                    <div class='popup-container hidden'>
                                                        <div class='popup'>
                                                            <div class='close-container'>
                                                                <a href='#' class='close-button'>
                                                                    <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                                                </a>
                                                            </div>
                                                            <div class='popup-content'>
                                                                <label>Are you sure you want to delete this item from ALL lists?</label>
                                                                <p><?php echo $itemName; ?></p>
                                                                <div style="margin: 1rem 0;" class='delete-popup-buttons center'>
                                                                    <a class='button secondary no-button double-no' href='#'>No</a>
                                                                    <?php if($purchased == 'Yes'): ?>
                                                                        <a class='button primary popup-button' href='#'>Yes</a>
                                                                        <div class='popup-container first hidden'>
                                                                            <div class='popup'>
                                                                                <div class='close-container'>
                                                                                    <a href='#' class='close-button'>
                                                                                        <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                                                                    </a>
                                                                                </div>
                                                                                <div class='popup-content'>
                                                                                    <p><strong>NOTE: This item has already been marked as purchased.</strong></p>
                                                                                    <label>Are you REALLY sure you want to delete this item from ALL lists?</label>
                                                                                    <p><?php echo $itemName; ?></p>
                                                                                    <div style="margin: 1rem 0;" class='delete-popup-buttons center'>
                                                                                        <a class='button secondary no-button double-no' href='#'>No</a>
                                                                                        <form method="POST" action="/wishlists/<?php echo $wishlistId; ?>/item/<?php echo $itemId; ?>?pageno=<?php echo $page; ?>&deleteAll=yes" style="display: inline;">
                                                                                            <input type="hidden" name="_method" value="DELETE">
                                                                                            <button type="submit" class='button primary'>Yes</button>
                                                                                        </form>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <form method="POST" action="/wishlists/<?php echo $wishlistId; ?>/item/<?php echo $itemId; ?>?pageno=<?php echo $page; ?>&deleteAll=yes" style="display: inline;">
                                                                            <input type="hidden" name="_method" value="DELETE">
                                                                            <button type="submit" class='button primary'>Yes</button>
                                                                        </form>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="item-form-actions" style="display: none;">
                                <a class="action-icon cancel-form-button" href='#'>
                                    <?php require(__DIR__ . '/../../public/images/site-images/icons/delete-x.php'); ?>
                                </a>
                                <a class="action-icon edit-item-submit" href='#'>
                                    <?php require(__DIR__ . '/../../public/images/site-images/icons/checkmark.php'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="item-form" style="display: none;"></div>
                <div class="item-information">
                    <?php echo self::renderItemInformation($type, $item, $isPurchasedInBuyerView); ?>
                </div>
                </div>
                <?php if(!$isPurchasedInBuyerView && count($userWishLists) > 0): ?>
                    <div class="center" style="margin: 0.5rem 0;">
                        <a class="button primary popup-button" href="#">Add to My Wish List</a>
                        <div class="popup-container hidden">
                            <div class="popup">
                                <div class="close-container">
                                    <a href="#" class="close-button">
                                        <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                    </a>
                                </div>
                                <div class="popup-content">
                                    <h2 style="margin-top: 0;">Add Item to My Wish List</h2>
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <label for="target_wishlist_id">Select Wish List:</label>
                                    <select id="target_wishlist_id" name="target_wishlist_id" required>
                                        <?php foreach($userWishLists as $wishList): ?>
                                            <option value="<?php echo (int)$wishList['id']; ?>"><?php echo htmlspecialchars($wishList['wishlist_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div style="margin: 1rem 0;" class="center">
                                        <a class="button secondary no-button" href="#">Cancel</a>
                                        <button class="button primary add-item-button">Add Item</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if($type === 'buyer'): ?>
                    <?php if($item['purchased'] !== 'Yes' && $item['unlimited'] !== 'Yes'): ?>
                        <?php $wishlistForBuyer = \App\Models\Wishlist::find($wishlistId); $secretKey = $wishlistForBuyer ? $wishlistForBuyer['secret_key'] : ''; ?>
                        <div style='margin: 18px 0;' class='center'>
                            <input class='purchased-button popup-button' type='checkbox' id='<?php echo $item['id']; ?>'><label for='<?php echo $item['id']; ?>'> Mark as Purchased</label>
                            <div class='popup-container purchased-popup-<?php echo $item['id']; ?> hidden'>
                                <div class='popup'>
                                    <div class='close-container'>
                                        <a href='#' class='close-button'>
                                            <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                        </a>
                                    </div>
                                    <div class='popup-content'>
                                        <label>Are you sure you want to mark this item as purchased?</label>
                                        <p><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php if((int)($qtyRemaining ?? 1) > 1): ?>
                                            <div class='center' style='margin: 12px 0;'>
                                                <label for='purchase-qty-<?php echo $item['id']; ?>'>How many did you buy?</label>
                                                <input id='purchase-qty-<?php echo $item['id']; ?>' name='quantity' type='number' min='1' max='<?php echo (int)$qtyRemaining; ?>' value='1' style='width: 80px; margin-left: 8px;'>
                                            </div>
                                        <?php endif; ?>
                                        <div style="margin: 1rem auto;" class='center'>
                                            <a class='button secondary no-button' href='#'>No</a>
                                            <form method='POST' action='/buyer/<?php echo $secretKey; ?>/purchase/<?php echo $item['id']; ?>' style='display: inline;' class='buyer-purchase-form'>
                                                <?php if((int)($qtyRemaining ?? 1) > 1): ?>
                                                    <input type='hidden' name='quantity' value='1' data-bind-from='purchase-qty-<?php echo $item['id']; ?>'>
                                                <?php else: ?>
                                                    <input type='hidden' name='quantity' value='1'>
                                                <?php endif; ?>
                                                <button type='submit' class='button primary purchase-button' data-item-id='<?php echo $item['id']; ?>'>Yes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif($item['purchased'] === 'Yes'): ?>
                        <div class='center' style="margin: 0.5rem 0;">
                            <h4 class='center'>This item has been purchased!</h4>
                            <span class='unmark-msg'>If you need to unmark an item as purchased, email <a style="font-size: 14px;" href='mailto:support@anywishlist.com'>support@anywishlist.com</a> for help.</span>
                        </div>
                    <?php elseif($item['unlimited'] === 'Yes'): ?>
                        <div class='center' style="margin: 0.5rem 0;">
                            <h4 class='center'>This item has has an unlimited quantity needed.</h4>
                            <span class='unmark-msg'>No need to mark it as purchased!</span>
                        </div>
                    <?php endif; ?>
            <?php endif; ?>
            <?php if($type === 'wisher'): ?>
                <p class='date-added center'><em>Date Added: <?php echo $dateAdded; ?></em></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function renderItemInformation(string $type, array $item, bool $isPurchasedInBuyerView): string {
        $itemName = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');   
        $price = htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8');
        $quantityDisplay = '';
        if ($item['unlimited'] == 'Yes') {
            $quantityDisplay = 'Unlimited';
        } else {
            $qtyTotal = (int)($item['quantity'] ?? 0);
            $qtyPurchased = (int)($item['quantity_purchased'] ?? 0);
            $qtyRemaining = max(0, $qtyTotal - $qtyPurchased);
            // For buyer view show remaining needed; for wisher show total
            $quantityDisplay = $type === 'buyer' ? (string)$qtyRemaining : (string)$qtyTotal;
        }

        if($item['notes'] === null || trim($item['notes']) === '') {
            $item['notes'] = 'None';
        }
        $notes = htmlspecialchars($item['notes'] ?? '', ENT_QUOTES, 'UTF-8');        

        // Priority descriptions with wisher's name
        $priorities = [
            1 => "Must have this",
            2 => "Really want this", 
            3 => "Would be nice to have this",
            4 => "Could always use this"
        ];

        $priorityText = $priorities[$item['priority']] ?? '';

        ob_start(); ?>
        <div class='line'>
            <h3><?= $itemName ?></h3>
            <a href="#" class="see-more-link title-link" style="display: none;">View Full Name</a>
        </div>
        <?php if(!$isPurchasedInBuyerView): ?>
            <div class='line'><h4>Price: $<?php echo $price; ?></h4></div>
            <div class='line'><h4 class='notes-label'>Quantity Needed:</h4> <?php echo $quantityDisplay; ?></div>
            <div class='line'><h4 class='notes-label'>Priority: </h4><span><?php echo $item['priority'] == 4 ? '' : '('.$item['priority'].') '; ?><?php echo $priorityText; ?></span></div>
            <div class='line notes-line'>
                <h4 class='notes-label'>Notes: </h4><span><?php echo $notes; ?></span>
            </div>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }
}
