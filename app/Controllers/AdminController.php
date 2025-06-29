<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AdminModel; 
use App\Models\ServiceRequestModel;
use App\Models\ProductModel;
use App\Models\ReportsModel;
use App\Models\ProductBookingModel;
use App\Models\ProductAssignmentModel;
use App\Models\BookingAssignmentModel;
use App\Models\InventoryModel;

class AdminController extends BaseController {
    protected $userModel;
    protected $adminModel;
    protected $serviceModel;
    protected $productModel;
    protected $reportsModel;
    protected $productBookingModel;
    protected $bookingAssignmentModel;
    protected $productAssignmentModel;
    protected $inventoryModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->adminModel = new AdminModel(); 
        $this->serviceModel = new ServiceRequestModel();
        $this->productModel = new ProductModel();
        $this->reportsModel = new ReportsModel();
        
        // Initialize required models for product booking operations
        $this->productBookingModel = new ProductBookingModel();
        $this->bookingAssignmentModel = new BookingAssignmentModel();
        $this->productAssignmentModel = new ProductAssignmentModel();
        $this->inventoryModel = new InventoryModel();

        // Ensure user is authenticated and is an admin
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            // Redirect to login or show an error page
            // Ensure that redirect actually stops script execution if needed by adding exit or return after it.
            if (isset($_SESSION['user_id']) && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin')) {
                // Logged in but not admin (or role not set), show access denied or redirect to a safe page
                // For now, redirecting to home. Implement a proper access denied page later.
                $this->redirect('/'); 
                exit; // Important to stop further execution
            } else if (!isset($_SESSION['user_id'])){
                // Not logged in, redirect to login page
                $this->redirect('/login');
                exit; 
            }
           
