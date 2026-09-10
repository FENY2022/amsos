<?php
require 'connect.php';

// Specification columns to add after 'specifications'
$specColumns = ['HDD', 'SSD', 'RAM', 'Display', 'Processor', 'Battery', 'Operating System'];

function parseSpecifications($specs) {
    $out = array_fill_keys(['HDD','SSD','RAM','Display','Processor','Battery','Operating System'], '');
    if (empty($specs)) return $out;

    $s = $specs;

    // HDD
    if (preg_match('/\d+\s*(?:GB|TB)\s*(?:SATA\s*)?HDD/i', $s, $m)) {
        $out['HDD'] = trim($m[0]);
    } elseif (stripos($s, 'HDD') !== false) {
        $out['HDD'] = 'HDD';
    }

    // SSD
    if (preg_match('/\d+\s*(?:GB|TB)\s*(?:NVMe\s*)?SSD/i', $s, $m)) {
        $out['SSD'] = trim($m[0]);
    } elseif (stripos($s, 'SSD') !== false) {
        $out['SSD'] = 'SSD';
    }

    // RAM
    if (preg_match('/(?:\d+\s*[xX]\s*)?\d+\s*GB\s*(?:RAM|DDR[2345]|memory|Memory)\b/i', $s, $m)) {
        $out['RAM'] = trim($m[0]);
    } elseif (preg_match('/\d+\s*GB/i', $s, $m)) {
        $out['RAM'] = trim($m[0]) . ' RAM';
    }

    // Display
    if (preg_match('/\d+[\.\d]*["\x{0022}\x{201d}\x{201d}]?\s*(?:[^,]*?(?:monitor|Monitor|LED|FHD|HD|display|Display))[^,]*/u', $s, $m)) {
        $out['Display'] = trim($m[0]);
    } elseif (preg_match('/\d+[\.\d]*["\x{0022}]\s*[^,]*/u', $s, $m)) {
        $out['Display'] = trim($m[0]);
    }

    // Processor
    if (preg_match('/(?:Intel\s*Core\s*i[3579][-\s]\d+[A-Za-z0-9]*|AMD\s*Ryzen\s*\d+[A-Za-z0-9]*|RYZEN\s*\d+)/i', $s, $m)) {
        $out['Processor'] = trim($m[0]);
    } elseif (preg_match('/Intel\s*Core\s*i[3579]/i', $s, $m)) {
        $out['Processor'] = trim($m[0]);
    } elseif (preg_match('/Celeron|Pentium|Athlon/i', $s, $m)) {
        $out['Processor'] = trim($m[0]);
    }

    // Battery
    if (preg_match('/\d+\s*(?:cell|Wh|mAh)[^,]*/i', $s, $m)) {
        $out['Battery'] = trim($m[0]);
    } elseif (stripos($s, 'battery') !== false) {
        $out['Battery'] = 'with battery';
    }

    // Operating System
    if (preg_match('/(?:Windows\s*(?:1[01]|[789]|Vista|XP|Server)|W\s*(?:1[01]|[789]))/i', $s, $m)) {
        $out['Operating System'] = trim($m[0]);
    } elseif (preg_match('/W10|W11|Windows/i', $s, $m)) {
        $out['Operating System'] = trim($m[0]);
    }

    return $out;
}

// Insert spec columns after 'specifications' in the headers array
function addSpecColumns($headers) {
    global $specColumns;
    $idx = array_search('specifications', $headers);
    if ($idx === false) return $headers;
    array_splice($headers, $idx + 1, 0, $specColumns);
    return $headers;
}

