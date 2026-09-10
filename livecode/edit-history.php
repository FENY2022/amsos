<?php
require_once 'connect.php'; // Include database connection

// Fetch all records to display in table
$query = "SELECT * FROM srfhistory";
$result = $conn->query($query);

// Update logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $trackid = $_POST['trackid'];
    $name = $_POST['name'];
    $details = $_POST['details'];
    $equipment_id = $_POST['equipment_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $status = $_POST['status'];
    $personnel = $_POST['personnel'];

    $updateQuery = "UPDATE srfhistory SET trackid=?, name=?, details=?, equipment_id=?, date=?, time=?, status=?, personnel=? WHERE id=?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ississssi", $trackid, $name, $details, $equipment_id, $date, $time, $status, $personnel, $id);
    
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
    <title>Edit SRF History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Select SRF History to Edit</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Track ID</th>
                    <th>Name</th>
                    <th>Details</th>
                    <th>Equipment ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Personnel</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['trackid']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['details']; ?></td>
                        <td><?php echo $row['equipment_id']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['time']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['personnel']; ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">Edit</button>
                        </td>
                    </tr>
                    
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Edit SRF History</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Track ID:</label>
                                            <input type="text" name="trackid" class="form-control" value="<?php echo htmlspecialchars($row['trackid']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Name:</label>
                                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Details:</label>
                                            <textarea name="details" class="form-control" required><?php echo htmlspecialchars($row['details']); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Equipment ID:</label>
                                            <input type="number" name="equipment_id" class="form-control" value="<?php echo htmlspecialchars($row['equipment_id']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Date:</label>
                                            <input type="text" name="date" class="form-control" value="<?php echo htmlspecialchars($row['date']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Time:</label>
                                            <input type="text" name="time" class="form-control" value="<?php echo htmlspecialchars($row['time']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status:</label>
                                            <input type="text" name="status" class="form-control" value="<?php echo htmlspecialchars($row['status']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Personnel:</label>
                                            <input type="text" name="personnel" class="form-control" value="<?php echo htmlspecialchars($row['personnel']); ?>" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </tbody>
        </table>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
