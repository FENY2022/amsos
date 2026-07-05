<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Optional: Custom styles for better spacing or specific element adjustments */
        body {
            background-color: #f8f9fa; /* Light background for the page */
        }
        /* Changed from .container-fluid to .container-xl in HTML, no CSS needed here for that */
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); /* subtle shadow for the card */
        }
        .table thead th {
            background-color: #e9ecef; /* Light gray background for table headers */
        }
        .btn-sm {
            margin-right: 5px; /* Spacing between buttons */
        }
        /* Ensure textareas and inputs take full width of their table cell */
        .form-control {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container-xl"> <div class="row justify-content-center">
        <div class="col-12"> 
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="card-title mb-0">Edit Feedback Entries</h2>
                </div>
                <div class="card-body">
                    <?php
                    require_once 'connect_amsos.php';

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
                            echo "<tr id='row_" . $id . "'>
                                    <td>" . $id . "</td>
                                    <td>" . htmlspecialchars($row['srf_id']) . "</td>
                                    <td id='feedback_" . $id . "'>" . htmlspecialchars($row['feedback']) . "</td>
                                    <td id='acknowledged_by_" . $id . "'>" . htmlspecialchars($row['acknowledged_by']) . "</td>
                                    <td id='created_at_" . $id . "' data-raw-date='" . $created_at . "'>" . date("F j, Y", strtotime($created_at)) . "</td>
                                    <td id='date_rated_" . $id . "' data-raw-date='" . $date_rated . "'>" . date("F j, Y", strtotime($date_rated)) . "</td>
                                    <td>
                                        <button onclick='editRow(" . $id . ")' class='btn btn-primary btn-sm'>Edit</button>
                                        <button onclick='deleteRow(" . $id . ")' class='btn btn-danger btn-sm'>Delete</button>
                                    </td>
                                </tr>";
                        }

                        echo "</tbody></table>";
                        echo "</div>"; // End table-responsive
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

<script>
    function formatDateForDisplay(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }

    function editRow(id) {
        const feedback = document.getElementById('feedback_' + id).textContent;
        const acknowledgedBy = document.getElementById('acknowledged_by_' + id).textContent;
        const createdAt = document.getElementById('created_at_' + id).getAttribute('data-raw-date');
        const dateRated = document.getElementById('date_rated_' + id).getAttribute('data-raw-date');

        // Added inline style 'width: 100%' for the input and textarea
        document.getElementById('feedback_' + id).innerHTML = '<textarea id=\"feedback_input_' + id + '\" class=\"form-control\" style=\"width: 100%;\">' + feedback + '</textarea>';
        document.getElementById('acknowledged_by_' + id).innerHTML = '<input type=\"text\" id=\"acknowledged_by_input_' + id + '\" value=\"' + acknowledgedBy + '\" class=\"form-control\" style=\"width: 100%;\">';
        document.getElementById('created_at_' + id).innerHTML = '<input type=\"text\" id=\"created_at_input_' + id + '\" value=\"' + createdAt + '\" class=\"form-control\">';
        document.getElementById('date_rated_' + id).innerHTML = '<input type=\"text\" id=\"date_rated_input_' + id + '\" value=\"' + dateRated + '\" class=\"form-control\">';
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
                document.getElementById('feedback_' + id).textContent = feedback;
                document.getElementById('acknowledged_by_' + id).textContent = acknowledgedBy;
                
                const createdAtCell = document.getElementById('created_at_' + id);
                createdAtCell.setAttribute('data-raw-date', createdAt);
                createdAtCell.textContent = formatDateForDisplay(createdAt);
                
                const dateRatedCell = document.getElementById('date_rated_' + id);
                dateRatedCell.setAttribute('data-raw-date', dateRated);
                dateRatedCell.textContent = formatDateForDisplay(dateRated);

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
            fetch('delete_feedback.php', {
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
