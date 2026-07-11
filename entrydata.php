<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection is not available. Please check connect.php.');
}

if ($conn->connect_error) {
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error));
}

$conn->set_charset('utf8mb4');

// Check if there is saved form data from a previous submission
$saved_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_data']); // Clear the saved data after retrieving it

// --- 1. Fetch employee data for DATALISTS (Searchable Inputs) ---
$user_options = "";
$officeSRF = isset($_SESSION['OfficeSRF']) ? $_SESSION['OfficeSRF'] : 'DefaultOffice';

$sql_employees = "SELECT id, full_name FROM inventory_people WHERE office = ? ORDER BY full_name ASC";
$stmt_employees = $conn->prepare($sql_employees);
if ($stmt_employees) {
    $stmt_employees->bind_param("s", $officeSRF);
    $stmt_employees->execute();
    $result_employees = $stmt_employees->get_result();

    if ($result_employees->num_rows > 0) {
        while ($row_employee = $result_employees->fetch_assoc()) {
            // For datalists, we only need the option value, no 'selected' logic needed here
            $user_options .= "<option value='" . htmlspecialchars($row_employee['full_name']) . "'>";
        }
    }
    $stmt_employees->close();
} else {
    $user_options = "<option value='Unable to load employee list'>";
    error_log('entrydata.php employee query prepare failed: ' . $conn->error);
}

// --- 2. Fetch office/station data for STANDARD SELECT (Dropdown) ---
$office_options = "";
$sql_offices = "SELECT officeDivision FROM office_divisions WHERE office = ? ORDER BY officeDivision ASC";
$stmt_offices = $conn->prepare($sql_offices);
if ($stmt_offices) {
    $stmt_offices->bind_param("s", $officeSRF);
    $stmt_offices->execute();
    $result_offices = $stmt_offices->get_result();

    if ($result_offices->num_rows > 0) {
        while ($row_office = $result_offices->fetch_assoc()) {
            // Logic for standard Select Dropdown
            $selected = (isset($saved_data['officeDivision']) && $saved_data['officeDivision'] == $row_office['officeDivision']) ? 'selected' : '';
            $office_options .= "<option value='" . htmlspecialchars($row_office['officeDivision']) . "' $selected>" . htmlspecialchars($row_office['officeDivision']) . "</option>";
        }
    } else {
        $office_options = "<option value=''>No offices found</option>";
    }
    $stmt_offices->close();
} else {
    $office_options = "<option value=''>Error fetching offices</option>";
    error_log('entrydata.php office division query prepare failed: ' . $conn->error);
}

// --- 3. Fetch equipment data ---
$equipment_options = "";
$sql_equipment = "SELECT DISTINCT equipmentType FROM inv_inventory WHERE equipmentType IS NOT NULL AND TRIM(equipmentType) != '' ORDER BY equipmentType ASC";
$result_equipment = $conn->query($sql_equipment);

