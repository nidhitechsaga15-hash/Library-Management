<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Library Management') }}</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
        
        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                    color: #1a202c;
                }
                
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 2rem;
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                }
                
                header {
                    display: flex;
                    justify-content: flex-end;
                    padding: 1.5rem 2rem;
                }
                
                nav {
                    display: flex;
                    gap: 1rem;
                }
                
                .nav-link {
                    padding: 0.75rem 1.5rem;
                    border-radius: 0.5rem;
                    text-decoration: none;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    border: 2px solid transparent;
                }
                
                .nav-link.login {
                    color: white;
                    background: rgba(255, 255, 255, 0.2);
                    backdrop-filter: blur(10px);
                }
                
                .nav-link.login:hover {
                    background: rgba(255, 255, 255, 0.3);
                }
                
                .nav-link.register {
                    color: white;
                    background: white;
                    color: #667eea;
                }
                
                .nav-link.register:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                }
                
                .nav-link.dashboard {
                    color: white;
                    background: rgba(255, 255, 255, 0.2);
                    backdrop-filter: blur(10px);
                }
                
                .nav-link.dashboard:hover {
                    background: rgba(255, 255, 255, 0.3);
                }
                
                .hero {
                    text-align: center;
                    color: white;
                    padding: 4rem 2rem;
                    max-width: 800px;
                    margin: 0 auto;
                }
                
                .hero h1 {
                    font-size: 3.5rem;
                    font-weight: 700;
                    margin-bottom: 1rem;
                    line-height: 1.2;
                    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
                }
                
                .hero .subtitle {
                    font-size: 1.5rem;
                    font-weight: 400;
                    margin-bottom: 2rem;
                    opacity: 0.95;
                    line-height: 1.6;
                }
                
                .hero .description {
                    font-size: 1.125rem;
                    margin-bottom: 3rem;
                    opacity: 0.9;
                    line-height: 1.7;
                }
                
                .features {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 2rem;
                    margin-top: 4rem;
                    max-width: 1000px;
                    margin-left: auto;
                    margin-right: auto;
                }
                
                .feature-card {
                    background: rgba(255, 255, 255, 0.15);
                    backdrop-filter: blur(10px);
                    padding: 2rem;
                    border-radius: 1rem;
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    transition: all 0.3s ease;
                }
                
                .feature-card:hover {
                    transform: translateY(-5px);
                    background: rgba(255, 255, 255, 0.2);
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                }
                
                .feature-icon {
                    font-size: 2.5rem;
                    margin-bottom: 1rem;
                }
                
                .feature-card h3 {
                    font-size: 1.25rem;
                    font-weight: 600;
                    margin-bottom: 0.75rem;
                }
                
                .feature-card p {
                    font-size: 0.95rem;
                    opacity: 0.9;
                    line-height: 1.6;
                }
                
                .cta-buttons {
                    display: flex;
                    gap: 1rem;
                    justify-content: center;
                    margin-top: 2rem;
                    flex-wrap: wrap;
                }
                
                .btn {
                    padding: 1rem 2rem;
                    border-radius: 0.5rem;
                    text-decoration: none;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    display: inline-block;
                }
                
                .btn-primary {
                    background: white;
                    color: #667eea;
                }
                
                .btn-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                }
                
                .btn-secondary {
                    background: rgba(255, 255, 255, 0.2);
                    color: white;
                    border: 2px solid rgba(255, 255, 255, 0.3);
                }
                
                .btn-secondary:hover {
                    background: rgba(255, 255, 255, 0.3);
                }
                
                @media (max-width: 768px) {
                    .hero h1 {
                        font-size: 2.5rem;
                    }
                    
                    .hero .subtitle {
                        font-size: 1.25rem;
                    }
                    
                    .hero .description {
                        font-size: 1rem;
                    }
                    
                    .features {
                        grid-template-columns: 1fr;
                    }
                    
                    header {
                        padding: 1rem;
                    }
                }
            </style>
        @endif
    </head>
    <body>
        <header>
            @if (Route::has('login'))
                <nav>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="nav-link dashboard">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link login">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="nav-link register">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        
        <div class="container">
            <div class="hero">
                <h1>Welcome to Library Management</h1>
                <p class="subtitle">Your comprehensive solution for managing library resources</p>
                <p class="description">
                    Track borrows, manage books, and connect students with knowledge. 
                    Streamline your library operations with our modern and intuitive system.
                </p>
                
                <div class="features">
                    <div class="feature-card">
                        <div class="feature-icon">📚</div>
                        <h3>Book Management</h3>
                        <p>Easily manage your library's collection with advanced search and categorization features.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">👥</div>
                        <h3>Student Tracking</h3>
                        <p>Keep track of all borrows, returns, and student activities in one place.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Fast & Efficient</h3>
                        <p>Streamlined workflows to help you manage your library operations quickly and efficiently.</p>
                    </div>
                </div>
                
                @guest
                <div class="cta-buttons">
                    <a href="{{ route('login') }}" class="btn btn-primary">Get Started</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-secondary">Create Account</a>
                    @endif
                </div>
                @endguest
            </div>
        </div>
    </body>
</html>
