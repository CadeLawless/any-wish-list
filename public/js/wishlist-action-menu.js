function scrollToIfNotVisible($elem, padding = 20) {
    const elemTop = $elem.offset().top;
    const elemBottom = elemTop + $elem.outerHeight();

    const navbarHeight = $('.header-container').outerHeight() || 0; 
    const viewportTop = $(window).scrollTop() + navbarHeight;
    const viewportBottom = viewportTop + $(window).height();

    const fullyVisible = elemTop >= viewportTop && elemBottom <= viewportBottom;

    if(!fullyVisible){
        scrollToElement($elem);
    }
}

function scrollToElement($elem, padding = 20) {
    const elemTop = $elem.offset().top;
    const navbarHeight = $('.header-container').outerHeight() || 0; 
    const targetScroll = elemTop - navbarHeight - padding;

    $('html, body').animate({
        scrollTop: targetScroll
    }, 200);
}

$(document).ready(function(){
    $(document).on('click', '.dots-icon', function(e){
        e.stopPropagation();
        var quickMenu = $(this).closest('.three-dots-menu').find('.quick-menu');
        $('.quick-menu').not(quickMenu).removeClass('active-menu'); // Hide other open menus
        quickMenu.toggleClass('active-menu');
        if(quickMenu.hasClass('active-menu')){
            setTimeout(() => scrollToIfNotVisible(quickMenu), 200);
        }
        if ($('.wishlist-header').length) {
            scrollToIfNotVisible($('.wishlist-header'));
        }
    });

    // Hide quick menu when clicking outside
    $(document).on('click', function(e){
        if (!$(e.target).closest('.three-dots-menu').length) {
            $('.quick-menu').removeClass('active-menu');
        }
    });

    $(document).on('click', '.quick-menu-item', function(e){
        e.preventDefault();
        e.stopPropagation();
        var menuItem = $(this);
        menuItem.addClass('disabled');
        var wishlistId = $('#wishlist_show_wishlist_id').val() || menuItem.closest('.wishlist-grid-item').data('wishlist-id');
        //console.log(menuItem.attr('class').split(' ')[1]);
        switch (menuItem.attr('class').split(' ')[1]) {
            case 'toggle-complete':
                var currentComplete = $(this).data('current-complete');

                $.ajax({
                    url: `/wishlists/${wishlistId}/toggle-complete`,
                    type: 'POST',
                    data: {},
                    success: function(response) {
                        if (response.status === 'error') {
                            addAlertMessage(response.message);
                            menuItem.removeClass('disabled');
                            return;
                        }

                        var newComplete = currentComplete === 'Yes' ? 'No' : 'Yes';

                        if ($('.wishlist-header').length) {
                            // If we're on the wishlist detail page, just update the status text without showing an alert
                            $('#wish-list-status').text(currentComplete === 'Yes' ? 'Active' : 'Inactive');
                            
                            // Update the icon and text in the quick menu
                            if(newComplete === 'No'){
                                $('.visibility-container').removeClass('hidden');
                                $('.quick-menu-item.toggle-visibility').removeClass('hidden');
                                fetch('/public/images/site-images/icons/cancel.php')
                                .then(response => response.text())
                                .then(svg => {
                                    menuItem.find('.menu-icon').html(svg);
                                });
                                menuItem.contents().filter(function() { return this.nodeType === 3; }).last().replaceWith(' Deactivate');
                            }else{
                                $('.visibility-container').addClass('hidden');
                                $('.quick-menu-item.toggle-visibility').addClass('hidden');
                                fetch('/public/images/site-images/icons/checkmark.php')
                                .then(response => response.text())
                                .then(svg => {
                                    menuItem.find('.menu-icon').html(svg);
                                });
                                menuItem.contents().filter(function() { return this.nodeType === 3; }).last().replaceWith(' Reactivate');
                            }
                            var alertMessage = currentComplete === 'Yes' ? 'Wish reactivated successfully' : 'Wish list deactivated successfully';
                            addAlertMessage(alertMessage);
                            menuItem.removeClass('disabled').data('current-complete', newComplete);;
                        } else {
                            menuItem.closest('.three-dots-menu').find('.quick-menu').hide();
                            menuItem.closest('.wishlist-grid-item').fadeOut(500, function() {
                                $(this).remove();
                                $(".paginate-arrow.paginate-first").trigger('click');
                                var newTab = currentComplete === 'Yes' ? 'Active' : 'Inactive';
                                var alertMessage = $('.user-result').length && newComplete === 'Yes' ? 'Wish list deactivated successfully' : `Wish list moved to ${newTab}`;
                                addAlertMessage(alertMessage);
                                reloadWishLists();
                            });
                        }
                    },
                    error: function() {
                        addAlertMessage('Failed to update wish list status');
                        menuItem.removeClass('disabled');
                    }
                });
                break;
            case 'toggle-visibility':
                var currentVisibility = $(this).data('current-visibility');

                $.ajax({
                    url: `/wishlists/${wishlistId}/toggle-visibility`,
                    type: 'POST',
                    data: {},
                    success: function(response) {
                        if (response.status === 'error') {
                            addAlertMessage(response.message);
                            menuItem.removeClass('disabled');
                            return;
                        }
                        var newVisibility = currentVisibility === 'Public' ? 'Hidden' : 'Public';

                        if ($('.user-result').length && newVisibility === 'Hidden') {
                            menuItem.closest('.three-dots-menu').find('.quick-menu').hide();
                            menuItem.closest('.wishlist-grid-item').fadeOut(500, function() {
                                $(this).remove();
                                $(".paginate-arrow.paginate-first").trigger('click');
                                var newTab = currentComplete === 'Yes' ? 'Active' : 'Inactive';
                                addAlertMessage(`Wish list is now hidden`);
                                reloadWishLists();
                            });
                            return;
                        }

                        // Update the icon and text in the quick menu
                        if(newVisibility === 'Public'){
                            fetch('/public/images/site-images/icons/hide-view.php')
                            .then(response => response.text())
                            .then(svg => {
                                menuItem.find('.menu-icon').html(svg);
                            });
                            menuItem.contents().filter(function() { return this.nodeType === 3; }).last().replaceWith(' Hide');
                        }else{
                            fetch('/public/images/site-images/icons/view.php')
                            .then(response => response.text())
                            .then(svg => {
                                menuItem.find('.menu-icon').html(svg);
                            });
                            menuItem.contents().filter(function() { return this.nodeType === 3; }).last().replaceWith(' Make Public');
                        }

                        if ($('.wishlist-header').length) {
                            // If we're on the wishlist detail page, just update the visibility status and icon without showing an alert
                            $('#wish-list-visibility').text(newVisibility);
                        } else {
                            // Update the private icon visibility
                            var wishlistItem = menuItem.closest('.wishlist-grid-item');
                            if(newVisibility === 'Public'){
                                wishlistItem.find('.private-wishlist-icon').remove();
                                wishlistItem.find('.items-list').removeClass('private');
                            }else{
                                fetch('/public/images/site-images/icons/hide-view.php')
                                .then(response => response.text())
                                .then(svg => {
                                    wishlistItem.prepend(`<div class='private-wishlist-icon'>${svg}</div>`);
                                });
                                wishlistItem.find('.items-list').addClass('private');
                            }
                        }
                        var alertMessage = newVisibility === 'Public' ? 'Wish list is now public' : 'Wish list is now hidden';
                        addAlertMessage(alertMessage);
                        menuItem.removeClass('disabled').data('current-visibility', newVisibility);
                    },
                    error: function() {
                        addAlertMessage('Failed to update wish list visibility');
                        menuItem.removeClass('disabled');
                    }
                });
                break;

            case 'rename-wishlist':
                let currentWishListNameElement = $('.wishlist-header').length ? $('.wishlist-header .wishlist-title') : menuItem.closest('.wishlist-grid-item').find('.wishlist-name span');
                requestAnimationFrame(() => {
                    lockBodyScroll();
                    $('#rename-popup .popup').addClass('active');
                    $('#rename-popup').removeClass('hidden');
                    $('#rename-input').val(currentWishListNameElement.text().trim()).focus();
                    $('#rename-popup #rename-form').data('wishlist-id', wishlistId);
                    FormValidator.init('#rename-form', {
                        wishlist_name: {
                            required: true,
                            minLength: 1,
                            maxLength: 100
                        }
                    });
                });
                menuItem.removeClass('disabled');
                break;

            case 'delete-wishlist':
                requestAnimationFrame(() => {
                    lockBodyScroll();
                    $('#delete-popup .popup').addClass('active');
                    $('#delete-popup').removeClass('hidden');
                    $('.delete-wishlist-name').text(menuItem.closest('.wishlist-grid-item').find('.wishlist-name').text());
                    $('.delete-wishlist-yes').data('wishlist-id', wishlistId);
                });
                menuItem.removeClass('disabled');
                break;

            case 'change-theme':
                requestAnimationFrame(() => {
                    lockBodyScroll();
                    $('#wish-list-show-theme-popup .popup').addClass('active');
                    $('#wish-list-show-theme-popup').removeClass('hidden');
                    $('#wish-list-show-theme-popup').data('wishlist-id', wishlistId);
                });
                menuItem.removeClass('disabled');
                break;

            case 'copy-from':
                requestAnimationFrame(() => {
                    $('#wish-list-show-copy-from-popup .popup').addClass('active');
                    $('#wish-list-show-copy-from-popup').removeClass('hidden');
                    $('#wish-list-show-copy-from-popup').data('wishlist-id', wishlistId);
                });
                menuItem.removeClass('disabled');
                break;

            case 'copy-to':
                requestAnimationFrame(() => {
                    $('#wish-list-show-copy-to-popup .popup').addClass('active');
                    $('#wish-list-show-copy-to-popup').removeClass('hidden');
                    $('#wish-list-show-copy-to-popup').data('wishlist-id', wishlistId);
                });
                menuItem.removeClass('disabled');
                break;
        }

        menuItem.closest('.quick-menu').removeClass('active-menu');
    });

    $(document).on('click', '#rename-popup .close-button', function(e){
        e.preventDefault();
        $('#rename-popup').addClass("hidden").find('.popup').removeClass('active');
        unlockBodyScroll();
    });
    
    $(window).on('click', function(e){
        if (!$(e.target).closest('#rename-popup .popup').length && !$(e.target).closest('.quick-menu-item.rename-wishlist').length) {
            $('#rename-popup').addClass("hidden").find('.popup').removeClass('active');
            unlockBodyScroll();
        }
        if (!$(e.target).closest('#delete-popup .popup').length && !$(e.target).closest('.quick-menu-item.delete-wishlist').length) {
            $('#delete-popup').addClass("hidden").find('.popup').removeClass('active');
            unlockBodyScroll();
        }
    });

    $(document).on('click', '#delete-popup .close-button, #delete-popup .no-button', function(e){
        e.preventDefault();
        $('#delete-popup').addClass("hidden").find('.popup').removeClass('active');
        unlockBodyScroll();
    });

    $(document).on('submit', '#rename-form', function(e){
        e.preventDefault();
        var newName = $('#rename-input').val().trim();
        if(newName === ''){
            addAlertMessage('Wish list name cannot be empty');
            return;
        }
        var wishlistId = $(this).data('wishlist-id');
        var menuItem = $(`.wishlist-grid-item[data-wishlist-id='${wishlistId}']`).find('.quick-menu-item.rename-wishlist');
        let currentWishListNameElement = $('.wishlist-header').length ? $('.wishlist-header .wishlist-title') : menuItem.closest('.wishlist-grid-item').find('.wishlist-name span');
        $("#rename-popup .popup-content").find('.submit-error').remove();
        $.ajax({
            url: `/wishlists/${wishlistId}/rename`,
            type: 'POST',
            data: { name: newName },
            success: function(response) {
                if (response.status === 'error') {
                    $("#rename-popup .popup-content h2").after(`<div class='submit-error'>${response.errorHtml}</div>`);
                    return;
                }
                currentWishListNameElement.text(newName);
                if ($('.wishlist-header').length) {
                    $(document).attr('title', newName);
                }
                addAlertMessage('Wish list renamed successfully');
                menuItem.removeClass('disabled');
                $('#rename-popup').addClass('hidden').find('.popup').removeClass('active');
                unlockBodyScroll();
            },
            error: function() {
                $("#rename-popup .popup-content h2").after(`<div class='submit-error'>Failed to rename wish list</div>`);
                menuItem.removeClass('disabled');
            }
        });
    });

    $(document).on('click', '.delete-wishlist-yes', function(e){
        e.preventDefault();
        var wishlistId = $(this).data('wishlist-id');
        var menuItem = $(`.wishlist-grid-item[data-wishlist-id='${wishlistId}']`).find('.quick-menu-item.delete-wishlist');
        $("#delete-popup .popup-content").find('.submit-error').remove();
        $.ajax({
            url: `/wishlists/${wishlistId}/delete`,
            type: 'POST',
            data: {},
            success: function(response) {
                if (response.status === 'error') {
                    $("#delete-popup .popup-content h2").after(`<div class='submit-error'>${response.errorHtml}</div>`);
                    return;
                }
                if ($('.wishlist-header').length) {
                    // set cookie for alert message to show on wishlist index page after redirect
                    document.cookie = "alertMessage=Wish list deleted successfully; path=/wishlists; max-age=5";
                    window.location.href = '/wishlists';
                    return;
                }
                $("#delete-popup").addClass("hidden").find('.popup').removeClass('active');
                menuItem.closest('.wishlist-grid-item').fadeOut(500, function() {
                    $(this).remove();
                    addAlertMessage('Wish list deleted successfully');
                    reloadWishLists();
                });
            },
            error: function() {
                $("#delete-popup .popup-content h2").after(`<div class='submit-error'>Failed to delete wish list</div>`);
                menuItem.removeClass('disabled');
            }
        });
    });
});
