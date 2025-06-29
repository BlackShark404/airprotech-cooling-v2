<?php

namespace App\Controllers;

use Core\Cookie;
use Core\Session;
use Core\AvatarGenerator;
use App\Controllers\BaseController;

class AuthController extends BaseController
{
    protected $userModel;

    public function __construct() 
    {
        $this->userModel = $this->loadModel('UserModel');
        
        // Clean up expired tokens on controller initialization
        $this->userModel->cleanupExpiredTokens();
    }

    public function renderLogin() 
    {
        $this->render('auth/login');
    }

    public function renderRegister() 
    {
        $this->render('auth/register');
    }

    public function renderResetPassword() 
    {
        $this->render('auth/reset-password');
    }

    public function loginAccount() 
    {
        if (!$this->isPost() || !$this->isAjax()) {
            return $this->jsonError('Invalid request method');
        }

        $data = $this->getJsonInput();
        
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $remember = isset($data['remember']);

        // Get user record but check for soft deletion
        $user = $this->userModel->findByEmail($email);

        // Check if user exists
        if (!$user) {
            return $this->jsonError('Invalid email or password');
        }

        // Check if account is soft deleted
        if (isset($user['ua_deleted_at']) && $user['ua_deleted_at'] !== null) {
            return $this->jsonError('This account has been deactivated');
        }

        // Check if account is deactivated
        if (isset($user['ua_is_active']) && $user['ua_is_active'] === false) {
            return $this->jsonError('This account has been deactivated');
        }

        // Check if password exists
        if (!isset($user['ua_hashed_password'])) {
            return $this->jsonError('Authentication error');
        }

        // Check password and active status
        if (!$this->userModel->verifyPassword($password, $user['ua_hashed_password'])) {
            return $this->jsonError('Invalid email or password');
        }

        // Update last login timestamp
        $this->userModel->updateLastLogin($user['ua_id']);

        // Set session data
        Session::set('user_id', $user['ua_id']);
        Session::set('profile_url', $user['ua_profile_url']);
        Session::set('first_name', $user['ua_first_name']);
        Session::set('last_name', $user['ua_last_name']);
        Session::set('full_name', $user['ua_first_name'] . ' ' . $user['ua_last_name']);
        Session::set('email', $user['ua_email']);
        Session::set('phone_number', $user['ua_phone_number']);
        Session::set('address', $user['ua_address']);
        Session::set('user_role', $user['role_name'] ?? 'user');


        if ($remember) {
            $token = $this->userModel->generateRememberToken($user['ua_id'], 30);
            Cookie::set('remember_token', $token, 30);
        }

        $role = $user['role_name'] ?? "/";  

        if ($role === "admin") {
            Session::set("profile_route", '/admin/profile');
        } else if ($role === "technician") {
            Session::set("profile_route", '/technician/technician-profile');
        } else {
            Session::set("profile_route", '/user/user-profile');
        }

        $redirectUrl = match ($role) {
            'customer'      => '/user/services',
            'admin'     => '/admin/service-requests',
            default     => '/'
        };

        return $this->jsonSuccess(
            ['redirect_url' => $redirectUrl],
            'Login successful'
        );
    }

    private function isStrongPassword(string $password): bool {
        return strlen($password) >= 8 &&
            preg_match('/[A-Z]/', $password) &&
            preg_match('/[a-z]/', $password) &&
            preg_match('/[0-9]/', $password);
    }

