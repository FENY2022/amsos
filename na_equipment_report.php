<?php
// Include the database connection logic from your provided file
require_once('connect.php');

// SQL Query to fetch specific columns where equipmentType is "N/A"
$sql = "SELECT employeeName, equipmentType FROM inv_inventory WHERE equipmentType = 'N/A'";
$result = mysqli_query($conn, $sql);

$records = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
}
$count = count($records);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | N/A Equipment Report</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --light-color: #f8f9ff;
            --dark-color: #2b2d42;
            --success-color: #06d6a0;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7ff 0%, #eef2ff 100%);
            min-height: 100vh;
            color: var(--dark-color);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
            overflow: hidden;
        }
        
        .header-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 2.5rem 0;
            border-radius: 0 0 30px 30px;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border-left: 5px solid var(--primary-color);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .table-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        
        .table-header {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
        }
        
        .table-hover tbody tr {
            transition: all 0.2s ease;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
            transform: scale(1.01);
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1.5rem;
        }
        
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.3);
            border: none;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .floating-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
            color: white;
        }
        
        .toast-container {
            z-index: 9999;
        }
        
        .custom-toast {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(67, 97, 238, 0); }
            100% { box-shadow: 0 0 0 0 rgba(67, 97, 238, 0); }
        }
        
        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<!-- Loading Spinner -->
<div class="loading-spinner" id="loadingSpinner">
    <div class="spinner"></div>
</div>

<!-- Header Section -->
<div class="header-gradient">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white p-3 rounded-circle me-3 shadow">
                        <i class="bi bi-clipboard-data-fill text-primary fs-3"></i>
                    </div>
                    <div>
                        <h1 class="text-white fw-bold mb-1">N/A Equipment Report</h1>
                        <p class="text-white-50 mb-0">Inventory items with unspecified equipment types</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-inline-block stat-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-2"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0"><?php echo $count; ?></h3>
                            <p class="text-muted mb-0">Records Found</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Main Content -->
<div class="container mb-5">
    <div class="row">
        <div class="col-12">
            <!-- Summary Card -->
            <div class="glass-card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="fw-bold text-dark mb-2">Report Summary</h5>
                        <p class="text-muted mb-0">
                            This report shows all inventory items where the equipment type is marked as "N/A" (Not Available/Not Specified).
                            These items need attention to be properly categorized.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="badge bg-light text-dark fs-6 p-2">
                            <i class="bi bi-clock me-2"></i>
                            Generated: <?php echo date('M d, Y - h:i A'); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Table Card -->
            <div class="glass-card">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-header">
                                <tr>
                                    <th class="ps-4 py-3 text-white fw-semibold">#</th>
                                    <th class="py-3 text-white fw-semibold">
                                        <i class="bi bi-person-badge me-2"></i>Employee Name
                                    </th>
                                    <th class="py-3 text-center text-white fw-semibold">
                                        <i class="bi bi-tools me-2"></i>Equipment Type
                                    </th>
                                    <th class="py-3 text-center text-white fw-semibold">
                                        <i class="bi bi-gear me-2"></i>Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($count > 0): ?>
                                    <?php foreach ($records as $index => $row): ?>
                                    <tr>
                                        <td class="ps-4 align-middle">
                                            <span class="badge bg-light text-dark rounded-circle p-2">
                                                <?php echo $index + 1; ?>
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                                    <i class="bi bi-person"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold"><?php echo htmlspecialchars($row['employeeName']); ?></h6>
                                                    <small class="text-muted">Employee ID: <?php echo substr(md5($row['employeeName']), 0, 8); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="status-badge bg-warning bg-opacity-10 text-warning pulse-animation">
                                                <i class="bi bi-exclamation-circle me-1"></i>
                                                <?php echo htmlspecialchars($row['equipmentType']); ?>
                                            </span>
                                            <small class="d-block text-muted mt-1">Requires update</small>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button class="btn btn-sm btn-outline-primary edit-equipment" 
                                                    data-employee-name="<?php echo htmlspecialchars($row['employeeName']); ?>"
                                                    data-current-type="<?php echo htmlspecialchars($row['equipmentType']); ?>">
                                                <i class="bi bi-pencil me-1"></i>Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="bi bi-check-circle"></i>
                                            </div>
                                            <h4 class="text-muted mb-3">No Records Found</h4>
                                            <p class="text-muted mb-4">Great news! All equipment types are properly specified.</p>
                                            <button class="btn btn-primary">
                                                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($count > 0): ?>
                    <div class="p-4 border-top">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 me-3">
                                        <i class="bi bi-download"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Export Options</small>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary me-2">
                                                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                            </button>
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-file-earmark-excel me-1"></i>Excel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="d-inline-block bg-light p-3 rounded">
                                    <span class="text-muted">Total Records: </span>
                                    <span class="fw-bold fs-5 text-primary"><?php echo $count; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Info Box -->
            <?php if ($count > 0): ?>
            <div class="alert alert-info glass-card mt-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-info-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Action Required</h6>
                        <p class="mb-0">These <?php echo $count; ?> records require equipment type specification. Please update them in the main inventory system.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Floating Action Button -->
<button class="floating-btn" id="refreshBtn" title="Refresh Data">
    <i class="bi bi-arrow-clockwise fs-5"></i>
</button>

