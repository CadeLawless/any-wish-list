<?php

use App\Controllers\AuthController;
use App\Controllers\FriendController;
use App\Controllers\AdminController;
use App\Controllers\HomeController;
use App\Controllers\WishlistController;
use App\Controllers\ItemController;
use App\Controllers\BuyerController;
use App\Controllers\TestController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\AdminMiddleware;

// Register middleware
Router::middleware('auth', new AuthMiddleware());
Router::middleware('guest', new GuestMiddleware());
Router::middleware('admin', new AdminMiddleware());

// Test route
Router::get('/test', [TestController::class, 'index']);


// Dark theme toggle (must be first to avoid conflicts)
Router::post('/toggle-dark-mode', [AuthController::class, 'toggleDarkMode'])->middleware('auth');

// Guest routes (login, register, etc.)
Router::get('/login', [AuthController::class, 'login'])->middleware('guest');
Router::post('/login', [AuthController::class, 'login'])->middleware('guest');
Router::get('/register', [AuthController::class, 'register'])->middleware('guest');
Router::post('/register', [AuthController::class, 'register'])->middleware('guest');
// Password reset routes - accessible to both logged-in and logged-out users
// (logged-in users may have forgotten their password even though they're still logged in via "remember me")
Router::get('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('guest');
Router::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('guest');
Router::get('/reset-password', [AuthController::class, 'resetPassword']);
Router::post('/reset-password', [AuthController::class, 'resetPassword']);
Router::get('/verify-email', [AuthController::class, 'verifyEmail']);

// AJAX validation endpoints
Router::post('/api/check-username', [AuthController::class, 'checkUsername']);
Router::post('/api/check-email', [AuthController::class, 'checkEmail']);
Router::post('/api/resend-verification', [AuthController::class, 'resendVerification']);

// Logout
Router::get('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Home route
Router::get('/', [HomeController::class, 'index'])->middleware('auth');

// Profile routes (must come before generic /{id} routes)
Router::get('/profile', [AuthController::class, 'profile'])->middleware('auth');
Router::post('/profile', [AuthController::class, 'updateProfile'])->middleware('auth');
Router::post('/profile/upload-profile-picture', [AuthController::class, 'uploadProfilePicture'])->middleware('auth');

// Add Friends routes
Router::get('/friends', [FriendController::class, 'index'])->middleware('auth');
Router::get('/add-friends', [FriendController::class, 'index'])->middleware('auth');
Router::get('/friends/find', [FriendController::class, 'find'])->middleware('auth');
Router::post('/friends/search', [FriendController::class, 'search'])->middleware('auth');
Router::post('/friends/add', [FriendController::class, 'addFriend'])->middleware('auth');
Router::post('/friends/remove', [FriendController::class, 'removeFriend'])->middleware('auth');
Router::post('/friends/decline', [FriendController::class, 'declineInvitation'])->middleware('auth');

// Public User Wish Lists routes
Router::get('/{username}/wishlists', [WishlistController::class, 'publicUserWishlists']);
Router::get('/{username}/wishlists/reload', [WishlistController::class, 'reloadPublicUserWishlists']);

// Admin routes (must come before generic /{id} routes)
Router::get('/admin', [AdminController::class, 'users'])->middleware('admin');
Router::get('/admin/users', [AdminController::class, 'users'])->middleware('admin');
Router::post('/admin/users/paginate', [AdminController::class, 'paginateUsers'])->middleware('admin');
Router::get('/admin/users/edit', [AdminController::class, 'editUser'])->middleware('admin');
Router::post('/admin/users/update', [AdminController::class, 'updateUser'])->middleware('admin');
Router::post('/admin/users/send-password-reset', [AdminController::class, 'sendPasswordReset'])->middleware('admin');
Router::get('/admin/backgrounds', [AdminController::class, 'backgrounds'])->middleware('admin');
Router::post('/admin/backgrounds/paginate', [AdminController::class, 'paginateBackgrounds'])->middleware('admin');
Router::get('/admin/backgrounds/edit', [AdminController::class, 'editBackground'])->middleware('admin');
Router::get('/admin/backgrounds/add', [AdminController::class, 'addBackground'])->middleware('admin');
Router::post('/admin/backgrounds/create', [AdminController::class, 'createBackground'])->middleware('admin');
Router::post('/admin/backgrounds/update', [AdminController::class, 'updateBackground'])->middleware('admin');
Router::get('/admin/backgrounds/delete', [AdminController::class, 'deleteBackground'])->middleware('admin');
Router::get('/admin/gift-wraps', [AdminController::class, 'giftWraps'])->middleware('admin');
Router::post('/admin/gift-wraps/paginate', [AdminController::class, 'paginateGiftWraps'])->middleware('admin');
Router::get('/admin/gift-wraps/edit', [AdminController::class, 'editGiftWrap'])->middleware('admin');
Router::get('/admin/gift-wraps/add', [AdminController::class, 'addGiftWrap'])->middleware('admin');
Router::post('/admin/gift-wraps/create', [AdminController::class, 'createGiftWrap'])->middleware('admin');
Router::post('/admin/gift-wraps/update', [AdminController::class, 'updateGiftWrap'])->middleware('admin');
Router::post('/admin/gift-wraps/add-image', [AdminController::class, 'addGiftWrapImage'])->middleware('admin');
Router::post('/admin/gift-wraps/remove-image', [AdminController::class, 'removeGiftWrapImage'])->middleware('admin');
Router::post('/admin/gift-wraps/reorder-images', [AdminController::class, 'reorderGiftWrapImages'])->middleware('admin');
Router::get('/admin/wish-lists', [AdminController::class, 'wishlists'])->middleware('admin');
Router::post('/admin/wish-lists/paginate', [AdminController::class, 'paginateWishlists'])->middleware('admin');
Router::get('/admin/wish-lists/view', [AdminController::class, 'viewWishlist'])->middleware('admin');
Router::post('/admin/wish-lists/paginate-items', [AdminController::class, 'paginateWishlistItems'])->middleware('admin');


// Wishlist routes (home page shows user's wishlists)
Router::get('/wishlists/create', [WishlistController::class, 'create'])->middleware('auth');
Router::get('/wishlists', [WishlistController::class, 'index'])->middleware('auth');
Router::get('/wishlists/reload', [WishlistController::class, 'reloadWishLists'])->middleware('auth');
Router::get('/wishlists/inactive', [WishlistController::class, 'inactiveWishLists'])->middleware('auth');
Router::get('/wishlists/inactive/reload', [WishlistController::class, 'reloadInactiveWishLists'])->middleware('auth');
Router::post('/wishlists/bulk-action', [WishlistController::class, 'bulkAction'])->middleware('auth');
Router::post('/wishlists/paginate', [WishlistController::class, 'paginateWishLists'])->middleware('auth');
Router::post('/wishlists/inactive/paginate', [WishlistController::class, 'paginateWishLists'])->middleware('auth');
Router::post('/wishlists', [WishlistController::class, 'store'])->middleware('auth');
Router::get('/wishlists/{id}', [WishlistController::class, 'show'])->middleware('auth');
Router::get('/wishlists/{id}/edit', [WishlistController::class, 'edit'])->middleware('auth');
Router::post('/wishlists/{id}', [WishlistController::class, 'update'])->middleware('auth');
Router::delete('/wishlists/{id}', [WishlistController::class, 'delete'])->middleware('auth');
Router::post('/wishlists/{id}/toggle-visibility', [WishlistController::class, 'toggleVisibility'])->middleware('auth');
Router::post('/wishlists/{id}/toggle-complete', [WishlistController::class, 'toggleComplete'])->middleware('auth');

// Wishlist management routes
Router::post('/wishlists/{id}/rename', [WishlistController::class, 'rename'])->middleware('auth');
Router::post('/wishlists/{id}/theme', [WishlistController::class, 'updateTheme'])->middleware('auth');
Router::post('/wishlists/{id}/copy-from', [WishlistController::class, 'copyFrom'])->middleware('auth');
Router::post('/wishlists/{id}/copy-to', [WishlistController::class, 'copyTo'])->middleware('auth');
Router::post('/wishlists/{id}/hide', [WishlistController::class, 'hide'])->middleware('auth');
Router::post('/wishlists/{id}/show', [WishlistController::class, 'showPublic'])->middleware('auth');
Router::post('/wishlists/{id}/complete', [WishlistController::class, 'complete'])->middleware('auth');
Router::post('/wishlists/{id}/reactivate', [WishlistController::class, 'reactivate'])->middleware('auth');
Router::post('/wishlists/{id}/delete', [WishlistController::class, 'delete'])->middleware('auth');

// AJAX endpoints
Router::post('/wishlists/{id}/paginate', [WishlistController::class, 'paginateItems'])->middleware('auth');
Router::post('/wishlists/{id}/filter', [WishlistController::class, 'applyFilter'])->middleware('auth');
Router::post('/wishlists/{id}/items', [WishlistController::class, 'getOtherWishlistItems'])->middleware('auth');

// Item routes - Two-step flow
Router::get('/wishlists/{wishlistId}/item/add', [ItemController::class, 'addStep1'])->middleware('auth');
Router::get('/wishlists/{wishlistId}/item/create', [ItemController::class, 'create'])->middleware('auth');
Router::post('/wishlists/{wishlistId}/item', [ItemController::class, 'store'])->middleware('auth');

// API routes for URL metadata fetching
Router::post('/wishlists/{wishlistId}/api/fetch-url-metadata', [ItemController::class, 'fetchUrlMetadata'])->middleware('auth');
Router::post('/wishlists/{wishlistId}/api/store-fetched-data', [ItemController::class, 'storeFetchedData'])->middleware('auth');
Router::get('/wishlists/{wishlistId}/api/test-json', [ItemController::class, 'testJson'])->middleware('auth');
Router::get('/wishlists/{wishlistId}/item/{id}/edit', [ItemController::class, 'edit'])->middleware('auth');
Router::post('/wishlists/{wishlistId}/fetch-item-form', [ItemController::class, 'fetchItemForm'])->middleware('auth');
Router::post('/wishlists/{wishlistId}/item/{id}', [ItemController::class, 'update'])->middleware('auth');
Router::post('/wishlists/{wishlistId}/item/{id}/change-image', [ItemController::class, 'updateImage'])->middleware('auth');
Router::delete('/wishlists/{wishlistId}/item/{id}', [ItemController::class, 'delete'])->middleware('auth');
Router::post('/wishlists/{wishlistId}/item/{id}/toggle-purchased', [ItemController::class, 'togglePurchased'])->middleware('auth');

// Buyer routes
Router::get('/buyer/{key}', [BuyerController::class, 'show']);
Router::post('/buyer/{key}/filter', [BuyerController::class, 'filterItems']);
Router::post('/buyer/{key}/paginate', [BuyerController::class, 'paginateItems']);
Router::post('/buyer/{key}/purchase/{itemId}', [BuyerController::class, 'purchaseItem']);
Router::post('/buyer/add-item-to-wishlist', [BuyerController::class, 'addItemToUserWishlist'])->middleware('auth');
