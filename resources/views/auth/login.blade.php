<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Library Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        
        .login-header h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
        }
        
        .login-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .login-body {
            padding: 2.5rem 2rem;
        }
        
        .role-selector {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .role-btn {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            background: #f8f9fa;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .role-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .role-btn.active {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .role-btn.active.admin {
            border-color: #0d6efd;
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        }
        
        .role-btn.active.staff {
            border-color: #198754;
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        }
        
        .role-btn.active.student {
            border-color: #6f42c1;
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border-right: none;
            border-color: #dee2e6;
        }
        
        .form-control {
            border-left: none;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.875rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            background: white;
            padding: 0 1rem;
            position: relative;
            color: #666;
            font-size: 0.85rem;
        }
        
        .social-login {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .social-btn.google:hover {
            border-color: #4285F4;
        }
        
        .social-btn.facebook:hover {
            border-color: #1877F2;
        }
        
        .social-btn.apple:hover {
            border-color: #000;
        }
        
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .password-toggle {
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="mb-3">
                    <i class="bi bi-book-half" style="font-size: 3rem;"></i>
                </div>
                <h2>Library Management</h2>
                <p>Sign in to your account</p>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    <input type="hidden" name="role" id="roleInput" value="">
                    
                    <!-- Role Selection -->
                    <div class="mb-4">
                        <label class="form-label">Login as</label>
                        <div class="role-selector">
                            <button type="button" class="role-btn admin" onclick="selectRole('admin')" data-role="admin">
                                <i class="bi bi-shield-check"></i> Admin
                            </button>
                            <button type="button" class="role-btn staff" onclick="selectRole('staff')" data-role="staff">
                                <i class="bi bi-person-badge"></i> Staff
                            </button>
                            <button type="button" class="role-btn student" onclick="selectRole('student')" data-role="student">
                                <i class="bi bi-person"></i> Student
                            </button>
                        </div>
                        <div id="roleError" class="error-message" style="display: none;">
                            Please select a role to continue
                        </div>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   placeholder="Enter your email" 
                                   value="{{ old('email') }}" 
                                   required>
                        </div>
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password" 
                                   required>
                            <span class="input-group-text password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </span>
                        </div>
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Forgot Password -->
                    <div class="mb-4 text-end">
                        <a href="#" class="text-decoration-none" style="color: #667eea; font-size: 0.9rem;">Forgot password?</a>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-login w-100 text-white">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="divider">
                    <span>Or sign in with</span>
                </div>
                
                <!-- Social Login -->
                <div class="social-login mb-4">
                    <div class="social-btn google" title="Google">
                        <i class="bi bi-google" style="font-size: 1.25rem; color: #4285F4;"></i>
                    </div>
                    <div class="social-btn facebook" title="Facebook">
                        <i class="bi bi-facebook" style="font-size: 1.25rem; color: #1877F2;"></i>
                    </div>
                    <div class="social-btn apple" title="Apple">
                        <i class="bi bi-apple" style="font-size: 1.25rem; color: #000;"></i>
                    </div>
                </div>
                
                <!-- Register Link -->
                <div class="register-link">
                    Don't have an account? <a href="{{ route('register') }}">Register here</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let selectedRole = '';
        
        function selectRole(role) {
            selectedRole = role;
            document.getElementById('roleInput').value = role;
            document.getElementById('roleError').style.display = 'none';
            
            // Remove active class from all buttons
            document.querySelectorAll('.role-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to selected button
            const activeBtn = document.querySelector(`[data-role="${role}"]`);
            activeBtn.classList.add('active', role);
        }
        
        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }
        
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (!selectedRole) {
                e.preventDefault();
                document.getElementById('roleError').style.display = 'block';
                return false;
            }
        });
    </script>
</body>
</html>