<!-- Edit Equipment Modal -->
<div class="modal fade" id="editEquipmentModal" tabindex="-1" aria-labelledby="editEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="editEquipmentModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Equipment Type
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEquipmentForm">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Employee Name</label>
                        <input type="text" class="form-control" id="modalEmployeeName" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Current Equipment Type</label>
                        <input type="text" class="form-control" id="modalCurrentType" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">New Equipment Type</label>
                        <select class="form-select" id="modalNewType" required>
                            <option value="">Select Equipment Type</option>
                            <option value="Laptop">Laptop</option>
                            <option value="Desktop">Desktop</option>
                            <option value="Monitor">Monitor</option>
                            <option value="Phone">Phone</option>
                            <option value="Tablet">Tablet</option>
                            <option value="Printer">Printer</option>
                            <option value="Scanner">Scanner</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes (Optional)</label>
                        <textarea class="form-control" id="modalNotes" rows="3" placeholder="Add any additional notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEquipmentChanges">
                    <i class="bi bi-check-lg me-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <!-- Success Toast -->
    <div id="successToast" class="toast custom-toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong class="me-auto">Success</strong>
            <small>Just now</small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body bg-success bg-opacity-10">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-clipboard-data text-success fs-4"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Report Loaded Successfully</h6>
                    <p class="mb-0">Found <strong><?php echo $count; ?> records</strong> with equipment type "N/A"</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Warning Toast -->
    <div id="warningToast" class="toast custom-toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-warning text-dark">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong class="me-auto">Attention Needed</strong>
            <small>Inventory Alert</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body bg-warning bg-opacity-10">
            <p class="mb-0">There are <strong><?php echo $count; ?> items</strong> that require equipment type specification.</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Show loading spinner
        const loadingSpinner = document.getElementById('loadingSpinner');
        loadingSpinner.style.display = 'flex';
        
        // Show success toast
        const successToast = new bootstrap.Toast(document.getElementById('successToast'));
        
        // Show warning toast if there are records
        const warningToast = new bootstrap.Toast(document.getElementById('warningToast'));
        
        // Edit Equipment Modal
        const editModal = new bootstrap.Modal(document.getElementById('editEquipmentModal'));
        
        // Edit button functionality
        document.querySelectorAll('.edit-equipment').forEach(btn => {
            btn.addEventListener('click', function() {
                const employeeName = this.getAttribute('data-employee-name');
                const currentType = this.getAttribute('data-current-type');
                
                document.getElementById('modalEmployeeName').value = employeeName;
                document.getElementById('modalCurrentType').value = currentType;
                document.getElementById('modalNewType').value = '';
                document.getElementById('modalNotes').value = '';
                
                editModal.show();
            });
        });
        
        // Save changes functionality
        document.getElementById('saveEquipmentChanges').addEventListener('click', function() {
            const newType = document.getElementById('modalNewType').value;
            const employeeName = document.getElementById('modalEmployeeName').value;
            const notes = document.getElementById('modalNotes').value;
            
            if (!newType) {
                alert('Please select a new equipment type');
                return;
            }
            
            // Show loading
            loadingSpinner.style.display = 'flex';
            
            // Simulate API call
            setTimeout(() => {
                loadingSpinner.style.display = 'none';
                editModal.hide();
                
                // Show success message
                const toastBody = document.querySelector('#successToast .toast-body p');
                toastBody.textContent = `Equipment type updated to "${newType}" for ${employeeName}`;
                successToast.show();
                
                // Refresh page after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }, 1500);
        });
        
        // Hide loading spinner after page load
        setTimeout(() => {
            loadingSpinner.style.display = 'none';
            
            // Show success toast
            successToast.show();
            
            // Show warning toast if there are N/A records
            <?php if ($count > 0): ?>
            setTimeout(() => {
                warningToast.show();
            }, 2000);
            <?php endif; ?>
        }, 800);
        
        // Refresh button functionality
        const refreshBtn = document.getElementById('refreshBtn');
        refreshBtn.addEventListener('click', function() {
            loadingSpinner.style.display = 'flex';
            
            // Simulate refresh with a delay
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });
        
        // Add animation to table rows on hover
        const tableRows = document.querySelectorAll('.table-hover tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.1)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.boxShadow = 'none';
            });
        });
        
        // Export button functionality (mock)
        document.querySelectorAll('.btn-outline-primary, .btn-outline-success').forEach(btn => {
            btn.addEventListener('click', function() {
                const toast = new bootstrap.Toast(document.getElementById('successToast'));
                const toastBody = document.querySelector('#successToast .toast-body p');
                toastBody.textContent = 'Export functionality would be implemented here';
                toast.show();
            });
        });
    });
</script>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Show loading spinner
        const loadingSpinner = document.getElementById('loadingSpinner');
        loadingSpinner.style.display = 'flex';
        
        // Show success toast
        const successToast = new bootstrap.Toast(document.getElementById('successToast'));
        
        // Show warning toast if there are records
        const warningToast = new bootstrap.Toast(document.getElementById('warningToast'));
        
        // Hide loading spinner after page load
        setTimeout(() => {
            loadingSpinner.style.display = 'none';
            
            // Show success toast
            successToast.show();
            
            // Show warning toast if there are N/A records
            <?php if ($count > 0): ?>
            setTimeout(() => {
                warningToast.show();
            }, 2000);
            <?php endif; ?>
        }, 800);
        
        // Refresh button functionality
        const refreshBtn = document.getElementById('refreshBtn');
        refreshBtn.addEventListener('click', function() {
            loadingSpinner.style.display = 'flex';
            
            // Simulate refresh with a delay
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });
        
        // Add animation to table rows on hover
        const tableRows = document.querySelectorAll('.table-hover tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.1)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.boxShadow = 'none';
            });
        });
        
        // Export button functionality (mock)
        document.querySelectorAll('.btn-outline-primary, .btn-outline-success').forEach(btn => {
            btn.addEventListener('click', function() {
                const toast = new bootstrap.Toast(document.getElementById('successToast'));
                const toastBody = document.querySelector('#successToast .toast-body p');
                toastBody.textContent = 'Export functionality would be implemented here';
                toast.show();
            });
        });
    });
</script>

</body>
</html>