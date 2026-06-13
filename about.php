<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT-AMOS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f0f4f0 0%, #e8f0e8 100%);
            color: #2d3748;
            line-height: 1.7;
            min-height: 100vh;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: #fff;
            padding: 50px 20px 40px;
            text-align: center;
            border-radius: 0 0 30px 30px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }

        header .logo {
            max-width: 280px;
            height: auto;
            margin-bottom: 20px;
        }

        header h1 {
            color: #006400;
            font-size: 2.5em;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        header p {
            color: #4a5568;
            font-size: 1.1em;
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        /* Sections */
        section {
            background: #fff;
            border-radius: 16px;
            padding: 35px 40px;
            margin-bottom: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        h2 {
            color: #006400;
            font-size: 1.6em;
            font-weight: 600;
            padding-bottom: 12px;
            margin-bottom: 20px;
            border-bottom: 3px solid #e2e8f0;
            position: relative;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #006400, #4caf50);
            border-radius: 2px;
        }

        section p {
            font-size: 1.05em;
            color: #4a5568;
        }

        /* Features */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .feature {
            background: #f8faf8;
            border-radius: 12px;
            padding: 24px;
            border-left: 4px solid #006400;
            transition: background 0.2s ease;
        }

        .feature:hover {
            background: #f0f7f0;
        }

        .feature h3 {
            color: #1a3a1a;
            font-size: 1.15em;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .feature i {
            color: #006400;
            margin-right: 10px;
            font-size: 1.2em;
        }

        .logo-details i {
            color: #006400;
            margin-right: 8px;
        }

        h2 i {
            color: #006400;
            margin-right: 10px;
        }

        .feature p {
            font-size: 0.95em;
            color: #4a5568;
            line-height: 1.6;
        }

        /* Logo Details */
        .logo-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 5px;
        }

        .logo-details {
            background: #f8faf8;
            border-radius: 12px;
            padding: 22px;
            border-top: 3px solid #006400;
            transition: background 0.2s ease;
        }

        .logo-details:hover {
            background: #f0f7f0;
        }

        .logo-details h3 {
            color: #006400;
            font-size: 1.05em;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .logo-details ul {
            list-style: none;
            padding: 0;
        }

        .logo-details ul li {
            margin: 8px 0;
            padding-left: 20px;
            position: relative;
            font-size: 0.95em;
            color: #4a5568;
            line-height: 1.6;
        }

        .logo-details ul li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 9px;
            width: 8px;
            height: 8px;
            background: #006400;
            border-radius: 50%;
            opacity: 0.6;
        }

        .logo-details p {
            font-size: 0.95em;
            color: #4a5568;
            line-height: 1.6;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            header {
                padding: 40px 20px 30px;
            }

            header h1 {
                font-size: 2em;
            }

            section {
                padding: 28px 30px;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            header {
                padding: 30px 15px 25px;
                border-radius: 0 0 20px 20px;
            }

            header .logo {
                max-width: 200px;
            }

            header h1 {
                font-size: 1.6em;
            }

            header p {
                font-size: 0.95em;
            }

            section {
                padding: 22px 20px;
                border-radius: 12px;
            }

            h2 {
                font-size: 1.3em;
            }

            section p {
                font-size: 0.95em;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .logo-section {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .feature {
                padding: 18px;
            }

            .logo-details {
                padding: 18px;
            }
        }

        @media (max-width: 480px) {
            header {
                padding: 20px 12px 20px;
                margin-bottom: 20px;
                border-radius: 0 0 16px 16px;
            }

            header .logo {
                max-width: 160px;
                margin-bottom: 14px;
            }

            header h1 {
                font-size: 1.3em;
                letter-spacing: 1px;
            }

            header p {
                font-size: 0.85em;
            }

            section {
                padding: 18px 15px;
                margin-bottom: 16px;
            }

            h2 {
                font-size: 1.15em;
                padding-bottom: 10px;
                margin-bottom: 14px;
            }

            h2::after {
                width: 40px;
            }

            section p {
                font-size: 0.9em;
            }

            .features-grid {
                gap: 12px;
            }

            .feature {
                padding: 14px;
                border-left-width: 3px;
            }

            .feature h3 {
                font-size: 1em;
            }

            .feature p {
                font-size: 0.88em;
            }

            .logo-section {
                gap: 12px;
            }

            .logo-details {
                padding: 14px;
            }

            .logo-details h3 {
                font-size: 0.95em;
            }

            .logo-details ul li {
                font-size: 0.88em;
                padding-left: 16px;
            }

            .logo-details ul li::before {
                width: 6px;
                height: 6px;
                top: 7px;
            }

            .logo-details p {
                font-size: 0.88em;
            }
        }

        @media (max-width: 360px) {
            header h1 {
                font-size: 1.1em;
            }

            section {
                padding: 14px 12px;
            }

            h2 {
                font-size: 1.05em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <img src="logo/amsos.png" alt="ICT-AMOS Logo" class="logo">
            <h1><i class="fas fa-cogs"></i> ICT-AMOS</h1>
            <p><i class="fas fa-server"></i> Serving as the backbone for DENR's ICT infrastructure.</p>
        </header>

        <section id="overview">
            <h2><i class="fas fa-info-circle"></i> Overview</h2>
            <p>ICT-AMOS (Information and Communication Technology - Asset Management and Service Optimization System) plays a pivotal role within the Department of Environment and Natural Resources (DENR). Here's a more detailed explanation:</p>
        </section>

        <section id="features">
            <h2><i class="fas fa-star"></i> Key Features</h2>
            <div class="features-grid">
                <div class="feature">
                    <h3><i class="fas fa-boxes"></i> Inventory Enhancement</h3>
                    <p>ICT-AMOS streamlines the inventory process for ICT properties. It efficiently tracks and manages all ICT assets, including hardware, software, and network components. By maintaining an accurate and up-to-date inventory, DENR can make informed decisions regarding asset allocation, maintenance, and upgrades.</p>
                </div>
                <div class="feature">
                    <h3><i class="fas fa-rocket"></i> Faster Inventory</h3>
                    <p>Traditional manual inventory processes can be time-consuming and prone to errors. ICT-AMOS automates this task, significantly reducing the time required to catalog and verify assets. Through automated scans, data synchronization, and real-time updates, DENR gains a comprehensive view of its ICT resources without delays.</p>
                </div>
                <div class="feature">
                    <h3><i class="fas fa-wrench"></i> Service Optimization</h3>
                    <p>ICT-AMOS goes beyond inventory management. It also optimizes service delivery and support. When issues arise (such as equipment malfunction or software glitches), ICT-AMOS facilitates efficient troubleshooting, timely repairs, and preventive maintenance. Service requests are logged, tracked, and resolved promptly.</p>
                </div>
                <div class="feature">
                    <h3><i class="fas fa-tasks"></i> Work Optimization</h3>
                    <p>DENR staff benefit from ICT-AMOS by having streamlined workflows. Routine tasks, such as equipment checkouts, software installations, and license renewals, are automated. Workflows are optimized based on predefined rules, ensuring that resources are allocated effectively and tasks are prioritized.</p>
                </div>
            </div>
        </section>

        <section id="logo">
            <h2><i class="fas fa-palette"></i> About the Logo</h2>
            <div class="logo-section">
                <div class="logo-details">
                    <h3><i class="fas fa-paint-brush"></i> Color Scheme</h3>
                    <ul>
                        <li><strong>Blue:</strong> Represents trust, technology, and professionalism, which are crucial for ICT systems.</li>
                        <li><strong>Green:</strong> Symbolizes growth, innovation, and sustainability, aligning with the environmental aspect of DENR.</li>
                        <li><strong>Gray:</strong> Conveys stability and balance, important qualities for asset management and service optimization.</li>
                    </ul>
                </div>
                <div class="logo-details">
                    <h3><i class="fas fa-shapes"></i> Shapes and Symbols</h3>
                    <ul>
                        <li>The square with rounded corners symbol on the right is a universal representation of machinery and systems, indicating the optimization and service aspect.</li>
                        <li>The circuitry lines extending from the gear can symbolize connectivity and ICT, highlighting the technological focus.</li>
                        <li>Geometric shapes and lines (e.g., the blue diamond shape on the left) suggest structure and precision, reinforcing the concept of asset management.</li>
                    </ul>
                </div>
                <div class="logo-details">
                    <h3><i class="fas fa-font"></i> Text and Font</h3>
                    <p>The font choice and arrangement of the text are clear and professional, suitable for a formal and technical system. Emphasizing "ICT" in green and "AMOS" in gray differentiates the key components of the system.</p>
                </div>
                <div class="logo-details">
                    <h3><i class="fas fa-images"></i> Logo Composition</h3>
                    <p>The combination of text and symbols effectively conveys a modern and integrated system. The inclusion of the DENR emblem within the gear signifies the connection to the Department of Environment and Natural Resources, grounding the technological aspects in environmental management.</p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
