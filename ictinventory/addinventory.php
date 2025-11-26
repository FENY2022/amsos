

<!DOCTYPE html>
<html>
<head>


  <title>Add Inventory</title>
  <style>
 body {
  font-family: sans-serif;
  margin: 0;
  padding: 0;
}

form {
  width: 500px;
  margin: 0 auto;
}

label {
  font-size: 16px;
  margin-bottom: 10px;
}

input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
}

input:focus {
  border-color: #000;
}

.submit {
  background-color: #000;
  color: #fff;
  font-size: 16px;
  padding: 10px;
  cursor: pointer;
}



  </style>
</head>










<body>

<div class="container">







<form action="process.php" method="post">
  <label for="name">Name of Employee:</label>
  <input type="text" name="name" id="name" placeholder="Search" onkeyup="performSearch(this.value)" required>
  <br>
  <br>
  <label for="position">Position:</label>
  <input type="text" name="position" id="position" required>
  <br>
  <br>
  <label for="designation">Designation:</label>
  <input type="text" name="designation" id="designation" required>
  <br>
  <br>
  <label for="asset_class">Asset Class:</label>
  <input type="text" name="asset_class" id="asset_class" required>
  <br>
  <br>
  <label for="assigned_to">Assigned to:</label>
  <input type="text" name="assigned_to" id="assigned_to" required>
  <br>
  <br>
  <label for="nature_of_work">Nature of Work/Usage:</label>
  <input type="text" name="nature_of_work" id="nature_of_work" required>
  <br>
  <br>
  <label for="specification">Specification:</label>
  <!-- <input type="text" name="specification" id="specification" rows="10" style="width: 100%;" required> -->

  <textarea name="specification" id="specification" rows="10" style="width: 100%;" required></textarea>


  <br>
  <br>
  <label for="license_type">License Type:</label>
  <input type="text" name="license_type" id="license_type" required>
  <br>
  <br>
  <label for="year_acquired">Year Acquired:</label>
  <!-- <input type="text" name="year_acquired" id="year_acquired" required> -->
  <input type="number" name="year_acquired" id="year_acquired" min="1900" max="2099" value="<?php echo date('Y'); ?>" required>


  <br>
  <br>
  <label for="production_date">Production Date:</label>
  <?php
$date = date('Y-m-d', strtotime('-6 months'));
?>
<input type="date" name="production_date" id="production_date" value="<?php echo $date; ?>" required>


  <br>
  <br>
  <label for="shelf_life">Shelf Life:</label>
  <input type="text" name="shelf_life" id="shelf_life" required>
  <br>
  <br>
  <label for="suitable_specifications">Suitable specifications according to Usage:</label>
  <input type="text" name="suitable_specifications" id="suitable_specifications" required>
  <br>
  <br>
  <label for="total_number_assigned">Total Number Assigned END Device:</label>
  <input type="text" name="total_number_assigned" id="total_number_assigned" required>
  <br>
  <br>
  <label for="findings">Findings:</label>
  <textarea name="findings" id="findings" rows="10" style="width: 100%;"  required></textarea>

  <!-- <input type="text" name="findings" id="findings" required> -->
  <br>
  <br>
  <label for="recommendations">Recommendations:</label>
  <textarea name="recommendations" id="recommendations" rows="10" style="width: 100%;" required></textarea>
  <br>
  <br>
  <br>

  <input type="submit" value="Submit" name="save">
</form>


  </div>
</body>
<body>


</body>
</html>

