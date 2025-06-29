<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Air-Protech Cooling Services</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="main-content py-4">
        <div class="container">
            <div class="auth-container">
                <div class="panel login-panel">
                    <div class="left-panel">
                        <div>
                            <h2 class="mb-0">APCS</h2>
                            <p class="mb-2">Air-Protech Cooling Services</p>
                            <p class="company-tagline">Premium HVAC solutions for homes and businesses since 1995</p>

                            <i class="fas fa-snowflake snowflake-icon"></i>

                            <h1 class="mt-4">Join Our Community</h1>
                            <p class="mb-3">Connect. Share. Grow.</p>

                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle"></i> Professional HVAC Installation</li>
                                <li><i class="fas fa-check-circle"></i> Energy-efficient Solutions</li>
                                <li><i class="fas fa-check-circle"></i> Preventive Maintenance Plans</li>
                            </ul>
                        </div>

                        <i class="fas fa-fan fan-icon fa-spin" style="--fa-animation-duration: 15s;"></i>
                        <i class="fas fa-fan fan-icon-2 fa-spin" style="--fa-animation-duration: 10s;"></i>
                        <i class="fas fa-fan fan-icon-3 fa-spin" style="--fa-animation-duration: 20s;"></i>
                        
                        <svg class="wave-shape" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                            <path fill="#ffffff" fill-opacity="1" d="M0,160L48,176C96,192,192,224,288,224C384,224,480,192,576,181.3C672,171,768,181,864,192C960,203,1056,213,1152,202.7C1248,192,1344,160,1392,144L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                        </svg>
                    </div>

                    <div class="right-panel">
                        <div class="form-content">
                            <h2 class="text-center mb-2">Welcome Back</h2>
                            <p class="text-center text-muted mb-4">Log in to your account</p>

                            <form id="loginForm">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-envelope input-icon"></i>
                                        <input type="email" class="form-control input-with-icon" id="email" name="email" placeholder="Enter your email" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" class="form-control input-with-icon" id="password" name="password" placeholder="Enter your password" required>
                                        <button type="button" class="password-toggle" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">Remember me</label>
                                    </div>
                                </div>
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary">Log In</button>
                                </div>

                                

                                <div class="text-center mt-4">
                                    <p>Don't have an account? <a href="/register" class="toggle-link">Register now</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
        <div id="liveToast" class="toast text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">Success</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    

    <script>
        function showToast(title, message, type = 'success') {
            const toastEl = document.getElementById('liveToast');
            const toastMsg = document.getElementById('toastMessage');

            toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning');
            toastEl.classList.add(`bg-${type}`);
            toastMsg.innerText = message;

            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script src="/assets/js/utility/toast-notifications.js"></script>
    <script src="/assets/js/utility/form-handler.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            handleFormSubmission('loginForm', '/login'); 
        });
    </script>
    
</body>
</html>