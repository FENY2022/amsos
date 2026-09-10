<?php   
                
                mysqli_report(MYSQLI_REPORT_OFF);

                if (isset($conn_otos) && $conn_otos instanceof mysqli && @$conn_otos->ping()) {
                    return;
                }

                $servername = "153.92.15.60";
                $username = "u645536029_otos_root";
                $password = "6yI3PF3OZ";
                $dbname = "u645536029_otos";

                    $conn_otos = new mysqli('p:' . $servername, $username, $password, $dbname);

                    if ($conn_otos->connect_error) {
                        http_response_code(503);
                        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => 'OTOS users database is temporarily unavailable. Please try again later.']);
                            exit;
                        }
                        die("Connection failed: " . $conn_otos->connect_error);
                    }

                    $conn_otos->set_charset('utf8mb4');

                    date_default_timezone_set('Asia/Manila');


                ?>
