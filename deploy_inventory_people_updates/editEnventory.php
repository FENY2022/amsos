<?php

require_once 'connect.php';


if (isset($_GET['id'])) {
    $inventory_id = $_GET['id'];
} else 

exit();


{




    // Fetch inventory details
    $sql = "SELECT * FROM inv_inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inventory_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $inventory = $result->fetch_assoc();

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $employeeName = $_POST['employeeName'];
        $equipmentType = $_POST['equipmentType'];
        $yearAcquired = $_POST['yearAcquired'];
        $shelfLife = $_POST['shelfLife'];
        $brand = $_POST['brand'];
        $specifications = $_POST['specifications'];
        $rangeCategory = $_POST['rangeCategory'];
        $softwareInstalled = $_POST['softwareInstalled'];
        $licensingModel = $_POST['licensingModel'];
        $softwareInstalled_2 = $_POST['softwareInstalled_2'];
        $licensingModel_2 = $_POST['licensingModel_2'];
        $serialNumber = $_POST['serialNumber'];
        $propertyNumber = $_POST['propertyNumber'];
        $accountablePerson = $_POST['accountablePerson'];
        $sex = $_POST['sex'];
        $officeDivision = $_POST['officeDivision'];
        $statusOfEmployment = $_POST['statusOfEmployment'];
        $actualUser = $_POST['actualUser'];
        $actualUserSex = $_POST['actualUserSex'];
        $actualUserStatusOfEmployment = $_POST['actualUserStatusOfEmployment'];
        $natureOfWork = $_POST['natureOfWork'];
        $remarks = $_POST['remarks'];
        $office = $_POST['office'];
        $amount = $_POST['amount'];
        $depreciation_value = $_POST['depreciation_value'];
        $mark_as_done = $_POST['mark_as_done'];

        $update_sql = "UPDATE inv_inventory SET 
                   employeeName = ?,
                   equipmentType = ?,
                   yearAcquired = ?,
                   shelfLife = ?,
                   brand = ?,
                   specifications = ?,
                   rangeCategory = ?,
                   softwareInstalled = ?,
                   licensingModel = ?,
                   softwareInstalled_2 = ?,
                   licensingModel_2 = ?,
                   serialNumber = ?,
                   propertyNumber = ?,
                   accountablePerson = ?,
                   sex = ?,
                   officeDivision = ?,
                   statusOfEmployment = ?,
                   actualUser = ?,
                   actualUserSex = ?,
                   actualUserStatusOfEmployment = ?,
                   natureOfWork = ?,
                   remarks = ?,
                   office = ?,
                   amount = ?,
                   depreciation_value = ?,
                   mark_as_done = ?
                   WHERE id = ?";

        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param(
            "sssssssssssssssssssssssiiis",
            $employeeName,
            $equipmentType,
            $yearAcquired,
            $shelfLife,
            $brand,
            $specifications,
            $rangeCategory,
            $softwareInstalled,
            $licensingModel,
            $softwareInstalled_2,
            $licensingModel_2,
            $serialNumber,
            $propertyNumber,
            $accountablePerson,
            $sex,
            $officeDivision,
            $statusOfEmployment,
            $actualUser,
            $actualUserSex,
            $actualUserStatusOfEmployment,
            $natureOfWork,
            $remarks,
            $office,
            $amount,
            $depreciation_value,
            $mark_as_done,
            $inventory_id
        );

        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Inventory record updated successfully!";
            header("Location: inventory.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating inventory record: " . $conn->error;
        }
    }
}


?>


<?php


// Replace 'your_table_name' with your actual table name
$table_name = "inv_inventory";


