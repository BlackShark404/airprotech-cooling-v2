<footer class="footer text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3 mb-4">
                <h3 class="h5 mb-3"><span style="color: white;">AIR</span><span class="text-danger">PROTECH</span></h3>
                <p class="text-white-50">Your trusted partner for all air conditioning needs. Professional service guaranteed.</p>
            </div>
            <div class="col-md-3 mb-4">
                <h4 class="h6 mb-3">Quick Links</h4>
                <ul class="list-unstyled">
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="/admin/services" class="text-white-50 text-decoration-none">Services</a></li>
                        <li><a href="/admin/products" class="text-white-50 text-decoration-none">Products</a></li>
                        <li><a href="/admin/my-order-services" class="text-white-50 text-decoration-none">My Orders and Services Request</a></li>
                    <?php else: ?>
                        <li><a href="#hero" class="text-white-50 text-decoration-none">Home</a></li>
                        <li><a href="#our-services" class="text-white-50 text-decoration-none">Services</a></li>
                        <li><a href="#featured-products" class="text-white-50 text-decoration-none">Products</a></li>
                        <li><a href="#why-choose-us" class="text-white-50 text-decoration-none">Why Choose Us</a></li>
                        <li><a href="#contact" class="text-white-50 text-decoration-none">Contact</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h4 class="h6 mb-3">Contact Info</h4>
                <ul class="list-unstyled text-white-50">
                    <li><i class="fas fa-phone text-primary me-2"></i> 1-800-AIR-COOL</li>
                    <li><i class="fas fa-envelope text-primary me-2"></i> info@airprotech.com</li>
                    <li><i class="fas fa-map-marker-alt text-primary me-2"></i> 123 Cooling Street, AC City</li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h4 class="h6 mb-3">Stay Updated</h4>
                <p class="text-white-50">Follow us on social media for the latest promotions and updates!</p>
                <div class="social-links">
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-facebook-f fa-2x"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-twitter fa-2x"></i></a>
                    <a href="#" class="text-white-50"><i class="fab fa-instagram fa-2x"></i></a>
                </div>
            </div>
        </div>
        <div class="border-top border-white-50 mt-4 pt-4 text-center text-white-50">
            <p class="mb-0">&copy; 2025 AIRPROTECH. All rights reserved.</p>
        </div>
    </div>
</footer>