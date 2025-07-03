<?php
$title = 'Product Management - AirProtech';
$activeTab = 'product_management';

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
    .product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
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
    .tab-content {
        padding: 20px 0;
    }
    .feature-item, .spec-item, .variant-item {
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background-color: #f8f9fa;
    }
    .feature-remove, .spec-remove, .variant-remove {
        cursor: pointer;
        color: #dc3545;
    }
    .preview-image {
        max-width: 100%;
        max-height: 200px;
        margin-top: 10px;
        border-radius: 8px;
    }
    
    /* Responsive table styles */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
        margin-bottom: 1rem;
        -ms-overflow-style: -ms-autohiding-scrollbar;
    }
    
    /* Ensure table takes full width but allows scrolling */
    #productsTable {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 800px; /* Minimum width to ensure proper layout */
    }
    
    #productsTable thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 12px 8px;
        vertical-align: middle;
    }
    
    #productsTable tbody td {
        padding: 15px 8px;
        vertical-align: middle;
    }
    
    #productsTable tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.03);
    }
    
    /* Collapsible row styling */
    .details-control {
        cursor: pointer;
        font-size: 1.2rem;
        color: #007bff;
    }
    
    tr.details-row {
        background-color: #f8f9fa;
    }
    
    .detail-content {
        padding: 15px;
    }
    
    .nested-table {
        width: 100%;
        margin-top: 10px;
    }
    
    .nested-table th {
        background-color: #e9ecef;
        padding: 8px;
        font-weight: 600;
    }
    
    .nested-table td {
        padding: 8px;
        border-bottom: 1px solid #dee2e6;
    }
    
    /* Additional responsive styles for small screens */
    @media (max-width: 767.98px) {
        .product-image {
            width: 60px;
            height: 60px;
        }
        
        .action-icon {
            width: 28px;
            height: 28px;
            margin-right: 3px;
        }
        
        .filter-card .row .col-md-6 {
            margin-top: 10px;
        }
        
        #productsTable th, 
        #productsTable td {
            padding: 10px 5px;
            white-space: nowrap;
        }
        
        /* Improve scrolling experience on mobile */
        .table-responsive {
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }
        
        /* Make sure detail rows display well on mobile */
        tr.details-row .detail-content {
            padding: 10px;
        }
        
        tr.details-row .detail-content .row {
            flex-direction: column;
        }
        
        tr.details-row .detail-content .col-md-4 {
            width: 100%;
            margin-bottom: 15px;
        }
    }
</style>
HTML;

// Start output buffering for content
ob_start();
?>

