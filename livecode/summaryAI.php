<?php
// PHP code for database connection and data retrieval (UNCHANGED)

require_once 'connect.php'; // Include your database connection file

// Pagination settings
$itemsPerPage = 10;
$page = $_GET['page'] ?? 1; // Get current page, default to 1
$offset = ($page - 1) * $itemsPerPage;

// Build the base SQL query with optional filters
$sql = "SELECT * FROM inv_inventory WHERE 1=1";
$equipmentTypeFilter = $_GET['equipmentType'] ?? '';
$brandFilter = $_GET['brand'] ?? '';
$employeeNameFilter = $_GET['employeeName'] ?? '';
$shelfLifeFilter = $_GET['shelfLife'] ?? '';
$officeDivisionFilter = $_GET['officeDivision'] ?? '';
$serialNumberFilter = $_GET['serialNumber'] ?? '';

if (!empty($equipmentTypeFilter)) {
    $sql .= " AND equipmentType = '" . $conn->real_escape_string($equipmentTypeFilter) . "'";
}

if (!empty($brandFilter)) {
    $sql .= " AND brand = '" . $conn->real_escape_string($brandFilter) . "'";
}

if (!empty($employeeNameFilter)) {
    $sql .= " AND employeeName = '" . $conn->real_escape_string($employeeNameFilter) . "'";
}

if (!empty($shelfLifeFilter)) {
    $sql .= " AND shelfLife = '" . $conn->real_escape_string($shelfLifeFilter) . "'";
}

if (!empty($officeDivisionFilter)) {
    $sql .= " AND officeDivision = '" . $conn->real_escape_string($officeDivisionFilter) . "'";
}

if (!empty($serialNumberFilter)) {
    $sql .= " AND serialNumber = '" . $conn->real_escape_string($serialNumberFilter) . "'";
}

// Get the total number of records for pagination
$countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$countResult = $conn->query($countSql);
$totalRecords = $countResult->fetch_row()[0];
$totalPages = ceil($totalRecords / $itemsPerPage);

// Add LIMIT and OFFSET to the main query
$sql .= " LIMIT $itemsPerPage OFFSET $offset";
$result = $conn->query($sql);

// Prepare data for the bar chart (counts of each equipmentType), applying the same filters as the table
$chartDataSql = "SELECT equipmentType, COUNT(*) as count FROM inv_inventory WHERE 1=1";
if (!empty($equipmentTypeFilter)) {
    $chartDataSql .= " AND equipmentType = '" . $conn->real_escape_string($equipmentTypeFilter) . "'";
}
if (!empty($brandFilter)) {
    $chartDataSql .= " AND brand = '" . $conn->real_escape_string($brandFilter) . "'";
}
if (!empty($employeeNameFilter)) {
    $chartDataSql .= " AND employeeName = '" . $conn->real_escape_string($employeeNameFilter) . "'";
}
if (!empty($shelfLifeFilter)) {
    $chartDataSql .= " AND shelfLife = '" . $conn->real_escape_string($shelfLifeFilter) . "'";
}
if (!empty($officeDivisionFilter)) {
    $chartDataSql .= " AND officeDivision = '" . $conn->real_escape_string($officeDivisionFilter) . "'";
}
if (!empty($serialNumberFilter)) {
    $chartDataSql .= " AND serialNumber = '" . $conn->real_escape_string($serialNumberFilter) . "'";
}
$chartDataSql .= " GROUP BY equipmentType ORDER BY count DESC";

$chartResult = $conn->query($chartDataSql);
$chartData = [];
$chartLabels = [];
while($row = $chartResult->fetch_assoc()) {
    $chartLabels[] = ucwords($row['equipmentType']);
    $chartData[] = $row['count'];
}

// Fetch all distinct employee names for the combobox
$distinctEmployeeSql = "SELECT DISTINCT employeeName FROM inv_inventory WHERE employeeName != '' ORDER BY employeeName";
$distinctEmployeeResult = $conn->query($distinctEmployeeSql);
$employeeNames = [];
while ($row = $distinctEmployeeResult->fetch_assoc()) {
    // Apply proper casing to the employee names
    $employeeNames[] = ucwords($row['employeeName']);
}
$jsEmployeeNames = json_encode($employeeNames);

