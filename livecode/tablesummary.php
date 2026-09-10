<?php
// index.php

// Include the database connection file
require_once 'connect.php';


// --- (A) Get "Needed" Quantities from the inv_typeofequipment table ---
$neededQuantities = [];
$neededResult = $conn->query("SELECT equipment_name, Needed FROM inv_typeofequipment");
if ($neededResult) {
    while ($row = $neededResult->fetch_assoc()) {
        $neededQuantities[$row['equipment_name']] = (int)$row['Needed'];
    }
}


// --- (B) Get the selected year from the filter ---
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');


// --- (C) Fetch Data from the Database ---

// 1. Get all distinct years for the dropdown filter
$yearsResult = $conn->query("SELECT DISTINCT yearAcquired FROM inv_inventory ORDER BY yearAcquired DESC");

// 2. Get total inventoried items up to the selected year
$stmtTotalInventoried = $conn->prepare("SELECT COUNT(id) as total FROM inv_inventory WHERE yearAcquired <= ?");
$stmtTotalInventoried->bind_param("s", $selectedYear);
$stmtTotalInventoried->execute();
$totalInventoried = $stmtTotalInventoried->get_result()->fetch_assoc()['total'] ?? 0;
$stmtTotalInventoried->close();


// 3. Get total procured for the selected year
$stmtProcuredCY = $conn->prepare("SELECT COUNT(id) as total FROM inv_inventory WHERE yearAcquired = ?");
$stmtProcuredCY->bind_param("s", $selectedYear);
$stmtProcuredCY->execute();
$totalProcuredCY = $stmtProcuredCY->get_result()->fetch_assoc()['total'] ?? 0;
$stmtProcuredCY->close();


// 4. Get the breakdown by equipment type
$allEquipmentTypes = [];
$allTypesResult = $conn->query("SELECT DISTINCT equipmentType FROM inv_inventory WHERE equipmentType IS NOT NULL AND equipmentType != '' ORDER BY equipmentType ASC");
if ($allTypesResult) {
    while ($row = $allTypesResult->fetch_assoc()) {
        $allEquipmentTypes[$row['equipmentType']] = [
            'inventoryCount' => 0,
            'procuredCY' => 0
        ];
    }
}

// Get inventory counts UP TO the selected year
$stmtInventoryBreakdown = $conn->prepare("SELECT equipmentType, COUNT(id) as inventoryCount FROM inv_inventory WHERE equipmentType IS NOT NULL AND equipmentType != '' AND yearAcquired <= ? GROUP BY equipmentType");
$stmtInventoryBreakdown->bind_param("s", $selectedYear);
$stmtInventoryBreakdown->execute();
$inventoryBreakdownResult = $stmtInventoryBreakdown->get_result();
if ($inventoryBreakdownResult) {
    while ($row = $inventoryBreakdownResult->fetch_assoc()) {
        if (isset($allEquipmentTypes[$row['equipmentType']])) {
            $allEquipmentTypes[$row['equipmentType']]['inventoryCount'] = $row['inventoryCount'];
        }
    }
}
$stmtInventoryBreakdown->close();


// 5. Get the breakdown of procured items for the selected year
$stmtProcuredBreakdown = $conn->prepare("SELECT equipmentType, COUNT(id) as procuredCount FROM inv_inventory WHERE yearAcquired = ? GROUP BY equipmentType");
$stmtProcuredBreakdown->bind_param("s", $selectedYear);
$stmtProcuredBreakdown->execute();
$procuredBreakdownResult = $stmtProcuredBreakdown->get_result();
if ($procuredBreakdownResult) {
    while ($row = $procuredBreakdownResult->fetch_assoc()) {
        if (isset($allEquipmentTypes[$row['equipmentType']])) {
            $allEquipmentTypes[$row['equipmentType']]['procuredCY'] = $row['procuredCount'];
        }
    }
}
$stmtProcuredBreakdown->close();

// --- (D) Calculate Totals for New UI Cards & Footer ---
$totalNeeded = array_sum($neededQuantities);
$totalRemaining = 0; // This will be calculated in the table loop below

// Close the database connection for now, as we have all the data we need.
$conn->close();

