<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Tracking Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-text: #343a40;
            --border-color: #dee2e6;
            --card-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container-fluid {
            padding: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            padding: 25px;
            margin-bottom: 30px; /* Space between cards */
        }

        .card-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Allow wrapping on small screens */
            gap: 15px; /* Space between items in header */
        }

        .card-header h3 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Search Bar Styling */
        .search-container {
            display: flex;
            gap: 10px;
        }

        .search-container .form-control {
            border-radius: 5px;
            border: 1px solid var(--border-color);
            padding: 10px 15px;
            box-shadow: none; /* Remove default bootstrap shadow */
        }

        .search-container .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 5px;
            padding: 10px 20px;
            font-weight: 600;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .search-container .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        /* Table Styling */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0; /* Removed margin-bottom as it's within a card */
        }

        .table thead th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid var(--primary-color);
            font-weight: 600;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table tbody tr:hover {
            background-color: #e9ecef;
        }

        .table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .table tbody td:last-child {
            text-align: center; /* Center align action buttons */
        }

        /* Action buttons in table */
        .btn-action {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            transition: opacity 0.2s ease;
            font-size: 0.9rem;
            margin: 2px; /* Small margin between buttons */
            display: inline-block; /* Ensure buttons sit side-by-side */
        }

        .btn-action:hover {
            opacity: 0.8;
        }

        .btn-view {
            background-color: var(--info-color);
            border: 1px solid var(--info-color);
        }

        .btn-edit {
            background-color: var(--warning-color);
            border: 1px solid var(--warning-color);
            color: var(--dark-text); /* Warning color might need darker text */
        }

        .btn-delete {
            background-color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }

        /* Iframe styling */
        .iframe-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            height: 60vh; /* Set a specific height for the iframe section */
            display: flex;
            flex-direction: column;
        }

        .iframe-container h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--primary-color);
            font-weight: 600;
        }

        iframe {
            flex-grow: 1; /* Make iframe take remaining space */
            border: 1px solid var(--border-color);
            border-radius: 5px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto; /* Make table horizontally scrollable on small screens */
            }
            .search-container {
                flex-direction: column;
                width: 100%; /* Take full width on small screens */
            }
            .search-container .form-control,
            .search-container .btn-primary {
                width: 100%;
            }
            .card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Employee Tracking List</h3>
                    <form method="POST" action="" class="search-container">
                        <input type="text" name="search" class="form-control" placeholder="Search by Full Name">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Office</th>
                                <th>Station</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Include your PHP script here
                            // Make sure this script outputs table rows (<tr>...</tr>)
                            include 'fetch_assigntracking.php';
                            ?>
                            <?php /*
                            <tr>
                                <td>1</td>
                                <td>John Doe</td>
                                <td>Main Office</td>
                                <td>Station A</td>
                                <td>Manager</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-view">View</a>
                                    <a href="#" class="btn btn-sm btn-edit">Edit</a>
                                    <a href="#" class="btn btn-sm btn-delete">Delete</a>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Jane Smith</td>
                                <td>Branch Office</td>
                                <td>Station B</td>
                                <td>Engineer</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-view">View</a>
                                    <a href="#" class="btn btn-sm btn-edit">Edit</a>
                                    <a href="#" class="btn btn-sm btn-delete">Delete</a>
                                </td>
                            </tr>
                            */ ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="iframe-container" style="height: 25%;">
                <h3>Assignment Details</h3>
                <?php
                // Assuming $srfId is defined from your PHP logic
                // You might want to dynamically update this based on table row clicks
                $srfId = isset($srfId) ? $srfId : 'default_id'; // Provide a default or handle if not set
                echo '<iframe src="viewassigntracking.php?id=' . htmlspecialchars($srfId) . '" style="width: 100%; height: 3000px; border: none;"></iframe>';
                ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>