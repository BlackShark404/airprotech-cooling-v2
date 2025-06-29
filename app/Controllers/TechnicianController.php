<?php

namespace App\Controllers;

class TechnicianController extends BaseController
{
    private $technicianModel;
    private $bookingAssignmentModel;
    private $productAssignmentModel;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->technicianModel = $this->loadModel('TechnicianModel');
        $this->bookingAssignmentModel = $this->loadModel('BookingAssignmentModel');
        $this->productAssignmentModel = $this->loadModel('ProductAssignmentModel');
        $this->userModel = $this->loadModel('UserModel');
    }
    
    // Render technician dashboard
    public function dashboard()
    {
        // Ensure user is logged in and is a technician
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technician') {
            $this->redirect('/login');
        }
        
        $technicianId = $_SESSION['user_id'];
        
        // Get technician information
        $technician = $this->technicianModel->find($technicianId);
        
        // Get service request assignments
        $serviceAssignments = $this->bookingAssignmentModel->getAssignmentsByTechnician($technicianId);
        
        // Get product booking assignments
        $productAssignments = $this->productAssignmentModel->getAssignmentsByTechnician($technicianId);
        
        $this->render('technician/dashboard', [
            'technician' => $technician,
            'serviceAssignments' => $serviceAssignments,
            'productAssignments' => $productAssignments
        ]);
    }
    
    // API endpoint to get all technicians
    public function getAllTechnicians()
    {
        // Get all technicians with their user account information
        $technicians = $this->userModel->getTechnicians();
        
        $this->jsonSuccess($technicians);
    }
    
    // API endpoint to get a specific technician's assignments
    public function getTechnicianAssignments($technicianId)
    {
        // Get service request assignments
        $serviceAssignments = $this->bookingAssignmentModel->getAssignmentsByTechnician($technicianId);
        
        // Get product booking assignments
        $productAssignments = $this->productAssignmentModel->getAssignmentsByTechnician($technicianId);
        
        $this->jsonSuccess([
            'serviceAssignments' => $serviceAssignments,
            'productAssignments' => $productAssignments
        ]);
    }
    
    // API endpoint to update a service assignment status
    public function updateServiceAssignment()
    {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request method');
        }
        
        $input = $this->getJsonInput();
        
        $assignmentId = $input['assignment_id'] ?? null;
        $status = $input['status'] ?? null;
        $notes = $input['notes'] ?? null;
        
        if (!$assignmentId || !$status) {
            $this->jsonError('Missing required parameters');
        }
        
        $data = [
            'ba_status' => $status,
            'ba_notes' => $notes
        ];
        
        // If status is in-progress, set started_at timestamp
        if ($status === 'in-progress' && empty($input['started_at'])) {
            $data['ba_started_at'] = date('Y-m-d H:i:s');
        }
        
        // If status is completed, set completed_at timestamp
        if ($status === 'completed' && empty($input['completed_at'])) {
            $data['ba_completed_at'] = date('Y-m-d H:i:s');
        }
        
        $result = $this->bookingAssignmentModel->updateAssignment($assignmentId, $data);
        
        if ($result) {
            $this->jsonSuccess(['message' => 'Assignment updated successfully']);
        } else {
            $this->jsonError('Failed to update assignment');
        }
    }
    
    // API endpoint to update a product assignment status
    public function updateProductAssignment()
    {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request method');
        }
        
        $input = $this->getJsonInput();
        
        $assignmentId = $input['assignment_id'] ?? null;
        $status = $input['status'] ?? null;
        $notes = $input['notes'] ?? null;
        
        if (!$assignmentId || !$status) {
            $this->jsonError('Missing required parameters');
        }
        
        $data = [
            'pa_status' => $status,
            'pa_notes' => $notes
        ];
        
        // If status is in-progress, set started_at timestamp
        if ($status === 'in-progress' && empty($input['started_at'])) {
            $data['pa_started_at'] = date('Y-m-d H:i:s');
        }
        
        // If status is completed, set completed_at timestamp
        if ($status === 'completed' && empty($input['completed_at'])) {
            $data['pa_completed_at'] = date('Y-m-d H:i:s');
        }
        
        $result = $this->productAssignmentModel->updateAssignment($assignmentId, $data);
        
        if ($result) {
            $this->jsonSuccess(['message' => 'Assignment updated successfully']);
        } else {
            $this->jsonError('Failed to update assignment');
        }
    }
} 