// --- (E) Handle Excel Export Request ---
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $filename = "ICT_Inventory_Summary_" . $selectedYear . ".csv";
    
    // Set HTTP headers to force the browser to download the file
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open the output stream
    $output = fopen('php://output', 'w');
    
    // Write the CSV header row
    fputcsv($output, [
        'ICT Equipment', 
        'Inventory Count (End of ' . $selectedYear . ')', 
        'Needed', 
        'Procured CY ' . $selectedYear,
        'Procurement Progress (%)',
        'Remaining'
    ]);
    
    $totalRemainingForCsv = 0; // Initialize a counter for the total remaining items
    
    // Loop through the equipment data and write each row to the CSV
    foreach ($allEquipmentTypes as $name => $data) {
        $inventoryCount = $data['inventoryCount'];
        $needed = $neededQuantities[$name] ?? 0;
        $procured = $data['procuredCY'];
        $remaining = max(0, $needed - $inventoryCount);
        // MODIFIED: Cap progress at 100
        $progress = ($needed > 0) ? min(100, round(($inventoryCount / $needed) * 100, 1)) : 0;
        
        $totalRemainingForCsv += $remaining; // Accumulate the total remaining

        fputcsv($output, [$name, $inventoryCount, $needed, $procured, $progress, $remaining]);
    }

    // MODIFIED: Cap total progress at 100
    $totalProgress = $totalNeeded > 0 ? min(100, round(($totalInventoried / $totalNeeded) * 100, 1)) : 0;

    // Write the final totals row to the CSV
    fputcsv($output, [
        'TOTAL',
        $totalInventoried,
        $totalNeeded,
        $totalProcuredCY,
        $totalProgress,
        $totalRemainingForCsv
    ]);
    
    // Close the output stream and stop the script
    fclose($output);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Equipment Inventory Summary</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <script>
        // Custom Tailwind theme configuration
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#64748b',
                        success: '#22c55e',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        info: '#3b82f6',
                        light: '#f8fafc',
                        dark: '#1e293b'
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom styles for progress bar transitions and responsive table */
        .progress-bar {
            transition: width 0.5s ease-in-out;
        }
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }
            .equipment-name {
                min-width: 150px;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">
    <div class="container mx-auto px-4 py-8 max-w-7xl">

        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">ICT Equipment Procurement Summary</h1>
                    <p class="text-primary font-semibold">CY <?php echo htmlspecialchars($selectedYear); ?></p>
                </div>
                
                <form method="get" action="" class="flex items-center">
                    <label for="year" class="mr-2 font-medium text-gray-700">Filter by Year:</label>
                    <div class="relative">
                        <select name="year" id="year" onchange="this.form.submit()" class="appearance-none block w-full py-2 pl-3 pr-10 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary rounded-lg shadow-sm">
                            <?php 
                                // Reset pointer of yearsResult if it was used before
                                if ($yearsResult) $yearsResult->data_seek(0); 
                            ?>
                            <?php if ($yearsResult): ?>
                                <?php while($yearRow = $yearsResult->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($yearRow['yearAcquired']); ?>" <?php if ($yearRow['yearAcquired'] == $selectedYear) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($yearRow['yearAcquired']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100"><div class="flex justify-between items-start"><div><p class="text-sm text-blue-600 font-medium">Total Inventoried</p><h3 class="text-2xl font-bold text-blue-800 mt-1"><?php echo $totalInventoried; ?></h3></div><div class="bg-blue-100 p-2 rounded-full"><i class="fas fa-boxes text-blue-600"></i></div></div></div>
                <div class="bg-green-50 p-4 rounded-lg border border-green-100"><div class="flex justify-between items-start"><div><p class="text-sm text-green-600 font-medium">Total Procured (CY)</p><h3 class="text-2xl font-bold text-green-800 mt-1"><?php echo $totalProcuredCY; ?></h3></div><div class="bg-green-100 p-2 rounded-full"><i class="fas fa-shipping-fast text-green-600"></i></div></div></div>
                <div class="bg-amber-50 p-4 rounded-lg border border-amber-100"><div class="flex justify-between items-start"><div><p class="text-sm text-amber-600 font-medium">Total Needed</p><h3 class="text-2xl font-bold text-amber-800 mt-1"><?php echo $totalNeeded; ?></h3></div><div class="bg-amber-100 p-2 rounded-full"><i class="fas fa-clipboard-list text-amber-600"></i></div></div></div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-100"><div class="flex justify-between items-start"><div><p class="text-sm text-purple-600 font-medium">Remaining to Procure</p><h3 class="text-2xl font-bold text-purple-800 mt-1"><?php echo max(0, $totalNeeded - $totalInventoried); ?></h3></div><div class="bg-purple-100 p-2 rounded-full"><i class="fas fa-tasks text-purple-600"></i></div></div></div>
            </div>

            <div class="table-container">
                <table id="inventory-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider equipment-name">ICT Equipment</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Inventory Count</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Needed</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Procured CY <?php echo htmlspecialchars($selectedYear); ?></th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Procurement Progress</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Remaining</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($allEquipmentTypes)): ?>
                            <tr><td colspan="6" class="text-center py-4">No inventory data found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($allEquipmentTypes as $name => $data): ?>
                                <?php
                                    $inventoryCount = $data['inventoryCount'];
                                    $needed = $neededQuantities[$name] ?? 0;
                                    $procured = $data['procuredCY'];
                                    $remaining = max(0, $needed - $inventoryCount);
                                    // MODIFIED: Cap progress at 100
                                    $progress = ($needed > 0) ? min(100, ($inventoryCount / $needed) * 100) : 0;
                                    $totalRemaining += $remaining; // Add to the grand total for the footer
                                    
                                    // Determine progress bar color
                                    $progressColor = 'warning'; // Default to yellow
                                    if ($progress >= 100) $progressColor = 'success';
                                    else if ($progress >= 50) $progressColor = 'primary';
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 equipment-name"><?php echo htmlspecialchars($name); ?></td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-700"><?php echo $inventoryCount; ?></td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-700"><?php echo $needed; ?></td>
                                    <td class="px-4 py-3 text-sm text-center font-semibold <?php echo $procured > 0 ? 'text-green-600' : 'text-gray-600'; ?>"><?php echo $procured; ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center justify-center">
                                            <div class="w-24 bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-<?php echo $progressColor; ?> h-2.5 rounded-full progress-bar" style="width: <?php echo $progress; ?>%"></div>
                                            </div>
                                            <div class="ml-3 text-xs text-gray-600 w-10 text-right"><?php echo round($progress, 1); ?>%</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center font-semibold <?php echo $remaining > 0 ? 'text-danger' : 'text-gray-600'; ?>"><?php echo $remaining; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                      <tfoot>
                        <tr class="bg-gray-100 font-bold">
                            <td class="px-4 py-3 text-sm text-gray-900">TOTAL</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900"><?php echo $totalInventoried; ?></td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900"><?php echo $totalNeeded; ?></td>
                            <td class="px-4 py-3 text-sm text-center text-green-700"><?php echo $totalProcuredCY; ?></td>
                            <td class="px-4 py-3 text-sm text-center">
                                <?php 
                                    // MODIFIED: Cap total progress at 100
                                    $totalProgress = $totalNeeded > 0 ? min(100, round(($totalInventoried / $totalNeeded) * 100, 1)) : 0;
                                    $totalProgressColor = 'warning';
                                    if ($totalProgress >= 100) $totalProgressColor = 'success';
                                    else if ($totalProgress >= 50) $totalProgressColor = 'primary';
                                ?>
                                <span class="font-bold text-<?php echo $totalProgressColor; ?>"><?php echo $totalProgress; ?>%</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-red-700"><?php echo $totalRemaining; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i> Report generated on: <?php echo date('F j, Y, g:i a'); ?>
                </div>
                <div class="flex gap-2">
                    <button id="open-modal-button" class="px-4 py-2 bg-secondary text-white rounded-lg hover:bg-slate-700 transition flex items-center shadow-sm">
                        <i class="fas fa-window-maximize mr-2"></i> Edit
                    </button>
                    <button id="export-pdf" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition flex items-center shadow-sm">
                        <i class="fas fa-file-pdf mr-2"></i> Export PDF
                    </button>
                    <a href="?export=excel&year=<?php echo htmlspecialchars($selectedYear); ?>" class="px-4 py-2 bg-success text-white rounded-lg hover:bg-green-700 transition flex items-center shadow-sm">
                        <i class="fas fa-file-excel mr-2"></i> Export Excel
                    </a>
                </div>
            </div>
        </div>
    
    </div>

    <div id="iframe-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl h-full max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Details View</h3>
                <button id="modal-close-button" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-4 flex-grow">
                <iframe id="modal-iframe" src="" class="w-full h-full border-0" title="Modal Content"></iframe>
            </div>
        </div>
    </div>


    <script>
        // Simple animation for progress bars on page load
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });

        // PDF Export functionality
        document.getElementById('export-pdf').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const selectedYear = "<?php echo htmlspecialchars($selectedYear); ?>";

            doc.text(`ICT Equipment Procurement Summary - CY ${selectedYear}`, 14, 16);
            
            doc.autoTable({ 
                html: '#inventory-table',
                startY: 22,
                theme: 'grid',  
                headStyles: { fillColor: [37, 99, 235] }, // primary color
                footStyles: { fillColor: [243, 244, 246], textColor: [17, 24, 39], fontStyle: 'bold' }, // gray-100
            });

            doc.save(`ICT_Inventory_Summary_${selectedYear}.pdf`);
        });

        // --- NEW: Modal Control ---
        const openModalBtn = document.getElementById('open-modal-button');
        const closeModalBtn = document.getElementById('modal-close-button');
        const iframeModal = document.getElementById('iframe-modal');
        const modalIframe = document.getElementById('modal-iframe');

        // Show the modal
        openModalBtn.addEventListener('click', () => {
            // IMPORTANT: Change this URL to the page you want to load in the iframe
            const modalUrl = 'manage_equipment.php?year=<?php echo $selectedYear; ?>'; 
            modalIframe.src = modalUrl;
            iframeModal.classList.remove('hidden');
        });

        // Hide the modal
        function closeModal() {
            iframeModal.classList.add('hidden');
            modalIframe.src = ''; // Clear the src to stop video/audio, etc., from playing
        }

        closeModalBtn.addEventListener('click', closeModal);

        // Hide modal when clicking on the dark background (backdrop)
        iframeModal.addEventListener('click', (event) => {
            // Check if the clicked element is the modal backdrop itself
            if (event.target === iframeModal) {
                closeModal();
            }
        });
    </script>
</body>
</html>