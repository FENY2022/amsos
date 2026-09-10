<?php
require_once 'connect.php'; // Include database connection

// Fetch all records to display in table
$query = "SELECT * FROM srf_actiontaken";
$result = $conn->query($query);

// Update logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $trackid = $_POST['trackid'];
    $userId = $_POST['userId'];
    $name = $_POST['name'];
    $remarks = $_POST['remarks'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    
    $updateQuery = "UPDATE srf_actiontaken SET trackid=?, userId=?, name=?, remarks=?, date=?, time=? WHERE id=?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("isssssi", $trackid, $userId, $name, $remarks, $date, $time, $id);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Record updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating record.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit SRF Action Taken</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Select Record to Edit</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Track ID</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Remarks</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($rowTable = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $rowTable['id']; ?></td>
                        <td><?php echo $rowTable['trackid']; ?></td>
                        <td><?php echo $rowTable['userId']; ?></td>
                        <td><?php echo $rowTable['name']; ?></td>
                        <td><?php echo $rowTable['remarks']; ?></td>
                        <td><?php echo $rowTable['date']; ?></td>
                        <td><?php echo $rowTable['time']; ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" 
                                onclick="fillModal('<?php echo $rowTable['id']; ?>', '<?php echo $rowTable['trackid']; ?>', '<?php echo $rowTable['userId']; ?>', '<?php echo $rowTable['name']; ?>', '<?php echo $rowTable['remarks']; ?>', '<?php echo $rowTable['date']; ?>', '<?php echo $rowTable['time']; ?>')">Edit</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit SRF Action Taken</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label class="form-label">Track ID:</label>
                            <input type="number" id="edit_trackid" name="trackid" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">User ID:</label>
                            <input type="text" id="edit_userId" name="userId" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name:</label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks:</label>
                            <textarea id="edit_remarks" name="remarks" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" id="edit_date" name="date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time:</label>
                            <input type="text" id="edit_time" name="time" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillModal(id, trackid, userId, name, remarks, date, time) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_trackid').value = trackid;
            document.getElementById('edit_userId').value = userId;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_remarks').value = remarks;
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_time').value = time;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>