if ($result_equipment) {
    $used = [];
    if ($result_equipment->num_rows > 0) {
        while ($row_equipment = $result_equipment->fetch_assoc()) {
            $equipment = trim($row_equipment['equipmentType']);
            if ($equipment !== '' && !in_array(strtolower($equipment), $used)) {
                $equipment_options .= "<option value='" . htmlspecialchars($equipment) . "'>" . htmlspecialchars($equipment) . "</option>";
                $used[] = strtolower($equipment);
            }
        }
    } else {
        $equipment_options = "<option disabled>No equipment available</option>";
    }
    $result_equipment->free();
} else {
    $equipment_options = "<option disabled>Error fetching equipment</option>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ICT Equipment Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 20px;
        }

        .card-header {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 10px 10px 0 0 !important;
        }

        .section-title {
            color: var(--secondary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .required:after {
            content: " *";
            color: var(--accent-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .form-control,
        .form-select {
            border-radius: 6px;
            padding: 10px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }

        .action-buttons {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
            position: sticky;
            bottom: 20px;
        }

        .wizard-progress {
            background-color: white;
            padding: 16px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .wizard-progress .progress {
            height: 10px;
            border-radius: 999px;
        }

        .wizard-progress .progress-bar {
            border-radius: 999px;
        }

        .wizard-step {
            display: none;
        }

        .wizard-step.is-active {
            display: block;
        }

        .wizard-step.section-invalid .card-header {
            background-color: #c0392b !important;
        }

        .wizard-step .card-footer {
            padding-top: 0;
        }

        .step-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.85);
        }

        .step-number {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            background: rgba(255, 255, 255, 0.16);
            color: inherit;
        }

        .form-control.is-invalid,
        .form-select.is-invalid,
        textarea.form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .spec-input {
            display: none;
            margin-top: 5px;
            width: 100%;
        }

        .specs-container {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .specs-container h6 {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-active {
            background-color: #2ecc71;
        }

        .header-section {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .brand-dropdown {
            display: flex;
            gap: 10px;
        }

        .brand-dropdown select {
            flex: 1;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-0"><i class="fas fa-laptop me-2"></i>ICT Equipment Inventory</h1>
                    <p class="mb-0">Enter details for new ICT equipment to keep your inventory updated</p>
                </div>
                <div class="d-flex align-items-center">
                    <span class="status-indicator status-active"></span>
                    <span class="fw-medium">Creating New Record</span>
                </div>
            </div>
        </div>

        <div class="wizard-progress">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <div class="step-title" id="wizardStepLabel">Step 1 of 6</div>
                    <div class="fw-semibold" id="wizardStepName">Basic Information</div>
                </div>
                <div class="step-number" id="wizardStepCounter">1 / 6</div>
            </div>
            <div class="progress">
                <div class="progress-bar" id="wizardProgressBar" role="progressbar" style="width: 16.66%;" aria-valuenow="1" aria-valuemin="1" aria-valuemax="6"></div>
            </div>
        </div>

        <form id="ictEquipmentForm" action="entrydatahandler.php" method="post">
            <div class="card wizard-step" data-step="0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Basic Information</span>
                    <small class="step-number">Step 1 of 6</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Employee's Name</label>
                                <input type="text" class="form-control" id="employeeName" name="employeeName" list="employeeList" placeholder="Type to search..." value="<?php echo htmlspecialchars($saved_data['employeeName'] ?? ''); ?>" required autocomplete="off">
                                <datalist id="employeeList">
                                    <option value="N/A">
                                    <?php echo $user_options; ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Type of ICT Equipment</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="equipmentType" name="equipmentType" value="<?php echo htmlspecialchars($saved_data['equipmentType'] ?? ''); ?>" readonly required>
                                    <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        Action
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">Add</a></li>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#selectEquipmentModal">Select Equipment</a></li>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editEquipmentModal">Edit Equipment</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Year Acquired</label>
                                <input type="text" class="form-control" id="yearAcquired" name="yearAcquired" maxlength="4" value="<?php echo htmlspecialchars($saved_data['yearAcquired'] ?? ''); ?>" required oninput="calculateShelfLife(this.value)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Shelf Life</label>
                                <select class="form-select" id="shelfLife" name="shelfLife" required>
                                    <option value="" disabled selected>-- Select Shelf Life --</option>
                                    <option value="within5years" <?php echo ($saved_data['shelfLife'] ?? '') === 'within5years' ? 'selected' : ''; ?>>Within 5 years</option>
                                    <option value="beyond5years" <?php echo ($saved_data['shelfLife'] ?? '') === 'beyond5years' ? 'selected' : ''; ?>>Beyond 5 years</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Select Brand</label>
                                <select class="form-select" id="brandSelect" name="brandSelect" onchange="updateBrandField()">
                                    <option value="" disabled selected>Select a brand</option>
                                    <?php 
                                        $brands = ["Other", "Dell", "HP", "Lenovo", "Asus", "Acer", "Apple", "Microsoft", "Samsung", "Toshiba", "Oppo", "Xiaomi", "Sony", "LG", "Epson"];
                                        foreach($brands as $brand) {
                                            $selected = (isset($saved_data['brand']) && $saved_data['brand'] == $brand) ? 'selected' : '';
                                            echo "<option value='$brand' $selected>$brand</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($saved_data['brand'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                        <button type="button" class="btn btn-lg btn-outline-secondary" onclick="resetForm()">
                            <i class="fas fa-undo me-2"></i>Clear
                        </button>
                        <button type="button" class="btn btn-lg btn-primary" onclick="goToNextStep()">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card wizard-step" data-step="1">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Specifications / Descriptions</span>
                    <small class="step-number">Step 2 of 6</small>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Computer Specs</label>
                        <textarea class="form-control" id="computer_specs" name="computer_specs" rows="3" placeholder="1.MODEL, 2.PROCESSOR For Desktop & Laptop -eg. i7-10700T-, 3.INSTALLED MEMORY RAM SIZE, 4.COMPUTER NAME. 5.VideoCard"><?php echo htmlspecialchars($saved_data['computer_specs'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Specifications / Descriptions</label>
                        <textarea class="form-control" id="specifications" name="specifications" rows="5" required><?php echo htmlspecialchars($saved_data['specifications'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="specs-container">
                                <h6>Storage Options</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="hdd" name="storage_option" value="HDD" onchange="toggleSpecInput(this, 'hdd-capacity')" <?php echo isset($saved_data['hdd-capacity']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="hdd">HDD</label>
                                    <input type="text" id="hdd-capacity" class="spec-input form-control mt-2" name="hdd-capacity" placeholder="HDD Capacity (e.g., 1 TB)" value="<?php echo htmlspecialchars($saved_data['hdd-capacity'] ?? ''); ?>" style="<?php echo isset($saved_data['hdd-capacity']) ? 'display: block;' : 'display: none;'; ?>">
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="ssd" name="storage_option" value="SSD" onchange="toggleSpecInput(this, 'ssd-capacity')" <?php echo isset($saved_data['ssd-capacity']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="ssd">SSD</label>
                                    <input type="text" id="ssd-capacity" class="spec-input form-control mt-2" name="ssd-capacity" placeholder="SSD Capacity (e.g., 512 GB)" value="<?php echo htmlspecialchars($saved_data['ssd-capacity'] ?? ''); ?>" style="<?php echo isset($saved_data['ssd-capacity']) ? 'display: block;' : 'display: none;'; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="specs-container">
                                <h6>RAM Options</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ram" name="ram_option" onchange="toggleSpecInput(this, 'ram-capacity')" <?php echo isset($saved_data['ram-capacity']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="ram">RAM</label>
                                    <input type="text" id="ram-capacity" class="spec-input form-control mt-2" name="ram-capacity" placeholder="RAM Capacity (e.g., 16 GB)" value="<?php echo htmlspecialchars($saved_data['ram-capacity'] ?? ''); ?>" style="<?php echo isset($saved_data['ram-capacity']) ? 'display: block;' : 'display: none;'; ?>">
                                </div>
                            </div>
                            
                            <div class="specs-container mt-3">
                                <h6>Processor Options</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="processor" name="processor_option" onchange="toggleSpecInput(this, 'processor-type')" <?php echo isset($saved_data['processor-type']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="processor">Processor</label>
                                    <input type="text" id="processor-type" class="spec-input form-control mt-2" name="processor-type" placeholder="Processor (e.g., Intel i7, M1)" value="<?php echo htmlspecialchars($saved_data['processor-type'] ?? ''); ?>" style="<?php echo isset($saved_data['processor-type']) ? 'display: block;' : 'display: none;'; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="specs-container">
                                <h6>Display Options</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="display" name="display_option" onchange="toggleSpecInput(this, ['display-size', 'display-resolution'])" <?php echo (isset($saved_data['display-size']) || isset($saved_data['display-resolution'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="display">Display</label>
                                    <input type="text" id="display-size" class="spec-input form-control mt-2" name="display-size" placeholder="Display Size (e.g., 15.6 inch)" value="<?php echo htmlspecialchars($saved_data['display-size'] ?? ''); ?>" style="<?php echo (isset($saved_data['display-size']) || isset($saved_data['display-resolution'])) ? 'display: block;' : 'display: none;'; ?>">
                                    <input type="text" id="display-resolution" class="spec-input form-control mt-2" name="display-resolution" placeholder="Resolution (e.g., 1920x1080)" value="<?php echo htmlspecialchars($saved_data['display-resolution'] ?? ''); ?>" style="<?php echo (isset($saved_data['display-size']) || isset($saved_data['display-resolution'])) ? 'display: block;' : 'display: none;'; ?>">
                                </div>
                            </div>
                            
                            <div class="specs-container mt-3">
                                <h6>Battery Options</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="battery" name="battery_option" onchange="toggleSpecInput(this, 'battery-capacity')" <?php echo isset($saved_data['battery-capacity']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="battery">Battery</label>
                                    <input type="text" id="battery-capacity" class="spec-input form-control mt-2" name="battery-capacity" placeholder="Battery Capacity (e.g., 5000 mAh)" value="<?php echo htmlspecialchars($saved_data['battery-capacity'] ?? ''); ?>" style="<?php echo isset($saved_data['battery-capacity']) ? 'display: block;' : 'display: none;'; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="specs-container">
                        <h6>Operating System</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="os" name="os_option" onchange="toggleSpecInput(this, 'os-type')" <?php echo isset($saved_data['os-type']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="os">Operating System</label>
                            <input type="text" id="os-type" class="spec-input form-control mt-2" name="os-type" placeholder="OS (e.g., Windows 11, macOS, Android)" value="<?php echo htmlspecialchars($saved_data['os-type'] ?? ''); ?>" style="<?php echo isset($saved_data['os-type']) ? 'display: block;' : 'display: none;'; ?>">
                        </div>
                        <div class="mt-2" id="os-status-container" style="<?php echo isset($saved_data['os-type']) ? 'display: block;' : 'display: none;'; ?>">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="os-status" id="os-evaluation" value="Evaluation Copy" <?php echo (isset($saved_data['os-status']) && $saved_data['os-status'] === 'Evaluation Copy') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="os-evaluation">Evaluation Copy</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="os-status" id="os-genuine" value="Genuine" <?php echo (isset($saved_data['os-status']) && $saved_data['os-status'] === 'Genuine') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="os-genuine">Genuine</label>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                        <button type="button" class="btn btn-lg btn-outline-secondary" onclick="goToPreviousStep()">
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button type="button" class="btn btn-lg btn-primary" onclick="goToNextStep()">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card wizard-step" data-step="2">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Identification Details</span>
                    <small class="step-number">Step 3 of 6</small>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label required">Range Category (for Computers)</label>
                        <select class="form-select" id="rangeCategory" name="rangeCategory" required>
                            <option value="" disabled selected>Select Range Category</option>
                            <option value="Entry Level" <?php echo ($saved_data['rangeCategory'] ?? '') === 'Entry Level' ? 'selected' : ''; ?>>Entry Level</option>
                            <option value="Mid Range Level" <?php echo ($saved_data['rangeCategory'] ?? '') === 'Mid Range Level' ? 'selected' : ''; ?>>Mid Range Level</option>
                            <option value="High End" <?php echo ($saved_data['rangeCategory'] ?? '') === 'High End' ? 'selected' : ''; ?>>High End</option>
                            <option value="N/A" <?php echo ($saved_data['rangeCategory'] ?? '') === 'N/A' ? 'selected' : ''; ?>>N/A</option>          
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Serial Number</label>
                                <input type="text" class="form-control" id="serialNumber" name="serialNumber" value="<?php echo htmlspecialchars($saved_data['serialNumber'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Property Number</label>
                                <input type="text" class="form-control" id="propertyNumber" name="propertyNumber" value="<?php echo htmlspecialchars($saved_data['propertyNumber'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                        <button type="button" class="btn btn-lg btn-outline-secondary" onclick="goToPreviousStep()">
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button type="button" class="btn btn-lg btn-primary" onclick="goToNextStep()">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card wizard-step" data-step="3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Software Information</span>
                    <small class="step-number">Step 4 of 6</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Software Installed</label>
                                <textarea class="form-control" id="softwareInstalled" name="softwareInstalled" rows="3"><?php echo htmlspecialchars($saved_data['softwareInstalled'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Licensing Model</label>
                                <select class="form-select" id="licensingModel" name="licensingModel">
                                    <option value="" selected>-- Select Licensing Model --</option>
                                    <option value="Perpetual" <?php echo ($saved_data['licensingModel'] ?? '') === 'Perpetual' ? 'selected' : ''; ?>>Perpetual</option>
                                    <option value="Subscription" <?php echo ($saved_data['licensingModel'] ?? '') === 'Subscription' ? 'selected' : ''; ?>>Subscription</option>
                                    <option value="OEM" <?php echo ($saved_data['licensingModel'] ?? '') === 'OEM' ? 'selected' : ''; ?>>OEM</option>
                                    <option value="Volume" <?php echo ($saved_data['licensingModel'] ?? '') === 'Volume' ? 'selected' : ''; ?>>Volume</option>
                                    <option value="Other" <?php echo ($saved_data['licensingModel'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                        <button type="button" class="btn btn-lg btn-outline-secondary" onclick="goToPreviousStep()">
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button type="button" class="btn btn-lg btn-primary" onclick="goToNextStep()">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card wizard-step" data-step="4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Personnel Information</span>
                    <small class="step-number">Step 5 of 6</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Accountable Person</label>
                                <input type="text" class="form-control" id="accountablePerson" name="accountablePerson" list="accountablePersonList" placeholder="Type to search..." value="<?php echo htmlspecialchars($saved_data['accountablePerson'] ?? ''); ?>" required autocomplete="off">
                                <datalist id="accountablePersonList">
                                    <option value="N/A">
                                    <?php echo $user_options; ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Gender</label>
                                <select class="form-select" id="sex" name="sex" required>
                                    <option value="" disabled selected>-- Select Gender --</option>
                                    <option value="Male" <?php echo ($saved_data['sex'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($saved_data['sex'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($saved_data['sex'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    <option value="N/A" <?php echo ($saved_data['sex'] ?? '') === 'N/A' ? 'selected' : ''; ?>>N/A</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Office / Division</label>
                                <select class="form-select" id="officeDivision" name="officeDivision" required>
                                    <option value="" disabled selected>Select Office / Division</option>
                                    <?php echo $office_options; ?>
                                    <option value="N/A" <?php echo ($saved_data['officeDivision'] ?? '') === 'N/A' ? 'selected' : ''; ?>>N/A</option>
                                    <option value="COA">COA</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Status of Employment</label>
                                <select class="form-select" id="statusOfEmployment" name="statusOfEmployment" required>
                                    <option value="" disabled selected>-- Select Status --</option>
                                    <option value="Permanent" <?php echo ($saved_data['statusOfEmployment'] ?? '') === 'Permanent' ? 'selected' : ''; ?>>Permanent</option>
                                    <option value="Temporary" <?php echo ($saved_data['statusOfEmployment'] ?? '') === 'Temporary' ? 'selected' : ''; ?>>Temporary</option>
                                    <option value="Contractual" <?php echo ($saved_data['statusOfEmployment'] ?? '') === 'Contractual' ? 'selected' : ''; ?>>Contractual</option>
                                    <option value="Others" <?php echo ($saved_data['statusOfEmployment'] ?? '') === 'Others' ? 'selected' : ''; ?>>Others</option>
                                    <option value="N/A" <?php echo ($saved_data['statusOfEmployment'] ?? '') === 'N/A' ? 'selected' : ''; ?>>N/A</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-top">
                        <h5 class="section-title">Actual User Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Actual User</label>
                                    <input type="text" class="form-control" id="actualUser" name="actualUser" list="actualUserList" placeholder="Type to search..." value="<?php echo htmlspecialchars($saved_data['actualUser'] ?? ''); ?>" required autocomplete="off">
                                    <datalist id="actualUserList">
                                        <option value="N/A">
                                        <?php echo $user_options; ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Sex</label>
                                    <select class="form-select" id="actualUserSex" name="actualUserSex" required>
                                        <option value="" disabled selected>-- Select Gender --</option>
                                        <option value="Male" <?php echo ($saved_data['actualUserSex'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($saved_data['actualUserSex'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($saved_data['actualUserSex'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                        <option value="N/A" <?php echo ($saved_data['actualUserSex'] ?? '') === 'N/A' ? 'selected' : ''; ?>>N/A</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Status of Employment</label>
                                    <select class="form-select" id="actualUserStatusOfEmployment" name="actualUserStatusOfEmployment" required>
                                        <option value="" disabled selected>-- Select Status --</option>
                                        <option value="Permanent" <?php echo ($saved_data['actualUserStatusOfEmployment'] ?? '') === 'Permanent' ? 'selected' : ''; ?>>Permanent</option>
                                        <option value="Temporary" <?php echo ($saved_data['actualUserStatusOfEmployment'] ?? '') === 'Temporary' ? 'selected' : ''; ?>>Temporary</option>
                                        <option value="Contractual" <?php echo ($saved_data['actualUserStatusOfEmployment'] ?? '') === 'Contractual' ? 'selected' : ''; ?>>Contractual</option>
                                        <option value="N/A" <?php echo ($saved_data['actualUserStatusOfEmployment'] ?? '') === 'N/A' ? 'selected' : ''; ?>>N/A</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Nature of Work</label>
                                    <input type="text" class="form-control" id="natureOfWork" name="natureOfWork" value="<?php echo htmlspecialchars($saved_data['natureOfWork'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                        <button type="button" class="btn btn-lg btn-outline-secondary" onclick="goToPreviousStep()">
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button type="button" class="btn btn-lg btn-primary" onclick="goToNextStep()">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card wizard-step" data-step="5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Additional Information</span>
                    <small class="step-number">Step 6 of 6</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($saved_data['remarks'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Amount (₱)</label>
                                <input type="text" class="form-control" id="amount" name="amount" value="<?php echo htmlspecialchars($saved_data['amount'] ?? ''); ?>" required oninput="formatAmount(this); calculateAndDisplayDepreciation();">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Depreciation Value (₱)</label>
                                <input type="text" class="form-control" name="depreciation_value" id="depreciation_value" readonly value="<?php echo htmlspecialchars($saved_data['depreciation_value'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="mark_as_done" id="mark_as_done" value="1" <?php echo isset($saved_data['mark_as_done']) && $saved_data['mark_as_done'] === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="mark_as_done">Mark as Complete</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                        <button type="button" class="btn btn-lg btn-outline-secondary" onclick="goToPreviousStep()">
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button type="button" class="btn btn-lg btn-primary" onclick="openSaveInventoryConfirmModal()">
                            <i class="fas fa-save me-2"></i>Save Inventory
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="saveInventoryConfirmModal" tabindex="-1" aria-labelledby="saveInventoryConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="saveInventoryConfirmModalLabel">Confirm Inventory Entry</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Please review the summary below before saving this inventory record.</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <tbody id="saveInventorySummaryBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmSaveInventory()">Confirm Save</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    echo "<div class='modal fade' id='addEquipmentModal' tabindex='-1' aria-hidden='true'>
        <div class='modal-dialog'>
            <form id='addEquipmentForm' method='POST' action='addequipmenthandler.php'>
                <div class='modal-content'>
                    <div class='modal-header bg-info text-white'>
                        <h5 class='modal-title'>Add Equipment</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        <div class='form-group'>
                            <label for='equipmentName'>Name of Equipment</label>
                            <input type='text' name='equipment_name' id='equipmentName' class='form-control' required />
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                        <button type='submit' class='btn btn-primary'>Add Equipment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>";

    echo "<div class='modal fade' id='selectEquipmentModal' tabindex='-1' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header bg-info text-white'>
                    <h5 class='modal-title'>Select Equipment</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    <div class='form-group'>
                        <label for='equipmentSelect'>Select Equipment</label>
                        <select name='equipment' id='equipmentSelect' class='form-control' required onchange='updateEquipmentType()'>
                            <option value=''>-- Select Equipment --</option>
                            <option value='N/A'>N/A</option>
                            $equipment_options
                        </select>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' data-bs-dismiss='modal'>OK</button>
                </div>
            </div>
        </div>
    </div>";

    echo "<div class='modal fade' id='editEquipmentModal' tabindex='-1' aria-hidden='true'>
        <div class='modal-dialog'>
            <form method='POST' action='editequipmenthandler.php'>
                <div class='modal-content'>
                    <div class='modal-header bg-info text-white'>
                        <h5 class='modal-title'>Edit Equipment</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        <div class='form-group mb-3'>
                            <label for='equipmentSelectEdit'>Select Equipment to Edit</label>
                            <select name='equipment_old_name' id='equipmentSelectEdit' class='form-control' required onchange='populateEditEquipmentField()'>
                                <option value=''>-- Select Equipment --</option>
                                <option value='N/A'>N/A</option>
                                $equipment_options
                            </select>
                        </div>
                        <div class='form-group'>
                            <label for='editEquipmentName'>Edit Equipment Name</label>
                            <input type='text' id='editEquipmentName' name='equipment_new_name' class='form-control' value='' required>
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                        <button type='submit' class='btn btn-primary'>Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>";
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const wizardStepNames = [
            'Basic Information',
            'Specifications / Descriptions',
            'Identification Details',
            'Software Information',
            'Personnel Information',
            'Additional Information'
        ];

        let currentWizardStep = 0;

        function getWizardSteps() {
            return Array.from(document.querySelectorAll('.wizard-step'));
        }

        function isElementVisible(element) {
            return !!(element && element.getClientRects().length);
        }

        function clearFieldError(field) {
            if (!field) {
                return;
            }

            field.classList.remove('is-invalid');
        }

        function setFieldError(field) {
            if (!field) {
                return;
            }

            field.classList.add('is-invalid');
        }

        function clearStepErrors(stepElement) {
            if (!stepElement) {
                return;
            }

            stepElement.classList.remove('section-invalid');
            stepElement.querySelectorAll('.is-invalid').forEach(clearFieldError);

            const osStatusContainer = stepElement.querySelector('#os-status-container');
            if (osStatusContainer) {
                osStatusContainer.classList.remove('is-invalid');
                osStatusContainer.style.border = '';
                osStatusContainer.style.borderRadius = '';
                osStatusContainer.style.padding = '';
            }
        }

        function updateWizardHeader(stepIndex) {
            const stepLabel = document.getElementById('wizardStepLabel');
            const stepName = document.getElementById('wizardStepName');
            const stepCounter = document.getElementById('wizardStepCounter');
            const progressBar = document.getElementById('wizardProgressBar');
            const totalSteps = wizardStepNames.length;

            if (stepLabel) {
                stepLabel.textContent = `Step ${stepIndex + 1} of ${totalSteps}`;
            }

            if (stepName) {
                stepName.textContent = wizardStepNames[stepIndex] || '';
            }

            if (stepCounter) {
                stepCounter.textContent = `${stepIndex + 1} / ${totalSteps}`;
            }

            if (progressBar) {
                const percent = ((stepIndex + 1) / totalSteps) * 100;
                progressBar.style.width = `${percent}%`;
                progressBar.setAttribute('aria-valuenow', String(stepIndex + 1));
            }
        }

        function showStep(stepIndex) {
            const steps = getWizardSteps();
            const totalSteps = steps.length;
            const normalizedIndex = Math.max(0, Math.min(stepIndex, totalSteps - 1));

            currentWizardStep = normalizedIndex;

            steps.forEach((step, index) => {
                step.classList.toggle('is-active', index === normalizedIndex);
                step.setAttribute('aria-hidden', index === normalizedIndex ? 'false' : 'true');
            });

            updateWizardHeader(normalizedIndex);
        }

        function getFirstInvalidField(stepElement, ignoreVisibility = false) {
            if (!stepElement) {
                return null;
            }

            const candidates = Array.from(stepElement.querySelectorAll('input, select, textarea'));

            for (const field of candidates) {
                if (field.disabled || (!ignoreVisibility && !isElementVisible(field))) {
                    continue;
                }

                if (field.type === 'hidden' || field.type === 'button' || field.type === 'submit') {
                    continue;
                }

                if (field.required) {
                    if (field.type === 'radio') {
                        const checked = document.querySelector(`input[name="${field.name}"]:checked`);
                        if (!checked) {
                            return field;
                        }
                    } else if (field.type === 'checkbox') {
                        if (!field.checked) {
                            return field;
                        }
                    } else if (field.value.trim() === '') {
                        return field;
                    }
                }
            }

            return null;
        }

        function validateVisibleConditionalField(field, ignoreVisibility = false) {
            if (!field || field.disabled || (!ignoreVisibility && !isElementVisible(field))) {
                return true;
            }

            if (field.type === 'radio') {
                const checked = document.querySelector(`input[name="${field.name}"]:checked`);
                return !!checked;
            }

            return field.value.trim() !== '';
        }

        function validateSpecificationsStep(stepElement, ignoreVisibility = false) {
            const conditionalFields = [
                ['hdd', 'hdd-capacity'],
                ['ssd', 'ssd-capacity'],
                ['ram', 'ram-capacity'],
                ['processor', 'processor-type'],
                ['display', 'display-size'],
                ['display', 'display-resolution'],
                ['battery', 'battery-capacity'],
                ['os', 'os-type']
            ];

            let firstInvalid = null;

            conditionalFields.forEach(([checkboxId, fieldId]) => {
                const checkbox = document.getElementById(checkboxId);
                const field = document.getElementById(fieldId);

                if (!checkbox || !field || !checkbox.checked || (!ignoreVisibility && !isElementVisible(field))) {
                    return;
                }

                if (!validateVisibleConditionalField(field, ignoreVisibility)) {
                    setFieldError(field);
                    if (!firstInvalid) {
                        firstInvalid = field;
                    }
                }
            });

            const osCheckbox = document.getElementById('os');
            if (osCheckbox && osCheckbox.checked) {
                const osType = document.getElementById('os-type');
                const osRadio = document.querySelector('input[name="os-status"]');
                const osSelected = document.querySelector('input[name="os-status"]:checked');

                if (osType && isElementVisible(osType) && osType.value.trim() === '') {
                    setFieldError(osType);
                    if (!firstInvalid) {
                        firstInvalid = osType;
                    }
                }

                if (osRadio && !osSelected) {
                    const osStatusContainer = document.getElementById('os-status-container');
                    if (osStatusContainer) {
                        osStatusContainer.classList.add('is-invalid');
                        osStatusContainer.style.border = '1px solid #dc3545';
                        osStatusContainer.style.borderRadius = '8px';
                        osStatusContainer.style.padding = '8px';
                    }
                    if (!firstInvalid) {
                        firstInvalid = osRadio;
                    }
                }
            }

            return firstInvalid;
        }

        function validateStep(stepIndex, ignoreVisibility = false) {
            const steps = getWizardSteps();
            const stepElement = steps[stepIndex];

            if (!stepElement) {
                return { valid: true, field: null };
            }

            clearStepErrors(stepElement);

            let firstInvalidField = getFirstInvalidField(stepElement, ignoreVisibility);

            if (stepIndex === 1) {
                const specsInvalid = validateSpecificationsStep(stepElement, ignoreVisibility);
                if (specsInvalid && !firstInvalidField) {
                    firstInvalidField = specsInvalid;
                }
            }

            if (firstInvalidField) {
                const targetStep = firstInvalidField.closest('.wizard-step') || stepElement;
                targetStep.classList.add('section-invalid');
                setFieldError(firstInvalidField);
                return { valid: false, field: firstInvalidField };
            }

            return { valid: true, field: null };
        }

        function validateAllSteps() {
            const steps = getWizardSteps();

            for (let index = 0; index < steps.length; index++) {
                const result = validateStep(index, true);
                if (!result.valid) {
                    return { valid: false, stepIndex: index, field: result.field };
                }
            }

            return { valid: true, stepIndex: -1, field: null };
        }

        function focusInvalidField(field) {
            if (!field) {
                return;
            }

            setTimeout(() => {
                if (field.focus) {
                    field.focus({ preventScroll: true });
                }
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 50);
        }

        function goToNextStep() {
            const result = validateStep(currentWizardStep);
            if (!result.valid) {
                focusInvalidField(result.field);
                return;
            }

            showStep(currentWizardStep + 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function goToPreviousStep() {
            if (currentWizardStep === 0) {
                return;
            }

            showStep(currentWizardStep - 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Function to update the brand input field based on dropdown selection
        function updateBrandField() {
            var selectedBrand = document.getElementById("brandSelect").value;
            var brandInput = document.getElementById("brand");
            
            if (selectedBrand === "Other") {
                brandInput.value = "";
                brandInput.placeholder = "Please enter the brand";
            } else {
                brandInput.value = selectedBrand;
            }
        }

        // Function to update the equipmentType textbox with the selected value from the dropdown
        function updateEquipmentType() {
            var selectedEquipment = document.getElementById('equipmentSelect').value;
            document.getElementById('equipmentType').value = selectedEquipment;
            $('#selectEquipmentModal').modal('hide');
        }

        // Function to populate the edit field with the selected equipment
        function populateEditEquipmentField() {
            var selectedEquipment = document.getElementById('equipmentSelectEdit').value;
            document.getElementById('editEquipmentName').value = selectedEquipment;
        }

        // Specifications generation logic
        function toggleSpecInput(checkbox, inputId) {
            if (Array.isArray(inputId)) {
                inputId.forEach(id => {
                    const input = document.getElementById(id);
                    if (input) {
                        input.style.display = checkbox.checked ? 'block' : 'none';
                        input.required = checkbox.checked;
                        if (!checkbox.checked) input.value = '';
                    }
                });
            } else {
                const input = document.getElementById(inputId);
                if (input) {
                    input.style.display = checkbox.checked ? 'block' : 'none';
                    input.required = checkbox.checked;
                    if (!checkbox.checked) input.value = '';
                }
            }
            
            // Special handling for OS status radios
            if (checkbox.id === 'os') {
                const osStatusContainer = document.getElementById('os-status-container');
                if (osStatusContainer) {
                    osStatusContainer.style.display = checkbox.checked ? 'block' : 'none';
                    if (!checkbox.checked) {
                        document.querySelectorAll('input[name="os-status"]').forEach(radio => {
                            radio.checked = false;
                            radio.required = false;
                        });
                    } else {
                        document.querySelectorAll('input[name="os-status"]').forEach(radio => {
                            radio.required = true;
                        });
                    }
                }
            }

            updateSpecifications();
        }

        function syncConditionalFieldStates() {
            const mappings = [
                ['hdd', 'hdd-capacity'],
                ['ssd', 'ssd-capacity'],
                ['ram', 'ram-capacity'],
                ['processor', 'processor-type'],
                ['display', ['display-size', 'display-resolution']],
                ['battery', 'battery-capacity'],
                ['os', 'os-type']
            ];

            mappings.forEach(([checkboxId, inputId]) => {
                const checkbox = document.getElementById(checkboxId);
                if (checkbox) {
                    toggleSpecInput(checkbox, inputId);
                }
            });
        }

        function updateSpecifications() {
            let specText = [];
            
            const hdd = document.getElementById('hdd');
            const hddCapacity = document.getElementById('hdd-capacity');
            if (hdd.checked && hddCapacity.value.trim()) {
                specText.push(`HDD: ${hddCapacity.value}`);
            }
            
            const ssd = document.getElementById('ssd');
            const ssdCapacity = document.getElementById('ssd-capacity');
            if (ssd.checked && ssdCapacity.value.trim()) {
                specText.push(`SSD: ${ssdCapacity.value}`);
            }
            
            const ram = document.getElementById('ram');
            const ramCapacity = document.getElementById('ram-capacity');
            if (ram.checked && ramCapacity.value.trim()) {
                specText.push(`RAM: ${ramCapacity.value}`);
            }
            
            const processor = document.getElementById('processor');
            const processorType = document.getElementById('processor-type');
            if (processor.checked && processorType.value.trim()) {
                specText.push(`Processor: ${processorType.value}`);
            }
            
            const display = document.getElementById('display');
            const displaySize = document.getElementById('display-size');
            const displayResolution = document.getElementById('display-resolution');
            if (display.checked && (displaySize.value.trim() || displayResolution.value.trim())) {
                let displayDetails = [];
                if (displaySize.value.trim()) displayDetails.push(displaySize.value);
                if (displayResolution.value.trim()) displayDetails.push(displayResolution.value);
                specText.push(`Display: ${displayDetails.join(', ')}`);
            }
            
            const battery = document.getElementById('battery');
            const batteryCapacity = document.getElementById('battery-capacity');
            if (battery.checked && batteryCapacity.value.trim()) {
                specText.push(`Battery: ${batteryCapacity.value}`);
            }
            
            const os = document.getElementById('os');
            const osType = document.getElementById('os-type');
            if (os.checked && osType.value.trim()) {
                let osInfo = `OS: ${osType.value}`;
                const osStatus = document.querySelector('input[name="os-status"]:checked');
                if (osStatus) {
                    osInfo += ` (${osStatus.value})`;
                }
                specText.push(osInfo);
            }
            
            document.getElementById('specifications').value = specText.join('\n');
        }

        // Amount formatting
        function formatAmount(input) {
            let value = input.value.replace(/[^0-9.]/g, '');
            let parts = value.split('.');
            let integerPart = parts[0];
            let decimalPart = parts[1] ? '.' + parts[1].slice(0, 2) : '';
            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            input.value = integerPart + decimalPart;
        }

        // Depreciation calculation
        function calculateShelfLife(year) {
            if(year.length === 4) {
                const currentYear = new Date().getFullYear();
                const yearDiff = currentYear - parseInt(year);
                const shelfLifeSelect = document.getElementById('shelfLife');
                if(yearDiff <= 5) {
                    shelfLifeSelect.value = 'within5years';
                } else {
                    shelfLifeSelect.value = 'beyond5years';
                }
                calculateAndDisplayDepreciation();
            }
        }

        function calculateAndDisplayDepreciation() {
            const yearAcquired = parseInt(document.getElementById('yearAcquired').value);
            let amount = parseFloat(document.getElementById('amount').value.replace(/[^0-9.]/g, ''));
            
            // Define salvage value and useful life
            const salvageValue = 5000;
            const usefulLife = 5;
            
            if (isNaN(yearAcquired) || isNaN(amount) || amount <= 0) {
                document.getElementById('depreciation_value').value = '';
                return;
            }
            
            const currentYear = new Date().getFullYear();
            const yearDiff = currentYear - yearAcquired;
            
            let depreciationValue = 0;
            if (yearDiff >= usefulLife) {
                depreciationValue = amount - salvageValue;
                if (depreciationValue < 0) {
                    depreciationValue = 0;
                }
            } else {
                let annualDepreciation = (amount - salvageValue) / usefulLife;
                if (annualDepreciation < 0) {
                    annualDepreciation = 0;
                }
                depreciationValue = annualDepreciation * yearDiff;
            }
            
            document.getElementById('depreciation_value').value = '₱' + depreciationValue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function getFieldValue(id, fallback = 'N/A') {
            const field = document.getElementById(id);
            if (!field) {
                return fallback;
            }

            const value = field.value.trim();
            return value === '' ? fallback : value;
        }

        function getSelectLabelValue(id, fallback = 'N/A') {
            const field = document.getElementById(id);
            if (!field || !field.selectedOptions || !field.selectedOptions.length) {
                return fallback;
            }

            const value = field.selectedOptions[0].textContent.trim();
            return value === '' ? fallback : value;
        }

        function getRadioValue(name, fallback = 'N/A') {
            const selected = document.querySelector(`input[name="${name}"]:checked`);
            if (!selected || !selected.value.trim()) {
                return fallback;
            }

            return selected.value.trim();
        }

        function buildSummaryRow(label, value) {
            return `
                <tr>
                    <th class="bg-light" style="width: 40%;">${escapeHtml(label)}</th>
                    <td style="white-space: pre-wrap;">${escapeHtml(value)}</td>
                </tr>
            `;
        }

        function updateSaveInventorySummary() {
            const summaryBody = document.getElementById('saveInventorySummaryBody');
            if (!summaryBody) {
                return;
            }

            const rows = [
                ['Employee Name', getFieldValue('employeeName')],
                ['Equipment Type', getFieldValue('equipmentType')],
                ['Year Acquired', getFieldValue('yearAcquired')],
                ['Shelf Life', getSelectLabelValue('shelfLife')],
                ['Brand', getFieldValue('brand')],
                ['Computer Specs', getFieldValue('computer_specs')],
                ['Specifications / Descriptions', getFieldValue('specifications')],
                ['HDD Capacity', getFieldValue('hdd-capacity')],
                ['SSD Capacity', getFieldValue('ssd-capacity')],
                ['RAM Capacity', getFieldValue('ram-capacity')],
                ['Processor Type', getFieldValue('processor-type')],
                ['Display Size', getFieldValue('display-size')],
                ['Display Resolution', getFieldValue('display-resolution')],
                ['Battery Capacity', getFieldValue('battery-capacity')],
                ['Operating System', getFieldValue('os-type')],
                ['OS Status', getRadioValue('os-status')],
                ['Range Category', getSelectLabelValue('rangeCategory')],
                ['Serial Number', getFieldValue('serialNumber')],
                ['Property Number', getFieldValue('propertyNumber')],
                ['Software Installed', getFieldValue('softwareInstalled')],
                ['Licensing Model', getSelectLabelValue('licensingModel')],
                ['Accountable Person', getFieldValue('accountablePerson')],
                ['Gender', getSelectLabelValue('sex')],
                ['Office / Division', getSelectLabelValue('officeDivision')],
                ['Status of Employment', getSelectLabelValue('statusOfEmployment')],
                ['Actual User', getFieldValue('actualUser')],
                ['Actual User Sex', getSelectLabelValue('actualUserSex')],
                ['Actual User Status of Employment', getSelectLabelValue('actualUserStatusOfEmployment')],
                ['Nature of Work', getFieldValue('natureOfWork')],
                ['Remarks', getFieldValue('remarks')],
                ['Amount', getFieldValue('amount')],
                ['Depreciation Value', getFieldValue('depreciation_value')],
                ['Mark as Complete', document.getElementById('mark_as_done') && document.getElementById('mark_as_done').checked ? 'Yes' : 'No']
            ];

            summaryBody.innerHTML = rows.map(([label, value]) => buildSummaryRow(label, value)).join('');
        }

        function openSaveInventoryConfirmModal() {
            const currentValidation = validateStep(currentWizardStep);
            if (!currentValidation.valid) {
                focusInvalidField(currentValidation.field);
                return;
            }

            updateSaveInventorySummary();
            const modalElement = document.getElementById('saveInventoryConfirmModal');
            if (window.jQuery && typeof $('#saveInventoryConfirmModal').modal === 'function') {
                $('#saveInventoryConfirmModal').modal('show');
                return;
            }

            if (window.bootstrap && bootstrap.Modal) {
                new bootstrap.Modal(modalElement).show();
                return;
            }

            modalElement.classList.add('show');
            modalElement.style.display = 'block';
            modalElement.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        }

        function confirmSaveInventory() {
            const validation = validateAllSteps();
            const form = document.getElementById('ictEquipmentForm');

            if (!validation.valid) {
                if (window.jQuery && typeof $('#saveInventoryConfirmModal').modal === 'function') {
                    $('#saveInventoryConfirmModal').modal('hide');
                } else if (window.bootstrap && bootstrap.Modal) {
                    const modalElement = document.getElementById('saveInventoryConfirmModal');
                    bootstrap.Modal.getInstance(modalElement)?.hide();
                }

                showStep(validation.stepIndex);
                focusInvalidField(validation.field);
                return;
            }

            form.submit();
        }

        // Reset form
        function resetForm() {
            document.getElementById('ictEquipmentForm').reset();
            // Hide all spec inputs
            document.querySelectorAll('.spec-input').forEach(input => {
                input.style.display = 'none';
                input.required = false;
                clearFieldError(input);
            });
            // Uncheck OS status radios
            document.querySelectorAll('input[name="os-status"]').forEach(radio => radio.checked = false);
            const osStatusContainer = document.getElementById('os-status-container');
            if (osStatusContainer) {
                osStatusContainer.classList.remove('is-invalid');
                osStatusContainer.style.border = '';
                osStatusContainer.style.borderRadius = '';
                osStatusContainer.style.padding = '';
            }
            // Clear depreciation value
            document.getElementById('depreciation_value').value = '';
            getWizardSteps().forEach(clearStepErrors);
            showStep(0);
        }

        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            syncConditionalFieldStates();
            showStep(0);

            // Attach event listeners to all specification checkboxes and inputs
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    let inputId = this.id;
                    switch(inputId) {
                        case 'hdd': toggleSpecInput(this, 'hdd-capacity'); break;
                        case 'ssd': toggleSpecInput(this, 'ssd-capacity'); break;
                        case 'ram': toggleSpecInput(this, 'ram-capacity'); break;
                        case 'processor': toggleSpecInput(this, 'processor-type'); break;
                        case 'display': toggleSpecInput(this, ['display-size', 'display-resolution']); break;
                        case 'battery': toggleSpecInput(this, 'battery-capacity'); break;
                        case 'os': toggleSpecInput(this, 'os-type'); break;
                    }
                });
            });
            
            // Attach input event listeners to all spec fields
            const specInputs = document.querySelectorAll('.spec-input, input[name="os-status"]');
            specInputs.forEach(input => {
                input.addEventListener('input', updateSpecifications);
            });
            
            // Initial call for depreciation calculation if page loads with values
            calculateAndDisplayDepreciation();
        });
    </script>
    
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>

    <script>
        // Function to show toast notification
        function showToast(message, type = 'error') {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            // Set toast styles based on type
            const backgroundColor = type === 'error' ? '#ff4444' : '#00C851';
            
            toast.style.cssText = `
                padding: 15px 25px;
                margin: 10px;
                background-color: ${backgroundColor};
                color: white;
                border-radius: 5px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                opacity: 0;
                transition: opacity 0.3s ease-in;
            `;
            
            toast.textContent = message;
            toastContainer.appendChild(toast);
            
            // Fade in
            setTimeout(() => {
                toast.style.opacity = '1';
            }, 10);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    toastContainer.removeChild(toast);
                }, 1000);
            }, 10000);
        }

        // Check for PHP session messages
        <?php
        if(isset($_SESSION['error'])) {
            echo "showToast('" . addslashes($_SESSION['error']) . "', 'error');";
            unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])) {
            echo "showToast('" . addslashes($_SESSION['success']) . "', 'success');";
            unset($_SESSION['success']);
        }
        ?>
    </script>
</body>
</html>
