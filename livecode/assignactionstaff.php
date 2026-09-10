<?php
// Start the session at the very beginning of the file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection (if needed for displaying content on this page)
include 'connect.php'; // Make sure this path is correct
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5; /* Lighter, modern background */
            font-family: 'Inter', sans-serif; /* Modern font */
            color: #333;
        }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .header-title {
            font-weight: 700;
            color: #2c3e50; /* Darker, more prominent color */
        }
        .card {
            border: none;
            border-radius: 0.75rem; /* Slightly more rounded corners */
            box-shadow: 0 0.75rem 1.5rem rgba(0,0,0,0.08); /* Stronger, softer shadow */
            overflow: hidden; /* Ensures content stays within rounded corners */
        }
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
        }
        .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            color: #555;
            padding: 0.75rem 1.25rem;
            transition: all 0.3s ease;
        }
        .nav-tabs .nav-link.active {
            color: #007bff; /* Bootstrap primary blue */
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            font-weight: 600;
        }
        .nav-tabs .nav-link:hover:not(.active) {
            border-color: #e9ecef #e9ecef #dee2e6;
            background-color: #f8f9fa;
        }
        .table {
            --bs-table-striped-bg: #f6f6f6; /* Lighter stripe */
            --bs-table-hover-bg: #f0f0f0; /* Lighter hover */
        }
        .table thead {
            background-color: #e9ecef;
            color: #495057;
            font-weight: 600;
        }
        .table th, .table td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }
        .input-group > .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .input-group > .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .search-group {
            max-width: 500px; /* Slightly wider search bar */
            margin: 0 auto; /* Center the search bar */
        }
        /* Styles for the toast */
        .toast-container {
            z-index: 1080; /* Ensure toast is above other elements */
        }
        .toast {
            background-color: white; /* Default background */
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none; /* Remove default toast border */
        }
        /* Specific colors for toast types */
        .toast.text-bg-success { background-color: #d1e7dd !important; color: #0f5132 !important; }
        .toast.text-bg-success .toast-header { background-color: #badbcc !important; color: #0f5132 !important; border-bottom: 1px solid #a2d7c5; }
        .toast.text-bg-danger { background-color: #f8d7da !important; color: #842029 !important; }
        .toast.text-bg-danger .toast-header { background-color: #f5c2c7 !important; color: #842029 !important; border-bottom: 1px solid #ebc2c6; }
        .toast.text-bg-warning { background-color: #fff3cd !important; color: #664d03 !important; }
        .toast.text-bg-warning .toast-header { background-color: #ffecb5 !important; color: #664d03 !important; border-bottom: 1px solid #ffe8a1; }
        .toast.text-bg-info { background-color: #cff4fc !important; color: #055160 !important; }
        .toast.text-bg-info .toast-header { background-color: #b3ecf7 !important; color: #055160 !important; border-bottom: 1px solid #a3e6f5; }

        .toast .toast-header .btn-close {
            filter: invert(0.5) sepia(1) saturate(5) hue-rotate(175deg); /* Adjust close button color for dark backgrounds */
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h2 class="mb-5 text-center header-title">
            <i class="bi bi-people-fill me-2"></i>
            Assign Action Staff
        </h2>

        <div class="d-flex justify-content-center mb-5">
            <form class="input-group search-group shadow-sm" method="POST" action="">
                <input type="text" name="search" class="form-control form-control-lg border-primary" placeholder="Search employees by full name..." aria-label="Search employees">
                <button class="btn btn-primary btn-lg px-4" type="submit">
                    <i class="bi bi-search me-2"></i>
                    Search
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs mb-4" id="staffTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="assign-tab" data-bs-toggle="tab" data-bs-target="#assign" type="button" role="tab" aria-controls="assign" aria-selected="true">
                            <i class="bi bi-person-plus me-2"></i>Assign Staff
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="selected-tab" data-bs-toggle="tab" data-bs-target="#selected" type="button" role="tab" aria-controls="selected" aria-selected="false">
                            <i class="bi bi-person-check me-2"></i>Selected Staff
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="staffTabsContent">
                    <div class="tab-pane fade show active" id="assign" role="tabpanel" aria-labelledby="assign-tab">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Office</th>
                                        <th>Station</th>
                                        <th>Role</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Make sure these include files exist and generate valid table rows
                                    include 'fetch_assigactionstaff.php';
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="selected" role="tabpanel" aria-labelledby="selected-tab">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Office</th>
                                        <th>Station</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Make sure these include files exist and generate valid table rows
                                    include 'fetch_rictuactionstaff.php';
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

          <div id="notificationToast" class="toast position-fixed top-50 start-50 translate-middle shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="8000" style="min-width: 300px; z-index: 9999;">
              <div class="toast-header border-0 py-2">
                  <i class="bi bi-bell-fill me-2"></i>
                  <strong class="me-auto fs-5">Notification</strong>
                  <small class="text-muted"></small>
                  <button type="button" class="btn-close shadow-none" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
              <div class="toast-body py-3 fs-6">
              </div>
          </div>
      </div>

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"></script>
      <script>
          function showToast(message, type = 'success') {
              const toastEl = document.getElementById('notificationToast');
              const toast = new bootstrap.Toast(toastEl);
              const toastBody = toastEl.querySelector('.toast-body');
              const toastHeader = toastEl.querySelector('.toast-header strong');
              const toastIcon = toastEl.querySelector('.toast-header i');
              const toastCloseBtn = toastEl.querySelector('.toast-header .btn-close');

              toastBody.textContent = message;

              // Add animation class
              toastEl.classList.add('animate__animated', 'animate__fadeIn');

              // Reset and apply new classes for coloring
              toastEl.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info');
              toastHeader.classList.remove('text-success', 'text-danger', 'text-warning', 'text-info');
              toastIcon.classList.remove('text-success', 'text-danger', 'text-warning', 'text-info', 'bi-check-circle-fill', 'bi-x-circle-fill', 'bi-exclamation-circle-fill', 'bi-info-circle-fill');
              toastCloseBtn.style.filter = '';

              switch(type) {
                  case 'success':
                      toastEl.classList.add('text-bg-success');
                      toastHeader.classList.add('text-success');
                      toastIcon.classList.add('text-success', 'bi-check-circle-fill');
                      toastEl.style.borderLeft = '5px solid var(--bs-success)';
                      break;
                  case 'danger':
                      toastEl.classList.add('text-bg-danger');
                      toastHeader.classList.add('text-danger');
                      toastIcon.classList.add('text-danger', 'bi-x-circle-fill');
                      toastEl.style.borderLeft = '5px solid var(--bs-danger)';
                      toastCloseBtn.style.filter = 'invert(0.5) sepia(1) saturate(5) hue-rotate(175deg)';
                      break;
                  case 'warning':
                      toastEl.classList.add('text-bg-warning');
                      toastHeader.classList.add('text-warning');
                      toastIcon.classList.add('text-warning', 'bi-exclamation-circle-fill');
                      toastEl.style.borderLeft = '5px solid var(--bs-warning)';
                      break;
                  case 'info':
                      toastEl.classList.add('text-bg-info');
                      toastHeader.classList.add('text-info');
                      toastIcon.classList.add('text-info', 'bi-info-circle-fill');
                      toastEl.style.borderLeft = '5px solid var(--bs-info)';
                      break;
                  default:
                      toastEl.classList.add('text-bg-success');
                      toastHeader.classList.add('text-success');
                      toastIcon.classList.add('text-success', 'bi-check-circle-fill');
                      toastEl.style.borderLeft = '5px solid var(--bs-success)';
              }

              // Add timestamp
              const timestamp = toastEl.querySelector('.toast-header small');
              timestamp.textContent = new Date().toLocaleTimeString();

              toast.show();

              // Remove animation class after animation ends
              toastEl.addEventListener('animationend', () => {
                  toastEl.classList.remove('animate__animated', 'animate__fadeIn');
              });
          }

          // PHP block to display toast after redirect
          <?php
          if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])) {
              $message = addslashes($_SESSION['toast_message']);
              $type = addslashes($_SESSION['toast_type']);

              echo "document.addEventListener('DOMContentLoaded', function() {";
              echo "showToast('$message', '$type');";
              echo "});";

              unset($_SESSION['toast_message']);
              unset($_SESSION['toast_type']);
          }
          ?>
      </script>

   
</body>
</html>