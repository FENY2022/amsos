<?php
if (
    isset($_GET['dir'], $_GET['export']) &&
    $_GET['dir'] === 'analysisandgraph_datafilter' &&
    $_GET['export'] === 'excel'
) {
    require_once 'analysisandgraph_datafilter.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT-AMSOS</title>
    <link rel="shortcut icon" type="image/x-icon" href="icon/amsos.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/mainmenu.css">
</head>
<body>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'connect.php';
require_once 'session_checker.php';
require_once 'navbar.php';
require_once 'sidebar.php';
?>


 
        <div class="content">

            <?php
            
              
            if (isset($_GET['dir']) && !empty($_GET['dir'])) {
                    $_dirlist = $_GET['dir'];
                } else {
                    $_dirlist = "";
                }
                



                if ($_dirlist == 'home') {
                require_once 'analysisandgraph.php' ; 

                

              }elseif ($_dirlist == 'about') {
                
                require_once 'about.php' ;

            }elseif ($_dirlist == 'entrydata') {

                require_once 'entrydata.php' ;

            }elseif ($_dirlist == 'entrydatahidesidebar') {

                require_once 'entrydata.php' ;
                
             
            }elseif ($_dirlist == 'edupdate') {

                require_once 'entryupdate.php' ;

            }elseif ($_dirlist == 'getinventory') {

                echo '<iframe src="getinventory.php" onload="var overlay = window.parent.document.getElementById(\'globalLoadingOverlay\'); if (overlay) { overlay.classList.remove(\'active\'); overlay.setAttribute(\'aria-hidden\', \'true\'); }" style="width:100%; height:100vh; border:none;"></iframe>';

                  
            }elseif ($_dirlist == 'analysisandgraph') {

                require_once 'analysisandgraph.php' ;

            }elseif ($_dirlist == 'returnedequipment') {

                require_once 'returnedequipment.php' ;

            }elseif ($_dirlist == 'srfrequestform') {

                echo '<iframe src="srfrequestform.php" onload="var overlay = window.parent.document.getElementById(\'globalLoadingOverlay\'); if (overlay) { overlay.classList.remove(\'active\'); overlay.setAttribute(\'aria-hidden\', \'true\'); }" style="width:100%; height:100vh; border:none;"></iframe>';

            }elseif ($_dirlist == 'srfactiontaken') {

                require_once 'srfactiontaken.php' ;
                
            }elseif ($_dirlist == 'srfdatagraph') {

                require_once 'srfdatagraph.php' ;

            }elseif ($_dirlist == 'srfwaitingnumber') {

                require_once 'srfwaitingnumber.php' ;

            }elseif ($_dirlist == 'requestlist') {

                require_once 'requestlist.php' ;

            }elseif ($_dirlist == 'assigntracking') {

                require_once 'assigntracking.php' ;

            }elseif ($_dirlist == 'viewassigntracking') {

                require_once 'viewassigntracking.php' ;

            }elseif ($_dirlist == 'assignactionstaff') {

                require_once 'assignactionstaff.php' ;

            }elseif ($_dirlist == 'otos_employee_include') {

                require_once 'otos_employee_include.php' ;


            }elseif ($_dirlist == 'printform') {

                require_once 'printform.php' ;

            }elseif ($_dirlist == 'recent') {

                require_once 'recent.php' ;

            }elseif ($_dirlist == 'viewuploaded') {

                require_once 'viewuploaded.php' ;



            }elseif ($_dirlist == 'equipment_page') {

                require_once 'equipment_page.php' ;

            }elseif ($_dirlist == 'analysis_of_device') {

                require_once 'analysis_of_device.php' ;

            }elseif ($_dirlist == 'preventive_maintenance') {

                require_once 'preventive_maintenance.php' ;

            }elseif ($_dirlist == 'preventive_maintenance_form') {

                require_once 'preventive_maintenance_form.php' ;


            }elseif ($_dirlist == 'calendarScheduler') {

                require_once 'calendarScheduler.php' ;

            }elseif ($_dirlist == 'datarep') {

                require_once 'datarep.php' ;

            }elseif ($_dirlist == 'maintenance_report') {

                require_once 'maintenance_report.php' ;


            }elseif ($_dirlist == 'summary') {

                require_once 'summary.php' ;

            }elseif ($_dirlist == 'qr') {

                require_once 'qr.php' ;


            }elseif ($_dirlist == 'scanQR') {

                require_once 'scanQR.php' ;

            }elseif ($_dirlist == 'ai') {

                echo '<iframe src="summaryAI.php" style="width:100%; height:100vh; border:none;"></iframe>';

            }elseif ($_dirlist == 'tablesummary') {

                echo '<iframe src="tablesummary.php" style="width:100%; height:100vh; border:none;"></iframe>';

            }elseif ($_dirlist == 'search_inventory') {

                echo '<iframe src="search_inventory.php" style="width:100%; height:100vh; border:none;"></iframe>';
                
                            }elseif ($_dirlist == 'repair_frequency') {

                echo '<iframe src="repair_frequency.php" style="width:100%; height:100vh; border:none;"></iframe>';


     
            }elseif ($_dirlist == 'analysisandgraph_datafilter') {

                require_once 'analysisandgraph_datafilter.php' ;


      
                
                }else{

               require_once 'analysisandgraph.php' ;

                }

    
                
            ?>
       
        </div>
    </div>

    <div id="globalLoadingOverlay" class="loading-overlay" aria-hidden="true">
        <div class="loading-overlay-card">
            <div class="loading-overlay-icon"><i class="fas fa-spinner fa-spin"></i></div>
            <div class="loading-overlay-text">Loading...</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
                $(document).ready(function () {
                    const loadingOverlay = document.getElementById('globalLoadingOverlay');

                    function showLoadingOverlay() {
                        if (!loadingOverlay) {
                            return;
                        }
                        loadingOverlay.classList.add('active');
                        loadingOverlay.setAttribute('aria-hidden', 'false');
                    }

                    window.hideLoadingOverlay = function hideLoadingOverlay() {
                        if (!loadingOverlay) {
                            return;
                        }
                        loadingOverlay.classList.remove('active');
                        loadingOverlay.setAttribute('aria-hidden', 'true');
                    }

                    hideLoadingOverlay();

                    document.querySelectorAll('a[href^="mainmenu.php?dir="]').forEach(function (link) {
                        if (link.getAttribute('target') === '_blank') {
                            return;
                        }

                        link.addEventListener('click', function (event) {
                            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
                                return;
                            }
                            showLoadingOverlay();
                        });
                    });

                    window.addEventListener('pageshow', hideLoadingOverlay);

                    // Toggle sidebar when clicking the collapse button
                    $('#sidebarCollapse').on('click', function () {
                        $('#sidebar').toggleClass('active');
                    });

                    // Automatically collapse the sidebar on mobile view
                    function checkSidebar() {
                        if ($(window).width() <= 576) { // Mobile view breakpoint
                            $('#sidebar').addClass('active'); // Collapse sidebar by default on mobile
                        } else {
                            $('#sidebar').removeClass('active'); // Expand sidebar on larger screens
                        }
                    }

                    // Run the check on page load
                    checkSidebar();

                    // Run the check on window resize
                    $(window).resize(checkSidebar);

                    // Confirm logout
                    $('#logoutLink').on('click', function (e) {
                        e.preventDefault();
                        if (confirm('Are you sure you want to log out?')) {
                            window.location.href = 'logout.php'; // Redirect to the logout script
                        }
                    });
                });
</script>




</body>
</html>
