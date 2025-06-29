<?php use Core\Session;?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Conditioning Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/home.css" >
    
    <style>
        .product-card {
            border-radius: 12px;
            transition: transform 0.3s ease;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            background-color: white;
            overflow: hidden;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }
        
        .product-img-container {
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-color: #f8f9fa;
        }
        
        .product-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        
        .product-info {
            padding: 1.8rem;
        }
        
        .product-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            font-size: 1.4rem;
        }
        
        .product-desc {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 1.2rem;
            line-height: 1.5;
        }
        
        .product-price {
            font-weight: 700;
            color: #dc3545;
            font-size: 1.5rem;
            margin-bottom: 1.2rem;
        }
        
        .product-variants {
            font-size: 0.9rem;
            color: #495057;
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 0.25rem;
            background-color: #f8f9fa;
            border-left: 3px solid #6c757d;
        }
        
        .variant-badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            margin-right: 0.3rem;
            margin-bottom: 0.3rem;
            background-color: #e9ecef;
            border-radius: 50px;
            font-size: 0.85rem;
            color: #495057;
        }
        
        .btn-book-now {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            padding: 0.6rem 1.8rem;
            border-radius: 5px;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-book-now:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-2px);
        }
        
        .hero-section {
            padding: 100px 0;
            color: white;
            margin-bottom: 3rem;
        }
        
        .featured-section {
            padding: 2rem 0 4rem;
            background-color: #f5f7fa;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            position: relative;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 3px;
            background-color: var(--secondary-color);
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        /* Filter Styles - Modified for horizontal layout */
        .filter-card {
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
            background-color: white;
        }
        
        .filter-title {
            font-weight: 600;
            margin-bottom: 1.2rem;
            color: var(--primary-color);
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.8rem;
        }
        
        .filter-group {
            margin-bottom: 0;
        }
        
        .horizontal-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: space-between;
        }
        
        .horizontal-filters .filter-group {
            flex: 0 0 auto;
            margin-right: 20px;
        }
        
        .filter-buttons {
            display: flex;
            align-items: flex-end;
            margin-left: auto;
        }
        
        .btn-reset {
            background-color: transparent;
            border: 1px solid #dc3545;
            color: #dc3545;
            white-space: nowrap;
            height: 38px;
        }
        
        .btn-reset:hover {
            background-color: #dc3545;
            color: white;
        }
        
        /* Search Box */
        .search-box {
            position: relative;
            flex-grow: 1;
            min-width: 300px;
            margin-right: 20px;
        }
        
        .search-box input {
            border-radius: 8px;
            padding-left: 3rem;
            height: 38px;
            border: 1px solid #e5e5e5;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03);
        }
        
        /* Form Elements Focus States */
        .search-box input:focus, 
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            border-color: #86b7fe;
        }
        
        /* Make select box match input styles */
        .filter-group select {
            border-radius: 8px;
            height: 38px;
            border: 1px solid #e5e5e5;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03);
        }
        
        .search-box .search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 1;
        }
        
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 992px) {
            .horizontal-filters {
                flex-direction: column;
                gap: 1rem;
            }
            
            .horizontal-filters .filter-group {
                width: 100%;
                min-width: 100%;
            }
            
            .search-box {
                width: 100%;
                min-width: 100%;
            }
            
            .filter-buttons {
                width: 100%;
                margin-top: 1rem;
                margin-left: 0;
                justify-content: flex-end;
            }
            
            .btn-reset {
                width: auto;
            }
        }
    </style>
</head>
<body data-user-address="<?= Session::get('address') ?? '' ?>">
    <!-- Top Bar -->
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

<!-- Optional CSS for hover effect -->
<style>
    #userDropdown:hover img {
        opacity: 0.8;
        transform: scale(1.1);
        transition: all 0.2s ease-in-out;
    }
