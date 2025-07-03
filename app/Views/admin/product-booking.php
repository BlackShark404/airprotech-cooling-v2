<?php
$title = 'Product Bookings - AirProtech';
$activeTab = 'product_bookings';

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
    .badge-confirmed {
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
    #productBookingsTable {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    #productBookingsTable thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 12px 8px;
        vertical-align: middle;
    }
    
    #productBookingsTable tbody td {
        padding: 15px 8px;
        vertical-align: middle;
    }
    
    #productBookingsTable tbody tr:hover {
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
</style>
HTML;

// Start output buffering for content
ob_start();
?>

<div class="container-fluid py-4">
    <div class="col">
        <h1 class="h3 mb-0">Product Booking Management</h1>
        <p class="text-muted">Manage product bookings and deliveries</p>
    </div>

    <!-- Filters Card -->
    <div class="card filter-card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Filters</h5>
            <div class="row">
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
                    <label for="productFilter" class="form-label">Product</label>
                    <select id="productFilter" class="form-select filter-dropdown">
                        <option value="">All Products</option>
                        <!-- To be populated by AJAX -->
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="dateRangeFilter" class="form-label">Date Range</label>
                    <select id="dateRangeFilter" class="form-select filter-dropdown">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last7days">Last 7 Days</option>
                        <option value="last30days">Last 30 Days</option>
                        <option value="thisMonth">This Month</option>
                        <option value="lastMonth">Last Month</option>
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

    <!-- Product Bookings Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="productBookingsTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Delivery Date</th>
                            <th>Delivery Time</th>
                            <th>Technicians</th>
                            <th>Status</th>
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

<!-- View Product Booking Modal -->
<div class="modal fade" id="viewProductBookingModal" tabindex="-1" role="dialog" aria-labelledby="viewProductBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewProductBookingModalLabel">Product Booking Details</h5>
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
                    <div class="col-md-4 text-center mb-4">
                        <img id="view-product-image" src="" alt="Product Image" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                    </div>
                    <div class="col-md-8">
                        <h4 id="view-product-name" class="fw-bold"></h4>
                        <p><strong>Variant:</strong> <span id="view-product-variant"></span></p>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>Booking ID:</strong> <span id="view-id"></span></p>
                                <p><strong>Quantity:</strong> <span id="view-quantity"></span></p>
                                <p><strong>Unit Price:</strong> <span id="view-unit-price"></span></p>
                                <p><strong>Price Type:</strong> <span id="view-price-type"></span></p>
                                <p><strong>Total Amount:</strong> <span id="view-total-amount" class="fw-bold text-primary"></span></p>
                                <p class="mt-3"><strong>SRP Price:</strong> <span id="view-srp-price"></span></p>
                                <div id="view-free-install-container">
                                    <p><strong>Free Installation Price:</strong> <span id="view-free-install-price"></span></p>
                                </div>
                                <div id="view-with-install-container">
                                    <p><strong>With Installation Price 1:</strong> <span id="view-with-install-price1"></span></p>
                                    <p><strong>With Installation Price 2:</strong> <span id="view-with-install-price2"></span></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> <span id="view-status"></span></p>
                                <p><strong>Order Date:</strong> <span id="view-order-date"></span></p>
                                <p><strong>Preferred Delivery Date:</strong> <span id="view-delivery-date"></span></p>
                                <p><strong>Preferred Delivery Time:</strong> <span id="view-delivery-time"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Delivery/Installation Address:</strong></p>
                        <p id="view-address" class="border p-2 bg-light"></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Additional Instructions:</strong></p>
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

