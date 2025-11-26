<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Timeline</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .timeline {
            width: 80%;
            margin: 20px auto;
            position: relative;
            padding: 40px 0;
            background: #e9f8f6;
            border-radius: 8px;
            overflow: hidden; /* Ensure the timeline container clears floats */
        }

        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 4px;
            background: #00a19d;
            z-index: 1;
        }

        .timeline-item {
            position: relative;
            width: 50%;
            padding: 20px;
            box-sizing: border-box;
        }

        .timeline-item.left {
            float: left;
            text-align: right;
            clear: both;
        }

        .timeline-item.right {
            float: right;
            text-align: left;
            clear: both;
        }

        .timeline-item:after {
            content: '';
            display: table;
            clear: both;
        }

        .timeline-item h3 {
            margin: 0;
            font-size: 18px;
            color: #00a19d;
        }

        .timeline-item p {
            margin: 10px 0 0;
            font-size: 14px;
            color: #333;
        }

        .timeline-item .icon {
            width: 50px;
            height: 50px;
            background: #00a19d;
            border-radius: 50%;
            position: absolute;
            top: 20px;
            z-index: 2;
        }

        .timeline-item.left .icon {
            right: -25px;
        }

        .timeline-item.right .icon {
            left: -25px;
        }

        .timeline-item .icon img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
        }

        @media (max-width: 768px) {
            .timeline-item {
                width: 100%;
                text-align: left;
            }

            .timeline-item.left, .timeline-item.right {
                float: none;
                clear: none;
            }

            .timeline-item.left .icon, .timeline-item.right .icon {
                left: 10px;
                right: auto;
            }

            .timeline:before {
                left: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="timeline">
        <!-- Week 1 -->
        <div class="timeline-item left">
            <div class="icon">
                <img src="icon1.png" alt="Consult Icon">
            </div>
            <h3>Week 1 - Initial Consult</h3>
            <p>Review terms and sign contract with client. Present project timeline and clarify scope.</p>
        </div>
        <!-- Week 2 -->
        <div class="timeline-item right">
            <div class="icon">
                <img src="icon2.png" alt="Design Inquiry Icon">
            </div>
            <h3>Week 2 - Design Inquiry</h3>
            <p>Meeting at project site. Discuss preferred styles, aesthetics, and needs. Photograph and measure.</p>
        </div>
        <!-- Week 3 -->
        <div class="timeline-item left">
            <div class="icon">
                <img src="icon3.png" alt="Design Concept Icon">
            </div>
            <h3>Week 3 - Initial Design Concept</h3>
            <p>Meeting at studio to present layout/space plans, colors, fabrics, and furnishings.</p>
        </div>
        <!-- Week 4 -->
        <div class="timeline-item right">
            <div class="icon">
                <img src="icon4.png" alt="Second Concept Icon">
            </div>
            <h3>Week 4 - Second Design Concept</h3>
            <p>Finalize materials and layout. Review proposals with pricing.</p>
        </div>
        <!-- Week 5 -->
        <div class="timeline-item left">
            <div class="icon">
                <img src="icon5.png" alt="Send Design Icon">
            </div>
            <h3>Week 5 - Send Out Design Concept</h3>
            <p>Use platforms to release bids. Revisit design and re-issue bids if necessary.</p>
        </div>
        <!-- Week 6 -->
        <div class="timeline-item right">
            <div class="icon">
                <img src="icon6.png" alt="Sales Icon">
            </div>
            <h3>Week 6 - Finalize Sales Presentation</h3>
            <p>Determine deposit amount and finalize contractor details.</p>
        </div>
        <!-- Week 7 -->
        <div class="timeline-item left">
            <div class="icon">
                <img src="icon7.png" alt="Materials Icon">
            </div>
            <h3>Week 7 - Purchasing of Materials</h3>
            <p>Purchase and drop off materials on-site. Notify contractor.</p>
        </div>
        <!-- Week 8 -->
        <div class="timeline-item right">
            <div class="icon">
                <img src="icon8.png" alt="Installation Icon">
            </div>
            <h3>Week 8 - Start Initial Installation</h3>
            <p>Installation begins based on project scope.</p>
        </div>
        <!-- Week 9 -->
        <div class="timeline-item left">
            <div class="icon">
                <img src="icon9.png" alt="Review Icon">
            </div>
            <h3>Week 9 - Review</h3>
            <p>Project completed. Present warranty and review details with client.</p>
        </div>
    </div>
</body>
</html>
