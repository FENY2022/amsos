<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Feedback</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .table thead th {
            background-color: #e9ecef;
        }
        .btn-sm {
            margin-right: 5px;
        }
        .form-control {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container-xl"> 
    <div class="row justify-content-center">
        <div class="col-12"> 
            <div class="card mt-4"> 
                <div class="card-header bg-primary text-white">
                    <h2 class="card-title mb-0">Edit Feedback Entries</h2>
                </div>
                <div class="card-body">
                    <?php
                    require_once 'connect.php';

                    $trackid = $_GET['id'] ?? '';

                    $query = "SELECT 
                                id,
                                srf_id,
                                feedback,
                                acknowledged_by,
                                created_at,
                                date_rated
                            FROM srffeedback
                            WHERE srf_id = ?";

                    $stmt = $conn->prepare($query);

                    if ($stmt === false) {
                        die('Prepare failed: ' . htmlspecialchars($conn->error));
                    }

                    $stmt->bind_param("s", $trackid);

                    if (!$stmt->execute()) {
                        die('Execute failed: ' . htmlspecialchars($stmt->error));
                    }

                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-striped table-hover table-bordered'>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>SRF ID</th>
                                        <th>Feedback</th>
                                        <th>Acknowledged By</th>
                                        <th>Created At</th>
                                        <th>Date Rated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>";

                        while ($row = $result->fetch_assoc()) {
                            $id = $row['id'];
                            $created_at = htmlspecialchars($row['created_at']);
                            $date_rated = htmlspecialchars($row['date_rated']);
                            
                            // CHANGED: Display format uses H:i for 24-hour time
                            $display_created = date("F j, Y, H:i", strtotime($created_at));
                            $display_rated = date("F j, Y, H:i", strtotime($date_rated));

                            echo "<tr id='row_" . $id . "'>
                                    <td>" . $id . "</td>
                                    <td>" . htmlspecialchars($row['srf_id']) . "</td>
                                    <td id='feedback_" . $id . "'>" . htmlspecialchars($row['feedback']) . "</td>
                                    <td id='acknowledged_by_" . $id . "'>" . htmlspecialchars($row['acknowledged_by']) . "</td>
                                    <td id='created_at_" . $id . "' data-raw-date='" . $created_at . "'>" . $display_created . "</td>
                                    <td id='date_rated_" . $id . "' data-raw-date='" . $date_rated . "'>" . $display_rated . "</td>
                                    <td>
                                        <button onclick='editRow(" . $id . ")' class='btn btn-primary btn-sm'>Edit</button>
                                        <button onclick='deleteRow(" . $id . ")' class='btn btn-danger btn-sm'>Delete</button>
                                    </td>
                                </tr>";
                        }

                        echo "</tbody></table>";
                        echo "</div>";
                    } else {
                        echo "<div class='alert alert-info text-center' role='alert'>
                                No records found for the provided Track ID.
                            </div>";
                    }

                    $stmt->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    function formatDateForDisplay(dateString) {
        const date = new Date(dateString.replace(/-/g, '/')); 
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false // CHANGED: Forces 24-hour format in JavaScript
        };
        return date.toLocaleDateString('en-US', options);
    }

    function editRow(id) {
        const feedback = document.getElementById('feedback_' + id).textContent;
        const acknowledgedBy = document.getElementById('acknowledged_by_' + id).textContent;
        
        const rawCreatedAt = document.getElementById('created_at_' + id).getAttribute('data-raw-date');
        const rawDateRated = document.getElementById('date_rated_' + id).getAttribute('data-raw-date');

        // Replace text content with input fields
        document.getElementById('feedback_' + id).innerHTML = '<textarea id=\"feedback_input_' + id + '\" class=\"form-control\" style=\"width: 100%;\">' + feedback + '</textarea>';
        document.getElementById('acknowledged_by_' + id).innerHTML = '<input type=\"text\" id=\"acknowledged_by_input_' + id + '\" value=\"' + acknowledgedBy + '\" class=\"form-control\" style=\"width: 100%;\">';
        
        // Setup inputs for Flatpickr
        document.getElementById('created_at_' + id).innerHTML = '<input type=\"text\" id=\"created_at_input_' + id + '\" value=\"' + rawCreatedAt + '\" class=\"form-control bg-white\">';
        document.getElementById('date_rated_' + id).innerHTML = '<input type=\"text\" id=\"date_rated_input_' + id + '\" value=\"' + rawDateRated + '\" class=\"form-control bg-white\">';
        
        // CHANGED: Flatpickr config updated for 24-hour time
        const flatpickrConfig = {
            enableTime: true,
            time_24hr: true, // Tells the time picker to use 24-hour mode
            dateFormat: "Y-m-d H:i:S", 
            altInput: true,
            altFormat: "F j, Y, H:i", // Displays as "Month DD, YYYY, HH:MM"
            allowInput: true
        };

        flatpickr("#created_at_input_" + id, flatpickrConfig);
        flatpickr("#date_rated_input_" + id, flatpickrConfig);

        // Update Action buttons
        const actionCell = document.getElementById('row_' + id).lastElementChild;
        actionCell.innerHTML = '<button onclick=\"saveRow(' + id + ')\" class=\"btn btn-success btn-sm\">Save</button>' +
                                '<button onclick=\"cancelEdit(' + id + ')\" class=\"btn btn-secondary btn-sm\">Cancel</button>';
    }

    function saveRow(id) {
        const feedback = document.getElementById('feedback_input_' + id).value;
        const acknowledgedBy = document.getElementById('acknowledged_by_input_' + id).value;
        
        const createdAt = document.getElementById('created_at_input_' + id).value;
        const dateRated = document.getElementById('date_rated_input_' + id).value;

        fetch('update_action3.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id,
                feedback: feedback,
                acknowledged_by: acknowledgedBy,
                created_at: createdAt,
                date_rated: dateRated
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Revert to plain text
                document.getElementById('feedback_' + id).textContent = feedback;
                document.getElementById('acknowledged_by_' + id).textContent = acknowledgedBy;
                
                const createdAtCell = document.getElementById('created_at_' + id);
                createdAtCell.setAttribute('data-raw-date', createdAt);
                createdAtCell.textContent = formatDateForDisplay(createdAt);
                
                const dateRatedCell = document.getElementById('date_rated_' + id);
                dateRatedCell.setAttribute('data-raw-date', dateRated);
                dateRatedCell.textContent = formatDateForDisplay(dateRated);

                // Reset action buttons
                const actionCell = document.getElementById('row_' + id).lastElementChild;
                actionCell.innerHTML = '<button onclick=\"editRow(' + id + ')\" class=\"btn btn-primary btn-sm\">Edit</button>' +
                                        '<button onclick=\"deleteRow(' + id + ')\" class=\"btn btn-danger btn-sm\">Delete</button>';
            } else {
                alert('Update failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during update.');
        });
    }

    function cancelEdit(id) {
        location.reload();
    }

    function deleteRow(id) {
        if (confirm('Are you sure you want to delete this record?')) {
            fetch('delete_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('row_' + id).remove();
                } else {
                    alert('Delete failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during deletion.');
            });
        }
    }
</script>

</body>
</html>