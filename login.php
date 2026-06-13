<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT-AMSOS Login - DENR</title>
    <link rel="shortcut icon" type="image/x-icon" href="icon/amsos.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <style>
        /* CSS from styles.css */
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');

        :root {
            --primary-color: #007bff; /* DENR blue or a chosen primary color */
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --error-color: #dc3545;
            --text-color: #333;
            --light-text-color: #f8f9fa;
            --background-light: #f4f7f6;
            --background-dark: #2c3e50; /* For card backgrounds */
            --border-color: #e0e0e0;
            --card-bg: #ffffff;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-medium: rgba(0, 0, 0, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            overflow: hidden; /* Hide scrollbar due to full-screen slideshow */
        }

        /* --- Container & Slideshow --- */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .slideshow {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .slide {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            filter: brightness(0.7); /* Darken images for better text contrast */
        }

        .slide.active {
            opacity: 1;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Darker overlay for better contrast */
            z-index: 0;
        }

        /* --- Login Wrapper & Card --- */
        .login-wrapper {
            z-index: 1;
            padding: 20px;
            width: 100%;
            max-width: 400px; /* Limit login card width */
        }

        .login-card {
            background-color: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px var(--shadow-medium);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        .logo img {
            max-width: 180px;
            width: 80%;
            margin-bottom: 25px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .login-title {
            font-size: 2.2em;
            color: var(--text-color);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-subtitle {
            font-size: 1.1em;
            color: var(--secondary-color);
            margin-bottom: 30px;
        }

        /* --- Input Groups --- */
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group label {
            position: absolute;
            top: 12px;
            left: 15px;
            color: #999;
            pointer-events: none;
            transition: all 0.3s ease;
            font-size: 1em;
            /* Using sr-only now, but keeping this for reference if you switch to floating labels */
        }

        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px; /* Adjust padding for icon */
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1.1em;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            color: var(--text-color);
            background-color: var(--background-light);
        }

        .input-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .input-group input:focus + .input-icon,
        .input-group input:not(:placeholder-shown) + .input-icon {
            color: var(--primary-color);
        }

        .input-group input::placeholder {
            color: #a0a0a0;
            opacity: 1; /* Ensure placeholder is visible */
        }

        .input-group .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.1em;
            transition: color 0.3s ease;
        }

        /* Password Toggle */
        .password-group {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 1.1em;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        /* --- Button --- */
        .login-button {
            width: 100%;
            padding: 15px;
            background-color: var(--primary-color);
            color: var(--light-text-color);
            border: none;
            border-radius: 8px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }

        .login-button:hover {
            background-color: #0056b3; /* Darker shade of primary */
            transform: translateY(-2px);
        }

        .login-button:active {
            transform: translateY(0);
        }

        /* --- Forgot Password --- */
        .forgot-password {
            margin-top: 20px;
            font-size: 0.95em;
        }

        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        /* --- Animations --- */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- Responsive Design --- */
        @media (max-width: 768px) {
            .login-card {
                padding: 30px 20px;
                margin: 0 15px; /* Add some margin on smaller screens */
            }

            .login-title {
                font-size: 1.8em;
            }

            .login-subtitle {
                font-size: 1em;
            }

            .input-group input,
            .login-button {
                font-size: 1em;
                padding: 12px 12px 12px 40px;
            }

            .input-group .input-icon,
            .password-toggle {
                font-size: 1em;
            }
        }

        @media (max-height: 600px) {
            .login-card {
                padding: 20px;
            }
            .logo img {
                max-width: 140px;
                margin-bottom: 15px;
            }
            .login-title {
                font-size: 1.6em;
            }
            .login-subtitle {
                margin-bottom: 20px;
            }
        }

        /* Screen reader only styles */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="slideshow">
            <img class="slide" src="image/image1.jpg" alt="Scenic Background 1">
            <img class="slide" src="image/image2.jpg" alt="Scenic Background 2">
            <img class="slide" src="image/image3.jpg" alt="Scenic Background 3">
            <div class="overlay"></div> </div>
        
        <div class="login-wrapper">
            <div class="login-card">
                <div class="logo">
                    <a href="index.php" aria-label="Home page">
                        <img src="logo/denr.png" alt="DENR Logo">
                    </a>
                </div>
                <h1 class="login-title">Welcome Back!</h1>
                <p class="login-subtitle">Please log in to your account.</p>

                <form autocomplete="off" id="loginForm" aria-live="polite">
                    <div class="input-group">
                        <label for="username" class="sr-only">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required aria-required="true">
                        <i class="fas fa-user input-icon" aria-hidden="true"></i>
                    </div>
                    <div class="input-group password-group">
                        <label for="password" class="sr-only">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required aria-required="true">
                        <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                        <span class="password-toggle" aria-label="Show password" role="button" tabindex="0">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </span>
                    </div>
                    <button type="submit" class="login-button">Login</button>
                    <div class="forgot-password">
                        <a href="#" aria-label="Forgot your password? Click here.">Forgot Password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Custom right-click prevention (consider impact on accessibility and user expectations)
        document.addEventListener("contextmenu", function(event) {
            console.log("Context menu event detected");
            // Instead of an alert, maybe a subtle toast or simply prevent it.
            // toastr.info("Right-click is disabled on this page.");
            event.preventDefault();
            console.log("Default action prevented");
        });

        // Initialize Toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // JavaScript from scripts.js
        $(document).ready(function() {
            // Slideshow functionality
            let slideIndex = 0;
            const slides = $('.slide');
            const totalSlides = slides.length;

            function showSlides() {
                slides.removeClass('active');
                slides.eq(slideIndex).addClass('active');
                slideIndex = (slideIndex + 1) % totalSlides;
            }

            // Initial display
            if (totalSlides > 0) {
                slides.eq(0).addClass('active');
                setInterval(showSlides, 4000); // Change slide every 4 seconds
            }

            // Password Toggle
            $('.password-toggle').on('click', function() {
                const passwordField = $('#password');
                const icon = $(this).find('i');
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    $(this).attr('aria-label', 'Hide password');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    $(this).attr('aria-label', 'Show password');
                }
            });

            // Login Form Submission
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const username = $('#username').val().trim();
                const password = $('#password').val().trim();

                if (!username || !password) {
                    toastr.warning('Please enter both username and password.');
                    return;
                }

                const loginButton = $('.login-button');
                loginButton.text('Logging in...').prop('disabled', true).addClass('loading');

                $.ajax({
                    url: 'loginhandler.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { username: username, password: password },
                    success: function(result) {
                        loginButton.text('Login').prop('disabled', false).removeClass('loading');

                        if (result.success) {
                            toastr.success(result.message);
                            setTimeout(function() {
                                window.location.href = 'mainmenu.php';
                            }, 1500);
                        } else {
                            toastr.error(result.message || 'Login failed. Please try again.');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        loginButton.text('Login').prop('disabled', false).removeClass('loading');

                        var msg = 'Connection error. Please try again.';
                        try {
                            var resp = JSON.parse(jqXHR.responseText);
                            if (resp.message) msg = resp.message;
                        } catch(e) {
                            if (jqXHR.responseText) msg = jqXHR.responseText;
                        }
                        toastr.error(msg);
                        console.error('AJAX error:', textStatus, errorThrown);
                    }
                });
            });
        });
    </script>
</body>
</html>