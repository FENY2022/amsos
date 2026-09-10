
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
        .account-picture {
            width: 122px;
            height: 122px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 12px auto 0;
        }

        .account-img,
        .temporary-profile-avatar {
            width: 112px;
            height: 112px;
            min-width: 112px;
            min-height: 112px;
            max-width: 112px;
            max-height: 112px;
            box-sizing: border-box;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
            overflow: hidden;
        }

        .account-img {
            display: block;
            object-fit: cover;
            aspect-ratio: 1 / 1;
            background: #4b5563;
        }

        .temporary-profile-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb, #14b8a6);
            font-size: 2.3rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .camera-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            right: 2px;
            bottom: 8px;
            color: #374151;
            background: #ffffff;
            border: 3px solid #444;
            border-radius: 50%;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.25);
            font-size: 0.95rem;
        }

        h4 {
            text-align: center;
        }

        /* SRF Notification */
        .notification-badge {
            background-color: red;
            color: white;
            padding: 3px 7px;
            border-radius: 50%;
            font-size: 12px;
            position: absolute;
            top: 0;
            right: 5px;
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
            right: -20px;
            transform: translate(50%, 50%);
        }

        li {
            position: relative;
        }

        /* Sidebar Links Base */
        a {
            position: relative;
            display: block; /* Para lapad click area */
            padding: 10px 15px; 
            color: #d1d1d1; /* Slight gray/white para maklaro sa dark background IF dark ang imo main sidebar */
            text-decoration: none;
        }

        /* Hover sa Main Links */
        a:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1); /* Subtle highlight */
            text-decoration: none;
        }

        #sidebar a,
        #sidebar a:visited,
        #sidebar a:hover,
        #sidebar a:focus,
        #sidebar a:active {
            text-decoration: none !important;
        }

        /* Gi-ilisan ang color sa ARROW: 
           Kaniadto 'white', gi-ilis nako to 'currentColor' para mu-sunod sa text color. 
           Kung black ang imo text, ma black sad ni. 
        */
        .arrow {
            float: right;
            display: inline-block;
            margin-left: 20px;
            border: solid currentColor; /* Musunod sa color sa font */
            border-width: 0 2px 2px 0;
            padding: 3px;
            margin-top: 5px;
            transform: rotate(45deg); /* Right arrow */
            transition: transform 0.3s ease;
        }

        a.active {
            font-weight: bold;
            color: #007bff; /* Active highlight color */
            background-color: rgba(0, 123, 255, 0.1);
        }

        a[aria-expanded="true"] .arrow {
            transform: rotate(135deg); /* Down arrow */
        }

        /* =========================================
           CHILD MENU (SUBMENU) PLASTAR STYLES
           ========================================= */
        ul.collapse {
            background-color: rgba(0, 0, 0, 0.2); /* Darken gamay ang child menu aron lahi sa main menu */
            list-style-type: none;
            padding: 5px 0;
            margin: 0;
        }

        ul.collapse li a {
            padding: 8px 15px 8px 45px !important; /* Pasudlon ang text (indent) */
            font-size: 0.9em;
            color: #b8c7ce; /* Grayish blue para dili perting putia */
            transition: all 0.3s ease;
        }

        /* Hover sa Child Links */
        ul.collapse li a:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            padding-left: 50px !important; /* Gamayng irog inig hover */
        }

        .submenu-link.active, ul.collapse li a.active {
            font-weight: bold;
            color: #fff; /* Puti inig active */
            background-color: #007bff; /* Blue box para klaro ang gi-pili */
            border-left: 4px solid #fff;
        }
</style>
<?php

$stationID = $_SESSION['idSRF'];
$fullName = $_SESSION['Full_NameSRF'] ?? 'User';
$profileLink = trim($_SESSION['Profile_LinkSRF'] ?? '');
$profileUrl = $profileLink !== '' ? 'https://otos.e-dats.info/forms/dashboard/' . $profileLink : '';
$nameParts = preg_split('/\s+/', trim($fullName));
$temporaryInitials = '';

foreach ($nameParts as $namePart) {
    if ($namePart !== '') {
        $temporaryInitials .= strtoupper(substr($namePart, 0, 1));
    }

    if (strlen($temporaryInitials) >= 2) {
        break;
    }
}

