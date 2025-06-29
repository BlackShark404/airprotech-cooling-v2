<?php
$title = 'Technicians - AirProtech';
$activeTab = 'technician';

// Add any additional styles specific to this page
$additionalStyles = <<<HTML
<style>
    .filter-card {
        border-radius: 12px;
        background-color: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .filter-dropdown {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.5rem 1rem;
        width: 100%;
    }
    .date-input {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.5rem 1rem;
        width: 100%;
    }
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
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.375rem;
    }
    .badge-pending {
        background-color: #ffc107;
        color: #212529;
    }
    .badge-progress {
        background-color: #0dcaf0;
        color: #212529;
    }
    .badge-completed {
        background-color: #198754;
        color: #fff;
    }
    .badge-cancelled {
        background-color: #dc3545;
        color: #fff;
    }
    .badge-available {
        background-color: #198754;
        color: #fff;
    }
    .badge-unavailable {
        background-color: #dc3545;
        color: #fff;
    }
    .modal-header {
        border-bottom: 1px solid #dee2e6;
        border-top-left-radius: calc(0.3rem - 1px);
        border-top-right-radius: calc(0.3rem - 1px);
        padding: 1rem 1rem;
    }
    .modal-body {
        padding: 1rem;
    }
    .modal-footer {
        border-top: 1px solid #dee2e6;
        border-bottom-right-radius: calc(0.3rem - 1px);
        border-bottom-left-radius: calc(0.3rem - 1px);
        padding: 0.75rem;
    }
    
    /* Responsive table styles */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Table styling */
    #techniciansTable {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    #techniciansTable thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 12px 8px;
        vertical-align: middle;
    }
    
    #techniciansTable tbody td {
        padding: 15px 8px;
        vertical-align: middle;
    }
    
    #techniciansTable tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.03);
    }
    
    /* Assignment chip */
    .assignment-chip {
        display: inline-flex;
        align-items: center;
        background: #e9ecef;
        border-radius: 50px;
        padding: 4px 10px;
        margin: 3px;
        font-size: 0.85rem;
        border: 1px solid #dee2e6;
    }
    
    /* Technician info styles */
    .technician-info {
        display: flex;
        align-items: center;
    }
    .technician-avatar {
        width: 43px;
        height: 43px;
        border-radius: 50%;
        margin-right: 12px;
        object-fit: cover;
        border: 1px solid #eee;
    }
    .technician-details {
        display: flex;
        flex-direction: column;
    }
    .technician-name {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 2px;
    }
    .technician-contact {
        font-size: 0.8rem;
        color: #6c757d;
        line-height: 1.4;
    }
    
    @media (max-width: 992px) {
        .action-icon {
            width: 28px;
            height: 28px;
            margin-right: 3px;
        }
    }
</style>
HTML;

// Start output buffering for content
ob_start();
?>

<div class="container-fluid py-4">
    <div class="col">
        <h1 class="h3 mb-0">Technician Management</h1>
        <p class="text-muted">Manage technicians and view their assignments</p>
    </div>

    <!-- Filters Card -->
    <div class="card filter-card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Filters</h5>
            <div class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label for="availabilityFilter" class="form-label">Availability</label>
                    <select id="availabilityFilter" class="form-select filter-dropdown">
                        <option value="">All</option>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button id="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Technicians Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="techniciansTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Technician</th>
                            <th>Availability</th>
                            <th>Active Assignments</th>
                            <th>Completed Assignments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by DataTablesManager -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Technician Modal -->
