<?php
$page = isset($_GET['p']) ? $_GET['p'] : '';
$allowed = ['about','acknowlege_notification','add_event','add_signature','addequipmenthandler','ai_suggestion','amsos-requestdata','analysis_of_device','analysis','analysisandgraph_datafilter','analysisandgraph','approve','assign','assignactionstaff','assigntracking','backend_analysis','backup','button','calendarScheduler','calendarSchedulerdb','chatbot','comprehensive_reports','connect_otos','connect','data','datarep','default','delete_action','delete_event','delete-receive','deleteEnventory','deletesrfsigner','depreciation_report','details','disapproved','division_employee_inventory','echo','edit_password','edit-feedback','edit-history','edit-receive','editEnventory','editequipmenthandler','editsrfsigner','entrydata','entrydatahandler','entryupdate','equipment_page','fetch_assigactionstaff','fetch_assigntracking','fetch_events','fetch_rictuactionstaff','fetch_station','fetch_table_historymodal','fetch_tracking','fetchdate','fetchdateSRFT','fetchdateSRFTrecent','flash','get_employees','get_filtered_data','get_stations_2','get_stations','getinventory','getMessages','getMessagesUser','history','icteq_chatboot','imageviewer_2','imageviewer','inventory','lifecyclewarrantymonitoring','load_chat','login','loginhandler','logout','mainmenu','maintenance_report','manage_equipment','na_equipment_report','navbar','notification','notify_action','office_employee','options','pnumberscreen','ppc','preventive_maintenance_form','preventive_maintenance_timeline','preventive_maintenance','print-all','printform_1','printform-request','printform','printformdummy','prioritynumber','propertytable','qr','rate','receive_action','recent','reference_generator','repair_frequency','replacement_data','replacement_report','requestlist_1','requestlist','returnedequipment','save_checklist','save_checklist1','save_repairdetails','scanQR','search_inventory','search_inventoryhandler','search_preventive_inventory','sendMessage','services','session_checker','sidebar_1','sidebar','signersactionstaffrfhandler','signersrfhandler','srf-actionedit','srf','srfactionstaffdelete','srfactiontaken','srfdatagraph','srfhistory','srfrequestform','srfwaitingnumber_1','srfwaitingnumber','submit_srfhandler','summary','summaryAI','tablesummary','toast','update_action','update_action3','update_description','update_event','update_inventory','update_notification','update_user','update-receive','updateEnventory','upload_signature','upload','user_data','view_inventory_specs','viewassigntracking','viewuploaded'];

