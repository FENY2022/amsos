
<style>

.account-picture {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .account-img {
            width: 100px; /* Adjust the size as needed */
            height: 100px; /* Adjust the size as needed */
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #000; /* Adjust the border color and size as needed */
        }

        .camera-icon {
            position: absolute;
            bottom: 0;
            right: 0;
            background: white;
            border-radius: 50%;
            padding: 5px;
        }

        h4 {
            text-align: center;
            }

            /* SRF  __________________________________*/
            .notification-badge {
            background-color: red;
            color: white;
            padding: 3px 7px;
            border-radius: 50%;
            font-size: 12px;
            position: absolute;
            top: 0;
            right: 5;
            transform: translate(50%, 50%);
         
        }


        .notification-badgeSRF {
            background-color: red;
            color: white;
            padding: 3px 7px;
            border-radius: 50%;
            font-size: 12px;
            position: absolute;
            top: 0;
            right: -20;
            transform: translate(50%, 50%);
          
        }





        li {
            position: relative;
 


        }

        a {
            position: relative;
            display: inline-block;
            text-decoration: none;

        }

        #sidebar a,
        #sidebar a:visited,
        #sidebar a:hover,
        #sidebar a:focus,
        #sidebar a:active {
            text-decoration: none !important;
        }

        .arrow {
                float: right;
                display: inline-block;
                margin-left: 50px;
                border: solid white;
                border-width: 0 2px 2px 0;
                padding: 3px;
                transform: rotate(45deg); /* Right arrow */
                transition: transform 0.3s ease;
            }

            
            a.active {
                font-weight: bold;
                color: #007bff; /* Highlight color */
            }


            a[aria-expanded="true"] .arrow {
                transform: rotate(135deg); /* Down arrow */
            }

  
            .submenu-link.active {
                font-weight: bold;
                color: #007bff;
}

</style>

<?php

$stationID = $_SESSION['idSRF'];

// Query 1: Get tracking count from 'srf'
$sql1 = "SELECT COUNT(*) as count FROM srf WHERE tracking = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("i", $stationID);
$stmt1->execute();
$stmt1->bind_result($tracking);
$stmt1->fetch();
$stmt1->close(); // Close the first statement

// Ensure $tracking is set to 0 if null
$tracking = $tracking ?? 0;

$sql2 = "SELECT COUNT(*) as count FROM inv_notification WHERE tracking = ? AND action = 0";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $stationID);
$stmt2->execute();
$stmt2->bind_result($tracking2);
$stmt2->fetch();
$stmt2->close(); // Close the second statement

// Ensure $tracking2 is set to 0 if null
$tracking2 = $tracking2 ?? 0;
?>

<div class="wrapper">
<nav id="sidebar">
    <div class="sidebar-header">
        <h4><?php echo $_SESSION['Full_NameSRF'] ; ?></h4>
        <div class="account-picture">
            <img src="https://otos.e-dats.info/forms/dashboard/<?php echo $_SESSION['Profile_LinkSRF']; ?>" alt="Account Picture" class="account-img">
            <a type="button"><span class="camera-icon">&#x1F4F7;</span></a>
        </div>
    </div>
    <ul class="list-unstyled components">
        <li class="active">
            <a href="mainmenu.php?dir=home">Home</a>
        </li>
        <li>

            <a href="#servicesSubmenu" data-toggle="collapse" aria-expanded="false" >Inventory<span class="arrow"></span></a>
            <ul class="collapse list-unstyled" id="servicesSubmenu">
                <li>
                    <a href="mainmenu.php?dir=entrydata">Entry Data</a>
                </li>
                <li>
                    <a href="mainmenu.php?dir=edupdate">Regular Update</a>
                </li>
                <li>
                    <a href="mainmenu.php?dir=scanQR">Scan QR</a>
                </li>
            </ul>
        </li>

