<?php
require_once "connect_otos.php";

$office = $_POST['office'] ?? '';
$station = $_POST['station'] ?? '';
$fullname = trim($_POST['fullname'] ?? '');

$sql = "SELECT * FROM useremployee WHERE Office = ?";
$params = [$office];

if (!empty($station)) {
    $sql .= " AND Station = ?";
    $params[] = $station;
}

if (!empty($fullname)) {
    $sql .= " AND Full_Name LIKE ?";
    $params[] = "%$fullname%";
}

$stmt = $conn_otos->prepare($sql);
if ($params) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();


// Styling the scrollable container
echo '<div style="max-height: 400px; overflow: auto; border: 1px solid #ccc; padding: 10px; border-radius: 8px;">';
echo '<table style="width: 100%; border-collapse: collapse;">';
echo '<tr style="background-color: #4CAF50; color: white;">';
echo '<th style="padding: 8px; text-align: left;">Full Name</th>';
echo '<th style="padding: 8px; text-align: left;">Office</th>';
echo '<th style="padding: 8px; text-align: left;">Station</th>';
echo '<th style="padding: 8px; text-align: left;">Action</th>';
echo '</tr>';

// Initialize row counter
$rowCount = 0;

// Loop through results
while ($row = $result->fetch_assoc()) {
    $srfId = $row['id'];
    $decryptedPassword = $row['password_dcryp'];
    $username = $row['username'];
    $rowCount++; // Increment row counter
    echo '<tr style="border-bottom: 1px solid #ddd;">';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['Full_Name']) . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['Office']) . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['Station']) . '</td>';
    echo '<td style="padding: 8px;">';
    echo "<div class='dropdown'>
            <button class='btn btn-danger dropdown-toggle' type='button' id='dropdownMenuButton{$srfId}' data-bs-toggle='dropdown' aria-expanded='false'>
                Action
            </button>
            <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton{$srfId}'>
                <li><a class='dropdown-item bg-info text-white' href='#' data-bs-toggle='modal' data-bs-target='#showpassword{$srfId}'>Showpassword</a></li>
            </ul>
          </div>";
    echo '</td>';


    echo "
    <div class='modal fade' id='showpassword{$srfId}' tabindex='-1' aria-labelledby='passwordModalLabel{$srfId}' aria-hidden='true'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class='modal-header'>
            <h5 class='modal-title' id='passwordModalLabel{$srfId}'>Account</h5>
            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class='modal-body'>
            <p><strong>Full Name:</strong> " . htmlspecialchars($row['Full_Name']) . "</p>
            <p><strong>User:</strong> " . $username . "</p>
            <p><strong>Decrypted Password:</strong> " . $decryptedPassword . "</p>
          </div>
          <div class='modal-footer'>
            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
          </div>
        </div>
      </div>
    </div>";


    echo "
    <div class='modal fade' id='editUserDetails{$srfId}' tabindex='-1' aria-labelledby='editUserDetailsModalLabel{$srfId}' aria-hidden='true'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class='modal-header'>
            <h5 class='modal-title' id='editUserDetailsModalLabel{$srfId}'>Edit User Details</h5>
            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class='modal-body'>
            <form method='post' action='update_user.php'>
              <input type='hidden' name='srfId' value='{$srfId}'>
              
              <!-- Username Field -->
              <div class='mb-3'>
                <label for='username{$srfId}' class='form-label'>Username</label>
                <input type='text' class='form-control' id='username{$srfId}' name='username' value='{$username}' required>
              </div>
              
              <!-- Password Fields -->
              <div class='mb-3'>
                <label for='currentPassword{$srfId}' class='form-label'>Current Password</label>
                <input type='password' class='form-control' id='currentPassword{$srfId}' name='currentPassword' required>
              </div>
              
              <div class='mb-3'>
                <label for='newPassword{$srfId}' class='form-label'>New Password</label>
                <input type='password' class='form-control' id='newPassword{$srfId}' name='newPassword'>
              </div>
              
              <div class='mb-3'>
                <label for='confirmPassword{$srfId}' class='form-label'>Confirm New Password</label>
                <input type='password' class='form-control' id='confirmPassword{$srfId}' name='confirmPassword'>
              </div>
              
              <button type='submit' class='btn btn-primary'>Update Details</button>
            </form>
          </div>
          <div class='modal-footer'>
            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
          </div>
        </div>
      </div>
    </div>";


    
    echo '</tr>';
}

// Display row count
echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
echo '<td colspan="3" style="padding: 8px; text-align: right;">Total Rows:</td>';
echo '<td style="padding: 8px; text-align: left;">' . $rowCount . '</td>';
echo '</tr>';

echo '</table>';
echo '</div>';

$stmt->close();
$conn->close();
?>
