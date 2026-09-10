<?php

// Include database connection
require_once 'connect.php';
$idSRF = $_SESSION['idSRF'];

// Fetch notifications from the database, sorted by 'action' in ascending order
$query = "SELECT * FROM inv_notification WHERE tracking = $idSRF ORDER BY action ASC";
$result = $conn->query($query);

// Check for query errors
if (!$result) {
    die("Query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preventive Maintenance </title>
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

    <div class="container mt-5">
        <h2 class="mb-4">Preventive Maintenance Notifications</h2>
        
        <!-- Search Bar -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" id="search" class="form-control" placeholder="Search notifications...">
            </div>
        </div>

        <!-- Notifications Table -->
        <table class="table table-striped table-bordered" id="notificationTable">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Property Name</th>
                    <th>Property Number</th>
                    <th>Details</th>
                    <th>Remarks</th>
                    <th>Brand</th>
                    <th>Action</th>
                    <th>Created At</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['property_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['property_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['details']); ?></td>
                        <td><?php echo htmlspecialchars($row['remarks'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['brand']); ?></td>
                        <td>
                            <?php if ($row['action'] == 0): ?>
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    Action
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <li><a class="dropdown-item bg-warning text-white" href="#" data-bs-toggle="modal" data-bs-target="#noted<?php echo $row['id']?>">Acknowledge</a></li>
                                    <li><a class="dropdown-item bg-danger text-white" href="#" data-bs-toggle="modal" data-bs-target="#deleteborrower<?php echo $row['id']?>">Remarks</a></li>
                                </ul>

                                        <!-- Modal Structure with Form Method -->
                                        <div class="modal fade" id="noted<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="notedLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="acknowlege_notification.php">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="notedLabel<?php echo $row['id']; ?>">Are you sure?</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php echo htmlspecialchars($row['remarks']); ?>
                                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Acknowledge</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        


                            <?php else: ?>
                                <span class="badge bg-secondary">Accomplished</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Search Functionality
        $(document).ready(function() {
            $("#search").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#notificationTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
