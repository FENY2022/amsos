<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Search System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #4cc9f0;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .logo i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-right: 10px;
        }

        h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .subtitle {
            color: var(--gray);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
            transition: var(--transition);
        }

        .search-container:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        input[type="text"] {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
        }

        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        button[type="submit"] {
            padding: 15px 30px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        button[type="submit"]:hover {
            background: linear-gradient(135deg, var(--primary-dark), #651a98);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 14px;
            background-color: white;
        }

        .results-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .results-header {
            padding: 20px;
            background: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .results-count {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .export-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .export-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .table-container {
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: var(--light-gray);
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            position: sticky;
            top: 0;
            border-bottom: 2px solid var(--light-gray);
        }

        td {
            padding: 12px;
            border-bottom: 1px solid var(--light-gray);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 250px;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .no-results {
            padding: 40px;
            text-align: center;
            color: var(--gray);
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--light-gray);
        }

        footer {
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 30px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }

        .pagination button {
            padding: 8px 15px;
            border: 1px solid var(--light-gray);
            background: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .pagination button.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination button:hover:not(.active) {
            background: var(--light-gray);
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .filters {
                flex-direction: column;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-boxes"></i>
                <h1>Inventory Search System</h1>
            </div>
            <p class="subtitle">Search and manage your inventory efficiently with our advanced search capabilities</p>
        </header>

        <div class="search-container">
            <form class="search-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="query" placeholder="Search by employee name, equipment type, serial number..." required value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
                </div>
                <button type="submit">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
            
            <div class="filters">
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select id="category">
                        <option value="">All Categories</option>
                        <option value="hardware">Hardware</option>
                        <option value="software">Software</option>
                        <option value="equipment">Equipment</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="office">Office/Division</label>
                    <select id="office">
                        <option value="">All Offices</option>
                        <option value="hr">Human Resources</option>
                        <option value="it">IT Department</option>
                        <option value="finance">Finance</option>
                    </select>
                </div>
            </div>
        </div>
        
        <?php
        // Database connection details
        require_once 'connect.php';
        // Check if a search query was submitted
        if (isset($_GET['query'])) {
            $search_query = $_GET['query'];

            // Define the columns you want to search through.
            $columns_to_search = [
                'employeeName',
                'equipmentType',
                'yearAcquired',
                'shelfLife',
                'brand',
                'specifications',
                'rangeCategory',
                'softwareInstalled',
                'licensingModel',
                'serialNumber',
                'propertyNumber',
                'accountablePerson',
                'sex',
                'officeDivision',
                'statusOfEmployment',
                'actualUser',
                'actualUserSex',
                'actualUserStatusOfEmployment',
                'natureOfWork',
                'remarks',
                'office'
            ];

            $sql_parts = [];
            $types = '';
            $params = [];

            // Build the SQL query dynamically
            $sql_template = "SELECT DISTINCT * FROM inv_inventory WHERE ";
            $conditions = [];
            foreach ($columns_to_search as $column) {
                $conditions[] = "`" . $column . "` LIKE ?";
                $types .= 's';
                $params[] = "%" . $search_query . "%";
            }
            $sql_template .= implode(" OR ", $conditions);
            $sql_template .= " ORDER BY id";

            // Prepare the statement to prevent SQL injection
            $stmt = $conn->prepare($sql_template);

            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }

            // Dynamically bind parameters
            $stmt->bind_param($types, ...$params);

            // Execute the statement
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Start the results display section
            echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" class="back-link"><i class="fas fa-arrow-left"></i> New Search</a>';
            echo '<div class="results-container">';
            echo '<div class="results-header">';
            echo '<div class="results-count">' . $result->num_rows . ' Results found for "' . htmlspecialchars($search_query) . '"</div>';
            echo '<button class="export-btn"><i class="fas fa-download"></i> Export Results</button>';
            echo '</div>';

            if ($result->num_rows > 0) {
                echo '<div class="table-container">';
                echo '<table>';
                echo '<thead><tr>';
                while ($fieldinfo = $result->fetch_field()) {
                    echo '<th>' . htmlspecialchars(ucwords(preg_replace('/(?<!\ )[A-Z]/', ' $0', $fieldinfo->name))) . '</th>';
                }
                echo '</tr></thead>';
                
                echo '<tbody>';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    foreach ($row as $data) {
                        echo '<td>' . htmlspecialchars($data) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            } else {
                echo '<div class="no-results">';
                echo '<i class="fas fa-search"></i>';
                echo '<h3>No results found</h3>';
                echo '<p>Try adjusting your search terms or filters</p>';
                echo '</div>';
            }

            echo '</div>';

            $stmt->close();
        }

        $conn->close();
        ?>

        <footer>
            <p>Inventory Search System &copy; 2023 | AMSOS Department</p>
        </footer>
    </div>
    
    <script>
        // Simple animation for the search container
        document.addEventListener('DOMContentLoaded', function() {
            const searchContainer = document.querySelector('.search-container');
            searchContainer.style.opacity = '0';
            searchContainer.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                searchContainer.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                searchContainer.style.opacity = '1';
                searchContainer.style.transform = 'translateY(0)';
            }, 100);
            
            // Export button functionality
            const exportBtn = document.querySelector('.export-btn');
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    alert('Export functionality would be implemented here. This could export to CSV, PDF, or Excel format.');
                });
            }
            
            // Filter functionality (basic implementation)
            const filters = document.querySelectorAll('.filter-group select');
            filters.forEach(filter => {
                filter.addEventListener('change', function() {
                    // In a real implementation, this would update the search results
                    console.log('Filter changed:', this.id, this.value);
                });
            });
        });
    </script>
</body>
</html>