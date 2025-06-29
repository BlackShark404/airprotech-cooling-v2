<?php use Core\Session; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'My Bookings & Service Requests' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/home.css">
    <style>
        /* Optional: Add some styling for loading or empty states */
        .service-icon i { font-size: 1.5rem; color: #0d6efd; }
        #service-requests-list:empty::before {
            content: "Loading service requests...";
            display: block;
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Top Bar (copied from your example) -->
    <div class="top-bar py-2">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="contact-info">
                <a href="tel:+1234567890" class="me-3 text-white text-decoration-none">
                    <i class="fas fa-phone me-2"></i>0917 175 7258
                </a>
                <a href="mailto:airprotechaircon123@gmail.com" class="text-white text-decoration-none">
                    <i class="fas fa-envelope me-2"></i>airprotechaircon123@gmail.com
                </a>
            </div>
            <div class="social-links">
                <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                <a href="https://air-protechaircon.mystrikingly.com" class="text-white"><i class="fas fa-globe"></i></a>
            </div>
        </div>
    </div>

   <!-- Main Navigation --> 
   <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="/assets/images/logo/Air-TechLogo.png" alt="Logo" class="rounded-circle me-2" width="40" height="40">
                <span class="brand-text">AIR<span class="text-danger">PROTECH</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="/user/services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="/user/products">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="/user/my-bookings">My Bookings & Service Requests</a></li>
                    <!-- User Profile -->
                    <li class="nav-item dropdown ms-3">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= Session::get('profile_url') ? Session::get('profile_url') : '/assets/images/user-profile/default-profile.png' ?>" alt="Profile" class="rounded-circle me-2" width="36" height="36" style="object-fit: cover;">
                            
                            <div class="d-flex flex-column lh-sm">
                                <span class="fw-semibold small text-dark"><?=$_SESSION['full_name'] ?? 'User'?></span>
                                <small class="text-success">● Online</small>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/user/profile">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="dashboard-area py-5">
        <div class="container">
            <h2 class="fw-bold mb-2">My Bookings & Service Requests</h2>
            <p class="text-muted mb-4">View and track your orders and service history</p>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="ordersTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="true">Product Bookings</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="services-tab-button" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab" aria-controls="services" aria-selected="false">Service Requests</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="ordersTabContent">
                <!-- Bookings Tab -->
                <div class="tab-pane fade show active" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                    <!-- Filters for Product Bookings -->
                    <form id="product-booking-filters" class="d-flex flex-wrap justify-content-end align-items-center mb-4 py-4">
                        <div class="me-2 mb-2">
                            <select class="form-select" style="width: auto;" id="product-date-filter" name="date_filter">
                                <option value="All time" selected>All Time</option>
                                <option value="Last 30 days">Last 30 days</option>
                                <option value="Last 60 days">Last 60 days</option>
                                <option value="Last 90 days">Last 90 days</option>
                            </select>
                        </div>
                        <div class="me-2 mb-2">
                            <select class="form-select" style="width: auto;" id="product-status-filter" name="status_filter">
                                <option value="All Status" selected>All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="in-progress">In-Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="me-2 mb-2">
                            <input type="text" class="form-control" placeholder="Search bookings..." style="width: 200px;" id="product-booking-search" name="search_term">
                        </div>
                        <div class="mb-2">
                            <button type="reset" class="btn btn-outline-secondary">Clear</button>
                        </div>
                    </form>
                    <p id="booking-results-count" class="text-muted small mb-2"></p>

                    <!-- Product Booking Items Container (to be populated by JS) -->
                    <div id="product-bookings-container">
                        <!-- Booking items will be rendered here -->
                    </div>

                    <!-- Pagination Container (to be populated by JS) -->
                    <div id="product-pagination-container" class="d-flex justify-content-center mt-4">
                        <!-- Pagination will be rendered here -->
                    </div>
                </div>

                <!-- Service Requests Tab -->
                <div class="tab-pane fade" id="services" role="tabpanel" aria-labelledby="services-tab-button">
                    <!-- Filters for Service Requests -->
                    <form id="service-request-filters" class="d-flex flex-wrap justify-content-end align-items-center mb-4 py-4">
                        <div class="me-2 mb-2">
                            <select class="form-select" style="width: auto;" id="date-filter" name="date_filter">
                                <option value="All time" selected>All Time</option>
                                <option value="Last 30 days">Last 30 days</option>
                                <option value="Last 60 days">Last 60 days</option>
                                <option value="Last 90 days">Last 90 days</option>
                            </select>
                        </div>
                        <div class="me-2 mb-2">
                            <select class="form-select" style="width: auto;" id="status-filter" name="status_filter">
                                <option value="All Status" selected>All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="in-progress">In-Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="me-2 mb-2">
                            <input type="text" class="form-control" placeholder="Search requests..." style="width: 200px;" id="service-request-search" name="search_term">
                        </div>
                        <div class="mb-2">
                            <button type="reset" class="btn btn-outline-secondary">Clear</button>
                        </div>
                    </form>
                    <p id="service-results-count" class="text-muted small mb-2"></p>

                    <!-- Service Request Items Container (to be populated by JS) -->
                    <div id="service-requests-list-container">
                    </div>

                    <!-- Pagination Container (to be populated by JS) -->
                    <div id="services-pagination-container" class="d-flex justify-content-center mt-4">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Request Detail Modal -->
    <div class="modal fade" id="serviceRequestDetailModal" tabindex="-1" aria-labelledby="serviceRequestDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="serviceRequestDetailModalLabel">
                        <span class="service-icon me-2" id="modal-service-icon"></span>
                        Service Request: <span id="modal-service-id" class="fw-bold"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <div class="d-flex justify-content-between align-items-start mb-3 p-3">
                    <h4 id="modal-service-name" class="fw-bold mb-0"></h4>
                    <span id="modal-status-badge" class="badge"></span>
                </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Service Information</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2"><strong>Service Type:</strong> <span id="modal-service-description"></span></p>
                                    <p class="mb-2"><strong>Estimated Cost:</strong> <span id="modal-estimated-cost" class="fw-bold"></span></p>
                                    <p class="mb-0"><strong>Priority:</strong> <span id="modal-priority"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Schedule Details</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2"><strong>Preferred Date:</strong> <span id="modal-requested-date"></span></p>
                                    <p class="mb-2"><strong>Preferred Time:</strong> <span id="modal-requested-time"></span></p>
                                    <p class="mb-0"><strong>Service Address:</strong> <span id="modal-address"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Request Details</h6>
                        </div>
                        <div class="card-body">
                            <p id="modal-notes" style="white-space: pre-wrap;"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Booking Detail Modal -->
    <div class="modal fade" id="productBookingDetailModal" tabindex="-1" aria-labelledby="productBookingDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="productBookingDetailModalLabel">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Product Booking: <span id="modal-booking-id" class="fw-bold"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <img id="modal-product-image" src="" alt="Product Image" class="img-fluid rounded shadow-sm mb-3" style="max-height: 200px;">
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h4 id="modal-product-name" class="fs-4 fw-bold text-primary mb-0"></h4>
                                <span id="modal-status" class="badge rounded-pill px-3 py-2"></span>
                            </div>
                            <p class="mb-2">Variant: <span id="modal-variant" class="badge bg-secondary rounded-pill px-3"></span></p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <p class="mb-0">Quantity: <span id="modal-quantity" class="fw-bold"></span></p>
                                    <p class="mb-0">Unit Price: <span id="modal-unit-price" class="fw-bold"></span></p>
                                </div>
                                <div>
                                    <p class="mb-0">Total Amount: <span id="modal-total-amount" class="fs-4 fw-bold text-primary"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Booking Details</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2"><strong>Booking Date:</strong> <span id="modal-booking-date" class="text-muted"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-truck me-2"></i>Delivery Details</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2"><strong>Preferred Date:</strong> <span id="modal-preferred-date" class="fw-bold"></span></p>
                                    <p class="mb-2"><strong>Preferred Time:</strong> <span id="modal-preferred-time" class="fw-bold"></span></p>
                                    <p class="mb-0"><strong>Delivery Address:</strong> <span id="modal-delivery-address" class="text-muted"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Additional Instructions</h6>
                        </div>
                        <div class="card-body">
                            <p id="modal-description" class="text-muted" style="white-space: pre-wrap;">No additional instructions provided</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require __DIR__. '/../includes/shared/footer.php' ?>

    <!-- JS Files -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    
    <script src="/assets/js/utility/ServiceRequestsManager.js"></script>
    <script src="/assets/js/utility/ProductBookingManager.js"></script>

    <script>
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Product Bookings by default
            initializeProductBookings();
            
            // Check if we are on the "Service Requests" tab
            const servicesTab = document.getElementById('services');
            if (servicesTab && servicesTab.classList.contains('active')) {
                initializeServiceRequests();
            }

            // Initialize if the service requests tab is clicked
            const servicesTabButton = document.getElementById('services-tab-button');
            if (servicesTabButton) {
                servicesTabButton.addEventListener('shown.bs.tab', function (event) {
                    if (event.target.getAttribute('data-bs-target') === '#services') {
                        initializeServiceRequests();
                    }
                });
            }

            // Initialize if the bookings tab is clicked (redundant now, but keeping for consistency)
            const ordersTabButton = document.getElementById('orders-tab');
            if (ordersTabButton) {
                ordersTabButton.addEventListener('shown.bs.tab', function (event) {
                    if (event.target.getAttribute('data-bs-target') === '#orders') {
                        initializeProductBookings();
                    }
                });
            }
        });
        
        let serviceRequestsManagerInstance = null;
        let productBookingManagerInstance = null;

        function initializeServiceRequests() {
            // Prevent re-initialization if already done
            if (serviceRequestsManagerInstance) {
                 // Optionally, tell it to re-apply filters if data might have changed externally
                 // serviceRequestsManagerInstance.applyFilters(); 
                return;
            }

            serviceRequestsManagerInstance = new ServiceRequestsManager({
                serviceRequestsEndpoint: '/api/user/service-bookings', // API for all bookings for the user
                // The JS also fetches individual service details: `${this.config.serviceRequestsEndpoint}/${serviceId}`
                // So, our backend route /api/user/service-bookings/[i:id] will handle this.
                containerSelector: '#service-requests-list-container', // Where cards are rendered
                modalId: 'serviceRequestDetailModal',
                filterFormId: 'service-request-filters',
                searchInputId: 'service-request-search',
                dateFilterId: 'date-filter',
                statusFilterId: 'status-filter',
                itemsPerPage: 5, // Or your preferred number
                paginationContainerSelector: '#services-pagination-container'
            });

            serviceRequestsManagerInstance.fetchAndRenderServiceRequests();
        }

        function initializeProductBookings() {
            // Prevent re-initialization if already done
            if (productBookingManagerInstance) {
                return;
            }

            productBookingManagerInstance = new ProductBookingManager({
                bookingsEndpoint: '/api/user/product-bookings', // API endpoint for product bookings
                containerSelector: '#product-bookings-container', // Where cards are rendered
                modalId: 'productBookingDetailModal',
                filterFormId: 'product-booking-filters',
                searchInputId: 'product-booking-search',
                dateFilterId: 'product-date-filter',
                statusFilterId: 'product-status-filter',
                itemsPerPage: 5, // Or your preferred number
                paginationContainerSelector: '#product-pagination-container'
            });

            productBookingManagerInstance.init();
        }
    </script>
</body>
</html>