<div class="modal fade" id="viewTechnicianModal" tabindex="-1" role="dialog" aria-labelledby="viewTechnicianModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTechnicianModalLabel">Technician Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Technician Information Card -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img id="view-technician-avatar" src="/assets/images/user-profile/default-profile.png" alt="Technician" class="rounded-circle me-3" width="80" height="80" style="border: 2px solid #eee; object-fit: cover;">
                            <div>
                                <h5 class="mb-1 fw-bold fs-4" id="view-technician-name"></h5>
                                <div class="text-muted mb-1" id="view-technician-email"><i class="fas fa-envelope me-2"></i><span></span></div>
                                <div class="text-muted" id="view-technician-phone"><i class="fas fa-phone me-2"></i><span></span></div>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="me-4">
                                <p><strong>ID:</strong> <span id="view-technician-id"></span></p>
                                <p><strong>Status:</strong> <span id="view-technician-status"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Assignment Tabs -->
                <ul class="nav nav-tabs mb-4" id="assignmentTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="service-tab" data-bs-toggle="tab" href="#service-assignments" role="tab">Service Assignments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="product-tab" data-bs-toggle="tab" href="#product-assignments" role="tab">Product Assignments</a>
                    </li>
                </ul>
                
                <div class="tab-content" id="assignmentsContent">
                    <!-- Service Assignments Tab -->
                    <div class="tab-pane fade show active" id="service-assignments" role="tabpanel">
                        <div class="table-responsive">
                            <table id="serviceAssignmentsTable" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Service Type</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Will be populated via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Product Assignments Tab -->
                    <div class="tab-pane fade" id="product-assignments" role="tabpanel">
                        <div class="table-responsive">
                            <table id="productAssignmentsTable" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Will be populated via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Technician Modal -->
<div class="modal fade" id="editTechnicianModal" tabindex="-1" role="dialog" aria-labelledby="editTechnicianModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTechnicianModalLabel">Edit Technician</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTechnicianForm">
                    <input type="hidden" id="edit-technician-id" name="technicianId">
                    
                    <div class="mb-3">
                        <label for="edit-availability" class="form-label">Availability</label>
                        <select id="edit-availability" name="isAvailable" class="form-select">
                            <option value="1">Available</option>
                            <option value="0">Unavailable</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTechnicianBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Service Assignment Status Modal -->
<div class="modal fade" id="updateServiceStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateServiceStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateServiceStatusModalLabel">Update Service Assignment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateServiceStatusForm">
                    <input type="hidden" id="service-assignment-id" name="assignmentId">
                    
                    <div class="mb-3">
                        <label for="service-status" class="form-label">Status</label>
                        <select id="service-status" name="status" class="form-select">
                            <option value="assigned">Assigned</option>
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="service-notes" class="form-label">Notes</label>
                        <textarea id="service-notes" name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveServiceStatusBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Product Assignment Status Modal -->
<div class="modal fade" id="updateProductStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateProductStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProductStatusModalLabel">Update Product Assignment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateProductStatusForm">
                    <input type="hidden" id="product-assignment-id" name="assignmentId">
                    
                    <div class="mb-3">
                        <label for="product-status" class="form-label">Status</label>
                        <select id="product-status" name="status" class="form-select">
                            <option value="assigned">Assigned</option>
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="product-notes" class="form-label">Notes</label>
                        <textarea id="product-notes" name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveProductStatusBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Include jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

<!-- Include DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script src="/assets/js/utility/DataTablesManager.js"></script>

