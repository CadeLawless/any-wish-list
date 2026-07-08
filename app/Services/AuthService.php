<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use App\Services\SessionManager;

class AuthService
{

    public function login(string $username, string $password, bool $remember = false): bool
    {
        $user = User::findByUsernameOrEmail($username);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // Set session variables using SessionManager
        \App\Services\SessionManager::setAuthUser($user);
        
        // Handle remember me database update
        if ($remember) {

            $selector = bin2hex(random_bytes(16));
            $token = bin2hex(random_bytes(32));

            UserSession::createSession([
                'username' => $user['username'],
                'selector' => $selector,
                'token_hash' => hash(
                    'sha256',
                    $token
                ),
                'expires_at' => date(
                    'Y-m-d H:i:s',
                    strtotime('+1 year')
                ),
                'created_at' => date(
                    'Y-m-d H:i:s'
                ),
                'ip_address' =>
                    $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' =>
                    $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);


            setcookie(
                'wishlist_remember',
                $selector . ':' . $token,
                [
                    'expires' => time() + (86400 * 365),
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
        }

        return true;
    }

    public function logout(): void
    {
        SessionManager::startSession();

        if (isset($_COOKIE['wishlist_remember'])) {

            [$selector] = explode(':', $_COOKIE['wishlist_remember'], 2);

            UserSession::deleteBySelector($selector);

            SessionManager::clearRememberCookie();
        }

        SessionManager::logout();
    }

    public function register(array $data): bool
    {
        try {
            // Hash the password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Generate email verification key
            $emailKey = bin2hex(random_bytes(25)); // 50 character string
            $emailKeyExpiration = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $sessionExpiration = date('Y-m-d H:i:s', strtotime('+1 year'));
            
            // Prepare data for database insertion
            $userData = [
                'name' => $data['name'],
                'unverified_email' => $data['email'],
                'username' => $data['username'],
                'password' => $hashedPassword,
                'session' => session_id(),
                'session_expiration' => $sessionExpiration,
                'email_key' => $emailKey,
                'email_key_expiration' => $emailKeyExpiration
            ];
            
            // Create user using static method
            $userId = User::create($userData);
            return $userId > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkSession(): ?array
    {
        // Check if already logged in via session using SessionManager
        if (\App\Services\SessionManager::isLoggedIn()) {
            return User::findByUsernameOrEmail(\App\Services\SessionManager::getUsername());
        }

        // Check remember me cookie
        if (isset($_COOKIE['wishlist_remember'])) {

            $cookie = $_COOKIE['wishlist_remember'];

            $parts = explode(':', $cookie, 2);

            if (count($parts) === 2) {

                $selector = $parts[0];
                $token = $parts[1];

                $session = UserSession::findBySelector($selector);

                if ($session) {

                    $valid = hash_equals(
                        $session['token_hash'],
                        hash('sha256', $token)
                    );

                    if ($valid) {

                        $user = User::whereEqual(
                            'username',
                            $session['username']
                        );

                        if ($user) {

                            \App\Services\SessionManager::setAuthUser($user);

                            return $user;
                        }
                    }
                }
            }
        }

        // Check for old session cookie
        if (isset($_COOKIE['wishlist_session_id'])) {

            $oldSessionId = $_COOKIE['wishlist_session_id'];

            $user = User::findBySessionId($oldSessionId);

            if ($user) {

                // Restore login
                SessionManager::setAuthUser($user);


                // Create new remember token
                $selector = bin2hex(random_bytes(16));
                $token = bin2hex(random_bytes(32));


                UserSession::createSession([
                    'username' => $user['username'],
                    'selector' => $selector,
                    'token_hash' => hash('sha256', $token),
                    'expires_at' => date(
                        'Y-m-d H:i:s',
                        strtotime('+1 year')
                    ),
                    'created_at' => date('Y-m-d H:i:s'),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);


                setcookie(
                    'wishlist_remember',
                    $selector . ':' . $token,
                    [
                        'expires' => time() + (86400 * 365),
                        'path' => '/',
                        'secure' => isset($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );


                // Remove old cookie
                setcookie(
                    'wishlist_session_id',
                    '',
                    time() - 3600,
                    '/'
                );


                // Remove old database values
                User::updateSession(
                    $user['id'],
                    null,
                    null
                );


                return $user;
            }
        }

        return null;
    }

    public function getCurrentUser(): ?array
    {
        return \App\Services\SessionManager::getAuthUser();
    }

    public function isLoggedIn(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === 'Admin';
    }

    public function updatePassword(string $username, string $newPassword): bool
    {
        $user = User::findByUsernameOrEmail($username);
        if ($user) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            return User::update($user['id'], ['password' => $hashedPassword]);
        }
        return false;
    }

    public function setUnverifiedEmail(string $username, string $email): bool
    {
        $user = User::findByUsernameOrEmail($username);
        if ($user) {
            return User::update($user['id'], ['email' => $email, 'verified' => 0]);
        }
        return false;
    }

    public function verifyEmail(string $username): bool
    {
        $user = User::findByUsernameOrEmail($username);
        if ($user) {
            return User::update($user['id'], ['verified' => 1]);
        }
        return false;
    }
}
