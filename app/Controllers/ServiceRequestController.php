<?php

namespace App\Controllers;

class ServiceRequestController extends BaseController
{
    private $serviceModel;
    private $serviceTypeModel;
    private $technicianModel;
    private $bookingAssignmentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->technicianModel = $this->loadModel('TechnicianModel');
        $this->serviceTypeModel = $this->loadModel('ServiceRequestTypeModel');
        $this->serviceModel = $this->loadModel('ServiceRequestModel');
        $this->bookingAssignmentModel = $this->loadModel('BookingAssignmentModel');
    }
    
    /**
     * Get all service bookings for the current user
     * API endpoint for ServiceRequestsManager.js
     */
    public function getUserServiceBookings()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $this->jsonError('User not authenticated', 401);
            return;
        }
        
        $customerId = $_SESSION['user_id'];
        $bookings = $this->serviceModel->getCustomerBookings($customerId);
        
        // Enhance bookings with service type information
        foreach ($bookings as &$booking) {
            $serviceType = $this->serviceTypeModel->getServiceTypeById($booking['sb_service_type_id']);
            if ($serviceType) {
                $booking['ST_NAME'] = $serviceType['st_name'];
                $booking['ST_DESCRIPTION'] = $serviceType['st_description'];
                $booking['ST_CODE'] = $serviceType['st_code'];
            }
            
            // Convert DB column names to the expected format for the frontend
            $booking['SB_ID'] = $booking['sb_id'];
            $booking['SB_STATUS'] = $booking['sb_status'];
            $booking['SB_CREATED_AT'] = $booking['sb_created_at'];
            $booking['SB_PREFERRED_DATE'] = $booking['sb_preferred_date'];
            $booking['SB_PREFERRED_TIME'] = $booking['sb_preferred_time'];
            $booking['SB_ADDRESS'] = $booking['sb_address'];
            $booking['SB_DESCRIPTION'] = $booking['sb_description'];
            $booking['SB_ESTIMATED_COST'] = $booking['sb_estimated_cost'] ?? null;
            $booking['SB_PRIORITY'] = $booking['sb_priority'] ?? 'normal';
        }
        
        $this->jsonSuccess($bookings);
    }
    
    /**
     * Get details for a specific service booking
     * API endpoint for ServiceRequestsManager.js
     */
    public function getUserServiceBookingDetails($id)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $this->jsonError('User not authenticated', 401);
            return;
        }
        
        $customerId = $_SESSION['user_id'];
        $booking = $this->serviceModel->getBookingWithDetails($id);
        
        // Check if booking exists and belongs to the current user
        if (!$booking || $booking['sb_customer_id'] != $customerId) {
            $this->jsonError('Service booking not found or access denied', 404);
            return;
        }
        
        // Convert DB column names to the expected format for the frontend
        $result = [
            'SB_ID' => $booking['sb_id'],
            'SB_STATUS' => $booking['sb_status'],
            'SB_CREATED_AT' => $booking['sb_created_at'],
            'SB_PREFERRED_DATE' => $booking['sb_preferred_date'],
            'SB_PREFERRED_TIME' => $booking['sb_preferred_time'],
            'SB_ADDRESS' => $booking['sb_address'],
            'SB_DESCRIPTION' => $booking['sb_description'],
            'SB_ESTIMATED_COST' => $booking['sb_estimated_cost'] ?? null,
            'SB_PRIORITY' => $booking['sb_priority'] ?? 'normal',
            'ST_NAME' => $booking['service_name'],
            'ST_DESCRIPTION' => $booking['service_description'],
            'CUSTOMER_NAME' => $booking['customer_first_name'] . ' ' . $booking['customer_last_name'],
            'CUSTOMER_EMAIL' => $booking['customer_email'],
            'CUSTOMER_PHONE' => $booking['customer_phone']
        ];
        
        $this->jsonSuccess($result);
    }
    
    /**
     * Display services page
     */
    public function index()
    {
        // Get all active service types
        $serviceTypes = $this->serviceTypeModel->getActiveServiceTypes();
        
        // Get user data from session
        $userData = [
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_name' => $_SESSION['user_name'] ?? null,
            'user_email' => $_SESSION['user_email'] ?? null,
            'user_role' => $_SESSION['user_role'] ?? null,
        ];
        
        // Render the services view
        $this->render('services', [
            'serviceTypes' => $serviceTypes,
            'userData' => $userData
        ]);
    }
    
    /**
     * Handle service booking form submission
     */
    public function bookService()
    {
        
        // Get JSON input from request body
        $input = $this->getJsonInput();
        
        // Validate required fields
        $requiredFields = [
            'serviceSelect',
            'preferredDate',
            'preferredTime',
            'fullName',
            'emailAddress',
            'address'
        ];
        
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                $this->jsonError("The {$field} field is required", 400);
                return;
            }
        }
        
        // Validate service type exists
        $serviceType = $this->serviceTypeModel->getServiceTypeByCode($input['serviceSelect']);
        if (!$serviceType) {
            $this->jsonError("Invalid service type selected", 400);
            return;
        }
        
        // Prepare booking data
        $bookingData = [
            'sb_customer_id' => $_SESSION['user_id'],
            'sb_service_type_id' => $serviceType['st_id'],
            'sb_preferred_date' => $input['preferredDate'],
            'sb_preferred_time' => $input['preferredTime'],
            'sb_address' => $input['address'],
            'sb_description' => !empty($input['serviceDescription']) ? $input['serviceDescription'] : 'No description provided',
            'sb_status' => 'pending'
        ];
        
        // Create the booking
        $success = $this->serviceModel->createBooking($bookingData);
        
        if ($success) {
            $this->jsonSuccess(
                ['status' => 'pending'],
                'Your service request has been submitted successfully. We will contact you soon.'
            );
        } else {
            $this->jsonError("Failed to submit service request. Please try again later.", 500);
        }
    }
    
    /**
     * Display user's service bookings
     */
    public function myBookings()
    {
        // Check if user is logged in
        $userId = $_SESSION['user_id'] ?? null;
        
        // Get user's bookings
        $bookings = $this->serviceModel->getCustomerBookings($userId);
        
        // Render the bookings view
        $this->render('user/bookings', [
            'bookings' => $bookings
        ]);
    }
    
    /**
     * Cancel a booking
     */
    public function cancelBooking($id = null)
    {
        // Check if user is logged in
        $userId = $_SESSION['user_id'] ?? null;
 
        // Validate booking ID
        if (!$id) {
            if ($this->isAjax()) {
                $this->jsonError("Booking ID is required", 400);
            } else {
                $this->redirect('/user/bookings');
            }
            return;
        }
        
        // Get the booking
        $booking = $this->serviceModel->find($id);
        
        // Check if booking exists and belongs to the user
        if (!$booking || $booking['sb_customer_id'] != $userId) {
            if ($this->isAjax()) {
                $this->jsonError("Booking not found or access denied", 404);
            } else {
                $this->redirect('/user/bookings');
            }
            return;
        }
        
        // Cancel the booking
        $success = $this->serviceModel->cancelBooking($id);
        
        if ($this->isAjax()) {
            if ($success) {
                $this->jsonSuccess(
                    ['status' => 'cancelled'],
                    'Your booking has been cancelled successfully'
                );
            } else {
                $this->jsonError("Failed to cancel booking. Please try again later.", 500);
            }
        } else {
            if ($success) {
                // Set flash message
                $_SESSION['flash_message'] = 'Your booking has been cancelled successfully';
                $_SESSION['flash_type'] = 'success';
            } else {
                // Set flash message
                $_SESSION['flash_message'] = 'Failed to cancel booking. Please try again later.';
                $_SESSION['flash_type'] = 'danger';
            }
            
            // Redirect back to bookings page
            $this->redirect('/user/bookings');
        }
    }
    
    /**
     * Admin: Update booking status
     */
    public function updateBookingStatus()
    {
        // Check if user is admin
        if (!$this->checkPermission('admin')) {
            if ($this->isAjax()) {
                $this->jsonError("Access denied", 403);
            } else {
                $this->redirect('/dashboard');
            }
            return;
        }
        
        // Check if the request is AJAX
        if (!$this->isAjax()) {
            $this->redirect('/admin/bookings');
            return;
        }
        
        // Get JSON input from request body
        $input = $this->getJsonInput();
        
        // Validate required fields
        if (empty($input['bookingId']) || empty($input['status'])) {
            $this->jsonError("Booking ID and status are required", 400);
            return;
        }
        
        // Validate status
        $validStatuses = ['pending', 'confirmed', 'in-progress', 'completed', 'cancelled'];
        if (!in_array($input['status'], $validStatuses)) {
            $this->jsonError("Invalid status", 400);
            return;
        }
        
        // Update booking status
        $success = $this->serviceModel->updateBookingStatus($input['bookingId'], $input['status']);
        
        if ($success) {
            $this->jsonSuccess(
                ['status' => $input['status']],
                'Booking status updated successfully'
            );
        } else {
            $this->jsonError("Failed to update booking status. Please try again later.", 500);
        }
    }
    
    /**
     * Admin: Manage service types
     */
    public function manageServiceTypes()
    {
        // Check if user is admin
        if (!$this->checkPermission('admin')) {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get all service types
        $serviceTypes = $this->serviceTypeModel->all();
        
        // Render the service types management view
        $this->render('admin/service-types', [
            'serviceTypes' => $serviceTypes
        ]);
    }
    
    /**
     * Admin: Add or update service type
     */
    public function saveServiceType()
    {
        // Check if user is admin
        if (!$this->checkPermission('admin')) {
            if ($this->isAjax()) {
                $this->jsonError("Access denied", 403);
            } else {
                $this->redirect('/dashboard');
            }
            return;
        }
        
        // Check if the request is AJAX
        if (!$this->isAjax()) {
            $this->redirect('/admin/service-types');
            return;
        }
        
        // Get JSON input from request body
        $input = $this->getJsonInput();
        
        // Validate required fields
        if (empty($input['code']) || empty($input['name'])) {
            $this->jsonError("Service type code and name are required", 400);
            return;
        }
        
        // Prepare service type data
        $serviceTypeData = [
            'ST_CODE' => $input['code'],
            'ST_NAME' => $input['name'],
            'ST_DESCRIPTION' => $input['description'] ?? '',
            'ST_IS_ACTIVE' => isset($input['isActive']) ? (bool) $input['isActive'] : true
        ];
        
        // Check if updating or creating
        if (!empty($input['id'])) {
            // Update existing service type
            $success = $this->serviceTypeModel->updateServiceType($input['id'], $serviceTypeData);
            $message = 'Service type updated successfully';
        } else {
            // Check if service type with this code already exists
            $existingType = $this->serviceTypeModel->getServiceTypeByCode($input['code']);
            if ($existingType) {
                $this->jsonError("A service type with this code already exists", 400);
                return;
            }
            
            // Create new service type
            $success = $this->serviceTypeModel->createServiceType($serviceTypeData);
            $message = 'Service type created successfully';
        }
        
        if ($success) {
            $this->jsonSuccess(
                ['status' => 'saved'],
                $message
            );
        } else {
            $this->jsonError("Failed to save service type. Please try again later.", 500);
        }
    }
    
    /**
     * Admin: Toggle service type active status
     */
    public function toggleServiceTypeStatus()
    {
        // Check if user is admin
        if (!$this->checkPermission('admin')) {
            if ($this->isAjax()) {
                $this->jsonError("Access denied", 403);
            } else {
                $this->redirect('/dashboard');
            }
            return;
        }
        
        // Check if the request is AJAX
        if (!$this->isAjax()) {
            $this->redirect('/admin/service-types');
            return;
        }
        
        // Get JSON input from request body
        $input = $this->getJsonInput();
        
        // Validate required fields
        if (empty($input['id']) || !isset($input['isActive'])) {
            $this->jsonError("Service type ID and active status are required", 400);
            return;
        }
        
        // Toggle service type status
        $success = $this->serviceTypeModel->toggleServiceTypeStatus($input['id'], (bool) $input['isActive']);
        
        if ($success) {
            $this->jsonSuccess(
                ['status' => $input['isActive'] ? 'active' : 'inactive'],
                'Service type status updated successfully'
            );
        } else {
            $this->jsonError("Failed to update service type status. Please try again later.", 500);
        }
    }
    
    /**
     * Get all service requests for admin
     * API endpoint for admin service request management
     */
    public function getAdminServiceRequests()
    {
        // Check if user is admin
        if (!$this->checkPermission('admin')) {
            $this->jsonError('Access denied', 403);
            return;
        }
        
        // Get query parameters for filtering
        $filters = [];
        
        // Status filter
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $filters['sb_status'] = $_GET['status'];
        }
        
        // Service type filter
        if (isset($_GET['service_type_id']) && !empty($_GET['service_type_id'])) {
            $filters['sb_service_type_id'] = $_GET['service_type_id'];
        }
        
        // Priority filter
        if (isset($_GET['priority']) && !empty($_GET['priority'])) {
            $filters['sb_priority'] = $_GET['priority'];
        }
        
        // Technician filter - Added to fix the technician filtering issue
        if (isset($_GET['technician_id']) && !empty($_GET['technician_id'])) {
            $filters['technician_id'] = $_GET['technician_id'];
        } elseif (isset($_GET['has_technician'])) {
            $filters['has_technician'] = filter_var($_GET['has_technician'], FILTER_VALIDATE_BOOLEAN);
        }
        
        // Get bookings with the specified filters
        $bookings = empty($filters) 
            ? $this->serviceModel->getAllActiveBookings() 
            : $this->serviceModel->getBookingsByCriteria($filters);
        
        // Enhance booking data with additional information
        $enhancedBookings = [];
        
        foreach ($bookings as $booking) {
            // Get service type information
            $serviceType = $this->serviceTypeModel->getServiceTypeById($booking['sb_service_type_id']);
            
            // Get customer information
            $customerInfo = $this->getUserInfo($booking['sb_customer_id']);
            
            // Get assigned technicians
            $assignedTechnicians = $this->bookingAssignmentModel->getAssignmentsForBooking($booking['sb_id']);
            $technicians = [];
            
            foreach ($assignedTechnicians as $assignment) {
                $technicianInfo = $this->getUserInfo($assignment['ba_technician_id']);
                
                if ($technicianInfo) {
                    $technicians[] = [
                        'id' => $assignment['ba_technician_id'],
                        'name' => $technicianInfo['ua_first_name'] . ' ' . $technicianInfo['ua_last_name'],
                        'profile_url' => $technicianInfo['ua_profile_url'] ?? '/assets/images/user-profile/default-profile.png',
                        'status' => $assignment['ba_status'],
                        'notes' => $assignment['ba_notes']
                    ];
                }
            }
            
            // Add the enhanced data
            $enhancedBooking = $booking;
            $enhancedBooking['service_name'] = $serviceType ? $serviceType['st_name'] : 'Unknown';
            $enhancedBooking['customer_name'] = $customerInfo 
                ? $customerInfo['ua_first_name'] . ' ' . $customerInfo['ua_last_name'] 
                : 'Unknown';
            $enhancedBooking['customer_email'] = $customerInfo ? $customerInfo['ua_email'] : '';
            $enhancedBooking['customer_phone'] = $customerInfo ? $customerInfo['ua_phone_number'] : '';
            $enhancedBooking['customer_profile_url'] = $customerInfo ? $customerInfo['ua_profile_url'] : '/assets/images/user-profile/default-profile.png';
            $enhancedBooking['technicians'] = $technicians;
            
            $enhancedBookings[] = $enhancedBooking;
        }
        
        $this->jsonSuccess($enhancedBookings);
    }
    
    /**
     * Get details for a specific service request (admin view)
     * API endpoint for admin service request management
     */
    public function getAdminServiceRequestDetails($id)
    {
        // Check if user is admin
        if (!$this->checkPermission('admin')) {
            $this->jsonError('Access denied', 403);
            return;
        }
        
        // Get the booking details
        $booking = $this->serviceModel->getBookingWithDetails($id);
        
        if (!$booking) {
            $this->jsonError('Service request not found', 404);
            return;
        }
        
        // Debug log to see what's in the booking data
        error_log("Booking data: " . json_encode($booking));
        
        // Get assigned technicians
        $assignedTechnicians = $this->bookingAssignmentModel->getAssignmentsForBooking($id);
        
        // Debug log to see what's in the assignments
        error_log("Assigned technicians: " . json_encode($assignedTechnicians));
        
        $technicians = [];
        
        foreach ($assignedTechnicians as $assignment) {
            $technicianInfo = $this->getUserInfo($assignment['ba_technician_id']);
            
            // Debug log for each technician info
            error_log("Technician info for ID {$assignment['ba_technician_id']}: " . json_encode($technicianInfo));
            
            if ($technicianInfo) {
                $technicians[] = [
                    'id' => $assignment['ba_technician_id'],
                    'name' => $technicianInfo['ua_first_name'] . ' ' . $technicianInfo['ua_last_name'],
                    'profile_url' => $technicianInfo['ua_profile_url'] ?? '/assets/images/user-profile/default-profile.png',
                    'email' => $technicianInfo['ua_email'] ?? '',
                    'phone' => $technicianInfo['ua_phone_number'] ?? '',
                    'status' => $assignment['ba_status'],
                    'assigned_at' => $assignment['ba_assigned_at'],
                    'notes' => $assignment['ba_notes']
                ];
            }
        }
        
        // Debug log for the final technicians array
        error_log("Final technicians array: " . json_encode($technicians));
        
        // Get customer profile URL
        $customerInfo = $this->getUserInfo($booking['sb_customer_id']);
        $customerProfileUrl = $customerInfo ? $customerInfo['ua_profile_url'] : '/assets/images/user-profile/default-profile.png';
        
        // Format the response data
        $result = [
            'sb_id' => $booking['sb_id'],
            'sb_customer_id' => $booking['sb_customer_id'],
            'sb_service_type_id' => $booking['sb_service_type_id'],
            'sb_preferred_date' => $booking['sb_preferred_date'],
            'sb_preferred_time' => $booking['sb_preferred_time'],
            'sb_status' => $booking['sb_status'],
            'sb_priority' => $booking['sb_priority'],
            'sb_estimated_cost' => $booking['sb_estimated_cost'],
            'sb_address' => $booking['sb_address'],
            'sb_description' => $booking['sb_description'],
            'sb_created_at' => $booking['sb_created_at'],
            'sb_updated_at' => $booking['sb_updated_at'],
            'service_name' => $booking['service_name'],
            'service_description' => $booking['service_description'],
            'customer_name' => $booking['customer_first_name'] . ' ' . $booking['customer_last_name'],
            'customer_email' => $booking['customer_email'],
            'customer_phone' => $booking['customer_phone'],
            'customer_profile_url' => $customerProfileUrl,
            'technicians' => $technicians
        ];
        
        $this->jsonSuccess($result);
    }
    
    /**
     * Update a service request
     * API endpoint for admin service request management
     */
    public function updateServiceRequest()
    {
        // Check if user is admin
        if (!$this->checkPermission('admin')) {
            $this->jsonError('Access denied', 403);
            return;
        }
        
        // Get JSON input
        $input = $this->getJsonInput();
        
        // Validate required fields
        if (empty($input['bookingId'])) {
            $this->jsonError('Booking ID is required', 400);
            return;
        }
        
        $bookingId = $input['bookingId'];
        
        // Start a transaction
        $this->pdo->beginTransaction();
        
        try {
            // Prepare data for update
            $updateData = [];
            
            // Update status if provided
            if (isset($input['status'])) {
                $updateData['sb_status'] = $input['status'];
            }
            
            // Update priority if provided
            if (isset($input['priority'])) {
                $updateData['sb_priority'] = $input['priority'];
            }
            
            // Update estimated cost if provided
            if (isset($input['estimatedCost'])) {
                $updateData['sb_estimated_cost'] = $input['estimatedCost'];
            }
            
            // Update preferred date and time if provided (for rescheduling)
            if (isset($input['preferredDate']) && !empty($input['preferredDate'])) {
                $updateData['sb_preferred_date'] = $input['preferredDate'];
            }
            
            if (isset($input['preferredTime']) && !empty($input['preferredTime'])) {
                $updateData['sb_preferred_time'] = $input['preferredTime'];
            }
            
            // Update the booking
            if (!empty($updateData)) {
                $success = $this->serviceModel->updateBooking($bookingId, $updateData);
                
                if (!$success) {
                    throw new \Exception('Failed to update service request');
                }
            }
            
            // Handle technician assignments if provided
            if (isset($input['technicians'])) {
                // Debug log
                error_log("Processing technicians: " . json_encode($input['technicians']));
                
                // First, get current assignments
                $currentAssignments = $this->bookingAssignmentModel->getAssignmentsForBooking($bookingId);
                $currentTechIds = array_column($currentAssignments, 'ba_technician_id');
                
                error_log("Current technician IDs: " . json_encode($currentTechIds));
                
                // Extract new technician IDs from the input objects
                $newTechnicianData = $input['technicians']; // This is an array of objects {id: x, notes: y}
                $newTechIds = array_map(function($tech) { return $tech['id']; }, $newTechnicianData);

                $techToAddUpdate = [];
                foreach ($newTechnicianData as $techData) {
                    $techToAddUpdate[$techData['id']] = $techData['notes'];
                }
                
                // Remove all current technician assignments and add new ones
                // This is the same approach used in ProductBookingModel
                error_log("Removing all current technician assignments");
                $this->bookingAssignmentModel->removeAllTechnicians($bookingId);
                
                // Add all technician assignments from the input
                foreach ($techToAddUpdate as $techId => $notes) {
                    // Check for scheduling conflicts first
                    error_log("Checking scheduling conflicts for technician: " . $techId);
                    $conflictCheck = $this->bookingAssignmentModel->hasSchedulingConflict($techId, $bookingId);
                    
                    if ($conflictCheck['conflict']) {
                        error_log("Scheduling conflict detected: " . $conflictCheck['message']);
                        throw new \Exception('Scheduling conflict: ' . $conflictCheck['message'] . ' for technician ID ' . $techId);
                    }
                    
                    // No conflicts, add assignment
                    error_log("Adding technician: " . $techId . " with notes: " . $notes);
                    $assignmentData = [
                        'ba_booking_id' => $bookingId,
                        'ba_technician_id' => $techId,
                        'ba_status' => 'assigned',
                        'ba_notes' => $notes, // Add notes here
                        'ba_assigned_at' => date('Y-m-d H:i:s')
                    ];
                    $this->bookingAssignmentModel->addAssignment($assignmentData);
                }
            }
            
            // Commit the transaction
            $this->pdo->commit();
            
            $this->jsonSuccess(
                ['status' => 'updated'],
                'Service request updated successfully'
            );
            
        } catch (\Exception $e) {
            // Rollback the transaction on error
            $this->pdo->rollback();
            $this->jsonError('Failed to update service request: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete a service request
     * API endpoint for admin service request management
     */
    public function deleteServiceRequest($id)
    {
        // Check if user is admin
        if (!$this->checkPermission('admin')) {
            $this->jsonError('Access denied', 403);
            return;
        }
        
        // Check if the booking exists
        $booking = $this->serviceModel->getBookingById($id);
        
        if (!$booking) {
            $this->jsonError('Service request not found', 404);
            return;
        }
        
        // Delete the booking (soft delete)
        $success = $this->serviceModel->deleteBooking($id);
        
        if ($success) {
            $this->jsonSuccess(
                ['status' => 'deleted'],
                'Service request deleted successfully'
            );
        } else {
            $this->jsonError('Failed to delete service request', 500);
        }
    }
    
    /**
     * Get all technicians
     * API endpoint for admin service request management
     */
    public function getTechnicians()
    {
        // We're only returning public information about technicians
        
        try {
            // This requires a join between technician and user_account tables
            $sql = "SELECT t.te_account_id, t.te_is_available, u.ua_first_name, u.ua_last_name, u.ua_email, u.ua_phone_number, u.ua_profile_url 
                    FROM technician t 
                    JOIN user_account u ON t.te_account_id = u.ua_id 
                    WHERE u.ua_is_active = true AND u.ua_role_id = (SELECT ur_id FROM user_role WHERE ur_name = 'technician')
                    ORDER BY u.ua_last_name, u.ua_first_name";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $technicians = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->jsonSuccess($technicians);
        } catch (\Exception $e) {
            $this->jsonError('Failed to retrieve technicians: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get all active service types
     * API endpoint for admin service request management
     */
    public function getServiceTypes()
    {
        // Get all active service types
        $serviceTypes = $this->serviceTypeModel->getActiveServiceTypes();
        
        $this->jsonSuccess($serviceTypes);
    }
    
    /**
     * Helper function to get user information by ID
     */
    private function getUserInfo($userId)
    {
        $sql = "SELECT * FROM user_account WHERE ua_id = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}