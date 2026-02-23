<?php
// Include your existing database connection file
require_once 'connect.php';

// Fetch the necessary columns from the inv_inventory table
$query = "SELECT brand, yearAcquired, rangeCategory, equipmentType, computer_specs, specifications, softwareInstalled, amount, remarks FROM inv_inventory ORDER BY id DESC";
$result = $conn->query($query);

// Calculate Total Records
$total_records = ($result) ? $result->num_rows : 0;

// Helper to get unique values for dropdowns
function getUniqueValues($conn, $column) {
    $vals = [];
    $res = $conn->query("SELECT DISTINCT $column FROM inv_inventory WHERE $column IS NOT NULL AND $column != '' ORDER BY $column ASC");
    while($row = $res->fetch_assoc()) {
        $vals[] = $row[$column];
    }
    return $vals;
}

$brands = getUniqueValues($conn, 'brand');
$categories = getUniqueValues($conn, 'rangeCategory');
$types = getUniqueValues($conn, 'equipmentType');
$years = getUniqueValues($conn, 'yearAcquired');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Specifications Viewer</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            outline: none;
        }
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1.5rem;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #14b8a6;
            box-shadow: 0 0 0 1px #14b8a6;
        }
        table.dataTable thead th, table.dataTable thead td {
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 10px;
        }
        .glass-header {
            background: linear-gradient(135deg, #0d9488 0%, #115e59 100%);
        }
        /* Custom styling for the filter dropdowns */
        .filter-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen">

    <div class="glass-header text-white py-8 px-6 shadow-lg mb-8">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold flex items-center gap-3">
                    <i class="fa-solid fa-server"></i> Equipment Specifications Viewer
                </h1>
                <p class="mt-2 text-brand-100 text-sm">Comprehensive overview of IT hardware and software assets.</p>
            </div>
            
            <div class="flex gap-4">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-lg px-6 py-3 text-center min-w-[150px]">
                    <span class="block text-brand-100 text-xs uppercase tracking-wider font-semibold">Total Records</span>
                    <span class="block text-3xl font-bold text-white"><?php echo number_format($total_records); ?></span>
                </div>

                <button onclick="window.print()" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg backdrop-blur-sm transition duration-300 flex items-center justify-center gap-2 h-full">
                    <i class="fa-solid fa-print"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- FILTER SECTION -->
        <div class="bg-white p-6 rounded-t-xl border border-gray-100 shadow-sm mb-0">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Brand</label>
                    <select id="filter-brand" class="filter-select w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-brand-500 focus:border-brand-500">
                        <option value="">All Brands</option>
                        <?php foreach($brands as $b) echo "<option value='".htmlspecialchars($b)."'>".htmlspecialchars($b)."</option>"; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Equipment Type</label>
                    <select id="filter-type" class="filter-select w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-brand-500 focus:border-brand-500">
                        <option value="">All Types</option>
                        <?php foreach($types as $t) echo "<option value='".htmlspecialchars($t)."'>".htmlspecialchars($t)."</option>"; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Year</label>
                    <select id="filter-year" class="filter-select w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-brand-500 focus:border-brand-500">
                        <option value="">Any Year</option>
                        <?php foreach($years as $y) echo "<option value='".htmlspecialchars($y)."'>".htmlspecialchars($y)."</option>"; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Category</label>
                    <select id="filter-category" class="filter-select w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-brand-500 focus:border-brand-500">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $c) echo "<option value='".htmlspecialchars($c)."'>".htmlspecialchars($c)."</option>"; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-b-xl shadow-md border border-gray-100 overflow-hidden">
            <div class="p-6">
                <table id="inventoryTable" class="w-full text-sm text-left text-gray-500 display responsive nowrap" style="width:100%">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="w-10"><i class="fa-solid fa-hashtag mr-1 text-gray-400"></i> No.</th>
                            <th><i class="fa-solid fa-tag mr-1 text-gray-400"></i> Brand</th>
                            <th><i class="fa-solid fa-desktop mr-1 text-gray-400"></i> Type</th>
                            <th><i class="fa-regular fa-calendar mr-1 text-gray-400"></i> Year</th>
                            <th><i class="fa-solid fa-layer-group mr-1 text-gray-400"></i> Category</th>
                            <th><i class="fa-solid fa-hard-drive mr-1 text-gray-400"></i> HDD</th>
                            <th><i class="fa-solid fa-memory mr-1 text-gray-400"></i> SSD</th>
                            <th><i class="fa-solid fa-microchip mr-1 text-gray-400"></i> RAM</th>
                            <th><i class="fa-solid fa-brain mr-1 text-gray-400"></i> Processor</th>
                            <th><i class="fa-brands fa-windows mr-1 text-gray-400"></i> OS</th>
                            <th><i class="fa-solid fa-money-bill-wave mr-1 text-gray-400"></i> Amount</th>
                            <th><i class="fa-regular fa-comment-dots mr-1 text-gray-400"></i> Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $result->data_seek(0); // Reset pointer
                            while ($row = $result->fetch_assoc()) {
                                // Default placeholders
                                $hdd = '<span class="text-gray-300">-</span>';
                                $ssd = '<span class="text-gray-300">-</span>';
                                $ram = '<span class="text-gray-300">-</span>';
                                $processor = '<span class="text-gray-300">-</span>';
                                $os = !empty($row['softwareInstalled']) ? htmlspecialchars($row['softwareInstalled']) : '<span class="text-gray-300">-</span>';
                                $equipType = !empty($row['equipmentType']) ? htmlspecialchars($row['equipmentType']) : '<span class="text-gray-300">-</span>';
                                
                                $raw_specs = !empty($row['specifications']) ? $row['specifications'] : $row['computer_specs'];
                                
                                if (!empty($raw_specs)) {
                                    if (preg_match('/(\d+\s*(?:GB|TB)\s*(?:DDR\d|RAM)?)/i', $raw_specs, $m)) $ram = $m[1];
                                    if (preg_match('/(\d+\s*(?:GB|TB)\s*(?:SSD|NVMe|M\.2))/i', $raw_specs, $m)) $ssd = $m[1];
                                    if (preg_match('/(\d+\s*(?:GB|TB)\s*(?:HDD|Hard Drive))/i', $raw_specs, $m)) $hdd = $m[1];
                                    if (preg_match('/(?:Processor|CPU):\s*([^,;]+)/i', $raw_specs, $m) || preg_match('/(Intel\s+Core\s+i\d+[-\w]+|AMD\s+Ryzen\s+\d+[-\w]+)/i', $raw_specs, $m)) {
                                        $processor = $m[1];
                                    }
                                }

                                $categoryBadge = "<span class='bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded border border-blue-400'>" . htmlspecialchars($row['rangeCategory']) . "</span>";
                                $typeBadge = "<span class='bg-gray-100 text-gray-700 text-xs font-semibold px-2 py-1 rounded border border-gray-200'>" . $equipType . "</span>";
                                $osBadge = "<span class='bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-1 rounded'>" . htmlspecialchars(strip_tags($os)) . "</span>";
                                
                                echo "<tr class='hover:bg-brand-50 transition-colors duration-200 border-b border-gray-50'>";
                                echo "<td class='text-center font-medium text-gray-500'></td>"; 
                                echo "<td class='font-medium text-gray-900'>" . htmlspecialchars($row['brand']) . "</td>";
                                echo "<td>" . $typeBadge . "</td>";
                                echo "<td>" . htmlspecialchars($row['yearAcquired']) . "</td>";
                                echo "<td>" . $categoryBadge . "</td>";
                                echo "<td>" . $hdd . "</td>";
                                echo "<td class='font-semibold text-brand-600'>" . $ssd . "</td>";
                                echo "<td class='font-semibold text-purple-600'>" . $ram . "</td>";
                                echo "<td class='truncate max-w-[150px]' title='" . htmlspecialchars($processor) . "'>" . $processor . "</td>";
                                echo "<td>" . $osBadge . "</td>";
                                echo "<td class='text-green-600 font-bold'>₱" . number_format((float)$row['amount'], 2) . "</td>";
                                echo "<td class='truncate max-w-[150px] text-gray-500' title='" . htmlspecialchars($row['remarks']) . "'>" . htmlspecialchars($row['remarks']) . "</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    
    <script>
        $(document).ready(function() {
            var table = $('#inventoryTable').DataTable({
                responsive: true,
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
                dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rtip',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search inventory...",
                },
                columnDefs: [
                    { "orderable": false, "targets": 0 }
                ],
                order: [[ 3, "desc" ]] 
            });

            // Re-calculate "No." sequence
            table.on('order.dt search.dt', function () {
                let i = 1;
                table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
                    this.data(i++);
                });
            }).draw();

            // DROPDOWN FILTER LOGIC
            // Brand is Column 1
            $('#filter-brand').on('change', function() {
                table.column(1).search(this.value).draw();
            });

            // Type is Column 2
            $('#filter-type').on('change', function() {
                table.column(2).search(this.value).draw();
            });

            // Year is Column 3
            $('#filter-year').on('change', function() {
                table.column(3).search(this.value).draw();
            });

            // Category is Column 4
            $('#filter-category').on('change', function() {
                table.column(4).search(this.value).draw();
            });
        });
    </script>
</body>
</html>
