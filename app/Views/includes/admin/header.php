<?php 
use Core\Session;
require_once __DIR__ . '/../../../../script/active_page.php';
?>

<!-- Navbar -->
<nav class="navbar navbar-light bg-white sticky-top border-bottom shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand text-dark" href="/">
            <img src="/assets/images/logo/Air-TechLogo.png" alt="AirProtect logo" height="36" width="36">
            <span class="brand-text">AIR<span class="text-danger">PROTECH</span></span>
        </a>
        <div class="d-flex">
            <div class="me-3 d-none d-md-block">
            </div>
            <!-- Mobile menu toggle button -->
            <button class="navbar-toggler me-2 d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavMenu" aria-controls="mobileNavMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= Session::get('profile_url') ? Session::get('profile_url') : '/assets/images/user-profile/default-profile.png' ?>" alt="Profile" class="rounded-circle me-2" width="36" height="36" style="object-fit: cover;">
                    <div class="d-flex flex-column lh-sm">
                        <span class="fw-semibold small text-dark"><?=$_SESSION['full_name'] ?? 'User'?></span>
                        <small>
                            <span class="text-success">●</span> <span class="text-muted">Online</span>
                        </small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li>
                        <a class="dropdown-item" href="/admin/profile">
                            <i class="bi bi-person-circle"></i> Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="/logout">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile menu collapse -->
<div class="collapse navbar-collapse" id="mobileNavMenu">
    <div class="container-fluid bg-light py-2 d-md-none">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'service_requests' ? 'active fw-bold' : '' ?>" href="<?= base_url('/admin/service-requests') ?>">
                    <i class="bi bi-tools me-2"></i>Service Requests
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'product_bookings' ? 'active fw-bold' : '' ?>" href="<?= base_url('/admin/product-bookings') ?>">
                    <i class="bi bi-bag-check me-2"></i>Product Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'product_management' ? 'active fw-bold' : '' ?>" href="<?= base_url('/admin/product-management') ?>">
                    <i class="bi bi-box me-2"></i>Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'inventory_management' ? 'active fw-bold' : '' ?>" href="<?= base_url('/admin/inventory-management') ?>">
                    <i class="bi bi-clipboard-data me-2"></i>Inventory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'reports' ? 'active fw-bold' : '' ?>" href="<?= base_url('/admin/reports') ?>">
                    <i class="bi bi-file-earmark-text me-2"></i>Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'technician' ? 'active fw-bold' : '' ?>" href="<?= base_url('/admin/technician') ?>">
                    <i class="bi bi-person-gear me-2"></i>Technician
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'user_management' ? 'active fw-bold' : '' ?>" href="<?= base_url('/admin/user-management') ?>">
                    <i class="bi bi-people me-2"></i>User Management
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Tabs with horizontal scroll for desktop and tablets -->
<div class="container-fluid d-none d-md-block">
    <div class="nav-scroll">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'service_requests' ? 'active' : '' ?>" href="<?= base_url('/admin/service-requests') ?>">Service Requests</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'product_bookings' ? 'active' : '' ?>" href="<?= base_url('/admin/product-bookings') ?>">Product Bookings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'product_management' ? 'active' : '' ?>" href="<?= base_url('/admin/product-management') ?>">Products</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'inventory_management' ? 'active' : '' ?>" href="<?= base_url('/admin/inventory-management') ?>">Inventory</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'reports' ? 'active' : '' ?>" href="<?= base_url('/admin/reports') ?>">Reports</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'technician' ? 'active' : '' ?>" href="<?= base_url('/admin/technician') ?>">Technician</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'user_management' ? 'active' : '' ?>" href="<?= base_url('/admin/user-management') ?>">User Management</a>
            </li>
        </ul>
    </div>
</div>

<!-- Add some custom CSS for mobile optimization -->
<style>
@media (max-width: 767.98px) {
    .nav-scroll {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    
    #mobileNavMenu .nav-link {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    #mobileNavMenu .nav-link.active {
        background-color: rgba(13, 110, 253, 0.1);
        border-left: 3px solid #0d6efd;
        padding-left: calc(1rem - 3px);
    }
}
</style>
