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
      overflow: visible;
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
      overflow: visible;
    }

    .inventory-people-filters {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 1rem;
      align-items: end;
      margin: 0;
      position: relative;
      z-index: 20;
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

    .name-combobox {
      position: relative;
      z-index: 30;
    }

    .name-combobox::after {
      content: '\f078';
      position: absolute;
      right: 0.85rem;
      bottom: 0.86rem;
      color: #64748b;
      font-family: 'Font Awesome 6 Free';
      font-size: 0.78rem;
      font-weight: 900;
      pointer-events: none;
    }

    .name-combobox input {
      padding-right: 2.3rem;
      cursor: text;
    }

    .name-options-panel {
      position: absolute;
      top: calc(100% + 0.4rem);
      left: 0;
      right: 0;
      z-index: 40;
      display: none;
      min-height: 128px;
      max-height: 270px;
      overflow: hidden;
      background: #fff;
      border: 1px solid #cbd5e1;
      border-radius: 14px;
      box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
    }

    .name-combobox.is-open .name-options-panel {
      display: block;
    }

    .name-options-search {
      display: flex;
      align-items: center;
      gap: 0.55rem;
      padding: 0.75rem 0.95rem;
      color: #64748b;
      border-bottom: 1px solid #e2e8f0;
      background: #f8fafc;
      font-size: 0.92rem;
    }

    .name-options-list {
      min-height: 72px;
      max-height: 215px;
      overflow-y: auto;
      padding: 0.35rem;
    }

    .name-option {
      padding: 0.7rem 0.8rem;
      border-radius: 10px;
      color: #0f172a;
      cursor: pointer;
      font-weight: 600;
    }

    .name-option:hover,
    .name-option.is-active {
      background: #ecfdf5;
      color: #166534;
    }

    .name-option-empty {
      padding: 0.85rem;
      color: #64748b;
      font-size: 0.92rem;
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
      position: relative;
      z-index: 1;
    }

    .division-confirm-backdrop {
      position: fixed;
      inset: 0;
      z-index: 2000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      background: rgba(15, 23, 42, 0.48);
    }

    .division-confirm-backdrop.is-open {
      display: flex;
    }

    .division-confirm-modal {
      width: min(460px, 100%);
      overflow: hidden;
      border-radius: 16px;
      background: #fff;
      box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
    }

    .division-confirm-header {
      padding: 1.1rem 1.25rem;
      color: #fff;
      background: linear-gradient(135deg, #0f766e, #16a34a);
    }

    .division-confirm-header h5 {
      margin: 0;
      font-weight: 800;
    }

    .division-confirm-body {
      padding: 1.25rem;
      color: #334155;
    }

    .division-confirm-body p {
      margin: 0 0 0.7rem;
    }

    .division-confirm-detail {
      padding: 0.75rem;
      border-radius: 10px;
      background: #f8fafc;
      color: #0f172a;
      font-weight: 700;
    }

    .division-confirm-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.7rem;
      padding: 1rem 1.25rem 1.25rem;
    }

    .division-confirm-actions button {
      min-width: 105px;
      height: 40px;
      border: 0;
      border-radius: 10px;
      font-weight: 800;
    }

    .division-confirm-cancel {
      background: #e2e8f0;
      color: #334155;
    }

    .division-confirm-save {
      background: #16a34a;
      color: #fff;
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
            <div class="name-combobox" id="nameCombobox">
              <input type="text" id="fullname" placeholder="Search name" autocomplete="off" />
              <div class="name-options-panel" id="nameOptionsPanel">
                <div class="name-options-search"><i class="fas fa-search"></i><span>Search full name</span></div>
                <div class="name-options-list" id="nameOptionsList">
                  <div class="name-option-empty">Select an office first.</div>
                </div>
              </div>
            </div>
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
  <div class="division-confirm-backdrop" id="divisionConfirmModal" aria-hidden="true">
    <div class="division-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="divisionConfirmTitle">
      <div class="division-confirm-header">
        <h5 id="divisionConfirmTitle"><i class="fas fa-building me-2"></i>Confirm Division Update</h5>
      </div>
      <div class="division-confirm-body">
        <p>Update this person's office division?</p>
        <div class="division-confirm-detail" id="divisionConfirmDetails"></div>
      </div>
      <div class="division-confirm-actions">
        <button type="button" class="division-confirm-cancel" id="cancelDivisionUpdate">Cancel</button>
        <button type="button" class="division-confirm-save" id="confirmDivisionUpdate">Update</button>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var officeSelect = document.getElementById('office');
      var divisionSelect = document.getElementById('station');
      var fullnameInput = document.getElementById('fullname');
      var nameCombobox = document.getElementById('nameCombobox');
      var nameOptionsList = document.getElementById('nameOptionsList');
      var showTableBtn = document.getElementById('showTableBtn');
      var tableContainer = document.getElementById('tableContainer');
      var divisionConfirmModal = document.getElementById('divisionConfirmModal');
      var divisionConfirmDetails = document.getElementById('divisionConfirmDetails');
      var cancelDivisionUpdate = document.getElementById('cancelDivisionUpdate');
      var confirmDivisionUpdate = document.getElementById('confirmDivisionUpdate');
      var fullNameOptions = [];
      var pendingDivisionUpdate = null;
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

      function escapeHtml(value) {
        return String(value || '').replace(/[&<>'"]/g, function (c) {
          return ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'})[c];
        });
      }

      function openNameDropdown() {
        nameCombobox.classList.add('is-open');
      }

      function closeNameDropdown() {
        nameCombobox.classList.remove('is-open');
      }

      function openDivisionConfirmModal(updateData) {
        pendingDivisionUpdate = updateData;
        divisionConfirmDetails.innerHTML =
          '<div>' + escapeHtml(updateData.name) + '</div>' +
          '<div style="margin-top: 0.35rem; color: #64748b; font-weight: 600;">' +
          escapeHtml(updateData.oldDivision || 'No division') + ' &rarr; ' + escapeHtml(updateData.newDivision) +
          '</div>';
        divisionConfirmModal.classList.add('is-open');
        divisionConfirmModal.setAttribute('aria-hidden', 'false');
      }

      function closeDivisionConfirmModal() {
        divisionConfirmModal.classList.remove('is-open');
        divisionConfirmModal.setAttribute('aria-hidden', 'true');
      }

      function revertPendingDivisionUpdate() {
        if (pendingDivisionUpdate && pendingDivisionUpdate.select) {
          pendingDivisionUpdate.select.value = pendingDivisionUpdate.oldDivision;
        }
        pendingDivisionUpdate = null;
        closeDivisionConfirmModal();
      }

      function renderNameOptions() {
        var query = fullnameInput.value.trim().toLowerCase();
        var matches = fullNameOptions.filter(function (name) {
          return !query || name.toLowerCase().indexOf(query) !== -1;
        }).slice(0, 80);

        if (!officeSelect.value) {
          nameOptionsList.innerHTML = '<div class="name-option-empty">Select an office first.</div>';
          return;
        }

        if (matches.length === 0) {
          nameOptionsList.innerHTML = '<div class="name-option-empty">No matching names found.</div>';
          return;
        }

        nameOptionsList.innerHTML = matches.map(function (name) {
          return '<div class="name-option" data-name="' + escapeHtml(name) + '">' + escapeHtml(name) + '</div>';
        }).join('');
      }

      function loadNameOptions() {
        fullNameOptions = [];
        renderNameOptions();

        if (!officeSelect.value) {
          return;
        }

        postForm('get_inventory_people_names.php', {
          office: officeSelect.value,
          officeDivision: divisionSelect.value
        })
          .then(function (response) {
            var data = JSON.parse(response);
            fullNameOptions = data.names || [];
            renderNameOptions();
          })
          .catch(function (error) {
            console.error('Error fetching names:', error);
            nameOptionsList.innerHTML = '<div class="name-option-empty">Unable to load names.</div>';
          });
      }

      function loadDivisions(selectedOffice, divisionToSelect) {
        localStorage.setItem('selectedOffice', selectedOffice);
        localStorage.removeItem('selectedDivision');
        divisionSelect.innerHTML = '<option value="">-- Select Division --</option>';
        fullNameOptions = [];
        renderNameOptions();

        if (!selectedOffice) {
          return;
        }

        postForm('get_stations_2.php', { office: selectedOffice })
          .then(function (response) {
            divisionSelect.insertAdjacentHTML('beforeend', response);
            if (divisionToSelect) {
              divisionSelect.value = divisionToSelect;
            }
            loadNameOptions();
          })
          .catch(function (error) {
            console.error('Error fetching divisions:', error);
            alert('Error fetching divisions');
          });
      }

      officeSelect.addEventListener('change', function () {
        fullnameInput.value = '';
        localStorage.removeItem('fullName');
        loadDivisions(this.value, '');
      });

      if (savedOffice) {
        officeSelect.value = savedOffice;
        loadDivisions(savedOffice, savedDivision);
      }

      divisionSelect.addEventListener('change', function () {
        localStorage.setItem('selectedDivision', this.value);
        fullnameInput.value = '';
        localStorage.removeItem('fullName');
        loadNameOptions();
      });

      fullnameInput.addEventListener('keyup', function () {
        localStorage.setItem('fullName', this.value.trim());
        renderNameOptions();
        openNameDropdown();
      });

      fullnameInput.addEventListener('change', function () {
        localStorage.setItem('fullName', this.value.trim());
      });

      fullnameInput.addEventListener('focus', function () {
        renderNameOptions();
        openNameDropdown();
      });

      nameOptionsList.addEventListener('mousedown', function (event) {
        var option = event.target.closest('.name-option');
        if (!option) {
          return;
        }

        fullnameInput.value = option.getAttribute('data-name') || option.textContent.trim();
        localStorage.setItem('fullName', fullnameInput.value.trim());
        closeNameDropdown();
      });

      document.addEventListener('mousedown', function (event) {
        if (!nameCombobox.contains(event.target)) {
          closeNameDropdown();
        }
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

      tableContainer.addEventListener('change', function (event) {
        var select = event.target.closest('.division-update-select');
        if (!select) {
          return;
        }

        var oldDivision = select.getAttribute('data-original') || '';
        var newDivision = select.value;

        if (newDivision === oldDivision) {
          return;
        }

        openDivisionConfirmModal({
          select: select,
          id: select.getAttribute('data-id'),
          name: select.getAttribute('data-name') || '',
          office: select.getAttribute('data-office') || '',
          oldDivision: oldDivision,
          newDivision: newDivision
        });
      });

      cancelDivisionUpdate.addEventListener('click', revertPendingDivisionUpdate);

      divisionConfirmModal.addEventListener('mousedown', function (event) {
        if (event.target === divisionConfirmModal) {
          revertPendingDivisionUpdate();
        }
      });

      confirmDivisionUpdate.addEventListener('click', function () {
        if (!pendingDivisionUpdate) {
          return;
        }

        var updateData = pendingDivisionUpdate;
        confirmDivisionUpdate.disabled = true;
        confirmDivisionUpdate.textContent = 'Updating...';

        postForm('update_inventory_person_division.php', {
          id: updateData.id,
          office: updateData.office,
          officeDivision: updateData.newDivision
        })
          .then(function (response) {
            var data = JSON.parse(response);
            if (!data.success) {
              throw new Error(data.message || 'Unable to update office division.');
            }

            updateData.select.setAttribute('data-original', updateData.newDivision);
            pendingDivisionUpdate = null;
            closeDivisionConfirmModal();
          })
          .catch(function (error) {
            alert(error.message || 'Unable to update office division.');
            if (updateData.select) {
              updateData.select.value = updateData.oldDivision;
            }
            pendingDivisionUpdate = null;
            closeDivisionConfirmModal();
          })
          .finally(function () {
            confirmDivisionUpdate.disabled = false;
            confirmDivisionUpdate.textContent = 'Update';
          });
      });
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>
