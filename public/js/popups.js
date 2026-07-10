/**
 * Comprehensive Popup System
 * Handles all popup functionality with clean separation from views
 */

let scrollPosition = 0;

function lockBodyScroll() {
    $('body').addClass('fixed');
    if (document.documentElement.scrollHeight > window.innerHeight) {
        $('html').addClass('modal-scrollbar-fix');
    }
}

function unlockBodyScroll() {
    $('body').removeClass('fixed');
    $('html').removeClass('modal-scrollbar-fix');
}

$(document).ready(function() {
    
    // =============================================================================
    // POPUP OPENING LOGIC
    // =============================================================================
    
    // Handle popup button clicks
    $(document.body).on("click", ".popup-button:not(.disabled)", function(e) {
        e.preventDefault();
        
        const button = this;
        
        // Add fixed class to body to prevent scrolling
        lockBodyScroll();
        
        // Find and show the popup
        let popup, container;
        
        if (button.tagName === "INPUT") {
            // For input buttons (less common)
            popup = button.nextElementSibling.nextElementSibling.firstElementChild;
            container = button.nextElementSibling.nextElementSibling;
        } else {
            // For regular anchor buttons (most common)
            popup = button.nextElementSibling.firstElementChild;
            container = button.nextElementSibling;
        }
        
        // Show the popup
        if (popup && container) {
            popup.classList.add("active");
            container.classList.remove("hidden");
        }
        
        // Trigger custom event
        $(container).trigger('popup:opened', [button]);
    });
    
    // Handle image popup button clicks
    $(document.body).on("click", ".image-popup-button", function(e) {
        e.preventDefault();
        
        const button = this;
        const imageSrc = $(button).find(".item-image").attr("src");
        const popup = $(".image-popup-container").first();
        const itemId = $(this).closest('.item-container').data('item-id');
        $('#newItemImage').data('item-id', itemId);
        
        if (popup.length && imageSrc) {
            popup.removeClass("hidden");
            popup.find(".image-popup").addClass("active");
            popup.find(".popup-image").attr("src", imageSrc);
            lockBodyScroll();
            
            // Trigger custom event
            popup.trigger('popup:opened', [button]);
        }
    });
    
    // =============================================================================
    // POPUP CLOSING LOGIC
    // =============================================================================
    
    // Handle close button clicks
    $(document.body).on("click", ".close-container:not(.options-close)", function(e) {
        e.preventDefault();
        
        const closeButton = this;
        const $popupContainer = $(closeButton).closest(".popup-container");
        
        // Remove fixed class only if not a first/second popup (special popup types)
        if (!$popupContainer.hasClass("first") && !$popupContainer.hasClass("second")) {
            unlockBodyScroll();
        }
        
        // Hide the popup
        hidePopup($popupContainer);
        
        // Clean up popup animations - only target popups within this specific container
        $popupContainer.find(".popup:not(.first, .second)").each(function() {
            const popup = this;
            // Only remove animation classes, not the hidden class
            popup.classList.remove("slide-in-left", "slide-out-left", "slide-in-right", "slide-out-right");
            if (popup.className.includes("yes")) {
                popup.classList.add("hidden");
            }
        });
        
        // Trigger custom event
        $popupContainer.trigger('popup:closed');
    });
    
    // Handle "No" button clicks
    $(document.body).on("click", ".no-button", function(e) {
        e.preventDefault();
        
        const noButton = this;
        const $popupContainer = $(noButton).closest(".popup-container");
        
        // Remove fixed class only if not a first/second popup
        if (!$popupContainer.hasClass("first") && !$popupContainer.hasClass("second")) {
            unlockBodyScroll();
        }
        
        // Hide the popup
        hidePopup($popupContainer);
        
        // Handle double-no buttons (for nested popups)
        if (noButton.classList.contains("double-no")) {
            const $prevPopup = $popupContainer.prev();
            if ($prevPopup.length && $prevPopup.hasClass("popup-container")) {
                hidePopup($prevPopup);
            }
        }
        
        // Trigger custom event
        $popupContainer.trigger('popup:cancelled');
    });
    
    // Handle window clicks (click outside to close)
    // Remove any existing handler first to prevent duplicates
    $(window).off("click.popups");
    $(window).on("click.popups", function(e) {
        const openPopups = $(".popup-container:not(.hidden)");
        const openDropdowns = $(".image-dropdown .options:not(.hidden)");
        
        if (openPopups.length > 0) {
            const isPopupButton = e.target.classList.contains("popup-button") || 
                                 e.target.id === "change-profile-picture" ||
                                 e.target.classList.contains("image-popup-button") ||
                                 $(e.target).closest(".popup-button").length > 0;
            const isInsidePopup = e.target.closest(".popup-container") !== null && 
                                 !e.target.classList.contains("popup-container");
            
            // Special handling: if second popup (mobile/desktop) is open, check if click is outside it
            const popupSecond = $(".popup-container.second:not(.hidden)");
            if (popupSecond.length > 0 && !isPopupButton) {
                // Check if click is inside the second popup's actual content area
                let clickedInsideSecondPopup = false;
                popupSecond.each(function() {
                    const $secondPopup = $(this);
                    const $popup = $secondPopup.find(".popup");
                    if ($popup.length && $popup[0].contains(e.target)) {
                        clickedInsideSecondPopup = true;
                        return false; // Break loop
                    }
                });
                
                // If clicking outside second popup (even if inside first popup), close only second popup
                if (!clickedInsideSecondPopup) {
                    const isCloseButton = $(e.target).closest(".close-container").length > 0;
                    if (!isCloseButton) {
                        if (openDropdowns.length > 0) {
                            // Close dropdowns first
                            openDropdowns.addClass("hidden");
                            openDropdowns.first().closest(".popup-content").removeClass("fixed static");
                        } else {
                            // Close only the second popup, keep first popup open
                            hidePopup(popupSecond[0]);
                        }
                        return; // Stop here, don't process further
                    }
                }
            }
            
            // If clicking outside popup and not on a popup button
            if (!isPopupButton && !isInsidePopup) {
                if (openDropdowns.length > 0) {
                    // Close dropdowns first
                    openDropdowns.addClass("hidden");
                    openDropdowns.first().closest(".popup-content").removeClass("fixed static");
                } else {
                    // Close popups in order: second, first, then any others
                    const popupFirst = $(".popup-container.first:not(.hidden)");
                    
                    if (popupFirst.length > 0) {
                        hidePopup(popupFirst[0]);
                        // Remove fixed class only if this was the last popup
                        if ($(".popup-container:not(.hidden)").length === 0) {
                            unlockBodyScroll();
                        }
                    } else {
                        // Close all remaining popups
                        openPopups.each(function() {
                            hidePopup(this);
                        });
                        unlockBodyScroll();
                    }
                }
            }
        }
    });
    
    // Handle keyboard events
    $(document).on("keydown", function(e) {
        // Close popups with Escape key
        if (e.key === 'Escape' || e.keyCode === 27) {
            const openPopups = $(".popup-container:not(.hidden)");
            if (openPopups.length > 0) {
                // Close the topmost popup
                const topPopup = openPopups.last()[0];
                hidePopup(topPopup);
                
                // If no more popups, remove fixed class
                if ($(".popup-container:not(.hidden)").length === 0) {
                    unlockBodyScroll();
                }
            }
        }
    });
    
    // =============================================================================
    // UTILITY FUNCTIONS
    // =============================================================================
    
    /**
     * Hide a popup element
     * @param {Element|jQuery} popupElement - The popup element to hide
     */
    function hidePopup(popupElement) {
        const popup = $(popupElement);
        if (popup.length) {
            popup.addClass("hidden");
            popup.find(".popup").removeClass("active");
        }
    }
    
    /**
     * Show a popup element
     * @param {string} popupId - The ID of the popup to show
     */
    function showPopup(popupId) {
        const popup = $("#" + popupId);
        if (popup.length) {
            popup.removeClass("hidden");
            popup.find(".popup").addClass("active");
            lockBodyScroll();
            
            // Trigger custom event
            popup.trigger('popup:opened');
        }
    }
    
    /**
     * Close all open popups
     */
    function closeAllPopups() {
        $(".popup-container:not(.hidden)").each(function() {
            hidePopup(this);
        });
        unlockBodyScroll();
    }
    
    // =============================================================================
    // PUBLIC API
    // =============================================================================
    
    // Expose utility functions globally
    window.PopupUtils = {
        show: showPopup,
        hide: hidePopup,
        closeAll: closeAllPopups
    };
    
    // =============================================================================
    // CUSTOM EVENT HANDLERS
    // =============================================================================
    
    // Example: Log popup events for debugging (disabled to prevent console spam)
    // $(document).on('popup:opened popup:closed popup:cancelled', function(e, button) {
    // });
    
});