    public function registerAccount() 
    {
        $avatar = new AvatarGenerator();

        if (!$this->isPost() || !$this->isAjax()) {
            return $this->jsonError('Invalid request method');
        }
        $data = $this->getJsonInput();

        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'email', 'password', 'confirm_password'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field] ?? '')) {
                return $this->jsonError('All fields are required');
            }
        }

        $profileUrl = $avatar->generate($data['first_name'] . ' ' . $data['last_name']);
        $firstName = $data['first_name'];
        $lastName = $data['last_name'];
        $email = $data['email'];
        $password = $data['password'];
        $confirmPassword = $data['confirm_password'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError('Invalid email format');
        }

        // Check if email already exists
        if ($this->userModel->emailExists($email)) {
            return $this->jsonError('Email already exists');
        }

        // Validate if passwords match
        if ($password != $confirmPassword) {
            return $this->jsonError('Passwords do not match');
        }

        // Validate Password Strength
        if (!$this->isStrongPassword($password)) {
            return $this->jsonError(
                'Password must be at least 8 characters and include uppercase, lowercase, and number.'
            );
        }

        // Create the user
        $result = $this->userModel->createUser([
            'ua_profile_url' => $profileUrl,
            'ua_first_name' => $firstName,
            'ua_last_name' => $lastName,
            'ua_email' => $email,
            'ua_hashed_password' => password_hash($password, PASSWORD_BCRYPT),
            'ua_role_id' => '1',
            'ua_is_active' => true
        ]);

        if ($result) {
            return $this->jsonSuccess(
                ['redirect_url' => '/login'],
                'User registered successfully'
            );
        } else {
            return $this->jsonError('Registration failed');
        }
    }

    public function logout()
    {
        // Clear "remember me" token from DB if set
        if (isset($_SESSION['user_id'])) {
            $this->userModel->clearRememberToken($_SESSION['user_id']);
        }

        // Remove session data
        Session::clear();
        Session::destroy(); 

        // Remove "remember me" cookie if it exists
        if (Cookie::has('remember_token')) {
            Cookie::delete('remember_token');
        }

        // Flash logout success message
        Session::flash("success", "Logout successful");

        // Redirect to login page
        $this->redirect("/");
    }

    /**
     * Check if user is already logged in via remember token
     * Called on application startup
     */
    public function checkRememberToken()
    {
        // If already logged in, skip this check
        if (isset($_SESSION['user_id'])) {
            return;
        }
        
        // Check for remember token cookie
        if (Cookie::has('remember_token')) {
            $token = Cookie::get('remember_token');
            $user = $this->userModel->findByRememberToken($token);
            
            // If valid token and user is active
            // findByRememberToken now includes expiration check
            if ($user && $user['ua_is_active']) {
                // Update last login timestamp
                $this->userModel->updateLastLogin($user['ua_id']);
                
                // Set session data
                Session::set('user_id', $user['ua_id']);
                Session::set('user_email', $user['ua_email']);
                Session::set('user_role', $user['role_name']);
                Session::set('full_name', $user['ua_first_name'] . ' ' . $user['ua_last_name']);
                Session::set('profile_url', $user['ua_profile_url']);
                Session::set('first_name', $user['ua_first_name']);
                Session::set('last_name', $user['ua_last_name']);
                Session::set('phone_number', $user['ua_phone_number']);
                Session::set('email', $user['ua_email']);
                Session::set('address', $user['ua_address']);

                // Generate a new token for security
                // This rotates the token on each successful auto-login
                $newToken = $this->userModel->generateRememberToken($user['ua_id'], 30);
                Cookie::set('remember_token', $newToken, 30);
            } else {
                // Token is invalid or expired, clear cookie
                Cookie::delete('remember_token');
            }
        }
    }

    /**
     * Handle password reset request form
     */
    public function forgotPasswordForm()
    {
        $this->render('auth/forgot-password');
    }

    /**
     * Process password reset request
     */
    public function forgotPassword()
    {
        if (!$this->isPost() || !$this->isAjax()) {
            return $this->jsonError('Invalid request method');
        }

        $data = $this->getJsonInput();
        $email = $data['email'] ?? '';
        
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            // Don't reveal if email exists or not for security
            Session::flash("success", "If your email is registered, you will receive password reset instructions");
            return $this->jsonSuccess(null, 'Reset instructions sent if email exists');
        }
        
        // Here you would generate a reset token and send an email
        // This is just a placeholder - implement actual email sending logic
        $resetToken = bin2hex(random_bytes(32));
        
        // Store the token in the database (you'd need to add this field)
        // $this->userModel->updateUser($user['ua_id'], ['reset_token' => $resetToken]);
        
        // Send email with reset link
        // sendResetEmail($user['ua_email'], $resetToken);
        
        Session::flash("success", "Password reset instructions sent to your email");
        return $this->jsonSuccess(null, 'Reset instructions sent');
    }
}