// Prepare the PHP data for JavaScript
$jsChartLabels = json_encode($chartLabels);
$jsChartData = json_encode($chartData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AMSOS Inventory Dashboard</title>
    <link rel="icon" type="image/ico" href="icon/amsos.ico">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #4F46E5;
            --primary-light: #6366F1;
            --primary-dark: #4338CA;
            --secondary: #10B981;
            --dark: #1F2937;
            --light: #F9FAFB;
            --gray: #6B7280;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7ec 100%);
            min-height: 100vh;
        }
        
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }
        
        .filter-input {
            border-radius: 50px;
            padding: 10px 20px;
            border: 1px solid #E5E7EB;
            transition: all 0.3s ease;
        }
        
        .filter-input:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        
        .table-row:hover {
            background-color: #F9FAFB;
        }
        
        .pagination-btn {
            border-radius: 10px;
            padding: 8px 16px;
            transition: all 0.2s ease;
        }
        
        .pagination-btn:hover {
            background-color: var(--primary-light);
            color: white;
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-radius: 16px;
            padding: 20px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: white;
            margin: auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        
        .ai-chatbot {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            overflow: hidden;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Animation for cards */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c5c5c5;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="card p-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800"><i class="fas fa-laptop-house mr-3 text-indigo-600"></i>IT Inventory Dashboard</h1>
                    <p class="text-gray-600 mt-2">Track and manage all IT equipment in your organization</p>
                </div>
                <div class="mt-4 md:mt-0 flex items-center space-x-4">
                    <div class="hidden md:block">
                        <div class="text-sm text-gray-500">Total Items</div>
                        <div class="text-2xl font-bold text-indigo-600"><?php echo $totalRecords; ?></div>
                    </div>
                    <button class="btn-primary flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Item
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="stats-card animate-fade-in" style="animation-delay: 0.1s">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm opacity-80">Equipment Types</div>
                        <div class="text-2xl font-bold mt-1"><?php echo count($chartLabels); ?></div>
                    </div>
                    <i class="fas fa-desktop text-3xl opacity-80"></i>
                </div>
            </div>
            
            <div class="stats-card animate-fade-in" style="animation-delay: 0.2s; background: linear-gradient(135deg, #10B981 0%, #34D399 100%);">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm opacity-80">Total Items</div>
                        <div class="text-2xl font-bold mt-1"><?php echo $totalRecords; ?></div>
                    </div>
                    <i class="fas fa-check-circle text-3xl opacity-80"></i>
                </div>
            </div>
            
            <div class="stats-card animate-fade-in" style="animation-delay: 0.3s; background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm opacity-80">Maintenance</div>
                        <div class="text-2xl font-bold mt-1">8</div>
                    </div>
                    <i class="fas fa-tools text-3xl opacity-80"></i>
                </div>
            </div>
            
            <div class="stats-card animate-fade-in" style="animation-delay: 0.4s; background: linear-gradient(135deg, #EF4444 0%, #F87171 100%);">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm opacity-80">End of Life</div>
                        <div class="text-2xl font-bold mt-1">5</div>
                    </div>
                    <i class="fas fa-exclamation-triangle text-3xl opacity-80"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">Equipment Distribution</h2>
                        <div class="flex space-x-2">
                            <button class="text-sm text-gray-500 hover:text-indigo-600">Week</button>
                            <button class="text-sm text-gray-500 hover:text-indigo-600">Month</button>
                            <button class="text-sm font-medium text-indigo-600">Year</button>
                        </div>
                    </div>
                    <div class="w-full h-80">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
                
                <div class="ai-chatbot p-6 card">
                    <div class="flex items-center">
                        <div class="mr-4">
                            <i class="fas fa-robot text-4xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold">AI Inventory Assistant</h3>
                            <p class="opacity-90 mt-1">Get insights and answers about your inventory</p>
                        </div>
                    </div>
                    <button id="open-chatbot-btn" class="mt-6 w-full bg-white text-indigo-600 py-3 rounded-lg font-medium hover:bg-gray-100 transition duration-200">
                        Ask a Question
                    </button>
                </div>
            </div>
            
            <div class="space-y-6">
                <div class="card p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Filters</h2>
                    <form id="filterForm" method="GET" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Equipment Type</label>
                            <select name="equipmentType" class="w-full filter-input">
                                <option value="">All Types</option>
                                <?php
                                $distinctSql = "SELECT DISTINCT equipmentType FROM inv_inventory WHERE equipmentType != '' ORDER BY equipmentType";
                                $distinctResult = $conn->query($distinctSql);
                                while ($row = $distinctResult->fetch_assoc()) {
                                    $selected = ($row['equipmentType'] == $equipmentTypeFilter) ? 'selected' : '';
                                    echo "<option value='{$row['equipmentType']}' {$selected}>" . ucwords($row['equipmentType']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                            <select name="brand" class="w-full filter-input">
                                <option value="">All Brands</option>
                                <?php
                                $distinctBrandSql = "SELECT DISTINCT brand FROM inv_inventory WHERE brand != '' ORDER BY brand";
                                $distinctBrandResult = $conn->query($distinctBrandSql);
                                while ($row = $distinctBrandResult->fetch_assoc()) {
                                    $selected = ($row['brand'] == $brandFilter) ? 'selected' : '';
                                    echo "<option value='{$row['brand']}' {$selected}>" . ucwords($row['brand']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="employeeNameSearch"
                                    placeholder="Search employees..."
                                    value="<?php echo htmlspecialchars($employeeNameFilter); ?>"
                                    class="w-full filter-input"
                                >
                                <input type="hidden" name="employeeName" id="employeeNameHidden" value="<?php echo htmlspecialchars($employeeNameFilter); ?>">
                                <ul id="employeeNameList" class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden"></ul>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Office Division</label>
                            <select name="officeDivision" class="w-full filter-input">
                                <option value="">All Divisions</option>
                                <?php
                                $distinctOfficeDivisionSql = "SELECT DISTINCT officeDivision FROM inv_inventory WHERE officeDivision != '' ORDER BY officeDivision";
                                $distinctOfficeDivisionResult = $conn->query($distinctOfficeDivisionSql);
                                while ($row = $distinctOfficeDivisionResult->fetch_assoc()) {
                                    $selected = ($row['officeDivision'] == $officeDivisionFilter) ? 'selected' : '';
                                    echo "<option value='{$row['officeDivision']}' {$selected}>" . ucwords($row['officeDivision']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Barcode/Serial No.</label>
                            <input
                                type="text"
                                name="serialNumber"
                                id="barcodeInput"
                                placeholder="Scan barcode..."
                                value="<?php echo htmlspecialchars($serialNumberFilter); ?>"
                                class="w-full filter-input"
                            >
                        </div>
                        
                        <div class="flex space-x-3 pt-2">
                            <button type="submit" class="flex-1 btn-primary">
                                <i class="fas fa-filter mr-2"></i> Apply Filters
                            </button>
                            <button type="button" id="resetBtn" class="px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                                <i class="fas fa-redo"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="card p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-2 gap-3">
                        <button class="py-3 bg-indigo-50 text-indigo-600 rounded-lg flex flex-col items-center justify-center hover:bg-indigo-100 transition duration-200">
                            <i class="fas fa-file-export text-lg mb-1"></i>
                            <span class="text-sm">Export</span>
                        </button>
                        <button class="py-3 bg-green-50 text-green-600 rounded-lg flex flex-col items-center justify-center hover:bg-green-100 transition duration-200">
                            <i class="fas fa-print text-lg mb-1"></i>
                            <span class="text-sm">Print</span>
                        </button>
                        <button class="py-3 bg-blue-50 text-blue-600 rounded-lg flex flex-col items-center justify-center hover:bg-blue-100 transition duration-200">
                            <i class="fas fa-sync text-lg mb-1"></i>
                            <span class="text-sm">Refresh</span>
                        </button>
                        <button class="py-3 bg-purple-50 text-purple-600 rounded-lg flex flex-col items-center justify-center hover:bg-purple-100 transition duration-200">
                            <i class="fas fa-chart-pie text-lg mb-1"></i>
                            <span class="text-sm">Reports</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800">Inventory Items</h2>
                <p class="text-gray-600 text-sm mt-1"><?php echo $totalRecords; ?> items found</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand/Model</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acquired</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Division</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        if ($result->num_rows > 0) {
                            $result->data_seek(0);
                            while($row = $result->fetch_assoc()) {
                                echo "<tr class='table-row'>";
                                echo "<td class='px-6 py-4 whitespace-nowrap'>
                                        <div class='flex items-center'>
                                            <div class='h-10 w-10 flex-shrink-0 bg-indigo-100 rounded-full flex items-center justify-center'>
                                                <i class='fas fa-user text-indigo-600'></i>
                                            </div>
                                            <div class='ml-4'>
                                                <div class='font-medium text-gray-900'>" . ucwords($row['employeeName']) . "</div>
                                            </div>
                                        </div>
                                    </td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap'>
                                        <div class='text-sm font-medium text-gray-900'>" . ucwords($row['equipmentType']) . "</div>
                                        <div class='text-sm text-gray-500'>SN: " . $row['serialNumber'] . "</div>
                                    </td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap'>
                                        <div class='text-sm text-gray-900'>" . ucwords($row['brand']) . "</div>
                                        <div class='text-sm text-gray-500'>" . ($row['model'] ?? 'N/A') . "</div>
                                    </td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $row['yearAcquired'] . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . ucwords($row['officeDivision']) . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium'>₱" . number_format($row['amount'], 2) . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium'>
                                        <button class='text-indigo-600 hover:text-indigo-900 mr-3'><i class='fas fa-edit'></i></button>
                                        <button class='text-red-600 hover:text-red-900'><i class='fas fa-trash'></i></button>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='px-6 py-4 text-center text-gray-500'>No inventory items found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium"><?php echo ($offset + 1); ?></span> to <span class="font-medium"><?php echo min($offset + $itemsPerPage, $totalRecords); ?></span> of <span class="font-medium"><?php echo $totalRecords; ?></span> results
                </div>
                <div class="flex space-x-2">
                    <?php
                    $queryString = http_build_query(array_filter(
                        [
                            'equipmentType' => $equipmentTypeFilter,
                            'brand' => $brandFilter,
                            'employeeName' => $employeeNameFilter,
                            'shelfLife' => $shelfLifeFilter,
                            'officeDivision' => $officeDivisionFilter,
                            'serialNumber' => $serialNumberFilter
                        ]
                    ));
                    $prevPage = max(1, $page - 1);
                    $nextPage = min($totalPages, $page + 1);
                    ?>
                    <a href="?page=<?php echo $prevPage; ?>&<?php echo $queryString; ?>"
                        class="pagination-btn border border-gray-300 <?php echo $page <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    
                    <?php 
                    // Show limited pagination buttons
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++): 
                    ?>
                        <a href="?page=<?php echo $i; ?>&<?php echo $queryString; ?>"
                            class="pagination-btn <?php echo $i == $page ? 'bg-indigo-600 text-white' : 'border border-gray-300'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <a href="?page=<?php echo $nextPage; ?>&<?php echo $queryString; ?>"
                        class="pagination-btn border border-gray-300 <?php echo $page >= $totalPages ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="chatbot-modal" class="modal">
        <div class="modal-content">
            <div class="gradient-bg p-4 text-white flex justify-between items-center">
                <h3 class="text-xl font-semibold"><i class="fas fa-robot mr-2"></i> AI Inventory Assistant</h3>
                <button id="close-modal-btn" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <iframe src="chatbot.php" class="w-full h-[500px] border-0"></iframe>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- ADDED: Restore scroll position on page load ---
            const scrollPosition = sessionStorage.getItem('scrollPosition');
            if (scrollPosition) {
                window.scrollTo(0, parseInt(scrollPosition, 10));
            }

            // --- ADDED: Save scroll position on scroll ---
            window.addEventListener('scroll', function() {
                // Use a timeout to avoid saving on every single scroll event, which can be inefficient
                setTimeout(() => {
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                }, 100); 
            });

            // Chart initialization
            const labels = <?php echo $jsChartLabels; ?>;
            const data = <?php echo $jsChartData; ?>;
            
            const ctx = document.getElementById('barChart').getContext('2d');
            const barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Equipment Count',
                        data: data,
                        backgroundColor: 'rgba(79, 70, 229, 0.7)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        hoverBackgroundColor: 'rgba(79, 70, 229, 0.9)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#1F2937',
                            bodyColor: '#4B5563',
                            borderColor: '#E5E7EB',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return `Count: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Combobox logic for employeeName (UNCHANGED)
            const employeeNames = <?php echo $jsEmployeeNames; ?>;
            const searchInput = document.getElementById('employeeNameSearch');
            const resultsList = document.getElementById('employeeNameList');
            const hiddenInput = document.getElementById('employeeNameHidden');
            const filterForm = document.getElementById('filterForm');

            function populateList(names) {
                resultsList.innerHTML = '';
                const allLi = document.createElement('li');
                allLi.textContent = 'All Employees';
                allLi.classList.add('p-3', 'cursor-pointer', 'hover:bg-indigo-50', 'font-medium', 'text-indigo-600', 'border-b', 'border-gray-100');
                allLi.addEventListener('click', () => {
                    searchInput.value = '';
                    hiddenInput.value = '';
                    resultsList.classList.add('hidden');
                    filterForm.submit();
                });
                resultsList.appendChild(allLi);

                names.forEach(name => {
                    const li = document.createElement('li');
                    li.textContent = name;
                    li.classList.add('p-3', 'cursor-pointer', 'hover:bg-indigo-50', 'text-gray-700');
                    li.addEventListener('click', () => {
                        searchInput.value = name;
                        hiddenInput.value = name;
                        resultsList.classList.add('hidden');
                        filterForm.submit();
                    });
                    resultsList.appendChild(li);
                });
                resultsList.classList.remove('hidden');
            }

            searchInput.addEventListener('input', () => {
                const searchTerm = searchInput.value.toLowerCase();
                const filteredNames = employeeNames.filter(name => name.toLowerCase().includes(searchTerm));
                
                if (searchTerm === '') {
                    populateList(employeeNames);
                    hiddenInput.value = '';
                } else if (filteredNames.length > 0) {
                    populateList(filteredNames);
                } else {
                    resultsList.classList.add('hidden');
                }
            });

            searchInput.addEventListener('blur', () => {
                setTimeout(() => {
                    resultsList.classList.add('hidden');
                }, 200);
            });

            searchInput.addEventListener('focus', () => {
                populateList(employeeNames);
            });
            
            // Handle barcode input enter key (UNCHANGED)
            const barcodeInput = document.getElementById('barcodeInput');
            barcodeInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterForm.submit();
                }
            });

            // --- MODIFIED: Handle reset button click ---
            document.getElementById('resetBtn').addEventListener('click', () => {
                // Clear the saved scroll position before resetting the page
                sessionStorage.removeItem('scrollPosition');
                window.location.href = window.location.pathname;
            });

            // Modal logic for chatbot (UNCHANGED)
            const chatbotModal = document.getElementById('chatbot-modal');
            const openChatbotBtn = document.getElementById('open-chatbot-btn');
            const closeModalBtn = document.getElementById('close-modal-btn');

            openChatbotBtn.addEventListener('click', () => {
                chatbotModal.style.display = 'flex';
            });

            closeModalBtn.addEventListener('click', () => {
                chatbotModal.style.display = 'none';
            });
            
            window.addEventListener('click', (event) => {
                if (event.target == chatbotModal) {
                    chatbotModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>