<!-- Initialize DataTables and handle technicians -->
<script>
let techniciansManager;
let serviceAssignmentsTable;
let productAssignmentsTable;
let currentTechnicianId;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the DataTablesManager for technicians
    techniciansManager = new DataTablesManager('techniciansTable', {
        ajaxUrl: '/api/admin/technicians',
        columns: [
            { data: 'te_account_id', title: 'ID' },
            { 
                data: null, 
                title: 'Technician',
                render: function(data, type, row) {
                    const profileUrl = row.ua_profile_url || '/assets/images/user-profile/default-profile.png';
                    return `
                        <div class="technician-info">
                            <img src="${profileUrl}" alt="Profile" class="technician-avatar">
                            <div class="technician-details">
                                <div class="technician-name">${row.ua_first_name} ${row.ua_last_name}</div>
                                <div class="technician-contact">${row.ua_email || ''}</div>
                                <div class="technician-contact">${row.ua_phone_number || ''}</div>
                            </div>
                        </div>
                    `;
                }
            },
            { 
                data: 'te_is_available', 
                title: 'Availability',
                render: function(data, type, row) {
                    if (data == 1) {
                        return '<span class="badge badge-available">Available</span>';
                    } else {
                        return '<span class="badge badge-unavailable">Unavailable</span>';
                    }
                }
            },
            { 
                data: 'active_assignments', 
                title: 'Active Assignments',
                render: function(data, type, row) {
                    return data || 0;
                }
            },
            { 
                data: 'completed_assignments', 
                title: 'Completed',
                render: function(data, type, row) {
                    return data || 0;
                }
            },
            {
                data: null,
                title: 'Actions',
                render: function(data, type, row) {
                    return `<div class="d-flex">
                        <div class="action-icon action-icon-view view-btn me-1" data-id="${row.te_account_id}">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="action-icon action-icon-edit edit-btn me-1" data-id="${row.te_account_id}">
                            <i class="bi bi-pencil"></i>
                        </div>
                    </div>`;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        responsive: true
    });

    // Manually attach event listeners for action buttons
    $('#techniciansTable').on('click', '.view-btn', function() {
        const id = $(this).data('id');
        viewTechnician(id);
    });

    $('#techniciansTable').on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        editTechnician(id);
    });

    // Handle filter changes
    $('#availabilityFilter, #assignmentTypeFilter, #assignmentStatusFilter').on('change', applyFilters);
    
    // Reset filters
    $('#resetFilters').on('click', resetFilters);
    
    // Save technician changes
    $('#saveTechnicianBtn').on('click', saveTechnician);
    
    // Save service assignment status changes
    $('#saveServiceStatusBtn').on('click', saveServiceAssignmentStatus);
    
    // Save product assignment status changes
    $('#saveProductStatusBtn').on('click', saveProductAssignmentStatus);
});

// View technician details
function viewTechnician(technicianId) {
    // Load technician data
    $.ajax({
        url: `/api/admin/technicians/${technicianId}`,
        method: 'GET',
        success: function(response) {
            const tech = response.data;
            
            // Populate the technician information
            $('#view-technician-id').text(tech.te_account_id);
            $('#view-technician-name').text(`${tech.ua_first_name} ${tech.ua_last_name}`);
            $('#view-technician-email span').text(tech.ua_email || '');
            $('#view-technician-phone span').text(tech.ua_phone_number || '');
            $('#view-technician-avatar').attr('src', tech.ua_profile_url || '/assets/images/user-profile/default-profile.png');
            $('#view-technician-status').html(tech.te_is_available == 1 ? 
                '<span class="badge badge-available">Available</span>' : 
                '<span class="badge badge-unavailable">Unavailable</span>');
            
            // Initialize assignment tables
            initServiceAssignmentsTable(technicianId);
            initProductAssignmentsTable(technicianId);
            
            // Show the modal
            $('#viewTechnicianModal').modal('show');
        },
        error: function(xhr) {
            showErrorToast('Failed to load technician details');
        }
    });
}

