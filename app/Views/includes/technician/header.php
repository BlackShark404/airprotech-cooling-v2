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
            <div class="me-3">
            </div>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= Session::get('profile_url') ? Session::get('profile_url') : '/assets/images/user-profile/default-profile.png' ?>" alt="Profile" class="rounded-circle me-2" width="36" height="36">
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

<!-- Tabs with horizontal scroll for mobile -->
<div class="container-fluid">
    <div class="nav-scroll">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'service_assignments' ? 'active' : '' ?>" href="<?= base_url('/admin/service-assignments') ?>">Service Assignments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'product_bookings' ? 'active' : '' ?>" href="<?= base_url('/admin/product-assignments') ?>">Product Assignments</a>
            </li>
        </ul>
    </div>
</div>
