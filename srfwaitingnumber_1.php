<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting List</title>
    <style>
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 1000px;
            text-align: center;
        }

        h1 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background-color: #4CAF50;
            color: white;
        }

        th, td {
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .controls {
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 10px;
            width: 60%;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .current-call h2 {
            color: #333;
        }

        .current-call span {
            font-weight: bold;
            color: #e74c3c;
            font-size: 2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SRF Waiting List</h1>
        <table>
            <thead>
                <tr>
                    <th>Priority Number</th>
                    <th>Name</th>
                    <th>Station</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="list-body">
                <?php
                // Backend PHP code to fetch data
                include 'connect.php'; // Include your database connection
                
                $nowserving = '';
                $status = '';

                $query = "SELECT ticketNumber, name, station, status FROM srf WHERE tracking <> 103 AND tracking <> 102 ORDER BY ticketNumber ASC";

                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $nowserving = $row['ticketNumber'] ; 
                        $status = $row['status'] ; 
                        echo "<tr>
                                <td>{$row['ticketNumber']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['station']}</td>";

                            if (($row['status'])=="Now Serving")
                                echo "<td><span class='badge badge-danger'>{$row['status']}</span></td>
                              </tr>";

                              if (($row['status'])=="Ongoing")
                              echo "<td><span class='badge badge-warning'>{$row['status']}</span></td>
                            </tr>";



                    }
                } else {
                    echo "<tr><td colspan='4'>No data found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>

        <!-- <div class="controls">
            <input type="text" id="name" placeholder="Enter your name">
            <button onclick="addToList()">Add to List</button>
            <button onclick="callNext()">Call Next</button>
        </div> -->

        <?php
        
            $nowserving = "20240913-002-615ea";
            $extracted = substr($nowserving, -5);
        ?>


        <div class="current-call">
        <h2>Succesfully Completed: <span id="current-number">
            <?php

            if ($status === 'Now Serving'){
                echo $extracted ;
            }else{

            }
             ; ?>

        </span></h2>

        </div>
    </div>

    <script>
        // Placeholder JavaScript for 'Add to List' and 'Call Next' functionality
        function addToList() {
            // Implement logic to add to the list
        }

        function callNext() {
            // Implement logic to call the next person in the list
        }
    </script>
</body>
</html>