$temporaryInitials = $temporaryInitials !== '' ? $temporaryInitials : 'U';


                    // if ($_SESSION['OfficeSRF'] != 'REGIONAL OFFICE') {
                        

                        
                    //     echo "<script>window.location.href='logout.php';</script>";
                    //     exit();
                    // }



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
        <h4><?php echo htmlspecialchars($fullName); ?></h4>
        <div class="account-picture">
            <?php if ($profileUrl !== ''): ?>
                <img src="<?php echo htmlspecialchars($profileUrl); ?>" alt="Account Picture" class="account-img" onerror="this.replaceWith(document.getElementById('temporaryProfileAvatar').content.cloneNode(true));">
                <template id="temporaryProfileAvatar">
                    <div class="temporary-profile-avatar" aria-label="Temporary profile picture"><?php echo htmlspecialchars(substr($temporaryInitials, 0, 2)); ?></div>
                </template>
            <?php else: ?>
                <div class="temporary-profile-avatar" aria-label="Temporary profile picture"><?php echo htmlspecialchars(substr($temporaryInitials, 0, 2)); ?></div>
            <?php endif; ?>
            <a type="button"><span class="camera-icon"><i class="fas fa-camera"></i></span></a>
        </div>
    </div>
    <ul class="list-unstyled components">
        <li class="active">
            <a href="mainmenu.php?dir=home"><i class="fas fa-home"></i> Home</a>
        </li>
        <li>

            <a href="#servicesSubmenu" data-toggle="collapse" aria-expanded="false" ><i class="fas fa-boxes"></i> Inventory<span class="arrow"></span></a>
            <ul class="collapse list-unstyled" id="servicesSubmenu">
                <li>
                    <a href="mainmenu.php?dir=entrydata"><i class="fas fa-keyboard"></i> Entry Data</a>
                </li>
                <li>
                    <a href="mainmenu.php?dir=edupdate"><i class="fas fa-sync"></i> Regular Update</a>
                </li>
                <li>
                    <a href="mainmenu.php?dir=scanQR"><i class="fas fa-qrcode"></i> Scan QR</a>
                </li>
                <li>
                    <a href="mainmenu.php?dir=generateQR"><i class="fas fa-tags"></i> Generate Stickers</a>
                </li>
            </ul>
        </li>

<li>
    <a href="#servicesSubmenuIData" data-toggle="collapse" aria-expanded="false"><i class="fas fa-chart-line"></i> Analysis<span class="arrow"></span></a>
    <ul class="collapse list-unstyled" id="servicesSubmenuIData">
        <li>
            <a href="mainmenu.php?dir=getinventory"><i class="fas fa-boxes"></i> GET Inventory</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=analysisandgraph"><i class="fas fa-chart-bar"></i> Analysis and Graphs</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=returnedequipment"><i class="fas fa-undo"></i> Returned Equipment</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=deletedinventory"><i class="fas fa-trash-alt"></i> Deleted Inventory</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=analysis_of_device"><i class="fas fa-laptop"></i> All Devices</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=calendarScheduler"><i class="fas fa-calendar-alt"></i> Calendar Scheduler</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=datarep"><i class="fas fa-sync"></i> Equipment Replacement</a>
        </li>

         <li>
            <a href="mainmenu.php?dir=repair_frequency"><i class="fas fa-wrench"></i> Repair History</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=ai"><i class="fas fa-robot"></i> AI</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=search_inventory"><i class="fas fa-search"></i> Search Inventory</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=tablesummary"><i class="fas fa-table"></i> Summary</a>
        </li>
    </ul>
</li>

<li>
    <a href="#preventiveSubmenu" data-toggle="collapse" aria-expanded="false" class="menu-toggle">
        <i class="fas fa-tools"></i> Preventive Maintenance
        <span class="arrow"></span>
        <?php if (!empty($tracking2)): ?>
            <span class="notification-badge"><?php echo htmlspecialchars($tracking2); ?></span>
        <?php endif; ?>
    </a>
    <ul class="collapse list-unstyled" id="preventiveSubmenu">
        <li>
            <a href="mainmenu.php?dir=preventive_maintenance&task=schedule" class="submenu-link"><i class="fas fa-calendar-check"></i> Scheduled Maintenance</a>
        </li>
        <li>
            <a href="mainmenu.php?dir=maintenance_report" class="submenu-link"><i class="fas fa-clipboard-list"></i> Maintenance Report</a>
        </li>
    </ul>
</li>


        <li>
        <a href="#servicesSubmenuSRF" data-toggle="collapse" aria-expanded="false" ><i class="fas fa-file-medical"></i> SRF
             <?php if (!empty($tracking)): ?>
                    <span class="notification-badgeSRF"><?php echo $tracking; ?></span>
            <?php endif; ?>
                    <span class="arrow"></span></a>
     <ul class="collapse list-unstyled" id="servicesSubmenuSRF">
                         <li>
             <a href="mainmenu.php?dir=srfrequestform"><i class="fas fa-file-alt"></i> Service Request</a>
         </li>




                <?php
                    // // Include database connection
                    // require_once 'connect.php';

                    // // Query to count rows where Notification_read = 1
                    // $query = "SELECT COUNT(*) AS unread_count FROM srf WHERE Notification_read = 1";
                    // $result = $conn->query($query);

                    // // Fetch the count
                    // $row = $result->fetch_assoc();
                    // $unreadCount = $row['unread_count'];

                    // // Display the badge only if there are unread notifications
                    // $badge = $unreadCount > 0 ? "<span style='background-color:red; color:white; padding:2px 8px; border-radius:50%; font-size:0.8em;'>{$unreadCount}</span>" : "";
                    
                    
                    $station = $_SESSION['StationSRF']; 

                    // Use a prepared statement to prevent SQL injection
                    $query = "SELECT COUNT(*) AS unread_count FROM srf WHERE Notification_read = 1 AND station = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $station); // "s" for string; use "i" if station is an integer
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    // Fetch the count
                    $row = $result->fetch_assoc();
                    $unreadCount = $row['unread_count'];
                    
                    // Display the badge only if there are unread notifications
                    $badge = $unreadCount > 0 ? "<span style='background-color:red; color:white; padding:2px 8px; border-radius:50%; font-size:0.8em;'>{$unreadCount}</span>" : "";

                    
                    
                    ?>


                <li>
                    <a href="mainmenu.php?dir=srfactiontaken"><i class="fas fa-tasks"></i> <?php echo $_SESSION['StationSRF']; ?> SRF <?php echo $badge; ?></a>
                </li>

                <?php
                        if ($_SESSION['User_RoleSRF'] == 'Encoder') {  
                            
                         } else{
                        ?>
                            <li style="position: relative;">
                                <a href="mainmenu.php?dir=requestlist" style="position: relative; display: inline-block;">
                                    <i class="fas fa-list"></i> Request List 
                                    <?php if (!empty($tracking)): ?>
                                        <span class="notification-badge"><?php echo $tracking; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php
                        }
                        ?>



 <script>