</style>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Professional Air Conditioning Solutions</h1>
                    <p class="lead mb-4">Browse our high-quality products for all your AC needs</p>
                    <a href="#product-section" class="btn btn-danger btn-lg">View Products</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="product-section" class="featured-section">
        <div class="container">
            <h2 class="section-title">Our AC Products</h2>
            
            <!-- Filters at the top -->
            <div class="filter-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="filter-title mb-0">Filter Products</h3>
                    
                </div>
                
                <form id="product-filters">
                    <div class="horizontal-filters align-items-end">
                        <!-- Search box -->
                        <div class="search-box">
                            <label class="form-label">Search</label>
                            <div class="position-relative">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" id="product-search" class="form-control" placeholder="Search products...">
                            </div>
                        </div>
                        
                        <!-- Price Range Filter -->
                        <div class="filter-group me-3" style="flex: 0 0 auto; min-width: 320px;">
                            <label class="form-label">Price Range (₱)</label>
                            <div class="d-flex gap-2">
                                <input type="number" name="min-price" class="form-control" placeholder="Min" min="0" step="100" style="width: 150px;">
                                <input type="number" name="max-price" class="form-control" placeholder="Max" min="0" step="100" style="width: 150px;">
                            </div>
                        </div>
                        
                        <!-- Stock Status Filter -->
                        <div class="filter-group" style="flex: 0 0 auto; min-width: 200px;">
                            <label for="availability-status" class="form-label">Availability</label>
                            <select id="availability-status" name="availability-status" class="form-select form-control">
                                <option value="">All</option>
                                <option value="Available">Available</option>
                                <option value="Out of Stock">Out of Stock</option>
                            </select>
                        </div>
                        
                        <!-- Filter Buttons -->
                        <div class="filter-buttons">
                            <button type="reset" class="btn btn-reset">
                                <i class="fas fa-times me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Results Info -->
            <div class="results-info">
                <div id="results-count" class="text-muted">Showing all products</div>
            </div>
            
            <!-- Products Container - Full Width -->
            <div class="row g-4" id="products-container">
                <!-- Products will be dynamically inserted here by ProductManager.js -->
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading products...</p>
                </div>
            </div>
            
            <!-- Pagination -->
            <div id="pagination-container" class="mt-4"></div>
        </div>
    </section>

    <!-- Product Modal Template -->
    <div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 overflow-hidden">
                <button type="button" class="btn-close position-absolute end-0 top-0 p-4 z-3" style="font-size: 0.8rem" data-bs-dismiss="modal" aria-label="Close"></button>
                
                <div class="row g-0">
                    <!-- Left Column - Product Image -->
                    <div class="col-lg-5 bg-light">
                        <div class="product-display d-flex flex-column h-100">
                            <div class="flex-grow-1 d-flex align-items-center justify-content-center p-5" style="min-height: 500px;">
                                <img id="modal-product-image" src="" alt="Product" class="img-fluid" style="max-height: 400px; object-fit: contain;">
                            </div>
                            <div class="product-info p-4 bg-white">
                                <div class="d-flex justify-content-between">
                                    <span class="fs-6 text-muted">Product ID: <span id="modal-product-code" class="fw-medium text-dark"></span></span>
                                    <div class="d-flex align-items-center">
                                        <span class="badge rounded-pill bg-success-subtle text-success me-2">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                        <span id="modal-availability-status" class="text-success fw-medium"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column - Product Details & Booking -->
                    <div class="col-lg-7">
                        <div class="p-5">
                            <h3 id="modal-product-name" class="display-7 fw-bold mb-0"></h3>
                            <h4 id="modal-product-price" class="mb-4 text-primary fw-bold fs-3"></h4>
                            
                            <div class="row gx-4 gy-3 mb-4">
                                <div class="col-md-7">
                                    <label for="modal-variant-select" class="form-label">Variant</label>
                                    <select id="modal-variant-select" class="form-select form-select-lg shadow-sm border-0 bg-light">
                                        <!-- Variants will be added dynamically -->
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label for="modal-quantity" class="form-label">Quantity</label>
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary border-0 bg-light px-3" type="button" id="decrease-quantity">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="text" class="form-control text-center border-0 bg-light" id="modal-quantity" value="1" readonly>
                                        <button class="btn btn-outline-secondary border-0 bg-light px-3" type="button" id="increase-quantity">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Product Information Tabs -->
                            <ul class="nav nav-tabs mb-4" id="productDetailTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="booking-tab" data-bs-toggle="tab" data-bs-target="#booking-content" type="button" role="tab" aria-controls="booking-content" aria-selected="true">
                                        <i class="fas fa-calendar-alt me-2"></i>Booking Details
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants-content" type="button" role="tab" aria-controls="variants-content" aria-selected="false">
                                        <i class="fas fa-cubes me-2"></i>Variants
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features-content" type="button" role="tab" aria-controls="features-content" aria-selected="false">
                                        <i class="fas fa-star me-2"></i>Features
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs-content" type="button" role="tab" aria-controls="specs-content" aria-selected="false">
                                        <i class="fas fa-cogs me-2"></i>Specifications
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="productDetailTabsContent">
                                <!-- Booking Tab -->
                                <div class="tab-pane fade show active" id="booking-content" role="tabpanel" aria-labelledby="booking-tab">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="date" id="modal-preferred-date" class="form-control" placeholder="Preferred Date" required>
                                                <label for="modal-preferred-date">Preferred Date*</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="time" id="modal-preferred-time" class="form-control" placeholder="Preferred Time" required>
                                                <label for="modal-preferred-time">Preferred Time*</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <textarea id="modal-address" class="form-control" placeholder="Delivery/Installation Address" style="height: 100px" required></textarea>
                                                <label for="modal-address">Delivery/Installation Address*</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <textarea id="modal-description" class="form-control" placeholder="Additional Instructions" style="height: 100px"></textarea>
                                                <label for="modal-description">Additional Instructions</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Variants Tab -->
                                <div class="tab-pane fade" id="variants-content" role="tabpanel" aria-labelledby="variants-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Capacity</th>
                                                    <th>SRP</th>
                                                    <th>15% Free Installation</th>
                                                    <th>25% With Installation Fee</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modal-variants-table">
                                                <!-- Variants will be added dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <small>Note: Final pricing will be determined by the installation requirements and will be confirmed after booking submission.</small>
                                    </div>
                                </div>
                                
                                <!-- Features Tab -->
                                <div class="tab-pane fade" id="features-content" role="tabpanel" aria-labelledby="features-tab">
                                    <ul id="modal-features" class="list-group list-group-flush">
                                        <!-- Features will be added dynamically -->
                                    </ul>
                                </div>
                                
                                <!-- Specifications Tab -->
                                <div class="tab-pane fade" id="specs-content" role="tabpanel" aria-labelledby="specs-tab">
                                    <ul id="modal-specifications" class="list-group list-group-flush">
                                        <!-- Specifications will be added dynamically -->
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="total-price">
                                    <p class="m-0 text-muted">Total Amount</p>
                                    <h4 id="modal-total-amount" class="m-0 text-primary fw-bold"></h4>
                                </div>
                                <button type="button" id="confirm-order" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-check-circle me-2"></i>Confirm Booking
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require __DIR__. '/../includes/shared/footer.php' ?>
        
    <!-- JS Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

    <!-- Smooth scrolling script -->
    <script src="/assets/js/home/home.js"></script>
    
    <!-- Product Manager Script -->
    <script src="/assets/js/utility/ProductManager.js"></script>
    
    <script>
        // Initialize AOS animation
        AOS.init({
            duration: 1000, 
            easing: 'ease-in-out', 
            once: true, 
        });
    </script>

    <script>
        // Initialize Product Manager when the document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Create a ProductManager instance with our API endpoints
            const productManager = new ProductManager({
                productsEndpoint: '/api/products',
                orderEndpoint: '/api/product-bookings'
            });
            
            // Initialize the product manager to fetch and display products
            productManager.fetchAndRenderProducts();
            
            // Add event listener for sorting dropdown
            document.querySelectorAll('[data-sort]').forEach(element => {
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    const sortType = this.getAttribute('data-sort');
                    const sortText = this.textContent;
                    
                    // Update dropdown button text
                    document.getElementById('sortDropdown').textContent = 'Sort By: ' + sortText;
                    
                    // Sort products
                    let sortedProducts = [...productManager.allProducts];
                    
                    switch (sortType) {
                        case 'price-low':
                            sortedProducts.sort((a, b) => {
                                const aPrice = a.variants && a.variants.length > 0 ? parseFloat(a.variants[0].VAR_SRP_PRICE) : 0;
                                const bPrice = b.variants && b.variants.length > 0 ? parseFloat(b.variants[0].VAR_SRP_PRICE) : 0;
                                return aPrice - bPrice;
                            });
                            break;
                        case 'price-high':
                            sortedProducts.sort((a, b) => {
                                const aPrice = a.variants && a.variants.length > 0 ? parseFloat(a.variants[0].VAR_SRP_PRICE) : 0;
                                const bPrice = b.variants && b.variants.length > 0 ? parseFloat(b.variants[0].VAR_SRP_PRICE) : 0;
                                return bPrice - aPrice;
                            });
                            break;
                        case 'name-asc':
                            sortedProducts.sort((a, b) => a.PROD_NAME.localeCompare(b.PROD_NAME));
                            break;
                        default:
                            // Default sorting (by ID)
                            sortedProducts.sort((a, b) => a.PROD_ID - b.PROD_ID);
                    }
                    
                    // Render sorted products
                    productManager.renderProducts(sortedProducts);
                });
            });
        });
    </script>
    
</body>
</html>