<div class="container-fluid py-4">
    
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Product Management</h1>
            <p class="text-muted">Manage products, features, specifications, and variants</p>
        </div>
        <div class="col-auto">
            <button id="addProductBtn" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add New Product
            </button>
        </div>
    </div>


    <!-- Products Table Card -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="productsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th width="40"></th>
                            <th width="100">Image</th>
                            <th width="200">Name</th>
                            <th>Description</th>
                            <th width="120">Created At</th>
                            <th width="120">Updated At</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Table content will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="productId" name="productId">
                    
                    <ul class="nav nav-tabs" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Product Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button" role="tab" aria-controls="features" aria-selected="false">Features</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab" aria-controls="specs" aria-selected="false">Specifications</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button" role="tab" aria-controls="variants" aria-selected="false">Variants</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="productTabsContent">
                        <!-- Product Details Tab -->
                        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                            <div class="mb-3">
                                <label for="productName" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="productName" name="productName" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="productDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="productDescription" name="productDescription" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="productImage" class="form-label">Product Image *</label>
                                <input type="file" class="form-control" id="productImage" name="productImage" accept="image/*">
                                <div id="imagePreview" class="mt-2"></div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="productHasFreeInstall" name="productHasFreeInstall" checked>
                                    <label class="form-check-label" for="productHasFreeInstall">
                                        Offer Free Installation Option
                                    </label>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4" id="freeInstallDiscountContainer">
                                    <label for="productDiscountFreeInstall" class="form-label">Free Install Discount (%)</label>
                                    <input type="number" class="form-control" id="productDiscountFreeInstall" name="productDiscountFreeInstall" min="0" max="100" step="0.01" value="15">
                                </div>
                                <div class="col-md-4">
                                    <label for="productDiscountWithInstall1" class="form-label">With Install Discount 1 (%)</label>
                                    <input type="number" class="form-control" id="productDiscountWithInstall1" name="productDiscountWithInstall1" min="0" max="100" step="0.01" value="25">
                                </div>
                                <div class="col-md-4" id="withInstall2Container">
                                    <label for="productDiscountWithInstall2" class="form-label">With Install Discount 2 (%)</label>
                                    <input type="number" class="form-control" id="productDiscountWithInstall2" name="productDiscountWithInstall2" min="0" max="100" step="0.01" value="0">
                                    <div class="form-text">Set to 0 to hide this option</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Features Tab -->
                        <div class="tab-pane fade" id="features" role="tabpanel" aria-labelledby="features-tab">
                            <div id="featuresContainer">
                                <!-- Features will be added here dynamically -->
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" id="addFeatureBtn" class="btn btn-outline-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Add Feature
                                </button>
                            </div>
                        </div>
                        
                        <!-- Specifications Tab -->
                        <div class="tab-pane fade" id="specs" role="tabpanel" aria-labelledby="specs-tab">
                            <div id="specsContainer">
                                <!-- Specs will be added here dynamically -->
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" id="addSpecBtn" class="btn btn-outline-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Add Specification
                                </button>
                            </div>
                        </div>
                        
                        <!-- Variants Tab -->
                        <div class="tab-pane fade" id="variants" role="tabpanel" aria-labelledby="variants-tab">
                            <div id="variantsContainer">
                                <!-- Variants will be added here dynamically -->
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" id="addVariantBtn" class="btn btn-outline-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Add Variant
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="saveProductBtn" class="btn btn-primary">Save Product</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this product? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Include jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">

<!-- Include DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<!-- DataTablesManager script -->
<script src="/assets/js/utility/DataTablesManager.js"></script>