// Initialize service assignments table
function initServiceAssignmentsTable(technicianId) {
    if (serviceAssignmentsTable) {
        serviceAssignmentsTable.destroy();
    }
    
    currentTechnicianId = technicianId;
    
    serviceAssignmentsTable = $('#serviceAssignmentsTable').DataTable({
        ajax: {
            url: `/api/admin/technicians/${technicianId}/assignments?type=service`,
            dataSrc: function(json) {
                console.log("Service assignments response:", json); // Debug
                // Handle doubly nested data structure
                return json.data && json.data.data ? json.data.data : [];
            }
        },
        columns: [
            { data: 'ba_booking_id', title: 'ID' },
            { data: 'service_type_name', title: 'Service Type' },
            { data: 'customer_name', title: 'Customer' },
            { data: 'sb_preferred_date', title: 'Date' },
            { 
                data: 'ba_status',
                title: 'Status',
                render: function(data, type, row) {
                    const statusMap = {
                        'assigned': { class: 'badge-pending', text: 'Assigned' },
                        'in-progress': { class: 'badge-progress', text: 'In Progress' },
                        'completed': { class: 'badge-completed', text: 'Completed' },
                        'cancelled': { class: 'badge-cancelled', text: 'Cancelled' }
                    };
                    
                    const status = statusMap[data] || { class: 'badge-secondary', text: data };
                    return `<span class="badge ${status.class}">${status.text}</span>`;
                }
            },
            {
                data: null,
                title: 'Actions',
                render: function(data, type, row) {
                    return `<div class="d-flex">
                        <a href="/admin/service-requests?id=${row.ba_booking_id}" class="action-icon action-icon-view me-1">
                            <i class="bi bi-eye"></i>
                        </a>
                        <div class="action-icon action-icon-edit update-service-status-btn" data-id="${row.ba_id}" data-status="${row.ba_status}" data-notes="${row.ba_notes || ''}">
                            <i class="bi bi-pencil"></i>
                        </div>
                    </div>`;
                }
            }
        ],
        responsive: true,
        order: [[3, 'desc']]
    });
    
    // Add event listener for update status buttons
    $('#serviceAssignmentsTable').on('click', '.update-service-status-btn', function() {
        const id = $(this).data('id');
        const status = $(this).data('status');
        const notes = $(this).data('notes');
        
        $('#service-assignment-id').val(id);
        $('#service-status').val(status);
        $('#service-notes').val(notes);
        
        $('#updateServiceStatusModal').modal('show');
    });
}

// Initialize product assignments table
function initProductAssignmentsTable(technicianId) {
    if (productAssignmentsTable) {
        productAssignmentsTable.destroy();
    }
    
    currentTechnicianId = technicianId;
    
    productAssignmentsTable = $('#productAssignmentsTable').DataTable({
        ajax: {
            url: `/api/admin/technicians/${technicianId}/assignments?type=product`,
            dataSrc: function(json) {
                console.log("Product assignments response:", json); // Debug
                // Handle doubly nested data structure
                return json.data && json.data.data ? json.data.data : [];
            }
        },
        columns: [
            { data: 'pa_order_id', title: 'ID' },
            { 
                data: null, 
                title: 'Product',
                render: function(data, type, row) {
                    return `${row.prod_name} (${row.var_capacity})`;
                }
            },
            { data: 'customer_name', title: 'Customer' },
            { data: 'pb_preferred_date', title: 'Date' },
            { 
                data: 'pa_status',
                title: 'Status',
                render: function(data, type, row) {
                    const statusMap = {
                        'assigned': { class: 'badge-pending', text: 'Assigned' },
                        'in-progress': { class: 'badge-progress', text: 'In Progress' },
                        'completed': { class: 'badge-completed', text: 'Completed' },
                        'cancelled': { class: 'badge-cancelled', text: 'Cancelled' }
                    };
                    
                    const status = statusMap[data] || { class: 'badge-secondary', text: data };
                    return `<span class="badge ${status.class}">${status.text}</span>`;
                }
            },
            {
                data: null,
                title: 'Actions',
                render: function(data, type, row) {
                    return `<div class="d-flex">
                        <a href="/admin/product-bookings?id=${row.pa_order_id}" class="action-icon action-icon-view me-1">
                            <i class="bi bi-eye"></i>
                        </a>
                        <div class="action-icon action-icon-edit update-product-status-btn" data-id="${row.pa_id}" data-status="${row.pa_status}" data-notes="${row.pa_notes || ''}">
                            <i class="bi bi-pencil"></i>
                        </div>
                    </div>`;
                }
            }
        ],
        responsive: true,
        order: [[3, 'desc']]
    });
    
    // Add event listener for update status buttons
    $('#productAssignmentsTable').on('click', '.update-product-status-btn', function() {
        const id = $(this).data('id');
        const status = $(this).data('status');
        const notes = $(this).data('notes');
        
        $('#product-assignment-id').val(id);
        $('#product-status').val(status);
        $('#product-notes').val(notes);
        
        $('#updateProductStatusModal').modal('show');
    });
}

