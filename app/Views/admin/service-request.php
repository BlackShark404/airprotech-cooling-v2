<?php
$title = 'Service Requests - AirProtech';
$activeTab = 'service_requests';

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
    .action-icon-delete {
        color: #dc3545;
    }
    .action-icon-assign {
        color: #17a2b8;
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
    .badge-high {
        background-color: #dc3545;
        color: #fff;
    }
    .badge-medium {
        background-color: #fd7e14;
        color: #212529;
    }
    .badge-low {
        background-color: #198754;
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
    .technician-badge {
        display: block;
        width: 100%;
        margin-bottom: 10px;
        padding: 10px;
        border-radius: 8px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .technician-remove {
        margin-left: 5px;
        cursor: pointer;
    }
    .technician-list {
        margin-top: 10px;
    }
    .add-technician-btn {
        margin-left: 10px;
    }
    
    /* Responsive table styles */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Table styling */
    #serviceRequestsTable {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    #serviceRequestsTable thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 12px 8px;
        vertical-align: middle;
    }
    
    #serviceRequestsTable tbody td {
        padding: 15px 8px;
        vertical-align: middle;
    }
    
    #serviceRequestsTable tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.03);
    }
    
    /* Customer info styles */
    .customer-info {
        display: flex;
        align-items: center;
    }
    .customer-avatar {
        width: 43px;
        height: 43px;
        border-radius: 50%;
        margin-right: 12px;
        object-fit: cover;
        border: 1px solid #eee;
    }
    .customer-details {
        display: flex;
        flex-direction: column;
    }
    .customer-name {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 2px;
    }
    .customer-contact {
        font-size: 0.8rem;
        color: #6c757d;
        line-height: 1.4;
    }
    
    /* Technician badges */
    .technician-chip {
        display: inline-flex;
        align-items: center;
        background: #e9ecef;
        border-radius: 50px;
        padding: 4px 10px;
        margin: 3px;
        font-size: 0.85rem;
        border: 1px solid #dee2e6;
    }
    .technician-chip img {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 7px;
        border: 1px solid #fff;
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
        <h1 class="h3 mb-0">Service Request Management</h1>
        <p class="text-muted">Manage service requests</p>
    </div>

    <!-- Filters Card -->
    <div class="card filter-card mb-4">
    <div class="card-body">
        <h5 class="mb-3">Filters</h5>
        <div class="row align-items-end">
            <div class="col-md-3 mb-3">
                <label for="statusFilter" class="form-label">Status</label>
                <select id="statusFilter" class="form-select filter-dropdown">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="in-progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="typeFilter" class="form-label">Service Type</label>
                <select id="typeFilter" class="form-select filter-dropdown">
                    <option value="">All Types</option>
                    <!-- To be populated by AJAX -->
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label for="priorityFilter" class="form-label">Priority</label>
                <select id="priorityFilter" class="form-select filter-dropdown">
                    <option value="">All Priorities</option>
                    <option value="urgent">Urgent</option>
                    <option value="moderate">Moderate</option>
                    <option value="normal">Normal</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label for="technicianFilter" class="form-label">Technician</label>
                <select id="technicianFilter" class="form-select filter-dropdown">
                    <option value="">All Technicians</option>
                    <option value="assigned">Assigned</option>
                    <option value="unassigned">Unassigned</option>
                    <!-- More options populated by AJAX -->
                </select>
            </div>
            <div class="col-md-2 mb-3 d-flex align-items-end">
                <button id="resetFilters" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filters
                </button>
            </div>
        </div>
    </div>
</div>


    <!-- Service Requests Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="serviceRequestsTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Service Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Technicians</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Est. Cost</th>
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

<!-- View Service Request Modal -->
<div class="modal fade" id="viewServiceRequestModal" tabindex="-1" role="dialog" aria-labelledby="viewServiceRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewServiceRequestModalLabel">Service Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Customer Information Card -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img id="view-customer-avatar" src="/assets/images/user-profile/default-profile.png" alt="Customer" class="rounded-circle me-3" width="80" height="80" style="border: 2px solid #eee; object-fit: cover;">
                            <div>
                                <h5 class="mb-1 fw-bold fs-4" id="view-customer"></h5>
                                <div class="text-muted mb-1" id="view-customer-email"><i class="fas fa-envelope me-2"></i><span></span></div>
                                <div class="text-muted" id="view-customer-phone"><i class="fas fa-phone me-2"></i><span></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID:</strong> <span id="view-id"></span></p>
                        <p><strong>Service Type:</strong> <span id="view-service-type"></span></p>
                        <p><strong>Preferred Date:</strong> <span id="view-date"></span></p>
                        <p><strong>Preferred Time:</strong> <span id="view-time"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <span id="view-status"></span></p>
                        <p><strong>Priority:</strong> <span id="view-priority"></span></p>
                        <p><strong>Estimated Cost:</strong> <span id="view-cost"></span></p>
                        <p><strong>Created:</strong> <span id="view-created"></span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Address:</strong></p>
                        <p id="view-address" class="border p-2 bg-light"></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Description:</strong></p>
                        <p id="view-description" class="border p-2 bg-light"></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Assigned Technicians:</strong></p>
                        <div id="view-technicians" class="border p-2 bg-light">
                            <!-- Technicians will be listed here -->
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

<!-- Edit Service Request Modal -->
<div class="modal fade" id="editServiceRequestModal" tabindex="-1" role="dialog" aria-labelledby="editServiceRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editServiceRequestModalLabel">Edit Service Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editServiceRequestForm">
                    <input type="hidden" id="edit-id" name="bookingId">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-status" class="form-label">Status</label>
                            <select id="edit-status" name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="in-progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-priority" class="form-label">Priority</label>
                            <select id="edit-priority" name="priority" class="form-select">
                                <option value="normal">Normal</option>
                                <option value="moderate">Moderate</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-cost" class="form-label">Estimated Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="edit-cost" name="estimatedCost" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-date" class="form-label">Preferred Date</label>
                            <input type="date" class="form-control" id="edit-date" name="preferredDate">
                        </div>
                        <div class="col-md-6">
                            <label for="edit-time" class="form-label">Preferred Time</label>
                            <input type="time" class="form-control" id="edit-time" name="preferredTime">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Assigned Technicians</label>
                            <div class="d-flex align-items-center">
                                <select id="technician-select" class="form-select">
                                    <option value="">Select a technician</option>
                                    <!-- Populated by AJAX -->
                                </select>
                                <button type="button" id="add-technician-btn" class="btn btn-primary add-technician-btn">Add</button>
                            </div>
                            <div id="technician-list" class="technician-list">
                                <!-- Assigned technicians will be listed here -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveServiceRequestBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteServiceRequestModal" tabindex="-1" role="dialog" aria-labelledby="deleteServiceRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteServiceRequestModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this service request? This action cannot be undone.</p>
                <p><strong>ID:</strong> <span id="delete-id"></span></p>
                <p><strong>Customer:</strong> <span id="delete-customer"></span></p>
                <p><strong>Service Type:</strong> <span id="delete-service-type"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
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

<script src="/assets/js/utility/toast-notifications.js"></script>
<script src="/assets/js/utility/DataTablesManager.js"></script>

<!-- Initialize DataTables and handle service requests -->
<script>
let serviceRequestsManager;
let assignedTechnicians = []; // Track currently assigned technicians

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the DataTablesManager
    serviceRequestsManager = new DataTablesManager('serviceRequestsTable', {
        ajaxUrl: '/api/admin/service-requests',
        columns: [
            { data: 'sb_id', title: 'ID' },
            { 
                data: null, 
                title: 'Customer',
                render: function(data, type, row) {
                    const profileUrl = row.customer_profile_url || '/assets/images/user-profile/default-profile.png';
                    return `
                        <div class="customer-info">
                            <img src="${profileUrl}" alt="Profile" class="customer-avatar">
                            <div class="customer-details">
                                <div class="customer-name">${row.customer_name}</div>
                                <div class="customer-contact">${row.customer_email || ''}</div>
                                <div class="customer-contact">${row.customer_phone || ''}</div>
                            </div>
                        </div>
                    `;
                }
            },
            { data: 'service_name', title: 'Service Type' },
            { data: 'sb_preferred_date', title: 'Date' },
            { 
                data: 'sb_preferred_time', 
                title: 'Time',
                render: function(data) {
                    return formatTime12Hour(data);
                }
            },
            {
                data: 'technicians',
                title: 'Technicians',
                render: function(data, type, row) {
                    if (!data || data.length === 0) {
                        return '<span class="badge bg-secondary">Unassigned</span>';
                    }
                    
                    let techHtml = '';
                    data.forEach(tech => {
                        const profileImg = tech.profile_url || '/assets/images/user-profile/default-profile.png';
                        techHtml += `
                            <div class="technician-chip" title="${tech.name}">
                                <img src="${profileImg}" alt="${tech.name}">
                                <span>${tech.name.split(' ')[0]}</span>
                            </div>
                        `;
                    });
                    
                    return techHtml;
                }
            },
            { 
                data: 'sb_status', 
                title: 'Status',
                badge: {
                    valueMap: {
                        'pending': { type: 'warning', display: 'Pending' },
                        'confirmed': { type: 'info', display: 'Confirmed' },
                        'in-progress': { type: 'primary', display: 'In Progress' },
                        'completed': { type: 'success', display: 'Completed' },
                        'cancelled': { type: 'danger', display: 'Cancelled' }
                    }
                }
            },
            { 
                data: 'sb_priority', 
                title: 'Priority',
                badge: {
                    valueMap: {
                        'normal': { type: 'success', display: 'Normal' },
                        'moderate': { type: 'warning', display: 'Moderate' },
                        'urgent': { type: 'danger', display: 'Urgent' }
                    }
                }
            },
            { data: 'sb_estimated_cost', title: 'Est. Cost', render: function(data) {
                return data ? '₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
            }},
            {
                data: null,
                title: 'Actions',
                render: function(data, type, row) {
                    return `<div class="d-flex">
                        <div class="action-icon action-icon-view view-btn me-1" data-id="${row.sb_id}">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="action-icon action-icon-edit edit-btn me-1" data-id="${row.sb_id}">
                            <i class="bi bi-pencil"></i>
                        </div>
                        <div class="action-icon action-icon-delete delete-btn" data-id="${row.sb_id}">
                            <i class="bi bi-trash"></i>
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
    $('#serviceRequestsTable').on('click', '.view-btn', function() {
        const id = $(this).data('id');
        viewServiceRequest({sb_id: id});
    });

    $('#serviceRequestsTable').on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        editServiceRequest({sb_id: id});
    });

    $('#serviceRequestsTable').on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        // Find the full row data from the DataTable
        const rowData = serviceRequestsManager.dataTable.row($(this).closest('tr')).data();
        confirmDeleteServiceRequest(rowData);
    });

    // Load service types for filter
    loadServiceTypes();
    
    // Load technicians for filter and assignment
    loadTechnicians();

    // Handle filter changes
    $('#statusFilter, #typeFilter, #priorityFilter, #technicianFilter').on('change', applyFilters);
    
    // Reset filters
    $('#resetFilters').on('click', resetFilters);
    
    // Add technician to the edit form
    $('#add-technician-btn').on('click', addTechnicianToList);
    
    // Date and time change events to validate technician assignments
    $('#edit-date, #edit-time').on('change', validateTechnicianAssignments);
    
    // Save service request changes
    $('#saveServiceRequestBtn').on('click', saveServiceRequest);
    
    // Confirm delete
    $('#confirmDeleteBtn').on('click', deleteServiceRequest);
});

// Load service types for the filter dropdown
function loadServiceTypes() {
    $.ajax({
        url: '/api/service-types',
        method: 'GET',
        success: function(response) {
            const typeSelect = $('#typeFilter');
            typeSelect.find('option:not(:first)').remove();
            
            response.data.forEach(type => {
                typeSelect.append(`<option value="${type.st_id}">${type.st_name}</option>`);
            });
        },
        error: function(xhr) {
            serviceRequestsManager.showErrorToast('Error', 'Failed to load service types');
        }
    });
}

// Load technicians for the filter and assignment dropdowns
function loadTechnicians() {
    $.ajax({
        url: '/api/technicians',
        method: 'GET',
        success: function(response) {
            const techSelect = $('#technicianFilter');
            const editTechSelect = $('#technician-select');
            
            techSelect.find('option:not(:first-child):not(:nth-child(2)):not(:nth-child(3))').remove();
            editTechSelect.find('option:not(:first)').remove();
            
            response.data.forEach(tech => {
                const techName = `${tech.ua_first_name} ${tech.ua_last_name}`;
                techSelect.append(`<option value="${tech.te_account_id}">${techName}</option>`);
                
                // For the edit dropdown, check if technician is available
                const isAvailable = tech.te_is_available == 1;
                const displayName = isAvailable ? techName : `${techName} (Unavailable)`;
                
                // Add to dropdown with disabled attribute if unavailable
                editTechSelect.append(`<option value="${tech.te_account_id}" data-name="${techName}" ${!isAvailable ? 'disabled' : ''}>${displayName}</option>`);
            });
        },
        error: function(xhr) {
            serviceRequestsManager.showErrorToast('Error', 'Failed to load technicians');
        }
    });
}

// Apply filters to the table
function applyFilters() {
    const filters = {};
    
    const status = $('#statusFilter').val();
    const type = $('#typeFilter').val();
    const priority = $('#priorityFilter').val();
    const technician = $('#technicianFilter').val();
    
    if (status) filters.status = status;
    if (type) filters.service_type_id = type;
    if (priority) filters.priority = priority;
    if (technician) {
        if (technician === 'assigned') {
            filters.has_technician = true;
        } else if (technician === 'unassigned') {
            filters.has_technician = false;
        } else {
            filters.technician_id = technician;
        }
    }
    
    // Update the AJAX URL with filter parameters
    $.ajax({
        url: '/api/admin/service-requests',
        method: 'GET',
        data: filters,
        success: function(response) {
            serviceRequestsManager.refresh(response.data);
        },
        error: function(xhr) {
            serviceRequestsManager.showErrorToast('Error', 'Failed to apply filters');
        }
    });
}

// Reset all filters
function resetFilters() {
    $('#statusFilter, #typeFilter, #priorityFilter, #technicianFilter').val('');
    serviceRequestsManager.refresh();
}

// View service request details
function viewServiceRequest(rowData) {
    // Load detailed service request data
    $.ajax({
        url: `/api/admin/service-requests/${rowData.sb_id}`,
        method: 'GET',
        success: function(response) {
            const data = response.data;
            
            // Populate the view modal
            $('#view-id').text(data.sb_id);
            $('#view-customer').text(data.customer_name);
            $('#view-customer-email span').text(data.customer_email);
            $('#view-customer-phone span').text(data.customer_phone);
            $('#view-customer-avatar').attr('src', data.customer_profile_url || '/assets/images/user-profile/default-profile.png');
            $('#view-service-type').text(data.service_name);
            $('#view-date').text(data.sb_preferred_date);
            $('#view-time').text(formatTime12Hour(data.sb_preferred_time));
            $('#view-status').text(data.sb_status.charAt(0).toUpperCase() + data.sb_status.slice(1));
            $('#view-priority').text(data.sb_priority.charAt(0).toUpperCase() + data.sb_priority.slice(1));
            $('#view-cost').text(data.sb_estimated_cost ? '₱' + parseFloat(data.sb_estimated_cost).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-');
            $('#view-created').text(data.sb_created_at);
            $('#view-address').text(data.sb_address);
            $('#view-description').text(data.sb_description);
            
            // Display assigned technicians
            const techContainer = $('#view-technicians');
            techContainer.empty();
            
            if (data.technicians && data.technicians.length > 0) {
                const techHtml = data.technicians.map(tech => {
                    const profileImg = tech.profile_url || '/assets/images/user-profile/default-profile.png';
                    return `
                        <div class="d-flex align-items-center mb-3 p-3 bg-white rounded border">
                            <img src="${profileImg}" alt="${tech.name}" class="rounded-circle me-3" width="48" height="48" style="border: 1px solid #eee;">
                            <div>
                                <div class="fw-bold fs-5">${tech.name}</div>
                                ${tech.email ? `<div class="text-muted mt-1"><i class="fas fa-envelope me-1"></i>${tech.email}</div>` : ''}
                                ${tech.phone ? `<div class="text-muted"><i class="fas fa-phone me-1"></i>${tech.phone}</div>` : ''}
                                ${tech.notes ? `<div class="text-muted mt-2 border-top pt-2">${tech.notes}</div>` : ''}
                            </div>
                        </div>
                    `;
                }).join('');
                
                techContainer.html(techHtml);
            } else {
                techContainer.html('<p class="text-muted mb-0">No technicians assigned</p>');
            }
            
            // Show the modal
            $('#viewServiceRequestModal').modal('show');
        },
        error: function(xhr) {
            console.error("Error fetching service request details:", xhr);
            alert('Failed to load service request details');
        }
    });
}

// Edit service request
function editServiceRequest(rowData) {
    // Load detailed service request data for editing
    $.ajax({
        url: `/api/admin/service-requests/${rowData.sb_id}`,
        method: 'GET',
        success: function(response) {
            const data = response.data;
            
            // Populate the edit form
            $('#edit-id').val(data.sb_id);
            $('#edit-status').val(data.sb_status);
            $('#edit-priority').val(data.sb_priority);
            $('#edit-cost').val(data.sb_estimated_cost || '');
            
            // Set min date and time to current date and time
            const now = new Date();
            const currentDate = now.toISOString().split('T')[0]; // YYYY-MM-DD format
            $('#edit-date').attr('min', currentDate);
            
            // Set the date and time values
            $('#edit-date').val(data.sb_preferred_date);
            $('#edit-time').val(data.sb_preferred_time);
            
            // Clear and populate assigned technicians
            assignedTechnicians = [];
            const techList = $('#technician-list');
            techList.empty();
            
            if (data.technicians && data.technicians.length > 0) {
                data.technicians.forEach(tech => {
                    assignedTechnicians.push({
                        id: tech.id,
                        name: tech.name,
                        notes: tech.notes
                    });
                    
                    addTechnicianBadge(tech.id, tech.name, tech.notes);
                });
            }
            
            // Show the modal
            $('#editServiceRequestModal').modal('show');
        },
        error: function(xhr) {
            serviceRequestsManager.showErrorToast('Error', 'Failed to load service request for editing');
        }
    });
}

// Add a technician to the list in the edit form
function addTechnicianToList() {
    const techSelect = $('#technician-select');
    const techId = techSelect.val();
    
    if (!techId) {
        // Show toast notification for empty selection
        if (typeof serviceRequestsManager !== 'undefined') {
            serviceRequestsManager.showWarningToast('Warning', 'Please select a technician');
        } else {
            alert('Please select a technician');
        }
        return;
    }
    
    const techName = techSelect.find('option:selected').data('name');
    
    // Check if technician is already in the list
    const alreadyAssigned = assignedTechnicians.some(tech => tech.id === techId || tech.id === parseInt(techId));
    
    if (alreadyAssigned) {
        // Show toast notification for duplicate technician
        if (typeof serviceRequestsManager !== 'undefined') {
            serviceRequestsManager.showWarningToast('Warning', `${techName} is already assigned to this request`);
        } else {
            alert(`${techName} is already assigned to this request`);
        }
        return;
    }
    
    // Check for scheduling conflicts
    const bookingId = $('#edit-id').val();
    const preferredDate = $('#edit-date').val();
    const preferredTime = $('#edit-time').val();
    
    // Show loading indicator
    const addBtn = $('#add-technician-btn');
    const originalText = addBtn.text();
    addBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    
    // Check for conflicts
    checkTechnicianScheduleConflict(techId, bookingId, preferredDate, preferredTime, function(hasConflict, message) {
        // Reset button state
        addBtn.prop('disabled', false).text(originalText);
        
        if (hasConflict) {
            if (typeof serviceRequestsManager !== 'undefined') {
                serviceRequestsManager.showErrorToast('Scheduling Conflict', message);
            } else {
                alert('Scheduling Conflict: ' + message);
            }
            
            // Highlight the technician select to indicate the error source
            techSelect.addClass('is-invalid border-danger');
            setTimeout(() => {
                techSelect.removeClass('is-invalid border-danger');
            }, 3000);
            return;
        }
        
        // No conflict, proceed with adding technician
        // Add to our tracking array
        assignedTechnicians.push({
            id: techId,
            name: techName
        });
        
        // Add badge to the UI
        addTechnicianBadge(techId, techName);
        
        // Reset the select
        techSelect.val('');
    });
}

// Function to check for technician scheduling conflicts
function checkTechnicianScheduleConflict(technicianId, bookingId, date, time, callback) {
    // Get technician name
    const technicianName = $('#technician-select option[value="' + technicianId + '"]').text();

    $.ajax({
        url: '/api/admin/technicians/schedule',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            technician_id: technicianId,
            start_date: date,
            end_date: date
        }),
        success: function(response) {
            if (!response.data || !response.data.schedule) {
                callback(false);
                return;
            }
            
            const schedule = response.data.schedule;
            
            // Convert booking time to a comparable format
            const bookingTime = new Date(`${date}T${time}`);
            const bookingHour = bookingTime.getHours();
            
            // Format selected time in 12-hour format with AM/PM
            const selectedTimeFormatted = formatTime12Hour(time);
            
            // Check each scheduled item for conflicts
            for (let item of schedule) {
                const itemDate = item.sb_preferred_date || item.pb_preferred_date;
                const itemTime = item.sb_preferred_time || item.pb_preferred_time;
                
                if (itemDate === date) {
                    // Parse the time string
                    const [hours, minutes] = itemTime.split(':').map(Number);
                    
                    // Check if times are within 3 hours of each other
                    if (Math.abs(bookingHour - hours) < 3) {
                        const itemType = item.service_type || item.product_info || 'booking';
                        
                        // Format conflict time in 12-hour format with AM/PM
                        const conflictTimeFormatted = formatTime12Hour(itemTime);
                        
                        const message = `${technicianName} already has a ${itemType} at ${conflictTimeFormatted} which conflicts with the selected time (${selectedTimeFormatted})`;
                        callback(true, message);
                        return;
                    }
                }
            }
            
            // No conflicts found
            callback(false);
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Failed to check technician schedule';
            callback(true, errorMsg);
        }
    });
}

// Helper function to format time in 12-hour format
function formatTime12Hour(timeString) {
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12; // Convert 0 to 12 for 12 AM
    return `${hour12}:${minutes} ${ampm}`;
}

// Create and add a technician badge to the UI
function addTechnicianBadge(techId, techName, notes = '') {
    const techList = $('#technician-list');
    const badge = $(`
        <div class="technician-badge" data-id="${techId}">
            <div>
                <span>${techName}</span>
                <span class="technician-remove" style="cursor: pointer; margin-left: 5px;">×</span>
            </div>
            <textarea class="form-control mt-1 technician-notes" placeholder="Add notes for ${techName}" rows="2">${notes || ''}</textarea>
        </div>
    `);
    
    // Add remove functionality
    badge.find('.technician-remove').on('click', function() {
        // Remove from tracking array
        assignedTechnicians = assignedTechnicians.filter(tech => tech.id !== techId);
        // Remove badge from UI
        badge.remove();
    });
    
    techList.append(badge);
}

// Save service request changes
function saveServiceRequest() {
    const bookingId = $('#edit-id').val();
    const status = $('#edit-status').val();
    const priority = $('#edit-priority').val();
    const estimatedCost = $('#edit-cost').val();
    const preferredDate = $('#edit-date').val();
    const preferredTime = $('#edit-time').val();
    
    // Get technician IDs and their notes
    const techniciansData = [];
    $('#technician-list .technician-badge').each(function() {
        const techId = $(this).data('id');
        const notes = $(this).find('.technician-notes').val();
        techniciansData.push({
            id: techId,
            notes: notes
        });
    });
    
    // Validate date and time
    const now = new Date();
    const selectedDateTime = new Date(`${preferredDate}T${preferredTime}`);
    
    if (selectedDateTime < now) {
        serviceRequestsManager.showErrorToast('Validation Error', 'Preferred date and time cannot be in the past');
        return;
    }
    
    // Prepare data for update
    const updateData = {
        bookingId: bookingId,
        status: status,
        priority: priority,
        estimatedCost: estimatedCost,
        preferredDate: preferredDate,
        preferredTime: preferredTime,
        technicians: techniciansData // Updated to send IDs and notes
    };
    
    // Show loading indicator
    const saveBtn = $('#saveServiceRequestBtn');
    const originalText = saveBtn.text();
    saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
    
    // Send update request
    $.ajax({
        url: '/api/admin/service-requests/update',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(updateData),
        success: function(response) {
            $('#editServiceRequestModal').modal('hide');
            serviceRequestsManager.showSuccessToast('Success', response.message);
            serviceRequestsManager.refresh();
        },
        error: function(xhr) {
            saveBtn.prop('disabled', false).text(originalText);
            
            const errorMsg = xhr.responseJSON?.message || 'Failed to update service request';
            
            // Check for scheduling conflict errors
            if (errorMsg.includes('Scheduling conflict')) {
                serviceRequestsManager.showErrorToast('Technician Scheduling Conflict', errorMsg);
                
                // Highlight the technician select to indicate the error source
                $('#technician-select').addClass('is-invalid border-danger');
                setTimeout(() => {
                    $('#technician-select').removeClass('is-invalid border-danger');
                }, 3000);
            } else {
                serviceRequestsManager.showErrorToast('Error', errorMsg);
            }
        },
        complete: function() {
            // Reset button state
            saveBtn.prop('disabled', false).text(originalText);
        }
    });
}

// Confirm service request deletion
function confirmDeleteServiceRequest(rowData) {
    $('#delete-id').text(rowData.sb_id);
    $('#delete-customer').text(rowData.customer_name);
    $('#delete-service-type').text(rowData.service_name);
    
    $('#deleteServiceRequestModal').modal('show');
}

// Delete service request
function deleteServiceRequest() {
    const bookingId = $('#delete-id').text();
    
    $.ajax({
        url: `/api/admin/service-requests/delete/${bookingId}`,
        method: 'POST',
        success: function(response) {
            $('#deleteServiceRequestModal').modal('hide');
            serviceRequestsManager.showSuccessToast('Success', response.message);
            serviceRequestsManager.refresh();
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Failed to delete service request';
            serviceRequestsManager.showErrorToast('Error', errorMsg);
        }
    });
}

// Validate technician assignments when date or time changes
function validateTechnicianAssignments() {
    const bookingId = $('#edit-id').val();
    const preferredDate = $('#edit-date').val();
    const preferredTime = $('#edit-time').val();
    
    // Skip validation if date or time is empty
    if (!preferredDate || !preferredTime || assignedTechnicians.length === 0) {
        return;
    }
    
    // Show warning message
    const warningHtml = `
        <div class="alert alert-warning mt-3 mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Date or time has changed. Technician schedules should be verified for conflicts.
        </div>
    `;
    
    // Only add the warning if it's not already there
    if ($('#technician-list .alert-warning').length === 0) {
        $('#technician-list').prepend(warningHtml);
        
        // Remove the warning after 5 seconds
        setTimeout(() => {
            $('#technician-list .alert-warning').fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
}
</script>

<?php
$content = ob_get_clean();

// Include the base template
include __DIR__ . '/../includes/admin/base.php';
?>