// --- Export to Excel (HTML format with proper styling) ---
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $sql = "SELECT * FROM inv_inventory ORDER BY id DESC";
    $result = $conn->query($sql);
    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    $conn->close();

    $filename = 'AMSOS_Inventory_' . date('Y-m-d_H-i-s') . '.xls';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta charset="UTF-8">';
    echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Inventory</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
    echo '<style>td,th{mso-number-format:"\@";font-size:12pt;vertical-align:middle;} th{font-weight:bold;background:#1a73e8;color:#fff;} td,th{border:1px solid #ccc;padding:6px 10px;} tr{height:28.6pt;}</style>';
    echo '</head><body>';

    // Columns that need extra styling
    $boldCols = ['employeeName', 'equipmentType', 'accountablePerson'];
    $centerCols = ['yearAcquired'];

    echo '<table>';
    if (!empty($rows)) {
        $headers = addSpecColumns(array_keys($rows[0]));
        // Header row
        echo '<tr>';
        foreach ($headers as $h) {
            $label = ucwords(str_replace('_', ' ', $h));
            echo '<th style="white-space:normal;">' . htmlspecialchars($label) . '</th>';
        }
        echo '</tr>';

        // Data rows
        foreach ($rows as $row) {
            $parsed = parseSpecifications($row['specifications'] ?? '');
            $rowWithSpecs = $row;
            // Insert parsed values after 'specifications'
            $idx = array_search('specifications', array_keys($rowWithSpecs));
            if ($idx !== false) {
                $pos = $idx + 1;
                $rowWithSpecs = array_slice($rowWithSpecs, 0, $pos, true)
                    + $parsed
                    + array_slice($rowWithSpecs, $pos, null, true);
            }

            echo '<tr>';
            foreach ($headers as $h) {
                $cell = $rowWithSpecs[$h] ?? '';
                $text = htmlspecialchars((string)$cell);
                $style = 'white-space:pre-wrap;';
                if (in_array($h, $boldCols)) {
                    $style .= 'font-weight:bold;';
                }
                if (in_array($h, $centerCols)) {
                    $style .= 'text-align:center;';
                }
                echo '<td style="' . $style . '">' . $text . '</td>';
            }
            echo '</tr>';
        }
    }
    echo '</table></body></html>';
    exit;
}

// --- Export to Excel (Color Coded) ---
if (isset($_GET['export']) && $_GET['export'] === 'excel_color') {
    $sql = "SELECT * FROM inv_inventory ORDER BY id DESC";
    $result = $conn->query($sql);
    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    $conn->close();

    // Generate color for each equipment type
    $eqColors = [];
    $palette = [
        '#FFD6D6','#D6F5D6','#D6E8FF','#FFF2CC','#E8D6FF',
        '#FFE0CC','#CCFFE0','#FFCCF2','#CCF5FF','#FFF9CC',
        '#D6FFD6','#FFD6E8','#E0D6FF','#D6FFE8','#FFECD6',
        '#D6F0FF','#F2D6FF','#FFD6CC','#CCFFF2','#FFE6CC',
        '#E6FFD6','#D6CCFF','#FFD6F0','#CCE8FF','#E8FFCC',
        '#FFD6E0','#D6FFCC','#CCD6FF','#FFECD6','#D6FFE0',
    ];

    $filename = 'AMSOS_Inventory_ColorCoded_' . date('Y-m-d_H-i-s') . '.xls';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta charset="UTF-8">';
    echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Inventory</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
    echo '<style>td,th{mso-number-format:"\@";font-size:12pt;vertical-align:middle;} th{font-weight:bold;background:#1a73e8;color:#fff;} td,th{border:1px solid #ccc;padding:6px 10px;} tr{height:28.6pt;}</style>';
    echo '</head><body>';

    $boldCols = ['employeeName', 'equipmentType', 'accountablePerson'];
    $centerCols = ['yearAcquired'];

    echo '<table>';
    if (!empty($rows)) {
        $headers = addSpecColumns(array_keys($rows[0]));
        // Header row
        echo '<tr>';
        foreach ($headers as $h) {
            $label = ucwords(str_replace('_', ' ', $h));
            echo '<th style="white-space:normal;">' . htmlspecialchars($label) . '</th>';
        }
        echo '</tr>';

        foreach ($rows as $row) {
            $parsed = parseSpecifications($row['specifications'] ?? '');
            $rowWithSpecs = $row;
            $idx = array_search('specifications', array_keys($rowWithSpecs));
            if ($idx !== false) {
                $pos = $idx + 1;
                $rowWithSpecs = array_slice($rowWithSpecs, 0, $pos, true)
                    + $parsed
                    + array_slice($rowWithSpecs, $pos, null, true);
            }

            echo '<tr>';
            $eqType = $row['equipmentType'] ?? '';
            if (!isset($eqColors[$eqType])) {
                $eqColors[$eqType] = $palette[count($eqColors) % count($palette)];
            }
            $rowColor = $eqColors[$eqType];

            foreach ($headers as $h) {
                $cell = $rowWithSpecs[$h] ?? '';
                $text = htmlspecialchars((string)$cell);
                $style = "white-space:pre-wrap;background-color:$rowColor;";
                if (in_array($h, $boldCols)) {
                    $style .= 'font-weight:bold;';
                }
                if (in_array($h, $centerCols)) {
                    $style .= 'text-align:center;';
                }
                echo '<td style="' . $style . '">' . $text . '</td>';
            }
            echo '</tr>';
        }
    }
    echo '</table></body></html>';
    exit;
}

