
<?php

require_once "connect.php" ;

?>
    
<!DOCTYPE html>
<html>
<head>


<?php 

$username = "ANTHONIE FENY V. CATALAN" ;
?> 
  <title>ICT Inventory Dashboard</title>
  <style>
    body {
      font-family:  Arial, Helvetica, sans-serif, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f1f1f1;
    }
    .container {
      display: flex;
      max-width: 1200px;
      margin: 0 auto;
      background-color: #eaf8e6;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .sidebar {
      width: 250px;
      background-color: #2d8659;
      color: #fff;
    }
    .sidebar ul {
      list-style-type: none;
      padding: 0;
      margin: 0;
    }
    .sidebar li {
      padding: 10px;
      border-bottom: 1px solid #5ebc88;
    }
    .sidebar li a {
      color: #fff;
      text-decoration: none;
    }
    .sidebar li a:hover {
      background-color: #5ebc88;
    }
    .content {
      flex-grow: 1;
      padding: 20px;
    }
    h1 {
      color: #333;
      margin-bottom: 20px;
    }
    .logo img {
      max-width: 100px;
    }
    .welcome-message {
      margin-bottom: 20px;
    }



    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #f2f2f2;
    }

    table {
    margin-left: auto;
    margin-right: auto;
  }

  select {
  display: dropdown;
  position: relative;
  }

  select  {
  background-color: #fff;
  border: 1px solid #ccc;
  padding: 5px 10px;
  }

  select :hover {
  background-color: #f9f9f9;
  }

  h1 {
  text-align: center;
  font-family: Arial, sans-serif;
  }

  hr {
  border-top: 1px solid gainsboro;
  color: gainsboro;
  }


  footer {
  position: fixed;
  bottom: 0;
  width: 100%;
  background-color: #888;
  padding: 20px;
}

footer p {
  color: #fff;
  font-size: 14px;
  margin-bottom: 10px;
}

footer a {
  color: #fff;
  text-decoration: none;
}

footer a:hover {
  color: #000;
}


    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.4);
      
    }
    .modal-content {
      background-color: #eaf8e6;
      margin: 10% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
    }
    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

  </style>
</head>
<body>
  <div class="container">
    <div class="sidebar">
      <div class="logo">
       

 <div style="text-align:center;">
  <img src="image/denr.png" alt="Logo">
</div>

        
      </div>
      <ul>

        <br>
        <li><a href="#">Home</a></li>
        <br>
        <li><a  onclick="openModal('myModal1')" >Add Inventory</a></li>
        <br>
        <li><a onclick="openModal('myModal2')" >ICT Inventory</a></li>
        <br>
        <li><a href="#">Findings</a></li>
        <br>
        <li><a href="#">Recommending</a></li>
        <br>

      </ul>
    </div>

    <hr style="display: flex;" />

    <div class="content">
      <h1>Bolos Kano ICT Inventory </h1>
      <br>

      <div class="welcome-message">
        <p>Welcome, <strong><?php echo $username; ?></strong>!</p>
      </div>
      <!-- Add your dashboard content here -->
      <body>


      <label for="office">Office: </label>
      <select name="office" id="office" data-bind="value: office">
      <option value="" selected>Select an office</option>
      <?php foreach ($offices as $office) : ?>
      <option value="<?php echo $office['id']; ?>"><?php echo $office['name']; ?></option>
      <?php endforeach; ?>


      

 

    </select>

    <hr style="display: flex;" />

<br><br>


<?php
// Replace the database connection details with your actual values
// define('USER', 'root');
// define('PASSWORD', '');
// define('HOST', 'localhost');
// define('DBNAME', 'your_database_name');

// // Create connection
// $conn = new mysqli(HOST, USER, PASSWORD, DBNAME);
// Function to convert date format to "January 1, 2023"
function formatDate($date) {
  return date("F j, Y", strtotime($date));
}
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the database
$sql = "SELECT * FROM addinventory";
$result = $conn->query($sql);

$conn->close();
?>

<!-- Display the fetched data in the table -->
<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Name of Employee</th>
            <th>Position</th>
            <th>Designation</th>
            <th>Asset Class</th>
            <th>Year Acquired</th>
            <th>Specification/Range Category</th>
            <th>Production Date</th>
            <th>Total No. of assigned End-Device</th>
            <th>Office</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            $counter = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $counter . "</td>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td>" . $row["position"] . "</td>";
                echo "<td>" . $row["designation"] . "</td>";
                echo "<td>" . $row["asset_class"] . "</td>";
                echo "<td>" . $row["year_acquired"] . "</td>";
                echo "<td>" . $row["specification"] . "</td>";
                echo "<td>" . formatDate(date("Y-m-d", strtotime($row["production_date"]))) . "</td>";
                echo "<td>" . $row["total_number_assigned"] . "</td>";
                echo "<td>" . $row["office"] . "</td>";
                echo "</tr>";
                $counter++;
            }
        } else {
            echo "<tr><td colspan='10'>No data available</td></tr>";
        }
        ?>
    </tbody>
</table>

    </div>
  </div>

  <!-- Add your JavaScript code -->
  <script>
    // Function to open a specific modal
    function openModal(modalId) {
      var modal = document.getElementById(modalId);
      modal.style.display = "block";
    }

    // Function to close a specific modal
    function closeModal(modalId) {
      var modal = document.getElementById(modalId);
      modal.style.display = "none";
    }
  </script>





  <!-- <button onclick="openModal('myModal1')">Open Modal 1</button> -->

  <!-- The first modal -->
  <div id="myModal1" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('myModal1')">&times;</span>
      <h2>Add Inventory</h2>
      <p>...</p>
      <?php include "addinventory.php" ; ?> 
    </div>
  </div>

  <!-- Add a button to open the second modal -->
  <!-- <button onclick="openModal('myModal2')">Open Modal 2</button> -->

  <!-- The second modal -->
  <div id="myModal2" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('myModal2')">&times;</span>
      <h2>ICT Inventory</h2>
      <p>. . . . . .  . . . .  . </p>

      <?php include "showTable.php" ; ?> 

      
    </div>
  </div>







  <footer>
  <p>Copyright &copy; 2023</p>
  <p>
    <a href="#">Terms of Use</a> | <a href="#">Privacy Policy</a>
  </p>
</footer>







</body>
</html>
