<?php

namespace Core;

use App\Models\UserModel;

class AuthMiddleware
{
    protected $userModel;

    public function __construct() 
    {
        $this->userModel = new UserModel();
    }

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Move flash messages to temporary storage
        $_SESSION['_old_flash'] = $_SESSION['_flash'] ?? [];

        // Clear flash messages from previous request
        unset($_SESSION['_flash']);
    }
    
    public static function requireLogin(string $redirectTo = '/login'): void
    {

        if (!isset($_SESSION['user_id'])) {
            header("Location: $redirectTo");
            exit;
        }
    }

    public static function requireGuest(string $redirectTo = 'user/dashboard'): void
    {
        if (isset($_SESSION['user_id'])) {
            header("Location: $redirectTo");
            exit;
        }
    }

    public static function logout(string $redirectTo = '/login'): void
    {
        session_unset();
        session_destroy();
        header("Location: $redirectTo");
        exit;
    }

    public static function requireRole(array $allowedRoles = [], string $redirectTo = '/error/403'): void
    {
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
            header("Location: $redirectTo");
            exit;
        }
    }

    public static function handle(array $accessMap = [], array $publicRoutes = []): void
    { // Auto-initialize session and manage flash message lifecycle
        self::init();

        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Check if the route is public
        foreach ($publicRoutes as $route) {
            if (preg_match("#^$route$#", $requestUri)) {
                return;
            }
        }

        // Check if user is logged in via session
        if (!isset($_SESSION['user_id'])) {
            // Check for remember token
            if (Cookie::has('remember_token')) {
                $token = Cookie::get('remember_token');
                
                // Create instance of this class to access the model
                $auth = new self();
                $user = $auth->userModel->findByRememberToken($token);
                
                if ($user) {
                    // User found, create session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // User is now logged in, continue with the request
                } else {
                    // Invalid token, remove it
                    Cookie::delete('remember_token');
                    // Redirect to login page
                    header("Location: /login");
                    exit;
                }
            } else {
                // No session or valid remember token, redirect to login
                header("Location: /login");
                exit;
            }
        }

        // At this point, user is authenticated (either via session or remember token)
        // Check for route-based role access
        foreach ($accessMap as $route => $allowedRoles) {
            if (preg_match("#^$route$#", $requestUri)) {
                self::requireRole($allowedRoles);
                return;
            }
        }
    }
}
