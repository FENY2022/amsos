             <?php   
                
                $servername = "153.92.15.60";
                $username = "u645536029_otos_root";
                $password = "6yI3PF3OZ";
                $dbname = "u645536029_otos";

                    $conn_otos = new mysqli($servername, $username, $password, $dbname);

                    if ($conn_otos->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    date_default_timezone_set('Asia/Manila');


                ?>