if ($page !== '' && in_array($page, $allowed)) {
    $file = $page . '.php';
    if (file_exists($file)) {
        ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT-AMSOS</title>
    <link rel="icon" href="icon/amsos.ico" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { overflow: hidden; }
        iframe { width: 100%; height: 100vh; border: none; display: block; }
    </style>
</head>
<body>
    <iframe src="<?= htmlspecialchars($file) ?>"></iframe>
</body>
</html><?php
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT-AMSOS | Modern Inventory Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="icon/amsos.ico" type="image/x-icon">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #2ec4b6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            background-color: #fafbff;
            overflow-x: hidden;
        }
        
        /* Navigation */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            transition: all 0.4s ease;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: translateY(-2px);
        }
        
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            padding: 8px 15px !important;
            margin: 0 5px;
            border-radius: 30px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.15);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: white;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 70%;
        }
        
        .btn-login {
            background: white;
            color: var(--primary) !important;
            border-radius: 30px;
            padding: 8px 25px !important;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            color: var(--secondary) !important;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" d="M0,96L48,112C96,128,192,160,288,181.3C384,203,480,213,576,197.3C672,181,768,139,864,138.7C960,139,1056,181,1152,186.7C1248,192,1344,160,1392,144L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: bottom;
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        
        .hero p {
            font-size: 1.3rem;
            max-width: 600px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .hero-btns {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-primary-custom {
            background: white;
            color: var(--primary);
            border: none;
            border-radius: 30px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            color: var(--secondary);
        }
        
        .btn-outline-custom {
            background: transparent;
            color: white;
            border: 2px solid white;
            border-radius: 30px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .btn-outline-custom:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        
        /* Carousel */
        .carousel-section {
            background: white;
            padding: 100px 0;
        }
        
        .carousel-container {
            max-width: 1200px;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(67, 97, 238, 0.15);
        }
        
        .carousel-item {
            height: 500px;
        }
        
        .carousel-item img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }
        
        .carousel-caption {
            background: rgba(0, 0, 0, 0.6);
            padding: 25px;
            border-radius: 15px;
            bottom: 50px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            max-width: 700px;
        }
        
        .carousel-caption h5 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .carousel-control-prev, .carousel-control-next {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            top: 50%;
            transform: translateY(-50%);
            opacity: 1;
            transition: all 0.3s ease;
        }
        
        .carousel-control-prev:hover, .carousel-control-next:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .carousel-control-prev {
            left: 30px;
        }
        
        .carousel-control-next {
            right: 30px;
        }
        
        /* Features Section */
        .features {
            padding: 100px 0;
            background: #f0f4ff;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }
        
        .section-title p {
            color: #666;
            max-width: 700px;
            margin: 20px auto 0;
            font-size: 1.1rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.4s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(67, 97, 238, 0.15);
        }
        
        .feature-icon {
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            font-size: 3.5rem;
        }
        
        .feature-content {
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .feature-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--secondary);
        }
        
        .feature-content p {
            color: #666;
            margin-bottom: 20px;
            flex-grow: 1;
        }
        
        .feature-link {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .feature-link i {
            margin-left: 8px;
            transition: transform 0.3s ease;
        }
        
        .feature-link:hover {
            color: var(--secondary);
        }
        
        .feature-link:hover i {
            transform: translateX(5px);
        }
        
        /* Benefits Section */
        .benefits {
            padding: 100px 0;
            background: white;
        }
        
        .benefit-box {
            display: flex;
            align-items: center;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .benefit-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(67, 97, 238, 0.15);
        }
        
        .benefit-icon {
            width: 80px;
            height: 80px;
            background: rgba(67, 97, 238, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 25px;
            flex-shrink: 0;
        }
        
        .benefit-icon i {
            font-size: 2rem;
            color: var(--primary);
        }
        
        .benefit-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--secondary);
        }
        
        /* CTA Section */
        .cta {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" d="M0,160L48,181.3C96,203,192,245,288,261.3C384,277,480,267,576,240C672,213,768,171,864,144C960,117,1056,107,1152,117.3C1248,128,1344,160,1392,176L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: bottom;
            opacity: 0.3;
        }
        
        .cta-content {
            position: relative;
            z-index: 2;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .cta p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 20px;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .footer-logo img {
            height: 40px;
            margin-right: 15px;
        }
        
        .footer-logo span {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .footer p {
            color: #aaa;
            margin-bottom: 20px;
            max-width: 300px;
        }
        
        .footer h5 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--accent);
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: #aaa;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .footer-links a:hover {
            color: white;
            transform: translateX(5px);
        }
        
        .footer-links a i {
            margin-right: 10px;
            color: var(--accent);
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 50px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #777;
            font-size: 0.9rem;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fadeInUp {
            animation: fadeInUp 0.8s ease forwards;
        }
        
        .delay-1 {
            animation-delay: 0.2s;
        }
        
        .delay-2 {
            animation-delay: 0.4s;
        }
        
        .delay-3 {
            animation-delay: 0.6s;
        }
        
        /* Responsive Design */
        @media (max-width: 991px) {
            .hero h1 {
                font-size: 2.8rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .carousel-item {
                height: 400px;
            }
        }
        
        @media (max-width: 767px) {
            .hero {
                padding: 100px 0 60px;
            }
            
            .hero h1 {
                font-size: 2.3rem;
            }
            
            .hero-btns {
                flex-direction: column;
                align-items: center;
            }
            
            .carousel-item {
                height: 300px;
            }
            
            .carousel-caption {
                bottom: 20px;
                padding: 15px;
            }
            
            .carousel-caption h5 {
                font-size: 1.3rem;
            }
            
            .feature-card {
                margin-bottom: 30px;
            }
            
            .benefit-box {
                flex-direction: column;
                text-align: center;
            }
            
            .benefit-icon {
                margin-right: 0;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="icon/amsos.ico" alt="ICT-AMSOS Logo">
                <span>ICT-AMSOS</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" data-toggle="modal" data-target="#aboutModal">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="?p=services">Services</a></li>
                    <li class="nav-item"><a class="nav-link btn-login" href="?p=login">Log In</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center hero-content">
                    <h1 class="fadeInUp">Asset Management and Service Optimization System<br><span style="font-size:0.6em; opacity:0.9;">ICT-AMSOS</span></h1>
                    <p class="fadeInUp delay-1">Powerful asset management solution for tracking hardware, software, and network components with precision and ease.</p>
                    <div class="hero-btns">
                        <a href="#features" class="btn btn-primary-custom fadeInUp delay-2">Explore Features</a>
                        <a href="?p=login" class="btn btn-outline-custom fadeInUp delay-3">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Carousel Section -->
    <section class="carousel-section">
        <div class="container">
            <div class="carousel-container">
                <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                        <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                        <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                    </ol>
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="image/image2.jpg" class="d-block w-100" alt="Inventory Management">
                            <div class="carousel-caption">
                                <h5>Streamline Inventory Management</h5>
                                <p>Efficient solutions tailored for your ICT needs</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="image/image1.jpg" class="d-block w-100" alt="Data Insights">
                            <div class="carousel-caption">
                                <h5>Data-Driven Insights</h5>
                                <p>Make informed decisions with real-time data</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="image/image3.jpg" class="d-block w-100" alt="Up-to-Date Inventory">
                            <div class="carousel-caption">
                                <h5>Up-to-Date Inventory</h5>
                                <p>Maintain an accurate record of all ICT assets</p>
                            </div>
                        </div>
                    </div>
                    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-title">
                <h2>Key Features</h2>
                <p>Discover how ICT-AMSOS transforms your inventory management with powerful capabilities</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4 fadeInUp">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Asset Tracking</h3>
                            <p>Efficiently track and manage all ICT assets, including hardware, software, and network components with real-time updates and comprehensive reporting.</p>
                            <a href="?p=services" class="feature-link">Learn more <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4 fadeInUp delay-1">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Informed Decisions</h3>
                            <p>Access powerful analytics and reporting tools to make informed decisions regarding asset allocation, maintenance, and future investments.</p>
                            <a href="?p=analysisandgraph" class="feature-link">Learn more <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4 fadeInUp delay-2">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Up-to-Date Inventory</h3>
                            <p>Maintain an accurate and current inventory database with automated updates, reducing errors and ensuring compliance with industry standards.</p>
                            <a href="?p=inventory" class="feature-link">Learn more <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="benefits">
        <div class="container">
            <div class="section-title">
                <h2>Key Benefits</h2>
                <p>Experience the advantages of streamlined ICT inventory management</p>
            </div>
            <div class="row">
                <div class="col-lg-6 fadeInUp">
                    <div class="benefit-box">
                        <div class="benefit-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="benefit-content">
                            <h3>Increased Efficiency</h3>
                            <p>Streamline your inventory processes to save valuable time and reduce operational errors by up to 65%.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 fadeInUp delay-1">
                    <div class="benefit-box">
                        <div class="benefit-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="benefit-content">
                            <h3>Cost Savings</h3>
                            <p>Optimize asset utilization to reduce costs and avoid unnecessary purchases with intelligent tracking.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 fadeInUp delay-2">
                    <div class="benefit-box">
                        <div class="benefit-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="benefit-content">
                            <h3>Enhanced Security</h3>
                            <p>Protect your ICT assets with comprehensive tracking and security features to prevent loss and theft.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 fadeInUp delay-3">
                    <div class="benefit-box">
                        <div class="benefit-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="benefit-content">
                            <h3>Real-time Updates</h3>
                            <p>Stay informed with instant updates on asset status, maintenance schedules, and utilization metrics.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Transform Your Inventory Management?</h2>
                <p>Join organizations that have streamlined their ICT asset tracking with ICT-AMSOS</p>
                <a href="?p=login" class="btn btn-primary-custom">Get Started Now</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="footer-logo">
                        <img src="icon/amsos.ico" alt="ICT-AMSOS Logo">
                        <span>ICT-AMSOS</span>
                    </div>
                    <p>Streamlining ICT inventory management for organizations DENR Caraga Wide with precision and efficiency.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="#features"><i class="fas fa-chevron-right"></i> Features</a></li>
                        <li><a href="#benefits"><i class="fas fa-chevron-right"></i> Benefits</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> About Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Resources</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Documentation</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Support</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Blog</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i>DENR RICTU- CARAGA REGION</a></li>
                        <li><a href="#"><i class="fas fa-phone"></i>09478984921</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> info@ict-amsos.com</a></li>
                        <li><a href="#"><i class="fas fa-clock"></i> Mon-Fri: 9AM - 6PM</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2024 ICT-AMSOS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- About Modal -->
    <div class="modal fade" id="aboutModal" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #4361ee, #3a0ca3); color: white;">
                    <h5 class="modal-title" id="aboutModalLabel"><i class="fas fa-info-circle"></i> About ICT-AMSOS</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 0;">
                    <iframe src="about.php" style="width: 100%; height: 70vh; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Navbar background change on scroll
        $(window).scroll(function() {
            if ($(this).scrollTop() > 50) {
                $('.navbar').css('background', 'linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%)');
                $('.navbar').css('box-shadow', '0 4px 20px rgba(0, 0, 0, 0.1)');
            } else {
                $('.navbar').css('background', 'linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%)');
                $('.navbar').css('box-shadow', 'none');
            }
        });
        
        // Initialize animations
        $(document).ready(function() {
            // Smooth scrolling for anchor links
            $(document).on('click', 'a[href*="#"]', function(e) {
                var target = $(this).attr('href');
                if (target !== '#' && $(target).length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: $(target).offset().top - 70
                    }, 600);
                }
            });
            
            // Add animation class to elements when they come into view
            $(window).scroll(function() {
                $('.fadeInUp').each(function() {
                    var position = $(this).offset().top;
                    var scrollPosition = $(window).scrollTop() + $(window).height();
                    if (position < scrollPosition) {
                        $(this).addClass('animated');
                    }
                });
            });
            
            // Trigger scroll event on page load
            $(window).scroll();
        });
    </script>
</body>
</html>