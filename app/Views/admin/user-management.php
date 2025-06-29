<?php
// Set page title and active tab
$title = 'User Management - AirProtect';
$activeTab = 'user_management';

// Include base template
ob_start();
?>
<link rel="stylesheet" href="/assets/css/user-management.css">
<style>
    /* Custom DataTables styling */
    .dataTables_wrapper .dataTables_paginate {
        float: right;
        margin-top: 10px;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5em 1em;
        margin-left: 5px;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        background-color: #fff;
        color: #495057;
        cursor: pointer;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background-color: #0d6efd;
        color: #fff !important;
        border-color: #0d6efd;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background-color: #e9ecef;
        color: #495057 !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #6c757d !important;
        background-color: #fff;
        cursor: not-allowed;
    }
    
    .dataTables_wrapper .dataTables_info {
        padding-top: 15px;
    }
    
    .dataTables_wrapper .dataTables_filter {
        float: right;
        margin-bottom: 15px;
    }
    
    .table-title {
        margin-top: 10px;
        margin-bottom: 15px;
    }
    
    .dataTables_wrapper .dataTables_length {
        margin-top: 10px;
    }
    
    /* Action icon styles */
    .action-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        background-color: #f8f9fa;
        margin-right: 5px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .action-icon:hover {
        background-color: #e9ecef;
    }
    .action-icon-view {
        color: #007bff;
    }
    .action-icon-edit {
        color: #28a745;
    }
    .action-icon-delete {
        color: #dc3545;
    }
</style>

<!-- Main Content -->
<div class="container-fluid py-4 fade-in">
    
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">User Management</h1>
            <p class="text-muted">Create user accounts and manage their account status</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-lg me-2"></i>Add User
            </button>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="roleFilter" class="form-label small text-muted">Role</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-person-badge text-muted"></i></span>
                        <select class="form-select" id="roleFilter">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="technician">Technician</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label small text-muted">Status</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-toggle2-on text-muted"></i></span>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
 
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button id="resetFilters" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="card">
        
        <div class="card-body table-container">
            <!-- Using empty thead to let DataTables build it properly -->
            <table id="usersTable" class="table align-middle table-hover display nowrap" style="width:100%">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="addUserModalLabel">
                    <i class="bi bi-person-plus me-2 text-primary"></i>Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" class="form-control border-start-0" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
                            <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-toggle2-on text-muted"></i></span>
                                <select class="form-select border-start-0" id="is_active" name="is_active" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role_id" class="form-label">Role</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield text-muted"></i></span>
                                <select class="form-select border-start-0" id="role_id" name="role_id" required>
                                    <option value="">Select Role</option>
                                    <option value="3">Admin</option>
                                    <option value="2">Technician</option>
                                    <option value="1">Customer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveUserBtn">
                    <i class="bi bi-save me-2"></i>Save User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="editUserModalLabel">
                    <i class="bi bi-pencil-square me-2 text-warning"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_first_name" class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="edit_first_name" name="first_name" required readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_last_name" class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="edit_last_name" name="last_name" required readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" class="form-control border-start-0" id="edit_email" name="email" required readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_is_active" class="form-label">Status</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-toggle2-on text-muted"></i></span>
                                <select class="form-select border-start-0" id="edit_is_active" name="is_active" required>
                                    <option value="true">Active</option>
                                    <option value="false">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_role_id" class="form-label">Role</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield text-muted"></i></span>
                                <select class="form-select border-start-0" id="edit_role_id" name="role_id" required disabled>
                                    <option value="">Select Role</option>
                                    <option value="3">Admin</option>
                                    <option value="2">Technician</option>
                                    <option value="1">Customer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateUserBtn">
                    <i class="bi bi-save me-2"></i>Update User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="viewUserModalLabel">
                    <i class="bi bi-person-badge me-2 text-primary"></i>User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="me-3 rounded-circle" style="width: 64px; height: 64px; overflow: hidden;">
                                <img id="userProfileImage" src="" alt="Profile" class="img-fluid rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div>
                                <h5 class="mb-1" id="viewUserName">John Doe</h5>
                                <p class="mb-0 text-muted" id="viewUserEmail">john.doe@example.com</p>
                            </div>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">User ID:</span>
                            <span class="fw-medium" id="viewUserId">12345</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Role:</span>
                            <span class="fw-medium" id="viewUserRole">Admin</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Status:</span>
                            <span class="fw-medium" id="viewUserStatus">Active</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Registered:</span>
                            <span class="fw-medium" id="viewUserRegistered">2023-01-15</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Last Login:</span>
                            <span class="fw-medium" id="viewUserLastLogin">2023-05-20</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewUserEditBtn">
                    <i class="bi bi-pencil me-2"></i>Edit User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Required JavaScript -->
<!-- Add jQuery first -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="//cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
<script src="//cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<!-- DataTablesManager -->
<script src="/assets/js/utility/DataTablesManager.js"></script>
<script src="/assets/js/utility/user-management.js"></script>

<?php
// Close the output buffer and include footer
$content = ob_get_clean();

include __DIR__ . '/../includes/admin/base.php';

?>                    