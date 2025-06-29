<?php use Core\Session;?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air Conditioning Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/home.css">
    <style>
      
        .footer {
            background-color: #212529;
        }
        
        .service-icon {
            color: var(--primary-color);
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: rgba(13, 110, 253, 0.1);
            margin-bottom: 1rem;
        }
        
        .service-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .hero-section {
            position: relative;
            min-height: 400px;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .cta-button {
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        
        .process-step {
            position: relative;
        }
        
        .process-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 35px;
            right: -20%;
            width: 40%;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .step-icon {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 1rem;
            background-color: var(--primary-color);
            color: white;
        }
        
        .contact-form .form-control {
            border-radius: 5px;
            padding: 12px;
            border: 1px solid #dee2e6;
        }
        
        .contact-form .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
            border-color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .process-step:not(:last-child)::after {
                display: none;
            }
        }
    </style>
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

    <!-- Hero Section -->
<section class="hero-section text-white py-5">
    <div class="container hero-content">
        <div class="row">
            <div class="col-lg-8 col-md-10">
                <h1 class="fw-bold mb-3 display-4">Professional AC Services & Solutions</h1>
                <p class="mb-4 lead">Expert installation, maintenance, and repair services for all your air conditioning needs</p>
                <button class="btn btn-danger cta-button px-4 py-2" data-bs-toggle="modal" data-bs-target="#bookServiceModal">Request Service</button>
            </div>
        </div>
    </div>
</section>

    

    <!-- Service Categories -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-2">Our Expertise</h2>
            <p class="text-center text-muted mb-5">Comprehensive air conditioning solutions for every need</p>
            
            <div class="row g-4 justify-content-center">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon mx-auto">
                                <i class="fas fa-tools fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">Installation</h5>
                            <p class="text-muted mb-0">Professional AC Installation</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon mx-auto">
                                <i class="fas fa-wrench fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">Repair</h5>
                            <p class="text-muted mb-0">Expert Check-up & Repair</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon mx-auto">
                                <i class="fas fa-broom fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">Maintenance</h5>
                            <p class="text-muted mb-0">General Cleaning & PMS</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon mx-auto">
                                <i class="fas fa-calculator fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">Survey & Quote</h5>
                            <p class="text-muted mb-0">Site Estimation & Quotation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Services Section -->
    <section id="our-services" class="py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-2">Professional Services</h2>
            <p class="text-center text-muted mb-5">Tailored solutions for residential and commercial air conditioning</p>
            
            <div class="row g-4">
                <!-- AC Check-up & Repair -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="service-icon">
                                <i class="fas fa-tools fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">Aircon Check-up & Repair</h5>
                            <p class="text-muted">Professional diagnostics and repair for all AC brands. Our certified technicians identify issues efficiently.</p>
                            <div class="mt-3">
                                <button class="btn btn-outline-primary service-select-btn" data-service="checkup-repair">Select Service</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Installation of Units -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="service-icon">
                                <i class="fas fa-plug fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">Installation of Units</h5>
                            <p class="text-muted">Expert installation of all types of air conditioning units with proper setup and configuration.</p>
                            <div class="mt-3">
                                <button class="btn btn-outline-primary service-select-btn" data-service="installation">Select Service</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ducting Works -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="service-icon">
                                <i class="fas fa-wind fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">Ducting Works</h5>
                            <p class="text-muted">Professional ducting installation and maintenance for improved airflow and system efficiency.</p>
                            <div class="mt-3">
                                <button class="btn btn-outline-primary service-select-btn" data-service="ducting">Select Service</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- General Cleaning & PMS -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="service-icon">
                                <i class="fas fa-broom fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">General Cleaning & PMS</h5>
                            <p class="text-muted">Preventive maintenance service and thorough cleaning to ensure optimal performance and longevity.</p>
                            <div class="mt-3">
                                <button class="btn btn-outline-primary service-select-btn" data-service="cleaning-pms">Select Service</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Survey & Estimation -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card service-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="service-icon">
                            <i class="fas fa-search fa-lg"></i>
                        </div>
                        <h5 class="fw-bold">Survey & Estimation</h5>
                        <p class="text-muted">On-site assessment and professional recommendations tailored to your specific requirements.</p>
                        <div class="mt-3">
                            <button class="btn btn-outline-primary service-select-btn" data-service="survey-estimation">Select Service</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Quotations -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card service-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="service-icon">
                            <i class="fas fa-file-invoice-dollar fa-lg"></i>
                        </div>
                        <h5 class="fw-bold">Project Quotations</h5>
                        <p class="text-muted">Get detailed and accurate project quotations tailored to your air conditioning needs and budget.</p>

                        <div class="mt-3">
                            <button class="btn btn-outline-primary service-select-btn" data-service="project-quotations">Select Service</button>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </section>

    <!-- Booking Process -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-2">Simple Booking Process</h2>
            <p class="text-center text-muted mb-5">Schedule your service in just a few easy steps</p>
            
            <div class="row text-center justify-content-center">
                <div class="col-md-3 col-sm-6 process-step mb-4 mb-md-0" data-aos="fade-right" data-aos-delay="100">
                    <div class="step-icon">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                    <h5 class="fw-bold mt-2">Select Service</h5>
                    <p class="text-muted small">Choose from our professional services</p>
                </div>
                
                <div class="col-md-3 col-sm-6 process-step mb-4 mb-md-0" data-aos="fade-right" data-aos-delay="200">
                    <div class="step-icon">
                        <i class="far fa-calendar-alt fa-lg"></i>
                    </div>
                    <h5 class="fw-bold mt-2">Pick Schedule</h5>
                    <p class="text-muted small">Choose your preferred date and time</p>
                </div>
                
                <div class="col-md-3 col-sm-6 process-step mb-4 mb-md-0" data-aos="fade-right" data-aos-delay="300">
                    <div class="step-icon">
                        <i class="fas fa-clipboard-list fa-lg"></i>
                    </div>
                    <h5 class="fw-bold mt-2">Provide Details</h5>
                    <p class="text-muted small">Tell us about your specific needs</p>
                </div>
                
                <div class="col-md-3 col-sm-6 process-step" data-aos="fade-right" data-aos-delay="400">
                    <div class="step-icon">
                        <i class="fas fa-thumbs-up fa-lg"></i>
                    </div>
                    <h5 class="fw-bold mt-2">Confirmation</h5>
                    <p class="text-muted small">Receive booking confirmation</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Book Service Modal -->
    <div class="modal fade" id="bookServiceModal" tabindex="-1" aria-labelledby="bookServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="bookServiceModalLabel">Request Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="serviceBookingForm">
                        <!-- User Information Display -->
                        <div class="card mb-3 bg-light">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Your Information</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?=Session::get('profile_url') ? Session::get('profile_url') : '/assets/images/user-profile/default-profile.png'?>" alt="Profile" class="rounded-circle me-3" width="48" height="48">
                                    <div>
                                        <div class="fw-medium"><?= Session::get('full_name') ?></div>
                                        <div class="text-muted small"><?= Session::get('email') ?></div>
                                        <div class="text-muted small"><?= Session::get('phone_number') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Selection -->
                        <div class="mb-3">
                            <label for="serviceSelect" class="form-label">Select Service <span class="text-danger">*</span></label>
                            <select class="form-select" id="serviceSelect" name="serviceSelect" required>
                                <option value="" selected disabled>Choose a service</option>
                                <option value="checkup-repair">Aircon Check-up & Repair</option>
                                <option value="installation">Installation of Units</option>
                                <option value="ducting">Ducting Works</option>
                                <option value="cleaning-pms">General Cleaning & PMS</option>
                                <option value="survey-estimation">Survey & Estimation</option>
                                <option value="project-quotations">Project Quotations</option>
                            </select>
                        </div>
                        
                        <!-- Date and Time Selection -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="preferredDate" class="form-label">Preferred Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="preferredDate" name="preferredDate" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="preferredTime" class="form-label">Preferred Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="preferredTime" name="preferredTime" required>
                            </div>
                        </div>
                        
                        <!-- Service Description -->
                        <div class="mb-3">
                            <label for="serviceDescription" class="form-label">Service Description</label>
                            <textarea class="form-control" id="serviceDescription" name="serviceDescription" rows="3" placeholder="Please describe your service needs..."></textarea>
                        </div>
                        
                        <!-- Hidden user information for form submission -->
                        <input type="hidden" id="fullName" name="fullName" value="<?= Session::get('full_name') ?>">
                        <input type="hidden" id="emailAddress" name="emailAddress" value="<?= Session::get('email') ?>">
                        <input type="hidden" id="phoneNumber" name="phoneNumber" value="<?= Session::get('phone_number') ?>">
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address" value="<?= Session::get('address') ?>" required>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-danger">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require __DIR__. '/../includes/shared/footer.php' ?>
        
    <!-- JS Files -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

    <script src="/assets/js/utility/toast-notifications.js"></script>
    <script src="/assets/js/utility/form-handler.js"></script>

    <script>
        // Initialize AOS animation library
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
        });

        document.addEventListener("DOMContentLoaded", function () {
            // Custom validation before form submission
            document.getElementById('serviceBookingForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get selected date and time
                const selectedDate = document.getElementById('preferredDate').value;
                const selectedTime = document.getElementById('preferredTime').value;
                
                // Create date objects for comparison
                const now = new Date();
                const selectedDateTime = new Date(`${selectedDate}T${selectedTime}`);
                
                // Check if selected date and time is in the past
                if (selectedDateTime < now) {
                    // Show error message
                    showToast('error', 'Invalid Appointment Time', 'Oops! It looks like you selected a date or time that has already passed. Please choose a future date and time for your service appointment.');
                    return false;
                }
                
                // Continue with form submission if validation passes
                handleFormSubmission('serviceBookingForm', '/user/service/request', false, e);
            });
            
            // Set minimum date for the date picker to today
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            const formattedToday = `${year}-${month}-${day}`;
            
            // Apply min date to all preferredDate inputs
            const dateInputs = document.querySelectorAll('#preferredDate');
            dateInputs.forEach(input => {
                input.setAttribute('min', formattedToday);
            });
        });

        // Service Selection Functionality
        document.addEventListener('DOMContentLoaded', function () {
            // Get all service selection buttons
            const serviceButtons = document.querySelectorAll('.service-select-btn');

            // Add click event to all service buttons
            serviceButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Get service type from data attribute
                    const serviceType = this.getAttribute('data-service');

                    // Open the modal
                    const bookingModal = new bootstrap.Modal(document.getElementById('bookServiceModal'));
                    bookingModal.show();

                    // Pre-select the service in the dropdown
                    const serviceSelect = document.getElementById('serviceSelect');
                    for (let i = 0; i < serviceSelect.options.length; i++) {
                        if (serviceSelect.options[i].value === serviceType) {
                            serviceSelect.selectedIndex = i;
                            break;
                        }
                    }
                });
            });
        });
    </script>

</body>
</html>
