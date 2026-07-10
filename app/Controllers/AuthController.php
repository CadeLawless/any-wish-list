<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Constants;
use App\Models\User;
use App\Services\AuthService;
use App\Validation\UserRequestValidator;
use App\Services\EmailService;
use App\Services\SessionManager;
use App\Services\UserPreferencesService;
use App\Helpers\StringHelper;
use App\Services\FileUploadService;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService = new AuthService(),
        private UserRequestValidator $userValidator = new UserRequestValidator(),
        private EmailService $emailService = new EmailService(),
        private FileUploadService $fileUploadService = new FileUploadService(),
    ) {
        parent::__construct();
    }

    public static function loginDataWithTitle(array $data): array {
        $data['title'] = 'Any Wish List | Login';
        return $data;
    }

    public function showLogin(): Response
    {
        
        $data = [
            'username' => $this->request->input('username', ''),
            'password' => '',
            'remember_me' => $this->request->input('remember_me', false),
            'customStyles' =>
                'input:not([type=submit], #new_password, #current_password) {
                    margin-bottom: 0;
                }'
        ];

        return $this->view('auth/login', AuthController::loginDataWithTitle($data), 'auth');
    }

    /**
     * Handle user login authentication
     * 
     * Processes login form submission, validates credentials, and establishes
     * user session. Redirects authenticated users away from login page.
     * 
     * @return Response Login form view or redirect after successful login
     */
    public function login(): Response
    {

        // Only process form submission on POST requests
        if ($this->request->isPost()) {
            $data = $this->request->input();
            $errors = $this->userValidator->validateLogin($data);

            if ($this->userValidator->hasErrors($errors)) {
                return $this->view('auth/login', AuthController::loginDataWithTitle([
                    'username' => $data['username'] ?? '',
                    'password' => '',
                    'remember_me' => isset($data['remember_me']),
                    'error_msg' => $this->userValidator->formatErrorsForDisplay($errors)
                ]), 'auth');
            }

            $remember = isset($data['remember_me']);
            
            if ($this->authService->login($data['username'], $data['password'], $remember)) {
                return $this->redirect('/');
            }

            return $this->view('auth/login', AuthController::loginDataWithTitle([
                'username' => $data['username'] ?? '',
                'password' => '',
                'remember_me' => $remember,
                'error_msg' => '<div class="submit-error"><strong>Login failed due to the following errors:</strong><ul><li>Username/email or password is incorrect</li></ul></div>'
            ]), 'auth');
        }

        // Show login form for GET requests
        return $this->view('auth/login', AuthController::loginDataWithTitle([
            'username' => '',
            'password' => '',
            'remember_me' => false,
            'error_msg' => ''
        ]), 'auth');
    }

    public function logout(): Response
    {
        $this->authService->logout();
        return $this->redirect('/login')->withSuccess('You have been logged out successfully.');
    }

    public static function registerDataWithTitle(array $data): array {
        $data['title'] = 'Create an Account';
        return $data;
    }

    public function showRegister(): Response
    {

        $data = [
            'username' => $this->request->input('username', ''),
            'name' => $this->request->input('name', ''),
            'email' => $this->request->input('email', ''),
            'password' => '',
            'password_confirmation' => '',
            'customStyles' =>
                'input:not([type=submit], #new_password, #current_password) {
                margin-bottom: 0;
            }'
        ];

        return $this->view('auth/register', AuthController::registerDataWithTitle($data), 'auth');
    }

    public function register(): Response
    {

        // Only process form submission on POST requests
        if ($this->request->isPost()) {
            $data = $this->request->input();
            $errors = $this->userValidator->validateRegistration($data);

            if ($this->userValidator->hasErrors($errors)) {
                return $this->view('auth/register', AuthController::registerDataWithTitle([
                    'username' => $data['username'] ?? '',
                    'name' => $data['name'] ?? '',
                    'email' => $data['email'] ?? '',
                    'password' => '',
                    'password_confirmation' => '',
                    'error_msg' => $this->userValidator->formatErrorsForDisplay($errors)
                ]), 'auth');
            }

            if ($this->authService->register($data)) {
                // Set up session and cookies
                SessionManager::setupRegistrationSession($data);
                
                // Send verification email
                $this->emailService->sendVerificationEmail($data['email'], $data['username']);
                
                return $this->redirect('/')->withSuccess('Registration successful! Please check your email to verify your account.');
            }

            return $this->view('auth/register', AuthController::registerDataWithTitle([
                'username' => $data['username'] ?? '',
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'password' => '',
                'password_confirmation' => '',
                'error_msg' => '<div class="submit-error"><strong>Registration failed:</strong><ul><li>Unable to create account. Please try again.</li></ul></div>'
            ]), 'auth');
        }

        // Show registration form for GET requests
        return $this->view('auth/register', AuthController::registerDataWithTitle([
            'username' => '',
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'error_msg' => ''
        ]), 'auth');
    }

    public static function forgotPasswordDataWithTitle(array $data): array {
        $data['title'] = 'Forgot Password';
        return $data;
    }

    public function forgotPassword(): Response
    {
        // Only process form submission on POST requests
        if ($this->request->isPost()) {
            $data = $this->request->input();
            
            // Validate that identifier field is not empty
            $errors = [];
            if (empty($data['identifier'])) {
                $errors['identifier'][] = 'Email or username is required.';
            }
            
            if ($this->userValidator->hasErrors($errors)) {
                return $this->view('auth/forgot-password', AuthController::forgotPasswordDataWithTitle([
                    'identifier' => $data['identifier'] ?? '',
                    'error_msg' => $this->userValidator->formatErrorsForDisplay($errors)
                ]), 'auth');
            }

            // Check if email or username exists
            $user = User::findByUsernameOrEmail($data['identifier']);
            
            if ($user) {
                // Check if user has unverified email (no verified email yet)
                if (empty($user['email']) && !empty($user['unverified_email'])) {
                    // Send verification email instead of reset link
                    $this->emailService->sendVerificationEmail($user['unverified_email'], $user['username']);
                    
                    // Return with special message about email verification
                    return $this->redirect('/login')->withSuccess('Your email address needs to be verified first. A verification email has been sent. Please check your inbox and verify your email before resetting your password.');
                }
                
                // Generate reset password key
                $resetPasswordKey = StringHelper::generateRandomString(Constants::RANDOM_STRING_LENGTH_EMAIL);
                $resetPasswordExpiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Update user with reset key
                User::update($user['id'], [
                    'reset_password_key' => $resetPasswordKey,
                    'reset_password_expiration' => $resetPasswordExpiration
                ]);
                
                // Send password reset email
                $this->emailService->sendPasswordResetEmailWithUsername($user['email'], $user['username'], $resetPasswordKey);
            }
            
            // Always show success message for security (don't reveal if account exists)
            return $this->redirect('/login')->withSuccess('If an account with that email or username exists, a password reset link has been sent.');
        }

        // Show forgot password form for GET requests
        return $this->view('auth/forgot-password', AuthController::forgotPasswordDataWithTitle([
            'identifier' => '',
            'error_msg' => ''
        ]), 'auth');
    }

    public static function resetPasswordDataWithTitle(array $data): array {
        $data['title'] = 'Any Wish List | Reset Password';
        return $data;
    }

    public function resetPassword(): Response
    {
        // Only process form submission on POST requests
        if ($this->request->isPost()) {
            $data = $this->request->input();
            $errors = $this->userValidator->validateNewPassword($data);

            if ($this->userValidator->hasErrors($errors)) {
                return $this->view('auth/reset-password', AuthController::resetPasswordDataWithTitle([
                    'key' => $data['key'] ?? '',
                    'email' => $data['email'] ?? '',
                    'password' => $data['password'] ?? '',
                    'password_confirmation' => $data['password_confirmation'] ?? '',
                    'error_msg' => $this->userValidator->formatErrorsForDisplay($errors)
                ]), 'auth');
            }

            // Validate the reset key and expiration
            $user = User::findByUsernameOrEmail($data['email']);
            
            if (!$user || $user['reset_password_key'] !== $data['key']) {
                return $this->redirect('/login')->withError('Invalid reset link.');
            }
            
            if (isset($user['reset_password_expiration']) && strtotime($user['reset_password_expiration']) < time()) {
                return $this->redirect('/login')->withError('Reset link has expired. Please request a new one.');
            }
            
            // Update password and clear reset fields
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            User::update($user['id'], [
                'password' => $hashedPassword,
                'reset_password_key' => null,
                'reset_password_expiration' => null
            ]);
            
            // Check if user is currently logged in
            $isLoggedIn = $this->authService->isLoggedIn();
            $currentUser = $this->authService->getCurrentUser();
            
            // If logged in and resetting their own password, redirect to home
            // Otherwise redirect to login
            if ($isLoggedIn && $currentUser && $currentUser['id'] == $user['id']) {
                return $this->redirect('/')->withSuccess('Password has been reset successfully.');
            }
            
            return $this->redirect('/login')->withSuccess('Password has been reset successfully. You can now log in with your new password.');
        }

        // Show reset password form for GET requests
        $key = $this->request->get('token');
        
        $user = null;
        $resetKey = null;
        $userEmail = null;
        
        if ($key) {
            $user = User::findByResetPasswordKey($key);

            if ($user && $user['reset_password_key'] === $key) {
                $resetKey = $key;
                $userEmail = $user['email'];
            }
        }
        
        // Validate we have the required information
        if (!$user || !$resetKey || !$userEmail) {
            return $this->view('auth/reset-password-error', AuthController::resetPasswordDataWithTitle([
                'error' => 'Invalid reset link.',
                'link_text' => 'Go to Login',
                'link_url' => '/login'
            ]), 'auth');
        }

        // Check expiration
        if (isset($user['reset_password_expiration']) && strtotime($user['reset_password_expiration']) < time()) {
            return $this->view('auth/reset-password-error', AuthController::resetPasswordDataWithTitle([
                'error' => 'This password reset link has expired. Try again!',
                'link_text' => 'Go to Login',
                'link_url' => '/login'
            ]), 'auth');
        }

        return $this->view('auth/reset-password', AuthController::resetPasswordDataWithTitle([
            'key' => $resetKey,
            'email' => $userEmail,
            'password' => '',
            'password_confirmation' => '',
            'error_msg' => ''
        ]), 'auth');
    }

    public static function verifyEmailDataWithTitle(array $data): array {
        $data['title'] = 'Any Wish List | Verify Email';
        return $data;
    }

    public function verifyEmail(): Response
    {
        $username = $this->request->get('username');
        $key = $this->request->get('key');

        if (!$username || !$key) {
            return $this->view('auth/verify-email', AuthController::verifyEmailDataWithTitle([
                'success' => false,
                'expired' => false,
                'notFound' => true
            ]), 'auth');
        }

        // Get user by username
        $user = User::findByUsernameOrEmail($username);
        
        if (!$user) {
            return $this->view('auth/verify-email', AuthController::verifyEmailDataWithTitle([
                'success' => false,
                'expired' => false,
                'notFound' => true
            ]), 'auth');
        }

        // Verify the key matches
        if ($user['email_key'] !== $key) {
            return $this->view('auth/verify-email', AuthController::verifyEmailDataWithTitle([
                'success' => false,
                'expired' => false,
                'notFound' => true
            ]), 'auth');
        }

        // Check if key has expired
        if (isset($user['email_key_expiration']) && strtotime($user['email_key_expiration']) < time()) {
            return $this->view('auth/verify-email', AuthController::verifyEmailDataWithTitle([
                'success' => false,
                'expired' => true,
                'notFound' => false,
                'username' => $username
            ]), 'auth');
        }

        // Move unverified_email to email field and clear the verification fields
        try {
            User::update($user['id'], [
                'email' => $user['unverified_email'],
                'unverified_email' => null,
                'email_key' => null,
                'email_key_expiration' => null
            ]);
            
            return $this->view('auth/verify-email', AuthController::verifyEmailDataWithTitle([
                'success' => true,
                'expired' => false,
                'notFound' => false
            ]), 'auth');
        } catch (\Exception $e) {
            return $this->view('auth/verify-email', AuthController::verifyEmailDataWithTitle([
                'success' => false,
                'expired' => false,
                'notFound' => false
            ]), 'auth');
        }
    }

    public function resendVerification(): Response
    {
        $username = $this->request->input('username');
        
        if (!$username) {
            return $this->redirect('/login')->withError('Invalid request.');
        }
        
        $user = User::findByUsernameOrEmail($username);
        
        if (!$user) {
            return $this->redirect('/login')->withError('User not found.');
        }
        
        // Get the email to send to
        $emailToUse = $user['email'] ?? $user['unverified_email'] ?? '';
        
        if (empty($emailToUse)) {
            return $this->redirect('/login')->withError('No email address found.');
        }
        
        // Send verification email
        try {
            $this->emailService->sendVerificationEmail($emailToUse, $username);
            return $this->redirect('/login')->withSuccess('Verification email resent! Please check your inbox.');
        } catch (\Exception $e) {
            return $this->redirect('/login')->withError('Failed to resend verification email. Please try again.');
        }
    }

    public function toggleDarkMode(): Response
    {
        // Check authentication without redirecting for AJAX requests
        $user = $this->auth();
        if (!$user) {
            return new Response(content: 'unauthorized', status: 401);
        }
        
        $dark = $this->request->input('dark');
        return UserPreferencesService::toggleDarkMode($user['id'], $dark);
    }

    public function profile(): Response
    {
        
        $user = $this->auth();
        
        // Use unverified_email if email is not set (newly registered users)
        $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
        
        $data = $this->buildProfileViewData(
            $user,
            name: $this->request->input('name', $user['name']),
            email: $this->request->input('email', $currentEmail),
            currentPassword: $this->request->input('current_password', ''),
            newPassword: $this->request->input('new_password', ''),
            confirmPassword: $this->request->input('confirm_password', '')
        );

        return $this->view('auth/profile', $data);
    }

    /**
     * Build profile view data with consistent structure
     * 
     * @param array $user Current user data
     * @param string $name Name value
     * @param string $email Email value
     * @param string $currentPassword Current password value
     * @param string $newPassword New password value
     * @param string $confirmPassword Confirm password value
     * @param string $nameErrorMsg Name error message
     * @param string $emailErrorMsg Email error message
     * @param string $passwordErrorMsg Password error message
     * @return array Complete profile view data
     */
    private function buildProfileViewData(
        array $user,
        string $name = '',
        string $email = '',
        string $currentPassword = '',
        string $newPassword = '',
        string $confirmPassword = '',
        string $nameErrorMsg = '',
        string $emailErrorMsg = '',
        string $passwordErrorMsg = ''
    ): array {
        return [
            'title' => 'View Profile',
            'user' => $user,
            'name' => $name ?: ($user['name'] ?? ''),
            'email' => $email,
            'current_password' => $currentPassword,
            'new_password' => $newPassword,
            'confirm_password' => $confirmPassword,
            'name_error_msg' => $nameErrorMsg,
            'email_error_msg' => $emailErrorMsg,
            'password_error_msg' => $passwordErrorMsg,
            'customStyles' =>
                '.form-flex {
                    max-width: unset;
                }'
        ];
    }

    public function uploadProfilePicture(): Response
    {
        $user = $this->auth();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $picture = $this->request->input('picture');
        if(!$picture) {
            return $this->json([
                'success' => false,
                'message' => 'No picture data provided'
            ], 400);
        }

        try {
            $filename = $this->fileUploadService->uploadProfilePicture($user['username'], $picture);

            if($filename['success'] === false) {
                throw new \Exception($filename['error']);
            }

            if(!isset($filename['filename'])) {
                throw new \Exception('Invalid file upload response');
            }

            // Update user's profile picture in database
            User::updateProfilePicture($user['username'], $filename['filename']);

            return $this->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to upload profile picture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(): Response
    {
        
        $user = $this->auth();
        $data = $this->request->input();
        
        // Handle name update
        if (isset($data['name_submit_button'])) {
            $errors = $this->userValidator->validateNameUpdate($data);
            
            if ($this->userValidator->hasErrors($errors)) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    name: $data['name'] ?? $user['name'],
                    email: $currentEmail,
                    nameErrorMsg: $this->userValidator->formatErrorsForDisplay($errors)
                ));
            }
            
            try {
                User::update($user['id'], ['name' => $data['name']]);
                return $this->redirect('/profile')->withSuccess('Name updated successfully!');
            } catch (\Exception $e) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    name: $data['name'] ?? $user['name'],
                    email: $currentEmail,
                    nameErrorMsg: '<div class="submit-error"><strong>Name could not be updated due to the following errors:</strong><ul><li>Something went wrong while trying to update your name</li></ul></div>'
                ));
            }
        }
        
        // Handle email update
        if (isset($data['email_submit_button'])) {
            $errors = $this->userValidator->validateEmailUpdate($data);
            
            if ($this->userValidator->hasErrors($errors)) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    email: $data['email'] ?? $currentEmail,
                    emailErrorMsg: $this->userValidator->formatErrorsForDisplay($errors)
                ));
            }
            
            // Check if email already exists
            $existingUsers = User::where('email', '=', $data['email']);
            $existingUser = !empty($existingUsers) ? $existingUsers[0] : null;
            if ($existingUser && $existingUser['id'] != $user['id']) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    email: $data['email'] ?? $currentEmail,
                    emailErrorMsg: '<div class="submit-error"><strong>Email could not be updated due to the following errors:</strong><ul><li>That email already has an account associated with it. Try a different one.</li></ul></div>'
                ));
            }
            
            try {
                // Generate email verification key
                $emailKey = StringHelper::generateRandomString(Constants::RANDOM_STRING_LENGTH_EMAIL);
                $emailKeyExpiration = date(format: 'Y-m-d H:i:s', timestamp: strtotime('+' . Constants::EMAIL_VERIFICATION_HOURS . ' hours'));
                
                User::update($user['id'], [
                    'unverified_email' => $data['email'],
                    'email_key' => $emailKey,
                    'email_key_expiration' => $emailKeyExpiration
                ]);
                
                // Send verification email
                $this->emailService->sendVerificationEmail($data['email'], $user['username']);
                
                return $this->redirect('/profile')->withSuccess('Email update initiated! Please check your email to verify your new address.');
            } catch (\Exception $e) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    email: $data['email'] ?? $currentEmail,
                    emailErrorMsg: '<div class="submit-error"><strong>Email could not be updated due to the following errors:</strong><ul><li>Something went wrong while trying to update your email</li></ul></div>'
                ));
            }
        }
        
        // Handle new email submission (when user already has unverified email)
        if (isset($data['new_email_submit_button'])) {
            $errors = $this->userValidator->validateEmailUpdate($data);
            
            if ($this->userValidator->hasErrors($errors)) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    email: $data['email'] ?? $currentEmail,
                    emailErrorMsg: $this->userValidator->formatErrorsForDisplay($errors)
                ));
            }
            
            // Check if email already exists
            $existingUsers = User::where('email', '=', $data['email']);
            $existingUser = !empty($existingUsers) ? $existingUsers[0] : null;
            if ($existingUser && $existingUser['id'] != $user['id']) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    email: $data['email'] ?? $currentEmail,
                    emailErrorMsg: '<div class="submit-error"><strong>Email could not be updated due to the following errors:</strong><ul><li>That email already has an account associated with it. Try a different one.</li></ul></div>'
                ));
            }
            
            try {
                // Generate email verification key
                $emailKey = StringHelper::generateRandomString(Constants::RANDOM_STRING_LENGTH_EMAIL);
                $emailKeyExpiration = date(format: 'Y-m-d H:i:s', timestamp: strtotime('+' . Constants::EMAIL_VERIFICATION_HOURS . ' hours'));
                
                User::update($user['id'], [
                    'unverified_email' => $data['email'],
                    'email_key' => $emailKey,
                    'email_key_expiration' => $emailKeyExpiration
                ]);
                
                // Send verification email
                $this->emailService->sendVerificationEmail($data['email'], $user['username']);
                
                return $this->redirect('/profile')->withSuccess('Email update initiated! Please check your email to verify your new address.');
            } catch (\Exception $e) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    email: $data['email'] ?? $currentEmail,
                    emailErrorMsg: '<div class="submit-error"><strong>Email could not be updated due to the following errors:</strong><ul><li>Something went wrong while trying to update your email</li></ul></div>'
                ));
            }
        }
        
        // Handle password update
        if (isset($data['password_submit_button'])) {
            $errors = $this->userValidator->validatePasswordChange($data);
            
            // Verify current password
            if (!password_verify($data['current_password'], $user['password'])) {
                $errors['current_password'][] = 'Incorrect current password';
            }
            
            if ($this->userValidator->hasErrors($errors)) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    email: $currentEmail,
                    currentPassword: $data['current_password'] ?? '',
                    newPassword: $data['new_password'] ?? '',
                    confirmPassword: $data['confirm_password'] ?? '',
                    passwordErrorMsg: $this->userValidator->formatErrorsForDisplay($errors)
                ));
            }
            
            try {
                $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
                User::update($user['id'], ['password' => $hashedPassword]);
                
                return $this->redirect('/profile')->withSuccess('Password changed successfully!');
            } catch (\Exception $e) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    email: $currentEmail,
                    currentPassword: $data['current_password'] ?? '',
                    newPassword: $data['new_password'] ?? '',
                    confirmPassword: $data['confirm_password'] ?? '',
                    passwordErrorMsg: '<div class="submit-error"><strong>Password could not be changed due to the following errors:</strong><ul><li>Something went wrong while trying to change your password</li></ul></div>'
                ));
            }
        }
        
        // Handle resend verification email
        if (isset($data['resend_verification_button'])) {
            $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
            
            if (empty($currentEmail)) {
                return $this->redirect('/profile')->withError('No email address found to send verification to.');
            }
            
            try {
                // Send verification email
                $this->emailService->sendVerificationEmail($currentEmail, $user['username']);
                
                return $this->redirect('/profile')->withSuccess('Verification email resent! Please check your inbox.');
            } catch (\Exception $e) {
                return $this->redirect('/profile')->withError('Failed to resend verification email. Please try again.');
            }
        }
        
        // Handle forgot password
        if (isset($data['forgot_password_submit_button'])) {
            $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
            
            if (empty($currentEmail)) {
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    email: $currentEmail,
                    emailErrorMsg: '<p>In order for you to reset a password that you don\'t know, an email with a password reset link needs to be sent to you. Please set up your email above before trying to reset your password</p>'
                ));
            }
            
            try {
                // Generate reset key
                $resetKey = StringHelper::generateRandomString(Constants::RANDOM_STRING_LENGTH_RESET);
                $resetExpiration = date(format: 'Y-m-d H:i:s', timestamp: strtotime('+' . Constants::PASSWORD_RESET_HOURS . ' hours'));
                
                User::update($user['id'], [
                    'reset_password_key' => $resetKey,
                    'reset_password_expiration' => $resetExpiration
                ]);
                
                // Send reset email to the current email (verified or unverified)
                $this->emailService->sendPasswordResetEmail($currentEmail, $user['name'], $resetKey);
                
                return $this->redirect('/profile')->withSuccess('Password reset email sent! Please check your email.');
            } catch (\Exception $e) {
                return $this->view('auth/profile', $this->buildProfileViewData(
                    $user,
                    email: $currentEmail,
                    passwordErrorMsg: '<div class="submit-error"><strong>Something went wrong while trying to send the reset email</strong></div>'
                ));
            }
        }
        
        return $this->redirect('/profile');
    }


    /**
     * AJAX endpoint to check if username exists
     */
    public function checkUsername(): Response
    {
        $username = $this->request->input('username');
        
        if (empty($username)) {
            return new Response(
                content: json_encode(['available' => true]),
                status: 200,
                headers: ['Content-Type' => 'application/json']
            );
        }
        
        $existingUser = User::findByUsernameOrEmail($username);
        
        return new Response(
            content: json_encode(['available' => !$existingUser]),
            status: 200,
            headers: ['Content-Type' => 'application/json']
        );
    }

    /**
     * AJAX endpoint to check if email exists
     */
    public function checkEmail(): Response
    {
        $email = $this->request->input('email');
        
        if (empty($email)) {
            return new Response(
                content: json_encode(['available' => true]),
                status: 200,
                headers: ['Content-Type' => 'application/json']
            );
        }
        
        $existingUser = User::findByUsernameOrEmail($email);
        
        return new Response(
            content: json_encode(['available' => !$existingUser]),
            status: 200,
            headers: ['Content-Type' => 'application/json']
        );
    }

}
