<?php

namespace App\Services;

class SessionManager
{
    /**
     * Initialize session if not already started
     */
    public static function startSession(): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * Set up session for successful registration
     */
    public static function setupRegistrationSession(array $userData): void
    {
        self::startSession();

        $_SESSION['wishlist_logged_in'] = true;
        $_SESSION['username'] = $userData['username'];
        $_SESSION['account_created'] = true;
    }

    /**
     * Update dark mode preference in session
     */
    public static function updateDarkMode(bool $isDark): void
    {
        self::startSession();
        $_SESSION['dark'] = $isDark;
    }

    /**
     * Store buyer sort preferences in session
     */
    public static function storeBuyerSortPreferences(string $sortPriority, string $sortPrice): void
    {
        self::startSession();
        $_SESSION['buyer_sort_priority'] = $sortPriority;
        $_SESSION['buyer_sort_price'] = $sortPrice;
    }

    /**
     * Get buyer sort preferences from session
     */
    public static function getBuyerSortPreferences(): array
    {
        self::startSession();
        return [
            'sort_priority' => $_SESSION['buyer_sort_priority'] ?? '',
            'sort_price' => $_SESSION['buyer_sort_price'] ?? ''
        ];
    }

    /**
     * Store wisher sort preferences in session
     */
    public static function storeWisherSortPreferences(string $sortPriority, string $sortPrice): void
    {
        self::startSession();
        $_SESSION['wisher_sort_priority'] = $sortPriority;
        $_SESSION['wisher_sort_price'] = $sortPrice;
    }

    /**
     * Get wisher sort preferences from session
     */
    public static function getWisherSortPreferences(): array
    {
        self::startSession();
        return [
            'sort_priority' => $_SESSION['wisher_sort_priority'] ?? '',
            'sort_price' => $_SESSION['wisher_sort_price'] ?? ''
        ];
    }

    /**
     * Clear all session data
     */
    public static function clearSession(): void
    {
        self::startSession();
        session_destroy();
    }

    /**
     * Set wishlist context for navigation
     */
    public static function setWishlistContext(int $wishlistId, int $pageno = 1): void
    {
        self::startSession();
        $_SESSION['wisher_wishlist_id'] = $wishlistId;
        $_SESSION['home'] = "/{$wishlistId}?pageno={$pageno}#paginate-top";
        $_SESSION['type'] = 'wisher';
    }

    /**
     * Set flash message for wishlist operations
     */
    public static function setWishlistFlash(string $message, string $type = 'success'): void
    {
        self::startSession();
        $_SESSION["wishlist_{$type}"] = $message;
    }

    /**
     * Get and clear flash messages
     */
    public static function getFlashMessages(): array
    {
        self::startSession();
        $messages = [];
        
        // Check for new format (flash.success, flash.error)
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            if (is_array($flash)) {
                $messages = array_merge($messages, $flash);
            }
            unset($_SESSION['flash']);
        }
        
        // Check for old format (success, error) for backward compatibility
        $flashKeys = ['success', 'error', 'wishlist_hidden', 'wishlist_public', 'wishlist_complete', 'wishlist_reactivated'];
        
        foreach ($flashKeys as $key) {
            if (isset($_SESSION[$key])) {
                $messages[$key] = $_SESSION[$key];
                unset($_SESSION[$key]);
            }
        }
        
        return $messages;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        self::startSession();
        return isset($_SESSION['wishlist_logged_in']) && $_SESSION['wishlist_logged_in'] === true;
    }

    /**
     * Get current username from session
     */
    public static function getUsername(): ?string
    {
        self::startSession();
        return $_SESSION['username'] ?? null;
    }

    /**
     * Set user login session
     */
    public static function setLoginSession(string $username): void
    {
        self::startSession();
        $_SESSION['wishlist_logged_in'] = true;
        $_SESSION['username'] = $username;
    }

    /**
     * Logout user and clear session
     */
    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Get current authenticated user data from session
     */
    public static function getAuthUser(): ?array
    {
        self::startSession();
        
        if (isset($_SESSION['wishlist_logged_in']) && $_SESSION['wishlist_logged_in']) {
            // Get full user data from database
            $username = $_SESSION['username'] ?? null;
            if ($username) {
                $user = \App\Models\User::findByUsernameOrEmail($username);
                if ($user) {
                    return $user;
                }
            }
            
            // Fallback to session data if database lookup fails
            return [
                'id' => $_SESSION['user_id'] ?? null,
                'username' => $_SESSION['username'] ?? null,
                'name' => $_SESSION['name'] ?? null,
                'email' => $_SESSION['user_email'] ?? null,
                'admin' => $_SESSION['admin'] ?? false,
                'dark' => $_SESSION['dark'] ?? false
            ];
        }
        
        return null;
    }

    /**
     * Set authenticated user session data
     */
    public static function setAuthUser(array $user): void
    {
        self::startSession();

        $_SESSION['wishlist_logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['admin'] = $user['role'] === 'Admin';
        $_SESSION['dark'] = $user['dark'] === 'Yes';
    }

    /**
     * Get a session value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::startSession();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value
     */
    public static function set(string $key, $value): void
    {
        self::startSession();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a session key exists
     */
    public static function has(string $key): bool
    {
        self::startSession();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value
     */
    public static function remove(string $key): void
    {
        self::startSession();
        unset($_SESSION[$key]);
    }

    /**
     * Store fetched item data in session
     */
    public static function setFetchedItemData(array $data): void
    {
        self::startSession();
        $data['timestamp'] = time();
        $_SESSION['fetched_item_data'] = $data;
    }

    /**
     * Get fetched item data from session (with expiry check)
     */
    public static function getFetchedItemData(): ?array
    {
        self::startSession();
        
        $fetchedData = $_SESSION['fetched_item_data'] ?? null;
        if ($fetchedData && isset($fetchedData['timestamp'])) {
            $age = time() - $fetchedData['timestamp'];
            if ($age > 600) { // 10 minutes
                unset($_SESSION['fetched_item_data']);
                return null;
            }
        }
        
        return $fetchedData;
    }

    /**
     * Clear fetched item data from session
     */
    public static function clearFetchedItemData(): void
    {
        self::startSession();
        unset($_SESSION['fetched_item_data']);
    }

    public static function clearRememberCookie(): void
    {
        setcookie(
            'wishlist_remember',
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }
}
