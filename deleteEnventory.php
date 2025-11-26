    <?php

           $id = $_POST['id'];
                require_once 'connect.php';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
        
 
    
        // Prepare and execute delete query
        $stmt = $conn->prepare("DELETE FROM inv_inventory WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
        

            echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
            echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
            echo '<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                    <div class="toast show align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                Record deleted successfully
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>';
          

            exit();
        } else {
            $_SESSION['error'] = "Error deleting record: " . $conn->error;
              header("Location: editEnventory.php?id=" . $_POST['id']);            exit();
        }
        
        $stmt->close();
        $conn->close();
    }
    ?>