            if(!isset($_SESSION['user_id'])) $this->redirect('/login');
            else $this->redirect('/'); 
            exit;
        }
    }

    public function renderServiceRequest() {
        $this->render('admin/service-request');
    }

    public function renderProductBookings() {
        $this->render('admin/product-booking');
    }

    public function renderInventory() {
        $this->render('admin/inventory');
    }

    public function renderAdminProfile() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login'); 
            return;
        }
        
        $adminId = $_SESSION['user_id'];
        
        // Get complete admin data (joins USER_ACCOUNT and ADMIN tables)
        $adminUser = $this->userModel->findUserWithRoleDetails($adminId, 'admin');
        
        if ($adminUser) {
            // Update session with latest data from DB, useful if changed elsewhere or for consistency
            $_SESSION['email'] = $adminUser['ua_email'];
            $_SESSION['address'] = $adminUser['ua_address'];
            $_SESSION['first_name'] = $adminUser['ua_first_name'];
            $_SESSION['last_name'] = $adminUser['ua_last_name'];
            $_SESSION['full_name'] = trim($adminUser['ua_first_name'] . ' ' . $adminUser['ua_last_name']);
            $_SESSION['profile_url'] = $adminUser['ua_profile_url'];
            $_SESSION['office_number'] = $adminUser['ad_office_no'] ?? null; // Ensure it handles null
        } else {
            unset($_SESSION['user_id']); // Clear potentially problematic session
            unset($_SESSION['user_role']);
            // log_error("Admin user details not found for ID: " . $adminId);
            $this->redirect('/login');
            return;
        }
        
        // Get system statistics
        $statistics = $this->adminModel->getSystemStatistics();

        $viewData = [
            'user' => $adminUser, // Pass the admin data to the view
            'statistics' => $statistics // Pass system statistics to the view
        ];
        
        $this->render("admin/admin-profile", $viewData);
    }

    public function updateAdminProfile() {
        if (!$this->isAjax()) {
            return $this->jsonError('Invalid request method', 405);
        }
        
        $adminId = $_SESSION['user_id'];
        $data = $this->getJsonInput();
        
        $userAllowedFields = ['first_name', 'last_name', 'phone_number', 'address'];
        $userUpdateData = array_intersect_key($data, array_flip($userAllowedFields));
        
        $userMappedData = [];
        if (isset($userUpdateData['first_name'])) $userMappedData['ua_first_name'] = trim($userUpdateData['first_name']);
        if (isset($userUpdateData['last_name'])) $userMappedData['ua_last_name'] = trim($userUpdateData['last_name']);
        if (isset($userUpdateData['phone_number'])) $userMappedData['ua_phone_number'] = $userUpdateData['phone_number'];
        if (isset($userUpdateData['address'])) $userMappedData['ua_address'] = $userUpdateData['address'];

        $adminUpdateData = [];
        if (isset($data['office_number'])) $adminUpdateData['ad_office_no'] = $data['office_number'];
        
        if (empty($userMappedData) && empty($adminUpdateData)) {
            return $this->jsonError('No valid data provided for update', 400);
        }
        
        try {
            $this->pdo->beginTransaction(); // Start transaction using $this->pdo

            $userResult = true; 
            if (!empty($userMappedData)) {
                $userResult = $this->userModel->updateUser($adminId, $userMappedData);
            }

            $adminResult = true; 
            if (!empty($adminUpdateData)) {
                $adminExists = $this->adminModel->findByAccountId($adminId);
                if ($adminExists) {
                    $adminResult = $this->adminModel->updateByAccountId($adminId, $adminUpdateData);
                } else {
                    $adminDataWithId = array_merge($adminUpdateData, ['ad_account_id' => $adminId]);
                    $adminResult = $this->adminModel->createAdmin($adminDataWithId); 
                }
            }
            
            if ($userResult && $adminResult) {
                $this->pdo->commit(); // Commit transaction using $this->pdo

                // Update session data
                if (isset($userMappedData['ua_first_name'])) $_SESSION['first_name'] = $userMappedData['ua_first_name'];
                if (isset($userMappedData['ua_last_name'])) $_SESSION['last_name'] = $userMappedData['ua_last_name'];
                if (isset($userMappedData['ua_first_name']) || isset($userMappedData['ua_last_name'])) {
                    $_SESSION['full_name'] = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
                }
                if (isset($userMappedData['ua_phone_number'])) $_SESSION['phone_number'] = $userMappedData['ua_phone_number'];
                if (isset($userMappedData['ua_address'])) $_SESSION['address'] = $userMappedData['ua_address'];
                if (isset($adminUpdateData['ad_office_no'])) $_SESSION['office_number'] = $adminUpdateData['ad_office_no'];
                
                return $this->jsonSuccess([], 'Profile updated successfully');
            } else {
                $this->pdo->rollBack(); // Rollback transaction using $this->pdo
                return $this->jsonError('Failed to update profile', 500);
            }
        } catch (\Exception $e) {
            $this->pdo->rollBack(); // Rollback transaction on error using $this->pdo
            // Log the error: error_log($e->getMessage());
            return $this->jsonError('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    private function isStrongPassword(string $password): bool {
        return strlen($password) >= 8 &&
            preg_match('/[A-Z]/', $password) &&
            preg_match('/[a-z]/', $password) &&
            preg_match('/[0-9]/', $password);
    }
    
    
    public function updateAdminPassword() {
        if (!$this->isAjax()) {
            return $this->jsonError('Invalid request method', 405);
        }
        
        // Auth check in constructor
        $adminId = $_SESSION['user_id'];
        $data = $this->getJsonInput();
        
        if (!isset($data['current_password']) || !isset($data['new_password']) || !isset($data['confirm_password'])) {
            return $this->jsonError('All password fields are required', 400);
        }
        
        if ($data['new_password'] !== $data['confirm_password']) {
            return $this->jsonError('Password and confirmation do not match', 400);
        }
 
        // Validate Password Strength
        if (!$this->isStrongPassword($data['new_password'])) {
            return $this->jsonError(
                'Password must be at least 8 characters and include uppercase, lowercase, and number.'
            );
        }
        
        $adminUser = $this->userModel->findById($adminId);
        if (!$adminUser) {
            return $this->jsonError('Admin user not found', 404);
        }
        
        if (!$this->userModel->verifyPassword($data['current_password'], $adminUser['ua_hashed_password'])) {
            return $this->jsonError('Current password is incorrect', 400);
        }
        
        $hashedPassword = $this->userModel->hashPassword($data['new_password']);
        
        try {
            $result = $this->userModel->updateUser($adminId, ['ua_hashed_password' => $hashedPassword]);
            
            if ($result) {
                return $this->jsonSuccess([], 'Password updated successfully');
            } else {
                return $this->jsonError('Failed to update password', 500);
            }
        } catch (\Exception $e) {
            // Log the error: error_log($e->getMessage());
            return $this->jsonError('An error occurred: ' . $e->getMessage(), 500);
        }
    }
    
    public function uploadAdminProfileImage() {
        if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            return $this->jsonError('No image file uploaded or upload error', 400);
        }
        
        // Auth check in constructor
        $adminId = $_SESSION['user_id'];
        $file = $_FILES['profile_image'];
        
        // Validation for file type and size (copied from UserController, can be moved to a helper/BaseController)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            return $this->jsonError('Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed', 400);
        }
        
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            return $this->jsonError('File size exceeds the maximum limit of 2MB', 400);
        }
        
        $adminUser = $this->userModel->findById($adminId);
        $oldProfileUrl = $adminUser['ua_profile_url'] ?? null;
        
        $uploadSubDir = 'profile_images';
        $uploadsDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/' . $uploadSubDir;
        
        if (!file_exists($uploadsDir)) {
            if (!mkdir($uploadsDir, 0775, true)) { 
                // error_log("Failed to create upload directory: {$uploadsDir}");
                return $this->jsonError('Failed to create upload directory. Check server permissions.', 500);
            }
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeExtension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? $extension : 'jpg'; // Default to jpg if ext is unusual
        $filename = 'admin_profile_' . $adminId . '_' . time() . '.' . $safeExtension; 
        $targetPath = $uploadsDir . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            // error_log("Failed to move uploaded file to: {$targetPath}");
            return $this->jsonError('Failed to save the uploaded file. Check server permissions or path.', 500);
        }
        
        $profileUrl = '/uploads/' . $uploadSubDir . '/' . $filename;
        try {
            $result = $this->userModel->updateUser($adminId, ['ua_profile_url' => $profileUrl]);
            
            if ($result) {
                $_SESSION['profile_url'] = $profileUrl;
                
                // Delete old profile image if it exists, is not the default, and is in the uploads folder
                if ($oldProfileUrl && $oldProfileUrl !== '/assets/images/user-profile/default-profile.png' && strpos($oldProfileUrl, '/uploads/' . $uploadSubDir . '/') === 0) {
                    $oldFilePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $oldProfileUrl;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                
                return $this->jsonSuccess(['profile_url' => $profileUrl], 'Profile image updated successfully');
            } else {
                @unlink($targetPath); // Clean up uploaded file if DB update fails
                return $this->jsonError('Failed to update profile image in database', 500);
            }
        } catch (\Exception $e) {
            @unlink($targetPath); 
            // Log the error: error_log("DB error updating profile image: " . $e->getMessage());
            return $this->jsonError('An error occurred during database update: ' . $e->getMessage(), 500);
        }
    }

    public function renderReports() {
        // Get current year for default filter
        $year = date('Y');
        
        // Get service request statistics
        $serviceRequestsByStatus = $this->reportsModel->getServiceRequestsByStatus();
        $serviceRequestsByType = $this->reportsModel->getServiceRequestsByType();
        $serviceRequestsByMonth = $this->reportsModel->getServiceRequestsByMonth($year);
        
        // Get product booking statistics
        $productBookingsByStatus = $this->reportsModel->getProductBookingsByStatus();
        $productBookingsByMonth = $this->reportsModel->getProductBookingsByMonth($year);
        $topSellingProducts = $this->reportsModel->getTopSellingProducts(5);
        
        // Get technician performance data
        $technicianPerformance = $this->reportsModel->getTechnicianPerformance();
        
        // Get revenue data
        $revenueByMonth = $this->reportsModel->getRevenueByMonth($year);
        
        // Pass data to the view
        $this->render('admin/reports', [
            'serviceRequestsByStatus' => $serviceRequestsByStatus,
            'serviceRequestsByType' => $serviceRequestsByType,
            'serviceRequestsByMonth' => $serviceRequestsByMonth,
            'productBookingsByStatus' => $productBookingsByStatus,
            'productBookingsByMonth' => $productBookingsByMonth,
            'topSellingProducts' => $topSellingProducts,
            'technicianPerformance' => $technicianPerformance,
            'revenueByMonth' => $revenueByMonth,
            'currentYear' => $year
        ]);
    }

    public function getReportsByYear($year) {
        // Validate year
        if (!is_numeric($year) || $year < 2000 || $year > date('Y') + 1) {
            return $this->jsonError('Invalid year', 400);
        }
        
        // Get data for the specified year
        $serviceRequestsByMonth = $this->reportsModel->getServiceRequestsByMonth($year);
        $productBookingsByMonth = $this->reportsModel->getProductBookingsByMonth($year);
        $revenueByMonth = $this->reportsModel->getRevenueByMonth($year);
        
        // Format the data for charts
        $monthlyServiceData = array_fill(1, 12, 0);
        if (!empty($serviceRequestsByMonth)) {
            foreach ($serviceRequestsByMonth as $monthly) {
                $monthlyServiceData[(int)$monthly['month']] = (int)$monthly['count'];
            }
        }
        
        $monthlyProductData = array_fill(1, 12, 0);
        if (!empty($productBookingsByMonth)) {
            foreach ($productBookingsByMonth as $monthly) {
                $monthlyProductData[(int)$monthly['month']] = (int)$monthly['count'];
            }
        }
        
        $monthlyRevenueData = array_fill(1, 12, 0);
        if (!empty($revenueByMonth)) {
            foreach ($revenueByMonth as $monthly) {
                $monthlyRevenueData[(int)$monthly['month']] = (float)$monthly['total_revenue'];
            }
        }
        
        // Return the formatted data
        header('Content-Type: application/json');
        return $this->jsonSuccess([
            'serviceRequestsByMonth' => array_values($monthlyServiceData),
            'productBookingsByMonth' => array_values($monthlyProductData),
            'revenueByMonth' => array_values($monthlyRevenueData),
            'year' => $year
        ]);
    }

    public function renderAddProduct() {
        $this->render('admin/add-product');
    }

    public function renderUserManagement() {
        $this->render('admin/user-management');
    }
    
    public function renderProductManagement() {
        $this->render('admin/product-management');
    }
    
    public function renderInventoryManagement() {
        $this->render('admin/inventory-management');
    }
    
    public function renderWarehouseManagement() {
        $this->render('admin/warehouse-management');
    }
    
    public function renderProductOrders() {
        $this->render('admin/product-orders');
    }

    public function renderTechnician() {
        
        $this->render('admin/technician');
    }
    
    // API endpoint to get all technicians
    public function getTechnicians()
    {
        $userModel = $this->loadModel('UserModel');
        $technicians = $userModel->getTechnicians();
        
        $this->jsonSuccess($technicians);
    }
    
    // API endpoint to get a specific technician's assignments
    public function getTechnicianAssignments($id)
    {
        $bookingAssignmentModel = $this->loadModel('BookingAssignmentModel');
        $productAssignmentModel = $this->loadModel('ProductAssignmentModel');
        
        // Check if type parameter is provided
        $type = $_GET['type'] ?? null;
        
        if ($type === 'service') {
            // Return only service assignments
            $serviceAssignments = $bookingAssignmentModel->getAssignmentsForTechnician($id);
            $this->jsonSuccess(['data' => $serviceAssignments]);
        } else if ($type === 'product') {
            // Return only product assignments
            $productAssignments = $productAssignmentModel->getAssignmentsByTechnician($id);
            $this->jsonSuccess(['data' => $productAssignments]);
        } else {
            // Return both types of assignments
            $serviceAssignments = $bookingAssignmentModel->getAssignmentsForTechnician($id);
            $productAssignments = $productAssignmentModel->getAssignmentsByTechnician($id);
            
            $this->jsonSuccess([
                'serviceAssignments' => $serviceAssignments,
                'productAssignments' => $productAssignments
            ]);
        }
    }
    
    // API endpoint to assign a service request to a technician
    public function assignServiceRequest()
    {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request method');
        }
        
        $input = $this->getJsonInput();
        
        $bookingId = $input['booking_id'] ?? null;
        $technicianId = $input['technician_id'] ?? null;
        
        if (!$bookingId || !$technicianId) {
            $this->jsonError('Missing required parameters');
        }
        
        $bookingAssignmentModel = $this->loadModel('BookingAssignmentModel');
        
        // Check for scheduling conflicts
        $conflictCheck = $bookingAssignmentModel->hasSchedulingConflict($technicianId, $bookingId);
        if ($conflictCheck['conflict']) {
            $this->jsonError($conflictCheck['message']);
        }
        
        $data = [
            'ba_booking_id' => $bookingId,
            'ba_technician_id' => $technicianId,
            'ba_status' => 'assigned'
        ];
        
        $result = $bookingAssignmentModel->addAssignment($data);
        
        if ($result) {
            $this->jsonSuccess(['message' => 'Service request assigned successfully']);
        } else {
            $this->jsonError('Failed to assign service request');
        }
    }
    
    // API endpoint to assign a product booking to a technician
    public function assignProductBooking()
    {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request method');
        }
        
        $input = $this->getJsonInput();
        
        $bookingId = $input['booking_id'] ?? null;
        $technicianId = $input['technician_id'] ?? null;
        
        if (!$bookingId || !$technicianId) {
            $this->jsonError('Missing required parameters');
        }
        
        $productAssignmentModel = $this->loadModel('ProductAssignmentModel');
        
        // Check for scheduling conflicts
        $conflictCheck = $productAssignmentModel->hasSchedulingConflict($technicianId, $bookingId);
        if ($conflictCheck['conflict']) {
            $this->jsonError($conflictCheck['message']);
        }
        
        $data = [
            'pa_order_id' => $bookingId,
            'pa_technician_id' => $technicianId,
            'pa_status' => 'assigned'
        ];
        
        $result = $productAssignmentModel->createAssignment($data);
        
        if ($result) {
            $this->jsonSuccess(['message' => 'Product booking assigned successfully']);
        } else {
            $this->jsonError('Failed to assign product booking');
        }
    }
    
    // API endpoint to get a specific technician's details
    public function getTechnician($id)
    {
        $userModel = $this->loadModel('UserModel');
        $technician = $userModel->getTechnicianDetails($id);
        
        if (!$technician) {
            $this->jsonError('Technician not found', 404);
        }
        
        $this->jsonSuccess($technician);
    }
    
    // API endpoint to update a technician's details
    public function updateTechnician($id)
    {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request method');
        }
        
        $technicianModel = $this->loadModel('TechnicianModel');
        
        // Get post data
        $isAvailable = isset($_POST['te_is_available']) ? (int)$_POST['te_is_available'] : null;
        
        if ($isAvailable === null) {
            $this->jsonError('Missing required parameters');
        }
        
        $data = [
            'te_is_available' => $isAvailable
        ];
        
        $result = $technicianModel->update($data, 'te_account_id = :id', ['id' => $id]);
        
        if ($result) {
            $this->jsonSuccess(['message' => 'Technician updated successfully']);
        } else {
            $this->jsonError('Failed to update technician');
        }
    }
    
    // API endpoint to update a service assignment status
    public function updateServiceAssignment()
    {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request method');
        }
        
        $bookingAssignmentModel = $this->loadModel('BookingAssignmentModel');
        
        // Get post data
        $assignmentId = $_POST['assignment_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $notes = $_POST['notes'] ?? null;
        
        if (!$assignmentId || !$status) {
            $this->jsonError('Missing required parameters');
        }
        
        $data = [
            'ba_status' => $status,
            'ba_notes' => $notes
        ];
        
        // If status is in-progress, set started_at timestamp
        if ($status === 'in-progress' && empty($_POST['started_at'])) {
            $data['ba_started_at'] = date('Y-m-d H:i:s');
        }
        
        // If status is completed, set completed_at timestamp
        if ($status === 'completed' && empty($_POST['completed_at'])) {
            $data['ba_completed_at'] = date('Y-m-d H:i:s');
        }
        
        $result = $bookingAssignmentModel->updateAssignment($assignmentId, $data);
        
        if ($result) {
            $this->jsonSuccess(['message' => 'Service assignment updated successfully']);
        } else {
            $this->jsonError('Failed to update service assignment');
        }
    }
    
    // API endpoint to update a product assignment status
    public function updateProductAssignment()
    {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request method');
        }
        
        $productAssignmentModel = $this->loadModel('ProductAssignmentModel');
        
        // Get post data
        $assignmentId = $_POST['assignment_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $notes = $_POST['notes'] ?? null;
        
        if (!$assignmentId || !$status) {
            $this->jsonError('Missing required parameters');
        }
        
        $data = [
            'pa_status' => $status,
            'pa_notes' => $notes
        ];
        
        // If status is in-progress, set started_at timestamp
        if ($status === 'in-progress' && empty($_POST['started_at'])) {
            $data['pa_started_at'] = date('Y-m-d H:i:s');
        }
        
        // If status is completed, set completed_at timestamp
        if ($status === 'completed' && empty($_POST['completed_at'])) {
            $data['pa_completed_at'] = date('Y-m-d H:i:s');
        }
        
        $result = $productAssignmentModel->updateAssignment($assignmentId, $data);
        
        if ($result) {
            $this->jsonSuccess(['message' => 'Product assignment updated successfully']);
        } else {
            $this->jsonError('Failed to update product assignment');
        }
    }

    // Update product booking
    public function updateProductBooking()
    {
        if (!$this->isAdmin()) {
            $this->jsonError('Unauthorized access', 403);
            return;
        }

        $data = $this->getJsonInput();
        
        if (empty($data) || empty($data['bookingId'])) {
            $this->jsonError('Missing booking ID', 400);
            return;
        }

        $bookingId = $data['bookingId'];
        
        // Get the current booking to check status changes
        $booking = $this->productBookingModel->getBookingById($bookingId);
        if (!$booking) {
            $this->jsonError('Booking not found', 404);
            return;
        }
        
        $updateData = [];

        // Add fields to update if they are provided
        if (!empty($data['status'])) {
            $updateData['PB_STATUS'] = $data['status'];
        }

        if (!empty($data['priceType'])) {
            $updateData['PB_PRICE_TYPE'] = $data['priceType'];
        }
        
        if (!empty($data['unitPrice'])) {
            $updateData['PB_UNIT_PRICE'] = $data['unitPrice'];
        }

        if (!empty($data['preferredDate'])) {
            $updateData['PB_PREFERRED_DATE'] = $data['preferredDate'];
        }

        if (!empty($data['preferredTime'])) {
            $updateData['PB_PREFERRED_TIME'] = $data['preferredTime'];
        }
        
        if (!empty($data['warehouseId'])) {
            $updateData['PB_WAREHOUSE_ID'] = $data['warehouseId'];
        }

        if (isset($data['description'])) {
            $updateData['PB_DESCRIPTION'] = $data['description'];
        }

        if (empty($updateData)) {
            $this->jsonError('No data to update', 400);
            return;
        }

        try {
            $this->productBookingModel->beginTransaction();

            // Update booking
            $success = $this->productBookingModel->updateBooking($bookingId, $updateData);
            if (!$success) {
                $this->productBookingModel->rollback();
                $this->jsonError('Failed to update booking', 500);
                return;
            }

            // Handle technician assignments if provided
            if (isset($data['technicians'])) {
                // First, get current assignments
                $currentAssignments = $this->productAssignmentModel->getAssignmentsByOrderId($bookingId);
                $currentTechIds = array_column($currentAssignments, 'pa_technician_id');
                
                // Remove all current assignments if technicians array is empty
                if (empty($data['technicians'])) {
                    $this->productBookingModel->removeAllTechnicians($bookingId);
                } else {
                    // Process new assignments
                    $newTechIds = array_column($data['technicians'], 'id');
                    
                    // Technicians to add (in new but not in current)
                    $techsToAdd = array_diff($newTechIds, $currentTechIds);
                    foreach ($techsToAdd as $techId) {
                        // Check for scheduling conflicts before adding new technician
                        $conflictCheck = $this->productAssignmentModel->hasSchedulingConflict($techId, $bookingId);
                        if ($conflictCheck['conflict']) {
                            $this->productBookingModel->rollback();
                            $this->jsonError('Scheduling conflict: ' . $conflictCheck['message'] . ' for technician ID ' . $techId);
                            return;
                        }
                        
                        $techData = array_filter($data['technicians'], function($tech) use ($techId) {
                            return $tech['id'] == $techId;
                        });
                        $techData = reset($techData);
                        
                        $assignmentData = [
                            'pa_order_id' => $bookingId,
                            'pa_technician_id' => $techId,
                            'pa_notes' => $techData['notes'] ?? ''
                        ];
                        
                        $this->productAssignmentModel->createAssignment($assignmentData);
                    }
                    
                    // Technicians to remove (in current but not in new)
                    $techsToRemove = array_diff($currentTechIds, $newTechIds);
                    foreach ($techsToRemove as $techId) {
                        $this->productAssignmentModel->deleteAssignmentByOrderAndTechnician($bookingId, $techId);
                    }
                    
                    // Update notes for existing techs
                    $techsToUpdate = array_intersect($currentTechIds, $newTechIds);
                    foreach ($techsToUpdate as $techId) {
                        $techData = array_filter($data['technicians'], function($tech) use ($techId) {
                            return $tech['id'] == $techId;
                        });
                        $techData = reset($techData);
                        
                        if (isset($techData['notes'])) {
                            $assignmentData = [
                                'pa_notes' => $techData['notes']
                            ];
                            
                            $this->productAssignmentModel->updateAssignmentNotes($bookingId, $techId, $assignmentData);
                        }
                    }
                }
            }

            $this->productBookingModel->commit();
            $this->jsonSuccess(['bookingId' => $bookingId], 'Product booking updated successfully');
            
        } catch (\Exception $e) {
            $this->productBookingModel->rollback();
            error_log("Error updating product booking: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->jsonError('Error updating product booking: ' . $e->getMessage(), 500);
        }
    }

    // Check if current user is an admin
    private function isAdmin()
    {
        // Get the current user role from the session
        $userRole = $_SESSION['user_role'] ?? null;
        
        // Check if user is an admin
        return $userRole === 'admin';
    }

    // API endpoint to get a technician's schedule
    public function getTechnicianSchedule()
    {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request method');
        }
        
        $input = $this->getJsonInput();
        
        $technicianId = $input['technician_id'] ?? null;
        $startDate = $input['start_date'] ?? date('Y-m-d');
        $endDate = $input['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
        
        if (!$technicianId) {
            $this->jsonError('Missing required parameters');
        }
        
        $technicianModel = $this->loadModel('TechnicianModel');
        $schedule = $technicianModel->getTechnicianSchedule($technicianId, $startDate, $endDate);
        
        if (isset($schedule['error'])) {
            $this->jsonError($schedule['error']);
        }
        
        $this->jsonSuccess([
            'technician_id' => $technicianId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'schedule' => $schedule
        ]);
    }
}