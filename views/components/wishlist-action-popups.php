<?php
use App\Services\PopupManager;

// Rename popup
echo PopupManager::generatePopupContainer([
    'id' => 'rename-popup',
    'classes' => 'hidden',
    "content" => "
        <h2 class='no-margin-top'>Rename Wish List</h2>
        <form id='rename-form'>
            <input type='text' id='rename-input' name='wishlist_name' class='input-field' required value='' placeholder='Enter new wish list name' />
            <div class='center'>
                <input type='submit' id='rename-confirm-button' class='button primary' value='Rename' />
            </div>
        </form>"
]);

// Delete confirmation popup
echo PopupManager::generatePopupContainer([
    'id' => 'delete-popup',
    'classes' => 'hidden',
    "content" => "
        <h2 class='no-margin-top'>Delete Wish List</h2>
        <strong>Are you sure you want to delete this wish list?</strong>
        <p class='delete-wishlist-name'></p>
        <div style='margin: 1rem 0;' class='center delete-popup-buttons'>
            <a class='button secondary no-button' href='#'>No</a>
            <a class='button primary delete-wishlist-yes'>Yes</a>
        </div>"
]);
?>