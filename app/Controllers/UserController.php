<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserController extends BaseController{
    protected $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }
    
    public function renderUserDashboard() {
        $this->render("user/dashboard");
    }

    public function renderUserServices() {
        $this->render("user/services");
    }

    public function renderUserProducts() {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            
            // Get user data if not already in session
            if (!isset($_SESSION['address'])) {
                $user = $this->userModel->findById($userId);
                if ($user && isset($user['ua_address'])) {
                    $_SESSION['address'] = $user['ua_address'];
                }
            }
        }
        
        $this->render("user/products");
    }

    public function renderMyOrders() {
        $this->render("user/my-bookings");
    }

    public function renderUserProfile() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get complete user data
        $user = $this->userModel->findById($userId);
        
        // Make sure the session has the user's email and address
        if ($user) {
            if (!isset($_SESSION['email']) && isset($user['ua_email'])) {
                $_SESSION['email'] = $user['ua_email'];
            }
            
            if (!isset($_SESSION['address']) && isset($user['ua_address'])) {
                $_SESSION['address'] = $user['ua_address'];
            }
        }
        
        // Get customer statistics
        $statistics = $this->userModel->getCustomerStatistics($userId);
        
        // Add statistics to view data
        $viewData = [
            'statistics' => $statistics,
            'user' => $user
        ];
        
        $this->render("user/user-profile", $viewData);
    }
    
    public function updateProfile() {
        if (!$this->isAjax()) {
            return $this->jsonError('Invalid request method', 405);
        }
        
        if (!isset($_SESSION['user_id'])) {
            return $this->jsonError('User not authenticated', 401);
        }
        
        $userId = $_SESSION['user_id'];
        $data = $this->getJsonInput();
        
        // Filter allowed fields for update
        $allowedFields = ['first_name', 'last_name', 'phone_number', 'address'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        // Map to database column names
        $mappedData = [];
        if (isset($updateData['first_name'])) $mappedData['ua_first_name'] = $updateData['first_name'];
        if (isset($updateData['last_name'])) $mappedData['ua_last_name'] = $updateData['last_name'];
        if (isset($updateData['phone_number'])) $mappedData['ua_phone_number'] = $updateData['phone_number'];
        if (isset($updateData['address'])) $mappedData['ua_address'] = $updateData['address'];
        
        if (empty($mappedData)) {
            return $this->jsonError('No valid data provided for update', 400);
        }
        
        try {
            $result = $this->userModel->updateUser($userId, $mappedData);
            
            if ($result) {
                // Update session data
                if (isset($mappedData['ua_first_name'])) $_SESSION['first_name'] = $mappedData['ua_first_name'];
                if (isset($mappedData['ua_last_name'])) $_SESSION['last_name'] = $mappedData['ua_last_name'];
                if (isset($mappedData['ua_first_name']) || isset($mappedData['ua_last_name'])) {
                    $_SESSION['full_name'] = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
                }
                if (isset($mappedData['ua_phone_number'])) $_SESSION['phone_number'] = $mappedData['ua_phone_number'];
                if (isset($mappedData['ua_address'])) $_SESSION['address'] = $mappedData['ua_address'];
                
                return $this->jsonSuccess([], 'Profile updated successfully');
            } else {
                return $this->jsonError('Failed to update profile', 500);
            }
        } catch (\Exception $e) {
            return $this->jsonError('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    private function isStrongPassword(string $password): bool {
        return strlen($password) >= 8 &&
            preg_match('/[A-Z]/', $password) &&
            preg_match('/[a-z]/', $password) &&
            preg_match('/[0-9]/', $password);
    }
    
    public function updatePassword() {
        if (!$this->isAjax()) {
            return $this->jsonError('Invalid request method', 405);
        }
        
        if (!isset($_SESSION['user_id'])) {
            return $this->jsonError('User not authenticated', 401);
        }
        
        $userId = $_SESSION['user_id'];
        $data = $this->getJsonInput();
        
        // Validate required fields
        if (!isset($data['current_password']) || !isset($data['new_password']) || !isset($data['confirm_password'])) {
            return $this->jsonError('All password fields are required', 400);
        }
        
        // Validate password confirmation
        if ($data['new_password'] !== $data['confirm_password']) {
            return $this->jsonError('Password and confirmation do not match', 400);
        }

        // Validate Password Strength
        if (!$this->isStrongPassword($data['new_password'])) {
            return $this->jsonError(
                'Password must be at least 8 characters and include uppercase, lowercase, and number.'
            );
        }
        
        // Get user data to verify current password
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return $this->jsonError('User not found', 404);
        }
        
        // Verify current password
        if (!$this->userModel->verifyPassword($data['current_password'], $user['ua_hashed_password'])) {
            return $this->jsonError('Current password is incorrect', 400);
        }
        
        // Hash new password
        $hashedPassword = $this->userModel->hashPassword($data['new_password']);
        
        // Update password
        try {
            $result = $this->userModel->updateUser($userId, ['ua_hashed_password' => $hashedPassword]);
            
            if ($result) {
                return $this->jsonSuccess([], 'Password updated successfully');
            } else {
                return $this->jsonError('Failed to update password', 500);
            }
        } catch (\Exception $e) {
            return $this->jsonError('An error occurred: ' . $e->getMessage(), 500);
        }
    }
    
    public function uploadProfileImage() {
        if (!isset($_SESSION['user_id'])) {
            return $this->jsonError('User not authenticated', 401);
        }
        
        $userId = $_SESSION['user_id'];
        
        // Check if file was uploaded
        if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            return $this->jsonError('No image file uploaded or upload error', 400);
        }
        
        $file = $_FILES['profile_image'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return $this->jsonError('Invalid file type. Only JPG, PNG, and WEBP are allowed', 400);
        }
        
        // Validate file size (max 2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB in bytes
        if ($file['size'] > $maxSize) {
            return $this->jsonError('File size exceeds the maximum limit of 2MB', 400);
        }
        
        // Get current profile image URL before updating
        $user = $this->userModel->findById($userId);
        $oldProfileUrl = $user['ua_profile_url'] ?? null;
        
        // Create uploads directory if it doesn't exist
        $uploadsDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/profile_images';
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '.' . $extension;
        $targetPath = $uploadsDir . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $this->jsonError('Failed to save the uploaded file', 500);
        }
        
        // Update profile URL in database
        $profileUrl = '/uploads/profile_images/' . $filename;
        try {
            $result = $this->userModel->updateUser($userId, ['ua_profile_url' => $profileUrl]);
            
            if ($result) {
                // Update session
                $_SESSION['profile_url'] = $profileUrl;
                
                // Delete old profile image if it exists and is not the default image
                if ($oldProfileUrl && $oldProfileUrl !== '/assets/images/user-profile/default-profile.png' && strpos($oldProfileUrl, '/uploads/profile_images/') === 0) {
                    $oldFilePath = $_SERVER['DOCUMENT_ROOT'] . $oldProfileUrl;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                
                return $this->jsonSuccess(['profile_url' => $profileUrl], 'Profile image updated successfully');
            } else {
                // Remove uploaded file if database update fails
                @unlink($targetPath);
                return $this->jsonError('Failed to update profile image in database', 500);
            }
        } catch (\Exception $e) {
            // Remove uploaded file if an exception occurs
            @unlink($targetPath);
            return $this->jsonError('An error occurred: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * API endpoint to get customer statistics
     */
    public function getCustomerStats() {
        if (!$this->isAjax()) {
            return $this->jsonError('Invalid request method', 405);
        }
        
        if (!isset($_SESSION['user_id'])) {
            return $this->jsonError('User not authenticated', 401);
        }
        
        $userId = $_SESSION['user_id'];      
        
        try {
            $statistics = $this->userModel->getCustomerStatistics($userId);
            return $this->jsonSuccess($statistics, 'Statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->jsonError('An error occurred: ' . $e->getMessage(), 500);
        }
    }
}