// function checkForUpdates() {
//     fetch("fetch_tracking.php") // Replace with your actual PHP script name
//         .then(response => response.json())
//         .then(data => {
//             if (data.newRecord) {
//                 location.reload(); // Refresh the page if a new record is found
//             }
//         })
//         .catch(error => console.error("Error checking updates:", error));
// }


// setInterval(checkForUpdates, 30000);
// </script>



<?php

if ($_SESSION['User_RoleSRF'] == 'Encoder' || $_SESSION['User_RoleSRF'] == 'Verifier' || $_SESSION['User_RoleSRF'] == 'Approver' || $_SESSION['User_RoleSRF'] == 'Recommending_approval') {

                        echo '<li>
                        <a href="mainmenu.php?dir=recent"><i class="fas fa-history"></i> Recent</a>
                            </li>';

                    
}else{
                            echo '<li>
                                    <a href="mainmenu.php?dir=printform"><i class="fas fa-print"></i> Print Form</a>
                                </li>';
                                
                            echo '<li>
                                        <a href="mainmenu.php?dir=srfdatagraph"><i class="fas fa-chart-bar"></i> Data Action Taken</a>
                                 </li>';

                            echo '<li>
                        <a href="mainmenu.php?dir=recent"><i class="fas fa-history"></i> Recent</a>
                            </li>';

      
                    

}

?>

                  <li>
                      <a href="mainmenu.php?dir=srfwaitingnumber"><i class="fas fa-list"></i> Waiting List</a>
                  </li>

            </ul>
        </li>

        
<?php
 

 if ($_SESSION['User_RoleSRF'] == 'Encoder' || $_SESSION['User_RoleSRF'] == 'Verifier' || $_SESSION['User_RoleSRF'] == 'Approver' || $_SESSION['User_RoleSRF'] == 'Recommending_approval') {


    
    
 }else{

            echo '<li>
        
                    <a href="#configurationSRF" data-toggle="collapse" aria-expanded="false" ><i class="fas fa-cog"></i> Configuration<span class="arrow"></span></a>
                        <ul class="collapse list-unstyled" id="configurationSRF">
                            <li>
                                <a href="mainmenu.php?dir=assigntracking"><i class="fas fa-tasks"></i> Assign Tracking</a>
                            </li>


                            
                            <li>
                                <a href="mainmenu.php?dir=assignactionstaff"><i class="fas fa-user-plus"></i> Assign Action Staff</a>
                            </li>

                            <li>
                                <a href="mainmenu.php?dir=otos_employee_include"><i class="fas fa-users"></i> OTOS Employee Include</a>
                            </li>

                            <li>
                                <a href="mainmenu.php?dir=division_counts"><i class="fas fa-table"></i> Division Counts</a>
                            </li>

                            <li>
                                <a href="mainmenu.php?dir=amsos_requestdata"><i class="fas fa-database"></i> Request Data</a>
                            </li>

                        </ul>
                    </li>';

 }



 ?>

    
        <li>
            <a href="mainmenu.php?dir=about"><i class="fas fa-info-circle"></i> About</a>
        </li>
        
        <li>
             <a class="nav-link logout-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
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

            if (!linkHref || linkHref.startsWith('#') || this.dataset.toggle === 'collapse' || this.getAttribute('target') === '_blank' || this.classList.contains('logout-link')) {
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



<script>
// Save and restore sidebar scroll position
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    // Restore scroll position
    const scrollPos = localStorage.getItem("sidebarScrollPos");
    if (scrollPos !== null) {
        sidebar.scrollTop = parseInt(scrollPos, 10);
    }
    // Save scroll position on scroll
    sidebar.addEventListener("scroll", function () {
        localStorage.setItem("sidebarScrollPos", sidebar.scrollTop);
    });
});
</script>