<!-- Edit Product Booking Modal -->
<div class="modal fade" id="editProductBookingModal" tabindex="-1" role="dialog" aria-labelledby="editProductBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductBookingModalLabel">Edit Product Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProductBookingForm">
                    <input type="hidden" id="edit-id" name="bookingId">
                    <input type="hidden" id="edit-unit-price" name="unitPrice">
                    
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
                            <label for="edit-price-type" class="form-label">Price Type</label>
                            <select id="edit-price-type" name="priceType" class="form-select">
                                <!-- Price type options will be populated dynamically based on product configuration -->
                            </select>
                            <div class="price-details mt-3">
                                <p class="mb-1 text-muted small">SRP: <span id="edit-srp-price">₱0.00</span></p>
                                <div id="free-install-price-container">
                                    <p class="mb-1 text-muted small">Free Install Price: <span id="edit-free-install-price">₱0.00</span></p>
                                </div>
                                <div id="with-install-price-container">
                                    <p class="mb-1 text-muted small with-install1">With Install Price 1: <span id="edit-with-install-price1">₱0.00</span></p>
                                    <p class="mb-1 text-muted small with-install2">With Install Price 2: <span id="edit-with-install-price2">₱0.00</span></p>
                                </div>
                                <p class="mt-2 fw-bold">Total Amount: <span id="edit-total-amount" class="text-primary">₱0.00</span></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Warehouse Selection -->
                    <div class="row mb-3" id="warehouse-selection-container">
                        <div class="col-12">
                            <label for="edit-warehouse-id" class="form-label">Warehouse (for inventory deduction)</label>
                            <select id="edit-warehouse-id" name="warehouseId" class="form-select">
                                <option value="auto" selected>Auto-select warehouse (Default)</option>
                                <!-- Warehouses will be populated via AJAX -->
                            </select>
                            <small class="text-muted">Only warehouses with sufficient inventory are enabled. Inventory will be deducted when status is set to "Confirmed".</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-delivery-date" class="form-label">Delivery Date</label>
                            <input type="date" class="form-control" id="edit-delivery-date" name="deliveryDate">
                        </div>
                        <div class="col-md-6">
                            <label for="edit-delivery-time" class="form-label">Delivery Time</label>
                            <input type="time" class="form-control" id="edit-delivery-time" name="deliveryTime">
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
                <button type="button" class="btn btn-primary" id="saveProductBookingBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteProductBookingModal" tabindex="-1" role="dialog" aria-labelledby="deleteProductBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProductBookingModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product booking? This action cannot be undone.</p>
                <p><strong>ID:</strong> <span id="delete-id"></span></p>
                <p><strong>Customer:</strong> <span id="delete-customer"></span></p>
                <p><strong>Product:</strong> <span id="delete-product"></span></p>
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

