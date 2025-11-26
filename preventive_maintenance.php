<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Viewer</title>
    <!-- Bootstrap CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Inventory Viewer</h2>
    <!-- Search and Filter Form -->
    <form id="searchForm" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="employeeName" id="employeeName" class="form-control" placeholder="Search by Employee Name">
        </div>
        <div class="col-md-3">
            <select name="officeDivision" id="officeDivision" class="form-select">
                <option value="">Filter by Office Division</option>
                <?php
                require_once "connect.php";
                $result = $conn->query("SELECT DISTINCT officeDivision FROM inv_inventory");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value=\"" . htmlspecialchars($row['officeDivision']) . "\">" . htmlspecialchars($row['officeDivision']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="equipmentType" id="equipmentType" class="form-select">
                <option value="">Filter by Equipment Type</option>
                <?php
                $result = $conn->query("SELECT DISTINCT equipmentType FROM inv_inventory");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value=\"" . htmlspecialchars($row['equipmentType']) . "\">" . htmlspecialchars($row['equipmentType']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-center">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </form>

    <!-- Inventory Table -->
    <table class="table table-striped" id="inventoryTable">
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Equipment Type</th>
                <th>Office Division</th>
                <th>Brand</th>
                <th>Year Acquired</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Results will be appended here via AJAX -->
        </tbody>
    </table>
</div>

<!-- AJAX Script -->
<script>

            document.addEventListener('DOMContentLoaded', function() {
                const searchForm = document.getElementById('searchForm');
                const inventoryTableBody = document.querySelector('#inventoryTable tbody');

                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // Prevent the default form submission

                    const formData = new FormData(searchForm); // Collect form data
                    const queryString = new URLSearchParams(formData).toString(); // Convert to query string

                    // Make the AJAX request
                    fetch('search_preventive_inventory.php?' + queryString, {
                        method: 'GET'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text(); // Expect an HTML response
                    })
                    .then(data => {
                        inventoryTableBody.innerHTML = data; // Update table body with the response
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while processing your request.');
                    });
                });
            });

</script>


</body>
</html>



<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.getElementById('searchForm');
    const inventoryTableBody = document.querySelector('#inventoryTable tbody');

    // Restore search inputs and filters from localStorage
    if (localStorage.getItem('employeeName')) {
        document.getElementById('employeeName').value = localStorage.getItem('employeeName');
    }
    if (localStorage.getItem('officeDivision')) {
        document.getElementById('officeDivision').value = localStorage.getItem('officeDivision');
    }
    if (localStorage.getItem('equipmentType')) {
        document.getElementById('equipmentType').value = localStorage.getItem('equipmentType');
    }

    // Trigger a search automatically when page reloads with saved filters
    const savedFilters = ['employeeName', 'officeDivision', 'equipmentType'];
    if (savedFilters.some(filter => localStorage.getItem(filter))) {
        const formData = new FormData(searchForm); // Collect form data
        const queryString = new URLSearchParams(formData).toString(); // Convert to query string

        // Make the AJAX request
        fetch('search_preventive_inventory.php?' + queryString, {
            method: 'GET'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text(); // Expect an HTML response
            })
            .then(data => {
                inventoryTableBody.innerHTML = data; // Update table body with the response
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            });
    }

    // Save search inputs and filters to localStorage on submit
    searchForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        // Save current inputs/filters to localStorage
        localStorage.setItem('employeeName', document.getElementById('employeeName').value);
        localStorage.setItem('officeDivision', document.getElementById('officeDivision').value);
        localStorage.setItem('equipmentType', document.getElementById('equipmentType').value);

        const formData = new FormData(searchForm); // Collect form data
        const queryString = new URLSearchParams(formData).toString(); // Convert to query string

        // Make the AJAX request
        fetch('search_preventive_inventory.php?' + queryString, {
            method: 'GET'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text(); // Expect an HTML response
            })
            .then(data => {
                inventoryTableBody.innerHTML = data; // Update table body with the response
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            });
    });
});
</script>