<li>
    <a href="#servicesSubmenuIData" data-toggle="collapse" aria-expanded="false">Analysis<span class="arrow"></span></a>
    <ul class="collapse list-unstyled" id="servicesSubmenuIData">
        <li>
            <a href="mainmenu.php?dir=getinventory">GET Inventory</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=analysisandgraph">Analysis and Graphs</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=returnedequipment">Returned Equipment</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=analysis_of_device">All Devices</a>
        </li>


        <li>
            <a href="mainmenu.php?dir=calendarScheduler">Calendar Scheduler</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=datarep">Equipment Replacement</a>
        </li>
    </ul>
</li>

<li>
    <a href="#preventiveSubmenu" data-toggle="collapse" aria-expanded="false" class="menu-toggle">
        Preventive Maintenance
        <span class="arrow"></span>
        <?php if (!empty($tracking2)): ?>
            <span class="notification-badge"><?php echo htmlspecialchars($tracking2); ?></span>
        <?php endif; ?>
    </a>
    <ul class="collapse list-unstyled" id="preventiveSubmenu">
        <li>
            <a href="mainmenu.php?dir=preventive_maintenance&task=schedule" class="submenu-link">Scheduled Maintenance</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=maintenance_report" class="submenu-link">Maintenance Report</a>
        </li>
    </ul>
</li>


        <li>
        <a href="#servicesSubmenuSRF" data-toggle="collapse" aria-expanded="false" >SRF
             <?php if (!empty($tracking)): ?>
                    <span class="notification-badgeSRF"><?php echo $tracking; ?></span>
            <?php endif; ?>
                    <span class="arrow"></span></a>
     
            <ul class="collapse list-unstyled" id="servicesSubmenuSRF">
                                <li>
                    <a href="mainmenu.php?dir=srfrequestform">Service Request</a>
                </li>




                <?php
                    // Include database connection
                    require_once 'connect.php';

                    // Query to count rows where Notification_read = 1
                    $query = "SELECT COUNT(*) AS unread_count FROM srf WHERE Notification_read = 1";
                    $result = $conn->query($query);

                    // Fetch the count
                    $row = $result->fetch_assoc();
                    $unreadCount = $row['unread_count'];

                    // Display the badge only if there are unread notifications
                    $badge = $unreadCount > 0 ? "<span style='background-color:red; color:white; padding:2px 8px; border-radius:50%; font-size:0.8em;'>{$unreadCount}</span>" : "";
                    ?>


                <li>
                    <a href="mainmenu.php?dir=srfactiontaken"><?php echo $_SESSION['StationSRF']; ?> SRF <?php echo $badge; ?></a>
                </li>

                <?php
                        if ($_SESSION['User_RoleSRF'] == 'Encoder') {  
                            
                         } else{
                        ?>
                            <li style="position: relative;">
                                <a href="mainmenu.php?dir=requestlist" style="position: relative; display: inline-block;">
                                    Request List 
                                    <?php if (!empty($tracking)): ?>
                                        <span class="notification-badge"><?php echo $tracking; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php
                        }
                        ?>

<?php

if ($_SESSION['User_RoleSRF'] == 'Encoder' || $_SESSION['User_RoleSRF'] == 'Verifier' || $_SESSION['User_RoleSRF'] == 'Approver' || $_SESSION['User_RoleSRF'] == 'Recommending_approval') {

                        echo '<li>
                        <a href="mainmenu.php?dir=recent">Recent</a>
                            </li>';

                    
}else{
                            echo '<li>
                                    <a href="mainmenu.php?dir=printform">Print Form</a>
                                </li>';
                                
                            echo '<li>
                                        <a href="mainmenu.php?dir=srfdatagraph">Data Action Taken</a>
                                 </li>';

                            echo '<li>
                        <a href="mainmenu.php?dir=recent">Recent</a>
                            </li>';

      
                    

}

?>



                <li>
                    <a href="mainmenu.php?dir=srfwaitingnumber">Waiting List</a>
                </li>

            </ul>
        </li>

        
