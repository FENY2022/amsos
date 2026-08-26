<?php
if (isset($_GET['dir']) && !empty($_GET['dir'])) {
    $_dirlist = $_GET['dir'];
} else {
    $_dirlist = "";
}

require_once 'srf_request_notification_helpers.php';

$currentSrfUserId = isset($_SESSION['idSRF']) ? (int)$_SESSION['idSRF'] : 0;
$srfRequestUnreadCount = 0;
$srfRequestNotifications = array();
$pusherConfig = require 'pusher_config.php';

if ($currentSrfUserId > 0 && isset($conn) && $conn instanceof mysqli) {
    $srfRequestUnreadCount = getUnreadSrfRequestNotificationCount($conn, $currentSrfUserId);
    $srfRequestNotifications = getUnreadSrfRequestNotifications($conn, $currentSrfUserId, 5);
}


if ($_dirlist == 'entrydatahidesidebar') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const navbar = document.querySelector('.navbar');
            sidebar.style.marginLeft = '-250px';
            navbar.style.display = 'none';
            localStorage.setItem('sidebarHidden', 'true');
        });
    </script>";
}

?>


<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <button type="button" id="sidebarCollapse" class="btn btn-info">
        =
    </button>
    <a class="navbar-brand ml-3" href="#">ICT-AMSOS</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
                <a class="nav-link" href="#">Home</a>
            </li>
            <li class="nav-item dropdown srf-bell-nav">
                <a class="nav-link dropdown-toggle srf-bell-link" href="#" id="srfRequestBell" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <span id="srfRequestBellBadge" class="srf-bell-badge" style="<?php echo $srfRequestUnreadCount > 0 ? '' : 'display:none;'; ?>"><?php echo (int)$srfRequestUnreadCount; ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right srf-notification-menu" aria-labelledby="srfRequestBell" id="srfRequestNotificationMenu">
                    <div class="srf-notification-header">
                        <strong>Request Notifications</strong>
                    </div>
                    <div id="srfRequestNotificationItems">
                        <?php if (!empty($srfRequestNotifications)): ?>
                            <?php foreach ($srfRequestNotifications as $notification): ?>
                                <a class="dropdown-item srf-notification-item" href="mark_srf_request_notification_read.php?srf_id=<?php echo (int)$notification['id']; ?>">
                                    <span class="srf-notification-title"><?php echo htmlspecialchars($notification['ticketNumber']); ?></span>
                                    <span class="srf-notification-text"><?php echo htmlspecialchars($notification['name']); ?> - <?php echo htmlspecialchars($notification['requestType']); ?></span>
                                    <span class="srf-notification-time"><?php echo htmlspecialchars($notification['status']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dropdown-item text-muted srf-notification-empty" id="srfRequestNotificationEmpty">No new requests</div>
                        <?php endif; ?>
                    </div>
                    <div class="srf-notification-footer">
                        <a href="mainmenu.php?dir=requestlist">View request list</a>
                    </div>
                </div>
            </li>
            <li class="nav-item">
                <button type="button" class="btn btn-sm btn-outline-secondary nav-notification-btn" id="allowBrowserNotifications" style="display:none;">
                    Allow Notifications
                </button>
            </li>
            <li class="nav-item">
                <a class="nav-link logout-link" href="logout.php">Log Out</a>
            </li>
        </ul>
    </div>
</nav>

<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById("sidebar");
        const sidebarCollapse = document.getElementById("sidebarCollapse");
        const currentSrfUserId = <?php echo (int)$currentSrfUserId; ?>;
        const pusherKey = <?php echo json_encode($pusherConfig['app_key']); ?>;
        const pusherCluster = <?php echo json_encode($pusherConfig['cluster']); ?>;
        const bellBadge = document.getElementById('srfRequestBellBadge');
        const notificationItems = document.getElementById('srfRequestNotificationItems');
        const allowBrowserNotifications = document.getElementById('allowBrowserNotifications');

        function updateBellBadge(count) {
            if (!bellBadge) return;
            bellBadge.textContent = count;
            bellBadge.style.display = count > 0 ? '' : 'none';
        }

        function getBellBadgeCount() {
            return bellBadge ? parseInt(bellBadge.textContent || '0', 10) || 0 : 0;
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>'"]/g, function (char) {
                return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'})[char];
            });
        }

        function addRequestNotification(data) {
            if (!notificationItems || !data || !data.srf_id) return;

            const emptyItem = document.getElementById('srfRequestNotificationEmpty');
            if (emptyItem) emptyItem.remove();

            const item = document.createElement('a');
            item.className = 'dropdown-item srf-notification-item';
            item.href = 'mark_srf_request_notification_read.php?srf_id=' + encodeURIComponent(data.srf_id);
            item.innerHTML = '<span class="srf-notification-title">' + escapeHtml(data.ticketNumber) + '</span>' +
                '<span class="srf-notification-text">' + escapeHtml(data.name) + ' - ' + escapeHtml(data.requestType) + '</span>' +
                '<span class="srf-notification-time">New request</span>';

            notificationItems.insertBefore(item, notificationItems.firstChild);
            updateBellBadge(getBellBadgeCount() + 1);
        }

        function refreshBrowserNotificationButton() {
            if (!allowBrowserNotifications || !('Notification' in window)) return;
            allowBrowserNotifications.style.display = Notification.permission === 'default' ? '' : 'none';
        }

        if (allowBrowserNotifications && 'Notification' in window) {
            refreshBrowserNotificationButton();
            allowBrowserNotifications.addEventListener('click', function () {
                Notification.requestPermission().then(refreshBrowserNotificationButton);
            });
        }

        function showBrowserNotification(data) {
            if (!('Notification' in window) || Notification.permission !== 'granted') return;

            const title = 'New SRF Request';
            const body = (data.ticketNumber || 'New ticket') + ' - ' + (data.requestType || 'Service Request');
            const notification = new Notification(title, { body: body, icon: 'icon/amsos.ico' });
            notification.onclick = function () {
                window.focus();
                window.location.href = 'mark_srf_request_notification_read.php?srf_id=' + encodeURIComponent(data.srf_id);
            };
        }

        if (currentSrfUserId > 0 && typeof Pusher !== 'undefined') {
            const pusher = new Pusher(pusherKey, {
                cluster: pusherCluster,
                forceTLS: true,
                authEndpoint: 'pusher_auth.php'
            });

            const channel = pusher.subscribe('private-srf-request-user-' + currentSrfUserId);
            channel.bind('new-srf-request', function (data) {
                addRequestNotification(data);
                showBrowserNotification(data);
            });
        }

        // Load sidebar state from localStorage
        if (localStorage.getItem("sidebarHidden") === "true") {
            sidebar.style.marginLeft = "-250px"; // Hide sidebar
        }

        // Toggle sidebar on button click
        sidebarCollapse.addEventListener("click", function () {
            if (sidebar.style.marginLeft === "-250px") {
                sidebar.style.marginLeft = "0";
                localStorage.setItem("sidebarHidden", "false");
            } else {
                sidebar.style.marginLeft = "-250px";
                localStorage.setItem("sidebarHidden", "true");
            }
        });
    });
