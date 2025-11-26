<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRF Action Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">SRF Action Details</h2>

    <!-- PHP to fetch and display SRF details -->
    <?php
 
    // Database connection
    require_once 'connect.php';

    // Fetch SRF details including file paths
    $sql = "SELECT trackid, user, remarks, file_path FROM srfaction_details WHERE trackid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_GET['id']); // Bind the track ID
    $stmt->execute();
    $result = $stmt->get_result();

    // Display records in a table
    echo "<table class='table table-bordered'>";
    echo "<thead>
            <tr>
                <th>Track ID</th>
                <th>User</th>
                <th>Remarks</th>
                <th>File</th>
            </tr>
          </thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['trackid']}</td>
                <td>{$row['user']}</td>
                <td>{$row['remarks']}</td>";
                
        // If there is a file path, display the file viewer link
        if (!empty($row['file_path'])) {
            echo "<td><a href='#' class='btn btn-primary' data-toggle='modal' data-target='#fileModal' data-filepath='{$row['file_path']}'>View File</a></td>";
        } else {
            echo "<td>No file uploaded</td>";
        }
        
        echo "</tr>";
    }
    echo "</tbody></table>";

    $stmt->close();
    $conn->close();
    ?>
</div>

<!-- Modal to display the file -->
<div class="modal fade" id="fileModal" tabindex="-1" role="dialog" aria-labelledby="fileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileModalLabel">View File</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="fileViewer" src="" width="100%" height="500px" style="border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // When the modal is shown, load the file in the iframe
    $('#fileModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var filePath = button.data('filepath'); // Extract info from data-filepath attribute
        var modal = $(this);
        modal.find('#fileViewer').attr('src', filePath);
    });

    // Clear the iframe when the modal is closed
    $('#fileModal').on('hide.bs.modal', function (event) {
        var modal = $(this);
        modal.find('#fileViewer').attr('src', '');
    });
</script>

</body>
</html>
