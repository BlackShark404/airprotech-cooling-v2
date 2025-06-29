<?php use Core\Session;?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Conditioning Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/home.css" >
</head>
<body>
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
                <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/user/dashboard">
            <img src="<?= Session::get('profile_url') ? Session::get('profile_url') : '/assets/images/user-profile/default-profile.png' ?>" alt="Profile" class="rounded-circle me-2" width="36" height="36" style="object-fit: cover;">
                <span class="brand-text">AIR<span class="text-danger">PROTECH</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="/user/dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/user/services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="/user/products">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="/user/orders-services">My Bookings & Service Requests</a></li>

                    <!-- User Profile -->
                    <li class="nav-item dropdown ms-3">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src=<?=Session::get('profile_url') ? Session::get('profile_url') : '/assets/images/user-profile/default-profile.png'?> alt="Profile" class="rounded-circle me-2" width="36" height="36">
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

    <!-- User Dashboard Area -->
    <div class="dashboard-area py-4">
        <div class="container">
            <div class="dashboard-header mb-4">
                <h2 class="fw-bold text-navy">Welcome back, <?=$_SESSION['first_name'] ?></h2>
                <p class="text-muted">Manage your services and bookings</p>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card dashboard-card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 text-center">
                            <div class="icon-circle bg-light-blue mb-3">
                                <i class="fas fa-calendar-check text-blue"></i>
                            </div>
                            <h3 class="display-4 fw-bold text-blue">3</h3>
                            <p class="text-muted mb-0">Active Bookings</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 text-center">
                            <div class="icon-circle bg-light-orange mb-3">
                                <i class="fas fa-tools text-orange"></i>
                            </div>
                            <h3 class="display-4 fw-bold text-orange">2</h3>
                            <p class="text-muted mb-0">Pending Services</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 text-center">
                            <div class="icon-circle bg-light-green mb-3">
                                <i class="fas fa-check-circle text-green"></i>
                            </div>
                            <h3 class="display-4 fw-bold text-green">12</h3>
                            <p class="text-muted mb-0">Completed Services</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 text-center">
                            <div class="icon-circle bg-light-purple mb-3">
                                <i class="fas fa-shopping-cart text-purple"></i>
                            </div>
                            <h3 class="display-4 fw-bold text-purple">5</h3>
                            <p class="text-muted mb-0">Product Orders</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Info and Current Bookings -->
            <div class="row g-4 mb-5">
                <!-- Personal Information -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Personal Information</h5>
                                <a href="#" class="edit-link"><i class="fas fa-pencil-alt"></i></a>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small text-muted">Full Name</label>
                                <p class="mb-0 fw-medium"><?=$_SESSION['full_name'] ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small text-muted">Phone Number</label>
                                <p class="mb-0 fw-medium">0917 175 7258</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small text-muted">Email</label>
                                <p class="mb-0 fw-medium">alex.mitchell@email.com</p>
                            </div>
                            
                            <div>
                                <label class="form-label small text-muted">Service Address</label>
                                <p class="mb-0 fw-medium">123 Cooling Street, AC City</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Current Bookings -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold">Current Bookings</h5>
                                <a href="#" class="text-primary text-decoration-none small fw-medium">View All</a>
                            </div>
                            
                            <!-- Booking Item -->
                            <div class="booking-item p-3 mb-3 border rounded-3 bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-semibold mb-1">AC Maintenance</h6>
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-clock text-muted me-2"></i>
                                            <span class="small text-muted">Jan 15, 2024 at 10:00 AM</span>
                                        </div>
                                    </div>
                                    <span class="badge bg-success rounded-pill px-3 py-2">Confirmed</span>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm">Reschedule</button>
                                    <button class="btn btn-outline-danger btn-sm">Cancel</button>
                                </div>
                            </div>
                            
                            <!-- Booking Item -->
                            <div class="booking-item p-3 border rounded-3 bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-semibold mb-1">Filter Replacement</h6>
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-clock text-muted me-2"></i>
                                            <span class="small text-muted">Jan 18, 2024 at 2:30 PM</span>
                                        </div>
                                    </div>
                                    <span class="badge bg-warning rounded-pill px-3 py-2">Pending</span>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm">Reschedule</button>
                                    <button class="btn btn-outline-danger btn-sm">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Service History -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold">Service History</h5>
                                <a href="#" class="text-primary text-decoration-none small fw-medium">View All <i class="fas fa-chevron-right ms-1 small"></i></a>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="text-muted small">
                                        <tr>
                                            <th>Service</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>AC Installation</td>
                                            <td>Dec 28, 2023</td>
                                            <td><span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Completed</span></td>
                                            <td>$299.99</td>
                                        </tr>
                                        <tr>
                                            <td>Annual Maintenance</td>
                                            <td>Nov 15, 2023</td>
                                            <td><span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Completed</span></td>
                                            <td>$149.99</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex gap-3">
                        <button class="btn btn-danger px-4 py-2">Book New Service</button>
                        <button class="btn btn-outline-dark px-4 py-2">View All Services</button>
                        <button class="btn btn-outline-dark px-4 py-2">Manage Orders</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-white py-5">
        <div class="container">
            <div class="row">
                <!-- Brand & Description -->
                <div class="col-md-3 mb-4">
                    <h3 class="h5 mb-3"><span style="color: white;">AIR</span><span class="text-danger">PROTECH</span></h3>
                    <p class="text-white-50">Your trusted partner for all air conditioning needs. Professional service guaranteed.</p>
                </div>
        
                <!-- Quick Links -->
                <div class="col-md-3 mb-4">
                    <h4 class="h6 mb-3">Quick Links</h4>
                    <ul class="list-unstyled">
                        <li><a href="#hero" class="text-white-50 text-decoration-none">Home</a></li>
                        <li><a href="#our-services" class="text-white-50 text-decoration-none">Services</a></li>
                        <li><a href="#featured-products" class="text-white-50 text-decoration-none">Products</a></li>
                        <li><a href="#why-choose-us" class="text-white-50 text-decoration-none">Why Choose Us</a></li>
                        <li><a href="#contact" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
        
                <!-- Contact Info -->
                <div class="col-md-3 mb-4">
                    <h4 class="h6 mb-3">Contact Info</h4>
                    <ul class="list-unstyled text-white-50">
                        <li><i class="fas fa-phone text-primary me-2"></i> 1-800-AIR-COOL</li>
                        <li><i class="fas fa-envelope text-primary me-2"></i> info@airprotech.com</li>
                        <li><i class="fas fa-map-marker-alt text-primary me-2"></i> 123 Cooling Street, AC City</li>
                    </ul>
                </div>
        
                <!-- Newsletter -->
                <div class="col-md-3 mb-4">
                    <h4 class="h6 mb-3">Newsletter</h4>
                    <p class="text-white-50">Subscribe for updates and special offers</p>
                    <div class="input-group">
                        <input type="email" class="form-control bg-dark text-white border-0" placeholder="Your email">
                        <button class="btn btn-primary">Subscribe</button>
                    </div>
                </div>
            </div>
            <div class="border-top border-white-50 mt-4 pt-4 text-center text-white-50">
                <p class="mb-0">&copy; 2025 Air-Protech. All rights reserved.</p>
            </div>
        </div>
    </footer>
        
    <!-- JS Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

    <!-- Smooth scrolling script -->
    <script src="/assets/js/home.js"></script>

    <script>
        AOS.init({
            duration: 1000, 
            easing: 'ease-in-out', 
            once: true, 
        });
    </script>
</body>
</html>