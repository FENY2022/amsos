<?php
require_once "connect.php";

$officesQuery = "SELECT DISTINCT office FROM inventory_people WHERE office IS NOT NULL AND office != '' ORDER BY office ASC";
$officesResult = $conn->query($officesQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory People</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    /* Styling same as before */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #e0e0e0, #ffffff);
      margin: 0;
      padding: 20px;
      color: #333;
    }
    .container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    h1 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    form {
      margin-bottom: 20px;
      text-align: center;
    }
    label {
      font-weight: bold;
      margin-right: 10px;
      color: #34495e;
    }
    select, input {
      padding: 10px;
      font-size: 16px;
      border: none;
      border-radius: 6px;
      width: 220px;
      background: #f0f0f0;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 10px;
      transition: box-shadow 0.3s ease;
    }
    select:focus, input:focus {
      outline: none;
      box-shadow: 0 0 8px rgba(52,152,219,0.6);
    }
    button {
      padding: 12px 24px;
      font-size: 16px;
      color: #fff;
      background: linear-gradient(145deg, #4CAF50, #45a049);
      border: none;
      border-radius: 8px;
      cursor: pointer;
      box-shadow: 4px 4px 6px #d1d1d1, -4px -4px 6px #ffffff;
      transition: transform 0.1s ease, box-shadow 0.3s ease;
    }
    button:hover {
      transform: translateY(-2px);
      box-shadow: 6px 6px 8px #d1d1d1, -6px -6px 8px #ffffff;
    }
    button:disabled {
      background: #aaa;
      cursor: not-allowed;
      box-shadow: none;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background-color: #fff;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      border-radius: 8px;
      overflow: hidden;
    }
    th, td {
      padding: 14px;
      text-align: left;
    }
    th {
      background: linear-gradient(145deg, #4CAF50, #45a049);
      color: #fff;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    tr:hover {
      background-color: #f1f1f1;
    }
    #tableContainer {
      margin-top: 30px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Inventory People</h1>
    <form method="POST" action="">
      <label for="office">Select Office:</label>
      <select name="office" id="office">
        <option value="">-- Select an Office --</option>
        <?php while ($row = $officesResult->fetch_assoc()): ?>
          <option value="<?php echo htmlspecialchars($row['office']); ?>">
            <?php echo htmlspecialchars($row['office']); ?>
          </option>
        <?php endwhile; ?>
      </select>
    </form>
    <div style="text-align:center;">
      <label for="station">Office Division:</label>
      <select name="station" id="station">
        <option value="">-- Select Division --</option>
      </select>
      <br><br>
      <label for="fullname">Full Name:</label>
      <input type="text" id="fullname" placeholder="Enter Full Name" />
      <br><br>
      <button id="showTableBtn">Show Table</button>
    </div>
    <div id="tableContainer"></div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var officeSelect = document.getElementById('office');
      var divisionSelect = document.getElementById('station');
      var fullnameInput = document.getElementById('fullname');
      var showTableBtn = document.getElementById('showTableBtn');
      var tableContainer = document.getElementById('tableContainer');
      var savedOffice = localStorage.getItem('selectedOffice');
      var savedDivision = localStorage.getItem('selectedDivision');
      var savedFullName = localStorage.getItem('fullName');

      if (savedFullName) {
        fullnameInput.value = savedFullName;
      }

      function postForm(url, data) {
        var body = new URLSearchParams();
        Object.keys(data).forEach(function (key) {
          body.append(key, data[key]);
        });

        return fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
          },
          body: body.toString()
        }).then(function (response) {
          if (!response.ok) {
            throw new Error('Request failed: ' + response.status);
          }
          return response.text();
        });
      }

      function loadDivisions(selectedOffice, divisionToSelect) {
        localStorage.setItem('selectedOffice', selectedOffice);
        localStorage.removeItem('selectedDivision');
        divisionSelect.innerHTML = '<option value="">-- Select Division --</option>';

        if (!selectedOffice) {
          return;
        }

        postForm('get_stations_2.php', { office: selectedOffice })
          .then(function (response) {
            divisionSelect.insertAdjacentHTML('beforeend', response);
            if (divisionToSelect) {
              divisionSelect.value = divisionToSelect;
            }
          })
          .catch(function (error) {
            console.error('Error fetching divisions:', error);
            alert('Error fetching divisions');
          });
      }

      officeSelect.addEventListener('change', function () {
        loadDivisions(this.value, '');
      });

      if (savedOffice) {
        officeSelect.value = savedOffice;
        loadDivisions(savedOffice, savedDivision);
      }

      divisionSelect.addEventListener('change', function () {
        localStorage.setItem('selectedDivision', this.value);
      });

      fullnameInput.addEventListener('keyup', function () {
        localStorage.setItem('fullName', this.value.trim());
      });

      fullnameInput.addEventListener('change', function () {
        localStorage.setItem('fullName', this.value.trim());
      });

      showTableBtn.addEventListener('click', function () {
        var selectedOffice = officeSelect.value;
        var selectedDivision = divisionSelect.value;
        var fullName = fullnameInput.value.trim();

        if (!selectedOffice) {
          alert('Please select an office');
          return;
        }

        postForm('get_filtered_data.php', {
          office: selectedOffice,
          officeDivision: selectedDivision,
          fullname: fullName
        })
          .then(function (response) {
            tableContainer.innerHTML = response;
          })
          .catch(function (error) {
            console.error('Error fetching data:', error);
            alert('Error fetching data');
          });
      });
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>
