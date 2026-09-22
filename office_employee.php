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
    .inventory-people-page {
      padding: 1.5rem;
    }

    .inventory-people-card {
      background: #fff;
      border: 0;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
      overflow: hidden;
    }

    .inventory-people-header {
      background: linear-gradient(135deg, #0f766e, #16a34a);
      color: #fff;
      padding: 1.4rem 1.6rem;
    }

    .inventory-people-header h1 {
      margin: 0;
      font-size: 1.55rem;
      font-weight: 700;
    }

    .inventory-people-header p {
      margin: 0.3rem 0 0;
      opacity: 0.9;
      font-size: 0.92rem;
    }

    .inventory-people-body {
      padding: 1.5rem;
    }

    .inventory-people-filters {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 1rem;
      align-items: end;
      margin: 0;
    }

    .inventory-people-page label {
      display: block;
      margin: 0 0 0.35rem;
      color: #334155;
      font-size: 0.84rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      text-transform: uppercase;
    }

    .inventory-people-page select,
    .inventory-people-page input {
      width: 100%;
      height: 44px;
      padding: 0.55rem 0.75rem;
      border: 1px solid #cbd5e1;
      border-radius: 10px;
      background: #fff;
      color: #0f172a;
      font-size: 0.95rem;
      box-shadow: none;
    }

    .inventory-people-page select:focus,
    .inventory-people-page input:focus {
      border-color: #16a34a;
      box-shadow: 0 0 0 0.2rem rgba(22, 163, 74, 0.16);
      outline: none;
    }

    .inventory-people-page button {
      width: 100%;
      height: 44px;
      border: 0;
      border-radius: 10px;
      background: #16a34a;
      color: #fff;
      font-weight: 700;
      box-shadow: 0 8px 16px rgba(22, 163, 74, 0.2);
      transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }

    .inventory-people-page button:hover {
      background: #15803d;
      transform: translateY(-1px);
      box-shadow: 0 10px 18px rgba(22, 163, 74, 0.24);
    }

    #tableContainer {
      margin-top: 1.5rem;
    }

    @media (max-width: 1199.98px) {
      .inventory-people-filters {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 767.98px) {
      .inventory-people-page {
        padding: 1rem 0.5rem;
      }

      .inventory-people-filters {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="inventory-people-page">
    <div class="inventory-people-card">
      <div class="inventory-people-header">
        <h1><i class="fas fa-users me-2"></i>Inventory People</h1>
        <p>Local records from amsos.inventory_people</p>
      </div>
      <div class="inventory-people-body">
        <form method="POST" action="" class="inventory-people-filters">
          <div>
            <label for="office">Office</label>
            <select name="office" id="office">
              <option value="">-- Select Office --</option>
              <?php while ($row = $officesResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($row['office']); ?>">
                  <?php echo htmlspecialchars($row['office']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div>
            <label for="station">Office Division</label>
            <select name="station" id="station">
              <option value="">-- Select Division --</option>
            </select>
          </div>
          <div>
            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" placeholder="Search name" />
          </div>
          <div>
            <label>&nbsp;</label>
            <button type="button" id="showTableBtn"><i class="fas fa-search me-1"></i> Show Table</button>
          </div>
        </form>
        <div id="tableContainer"></div>
      </div>
    </div>
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
