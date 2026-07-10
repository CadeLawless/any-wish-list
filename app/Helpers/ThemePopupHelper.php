<?php

namespace App\Helpers;

use App\Services\ThemeService;

class ThemePopupHelper
{
    public static function renderThemePopup(string $type, bool $swap = false): string
    {
        $typeTitle = ucfirst($type);
        $backgrounds = ThemeService::getBackgroundThemes($typeTitle);
        $giftWraps = ThemeService::getGiftWrapThemes($typeTitle);
        
        ob_start();
        ?>
        <div id="wish-list-show-theme-popup" class='popup-container <?php echo $type; ?> hidden'>
            <div class='popup fullscreen theme-popup-container'>
                <img class='background-theme desktop-background hidden' src="" />
                <img class='background-theme mobile-background hidden' src="" />
                <div class='close-container'>
                    <a href='#' class='close-button'>
                        <?php require(__DIR__ . "/../../public/images/site-images/menu-close.php"); ?>
                    </a>
                </div>
                <div class="theme-content">
                    <h2 class="theme-header background-header" style="margin-top: 0;">Choose a Background</h2>
                    <div class='popup-content choose-theme-popup'>
                        <div class="theme-list">
                            <?php
                            // Default background option
                            echo "
                            <a class='theme popup-button' href='#'>
                                <div class='theme-image desktop-theme-image default-background'></div>
                                <div class='theme-image mobile-theme-image default-background'></div>
                                <div class='hover-title'>Default</div>
                            </a>
                            <div class='popup-container second center-items individual-theme-popup hidden'>
                                <div class='popup'>
                                    <div class='close-container'>
                                        <a href='#' class='close-button'>";
                                        require(__DIR__ . "/../../public/images/site-images/menu-close.php");
                                        echo "</a>
                                    </div>
                                    <div class='popup-content'>
                                        <h2 style='margin-top: 0'>Default</h2>
                                        <div class='theme-nav'>
                                            <a href='#' class='desktop active'>Desktop</a>
                                            <a href='#' class='mobile'>Mobile</a>
                                        </div>
                                        <div class='theme-picture'>
                                            <div style='background-color: var(--background); height: 300px; width: 100%; border: 1px solid var(--text);' class='desktop'></div>
                                            <div style='background-color: var(--background); height: 300px; width: 100%; max-width: 200px; border: 1px solid var(--text);' class='mobile hidden'></div>
                                        </div>
                                        <p class='center'><a class='select-theme button primary' data-default-gift-wrap='0' data-background-id='0' data-background-image='default' href='#'>Select Background</a></p>
                                    </div>
                                </div>
                            </div>";
                            
                            // Background themes
                            foreach ($backgrounds as $row) {
                                $backgroundId = $row["theme_id"];
                                $backgroundName = $row["theme_name"];
                                $backgroundImage = $row["theme_image"];
                                $defaultGiftWrap = $row["default_gift_wrap"];
                                echo "
                                <a class='theme popup-button' href='#'>
                                    <img src='/public/images/site-images/themes/desktop-thumbnails/$backgroundImage?t=" . time() . "' class='theme-image desktop-theme-image' alt='$backgroundName theme' />
                                    <img src='/public/images/site-images/themes/mobile-thumbnails/$backgroundImage?t=" . time() . "' class='theme-image mobile-theme-image' alt='$backgroundName theme' />
                                    <div class='hover-title'>$backgroundName</div>
                                </a>
                                <div class='popup-container second center-items individual-theme-popup hidden'>
                                    <div class='popup'>
                                        <div class='close-container'>
                                            <a href='#' class='close-button'>";
                                            require(__DIR__ . "/../../public/images/site-images/menu-close.php");
                                            echo "</a>
                                        </div>
                                        <div class='popup-content'>
                                            <h2 style='margin-top: 0'>$backgroundName</h2>
                                            <div class='theme-nav'>
                                                <a href='#' class='desktop active'>Desktop</a>
                                                <a href='#' class='mobile'>Mobile</a>
                                            </div>
                                            <div class='theme-picture'>
                                                <img class='desktop' src='/public/images/site-images/themes/desktop-thumbnails/$backgroundImage?t=" . time() . "' alt='$backgroundName desktop' />
                                                <img class='mobile hidden' src='/public/images/site-images/themes/mobile-thumbnails/$backgroundImage?t=" . time() . "' alt='$backgroundName desktop' />
                                            </div>
                                            <p class='center'><a class='select-theme button primary' data-default-gift-wrap='$defaultGiftWrap' data-background-id='$backgroundId' data-background-image='$backgroundImage' href='#'>Select Background</a></p>
                                        </div>
                                    </div>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="gift-wrap-content hidden">
                    <p style="padding-left: calc(5% - 10px); text-align: left;"><a class="button accent back-to" href="#">Back to Backgrounds</a></p>
                    <h2 class="theme-header gift-wrap-header" style="margin-top: 0;">
                        <div>Choose a Gift Wrap<?php
                        if(!$swap){
                            echo "<a class='button primary continue-button' href='#'>Continue</a>";
                        }else{
                            // Get the current wishlist ID from the URL or session
                            $wishlistId = $_GET['id'] ?? \App\Services\SessionManager::get('wisher_wishlist_id', '');
                            echo "
                            <form action='/wishlists/$wishlistId/theme' method='POST' style='display: inline;'>
                                <input type='hidden' id='theme_background_id' name='theme_background_id' value='' />
                                <input type='hidden' id='theme_gift_wrap_id' name='theme_gift_wrap_id' value='' />
                                <input type='submit' class='button primary continue-button' value='Confirm Change' name='theme_submit_button' />
                            </form>";
                        } ?>
                        </div>
                    </h2>
                    <div class='popup-content no-margin-top'>
                        <div class='theme-header' style="margin: 0 0 15px; width: auto;">Gift wraps will be displayed over any purchased item images. You will never see the gift wraps. Only the people who purchase items off your wish list will see them.</div>
                        <div class="theme-dropdown background-dropdown">
                            <strong>Background:</strong>
                            <div class="image-dropdown background" style="margin-bottom: 10px;">
                                <div class="selected-option">
                                    <span class="value"></span>
                                    <span class="preview-image desktop-image"></span>
                                    <span class="preview-image mobile-image"></span>
                                    <span class="popup-plus"><?php require(__DIR__ . "/../../public/images/site-images/icons/plus.php"); ?></span>
                                </div>
                                <div class="options hidden">
                                    <div class='close-container options-close'>
                                        <a href='#' class='close-button'>
                                        <?php require(__DIR__ . "/../../public/images/site-images/menu-close.php"); ?>
                                        </a>
                                    </div>
                                    <div class="options-content">
                                    <?php
                                    // Default background option
                                    echo "
                                    <div class='option default-background-option'>
                                        <span class='value' data-background-image='default' data-background-id='0' data-default-gift-wrap='";
                                        echo $type == "birthday" ? "28" : "60";
                                        echo "'>Default</span>
                                        <span class='preview-image desktop-background-image'><span class='default-background'></span></span>
                                        <span class='preview-image mobile-background-image'><span class='default-background'></span></span>
                                    </div>";
                                    
                                    // Background options
                                    foreach ($backgrounds as $row) {
                                        $backgroundId = $row["theme_id"];
                                        $backgroundName = $row["theme_name"];
                                        $backgroundImage = $row["theme_image"];
                                        $defaultGiftWrap = $row["default_gift_wrap"];
                                        echo "
                                        <div class='option'>
                                            <span class='value' data-background-image='$backgroundImage' data-background-id='$backgroundId' data-default-gift-wrap='$defaultGiftWrap'>$backgroundName</span>
                                            <span class='preview-image desktop-background-image'><img src='/public/images/site-images/themes/desktop-thumbnails/$backgroundImage?t=" . time() . "' /></span>
                                            <span class='preview-image mobile-background-image'><img src='/public/images/site-images/themes/mobile-thumbnails/$backgroundImage?t=" . time() . "' /></span>
                                        </div>";
                                    }
                                    ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="theme-dropdown gift-wrap-dropdown">
                            <strong>Gift Wrap:</strong>
                            <div class="image-dropdown gift-wrap">
                                <div class="selected-option">
                                    <span class="value"></span>
                                    <span class="preview-image"></span>
                                    <span class="popup-plus"><?php require(__DIR__ . "/../../public/images/site-images/icons/plus.php"); ?></span>
                                </div>
                                <div class="options hidden">
                                    <div class='close-container options-close'>
                                        <a href='#' class='close-button'>
                                        <?php require(__DIR__ . "/../../public/images/site-images/menu-close.php"); ?>
                                        </a>
                                    </div>
                                    <div class="options-content">
                                    <?php
                                    foreach ($giftWraps as $row) {
                                        $wrapId = $row["theme_id"];
                                        $wrapName = $row["theme_name"];
                                        $wrapImage = $row["theme_image"];
                                        $numberOfWraps = ThemeService::getGiftWrapFileCount($wrapImage);
                                        echo "
                                        <div class='option'>
                                            <span class='value' data-wrap-image='$wrapImage' data-wrap-id='$wrapId' data-number-of-files='$numberOfWraps'>$wrapName</span>";
                                        for($i=1; $i<=$numberOfWraps; $i++) {
                                            if($i <= 6){
                                                echo "<span class='preview-image'><img src='/public/images/site-images/themes/gift-wraps/$wrapImage/$i.png?t=" . time() . "' /></span>";
                                            }
                                        }
                                        echo "
                                            <span class='recommended'>Recommended</span>
                                        </div>";
                                    }
                                    ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        // Template preview items with gift wrap functionality
                        // Use the same default gift wrap IDs as the original system
                        $defaultWrapImage = $type == "birthday" ? "black-and-white-foggy" : "christmas-black-and-white"; // Default gift wrap IDs
                        
                        // Sample items data
                        $sampleItems = [
                            ["name" => "Wireless Headphones", "description" => "This is how your items will look with the selected theme."],
                            ["name" => "Coffee Maker", "description" => "Gift wraps will appear on purchased items."],
                            ["name" => "Yoga Mat", "description" => "Different gift wrap variations will cycle through."],
                            ["name" => "Bluetooth Speaker", "description" => "Each item shows a unique wrap design."],
                            ["name" => "Reading Lamp", "description" => "Preview how your wishlist will look to buyers."],
                            ["name" => "Kitchen Knife Set", "description" => "Themes create a cohesive gift experience."],
                            ["name" => "Plant Pot Set", "description" => "Choose colors that match your personality."],
                            ["name" => "Phone Case", "description" => "Perfect for any occasion or holiday."],
                            ["name" => "Desk Organizer", "description" => "Make your wishlist stand out beautifully."],
                            ["name" => "Tea Set", "description" => "Gift wraps add excitement to every purchase."],
                            ["name" => "Throw Pillow", "description" => "Create memorable gift-giving moments."],
                            ["name" => "Book Collection", "description" => "Your recipients will love the presentation."]
                        ];
                        
                        echo "<div class='items-list'>";
                        
                        foreach($sampleItems as $index => $item) {
                            $giftWrapNumber = ($index % 6) + 1; // Cycle through 8 gift wrap variations
                            
                            echo "<div class='item-container'>
                                <img src='/public/images/site-images/themes/gift-wraps/$defaultWrapImage/$giftWrapNumber.png?t=" . time() . "' class='gift-wrap' alt='gift wrap'>
                                <div class='item-description'>
                                    <div class='line'><h3>" . htmlspecialchars($item["name"]) . "</h3></div>
                                    <div class='line'><p>" . htmlspecialchars($item["description"]) . "</p></div>
                                </div>
                            </div>";
                        }
                        
                        echo "</div>";
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