<!-- Initialize DataTables and handle product bookings -->
<script>
let productBookingsManager;
let assignedTechnicians = []; // Track currently assigned technicians

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the DataTablesManager
    productBookingsManager = new DataTablesManager('productBookingsTable', {
        ajaxUrl: '/api/admin/product-bookings',
        columns: [
            { data: 'pb_id', title: 'ID' },
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
            { data: 'prod_name', title: 'Product' },
            { data: 'pb_quantity', title: 'Quantity' },
            { data: 'pb_total_amount', title: 'Total Amount', render: function(data) {
                return data ? '₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
            }},
            { data: 'pb_preferred_date', title: 'Delivery Date' },
            { 
                data: 'pb_preferred_time', 
                title: 'Delivery Time',
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
                data: 'pb_status', 
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
                data: null,
                title: 'Actions',
                render: function(data, type, row) {
                    return `<div class="d-flex">
                        <div class="action-icon action-icon-view view-btn me-1" data-id="${row.pb_id}">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="action-icon action-icon-edit edit-btn me-1" data-id="${row.pb_id}">
                            <i class="bi bi-pencil"></i>
                        </div>
                        <div class="action-icon action-icon-delete delete-btn" data-id="${row.pb_id}">
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
    $('#productBookingsTable').on('click', '.view-btn', function() {
        const id = $(this).data('id');
        viewProductBooking({pb_id: id});
    });

    $('#productBookingsTable').on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        editProductBooking({pb_id: id});
    });

    $('#productBookingsTable').on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        // Find the full row data from the DataTable
        const rowData = productBookingsManager.dataTable.row($(this).closest('tr')).data();
        confirmDeleteProductBooking(rowData);
    });

    // Load products for filter
    loadProducts();
    
    // Load technicians for filter and assignment
    loadTechnicians();

    // Handle filter changes
    $('#statusFilter, #productFilter, #dateRangeFilter, #technicianFilter').on('change', applyFilters);
    
    // Reset filters
    $('#resetFilters').on('click', resetFilters);
    
    // Add technician to the edit form
    $('#add-technician-btn').on('click', addTechnicianToList);
    
    // Date and time change events to validate technician assignments
    $('#edit-delivery-date, #edit-delivery-time').on('change', validateTechnicianAssignments);
    
    // Save product booking changes
    $('#saveProductBookingBtn').on('click', saveProductBooking);
    
    // Confirm delete
    $('#confirmDeleteBtn').on('click', deleteProductBooking);
});

// Load products for the filter dropdown
function loadProducts() {
    $.ajax({
        url: '/api/products',
        method: 'GET',
        success: function(response) {
            const productSelect = $('#productFilter');
            productSelect.find('option:not(:first)').remove();
            
            response.data.forEach(product => {
                productSelect.append(`<option value="${product.prod_id}">${product.prod_name}</option>`);
            });
        },
        error: function(xhr) {
            productBookingsManager.showErrorToast('Error', 'Failed to load products');
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
            productBookingsManager.showErrorToast('Error', 'Failed to load technicians');
        }
    });
}

// Apply filters to the table
function applyFilters() {
    const filters = {};
    
    const status = $('#statusFilter').val();
    const product = $('#productFilter').val();
    const dateRange = $('#dateRangeFilter').val();
    const technician = $('#technicianFilter').val();
    
    if (status) filters.status = status;
    if (product) filters.product_id = product;
    if (dateRange) filters.date_range = dateRange;
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
        url: '/api/admin/product-bookings',
        method: 'GET',
        data: filters,
        success: function(response) {
            productBookingsManager.refresh(response.data);
        },
        error: function(xhr) {
            productBookingsManager.showErrorToast('Error', 'Failed to apply filters');
        }
    });
}

// Reset all filters
function resetFilters() {
    $('#statusFilter, #productFilter, #dateRangeFilter, #technicianFilter').val('');
    productBookingsManager.refresh();
}

// Function to view product booking details
function viewProductBooking(rowData) {
    // Fetch detailed product booking data
    $.ajax({
        url: `/api/admin/product-bookings/${rowData.pb_id}`,
        method: 'GET',
        success: function(response) {
            const data = response.data;
            
            // Set basic booking information
            $('#view-id').text(data.pb_id);
            $('#view-status').html(`<span class="badge badge-${data.pb_status}">${capitalizeFirstLetter(data.pb_status)}</span>`);
            
            // Set customer information
            $('#view-customer').text(`${data.customer_name}`);
            $('#view-customer-email span').text(data.customer_email);
            $('#view-customer-phone span').text(data.customer_phone);
            
            // Set customer avatar
            if (data.customer_profile_url) {
                $('#view-customer-avatar').attr('src', data.customer_profile_url);
            } else {
                $('#view-customer-avatar').attr('src', '/assets/images/user-profile/default-profile.png');
            }
            
            // Set product information
            $('#view-product-name').text(data.prod_name);
            $('#view-product-variant').text(data.var_capacity);
            
            // Set product image
            if (data.prod_image) {
                $('#view-product-image').attr('src', data.prod_image).show();
            } else {
                $('#view-product-image').hide();
            }
            
            // Set booking details
            $('#view-quantity').text(data.pb_quantity);
            
            // Format currency values with thousand separators
            $('#view-unit-price').text(formatCurrency(data.pb_unit_price));
            $('#view-total-amount').text(formatCurrency(data.pb_total_amount));
            $('#view-srp-price').text(formatCurrency(data.var_srp_price));
            
            // Handle different pricing display based on product configuration
            const hasFreeInstallOption = data.pb_has_free_install_option || false;
            
            if (hasFreeInstallOption) {
                // Show free install + with install price 1
                $('#view-free-install-container').show();
                $('#view-free-install-price').text(formatCurrency(data.var_price_free_install));
                
                $('#view-with-install-container').html(`
                    <p><strong>With Installation Price:</strong> <span>${formatCurrency(data.var_price_with_install1)}</span></p>
                `);
            } else {
                // Hide free install option, show with install 1 and 2
                $('#view-free-install-container').hide();
                
                $('#view-with-install-container').html(`
                    <p><strong>With Installation Price 1:</strong> <span>${formatCurrency(data.var_price_with_install1)}</span></p>
                    <p><strong>With Installation Price 2:</strong> <span>${formatCurrency(data.var_price_with_install2)}</span></p>
                `);
            }
            
            // Format price type for display
            let priceTypeFormatted = 'Unknown';
            if (data.pb_price_type) {
                if (data.pb_price_type === 'free_installation') {
                    priceTypeFormatted = 'Free Installation';
                } else if (data.pb_price_type === 'with_installation1') {
                    priceTypeFormatted = hasFreeInstallOption ? 'With Installation' : 'With Installation 1';
                } else if (data.pb_price_type === 'with_installation2') {
                    priceTypeFormatted = 'With Installation 2';
                }
            }
            
            $('#view-price-type').text(priceTypeFormatted);
            
            // Format dates and times
            $('#view-order-date').text(formatDate(data.pb_order_date) + ' ' + formatTime12Hour(data.pb_order_date.split(' ')[1]));
            $('#view-delivery-date').text(formatDate(data.pb_preferred_date));
            $('#view-delivery-time').text(formatTime12Hour(data.pb_preferred_time));
            
            // Set address and description
            $('#view-address').text(data.pb_address);
            $('#view-description').text(data.pb_description || 'No additional instructions provided');
            
            // Set technicians
            const techContainer = $('#view-technicians');
            
            if (data.technicians && data.technicians.length > 0) {
                const techHtml = data.technicians.map(tech => {
                    const profileUrl = tech.profile_url || '/assets/images/user-profile/default-profile.png';
                    return `
                        <div class="technician-chip mb-2">
                            <img src="${profileUrl}" alt="${tech.name}">
                            <div>
                                <div class="fw-bold">${tech.name}</div>
                                <div class="text-muted small">${tech.notes || 'No notes'}</div>
                            </div>
                        </div>
                    `;
                }).join('');
                
                techContainer.html(techHtml);
            } else {
                techContainer.html('<p class="text-muted mb-0">No technicians assigned</p>');
            }
            
            // Show the modal
            $('#viewProductBookingModal').modal('show');
        },
        error: function(xhr) {
            console.error("Error fetching product booking details:", xhr);
            alert('Failed to load product booking details');
        }
    });
}

// Helper function to format currency with thousand separators
function formatCurrency(value) {
    // Parse the value to float and fix to 2 decimal places
    const num = parseFloat(value || 0).toFixed(2);
    // Format with thousand separators
    return '₱' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Helper function to format date
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; // Return original if invalid
    
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Helper function to capitalize the first letter of a string
function capitalizeFirstLetter(string) {
    if (!string) return '';
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Edit product booking
function editProductBooking(rowData) {
    // Load detailed product booking data for editing
    $.ajax({
        url: `/api/admin/product-bookings/${rowData.pb_id}`,
        method: 'GET',
        success: function(response) {
            const data = response.data;
            
            // Populate the edit form
            $('#edit-id').val(data.pb_id);
            $('#edit-status').val(data.pb_status);
            
            // Clear and populate price type options based on product configuration
            const hasFreeInstallOption = data.pb_has_free_install_option || false;
            const priceTypeSelect = $('#edit-price-type');
            priceTypeSelect.empty();
            
            if (hasFreeInstallOption) {
                // Product has free installation option
                priceTypeSelect.append(`<option value="free_installation">Free Installation</option>`);
                priceTypeSelect.append(`<option value="with_installation1">With Installation</option>`);
                
                // Show/hide price containers
                $('#free-install-price-container').show();
                $('.with-install2').hide();
                $('.with-install1').text('With Install Price:');
                
                // Set prices
                $('#edit-free-install-price').text(formatCurrency(data.var_price_free_install));
                $('#edit-with-install-price1').text(formatCurrency(data.var_price_with_install1));
            } else {
                // Product doesn't have free installation option
                priceTypeSelect.append(`<option value="with_installation1">With Installation 1</option>`);
                
                // Only show with_installation2 if the price is not 0
                const withInstall2Price = parseFloat(data.var_price_with_install2 || 0);
                if (withInstall2Price > 0) {
                    priceTypeSelect.append(`<option value="with_installation2">With Installation 2</option>`);
                    $('.with-install2').show();
                } else {
                    $('.with-install2').hide();
                }
                
                // Show/hide price containers
                $('#free-install-price-container').hide();
                $('.with-install1').text('With Install Price 1:');
                
                // Set prices
                $('#edit-with-install-price1').text(formatCurrency(data.var_price_with_install1));
                $('#edit-with-install-price2').text(formatCurrency(data.var_price_with_install2));
            }
            
            // Select current price type
            const currentPriceType = data.pb_price_type || 'free_installation';
            if (priceTypeSelect.find(`option[value="${currentPriceType}"]`).length > 0) {
                priceTypeSelect.val(currentPriceType);
            } else {
                // Default to first option if current price type is not available
                priceTypeSelect.val(priceTypeSelect.find('option:first').val());
            }
            
            // Set min date to current date
            const now = new Date();
            const currentDate = now.toISOString().split('T')[0]; // YYYY-MM-DD format
            $('#edit-delivery-date').attr('min', currentDate);
            
            // Set the date and time values
            $('#edit-delivery-date').val(data.pb_preferred_date);
            $('#edit-delivery-time').val(data.pb_preferred_time);
            
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
            
            // Set price information with thousand separator formatting
            const quantity = parseInt(data.pb_quantity) || 1;
            const srp = parseFloat(data.var_srp_price || 0);
            const freeInstallPrice = parseFloat(data.var_price_free_install || 0);
            const withInstallPrice = parseFloat(data.var_price_with_install || 0);
            
            $('#edit-srp-price').text(formatCurrency(srp));
            $('#edit-free-install-price').text(formatCurrency(freeInstallPrice));
            $('#edit-with-install-price').text(formatCurrency(withInstallPrice));
            
            // Calculate and update total amount based on current price type
            updateTotalAmount(data);
            
            // Add event listener for price type changes
            $('#edit-price-type').off('change').on('change', function() {
                updateTotalAmount(data);
            });
            
            // Add event listener for status changes to handle warehouse visibility
            $('#edit-status').off('change').on('change', function() {
                const selectedStatus = $(this).val();
                const warehouseContainer = $('#warehouse-selection-container');
                
                // Show warehouse selection only if status is 'pending' or 'confirmed'
                if (selectedStatus === 'confirmed' || selectedStatus === 'pending') {
                    warehouseContainer.show();
                    
                    // Only reload warehouses if changing to confirmed (in case inventory changed)
                    if (selectedStatus === 'confirmed') {
                        loadWarehousesForVariant(data.pb_variant_id, quantity, data.pb_warehouse_id);
                    }
                } else {
                    warehouseContainer.hide();
                }
            });
            
            // Trigger the status change handler initially
            $('#edit-status').trigger('change');
            
            // Load warehouses with inventory information for the variant
            loadWarehousesForVariant(data.pb_variant_id, quantity, data.pb_warehouse_id);
            
            // Show the modal
            $('#editProductBookingModal').modal('show');
        },
        error: function(xhr) {
            productBookingsManager.showErrorToast('Error', 'Failed to load product booking for editing');
        }
    });
}

// Helper function to update total amount based on price type
function updateTotalAmount(data) {
    const priceType = $('#edit-price-type').val();
    const quantity = parseInt(data.pb_quantity) || 1;
    const hasFreeInstallOption = data.pb_has_free_install_option || false;
    let unitPrice = 0;
    
    if (priceType === 'free_installation' && hasFreeInstallOption) {
        // Free installation
        unitPrice = parseFloat(data.var_price_free_install || 0);
        
        // Highlight the selected price
        $('#edit-free-install-price').addClass('fw-bold text-primary');
        $('#edit-with-install-price1').removeClass('fw-bold text-primary');
        $('#edit-with-install-price2').removeClass('fw-bold text-primary');
    } else if (priceType === 'with_installation1') {
        // With installation 1
        unitPrice = parseFloat(data.var_price_with_install1 || 0);
        
        // Highlight the selected price
        $('#edit-free-install-price').removeClass('fw-bold text-primary');
        $('#edit-with-install-price1').addClass('fw-bold text-primary');
        $('#edit-with-install-price2').removeClass('fw-bold text-primary');
    } else if (priceType === 'with_installation2') {
        // With installation 2
        unitPrice = parseFloat(data.var_price_with_install2 || 0);
        
        // Highlight the selected price
        $('#edit-free-install-price').removeClass('fw-bold text-primary');
        $('#edit-with-install-price1').removeClass('fw-bold text-primary');
        $('#edit-with-install-price2').addClass('fw-bold text-primary');
    }
    
    const totalAmount = unitPrice * quantity;
    $('#edit-total-amount').text(formatCurrency(totalAmount));
    
    // Update unit price for the booking
    $('#edit-unit-price').val(unitPrice);
}

// Add a technician to the list in the edit form
function addTechnicianToList() {
    const techSelect = $('#technician-select');
    const techId = techSelect.val();
    
    if (!techId) {
        // Show toast notification for empty selection
        productBookingsManager.showWarningToast('Warning', 'Please select a technician');
        return;
    }
    
    const techName = techSelect.find('option:selected').data('name');
    
    // Check if technician is already in the list
    const alreadyAssigned = assignedTechnicians.some(tech => tech.id === techId || tech.id === parseInt(techId));
    
    if (alreadyAssigned) {
        // Show toast notification for duplicate technician
        productBookingsManager.showWarningToast('Warning', `${techName} is already assigned to this booking`);
        return;
    }
    
    // Check for scheduling conflicts
    const bookingId = $('#edit-id').val();
    const preferredDate = $('#edit-delivery-date').val();
    const preferredTime = $('#edit-delivery-time').val();
    
    // Show loading indicator
    const addBtn = $('#add-technician-btn');
    const originalText = addBtn.text();
    addBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    
    // Check for conflicts
    checkTechnicianScheduleConflict(techId, bookingId, preferredDate, preferredTime, function(hasConflict, message) {
        // Reset button state
        addBtn.prop('disabled', false).text(originalText);
        
        if (hasConflict) {
            productBookingsManager.showErrorToast('Scheduling Conflict', message);
            
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
        // Remove from tracking array - convert both to numbers for comparison
        const numTechId = parseInt(techId);
        assignedTechnicians = assignedTechnicians.filter(tech => parseInt(tech.id) !== numTechId);
        // Remove badge from UI
        badge.remove();
    });
    
    techList.append(badge);
}

// Load warehouses with inventory information for a specific variant and quantity
function loadWarehousesForVariant(variantId, quantity, currentWarehouseId) {
    const warehouseSelect = $('#edit-warehouse-id');
    warehouseSelect.empty();
    warehouseSelect.append(`<option value="auto" selected>Auto-select warehouse (Default)</option>`);
    
    // Show loading indicator
    warehouseSelect.prop('disabled', true);
    warehouseSelect.append(`<option disabled>Loading warehouses...</option>`);
    
    $.ajax({
        url: '/api/inventory/variant-warehouses',
        method: 'GET',
        data: {
            variant_id: variantId,
            quantity: quantity
        },
        success: function(response) {
            warehouseSelect.empty();
            warehouseSelect.append(`<option value="auto">Auto-select warehouse (Default)</option>`);
            
            // If no warehouses have enough inventory, disable the auto option
            let anyWarehouseHasEnough = false;
            
            if (response.data && response.data.length > 0) {
                response.data.forEach(warehouse => {
                    const hasEnough = warehouse.available_quantity >= quantity;
                    anyWarehouseHasEnough = anyWarehouseHasEnough || hasEnough;
                    
                    const option = $(`<option value="${warehouse.whouse_id}" ${!hasEnough ? 'disabled' : ''}>${warehouse.whouse_name} (Available: ${warehouse.available_quantity})</option>`);
                    warehouseSelect.append(option);
                    
                    // If this is the current warehouse, select it
                    if (currentWarehouseId && warehouse.whouse_id == currentWarehouseId) {
                        option.prop('selected', true);
                    }
                });
            } else {
                warehouseSelect.append(`<option disabled>No warehouses found</option>`);
            }
            
            // If no warehouse has enough inventory, disable the auto option
            if (!anyWarehouseHasEnough) {
                warehouseSelect.find('option[value="auto"]').prop('disabled', true)
                    .text('Auto-select warehouse (No warehouse has enough inventory)');
            } else {
                // If we don't have a selected warehouse already, select auto
                if (!currentWarehouseId) {
                    warehouseSelect.find('option[value="auto"]').prop('selected', true);
                }
            }
            
            warehouseSelect.prop('disabled', false);
        },
        error: function(xhr) {
            warehouseSelect.empty();
            warehouseSelect.append(`<option value="auto" selected>Auto-select warehouse (Default)</option>`);
            warehouseSelect.append(`<option disabled>Error loading warehouses</option>`);
            warehouseSelect.prop('disabled', false);
            
            console.error("Error loading warehouses:", xhr);
        }
    });
}

// Save product booking changes
function saveProductBooking() {
    const bookingId = $('#edit-id').val();
    const status = $('#edit-status').val();
    const priceType = $('#edit-price-type').val();
    const preferredDate = $('#edit-delivery-date').val();
    const preferredTime = $('#edit-delivery-time').val();
    const warehouseId = $('#edit-warehouse-id').val();
    
    // Get unit price from the hidden field (set by updateTotalAmount function)
    const unitPrice = parseFloat($('#edit-unit-price').val() || 0);
    
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
        productBookingsManager.showErrorToast('Validation Error', 'Delivery date and time cannot be in the past');
        return;
    }
    
    // Prepare data for update
    const updateData = {
        bookingId: bookingId,
        status: status,
        priceType: priceType,
        unitPrice: unitPrice,
        preferredDate: preferredDate,
        preferredTime: preferredTime,
        technicians: techniciansData
    };
    
    // Only include warehouse ID if it's not 'auto'
    if (warehouseId && warehouseId !== 'auto') {
        updateData.warehouseId = warehouseId;
    }
    
    // If we have description field, include it
    const description = $('#edit-description').val();
    if (description) {
        updateData.description = description;
    }
    
    // Show loading indicator
    const saveBtn = $('#saveProductBookingBtn');
    const originalText = saveBtn.text();
    saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
    
    // Send update request
    $.ajax({
        url: '/api/admin/product-bookings/update',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(updateData),
        success: function(response) {
            $('#editProductBookingModal').modal('hide');
            productBookingsManager.showSuccessToast('Success', response.message);
            productBookingsManager.refresh();
        },
        error: function(xhr) {
            saveBtn.prop('disabled', false).text(originalText);
            
            const errorMsg = xhr.responseJSON?.message || 'Failed to update product booking';
            
            // Check for scheduling conflict errors
            if (errorMsg.includes('Scheduling conflict')) {
                productBookingsManager.showErrorToast('Technician Scheduling Conflict', errorMsg);
                
                // Highlight the technician select to indicate the error source
                $('#technician-select').addClass('is-invalid border-danger');
                setTimeout(() => {
                    $('#technician-select').removeClass('is-invalid border-danger');
                }, 3000);
            } else {
                productBookingsManager.showErrorToast('Error', errorMsg);
            }
        },
        complete: function() {
            // Reset button state
            saveBtn.prop('disabled', false).text(originalText);
        }
    });
}

// Confirm product booking deletion
function confirmDeleteProductBooking(rowData) {
    $('#delete-id').text(rowData.pb_id);
    $('#delete-customer').text(rowData.customer_name);
    $('#delete-product').text(rowData.prod_name);
    
    $('#deleteProductBookingModal').modal('show');
}

// Delete product booking
function deleteProductBooking() {
    const bookingId = $('#delete-id').text();
    
    $.ajax({
        url: `/api/admin/product-bookings/delete/${bookingId}`,
        method: 'POST',
        success: function(response) {
            $('#deleteProductBookingModal').modal('hide');
            productBookingsManager.showSuccessToast('Success', response.message);
            productBookingsManager.refresh();
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Failed to delete product booking';
            productBookingsManager.showErrorToast('Error', errorMsg);
        }
    });
}

// Validate technician assignments when date or time changes
function validateTechnicianAssignments() {
    const bookingId = $('#edit-id').val();
    const preferredDate = $('#edit-delivery-date').val();
    const preferredTime = $('#edit-delivery-time').val();
    
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