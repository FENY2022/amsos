<?php
// Include your existing database connection file
require_once 'connect.php';

// Fetch the necessary columns from the inv_inventory table
$query = "SELECT brand, yearAcquired, rangeCategory, computer_specs, specifications, softwareInstalled, amount, remarks FROM inv_inventory ORDER BY id DESC";
$result = $conn->query($query);

// Calculate Total Records
$total_records = ($result) ? $result->num_rows : 0;
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
        /* Custom Overrides for DataTables to match Tailwind */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            outline: none;
            margin-bottom: 1rem;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #14b8a6;
            box-shadow: 0 0 0 1px #14b8a6;
        }
        table.dataTable thead th, table.dataTable thead td {
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 10px;
        }
        table.dataTable.no-footer {
            border-bottom: 1px solid #e2e8f0;
        }
        .glass-header {
            background: linear-gradient(135deg, #0d9488 0%, #115e59 100%);
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
        
        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            <div class="p-6">
                <table id="inventoryTable" class="w-full text-sm text-left text-gray-500 display responsive nowrap" style="width:100%">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th><i class="fa-solid fa-tag mr-1 text-gray-400"></i> Brand</th>
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
                            while ($row = $result->fetch_assoc()) {
                                // Default placeholders
                                $hdd = '<span class="text-gray-300">-</span>';
                                $ssd = '<span class="text-gray-300">-</span>';
                                $ram = '<span class="text-gray-300">-</span>';
                                $processor = '<span class="text-gray-300">-</span>';
                                $os = !empty($row['softwareInstalled']) ? htmlspecialchars($row['softwareInstalled']) : '<span class="text-gray-300">-</span>';
                                
                                $raw_specs = !empty($row['specifications']) ? $row['specifications'] : $row['computer_specs'];
                                
                                // Intelligent Regex Parsing
                                if (!empty($raw_specs)) {
                                    if (preg_match('/(\d+\s*(?:GB|TB)\s*(?:DDR\d|RAM)?)/i', $raw_specs, $m)) $ram = $m[1];
                                    if (preg_match('/(\d+\s*(?:GB|TB)\s*(?:SSD|NVMe|M\.2))/i', $raw_specs, $m)) $ssd = $m[1];
                                    if (preg_match('/(\d+\s*(?:GB|TB)\s*(?:HDD|Hard Drive))/i', $raw_specs, $m)) $hdd = $m[1];
                                    if (preg_match('/(?:Processor|CPU):\s*([^,;]+)/i', $raw_specs, $m) || preg_match('/(Intel\s+Core\s+i\d+[-\w]+|AMD\s+Ryzen\s+\d+[-\w]+)/i', $raw_specs, $m)) {
                                        $processor = $m[1];
                                    }
                                    if (preg_match('/OS:\s*([^,;]+)/i', $raw_specs, $m)) {
                                        $os = $m[1];
                                    }
                                }

                                // Styling Elements
                                $categoryBadge = "<span class='bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded border border-blue-400'>" . htmlspecialchars($row['rangeCategory']) . "</span>";
                                $osBadge = "<span class='bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-1 rounded'>" . htmlspecialchars(str_replace('<span class="text-gray-300">-</span>', '-', $os)) . "</span>";
                                
                                echo "<tr class='hover:bg-brand-50 transition-colors duration-200 border-b border-gray-50'>";
                                echo "<td class='font-medium text-gray-900'>" . htmlspecialchars($row['brand']) . "</td>";
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
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search inventory...",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries" // Built-in footer counter
                },
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 5 },
                    { responsivePriority: 3, targets: 8 }
                ],
                order: [[ 1, "desc" ]]
            });
        });
    </script>
</body>
</html>