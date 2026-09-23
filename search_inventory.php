<?php
require_once 'connect.php';

if (!function_exists('fetchDistinctValues')) {
    function fetchDistinctValues(mysqli $conn, string $column): array
    {
        $allowedColumns = ['employeeName', 'equipmentType', 'statusOfEmployment', 'officeDivision'];
        if (!in_array($column, $allowedColumns, true)) {
            return [];
        }

        $values = [];
        $sql = "SELECT DISTINCT `$column` AS value FROM inv_inventory WHERE `$column` IS NOT NULL AND TRIM(`$column`) != '' ORDER BY `$column` ASC";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $values[] = $row['value'];
            }
            $result->free();
        }

        return $values;
    }
}

$employeeNames = fetchDistinctValues($conn, 'employeeName');
$categoryOptions = fetchDistinctValues($conn, 'equipmentType');
$statusOptions = fetchDistinctValues($conn, 'statusOfEmployment');
$officeOptions = fetchDistinctValues($conn, 'officeDivision');

$employeeNameFilter = trim($_GET['employeeName'] ?? $_GET['query'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$officeDivisionFilter = trim($_GET['officeDivision'] ?? '');
$hasFilters = $employeeNameFilter !== '' || $categoryFilter !== '' || $statusFilter !== '' || $officeDivisionFilter !== '';
$formAction = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
$currentDir = $_GET['dir'] ?? '';
?>
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
            margin-bottom: 20px;
        }

        .name-combobox {
            position: relative;
            max-width: 440px;
        }

        .name-combobox-shell {
            position: relative;
        }

        .name-combobox-shell i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #a8a8b3;
            pointer-events: none;
            z-index: 2;
        }

        .name-combobox input[type="text"] {
            width: 100%;
            padding: 18px 46px 18px 52px;
            border: 2px solid #9285ff;
            border-radius: 18px;
            background: #1f1f23;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            transition: var(--transition);
        }

        .name-combobox input[type="text"]::placeholder {
            color: #b7b7c2;
            font-weight: 500;
        }

        .name-combobox input[type="text"]:focus {
            outline: none;
            border-color: #a99eff;
            box-shadow: 0 0 0 3px rgba(146, 133, 255, 0.25);
        }

        .combobox-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #b7b7c2;
            pointer-events: none;
        }

        .name-suggestions {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 30;
            max-height: 320px;
            overflow-y: auto;
            list-style: none;
            margin: 0;
            padding: 10px 0;
            background: #242428;
            border: 1px solid #3b3b42;
            border-radius: 14px;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28);
        }

        .name-suggestions.is-open {
            display: block;
        }

        .name-suggestions li {
            padding: 12px 18px;
            color: #f3f3f5;
            cursor: pointer;
            font-weight: 600;
        }

        .name-suggestions li:hover,
        .name-suggestions li.is-active {
            background: #33333a;
        }

        .name-suggestions .empty-option {
            cursor: default;
            color: #b7b7c2;
            font-weight: 500;
        }

        .filter-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .filter-actions button,
        .filter-actions a {
            padding: 12px 22px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .filter-actions button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
        }

        .filter-actions a {
            background: #eef0f5;
            color: var(--dark);
            border: 1px solid var(--light-gray);
        }

        .filter-actions button:hover,
        .filter-actions a:hover {
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

        .row-name-select,
        .row-division-select {
            min-width: 190px;
            padding: 8px 10px;
            border: 1px solid #cfd4dc;
            border-radius: 8px;
            background: #fff;
            color: var(--dark);
            font-size: 14px;
        }

        .row-update-btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .row-update-btn:hover:not(:disabled) {
            background: var(--primary-dark);
        }

        .row-update-btn:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .row-update-status {
            display: inline-block;
            margin-left: 8px;
            font-size: 12px;
            font-weight: 600;
            color: var(--gray);
        }

        .row-update-status.is-success {
            color: #198754;
        }

        .row-update-status.is-error {
            color: #dc3545;
        }

        .confirm-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.55);
        }

        .confirm-modal-backdrop.is-open {
            display: flex;
        }

        .confirm-modal {
            width: min(420px, 100%);
            padding: 24px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
        }

        .confirm-modal h3 {
            margin-bottom: 8px;
            color: var(--dark);
            font-size: 1.25rem;
        }

        .confirm-modal p {
            margin-bottom: 20px;
            color: var(--gray);
        }

        .confirm-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .confirm-modal-actions button {
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
        }

        .confirm-no-btn {
            border: 1px solid #d0d5dd;
            background: #fff;
            color: var(--dark);
        }

        .confirm-yes-btn {
            border: none;
            background: var(--primary);
            color: #fff;
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

            .name-combobox {
                max-width: 100%;
            }

            .filter-actions {
                flex-direction: column;
            }

            .filter-actions button,
            .filter-actions a {
                justify-content: center;
                width: 100%;
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
            <form class="search-form" id="inventorySearchForm" action="<?php echo $formAction; ?>" method="GET" autocomplete="off">
                <?php if ($currentDir !== ''): ?>
                    <input type="hidden" name="dir" value="<?php echo htmlspecialchars($currentDir, ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>
                <div class="name-combobox">
                    <div class="name-combobox-shell">
                        <i class="fas fa-search"></i>
                        <input type="text" id="employeeNameSearch" name="employeeName" placeholder="Search employee name..." value="<?php echo htmlspecialchars($employeeNameFilter, ENT_QUOTES, 'UTF-8'); ?>" aria-autocomplete="list" aria-expanded="false" aria-controls="employeeNameSuggestions">
                        <span class="combobox-toggle"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <ul class="name-suggestions" id="employeeNameSuggestions"></ul>
                </div>

            <div class="filters">
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categoryOptions as $category): ?>
                            <option value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $categoryFilter === $category ? 'selected' : ''; ?>><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($statusOptions as $status): ?>
                            <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $statusFilter === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="office">Office/Division</label>
                    <select id="office" name="officeDivision">
                        <option value="">All Offices</option>
                        <?php foreach ($officeOptions as $officeDivision): ?>
                            <option value="<?php echo htmlspecialchars($officeDivision, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $officeDivisionFilter === $officeDivision ? 'selected' : ''; ?>><?php echo htmlspecialchars($officeDivision, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
                <div class="filter-actions">
                    <button type="submit"><i class="fas fa-filter"></i> Apply Filters</button>
                    <a href="<?php echo $formAction . ($currentDir !== '' ? '?dir=' . urlencode($currentDir) : ''); ?>"><i class="fas fa-rotate-left"></i> Reset</a>
                </div>
            </form>
        </div>
        
        <?php
        if ($hasFilters) {
            $conditions = [];
            $types = '';
            $params = [];

            if ($employeeNameFilter !== '') {
                $conditions[] = '`employeeName` LIKE ?';
                $types .= 's';
                $params[] = '%' . $employeeNameFilter . '%';
            }

            if ($categoryFilter !== '') {
                $conditions[] = '`equipmentType` = ?';
                $types .= 's';
                $params[] = $categoryFilter;
            }

            if ($statusFilter !== '') {
                $conditions[] = '`statusOfEmployment` = ?';
                $types .= 's';
                $params[] = $statusFilter;
            }

            if ($officeDivisionFilter !== '') {
                $conditions[] = '`officeDivision` = ?';
                $types .= 's';
                $params[] = $officeDivisionFilter;
            }

            $sql_template = 'SELECT DISTINCT * FROM inv_inventory WHERE ' . implode(' AND ', $conditions) . ' ORDER BY employeeName ASC, id DESC';
            $stmt = $conn->prepare($sql_template);

            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }

            $stmt->bind_param($types, ...$params);

            // Execute the statement
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Start the results display section
            $newSearchUrl = $formAction . ($currentDir !== '' ? '?dir=' . urlencode($currentDir) : '');
            $filterSummaryParts = [];
            if ($employeeNameFilter !== '') {
                $filterSummaryParts[] = 'Name: ' . $employeeNameFilter;
            }
            if ($categoryFilter !== '') {
                $filterSummaryParts[] = 'Category: ' . $categoryFilter;
            }
            if ($statusFilter !== '') {
                $filterSummaryParts[] = 'Status: ' . $statusFilter;
            }
            if ($officeDivisionFilter !== '') {
                $filterSummaryParts[] = 'Office/Division: ' . $officeDivisionFilter;
            }
            $filterSummary = implode(', ', $filterSummaryParts);

            echo '<a href="' . htmlspecialchars($newSearchUrl, ENT_QUOTES, 'UTF-8') . '" class="back-link"><i class="fas fa-arrow-left"></i> New Search</a>';
            echo '<div class="results-container">';
            echo '<div class="results-header">';
            echo '<div class="results-count">' . $result->num_rows . ' Results found' . ($filterSummary !== '' ? ' for "' . htmlspecialchars($filterSummary, ENT_QUOTES, 'UTF-8') . '"' : '') . '</div>';
            echo '<button class="export-btn"><i class="fas fa-download"></i> Export Results</button>';
            echo '</div>';

            if ($result->num_rows > 0) {
                echo '<div class="table-container">';
                echo '<table>';
                echo '<thead><tr>';
                $fieldNames = [];
                while ($fieldinfo = $result->fetch_field()) {
                    $fieldNames[] = $fieldinfo->name;
                    echo '<th>' . htmlspecialchars(ucwords(preg_replace('/(?<!\ )[A-Z]/', ' $0', $fieldinfo->name))) . '</th>';
                }
                echo '<th>Action</th>';
                echo '</tr></thead>';
                
                echo '<tbody>';
                while ($row = $result->fetch_assoc()) {
                    $inventoryId = (int)($row['id'] ?? 0);
                    echo '<tr data-inventory-id="' . $inventoryId . '">';
                    foreach ($fieldNames as $fieldName) {
                        $data = $row[$fieldName] ?? '';
                        if ($fieldName === 'employeeName') {
                            echo '<td>';
                            echo '<select class="row-name-select" data-original-name="' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '">';
                            foreach ($employeeNames as $employeeNameOption) {
                                $selected = (string)$data === (string)$employeeNameOption ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($employeeNameOption, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($employeeNameOption, ENT_QUOTES, 'UTF-8') . '</option>';
                            }
                            if ($data !== '' && !in_array($data, $employeeNames, true)) {
                                echo '<option value="' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '" selected>' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '</option>';
                            }
                            echo '</select>';
                            echo '</td>';
                        } elseif ($fieldName === 'officeDivision') {
                            echo '<td>';
                            echo '<select class="row-division-select" data-original-division="' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '">';
                            foreach ($officeOptions as $divisionOption) {
                                $selected = (string)$data === (string)$divisionOption ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($divisionOption, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($divisionOption, ENT_QUOTES, 'UTF-8') . '</option>';
                            }
                            if ($data !== '' && !in_array($data, $officeOptions, true)) {
                                echo '<option value="' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '" selected>' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '</option>';
                            }
                            echo '</select>';
                            echo '</td>';
                        } else {
                            echo '<td>' . htmlspecialchars($data) . '</td>';
                        }
                    }
                    echo '<td><button type="button" class="row-update-btn">Update</button><span class="row-update-status" aria-live="polite"></span></td>';
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
            <p>Asset Management and Service Optimization System &copy; 2023 | ICT AMSOS</p>
        </footer>
    </div>

    <div class="confirm-modal-backdrop" id="divisionConfirmModal" aria-hidden="true">
        <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="divisionConfirmTitle">
            <h3 id="divisionConfirmTitle">Update Inventory Row?</h3>
            <p>Do you really want to update this row's employee name or office division?</p>
            <div class="confirm-modal-actions">
                <button type="button" class="confirm-no-btn" id="divisionConfirmNo">No</button>
                <button type="button" class="confirm-yes-btn" id="divisionConfirmYes">Yes</button>
            </div>
        </div>
    </div>
    
    <script>
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

            const employeeNames = <?php echo json_encode($employeeNames, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
            const form = document.getElementById('inventorySearchForm');
            const nameInput = document.getElementById('employeeNameSearch');
            const suggestions = document.getElementById('employeeNameSuggestions');

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderSuggestions(filter = '') {
                const normalizedFilter = filter.trim().toLowerCase();
                const matches = employeeNames
                    .filter((name) => normalizedFilter === '' || name.toLowerCase().includes(normalizedFilter))
                    .slice(0, 60);

                if (!matches.length) {
                    suggestions.innerHTML = '<li class="empty-option">No matching names found</li>';
                    suggestions.classList.add('is-open');
                    nameInput.setAttribute('aria-expanded', 'true');
                    return;
                }

                suggestions.innerHTML = matches.map((name) => '<li data-name="' + escapeHtml(name) + '">' + escapeHtml(name) + '</li>').join('');
                suggestions.classList.add('is-open');
                nameInput.setAttribute('aria-expanded', 'true');
            }

            function closeSuggestions() {
                suggestions.classList.remove('is-open');
                nameInput.setAttribute('aria-expanded', 'false');
            }

            nameInput.addEventListener('focus', function() {
                renderSuggestions(nameInput.value);
            });

            nameInput.addEventListener('input', function() {
                renderSuggestions(nameInput.value);
            });

            nameInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    closeSuggestions();
                    form.submit();
                }
            });

            suggestions.addEventListener('mousedown', function(event) {
                const option = event.target.closest('li[data-name]');
                if (!option) {
                    return;
                }

                nameInput.value = option.dataset.name;
                closeSuggestions();
                form.submit();
            });

            document.addEventListener('mousedown', function(event) {
                if (!event.target.closest('.name-combobox')) {
                    closeSuggestions();
                }
            });

            const confirmModal = document.getElementById('divisionConfirmModal');
            const confirmYes = document.getElementById('divisionConfirmYes');
            const confirmNo = document.getElementById('divisionConfirmNo');
            let pendingDivisionUpdate = null;

            function openConfirmModal(updateData) {
                pendingDivisionUpdate = updateData;
                confirmModal.classList.add('is-open');
                confirmModal.setAttribute('aria-hidden', 'false');
                confirmYes.focus();
            }

            function closeConfirmModal(revertChange = false) {
                if (revertChange && pendingDivisionUpdate) {
                    if (pendingDivisionUpdate.nameSelect) {
                        pendingDivisionUpdate.nameSelect.value = pendingDivisionUpdate.nameSelect.dataset.originalName;
                    }
                    if (pendingDivisionUpdate.divisionSelect) {
                        pendingDivisionUpdate.divisionSelect.value = pendingDivisionUpdate.divisionSelect.dataset.originalDivision;
                    }
                    pendingDivisionUpdate.status.textContent = '';
                    pendingDivisionUpdate.status.className = 'row-update-status';
                }

                pendingDivisionUpdate = null;
                confirmModal.classList.remove('is-open');
                confirmModal.setAttribute('aria-hidden', 'true');
            }

            function saveDivisionUpdate(updateData) {
                const formData = new FormData();
                formData.append('id', updateData.inventoryId);
                formData.append('employeeName', updateData.nameSelect.value);
                formData.append('officeDivision', updateData.divisionSelect.value);

                updateData.status.textContent = 'Updating...';
                updateData.status.className = 'row-update-status';
                updateData.button.disabled = true;
                updateData.button.textContent = 'Updating...';

                fetch('update_inventory_division.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                    .then(function(response) {
                        return response.json().then(function(data) {
                            if (!response.ok || !data.success) {
                                throw new Error(data.message || 'Update failed.');
                            }
                            return data;
                        });
                    })
                    .then(function() {
                        updateData.nameSelect.dataset.originalName = updateData.nameSelect.value;
                        updateData.divisionSelect.dataset.originalDivision = updateData.divisionSelect.value;
                        updateData.status.textContent = 'Updated';
                        updateData.status.classList.add('is-success');
                    })
                    .catch(function(error) {
                        updateData.status.textContent = error.message || 'Error';
                        updateData.status.classList.add('is-error');
                    })
                    .finally(function() {
                        updateData.button.disabled = false;
                        updateData.button.textContent = 'Update';
                    });
            }

            document.querySelectorAll('.row-update-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    const row = button.closest('tr[data-inventory-id]');
                    const nameSelect = row ? row.querySelector('.row-name-select') : null;
                    const divisionSelect = row ? row.querySelector('.row-division-select') : null;
                    const status = row ? row.querySelector('.row-update-status') : null;
                    const inventoryId = row ? row.dataset.inventoryId : '';

                    if (!row || !nameSelect || !divisionSelect || !status || !inventoryId) {
                        return;
                    }

                    if (nameSelect.value === nameSelect.dataset.originalName && divisionSelect.value === divisionSelect.dataset.originalDivision) {
                        status.textContent = 'No changes';
                        status.className = 'row-update-status';
                        return;
                    }

                    openConfirmModal({
                        button: button,
                        inventoryId: inventoryId,
                        nameSelect: nameSelect,
                        divisionSelect: divisionSelect,
                        status: status
                    });
                });
            });

            document.querySelectorAll('.row-name-select, .row-division-select').forEach(function(select) {
                select.addEventListener('change', function() {
                    const row = select.closest('tr[data-inventory-id]');
                    const button = row ? row.querySelector('.row-update-btn') : null;
                    const nameSelect = row ? row.querySelector('.row-name-select') : null;
                    const divisionSelect = row ? row.querySelector('.row-division-select') : null;
                    const status = row ? row.querySelector('.row-update-status') : null;
                    const inventoryId = row ? row.dataset.inventoryId : '';

                    if (!row || !button || !nameSelect || !divisionSelect || !status || !inventoryId) {
                        return;
                    }

                    if (nameSelect.value === nameSelect.dataset.originalName && divisionSelect.value === divisionSelect.dataset.originalDivision) {
                        status.textContent = 'No changes';
                        status.className = 'row-update-status';
                        return;
                    }

                    status.textContent = '';
                    status.className = 'row-update-status';
                    openConfirmModal({
                        button: button,
                        inventoryId: inventoryId,
                        nameSelect: nameSelect,
                        divisionSelect: divisionSelect,
                        status: status
                    });
                });
            });

            confirmYes.addEventListener('click', function() {
                const updateData = pendingDivisionUpdate;
                closeConfirmModal();
                if (updateData) {
                    saveDivisionUpdate(updateData);
                }
            });

            confirmNo.addEventListener('click', function() {
                closeConfirmModal(true);
            });

            confirmModal.addEventListener('mousedown', function(event) {
                if (event.target === confirmModal) {
                    closeConfirmModal(true);
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && confirmModal.classList.contains('is-open')) {
                    closeConfirmModal(true);
                }
            });
        });
    </script>
</body>
</html>
