<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account - Library Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        .register-container {
            min-height: 100vh;
            display: flex;
        }
        
        /* Left Side - Welcome Section */
        .welcome-section {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            overflow: hidden;
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255,255,255,0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(255,255,255,0.08) 0%, transparent 60%);
            background-size: 600px 600px, 800px 800px, 1000px 1000px;
            background-position: 20% 30%, 80% 70%, 50% 50%;
            background-repeat: no-repeat;
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .welcome-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
        }
        
        .welcome-content h1 {
            font-size: 4.5rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            text-shadow: 3px 3px 15px rgba(0,0,0,0.3);
            letter-spacing: 4px;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .welcome-content p {
            font-size: 1.5rem;
            opacity: 0.95;
            line-height: 1.8;
            max-width: 500px;
            margin: 0 auto;
            font-weight: 300;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.2);
        }
        
        /* Decorative Shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: pulse 4s ease-in-out infinite;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            background: white;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            background: white;
            bottom: 20%;
            right: 15%;
            animation-delay: 1s;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            background: white;
            top: 50%;
            left: 5%;
            animation-delay: 2s;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.1; }
            50% { transform: scale(1.2); opacity: 0.15; }
        }
        
        /* Right Side - Form Section */
        .form-section {
            flex: 1;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
        }
        
        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%),
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.03) 0%, transparent 50%);
        }
        
        .form-container {
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
        }
        
        .form-header {
            margin-bottom: 2rem;
        }
        
        .form-header h2 {
            color: white;
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        
        .form-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.05rem;
        }
        
        .form-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .social-buttons {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .social-btn {
            flex: 1;
            padding: 0.875rem;
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .social-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        
        .social-btn.google {
            border-color: rgba(66, 133, 244, 0.4);
        }
        
        .social-btn.google:hover {
            background: rgba(66, 133, 244, 0.25);
            border-color: rgba(66, 133, 244, 0.6);
        }
        
        .social-btn.facebook {
            border-color: rgba(24, 119, 242, 0.4);
        }
        
        .social-btn.facebook:hover {
            background: rgba(24, 119, 242, 0.25);
            border-color: rgba(24, 119, 242, 0.6);
        }
        
        .social-btn.twitter {
            border-color: rgba(29, 161, 242, 0.4);
        }
        
        .social-btn.twitter:hover {
            background: rgba(29, 161, 242, 0.25);
            border-color: rgba(29, 161, 242, 0.6);
        }
        
        .divider {
            text-align: center;
            margin: 1.75rem 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: rgba(255, 255, 255, 0.25);
        }
        
        .divider span {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 0 1.25rem;
            position: relative;
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.9rem;
        }
        
        .form-label {
            color: rgba(255, 255, 255, 0.95);
            font-weight: 600;
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.12);
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            padding: 0.875rem 1.125rem;
            color: white;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.15);
        }
        
        .form-control:focus::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .input-group-text {
            background: rgba(255, 255, 255, 0.12);
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-right: none;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.18);
        }
        
        .form-check-input {
            background-color: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            width: 1.25rem;
            height: 1.25rem;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .form-check-label {
            color: rgba(255, 255, 255, 0.95);
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }
        
        .form-check-label a {
            color: rgba(255, 255, 255, 0.95);
            text-decoration: underline;
            font-weight: 600;
        }
        
        .form-check-label a:hover {
            color: white;
        }
        
        .btn-signup {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1rem;
            font-weight: 700;
            font-size: 1.05rem;
            border-radius: 14px;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-signup:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.75rem;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
        }
        
        .login-link a {
            color: white;
            text-decoration: none;
            font-weight: 700;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: #ff6b9d;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            font-weight: 500;
        }
        
        .password-toggle {
            cursor: pointer;
            color: rgba(255, 255, 255, 0.7);
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: white;
        }
        
        .text-muted {
            color: rgba(255, 255, 255, 0.6) !important;
        }
        
        .role-selector {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .role-btn {
            flex: 1;
            padding: 0.875rem;
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .role-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }
        
        .role-btn.active {
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
        }
        
        .role-btn.active.admin {
            border-color: rgba(13, 110, 253, 0.6);
            background: rgba(13, 110, 253, 0.25);
        }
        
        .role-btn.active.staff {
            border-color: rgba(25, 135, 84, 0.6);
            background: rgba(25, 135, 84, 0.25);
        }
        
        .role-btn.active.student {
            border-color: rgba(111, 66, 193, 0.6);
            background: rgba(111, 66, 193, 0.25);
        }
        
        .role-error {
            color: #ff6b9d;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            font-weight: 500;
            display: none;
        }
        
        @media (max-width: 992px) {
            .register-container {
                flex-direction: column;
            }
            
            .welcome-section {
                min-height: 300px;
            }
            
            .welcome-content h1 {
                font-size: 3rem;
            }
            
            .form-section {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Left Side - Welcome Section -->
        <div class="welcome-section">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            
            <div class="welcome-content">
                <h1>WELCOME</h1>
                <p>We are glad to see you :)</p>
            </div>
        </div>
        
        <!-- Right Side - Form Section -->
        <div class="form-section">
            <div class="form-container">
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p id="roleText">Select your role to continue</p>
                </div>
                
                <div class="form-card">
                    <form method="POST" action="{{ route('register') }}" id="registerForm">
                        @csrf
                        <input type="hidden" name="role" id="roleInput" value="">
                        
                        <!-- Role Selection -->
                        <div class="mb-4">
                            <label class="form-label">Register as</label>
                            <div class="role-selector">
                                <button type="button" class="role-btn admin" onclick="selectRole('admin')" data-role="admin">
                                    <i class="bi bi-shield-check"></i>
                                    <span class="d-none d-md-inline">Admin</span>
                                </button>
                                <button type="button" class="role-btn staff" onclick="selectRole('staff')" data-role="staff">
                                    <i class="bi bi-person-badge"></i>
                                    <span class="d-none d-md-inline">Staff</span>
                                </button>
                                <button type="button" class="role-btn student" onclick="selectRole('student')" data-role="student">
                                    <i class="bi bi-person"></i>
                                    <span class="d-none d-md-inline">Student</span>
                                </button>
                            </div>
                            <div id="roleError" class="role-error">Please select a role to continue</div>
                        </div>
                        
                        <!-- Social Sign Up -->
                        <div class="social-buttons">
                            <button type="button" class="social-btn google">
                                <i class="bi bi-google"></i>
                                <span class="d-none d-md-inline">Google</span>
                            </button>
                            <button type="button" class="social-btn facebook">
                                <i class="bi bi-facebook"></i>
                                <span class="d-none d-md-inline">Facebook</span>
                            </button>
                            <button type="button" class="social-btn twitter">
                                <i class="bi bi-twitter"></i>
                                <span class="d-none d-md-inline">Twitter</span>
                            </button>
                        </div>
                        
                        <!-- Divider -->
                        <div class="divider">
                            <span>Or</span>
                        </div>
                        
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   placeholder="Enter your full name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Father Name -->
                        <div class="mb-3">
                            <label for="father_name" class="form-label">Father's Name</label>
                            <input type="text" 
                                   class="form-control @error('father_name') is-invalid @enderror" 
                                   id="father_name" 
                                   name="father_name" 
                                   placeholder="Enter father's name" 
                                   value="{{ old('father_name') }}" 
                                   required>
                            @error('father_name')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Mother Name -->
                        <div class="mb-3">
                            <label for="mother_name" class="form-label">Mother's Name</label>
                            <input type="text" 
                                   class="form-control @error('mother_name') is-invalid @enderror" 
                                   id="mother_name" 
                                   name="mother_name" 
                                   placeholder="Enter mother's name" 
                                   value="{{ old('mother_name') }}" 
                                   required>
                            @error('mother_name')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Date of Birth -->
                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" 
                                   class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" 
                                   name="date_of_birth" 
                                   value="{{ old('date_of_birth') }}" 
                                   required>
                            @error('date_of_birth')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea 
                                   class="form-control @error('address') is-invalid @enderror" 
                                   id="address" 
                                   name="address" 
                                   rows="3"
                                   placeholder="Enter your address" 
                                   required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   placeholder="Enter your email" 
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Student Details (Only for Students) -->
                        <div id="studentFields" style="display: none;">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Enrollment / Roll Number</label>
                                <input type="text" 
                                       class="form-control @error('student_id') is-invalid @enderror" 
                                       id="student_id" 
                                       name="student_id" 
                                       placeholder="Enter enrollment/roll number" 
                                       value="{{ old('student_id') }}">
                                @error('student_id')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="course" class="form-label">Course</label>
                                    <select id="course" name="course" class="form-select @error('course') is-invalid @enderror">
                                        <option value="">Select Course</option>
                                        @foreach(['BCA','MCA','B.Tech','M.Tech','B.Sc','Polytechnic Diploma','BA','B.Com','BBA','MBA'] as $c)
                                            <option value="{{ $c }}" {{ old('course') === $c ? 'selected' : '' }}>{{ $c }}</option>
                                        @endforeach
                                    </select>
                                    @error('course')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="branch" class="form-label">Branch / Specialization</label>
                                    <select id="branch" name="branch" class="form-select @error('branch') is-invalid @enderror">
                                        <option value="">Select Branch</option>
                                    </select>
                                    @error('branch')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="semester" class="form-label">Semester</label>
                                    <select id="semester" name="semester" class="form-select @error('semester') is-invalid @enderror">
                                        <option value="">Select Semester</option>
                                    </select>
                                    @error('semester')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="year" class="form-label">Year</label>
                                    <select id="year" name="year" class="form-select @error('year') is-invalid @enderror">
                                        <option value="">Select Year</option>
                                        <option value="1st Year" {{ old('year') === '1st Year' ? 'selected' : '' }}>1st Year</option>
                                        <option value="2nd Year" {{ old('year') === '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                                        <option value="3rd Year" {{ old('year') === '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                                        <option value="4th Year" {{ old('year') === '4th Year' ? 'selected' : '' }}>4th Year</option>
                                    </select>
                                    @error('year')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="batch" class="form-label">Batch</label>
                                    <input type="text" 
                                           class="form-control @error('batch') is-invalid @enderror" 
                                           id="batch" 
                                           name="batch" 
                                           placeholder="e.g., 2023-2026"
                                           value="{{ old('batch') }}">
                                    @error('batch')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Phone (Optional) -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone <span class="text-muted">(Optional)</span></label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="Enter phone number" 
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Password -->
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
                                       placeholder="xxxxxxxx" 
                                       required>
                                <span class="input-group-text password-toggle" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="eyeIcon1"></i>
                                </span>
                            </div>
                            @error('password')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Repeat Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Repeat Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="xxxxxxxx" 
                                       required>
                                <span class="input-group-text password-toggle" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye" id="eyeIcon2"></i>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Terms Checkbox -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-signup">
                            <i class="bi bi-person-plus me-2"></i>Sign Up
                        </button>
                    </form>
                    
                    <!-- Login Link -->
                    <div class="login-link">
                        Already have an account? <a href="{{ route('login') }}">Sign in</a>
                    </div>
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
            
            // Update header text
            const roleText = document.getElementById('roleText');
            if (role === 'admin') {
                roleText.textContent = 'Register as Admin';
            } else if (role === 'staff') {
                roleText.textContent = 'Register as Staff';
            } else {
                roleText.textContent = 'Register as Student';
            }
            
            // Show/hide student-specific fields
            const studentFields = document.getElementById('studentFields');
            if (role === 'student') {
                studentFields.style.display = 'block';
            } else {
                studentFields.style.display = 'none';
            }
            
            // Remove active class from all buttons
            document.querySelectorAll('.role-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to selected button
            const activeBtn = document.querySelector(`[data-role="${role}"]`);
            activeBtn.classList.add('active', role);
        }
        
        function togglePassword(fieldId) {
            const password = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId === 'password' ? 'eyeIcon1' : 'eyeIcon2');
            
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
        
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (!selectedRole) {
                e.preventDefault();
                document.getElementById('roleError').style.display = 'block';
                return false;
            }
            
            // If student, ensure course, branch, semester, and year are filled
            if (selectedRole === 'student') {
                const course = document.getElementById('course').value.trim();
                const branch = document.getElementById('branch').value.trim();
                const semester = document.getElementById('semester').value.trim();
                const year = document.getElementById('year').value.trim();
                if (!course || !branch || !semester || !year) {
                    e.preventDefault();
                    alert('Please select your course, branch, semester, and year to continue.');
                    return false;
                }
            }
        });

        // Course -> Branch mapping
        const courseBranchMap = {
            'BCA': ['Computer Science', 'Data Analytics', 'AI & ML'],
            'MCA': ['Computer Science', 'AI & ML'],
            'B.Tech': ['Computer Science', 'IT', 'Electronics', 'Electrical', 'Civil', 'Mechanical', 'Textile'],
            'M.Tech': ['Computer Science', 'Electronics', 'Electrical', 'Civil', 'Mechanical'],
            'B.Sc': ['Computer Science', 'Maths', 'Biology', 'Physics', 'Chemistry'],
            'Polytechnic Diploma': ['Computer Science', 'Electrical', 'Civil', 'Mechanical', 'Textile'],
            'BA': ['English', 'Economics', 'History', 'Political Science'],
            'B.Com': ['Accounts', 'Finance', 'Taxation'],
            'BBA': ['Management', 'Finance', 'Marketing'],
            'MBA': ['Finance', 'Marketing', 'HR', 'Operations']
        };

        // Course -> Semesters (generic up to 8)
        const semesterOptions = ['1st Sem','2nd Sem','3rd Sem','4th Sem','5th Sem','6th Sem','7th Sem','8th Sem'];

        function populateBranches() {
            const course = document.getElementById('course').value;
            const branchSelect = document.getElementById('branch');
            branchSelect.innerHTML = '<option value=\"\">Select Branch</option>';
            if (course && courseBranchMap[course]) {
                courseBranchMap[course].forEach(b => {
                    branchSelect.insertAdjacentHTML('beforeend', `<option value=\"${b}\">${b}</option>`);
                });
            }
        }

        function populateSemesters() {
            const semesterSelect = document.getElementById('semester');
            semesterSelect.innerHTML = '<option value=\"\">Select Semester</option>';
            semesterOptions.forEach(s => {
                semesterSelect.insertAdjacentHTML('beforeend', `<option value=\"${s}\">${s}</option>`);
            });
        }

        document.getElementById('course').addEventListener('change', populateBranches);
        populateBranches();
        populateSemesters();
    </script>
</body>
</html>
