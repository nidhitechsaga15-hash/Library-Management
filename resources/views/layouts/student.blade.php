<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Dashboard') - Library Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.0/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.0/css/buttons.bootstrap5.min.css">
    
    @stack('styles')
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
        }
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            position: fixed;
            height: 100%;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar.collapsed {
            width: 80px;
        }
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .sidebar-header .logo {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }
        .sidebar-header .app-name {
            font-size: 18px;
            font-weight: 600;
            color: #fff;
        }
        .sidebar-header .app-tagline {
            font-size: 12px;
            color: rgba(255,255,255,0.8);
        }
        .sidebar.collapsed .sidebar-header .app-name,
        .sidebar.collapsed .sidebar-header .app-tagline {
            display: none;
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .menu-section {
            padding: 0 20px;
            margin-bottom: 20px;
        }
        .menu-section-title {
            font-size: 11px;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            padding: 0 15px;
        }
        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            border-radius: 8px;
            margin: 4px 10px;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
        }
        .menu-item:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            transform: translateX(5px);
        }
        .menu-item.active {
            background: rgba(255,255,255,0.25);
            color: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .menu-item i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
        }
        .sidebar.collapsed .menu-item span {
            display: none;
        }
        .sidebar.collapsed .menu-item {
            justify-content: center;
            padding: 12px 0;
            margin: 4px auto;
            width: 50px;
        }
        .sidebar.collapsed .menu-item i {
            margin-right: 0;
        }
        .sidebar.collapsed .menu-section-title {
            text-align: center;
            padding: 0;
        }
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            background-color: #f4f6f9;
            transition: all 0.3s;
        }
        .main-wrapper.shifted {
            margin-left: 80px;
        }
        .top-header {
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-header .navbar-toggler {
            border: none;
            font-size: 24px;
            color: #555;
        }
        .top-header .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .top-header .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .top-header .user-avatar:hover {
            transform: scale(1.1);
        }
        .top-header .user-profile-wrapper {
            position: relative;
        }
        .profile-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            min-width: 200px;
            max-width: 250px;
            z-index: 1050;
            display: none;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .profile-dropdown.show {
            display: block;
        }
        .profile-dropdown-header {
            padding: 12px 15px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .profile-dropdown-header .greeting {
            font-weight: 600;
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .profile-dropdown-body {
            padding: 8px 0;
        }
        .profile-dropdown-item {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #374151;
            text-decoration: none;
            transition: background 0.2s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 14px;
            cursor: pointer;
        }
        .profile-dropdown-item:hover {
            background: #f3f4f6;
        }
        .profile-dropdown-item i {
            width: 18px;
            font-size: 14px;
            color: #667eea;
        }
        .profile-dropdown-item.logout {
            color: #dc3545;
            border-top: 1px solid #e5e7eb;
            margin-top: 4px;
            padding-top: 12px;
        }
        .profile-dropdown-item.logout i {
            color: #dc3545;
        }
        .profile-dropdown-item.logout:hover {
            background: #fef2f2;
        }
        .notification-wrapper {
            position: relative;
        }
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            width: 350px;
            max-width: 90vw;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            z-index: 1001;
            overflow: hidden;
        }
        .notification-dropdown-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9fbfd;
        }
        .notification-dropdown-body {
            max-height: 400px;
            overflow-y: auto;
        }
        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }
        .notification-item:hover {
            background: #f9fbfd;
        }
        .notification-item.unread {
            background: #f0f7ff;
            border-left: 3px solid #667eea;
        }
        .notification-item.unread:hover {
            background: #e6f2ff;
        }
        .notification-item-title {
            font-weight: 600;
            font-size: 14px;
            color: #333;
            margin-bottom: 4px;
        }
        .notification-item-message {
            font-size: 13px;
            color: #666;
            margin-bottom: 4px;
        }
        .notification-item-time {
            font-size: 11px;
            color: #999;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 30px;
            margin-bottom: 30px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0 0 12px 12px;
        }
        .page-header h4 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .content-area {
            padding: 0;
        }
        .content-area .container-fluid {
            padding: 0 15px 30px;
        }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: none;
            height: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .stat-card .stat-content {
            flex-grow: 1;
        }
        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #333;
        }
        .stat-card .stat-label {
            font-size: 14px;
            font-weight: 500;
            color: #555;
            margin-bottom: 5px;
        }
        .stat-card .stat-desc {
            font-size: 12px;
            color: #888;
        }
        .stat-card .stat-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #fff;
            margin-left: 20px;
        }
        .stat-card .stat-icon-wrapper.primary { background: linear-gradient(45deg, #667eea, #764ba2); }
        .stat-card .stat-icon-wrapper.warning { background: linear-gradient(45deg, #f6e05e, #fbd38d); }
        .stat-card .stat-icon-wrapper.success { background: linear-gradient(45deg, #48bb78, #68d391); }
        .stat-card .stat-icon-wrapper.danger { background: linear-gradient(45deg, #f56565, #ef4444); }
        .stat-card .stat-icon-wrapper.info { background: linear-gradient(45deg, #4299e1, #63b3ed); }
        .card-modern {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .card-header-modern {
            padding: 20px 25px 20px 25px;
            border-bottom: 1px solid #eee;
            background-color: #f9fbfd;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0;
            width: 100%;
            box-sizing: border-box;
            margin: 0;
        }
        .card-header-modern h5 {
            margin: 0;
            padding: 0;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
        }
        .card-header-modern .btn {
            margin: 0;
            margin-left: auto;
            padding: 8px 16px;
            flex-shrink: 0;
            white-space: nowrap;
        }
        .form-actions {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        @media (min-width: 576px) {
            .form-actions {
                flex-direction: row;
            }
        }
        .table-modern {
            width: 100%;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-modern th, .table-modern td {
            padding: 15px 25px;
            vertical-align: middle;
            border-top: 1px solid #eee;
        }
        .table-modern thead th {
            background-color: #f9fbfd;
            color: #555;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #eee;
        }
        .table-modern tbody tr:hover {
            background-color: #f3f4f6;
        }
        .table-modern tbody td {
            font-size: 14px;
            color: #333;
        }
        .hover-shadow {
            transition: all 0.3s ease;
        }
        .hover-shadow:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .sidebar {
                left: -260px;
            }
            .sidebar.show {
                left: 0;
            }
            .main-wrapper {
                margin-left: 0;
            }
            .main-wrapper.shifted {
                margin-left: 0;
            }
            .top-header {
                padding: 15px 20px;
            }
            .page-header {
                padding: 15px 20px;
            }
            .content-area .container-fluid {
                padding: 0 15px 20px;
            }
        }
    </style>
</head>
<body>
    <div >
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <h1 class="app-name">STUDENT</h1>
                    <p class="app-tagline">Library Portal</p>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-section">
                    <h6 class="menu-section-title">MAIN</h6>
                    <a href="{{ route('student.dashboard') }}" class="menu-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="menu-section">
                    <h6 class="menu-section-title">Books</h6>
                    <a href="{{ route('student.books.index') }}" class="menu-item {{ request()->routeIs('student.books.index') ? 'active' : '' }}">
                        <i class="fas fa-book"></i> <span>Browse Books</span>
                    </a>
                    <a href="{{ route('student.books.search') }}" class="menu-item {{ request()->routeIs('student.books.search') ? 'active' : '' }}">
                        <i class="fas fa-search"></i> <span>Search Books</span>
                    </a>
                    <a href="{{ route('student.my-books') }}" class="menu-item {{ request()->routeIs('student.my-books') ? 'active' : '' }}">
                        <i class="fas fa-book-open"></i> <span>My Books</span>
                    </a>
                    <a href="{{ route('student.reservations.index') }}" class="menu-item {{ request()->routeIs('student.reservations.*') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i> <span>My Reservations</span>
                    </a>
                    <a href="{{ route('student.library-card.show') }}" class="menu-item {{ request()->routeIs('student.library-card.*') ? 'active' : '' }}">
                        <i class="fas fa-id-card"></i> <span>Library Card</span>
                    </a>
                </div>
                
                <div class="menu-section">
                    <h6 class="menu-section-title">Account</h6>
                    <a href="{{ route('student.chatbot.index') }}" class="menu-item {{ request()->routeIs('student.chatbot.*') ? 'active' : '' }}">
                        <i class="fas fa-robot"></i> <span>Library Chatbot</span>
                    </a>
                    <a href="{{ route('student.payments.index') }}" class="menu-item {{ request()->routeIs('student.payments.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card"></i> <span>Payments</span>
                    </a>
                    <a href="{{ route('student.lms.recommendations') }}" class="menu-item {{ request()->routeIs('student.lms.*') ? 'active' : '' }}">
                        <i class="fas fa-graduation-cap"></i> <span>Course Books</span>
                    </a>
                    <a href="{{ route('chat.index') }}" class="menu-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i> <span>Chat with Staff</span>
                        <span class="badge bg-danger ms-auto" id="chatUnreadBadge" style="display: none;">0</span>
                    </a>
                    <a href="{{ route('student.fines.index') }}" class="menu-item {{ request()->routeIs('student.fines.*') ? 'active' : '' }}">
                        <i class="fas fa-dollar-sign"></i> <span>Fine History</span>
                    </a>
                    <a href="{{ route('student.profile.show') }}" class="menu-item {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
                        <i class="fas fa-user"></i> <span>Profile</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="main-wrapper" id="main-wrapper">
            <!-- Top Header -->
            <header class="top-header">
                <button class="navbar-toggler d-block d-md-none" type="button" id="sidebarToggleMobile">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0 fw-bold d-none d-md-block">@yield('page-title', 'Dashboard')</h4>
                
                <div style="display: flex; align-items: center; gap: 15px;">
                    <!-- Notification Bell -->
                    <div class="notification-wrapper" style="position: relative;">
                        <button class="btn btn-link position-relative p-2" id="notificationToggle" onclick="toggleNotificationDropdown(event)" style="text-decoration: none; color: #333;">
                            <i class="fas fa-bell" style="font-size: 20px;"></i>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" id="notificationBadge" style="display: none; font-size: 10px; padding: 2px 5px;">0</span>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown" style="display: none;">
                            <div class="notification-dropdown-header">
                                <h6 class="mb-0 fw-bold">Notifications</h6>
                                <button class="btn btn-sm btn-link p-0" onclick="markAllNotificationsRead()" id="markAllReadBtn" style="display: none;">Mark all as read</button>
                            </div>
                            <div class="notification-dropdown-body" id="notificationList">
                                <div class="text-center p-3 text-muted">
                                    <i class="fas fa-bell-slash mb-2" style="font-size: 24px;"></i>
                                    <p class="mb-0 small">No notifications</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="user-profile-wrapper">
                        <div class="user-profile" id="profileToggle" onclick="toggleProfileDropdown(event)" style="cursor: pointer;">
                            <span class="text-muted d-none d-md-block">{{ Auth::user()->name }}</span>
                            <div class="user-avatar">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        </div>
                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="profile-dropdown-header">
                            <div class="greeting">HELLO {{ strtoupper(Auth::user()->name) }}</div>
                        </div>
                        <div class="profile-dropdown-body">
                            <a href="{{ route('student.profile.show') }}" class="profile-dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>Profile</span>
                            </a>
                            <a href="{{ route('student.profile.change-password') }}" class="profile-dropdown-item">
                                <i class="fas fa-key"></i>
                                <span>Change Password</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="profile-dropdown-item logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Header (Gradient Bar) -->
            <div class="page-header">
                <h4><i class="fas fa-home"></i> @yield('page-title', 'Dashboard')</h4>
                <button class="btn btn-light btn-sm d-none d-md-block" id="fullscreenToggle">
                    <i class="fas fa-expand"></i>
                </button>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.0.0/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.0/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.print.min.js"></script>

    <script>
        // Notification dropdown toggle
        window.toggleNotificationDropdown = function(e) {
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }
            var dropdown = document.getElementById('notificationDropdown');
            var profileDropdown = document.getElementById('profileDropdown');
            if (dropdown) {
                var isVisible = dropdown.style.display === 'block';
                if (isVisible) {
                    dropdown.style.display = 'none';
                } else {
                    if (profileDropdown) profileDropdown.style.display = 'none';
                    dropdown.style.display = 'block';
                    loadNotifications();
                }
            }
        };

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            var notificationWrapper = document.querySelector('.notification-wrapper');
            var profileWrapper = document.querySelector('.user-profile-wrapper');
            if (notificationWrapper && !notificationWrapper.contains(e.target)) {
                document.getElementById('notificationDropdown').style.display = 'none';
            }
            if (profileWrapper && !profileWrapper.contains(e.target)) {
                document.getElementById('profileDropdown').style.display = 'none';
            }
        });

        // Load notifications
        function loadNotifications() {
            $.ajax({
                url: '{{ route("notifications.index") }}',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    updateNotificationBadge(response.unreadCount);
                    renderNotifications(response.notifications);
                }
            });
        }

        // Render notifications
        function renderNotifications(notifications) {
            var list = $('#notificationList');
            if (notifications.length === 0) {
                list.html('<div class="text-center p-3 text-muted"><i class="fas fa-bell-slash mb-2" style="font-size: 24px;"></i><p class="mb-0 small">No notifications</p></div>');
                $('#markAllReadBtn').hide();
                return;
            }

            var html = '';
            var hasUnread = false;
            notifications.forEach(function(notif) {
                if (!notif.is_read) hasUnread = true;
                var timeAgo = getTimeAgo(notif.created_at);
                var unreadClass = !notif.is_read ? 'unread' : '';
                html += '<div class="notification-item ' + unreadClass + '" onclick="markNotificationRead(' + notif.id + ', \'' + (notif.link || '#') + '\')">';
                html += '<div class="notification-item-title">' + notif.title + '</div>';
                html += '<div class="notification-item-message">' + notif.message + '</div>';
                html += '<div class="notification-item-time">' + timeAgo + '</div>';
                html += '</div>';
            });
            list.html(html);
            $('#markAllReadBtn').toggle(hasUnread);
        }

        // Update notification badge
        function updateNotificationBadge(count) {
            var badge = $('#notificationBadge');
            if (count > 0) {
                badge.text(count > 99 ? '99+' : count).show();
            } else {
                badge.hide();
            }
        }

        // Mark notification as read
        function markNotificationRead(id, link) {
            $.ajax({
                url: '{{ route("notifications.read", ":id") }}'.replace(':id', id),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    if (link && link !== '#') {
                        // Convert full URL to relative path if needed
                        let finalLink = link;
                        if (link.startsWith('http://') || link.startsWith('https://')) {
                            try {
                                const url = new URL(link);
                                finalLink = url.pathname + (url.search || '') + (url.hash || '');
                            } catch (e) {
                                // If URL parsing fails, try to extract path manually
                                const match = link.match(/https?:\/\/[^\/]+(\/.*)/);
                                if (match) {
                                    finalLink = match[1];
                                }
                            }
                        }
                        window.location.href = finalLink;
                    } else {
                        loadNotifications();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error marking notification as read:', error);
                    // Still try to navigate if link exists
                    if (link && link !== '#') {
                        let finalLink = link;
                        if (link.startsWith('http://') || link.startsWith('https://')) {
                            try {
                                const url = new URL(link);
                                finalLink = url.pathname + (url.search || '') + (url.hash || '');
                            } catch (e) {
                                const match = link.match(/https?:\/\/[^\/]+(\/.*)/);
                                if (match) {
                                    finalLink = match[1];
                                }
                            }
                        }
                        window.location.href = finalLink;
                    }
                }
            });
        }

        // Mark all as read
        function markAllNotificationsRead() {
            $.ajax({
                url: '{{ route("notifications.read-all") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    loadNotifications();
                }
            });
        }

        // Get time ago
        function getTimeAgo(dateString) {
            var date = new Date(dateString);
            var now = new Date();
            var diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hour' + (Math.floor(diff / 3600) > 1 ? 's' : '') + ' ago';
            if (diff < 604800) return Math.floor(diff / 86400) + ' day' + (Math.floor(diff / 86400) > 1 ? 's' : '') + ' ago';
            return date.toLocaleDateString();
        }

        // Play notification sound
        function playNotificationSound() {
            var audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUKjl8LZjGwU8kdfyzHksBSR3x/DdkEAKFF606euoVRQKRp/g8r5sIQUrgc7y2Yk2CBtpvfDknE4MDlCo5fC2YxsFPJHX8sx5LAUkd8fw3ZBAC');
            audio.play().catch(function() {});
        }

        // Poll for new notifications
        var lastNotificationCount = 0;
        function pollNotifications() {
            $.ajax({
                url: '{{ route("notifications.index") }}',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.unreadCount > lastNotificationCount && lastNotificationCount > 0) {
                        playNotificationSound();
                    }
                    lastNotificationCount = response.unreadCount;
                    updateNotificationBadge(response.unreadCount);
                }
            });
        }

        // Global function for profile dropdown toggle
        window.toggleProfileDropdown = function(e) {
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }
            var dropdown = document.getElementById('profileDropdown');
            var notificationDropdown = document.getElementById('notificationDropdown');
            if (dropdown) {
                var isVisible = dropdown.style.display === 'block';
                if (isVisible) {
                    dropdown.style.display = 'none';
                    dropdown.classList.remove('show');
                } else {
                    if (notificationDropdown) notificationDropdown.style.display = 'none';
                    // Close any other open dropdowns
                    document.querySelectorAll('.profile-dropdown').forEach(function(el) {
                        el.style.display = 'none';
                        el.classList.remove('show');
                    });
                    dropdown.style.display = 'block';
                    dropdown.classList.add('show');
                }
            }
        };

        $(document).ready(function() {
            loadNotifications();
            setInterval(pollNotifications, 10000); // Poll every 10 seconds
            
            // Poll for unread chat messages
            function pollChatUnreadCount() {
                $.ajax({
                    url: '{{ route("chat.unread-count") }}',
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        const badge = document.getElementById('chatUnreadBadge');
                        if (badge) {
                            if (response.unread_count > 0) {
                                badge.textContent = response.unread_count;
                                badge.style.display = 'inline-block';
                            } else {
                                badge.style.display = 'none';
                            }
                        }
                    }
                });
            }
            pollChatUnreadCount();
            setInterval(pollChatUnreadCount, 15000); // Poll every 15 seconds
            // Initialize DataTables for all tables with class 'data-table'
            $('.data-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5',
                    'print'
                ],
                "pagingType": "full_numbers",
                "language": {
                    "search": "Search:",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });

            // Sidebar toggle for mobile
            $('#sidebarToggleMobile').on('click', function() {
                $('#sidebar').toggleClass('show');
            });

            // Profile dropdown toggle - jQuery backup
            $('#profileToggle').on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                if (typeof window.toggleProfileDropdown === 'function') {
                    window.toggleProfileDropdown(e);
                }
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#profileToggle, #profileDropdown').length) {
                    var dropdown = document.getElementById('profileDropdown');
                    if (dropdown) {
                        dropdown.style.display = 'none';
                        dropdown.classList.remove('show');
                    }
                }
            });
            
            // Prevent dropdown from closing when clicking inside it
            $(document).on('click', '#profileDropdown', function(e) {
                e.stopPropagation();
            });

            // Fullscreen toggle
            $('#fullscreenToggle').on('click', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                }
            });
        });
    </script>
    
    <!-- Sweet Alert 2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Handle duplicate request alert -->
    @if(session('duplicate_request'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Already Requested!',
            text: '{{ session("duplicate_request") }}',
            confirmButtonText: 'OK',
            confirmButtonColor: '#667eea'
        });
    </script>
    @endif
    
    @stack('scripts')
</body>
</html>
