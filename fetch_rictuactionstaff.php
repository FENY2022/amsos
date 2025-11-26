<?php


    $sql = "SELECT * FROM srfactionstaff WHERE Office = '{$_SESSION['OfficeSRF']}'";


$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . strtoupper($row['name']) . "</td>";
        echo "<td>" . $row['office'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>";

        echo "<div class='dropdown'>
                <button class='btn btn-secondary dropdown-toggle' type='button' id='dropdownMenuButton{$row['id']}' data-bs-toggle='dropdown' aria-expanded='false'>
                        Action
                    </button>
                    <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton{$row['id']}'>
                        <li><a class='dropdown-item bg-success text-white' href='#' data-bs-toggle='modal' data-bs-target='#uploadsignature{$row['id']}'>Upload Signature</a></li>
                        <li><a class='dropdown-item bg-danger text-white' href='#' data-bs-toggle='modal' data-bs-target='#delete{$row['id']}'>Delete</a></li>
                        <li><a class='dropdown-item bg-info text-white' href='#' data-bs-toggle='modal' data-bs-target='#editPasswordModal{$row['id']}'>Edit Password</a></li>
                    </ul>
                    </ul>
            </div>";

        echo "</td>";

        echo "<td>";

        echo "<div class='modal fade' id='delete{$row['id']}' tabindex='-1' aria-hidden='true'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header bg-danger'>
                            <h5 class='modal-title text-white'>System Information</h5>
                            <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
                                <span aria-hidden='true'>×</span>
                            </button>
                        </div>
                        <div class='modal-body'>ID: ({$row['id']}) Are you sure you want to Delete this record?</div>
                        <div class='modal-footer'>
                            <button class='btn btn-secondary' type='button' data-dismiss='modal'>Cancel</button>
                            <a class='btn btn-danger' href='srfactionstaffdelete.php?id={$row['id']}'>Delete</a>
                        </div>
                    </div>
                </div>
            </div>";

            echo "
            <!-- Modal -->
            <div class='modal fade' id='uploadsignature{$row['id']}' tabindex='-1' aria-labelledby='uploadSignatureLabel{$row['id']}' aria-hidden='true'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title' id='uploadSignatureLabel{$row['id']}'>Upload Signature</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                        <div class='modal-body'>
                            <!-- Existing Signature Viewer -->
                            <div class='mb-3'>
                                <label class='form-label'>Current Signature:</label><br>
                                <img src='srfsigner/{$row['signature']}' alt='No Signature Uploaded' id='currentSignature{$row['id']}' 
                                     class='img-fluid border rounded' style='max-width: 100%; max-height: 300px;' />
                            </div>
                            <!-- File Upload Form -->
                            <form id='uploadSignatureForm{$row['id']}' action='upload_signature.php' method='POST' enctype='multipart/form-data'>
                                <div class='mb-3'>
                                    <label for='signatureFile{$row['id']}' class='form-label'>Choose New Signature File</label>
                                    <input type='file' class='form-control' id='signatureFile{$row['id']}' name='signature_file' required>
                                </div>
                                <div class='mb-3'>
                                    <!-- Preview of Selected File -->
                                    <label class='form-label'>Preview:</label><br>
                                    <img id='previewImage{$row['id']}' src='#' alt='No file chosen' 
                                         class='img-fluid border rounded' style='max-width: 100%; max-height: 300px; display: none;' />
                                </div>
                                <input type='hidden' name='id' value='{$row['id']}'>
                            </form>
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                            <button type='submit' class='btn btn-primary' form='uploadSignatureForm{$row['id']}'>Upload</button>
                        </div>
                    </div>
                </div>
            </div>


            
            <script>
                // JavaScript to preview selected file
                document.getElementById('signatureFile{$row['id']}').addEventListener('change', function(event) {
                    const preview = document.getElementById('previewImage{$row['id']}');
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.src = '#';
                        preview.style.display = 'none';
                    }
                });
            </script>
            ";
            


            echo "
            <div class='modal fade' id='editPasswordModal{$row['id']}' tabindex='-1' aria-labelledby='editPasswordModalLabel{$row['id']}' aria-hidden='true'>
              <div class='modal-dialog'>
                <div class='modal-content'>
                  <div class='modal-header'>
                    <h5 class='modal-title' id='editPasswordModalLabel{$row['id']}'>Edit Password</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                  </div>
                  <div class='modal-body'>
                    <!-- The form to update the password -->
                    <form action='edit_password.php' method='post'>
                      <!-- Display User Information -->
                      <div class='mb-3'>
                        <label for='username{$row['id']}' class='form-label'>User</label>
                        <input type='text' class='form-control' id='username{$row['id']}' name='username' value='' >
                      </div>
                      <div class='mb-3'>
                        <label for='newPassword{$row['id']}' class='form-label'>New Password</label>
                        <div class='input-group'>
                          <input type='password' class='form-control' id='newPassword{$row['id']}' name='password' required>
                          <button type='button' class='btn btn-outline-secondary' id='generatePassword{$row['id']}'>Generate</button>
                          <button type='button' class='btn btn-outline-secondary' id='togglePassword{$row['id']}'><i class='bi bi-eye'></i></button>
                        </div>
                        <small class='form-text text-muted mt-2'>Suggested strong password will appear here.</small>
                      </div>
                      <!-- Pass the user ID (or record ID) for identification -->
                      <input type='hidden' name='id' value='{$row['personelid']}'>
                      <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                        <button type='submit' class='btn btn-primary'>Save changes</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            
            <script>
            // Password Generator
            document.getElementById('generatePassword{$row['id']}').addEventListener('click', function () {
              const passwordInput = document.getElementById('newPassword{$row['id']}');
              const generatedPassword = generateStrongPassword();
              passwordInput.value = generatedPassword;
            });
            
            function generateStrongPassword() {
              const length = 12; // Length of the password
              const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]:;?><,./-=';
              let password = '';
              for (let i = 0; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * charset.length);
                password += charset[randomIndex];
              }
              return password;
            }
            
            // Toggle Password Visibility
            document.getElementById('togglePassword{$row['id']}').addEventListener('click', function () {
              const passwordInput = document.getElementById('newPassword{$row['id']}');
              const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
              passwordInput.setAttribute('type', type);
            
              // Toggle the eye icon
              const eyeIcon = this.querySelector('i');
              if (type === 'password') {
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
              } else {
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
              }
            });
            </script>
          ";
          


            echo "</td>";

        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No results found</td></tr>";
}

$conn->close();
?>