// --- Export to Excel (Summary per Division) ---
if (isset($_GET['export']) && $_GET['export'] === 'excel_division') {
    $sql = "SELECT officeDivision, equipmentType, COUNT(*) as cnt FROM inv_inventory GROUP BY officeDivision, equipmentType ORDER BY officeDivision, equipmentType";
    $result = $conn->query($sql);
    $data = [];
    $divisions = [];
    $eqTypes = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $div = $row['officeDivision'];
            $eq = $row['equipmentType'];
            $data[$div][$eq] = (int)$row['cnt'];
            $divisions[$div] = true;
            $eqTypes[$eq] = true;
        }
    }

    // Personnel count per division
    $personnel = [];
    $perSql = "SELECT officeDivision, COUNT(DISTINCT employeeName) as personnel_count FROM inv_inventory GROUP BY officeDivision";
    $perResult = $conn->query($perSql);
    if ($perResult && $perResult->num_rows > 0) {
        while ($row = $perResult->fetch_assoc()) {
            $personnel[$row['officeDivision']] = (int)$row['personnel_count'];
        }
    }

    $divisions = array_keys($divisions);
    $eqTypes = array_keys($eqTypes);
    sort($divisions);
    sort($eqTypes);
    $conn->close();

    $filename = 'AMSOS_Inventory_ByDivision_' . date('Y-m-d_H-i-s') . '.xls';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta charset="UTF-8">';
    echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Summary</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
    echo '<style>td,th{mso-number-format:"\@";font-size:12pt;vertical-align:middle;text-align:center;} th{font-weight:bold;background:#1a73e8;color:#fff;} td,th{border:1px solid #ccc;padding:6px 10px;} tr{height:28.6pt;} .div-label{font-weight:bold;text-align:left;background:#e8f0fe;} .grand-total{font-weight:bold;background:#d4edda;} .sub-total{font-weight:bold;background:#fff3cd;} .personnel{font-weight:bold;background:#fce8e6;}</style>';
    echo '</head><body>';
    echo '<h2 style="font-family:Roboto,sans-serif;color:#1a73e8;">AMSOS Inventory Summary per Division</h2>';
    echo '<p style="font-family:Roboto,sans-serif;color:#5f6368;">Generated: ' . date('F j, Y h:i A') . '</p>';
    echo '<table>';

    // Header row
    echo '<tr><th style="text-align:left;">Office Division</th>';
    echo '<th style="background:#e3746b;color:#fff;">No. of Personnel</th>';
    foreach ($eqTypes as $eq) {
        echo '<th>' . htmlspecialchars($eq) . '</th>';
    }
    echo '<th>Total Equipment</th></tr>';

    // Data rows
    $grandTotalEq = 0;
    $grandTotalPersonnel = 0;
    $colTotals = array_fill_keys($eqTypes, 0);
    foreach ($divisions as $div) {
        echo '<tr><td class="div-label">' . htmlspecialchars($div) . '</td>';
        $pCount = $personnel[$div] ?? 0;
        $grandTotalPersonnel += $pCount;
        echo '<td class="personnel">' . $pCount . '</td>';
        $rowTotal = 0;
        foreach ($eqTypes as $eq) {
            $cnt = $data[$div][$eq] ?? 0;
            $rowTotal += $cnt;
            $colTotals[$eq] += $cnt;
            echo '<td>' . $cnt . '</td>';
        }
        $grandTotalEq += $rowTotal;
        echo '<td class="sub-total">' . $rowTotal . '</td></tr>';
    }

    // Grand total row
    echo '<tr><td class="grand-total" style="text-align:left;">GRAND TOTAL</td>';
    echo '<td class="grand-total">' . $grandTotalPersonnel . '</td>';
    foreach ($eqTypes as $eq) {
        echo '<td class="grand-total">' . $colTotals[$eq] . '</td>';
    }
    echo '<td class="grand-total">' . $grandTotalEq . '</td></tr>';

    echo '</table></body></html>';
    exit;
}
// --- End Export ---