// Edit technician
function editTechnician(technicianId) {
    $.ajax({
        url: `/api/admin/technicians/${technicianId}`,
        method: 'GET',
        success: function(response) {
            const tech = response.data;
            
            // Populate edit form
            $('#edit-technician-id').val(tech.te_account_id);
            $('#edit-availability').val(tech.te_is_available);
            
            // Show the modal
            $('#editTechnicianModal').modal('show');
        },
        error: function(xhr) {
            showErrorToast('Failed to load technician details');
        }
    });
}

// Save technician changes
function saveTechnician() {
    const technicianId = $('#edit-technician-id').val();
    const isAvailable = $('#edit-availability').val();
    
    $.ajax({
        url: `/api/admin/technicians/${technicianId}/update`,
        method: 'POST',
        data: {
            te_is_available: isAvailable
        },
        success: function(response) {
            showSuccessToast('Technician updated successfully');
            $('#editTechnicianModal').modal('hide');
            techniciansManager.refresh();
        },
        error: function(xhr) {
            showErrorToast('Failed to update technician');
        }
    });
}

// Apply filters to the table
function applyFilters() {
    const filters = {};
    
    const availability = $('#availabilityFilter').val();
    
    // Convert string values to numeric values for availability
    if (availability === 'available') {
        filters.te_is_available = 1;
    } else if (availability === 'unavailable') {
        filters.te_is_available = 0;
    }
    
    // Apply custom filtering directly to the DataTable
    $.fn.dataTable.ext.search.pop(); // Remove any existing filters
    
    if (availability) {
        // Add custom filter function to DataTables
        $.fn.dataTable.ext.search.push((settings, data, dataIndex, rowData) => {
            // Only filter our technicians table
            if (settings.nTable.id !== 'techniciansTable') {
                return true;
            }
            
            // Check availability
            if (availability === 'available' && rowData.te_is_available != 1) {
                return false;
            }
            if (availability === 'unavailable' && rowData.te_is_available != 0) {
                return false;
            }
            
            return true;
        });
    }
    
    // Redraw the table with filters applied
    techniciansManager.dataTable.draw();
}

// Reset all filters
function resetFilters() {
    // Reset filter select
    $('#availabilityFilter').val('');
    
    // Clear custom filters from DataTables
    $.fn.dataTable.ext.search.pop();
    
    // Redraw the table without filters
    techniciansManager.dataTable.draw();
}

// Show toast notification - success
function showSuccessToast(message) {
    if (techniciansManager) {
        techniciansManager.showSuccessToast('Success', message);
    } else {
        alert(message);
    }
}

// Show toast notification - error
function showErrorToast(message) {
    if (techniciansManager) {
        techniciansManager.showErrorToast('Error', message);
    } else {
        alert(message);
    }
}

// Save service assignment status
function saveServiceAssignmentStatus() {
    const assignmentId = $('#service-assignment-id').val();
    const status = $('#service-status').val();
    const notes = $('#service-notes').val();
    
    $.ajax({
        url: '/api/admin/service-assignments/update',
        method: 'POST',
        data: {
            assignment_id: assignmentId,
            status: status,
            notes: notes
        },
        success: function(response) {
            showSuccessToast('Service assignment status updated successfully');
            $('#updateServiceStatusModal').modal('hide');
            
            // Refresh the assignments table
            initServiceAssignmentsTable(currentTechnicianId);
        },
        error: function(xhr) {
            showErrorToast('Failed to update service assignment status');
        }
    });
}

// Save product assignment status
function saveProductAssignmentStatus() {
    const assignmentId = $('#product-assignment-id').val();
    const status = $('#product-status').val();
    const notes = $('#product-notes').val();
    
    $.ajax({
        url: '/api/admin/product-assignments/update',
        method: 'POST',
        data: {
            assignment_id: assignmentId,
            status: status,
            notes: notes
        },
        success: function(response) {
            showSuccessToast('Product assignment status updated successfully');
            $('#updateProductStatusModal').modal('hide');
            
            // Refresh the assignments table
            initProductAssignmentsTable(currentTechnicianId);
        },
        error: function(xhr) {
            showErrorToast('Failed to update product assignment status');
        }
    });
}
</script>

<?php
$content = ob_get_clean();

// Include the base template
include __DIR__ . '/../includes/admin/base.php';
?>