<?php

namespace App\Services;

class EmailService
{
    private readonly string $fromEmail;
    private readonly string $fromName;

    public function __construct(
        string $fromEmail = '',
        string $fromName = ''
    ) {
        $this->fromEmail = $fromEmail ?: \App\Core\Config::get('app.email.from');
        $this->fromName = $fromName ?: \App\Core\Config::get('app.email.from_name');
    }

    public function sendVerificationEmail(string $email, string $username): bool
    {
        $subject = 'Verify Your Email for Any Wish List';
        
        // Get the email_key from the database for this user
        $user = \App\Models\User::findByUsernameOrEmail($username);
        if (!$user || !isset($user['email_key'])) {
            return false;
        }
        
        $verificationLink = $this->generateVerificationLink($user['email_key'], $username);
        $message = $this->getVerificationEmailTemplate($username, $verificationLink);
        
        return $this->sendEmail($email, $subject, $message);
    }

    public function sendPasswordResetEmail(string $email, string $name, string $resetKey): bool
    {
        $subject = 'Reset Your Password for Any Wish List';
        $resetLink = $this->generateResetLink($resetKey);
        
        $message = $this->getPasswordResetEmailTemplate($name, $resetLink);
        
        return $this->sendEmail($email, $subject, $message);
    }

    public function sendPasswordResetEmailWithUsername(string $email, string $username, string $resetToken): bool
    {
        $subject = 'Reset Your Password for Any Wish List';
        $resetLink = $this->generateResetLink($resetToken);
        
        $message = $this->getPasswordResetEmailTemplate($username, $resetLink);
        
        return $this->sendEmail($email, $subject, $message);
    }

    public function sendWelcomeEmail(string $email, string $username): bool
    {
        $subject = 'Welcome to Any Wish List!';
        $message = $this->getWelcomeEmailTemplate($username);
        
        return $this->sendEmail($email, $subject, $message);
    }

    private function sendEmail(string $to, string $subject, string $message): bool
    {
        try {
            // Use Composer autoloaded PHPMailer
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings from configuration
            $mail->isSMTP();
            $mail->Host = \App\Core\Config::get('app.email.smtp_host');
            $mail->SMTPAuth = true;
            $mail->Username = \App\Core\Config::get('app.email.smtp_username');
            $mail->Password = \App\Core\Config::get('app.email.smtp_password');
            $mail->SMTPSecure = \App\Core\Config::get('app.email.smtp_encryption');
            $mail->Port = \App\Core\Config::get('app.email.smtp_port');
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->CharSet = 'UTF-8';
            
            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    private function generateVerificationLink(string $emailKey, string $username): string
    {
        $baseUrl = \App\Core\Config::get('app.url');
        
        return $baseUrl . '/verify-email?key=' . $emailKey . '&username=' . urlencode($username);
    }

    private function generateResetLink(string $token): string
    {
        $baseUrl = \App\Core\Config::get('app.url');
        return $baseUrl . '/reset-password?key=' . $token;
    }
    
    private function getVerificationEmailTemplate(string $username, string $link): string
    {
        return "
            <h2>Welcome to Any Wish List!</h2>
            <p>Thank you for signing up for Any Wish List!</p>
            <p>To complete your account registration, please verify your email address by clicking the button below:</p>
            <a href='{$link}' 
            style='display: inline-block; padding: 12px 24px; color: #ffffff; background-color: #3e5646; border-radius: 5px; text-decoration: none; font-weight: bold;'>
                Verify My Email Address
            </a>
            <p style='margin-top: 20px;'>This link will expire in 24 hours, so please complete your verification as soon as possible.</p>
            <p style='font-size: 12px;'>If you did not create an Any Wish List account, please ignore this email.</p>
            <p style='font-size: 12px; margin-top: 20px;'>Thank you,<br>The Any Wish List Team</p>";
    }

    private function getPasswordResetEmailTemplate(string $username, string $link): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Reset Your Password</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Password Reset Request</h2>
                <p>Hello {$username},</p>
                <p>We received a request to reset your password for your Any Wish List account. If you made this request, click the link below to reset your password:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$link}' style='background-color: #e74c3c; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                </p>
                <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #666;'>{$link}</p>
                <p>This link will expire in 1 hour for security reasons.</p>
                <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>This email was sent from Any Wish List. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }

    private function getWelcomeEmailTemplate(string $username): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Welcome to Any Wish List!</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Welcome to Any Wish List!</h2>
                <p>Hello {$username},</p>
                <p>Thank you for joining Any Wish List! We're excited to help you create and manage your wish lists.</p>
                <p>Here's what you can do with your new account:</p>
                <ul>
                    <li>Create multiple wish lists for different occasions</li>
                    <li>Add items with photos, descriptions, and links</li>
                    <li>Share your wish lists with family and friends</li>
                    <li>Track which items have been purchased</li>
                    <li>Customize your lists with themes</li>
                </ul>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . \App\Core\Config::get('app.url') . "' style='background-color: #27ae60; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Get Started</a>
                </p>
                <p>If you have any questions, feel free to reach out to us.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>This email was sent from Any Wish List. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }
}