$sql = "SELECT * FROM $table_name where id = '$inventory_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {



        $id = $row['id'];
        $employeeName = $row['employeeName'];
        $equipmentType = $row['equipmentType'];
        $yearAcquired = $row['yearAcquired'];
        $shelfLife = $row['shelfLife'];
        $brand = $row['brand'];
        $specifications = $row['specifications'];
        $rangeCategory = $row['rangeCategory'];
        $softwareInstalled = $row['softwareInstalled'];
        $licensingModel = $row['licensingModel'];
        $softwareInstalled_2 = $row['softwareInstalled_2'];
        $licensingModel_2 = $row['licensingModel_2'];
        $serialNumber = $row['serialNumber'];
        $propertyNumber = $row['propertyNumber'];
        $accountablePerson = $row['accountablePerson'];
        $sex = $row['sex'];
        $officeDivision = $row['officeDivision'];
        $statusOfEmployment = $row['statusOfEmployment'];
        $actualUser = $row['actualUser'];
        $actualUserSex = $row['actualUserSex'];
        $actualUserStatusOfEmployment = $row['actualUserStatusOfEmployment'];
        $natureOfWork = $row['natureOfWork'];
        $remarks = $row['remarks'];
        $office = $row['office'];
        $amount = $row['amount'];
        $depreciation_value = $row['depreciation_value'];
        $mark_as_done = $row['mark_as_done'];
        $inventory_id = $row['id'];
        $officeEscaped = $conn->real_escape_string($office);
    }
} else {
    echo "0 results";
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inventory Record Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        .btn-danger {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
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

        .form-section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 15px;
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

        .status-inactive {
            background-color: #e74c3c;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-0"><i class="fas fa-edit me-2"></i>Edit Inventory Record</h1>
                <p class="text-muted">Update equipment information and assign to users</p>
            </div>
            <div class="d-flex align-items-center">
                <span class="status-indicator status-active"></span>
                <span class="text-success fw-medium">Active Record</span>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Equipment Information</span>
                <span class="badge bg-info">ID: <?php echo $propertyNumber; ?></span>
            </div>
            <div class="card-body">

                <form id="inventoryForm">

                    <input type="hidden" name="id" value="<?php echo $id; ?>" >
                    
                    <div class="row">
                        <div class="col-md-6">

                      

                        
                            <div class="form-group">
                                <label class="form-label required">Equipment Type</label>
                                <select class="form-select" name="equipmentType" required>
                                    <option value="">Select type</option>
                                    <?php
                                    $sql = "SELECT DISTINCT equipmentType FROM inv_inventory";
                                    $result = mysqli_query($conn, $sql);
                                    while ($row = mysqli_fetch_array($result)) {
                                        $selected = ($equipmentType == $row['equipmentType']) ? 'selected' : '';
                                        echo "<option value='" . $row['equipmentType'] . "' " . $selected . ">" . $row['equipmentType'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Brand</label>
                                <input type="text" class="form-control" name="brand" value="<?php echo $brand; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Serial Number</label>
                                <input type="text" class="form-control" name="serialNumber" value="<?php echo $serialNumber; ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Property Number</label>
                                <input type="text" class="form-control" name="propertyNumber" value="<?php echo $propertyNumber; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Specifications</label>
                        <textarea class="form-control" name="specifications" rows="3" required><?php echo $specifications; ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label required">Year Acquired</label>
                                <input type="text" class="form-control" name="yearAcquired" value="<?php echo $yearAcquired; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label required">Shelf Life (Years)</label>
                                <input type="text" class="form-control" name="shelfLife" value="<?php echo $shelfLife; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Range Category</label>
                                <select class="form-select" name="rangeCategory">
                                    <option value="" <?php echo ($rangeCategory == "") ? 'selected' : ''; ?>>Select category</option>
                                    <option value="Mid range level" <?php echo ($rangeCategory == "Mid range level") ? 'selected' : ''; ?>>Mid range level</option>
                                    <option value="Entry/ Basic Level" <?php echo ($rangeCategory == "Entry/ Basic Level") ? 'selected' : ''; ?>>Entry/ Basic Level</option>
                                    <option value="High end level" <?php echo ($rangeCategory == "High end level") ? 'selected' : ''; ?>>High end level</option>
                                    <option value="Entry level" <?php echo ($rangeCategory == "Entry level") ? 'selected' : ''; ?>>Entry level</option>
                                    <option value="N/A" <?php echo ($rangeCategory == "N/A") ? 'selected' : ''; ?>>N/A</option>
                                </select>
                            </div>
                        </div>
                    </div>
              
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        Software Information
                    </div>
                    <div class="card-body">
                        <div class="form-section">
                            <h6 class="section-title">Primary Software</h6>
                            <div class="form-group">
                                <label class="form-label">Software Installed</label>
                                <input type="text" class="form-control" name="softwareInstalled" value="<?php echo $softwareInstalled; ?>" >
                            </div>
                            <div class="form-group">
                                <label class="form-label">Licensing Model</label>
                                <select class="form-select" name="licensingModel">
                                    <option value="" <?php echo ($licensingModel == "") ? 'selected' : ''; ?>>Select model</option>
                                    <option value="Evaluation Copy" <?php echo ($licensingModel == "Evaluation Copy") ? 'selected' : ''; ?>>Evaluation Copy</option>
                                    <option value="Perpetual Copy" <?php echo ($licensingModel == "Perpetual Copy") ? 'selected' : ''; ?>>Perpetual Copy</option>
                                    <option value="Subscription" <?php echo ($licensingModel == "Subscription") ? 'selected' : ''; ?>>Subscription</option>
                                    <option value="Perpetual" <?php echo ($licensingModel == "Perpetual") ? 'selected' : ''; ?>>Perpetual</option>
                                    <option value="N/A" <?php echo ($licensingModel == "N/A") ? 'selected' : ''; ?>>N/A</option>
                                </select>
                            </div>
                        </div>


                        
                        <div class="form-section">
                            <h6 class="section-title">Secondary Software</h6>
                            <div class="form-group">
                                <label class="form-label">Software Installed</label>
                                <input type="text" class="form-control" name="softwareInstalled_2" value="<?php echo $softwareInstalled_2; ?>" >
                            </div>
                            <div class="form-group">
                                <label class="form-label">Licensing Model</label>
                                <select class="form-select" name="licensingModel_2">
                                    <option value="" <?php echo ($softwareInstalled_2 == "") ? 'selected' : ''; ?>>Select model</option>
                                    <option value="Evaluation Copy" <?php echo ($licensingModel_2 == "Evaluation Copy") ? 'selected' : ''; ?>>Evaluation Copy</option>
                                    <option value="Perpetual Copy" <?php echo ($licensingModel_2 == "Perpetual Copy") ? 'selected' : ''; ?>>Perpetual Copy</option>
                                    <option value="Subscription" <?php echo ($licensingModel_2 == "Subscription") ? 'selected' : ''; ?>>Subscription</option>
                                    <option value="Perpetual" <?php echo ($licensingModel_2 == "Perpetual") ? 'selected' : ''; ?>>Perpetual</option>
                                    <option value="N/A" <?php echo ($licensingModel_2 == "N/A") ? 'selected' : ''; ?>>N/A</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        Financial Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Amount (PHP)</label>



                                <input type="number" class="form-control" name="amount" id="amount" placeholder="<?php echo $amount; ?>" value="<?php echo $amount; ?>"  onchange="calculateDepreciation()">

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Depreciation Value</label>
                                    <input type="number" class="form-control" name="depreciation_value" id="depreciation_value" value="<?php echo $depreciation_value; ?>"  readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mark as Done</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="mark_as_done" id="mark_as_done" value="True" <?php echo ($mark_as_done == True) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mark_as_done">Toggle status</label>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    function calculateDepreciation() {
                        const amount = parseFloat(document.getElementById('amount').value) || 0;
                        const yearAcquired = parseInt('<?php echo $yearAcquired; ?>');
                        const equipmentType = '<?php echo $equipmentType; ?>';
                        const currentYear = new Date().getFullYear();
                        const age = currentYear - yearAcquired;

                        let depreciationRate;
                        switch (equipmentType) {
                            case 'Computer':
                                depreciationRate = 0.25; // 25% per year
                                break;
                            case 'Furniture':
                                depreciationRate = 0.10; // 10% per year
                                break;
                            case 'Vehicle':
                                depreciationRate = 0.20; // 20% per year
                                break;
                            default:
                                depreciationRate = 0.15; // 15% per year for others
                        }

                        let depreciation = amount;
                        for (let i = 0; i < age; i++) {
                            depreciation = depreciation * (1 - depreciationRate);
                        }

                        document.getElementById('depreciation_value').value = Math.round(depreciation);
                    }

                    // Calculate initial depreciation on page load
                    window.onload = calculateDepreciation;
                </script>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Employee & Accountability
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-section">


                    <h6 class="section-title">Employee's Registered PAR</h6>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required">Employee's Name</label>
                                <select class="form-select" name="employeeName">
                                    <option value="">Select Employee</option>
                                    <?php
                                    $sqlEmp = "SELECT full_name FROM inventory_people WHERE office = '{$officeEscaped}' ORDER BY full_name ASC";
                                    $resultEmp = $conn->query($sqlEmp);
                                    if ($resultEmp && $resultEmp->num_rows > 0) {
                                        while ($rowEmp = $resultEmp->fetch_assoc()) {
                                            $selected = ($employeeName == $rowEmp['full_name']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($rowEmp['full_name']) . "' " . $selected . ">" . htmlspecialchars($rowEmp['full_name']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No employees found</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                            <h6 class="section-title">Accountable Person</h6>

                            <div class="form-group">
                                <label class="form-label required">Employee Name</label>
                                <select class="form-select" name="accountablePerson" required>
                                    <option value="">Select Employee</option>
                                    <?php
                                    $sqlAccountable = "SELECT full_name FROM inventory_people WHERE office = '{$officeEscaped}' ORDER BY full_name ASC";
                                    $resultAccountable = $conn->query($sqlAccountable);
                                    if ($resultAccountable && $resultAccountable->num_rows > 0) {
                                        while ($rowAccountable = $resultAccountable->fetch_assoc()) {
                                            $selected = ($accountablePerson == $rowAccountable['full_name']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($rowAccountable['full_name']) . "' " . $selected . ">" . htmlspecialchars($rowAccountable['full_name']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No accountable persons found</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label required">Sex</label>
                                        <select class="form-select" name="sex" required>
                                            <option value="">Select</option>
                                            <option value="Male" <?php echo ($sex == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo ($sex == 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo ($sex == 'Other') ? 'selected' : ''; ?>>Other</option>
                                            <option value="N/A" <?php echo ($sex == 'N/A') ? 'selected' : ''; ?>>N/A</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label required">Office Division</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="officeDivisionInput" name="officeDivision" value="<?php echo $officeDivision; ?>" required readonly>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#officeDivisionModal">
                                                Select
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                </div>

                                <!-- Office Division Modal -->
                                <div class="modal fade" id="officeDivisionModal" tabindex="-1" aria-labelledby="officeDivisionModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="officeDivisionModalLabel">Select Office Division</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <ul class="list-group" id="officeDivisionList">
                                                    <?php
                                                    $sqlDiv = "SELECT officeDivision FROM office_divisions WHERE office = '{$officeEscaped}' ORDER BY officeDivision ASC";
                                                    $resultDiv = $conn->query($sqlDiv);
                                                    if ($resultDiv && $resultDiv->num_rows > 0) {
                                                        while ($rowDiv = $resultDiv->fetch_assoc()) {
                                                            $division = htmlspecialchars($rowDiv['officeDivision']);
                                                            echo "<li class='list-group-item list-group-item-action' style='cursor:pointer;' onclick=\"selectOfficeDivision('{$division}')\">{$division}</li>";
                                                        }
                                                    } else {
                                                        echo "<li class='list-group-item'>No divisions found.</li>";
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                    <script>
                        function selectOfficeDivision(division) {
                            document.getElementById('officeDivisionInput').value = division;
                            var officeDivisionModalElement = document.getElementById('officeDivisionModal');
                            var modal = bootstrap.Modal.getInstance(officeDivisionModalElement);

                            // If the modal instance doesn't exist (e.g., first time opening), create it
                            if (!modal) {
                                modal = new bootstrap.Modal(officeDivisionModalElement);
                            }
                            
                            modal.hide();

                            // Properly dispose the modal instance after hiding to allow reopening
                            officeDivisionModalElement.addEventListener('hidden.bs.modal', function handler() {
                                // Remove any remaining modal-backdrop elements
                                const backdrops = document.querySelectorAll('.modal-backdrop');
                                backdrops.forEach(backdrop => {
                                    backdrop.remove();
                                });
                                document.body.style.overflow = '';
                                document.body.classList.remove('modal-open');
                                // Dispose the modal instance
                                var modalInstance = bootstrap.Modal.getInstance(officeDivisionModalElement);
                                if (modalInstance) {
                                    modalInstance.dispose();
                                }
                                // Remove this event listener after execution
                                officeDivisionModalElement.removeEventListener('hidden.bs.modal', handler);
                            });
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            // ... (Your existing form validation and toast notification code) ...

                            // Also add an event listener for when the modal is closed by the 'x' button or clicking outside
                            var officeDivisionModalElement = document.getElementById('officeDivisionModal');
                            officeDivisionModalElement.addEventListener('hidden.bs.modal', function () {
                                const backdrops = document.querySelectorAll('.modal-backdrop');
                                backdrops.forEach(backdrop => {
                                    backdrop.remove();
                                });
                                document.body.style.overflow = '';
                                document.body.classList.remove('modal-open');
                            });
                        });
                    </script>

  

                            <div class="form-group">
                                <label class="form-label required">Status of Employment</label>
                                <select class="form-select" name="statusOfEmployment" required>
                                    <option value="">Select status</option>
                                    <option value="Contract of Service / Job Order" <?php echo ($statusOfEmployment == 'Contract of Service / Job Order') ? 'selected' : ''; ?>>Contract of Service / Job Order</option>
                                    <option value="Casual" <?php echo ($statusOfEmployment == 'Casual') ? 'selected' : ''; ?>>Casual</option>
                                    <option value="Permanent" <?php echo ($statusOfEmployment == 'Permanent') ? 'selected' : ''; ?>>Permanent</option>
                                    <option value="N/A" <?php echo ($statusOfEmployment == 'N/A') ? 'selected' : ''; ?>>N/A</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-6">
                        <div class="form-section">
                            <h6 class="section-title">Actual User</h6>
                            <div class="form-group">
                                <label class="form-label required">Actual User</label>
                                <select class="form-select" name="actualUser" required>
                                    <option value="">Select Actual User</option>
                                    <?php
                                    $sqlActualUser = "SELECT full_name FROM inventory_people WHERE office = '{$officeEscaped}' ORDER BY full_name ASC";
                                    $resultActualUser = $conn->query($sqlActualUser);
                                    if ($resultActualUser && $resultActualUser->num_rows > 0) {
                                        while ($rowActualUser = $resultActualUser->fetch_assoc()) {
                                            $selected = ($actualUser == $rowActualUser['full_name']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($rowActualUser['full_name']) . "' " . $selected . ">" . htmlspecialchars($rowActualUser['full_name']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No actual users found</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label required">Sex</label>
                                        <select class="form-select" name="actualUserSex" required>
                                            <option value="">Select</option>
                                            <option value="Male" <?php echo ($actualUserSex == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo ($actualUserSex == 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo ($actualUserSex == 'Other') ? 'selected' : ''; ?>>Other</option>
                                            <option value="N/A" <?php echo ($actualUserSex == 'N/A') ? 'selected' : ''; ?>>N/A</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label required">Employment Status</label>
                                        <select class="form-select" name="actualUserStatusOfEmployment" required>
                                            <option value="">Select status</option>
                                            <option value="Contract of Service / Job Order" <?php echo ($actualUserStatusOfEmployment == 'Contract of Service / Job Order') ? 'selected' : ''; ?>>Contract of Service / Job Order</option>
                                            <option value="Casual" <?php echo ($actualUserStatusOfEmployment == 'Casual') ? 'selected' : ''; ?>>Casual</option>
                                            <option value="Permanent" <?php echo ($actualUserStatusOfEmployment == 'Permanent') ? 'selected' : ''; ?>>Permanent</option>
                                            <option value="N/A" <?php echo ($actualUserStatusOfEmployment == 'N/A') ? 'selected' : ''; ?>>N/A</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Nature of Work</label>
                                <input type="text" class="form-control" name="natureOfWork" value="<?php echo $natureOfWork; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    
                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                                                <textarea class="form-control" name="remarks" rows="2"><?php echo $remarks; ?></textarea>
                    </div>                   
                    
                    <div class="form-group">
                        <label class="form-label required">Office</label>
                        <input type="text" class="form-control" name="office" value="<?php echo $office; ?>" required>
                    </div>
                </div>
            </div>
        </div>
  </form>
  
        <div class="action-buttons">
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-lg btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <div>
                    <button type="button" class="btn btn-lg btn-outline-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash-alt me-2"></i>Delete Record
                    </button>
                    <button type="submit" form="inventoryForm" class="btn btn-lg btn-primary">
                        <i class="fas fa-save me-2"></i>Update Inventory Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this record? This action cannot be undone.
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="deleteEnventory.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('inventoryForm');

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Form validation
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (isValid) {
                    // Submit form to updateEnventory.php
                    form.action = "updateEnventory.php";
                    form.submit();
                } else {
                    alert('Please fill in all required fields marked with *');
                }
            });

            // Remove invalid class when user starts typing in required fields
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.hasAttribute('required') && this.value.trim()) {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        });
    </script>
</body>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('inventoryForm');

            // Show toast notification if success message exists
            const successMessage = "<?php echo isset($_SESSION['success_message']) ? $_SESSION['success_message'] : ''; ?>";
            const errorMessage = "<?php echo isset($_SESSION['error_message']) ? $_SESSION['error_message'] : ''; ?>";
            
            if (successMessage) {
                const toast = new bootstrap.Toast(document.createElement('div'));
                const toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                toastContainer.innerHTML = `
                    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Success</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${successMessage}
                        </div>
                    </div>
                `;
                document.body.appendChild(toastContainer);
                const toastElement = toastContainer.querySelector('.toast');
                const bsToast = new bootstrap.Toast(toastElement);
                bsToast.show();
                <?php unset($_SESSION['success_message']); ?>
            }

            if (errorMessage) {
                const toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                toastContainer.innerHTML = `
                    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-danger text-white">
                            <strong class="me-auto">Error</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${errorMessage}
                        </div>
                    </div>
                `;
                document.body.appendChild(toastContainer);
                const toastElement = toastContainer.querySelector('.toast');
                const bsToast = new bootstrap.Toast(toastElement);
                bsToast.show();
                <?php unset($_SESSION['error_message']); ?>
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Form validation
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (isValid) {
                    // Submit form to updateEnventory.php
                    form.action = "updateEnventory.php";
                    form.submit();
                } else {
                    alert('Please fill in all required fields marked with *');
                }
            });

            // Remove invalid class when user starts typing in required fields
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.hasAttribute('required') && this.value.trim()) {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        });
    </script>
</body>


</html>
