<?php
if (isset($_GET['dir']) && !empty($_GET['dir'])) {
    $_dirlist = $_GET['dir'];
} else {
    $_dirlist = "";
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
            <li class="nav-item">
                <a class="nav-link" href="#" id="logoutLink">Log Out</a>
            </li>
        </ul>
    </div>
</nav>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById("sidebar");
        const sidebarCollapse = document.getElementById("sidebarCollapse");

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
</style>