<?php
 

 if ($_SESSION['User_RoleSRF'] == 'Encoder' || $_SESSION['User_RoleSRF'] == 'Verifier' || $_SESSION['User_RoleSRF'] == 'Approver' || $_SESSION['User_RoleSRF'] == 'Recommending_approval') {


    
    
 }else{

            echo '<li>
        
                    <a href="#configurationSRF" data-toggle="collapse" aria-expanded="false" >Configuration<span class="arrow"></span></a>

                        <ul class="collapse list-unstyled" id="configurationSRF">
                            <li>
                                <a href="mainmenu.php?dir=assigntracking">Assign Tracking</a>
                            </li>

                            <li>
                                <a href="mainmenu.php?dir=viewassigntracking">View Assigned Tracking</a>
                            </li>
                            
                            <li>
                                <a href="mainmenu.php?dir=assignactionstaff">Assign Action Staff</a>
                            </li>

                            <li>
                                <a href="mainmenu.php?dir=otos_employee_include">OTOS Employee Include</a>
                            </li>

                            <li>
                                <a href="amsos-requestdata.php" target="_blank">Request Data</a>
                            </li>

                        </ul>
                    </li>';

 }



 ?>

    
        <li>
            <a href="mainmenu.php?dir=about">About</a>
        </li>

       
    </ul>
</nav>



<script>
document.addEventListener("DOMContentLoaded", function () {
    // Retrieve the last opened menu from localStorage
    const activeMenu = localStorage.getItem("activeMenu");
    const activeSubmenu = localStorage.getItem("activeSubmenu");

    function applyMenuLoadingState(link) {
        document.querySelectorAll('#sidebar a.menu-link-loading').forEach(activeLink => {
            activeLink.classList.remove('menu-link-loading');
            const spinner = activeLink.querySelector('.menu-loading-icon');
            if (spinner) {
                spinner.remove();
            }
        });

        if (!link.querySelector('.menu-loading-icon')) {
            link.insertAdjacentHTML('beforeend', '<span class="menu-loading-icon" aria-hidden="true"><i class="fas fa-spinner fa-spin"></i></span>');
        }

        link.classList.add('menu-link-loading');
    }

    // If there's an active menu, expand it and highlight it
    if (activeMenu) {
        const menuElement = document.querySelector(`#${activeMenu}`);
        const linkElement = menuElement.previousElementSibling;

        menuElement.classList.add("show"); // Expand the submenu
        linkElement.setAttribute("aria-expanded", "true"); // Set aria-expanded attribute to true

        // Add active class to the menu link
        linkElement.classList.add("active");
    }

    if (activeSubmenu) {
        const submenuLink = document.querySelector(`a[href="${activeSubmenu}"]`);
        if (submenuLink) submenuLink.classList.add("active");
    }

    // Add event listeners to store the active menu and submenu
    document.querySelectorAll("a[data-toggle='collapse']").forEach(menu => {
        menu.addEventListener("click", function () {
            const menuId = this.getAttribute("href").substring(1);
            localStorage.setItem("activeMenu", menuId);
            localStorage.removeItem("activeSubmenu"); // Reset active submenu on menu change
        });
    });

    document.querySelectorAll("a").forEach(link => {
        link.addEventListener("click", function (event) {
            const linkHref = this.getAttribute("href");
            localStorage.setItem("activeSubmenu", linkHref);

            if (!linkHref || linkHref.startsWith('#') || this.dataset.toggle === 'collapse' || this.getAttribute('target') === '_blank' || this.id === 'logoutLink') {
                return;
            }

            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
                return;
            }

            if (linkHref.startsWith('mainmenu.php?dir=')) {
                applyMenuLoadingState(this);
            }
        });
    });
});
</script>

<script>
// Retain submenu state using localStorage
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.querySelectorAll('.menu-toggle');
    const submenuLinks = document.querySelectorAll('.submenu-link');
    const submenuId = 'preventiveSubmenu';

    // Open the submenu if it was previously active
    if (localStorage.getItem('activeSubmenu') === submenuId) {
        document.getElementById(submenuId).classList.add('show');
    }

    // Track submenu click
    menuToggle.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const submenu = this.getAttribute('href').substring(1); // Remove '#'
            if (localStorage.getItem('activeSubmenu') === submenu) {
                localStorage.removeItem('activeSubmenu'); // Collapse submenu
            } else {
                localStorage.setItem('activeSubmenu', submenu); // Store active submenu
            }
        });
    });

    // Highlight active submenu link
    submenuLinks.forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });
});
</script>