$sql = "SELECT * FROM inv_inventory ORDER BY id DESC";
$result = $conn->query($sql);

$inventory_data = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $inventory_data[] = $row;
    }
}
$conn->close();

$totalRecords = count($inventory_data);
$uniqueDivisions = [];
$uniqueEquipmentTypes = [];

foreach ($inventory_data as $item) {
    $division = trim((string)($item['officeDivision'] ?? ''));
    $equipmentType = trim((string)($item['equipmentType'] ?? ''));

    if ($division !== '') {
        $uniqueDivisions[$division] = true;
    }
    if ($equipmentType !== '') {
        $uniqueEquipmentTypes[$equipmentType] = true;
    }
}

$divisionCount = count($uniqueDivisions);
$equipmentTypeCount = count($uniqueEquipmentTypes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMSOS Inventory - Material UI with Dropdown Search</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    
    <style>
        /* Google Material Design Global Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(180deg, #f4f7fb 0%, #eef3f9 100%);
            margin: 0;
            padding: 28px 16px;
            color: #202124;
        }

        .page-shell {
            max-width: 1560px;
            margin: 0 auto;
        }

        .hero-card {
            background: linear-gradient(135deg, #1a73e8 0%, #1557b0 100%);
            border-radius: 22px;
            padding: 24px 26px;
            color: #fff;
            box-shadow: 0 18px 42px rgba(26, 115, 232, 0.22);
            margin-bottom: 18px;
        }

        .hero-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 20px;
        }

        .hero-title {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 8px;
        }

        .hero-title i {
            font-size: 2rem;
        }

        .hero-subtitle {
            margin: 0;
            max-width: 760px;
            color: rgba(255, 255, 255, 0.86);
            font-size: 0.98rem;
            line-height: 1.55;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .stat-chip {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 16px;
            padding: 14px 16px;
            backdrop-filter: blur(4px);
        }

        .stat-chip .label {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 6px;
        }

        .stat-chip .value {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
        }

        .table-container {
            background: #ffffff;
            padding: 24px 24px 20px;
            border-radius: 22px;
            box-shadow: 0 12px 34px rgba(15, 23, 42, 0.08);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .table-header-copy h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 6px;
        }

        .table-header-copy p {
            margin: 0;
            color: #64748b;
            font-size: 0.92rem;
        }

        .table-note {
            display: none;
            margin: 0 0 12px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.86rem;
            font-weight: 500;
        }

        .table-shell {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
        }

        .header-title .export-btn,
        .export-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1a73e8;
            color: #fff;
            padding: 8px 20px;
            border-radius: 24px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.3s;
            cursor: pointer;
            border: none;
            font-family: 'Roboto', sans-serif;
        }

        .header-title .export-btn:hover,
        .export-btn:hover {
            background: #1557b0;
        }

        .export-dropdown {
            position: relative;
            display: inline-block;
        }

        .export-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 4px;
            background: #fff;
            min-width: 260px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 100;
            overflow: hidden;
        }

        .export-dropdown-content a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            text-decoration: none;
            color: #3c4043;
            font-size: 14px;
            transition: background 0.2s;
        }

        .export-dropdown-content a:hover {
            background: #f1f3f4;
        }

        .export-dropdown-content a .label {
            display: flex;
            flex-direction: column;
        }

        .export-dropdown-content a .label small {
            font-size: 11px;
            color: #80868b;
        }

        .export-dropdown-content a .color-icon {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        /* DataTable Material Overrides */
        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        table.dataTable thead th, table.dataTable tfoot th {
            color: #5f6368;
            font-weight: 500;
            text-transform: capitalize;
            padding: 14px 12px;
            font-size: 14px;
            border-bottom: 2px solid #e0e0e0 !important;
            border-top: none !important;
            background: #f8fafc;
        }

        table.dataTable tbody td {
            border-bottom: 1px solid #e0e0e0 !important;
            padding: 12px 12px;
            color: #3c4043;
            font-size: 14px;
            white-space: nowrap;
        }

        table.dataTable tbody tr:hover {
            background-color: #f1f3f4 !important;
        }

        /* Global Search Bar */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dadce0;
            border-radius: 24px;
            padding: 8px 16px;
            margin-left: 8px;
            outline: none;
            transition: all 0.3s;
            font-family: 'Roboto', sans-serif;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #1a73e8;
            box-shadow: 0 1px 3px rgba(26,115,232,0.3);
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 12px;
            margin-bottom: 6px;
            color: #475569;
            font-size: 0.92rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 5px 10px;
            background: #fff;
        }

        .dataTables_wrapper .dataTables_scroll {
            overflow: hidden;
        }

        .dataTables_wrapper .dataTables_scrollBody {
            border-top: 1px solid #eef2f7 !important;
            scrollbar-width: thin;
        }

        .dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar {
            height: 10px;
        }

        .dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        /* Pagination Buttons */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #e8f0fe !important;
            color: #1a73e8 !important;
            border: none !important;
            border-radius: 50%;
            font-weight: 500;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f1f3f4 !important;
            border: none !important;
            border-radius: 50%;
            color: #202124 !important;
        }

        /* Column Dropdown Filters Style */
        .column-search {
            width: 100%;
            padding: 6px;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
            color: #3c4043;
            outline: none;
            background-color: #fff;
        }
        
        .column-search:focus {
            border-color: #1a73e8;
        }

        @media (max-width: 992px) {
            body {
                padding: 20px 12px;
            }

            .hero-top,
            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .export-dropdown,
            .export-btn {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .hero-card {
                padding: 18px;
                border-radius: 18px;
            }

            .hero-title {
                font-size: 1.4rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table-container {
                padding: 18px 14px 14px;
                border-radius: 18px;
            }

            .table-note {
                display: block;
            }

            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                float: none !important;
                text-align: left !important;
                width: 100%;
            }

            .dataTables_wrapper .dataTables_filter label,
            .dataTables_wrapper .dataTables_length label {
                width: 100%;
                display: block;
            }

            .dataTables_wrapper .dataTables_filter input {
                margin-left: 0;
                margin-top: 8px;
                width: 100%;
                box-sizing: border-box;
            }

            .filter-row {
                display: none;
            }

            table.dataTable thead th {
                font-size: 12px;
                padding: 12px 10px;
            }

            table.dataTable tbody td {
                font-size: 12px;
                padding: 10px;
            }

            .export-dropdown-content {
                min-width: 100%;
                left: 0;
                right: auto;
            }
        }

        @media (max-width: 576px) {
            .hero-subtitle,
            .table-header-copy p,
            .table-note,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length {
                font-size: 0.84rem;
            }

            .hero-title i,
            .material-icons {
                font-size: 1.5rem;
            }

            .stat-chip .value {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>

    <div class="page-shell">
        <div class="hero-card">
            <div class="hero-top">
                <div>
                    <h1 class="hero-title"><i class="material-icons">inventory_2</i> AMSOS Equipment Inventory</h1>
                    <p class="hero-subtitle">Browse, search, and export the latest AMSOS equipment records. Desktop keeps full filtering controls, while mobile is optimized for quick search and smooth horizontal scrolling.</p>
                </div>
                <div class="export-dropdown">
                    <button class="export-btn" onclick="toggleExport(event)">
                        <i class="material-icons" style="font-size: 18px; vertical-align: middle;">file_download</i>
                        Export to Excel
                        <i class="material-icons" style="font-size: 18px;">arrow_drop_down</i>
                    </button>
                    <div class="export-dropdown-content" id="exportDropdown">
                        <a href="?export=excel">
                            <i class="material-icons" style="font-size: 20px; color:#1a73e8;">table_chart</i>
                            <span class="label">
                                Export to Excel
                                <small>Plain format without colors</small>
                            </span>
                        </a>
                        <a href="?export=excel_color">
                            <span class="color-icon" style="background: linear-gradient(135deg,#FFD6D6,#D6F5D6,#D6E8FF);"></span>
                            <span class="label">
                                Export to Excel (Color Coded)
                                <small>Color coded by Equipment Type</small>
                            </span>
                        </a>
                        <a href="?export=excel_division">
                            <i class="material-icons" style="font-size: 20px; color:#34a853;">bar_chart</i>
                            <span class="label">
                                Summary per Division
                                <small>Equipment count grouped by office division</small>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-chip">
                    <span class="label">Total Records</span>
                    <span class="value"><?php echo number_format($totalRecords); ?></span>
                </div>
                <div class="stat-chip">
                    <span class="label">Office Divisions</span>
                    <span class="value"><?php echo number_format($divisionCount); ?></span>
                </div>
                <div class="stat-chip">
                    <span class="label">Equipment Types</span>
                    <span class="value"><?php echo number_format($equipmentTypeCount); ?></span>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <div class="table-header-copy">
                    <h2><i class="material-icons">manage_search</i> Inventory Explorer</h2>
                    <p>Use global search for quick lookups. On desktop, each column also has a dropdown filter for exact matching.</p>
                </div>
            </div>

            <p class="table-note"><i class="material-icons" style="font-size: 16px; vertical-align: text-bottom;">swipe</i> Swipe sideways to view more columns on smaller screens.</p>

            <div class="table-shell">
                <table id="inventoryTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <?php
                    if (!empty($inventory_data)) {
                        foreach (array_keys($inventory_data[0]) as $headerName) {
                            $cleanHeader = str_replace('_', ' ', $headerName);
                            echo "<th>" . htmlspecialchars($cleanHeader) . "</th>";
                        }
                    } else {
                        echo "<th>No Data Found</th>";
                    }
                    ?>
                </tr>
                <tr class="filter-row">
                    <?php
                    if (!empty($inventory_data)) {
                        foreach (array_keys($inventory_data[0]) as $headerName) {
                            echo "<th></th>";
                        }
                    } else {
                        echo "<th></th>";
                    }
                    ?>
                </tr>
            </thead>
            
            <tbody>
                <?php
                if (!empty($inventory_data)) {
                    foreach ($inventory_data as $row) {
                        echo "<tr>";
                        foreach ($row as $data) {
                            echo "<td>" . htmlspecialchars((string)$data) . "</td>";
                        }
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
    
    <script>
        $(document).ready(function() {
            $('#inventoryTable').DataTable({
                "scrollX": true,
                "autoWidth": false,
                "pageLength": 15,
                "order": [[ 0, "desc" ]],
                "language": {
                    "search": "<i class='material-icons' style='vertical-align: middle; font-size: 20px; color: #5f6368;'>search</i> Global Search:",
                    "lengthMenu": "Display _MENU_ records"
                },
                initComplete: function () {
                    var api = this.api();
                    
                    // Ibutang ang dropdown search box sa filter row (thead > tr.filter-row)
                    $('.filter-row th', api.table().header()).each(function (i) {
                        var column = api.column(i);
                        var select = $('<select class="column-search"><option value="">Show All</option></select>')
                            .appendTo($(this))
                            .on('change', function () {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });
                            
                        column.data().unique().sort().each(function (d, j) {
                            if (d) {
                                select.append('<option value="' + d + '">' + d + '</option>');
                            }
                        });
                    });
                }
            });
        });

        // Export dropdown toggle
        function toggleExport(e) {
            e.stopPropagation();
            var dd = document.getElementById('exportDropdown');
            dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
        }
        document.addEventListener('click', function() {
            document.getElementById('exportDropdown').style.display = 'none';
        });
    </script>
</body>
</html>
