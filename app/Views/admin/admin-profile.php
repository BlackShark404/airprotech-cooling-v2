<?php use Core\Session;?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Air Conditioning Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/home.css"> <!-- Assuming admin shares some styles -->
</head>
<body>
    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="/"> <!-- Link to admin dashboard -->
                <img src="/assets/images/logo/Air-TechLogo.png" alt="Logo" class="rounded-circle me-2" width="40" height="40">
                <span class="brand-text">AIR<span class="text-danger">PROTECH</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- User Profile -->
                    <li class="nav-item dropdown ms-3">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?=Session::get('profile_url') ? Session::get('profile_url') : '/assets/images/user-profile/default-profile.png'?>" alt="Profile" class="rounded-circle me-2" width="36" height="36">
                            <div class="d-flex flex-column lh-sm">
                                <span class="fw-semibold small text-dark"><?=$_SESSION['full_name'] ?? 'Admin'?></span>
                                <small class="text-success">● Online</small>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/admin/profile">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Admin Profile Area -->
    <div class="profile-area py-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <!-- Profile Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4 text-center">
                            <div class="position-relative mb-4 mx-auto" style="width: 150px; height: 150px;">
                                <img src="<?=Session::get('profile_url') ? Session::get('profile_url') : '/assets/images/user-profile/default-profile.png'?>" 
                                     alt="Profile Picture" 
                                     class="rounded-circle border shadow-sm" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                                
                                <button type="button" 
                                        class="btn btn-primary btn-sm rounded-circle position-absolute end-0 bottom-0"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#profileImageModal">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            
                            <h5 class="fw-bold mb-1"><?=$_SESSION['full_name'] ?? 'Admin User'?></h5>
                            <p class="text-muted mb-3"><?=$_SESSION['email'] ?? ''?></p>
                            <?php if (!empty($user['ua_last_login'])): ?>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-history me-1"></i> Last Login: 
                                    <?= date('D M d, Y h:i A', strtotime($user['ua_last_login'])) ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($user['ua_updated_at'])): ?>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-edit me-1"></i> Profile Updated: 
                                    <?= date('D M d, Y h:i A', strtotime($user['ua_updated_at'])) ?>
                                </p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-danger text-white px-3 py-2">Admin</span> <!-- Changed to Admin -->
                                <span class="badge bg-light-green text-green px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i> Verified
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- System Stats -->
                    <div class="card border-0 shadow-sm rounded-4 mt-4"> <!-- Added mt-4 for spacing -->
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">System Statistics</h5>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Active Customers</span>
                                <span class="fw-semibold"><?= $statistics['total_active_customers'] ?? 0 ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Active Technicians</span>
                                <span class="fw-semibold"><?= $statistics['total_active_technicians'] ?? 0 ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Pending Service Requests</span>
                                <span class="fw-semibold"><?= $statistics['total_pending_service_requests'] ?? 0 ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">In-Progress Services</span>
                                <span class="fw-semibold"><?= $statistics['total_inprogress_service_requests'] ?? 0 ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Pending Product Orders</span>
                                <span class="fw-semibold"><?= $statistics['total_pending_product_orders'] ?? 0 ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3"> <!-- Added mt-3 for spacing -->
                                <span class="text-muted">Total Registered Users</span>
                                <span class="fw-semibold"><?= $statistics['total_registered_users'] ?? 0 ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-muted">Total Admin Accounts</span>
                                <span class="fw-semibold"><?= $statistics['total_admin_accounts'] ?? 0 ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-muted">Total Products</span>
                                <span class="fw-semibold"><?= $statistics['total_products'] ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <!-- Profile Information Form -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Personal Information</h5>
                            
                            <form id="profileUpdateForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="firstName" name="first_name" value="<?= $_SESSION['first_name'] ?? $user['ua_first_name'] ?? '' ?>">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="lastName" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lastName" name="last_name" value="<?= $_SESSION['last_name'] ?? $user['ua_last_name'] ?? '' ?>">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= $_SESSION['email'] ?? $user['ua_email'] ?? '' ?>" readonly>
                                        <small class="text-muted">Email cannot be changed</small>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="phoneNumber" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phoneNumber" name="phone_number" value="<?= $_SESSION['phone_number'] ?? $user['ua_phone_number'] ?? '' ?>">
                                    </div>

                                    <div class="col-md-12"> <!-- Changed to full width -->
                                        <label for="officeNumber" class="form-label">Office Number</label>
                                        <input type="text" class="form-control" id="officeNumber" name="office_number" value="<?= $user['ad_office_no'] ?? '' ?>"> <!-- Added Office Number -->
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?= $_SESSION['address'] ?? $user['ua_address'] ?? '' ?></textarea>
                                    </div>
                                    
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save me-2"></i> Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Password Update Section -->
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Change Password</h5>
                            
                            <form id="passwordUpdateForm">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="currentPassword" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="currentPassword" name="current_password">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="newPassword" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="newPassword" name="new_password">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password">
                                    </div>
                                    
                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-outline-primary px-4">
                                            <i class="fas fa-lock me-2"></i> Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Quick Actions</h5>
                            <div class="list-group">
                                <a href="/admin/service-requests" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    Service Requests
                                    <i class="fas fa-concierge-bell opacity-75"></i>
                                </a>
                                <a href="/admin/product-bookings" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    Product Bookings
                                    <i class="fas fa-shopping-cart opacity-75"></i>
                                </a>
                                <a href="/admin/product-management" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    Product Management
                                    <i class="fas fa-box-open opacity-75"></i>
                                </a>
                                <a href="/admin/inventory-management" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    Inventory Management
                                    <i class="fas fa-warehouse opacity-75"></i>
                                </a>
                                <a href="/admin/reports" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    View Reports
                                    <i class="fas fa-chart-line opacity-75"></i>
                                </a>
                                <a href="/admin/technician" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    Manage Technicians
                                    <i class="fas fa-tools opacity-75"></i>
                                </a>
                                <a href="/admin/user-management" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    Manage Users
                                    <i class="fas fa-users-cog opacity-75"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Image Upload Modal -->
    <div class="modal fade" id="profileImageModal" tabindex="-1" aria-labelledby="profileImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold" id="profileImageModalLabel">Update Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="profileImageForm" enctype="multipart/form-data">
                        <div class="mb-4 text-center">
                            <div id="imagePreview" class="mb-4 mx-auto" style="width: 180px; height: 180px; border-radius: 50%; overflow: hidden; position: relative; background-color: #f8f9fa; border: 2px dashed #dee2e6; display: flex; align-items: center; justify-content: center;">
                                <img src="/assets/images/user-profile/default-profile.png" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            
                            <label for="profileImage" class="btn btn-outline-primary btn-sm px-4 mt-2">
                                <i class="fas fa-image me-2"></i> Select Image
                            </label>
                            <input class="d-none" type="file" id="profileImage" name="profile_image" accept="image/*">
                        </div>
                        
                        <div class="bg-light p-3 rounded-3 mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                <small class="fw-medium">Image Requirements</small>
                            </div>
                            <ul class="small text-muted mb-0 ps-4">
                                <li>Maximum file size: 2MB</li>
                                <li>Supported formats: JPG, PNG, WEBP</li>
                                <li>Square images work best</li>
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-upload me-2"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Files -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    
    <script src="/assets/js/utility/form-handler.js"></script>
    <script src="/assets/js/utility/toast-notifications.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init();
        
        // Handle form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // Profile update form
            handleFormSubmission('profileUpdateForm', '/api/admin/profile/update', true); // Changed API endpoint
            
            // Password update form
            handleFormSubmission('passwordUpdateForm', '/api/admin/password/update'); // Changed API endpoint
            
            // Get references to modal elements
            const profileImageModal = document.getElementById('profileImageModal');
            const imagePreview = document.getElementById('imagePreview');
            const previewImage = imagePreview.querySelector('img');
            const profileImageInput = document.getElementById('profileImage');
            
            // Initialize modal with current profile image
            const currentProfileUrl = '<?=Session::get('profile_url') ? Session::get('profile_url') : '/assets/images/user-profile/default-profile.png'?>';
            
            // Update preview when modal opens
            profileImageModal.addEventListener('show.bs.modal', function() {
                previewImage.src = currentProfileUrl;
                
                if (currentProfileUrl !== '/assets/images/user-profile/default-profile.png') {
                    imagePreview.classList.add('active');
                    imagePreview.style.border = 'none';
                } else {
                    imagePreview.classList.remove('active');
                    imagePreview.style.border = '2px dashed #dee2e6';
                }
                
                // Reset form when modal opens
                document.getElementById('profileImageForm').reset();
            });
            
            // Profile image preview on file selection
            profileImageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        imagePreview.classList.add('active');
                        imagePreview.style.border = 'none';
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            // Handle profile image upload
            const profileImageForm = document.getElementById('profileImageForm');
            profileImageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Uploading...';
                submitBtn.disabled = true;
                
                const formData = new FormData(this);
                
                axios.post('/api/admin/profile/image', formData, { // Changed API endpoint
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(response => {
                    if (response.data.success) {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('profileImageModal'));
                        modal.hide();
                        
                        // Show success message
                        showToast('Success', response.data.message, 'success');
                        
                        // Short delay before refreshing the page to allow the toast to be seen
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Show error message
                        showToast('Error', response.data.message, 'danger');
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'Failed to upload profile image. Please try again.', 'danger');
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                });
            });
            
            // Removed user-specific statistics refresh
        });
    </script>
</body>
</html> 