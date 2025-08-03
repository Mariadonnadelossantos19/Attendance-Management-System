<?php
require_once 'init.php';

// Redirect based on authentication status
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: employee/dashboard.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            overflow-x: hidden;
        }

        .container-fluid {
            padding: 0;
        }

        .hero-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin: 2rem;
            padding: 3rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: white;
        }

        .feature-title {
            color: white;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .feature-desc {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .login-subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 1rem 1.2rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .input-group-text {
            background: transparent;
            border: 2px solid #e1e5e9;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #667eea;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .credentials-info {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 2rem;
            text-align: center;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .credentials-text {
            color: #667eea;
            font-weight: 500;
            margin: 0;
        }



        @media (max-width: 768px) {
            .hero-section {
                margin: 1rem;
                padding: 2rem;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .login-container {
                padding: 2rem;
            }
        }

        .fade-in {
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>


    <div class="container-fluid">
        <div class="row min-vh-100 align-items-center">
            <!-- Hero Section -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="hero-section fade-in">
                    <div class="text-center">
                        <div class="logo mb-4">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h1 class="hero-title">Attendance Management</h1>
                        <p class="hero-subtitle">Streamline your workforce tracking with our modern attendance system</p>
                        
                        <div class="row g-4 mt-4">
                            <div class="col-md-6">
                                <div class="feature-card">
                                    <div class="feature-icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <h5 class="feature-title">Admin Portal</h5>
                                    <p class="feature-desc">Manage employees and generate reports</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature-card">
                                    <div class="feature-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <h5 class="feature-title">Employee Portal</h5>
                                    <p class="feature-desc">Clock in/out and view attendance</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login Section -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="login-container fade-in" style="animation-delay: 0.3s;">
                    <div class="text-center mb-4">
                        <h2 class="login-title">Welcome Back</h2>
                        <p class="login-subtitle">Sign in to access your dashboard</p>
                    </div>
                    
                    <form action="auth/login.php" method="POST">
                        <div class="mb-4">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Enter your username" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter your password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>
                    
                    <div class="credentials-info">
                        <p class="credentials-text">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Default Admin:</strong> username: admin, password: admin123
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 