</script>

<style>
    .sidebar {
        padding: 20px;
    }

    .srf-bell-link {
        position: relative;
    }

    .srf-bell-badge {
        position: absolute;
        top: 2px;
        right: 0;
        min-width: 18px;
        height: 18px;
        padding: 1px 5px;
        border-radius: 999px;
        background: #dc3545;
        color: #fff;
        font-size: 11px;
        line-height: 16px;
        text-align: center;
        font-weight: 700;
    }

    .srf-notification-menu {
        width: 320px;
        padding: 0;
        overflow: hidden;
    }

    .srf-notification-header,
    .srf-notification-footer {
        padding: 10px 14px;
        background: #f8f9fa;
    }

    .srf-notification-footer {
        text-align: center;
        border-top: 1px solid #e9ecef;
    }

    .srf-notification-item {
        white-space: normal;
        border-top: 1px solid #f1f3f5;
    }

    .srf-notification-title,
    .srf-notification-text,
    .srf-notification-time {
        display: block;
    }

    .srf-notification-title {
        font-weight: 700;
        color: #212529;
    }

    .srf-notification-text {
        font-size: 13px;
        color: #495057;
    }

    .srf-notification-time {
        font-size: 12px;
        color: #868e96;
    }

    .nav-notification-btn {
        margin: 6px 8px 0 0;
    }
</style>