<!-- JavaScript for Product Management -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Log the first data item to see the actual property names
    fetch('/api/products')
        .then(response => response.json())
        .then(result => {
            if (result.data && result.data.length > 0) {
                console.log('First product data object:', result.data[0]);
            }
        })
        .catch(error => console.error('Error fetching products:', error));
    
    // Initialize DataTable
    let productsTable = $('#productsTable').DataTable({
        ajax: {
            url: '/api/products',
            dataSrc: 'data'
        },
        responsive: true,
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        columns: [
            {
                className: 'details-control',
                orderable: false,
                data: null,
                defaultContent: '<i class="bi bi-chevron-down"></i>',
                width: '40px'
            },
            { 
                data: 'prod_image',
                render: function(data) {
                    return data ? '<img src="/' + data + '" class="product-image" alt="Product Image">' : 'No Image';
                },
                width: '100px'
            },
            { 
                data: 'prod_name',
                width: '200px'
            },
            { 
                data: 'prod_description',
                render: function(data) {
                    return data ? (data.length > 100 ? data.substring(0, 100) + '...' : data) : 'N/A';
                }
            },
            { 
                data: 'prod_created_at',
                render: function(data) {
                    return data ? new Date(data).toLocaleDateString() : 'N/A';
                },
                width: '120px'
            },
            { 
                data: 'prod_updated_at',
                render: function(data) {
                    return data ? new Date(data).toLocaleDateString() : 'N/A';
                },
                width: '120px'
            },
            {
                data: null,
                orderable: false,
                width: '120px',
                render: function(data) {
                    return `
                        <div class="d-flex">
                            <div class="action-icon action-icon-view view-btn me-1" data-id="${data.prod_id}">
                                <i class="bi bi-eye"></i>
                            </div>
                            <div class="action-icon action-icon-edit edit-btn me-1" data-id="${data.prod_id}">
                                <i class="bi bi-pencil"></i>
                            </div>
                            <div class="action-icon action-icon-delete delete-btn" data-id="${data.prod_id}">
                                <i class="bi bi-trash"></i>
                            </div>
                        </div>
                    `;
                }
            }
        ],
        order: [[4, 'desc']], // Sort by Created At column by default
        columnDefs: [
            { responsivePriority: 1, targets: [0, 2, 6] }, // These columns will be displayed first
        ]
    });
    
    // Create a DataTablesManager instance WITHOUT initializing the table again
    const productsManager = new DataTablesManager('productsTable', {
        initialize: false  // Prevent the manager from initializing a new DataTable
    });
    
    // Manually set the dataTable property
    productsManager.dataTable = productsTable;
    
    // Handle row expand/collapse for details
    $('#productsTable tbody').on('click', 'td.details-control', function() {
        let tr = $(this).closest('tr');
        let row = productsTable.row(tr);
        let icon = $(this).find('i');
        
        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        } else {
            // Open this row
            let productId = row.data().prod_id;
            fetchProductDetails(productId, function(details) {
                row.child(formatProductDetails(details)).show();
                tr.addClass('shown');
                icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
            });
        }
    });
    
    // Format the product details row
    function formatProductDetails(details) {
        let featuresHtml = '';
        let specsHtml = '';
        let variantsHtml = '';
        
        // Generate features HTML
        if (details.features && details.features.length > 0) {
            featuresHtml = '<table class="nested-table"><thead><tr><th>Feature</th></tr></thead><tbody>';
            details.features.forEach(function(feature) {
                featuresHtml += `<tr><td>${feature.feature_name || 'N/A'}</td></tr>`;
            });
            featuresHtml += '</tbody></table>';
        } else {
            featuresHtml = '<p>No features available</p>';
        }
        
        // Generate specs HTML
        if (details.specs && details.specs.length > 0) {
            specsHtml = '<table class="nested-table"><thead><tr><th>Specification</th><th>Value</th></tr></thead><tbody>';
            details.specs.forEach(function(spec) {
                specsHtml += `<tr><td>${spec.spec_name || 'N/A'}</td><td>${spec.spec_value || 'N/A'}</td></tr>`;
            });
            specsHtml += '</tbody></table>';
        } else {
            specsHtml = '<p>No specifications available</p>';
        }
        
        // Generate variants HTML
        if (details.variants && details.variants.length > 0) {
            // Determine which pricing options to show based on product settings
            const hasFreeInstall = details.prod_has_free_install_option !== false;
            const hasSecondInstallOption = !hasFreeInstall && (details.prod_discount_with_install_pct2 || 0) > 0;
            
            // Build table header based on available options
            let headerHtml = '<tr><th>Capacity</th><th>SRP Price</th>';
            if (hasFreeInstall) {
                headerHtml += `<th>Free Install Price (${details.prod_discount_free_install_pct || 0}%)</th>`;
                headerHtml += `<th>With Install Price (${details.prod_discount_with_install_pct1 || 0}%)</th>`;
            } else {
                // Only show second option when free install is disabled
                headerHtml += `<th>With Install Price${hasSecondInstallOption ? ' 1' : ''} (${details.prod_discount_with_install_pct1 || 0}%)</th>`;
                if (hasSecondInstallOption) {
                    headerHtml += `<th>With Install Price 2 (${details.prod_discount_with_install_pct2 || 0}%)</th>`;
                }
            }
            headerHtml += '<th>Power Consumption</th></tr>';
            
            variantsHtml = '<table class="nested-table"><thead>' + headerHtml + '</thead><tbody>';
            
            // Generate rows with conditional columns
            details.variants.forEach(function(variant) {
                let rowHtml = `
                    <tr>
                        <td>${variant.var_capacity || 'N/A'}</td>
                        <td>${variant.var_srp_price ? '₱' + parseFloat(variant.var_srp_price).toLocaleString() : 'N/A'}</td>`;
                
                // Handle pricing columns based on installation options
                if (hasFreeInstall) {
                    // When free installation is offered
                    rowHtml += `<td>${variant.var_price_free_install ? '₱' + parseFloat(variant.var_price_free_install).toLocaleString() : 'N/A'}</td>`;
                    rowHtml += `<td>${variant.var_price_with_install1 ? '₱' + parseFloat(variant.var_price_with_install1).toLocaleString() : 'N/A'}</td>`;
                } else {
                    // When free installation is not offered
                    rowHtml += `<td>${variant.var_price_with_install1 ? '₱' + parseFloat(variant.var_price_with_install1).toLocaleString() : 'N/A'}</td>`;
                    
                    // Add second paid installation option if available
                    if (hasSecondInstallOption) {
                        rowHtml += `<td>${variant.var_price_with_install2 ? '₱' + parseFloat(variant.var_price_with_install2).toLocaleString() : 'N/A'}</td>`;
                    }
                }
                
                rowHtml += `<td>${variant.var_power_consumption || 'N/A'}</td>
                    </tr>`;
                    
                variantsHtml += rowHtml;
            });
            variantsHtml += '</tbody></table>';
        } else {
            variantsHtml = '<p>No variants available</p>';
        }
        
        return `
            <div class="detail-content">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Features</h6>
                        ${featuresHtml}
                    </div>
                    <div class="col-md-4">
                        <h6>Specifications</h6>
                        ${specsHtml}
                    </div>
                    <div class="col-md-4">
                        <h6>Variants</h6>
                        ${variantsHtml}
                    </div>
                </div>
            </div>
        `;
    }
    
    // Fetch product details
    function fetchProductDetails(productId, callback) {
        fetch(`/api/products/${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Product detail data:', data.data); // Debug: Log the entire product details
                    callback(data.data);
                } else {
                    console.error('Error fetching product details:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    // Add New Product Button
    $('#addProductBtn').on('click', function() {
        resetProductForm();
        $('#productModalLabel').text('Add New Product');
        
        // Automatically add 4 common specification rows for new products
        addSpecRow('MODEL', '');
        addSpecRow('CAPACITY', '');
        addSpecRow('REFRIGERANT', '');
        addSpecRow('COMPRESSOR', '');
        
        $('#productModal').modal('show');
    });
    
    // Edit Product Button
    $(document).on('click', '.edit-btn', function() {
        const productId = $(this).data('id');
        resetProductForm(); // This already resets all fields to editable state
        $('#productModalLabel').text('Edit Product');
        $('#productId').val(productId);
        
        fetchProductDetails(productId, function(product) {
            // Fill in product details
            $('#productName').val(product.prod_name);
            $('#productDescription').val(product.prod_description);
            
            // Set discount fields and ensure they're editable
            $('#productDiscountFreeInstall').val(product.prod_discount_free_install_pct || 15).prop('readonly', false);
            $('#productDiscountWithInstall1').val(product.prod_discount_with_install_pct1 || 25).prop('readonly', false);
            $('#productDiscountWithInstall2').val(product.prod_discount_with_install_pct2 || 0).prop('readonly', false);
            $('#productHasFreeInstall')
                .prop('checked', product.prod_has_free_install_option !== false)
                .prop('disabled', false);
            
            // Update discount fields visibility based on product settings
            updateDiscountFieldsVisibility();
            
            if (product.prod_image) {
                $('#imagePreview').html(`<img src="/${product.prod_image}" class="preview-image" alt="Product Image">`);
            }
            
            // Add features
            if (product.features && product.features.length > 0) {
                product.features.forEach(function(feature) {
                    addFeatureRow(feature.feature_name, feature.feature_id);
                });
            }
            
            // Add specs
            if (product.specs && product.specs.length > 0) {
                product.specs.forEach(function(spec) {
                    addSpecRow(spec.spec_name, spec.spec_value, spec.spec_id);
                });
            }
            
            // Add variants
            if (product.variants && product.variants.length > 0) {
                product.variants.forEach(function(variant) {
                    addVariantRow(
                        variant.var_id,
                        variant.var_capacity,
                        variant.var_srp_price,
                        variant.var_installation_fee,
                        variant.var_power_consumption
                    );
                });
            }
            
            $('#productModal').modal('show');
        });
    });
    
    // View Product Button
    $(document).on('click', '.view-btn', function() {
        const productId = $(this).data('id');
        
        fetchProductDetails(productId, function(product) {
            // Fill in product details in read-only mode
            $('#productModalLabel').text('View Product');
            $('#productId').val(productId);
            
            $('#productName').val(product.prod_name).prop('readonly', true);
            $('#productDescription').val(product.prod_description).prop('readonly', true);
            $('#productImage').prop('disabled', true);
            
            // Set discount fields in read-only mode
            $('#productDiscountFreeInstall').val(product.prod_discount_free_install_pct || 15).prop('readonly', true);
            $('#productDiscountWithInstall1').val(product.prod_discount_with_install_pct1 || 25).prop('readonly', true);
            $('#productDiscountWithInstall2').val(product.prod_discount_with_install_pct2 || 0).prop('readonly', true);
            $('#productHasFreeInstall').prop('checked', product.prod_has_free_install_option !== false).prop('disabled', true);
            
            // Update discount fields visibility based on product settings
            updateDiscountFieldsVisibility();
            
            if (product.prod_image) {
                $('#imagePreview').html(`<img src="/${product.prod_image}" class="preview-image" alt="Product Image">`);
            }
            
            // Add features in read-only mode
            if (product.features && product.features.length > 0) {
                product.features.forEach(function(feature) {
                    addFeatureRow(feature.feature_name, feature.feature_id, true);
                });
            }
            
            // Add specs in read-only mode
            if (product.specs && product.specs.length > 0) {
                product.specs.forEach(function(spec) {
                    addSpecRow(spec.spec_name, spec.spec_value, spec.spec_id, true);
                });
            }
            
            // Add variants in read-only mode
            if (product.variants && product.variants.length > 0) {
                product.variants.forEach(function(variant) {
                    addVariantRow(
                        variant.var_id,
                        variant.var_capacity,
                        variant.var_srp_price,
                        variant.var_installation_fee,
                        variant.var_power_consumption,
                        true
                    );
                });
            }
            
            // Hide save button and show close button
            $('#saveProductBtn').hide();
            $('#productModal .modal-footer .btn-secondary').text('Close');
            
            $('#productModal').modal('show');
        });
    });
    
    // Delete Product Button
    $(document).on('click', '.delete-btn', function() {
        const productId = $(this).data('id');
        $('#confirmDeleteBtn').data('id', productId);
        $('#deleteConfirmModal').modal('show');
    });
    
    // Confirm Delete Button
    $('#confirmDeleteBtn').on('click', function() {
        const productId = $(this).data('id');
        
        fetch(`/api/products/delete/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#deleteConfirmModal').modal('hide');
                    productsTable.ajax.reload();
                    productsManager.showSuccessToast('Success', 'Product deleted successfully');
                } else {
                    productsManager.showErrorToast('Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                productsManager.showErrorToast('Error', 'An error occurred while deleting the product');
            });
    });
    
    // Reset product form
    function resetProductForm() {
        $('#productForm')[0].reset();
        $('#productId').val('');
        $('#imagePreview').empty();
        $('#featuresContainer').empty();
        $('#specsContainer').empty();
        $('#variantsContainer').empty();
        $('#details-tab').tab('show');
        
        // Reset form controls to editable state
        $('#productName').prop('readonly', false);
        $('#productDescription').prop('readonly', false);
        $('#productImage').prop('disabled', false);
        
        // Reset discount fields to editable state
        $('#productDiscountFreeInstall').prop('readonly', false);
        $('#productDiscountWithInstall1').prop('readonly', false);
        $('#productDiscountWithInstall2').prop('readonly', false);
        $('#productHasFreeInstall').prop('disabled', false);
        
        // Show save button again
        $('#saveProductBtn').show();
        $('#productModal .modal-footer .btn-secondary').text('Cancel');
        
        // Update discount field visibility
        updateDiscountFieldsVisibility();
    }
    
    // Function to update visibility of discount fields based on product settings
    function updateDiscountFieldsVisibility() {
        const hasFreeInstall = $('#productHasFreeInstall').is(':checked');
        const withInstall2Value = parseFloat($('#productDiscountWithInstall2').val()) || 0;
        
        // Show/hide free installation discount field
        $('#freeInstallDiscountContainer').toggle(hasFreeInstall);
        
        // Show/hide the second install discount field based on free installation option
        $('#withInstall2Container').toggle(!hasFreeInstall);
        
        // If we're hiding the second discount field and it has a value, reset it to 0
        if (hasFreeInstall && withInstall2Value > 0) {
            $('#productDiscountWithInstall2').val(0);
        }
        
        // Update the label for install discount 1
        const label1 = hasFreeInstall ? 'With Install Discount (%)' : 
                       withInstall2Value > 0 ? 'With Install Discount 1 (%)' : 'With Install Discount (%)';
        $('#productDiscountWithInstall1').closest('.col-md-4').find('label').text(label1);
    }
    
    // Toggle discount field visibility when checkbox is clicked
    $('#productHasFreeInstall').on('change', updateDiscountFieldsVisibility);
    
    // Also update visibility when discount 2 value changes
    $('#productDiscountWithInstall2').on('input', updateDiscountFieldsVisibility);
    
    // Reset form when modal is closed
    $('#productModal').on('hidden.bs.modal', function() {
        resetProductForm();
    });
    
    // Initialize form when modal is shown
    $('#productModal').on('shown.bs.modal', function() {
        // Force re-enable inputs that might have been disabled in view mode
        $('#productName').prop('readonly', false);
        $('#productDescription').prop('readonly', false);
        $('#productImage').prop('disabled', false);
        $('#productDiscountFreeInstall').prop('readonly', false);
        $('#productDiscountWithInstall1').prop('readonly', false);
        $('#productDiscountWithInstall2').prop('readonly', false);
        $('#productHasFreeInstall').prop('disabled', false);
        
        // Only disable in view mode
        if ($('#saveProductBtn').css('display') === 'none') {
            $('#productName').prop('readonly', true);
            $('#productDescription').prop('readonly', true);
            $('#productImage').prop('disabled', true);
            $('#productDiscountFreeInstall').prop('readonly', true);
            $('#productDiscountWithInstall1').prop('readonly', true);
            $('#productDiscountWithInstall2').prop('readonly', true);
            $('#productHasFreeInstall').prop('disabled', true);
        }
        
        updateDiscountFieldsVisibility();
    });
    
    // Event listener for the checkbox to ensure it works correctly
    $('#productHasFreeInstall').on('click', function() {
        // If this click doesn't work due to disabled state, force update the state
        if ($(this).prop('disabled')) {
            $(this).prop('disabled', false);
            return false; // Prevent default behavior and retry the click
        }
    });
    
    // Validate product form
    function validateProductForm() {
        if (!$('#productName').val()) {
            productsManager.showErrorToast('Validation Error', 'Please enter a product name');
            $('#details-tab').tab('show');
            return false;
        }
        
        const productId = $('#productId').val();
        const isEditMode = productId !== '';
        
        // For new products, require an image
        if (!isEditMode && !$('#productImage').val() && !$('#imagePreview img').length) {
            productsManager.showErrorToast('Validation Error', 'Please select a product image');
            $('#details-tab').tab('show');
            return false;
        }
        
        // Require at least one variant
        if ($('.variant-row').length === 0) {
            productsManager.showErrorToast('Validation Error', 'Please add at least one product variant');
            $('#variants-tab').tab('show');
            return false;
        }
        
        return true;
    }
    
    // Add validation check when switching tabs
    $('#productTabs button').on('click', function(e) {
        // Only validate when leaving the details tab
        if ($('#details-tab').hasClass('active')) {
            const productId = $('#productId').val();
            const isEditMode = productId !== '';
            
            // Check required fields
            let isValid = true;
            let errorMessage = '';
            
            if (!$('#productName').val()) {
                isValid = false;
                errorMessage = 'Please enter a product name';
            } else if (!isEditMode && !$('#productImage').val() && !$('#imagePreview img').length) {
                isValid = false;
                errorMessage = 'Please select a product image';
            }
            
            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                productsManager.showErrorToast('Validation Error', errorMessage);
                return false;
            }
        }
    });
    
    // Add Feature Row
    $('#addFeatureBtn').on('click', function() {
        addFeatureRow();
    });
    
    function addFeatureRow(featureName = '', featureId = null, readOnly = false) {
        const featureRow = `
            <div class="feature-row row mb-2" ${featureId ? `data-id="${featureId}"` : ''}>
                <div class="col-${readOnly ? '12' : '10'}">
                    <input type="text" class="form-control feature-name" placeholder="Feature" value="${featureName}" ${readOnly ? 'readonly' : ''}>
                </div>
                ${readOnly ? '' : `
                <div class="col-2">
                    <button type="button" class="btn btn-outline-danger feature-remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                `}
            </div>
        `;
        
        $('#featuresContainer').append(featureRow);
    }
    
    // Remove Feature Row
    $(document).on('click', '.feature-remove', function() {
        $(this).closest('.feature-row').remove();
    });
    
    // Add Spec Row
    $('#addSpecBtn').on('click', function() {
        addSpecRow();
    });
    
    function addSpecRow(specName = '', specValue = '', specId = null, readOnly = false) {
        const specRow = `
            <div class="spec-row row mb-2" ${specId ? `data-id="${specId}"` : ''}>
                <div class="col-${readOnly ? '6' : '5'}">
                    <input type="text" class="form-control spec-name" placeholder="Specification Name" value="${specName}" ${readOnly ? 'readonly' : ''}>
                </div>
                <div class="col-${readOnly ? '6' : '5'}">
                    <input type="text" class="form-control spec-value" placeholder="Specification Value" value="${specValue}" ${readOnly ? 'readonly' : ''}>
                </div>
                ${readOnly ? '' : `
                <div class="col-2">
                    <button type="button" class="btn btn-outline-danger spec-remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                `}
            </div>
        `;
        
        $('#specsContainer').append(specRow);
    }
    
    // Remove Spec Row
    $(document).on('click', '.spec-remove', function() {
        $(this).closest('.spec-row').remove();
    });
    
    // Add Variant Row
    $('#addVariantBtn').on('click', function() {
        addVariantRow();
    });
    
    function addVariantRow(variantId = null, capacity = '', srpPrice = '', installationFee = '', powerConsumption = '', readOnly = false) {
        const variantRow = `
            <div class="variant-row card mb-3" ${variantId ? `data-id="${variantId}"` : ''}>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-${readOnly ? '12' : '10'}">
                            <h6>Product Variant</h6>
                        </div>
                        ${readOnly ? '' : `
                        <div class="col-2 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm variant-remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        `}
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Capacity (HP/BTU) *</label>
                            <input type="text" class="form-control variant-capacity" placeholder="e.g., 1.0 HP or 9000 BTU" value="${capacity}" ${readOnly ? 'readonly' : ''}>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SRP Price (₱) *</label>
                            <input type="number" class="form-control variant-srp-price" min="0" step="0.01" placeholder="e.g., 25000" value="${srpPrice}" ${readOnly ? 'readonly' : ''}>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Installation Fee (₱)</label>
                            <input type="number" class="form-control variant-installation-fee" min="0" step="0.01" placeholder="e.g., 1500" value="${installationFee}" ${readOnly ? 'readonly' : ''}>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Power Consumption</label>
                            <input type="text" class="form-control variant-power-consumption" placeholder="e.g., 800W" value="${powerConsumption}" ${readOnly ? 'readonly' : ''}>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#variantsContainer').append(variantRow);
    }
    
    // Remove Variant Row
    $(document).on('click', '.variant-remove', function() {
        $(this).closest('.variant-row').remove();
    });
    
    // Save Product Button
    $('#saveProductBtn').on('click', function() {
        // Validate form
        if (!validateProductForm()) {
            return;
        }
        
        const productId = $('#productId').val();
        const isEditMode = productId !== '';
        
        // Create FormData object for file upload
        const formData = new FormData();
        
        // Add product details
        const productData = {
            PROD_NAME: $('#productName').val(),
            PROD_DESCRIPTION: $('#productDescription').val(),
            PROD_DISCOUNT_FREE_INSTALL_PCT: $('#productDiscountFreeInstall').val(),
            PROD_DISCOUNT_WITH_INSTALL_PCT1: $('#productDiscountWithInstall1').val(),
            PROD_DISCOUNT_WITH_INSTALL_PCT2: $('#productDiscountWithInstall2').val(),
            PROD_HAS_FREE_INSTALL_OPTION: $('#productHasFreeInstall').is(':checked')
        };
        
        formData.append('product', JSON.stringify(productData));
        
        // Add product image if selected
        const productImageInput = document.getElementById('productImage');
        if (productImageInput.files.length > 0) {
            formData.append('product_image', productImageInput.files[0]);
        }
        
        // Add features
        const features = [];
        $('.feature-row').each(function() {
            const featureId = $(this).data('id');
            const featureName = $(this).find('.feature-name').val();
            
            if (featureName) {
                features.push({
                    FEATURE_ID: featureId || null,
                    FEATURE_NAME: featureName
                });
            }
        });
        formData.append('features', JSON.stringify(features));
        
        // Add specifications
        const specs = [];
        $('.spec-row').each(function() {
            const specId = $(this).data('id');
            const specName = $(this).find('.spec-name').val();
            const specValue = $(this).find('.spec-value').val();
            
            if (specName && specValue) {
                specs.push({
                    SPEC_ID: specId || null,
                    SPEC_NAME: specName,
                    SPEC_VALUE: specValue
                });
            }
        });
        formData.append('specs', JSON.stringify(specs));
        
        // Add variants
        const variants = [];
        $('.variant-row').each(function() {
            const variantId = $(this).data('id');
            const capacity = $(this).find('.variant-capacity').val();
            const srpPrice = $(this).find('.variant-srp-price').val();
            const installationFee = $(this).find('.variant-installation-fee').val();
            const powerConsumption = $(this).find('.variant-power-consumption').val();
            
            if (capacity && srpPrice) {
                variants.push({
                    VAR_ID: variantId || null,
                    VAR_CAPACITY: capacity,
                    VAR_SRP_PRICE: srpPrice,
                    VAR_INSTALLATION_FEE: installationFee || 0,
                    VAR_POWER_CONSUMPTION: powerConsumption || null
                });
            }
        });
        formData.append('variants', JSON.stringify(variants));
        
        // Debug: Log the form data
        console.log('Product Data:', productData);
        console.log('Features:', features);
        console.log('Specs:', specs);
        console.log('Variants:', variants);
        
        // For debugging FormData (can't directly console.log FormData contents)
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Determine the API endpoint based on whether we're adding or editing
        const url = isEditMode ? `/api/products/${productId}` : '/api/products';
        
        // Send AJAX request
        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#productModal').modal('hide');
                    productsTable.ajax.reload();
                    productsManager.showSuccessToast('Success', isEditMode ? 'Product updated successfully' : 'Product created successfully');
                } else {
                    productsManager.showErrorToast('Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                productsManager.showErrorToast('Error', 'An error occurred while saving the product');
            });
    });
    
    // Preview product image
    $('#productImage').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').html(`<img src="${e.target.result}" class="preview-image" alt="Product Image Preview">`);
            };
            reader.readAsDataURL(file);
        } else {
            $('#imagePreview').empty();
        }
    });
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the admin template and pass in variables
include_once __DIR__ . '/../includes/admin/base.php';
?>