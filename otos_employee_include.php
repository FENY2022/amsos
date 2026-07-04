<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$userOffice = $_SESSION['OfficeSRF'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-users"></i> OTOS Employee Include</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                OFF means do nothing for new employees. If an already included employee is unchecked and saved, the OTOS-added inventory person record will be removed from AMSOS inventory_people only.
            </div>

            <div class="row align-items-end">
                <div class="col-md-5 mb-3">
                    <label class="font-weight-bold" for="otosOffice">Office</label>
                    <input type="text" class="form-control" id="otosOffice" value="<?php echo htmlspecialchars($userOffice, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="font-weight-bold" for="otosStation">Station</label>
                    <select class="form-control" id="otosStation" <?php echo $userOffice === '' ? 'disabled' : ''; ?>>
                        <option value="">Select Station</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="button" class="btn btn-success btn-block" id="loadOtosEmployees" <?php echo $userOffice === '' ? 'disabled' : ''; ?>>Load</button>
                </div>
            </div>

            <div id="otosMessage"></div>

            <form id="otosIncludeForm" class="d-none">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 110px;">Include</th>
                                <th>Name</th>
                                <th>Station</th>
                                <th>Division/Unit</th>
                                <th>Employment Status</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="otosEmployeeRows"></tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary" id="saveOtosEmployees">Save Selected</button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var office = <?php echo json_encode($userOffice); ?>;
    var stationSelect = document.getElementById('otosStation');
    var loadButton = document.getElementById('loadOtosEmployees');
    var form = document.getElementById('otosIncludeForm');
    var rows = document.getElementById('otosEmployeeRows');
    var message = document.getElementById('otosMessage');
    var saveButton = document.getElementById('saveOtosEmployees');

    function setMessage(type, text) {
        if (!text) {
            message.innerHTML = '';
            return;
        }
        message.innerHTML = '<div class="alert alert-' + type + '">' + escapeHtml(text) + '</div>';
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>'"]/g, function (char) {
            return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'})[char];
        });
    }

    function loadStations() {
        if (!office) {
            setMessage('warning', 'No office found in your session. Please login again.');
            return;
        }

        fetch('get_otos_stations.php')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                stationSelect.innerHTML = '<option value="">Select Station</option>';
                if (!data.success) {
                    setMessage('danger', data.message || 'Unable to load stations.');
                    return;
                }
                data.stations.forEach(function (station) {
                    var option = document.createElement('option');
                    option.value = station;
                    option.textContent = station;
                    stationSelect.appendChild(option);
                });
            })
            .catch(function () {
                setMessage('danger', 'Unable to load stations.');
            });
    }

    function loadEmployees() {
        var station = stationSelect.value;
        form.classList.add('d-none');
        rows.innerHTML = '';

        if (!station) {
            setMessage('warning', 'Please select a station.');
            return;
        }

        setMessage('info', 'Loading employees...');
        loadButton.disabled = true;

        fetch('get_otos_employees.php?station=' + encodeURIComponent(station))
            .then(function (response) { return response.json(); })
            .then(function (data) {
                loadButton.disabled = false;
                if (!data.success) {
                    setMessage('danger', data.message || 'Unable to load employees.');
                    return;
                }
                if (data.employees.length === 0) {
                    setMessage('warning', 'No employees found for this station.');
                    return;
                }

                rows.innerHTML = data.employees.map(function (employee) {
                    var checked = employee.already_included ? 'checked' : '';
                    var label = employee.already_included ? 'Already included' : 'Not included';
                    var badge = employee.already_included ? 'secondary' : 'success';
                    var alreadyIncluded = employee.already_included ? '1' : '0';

                    return '<tr>' +
                        '<td class="text-center">' +
                            '<input type="checkbox" class="otos-include" value="' + escapeHtml(employee.id) + '" data-already-included="' + alreadyIncluded + '" ' + checked + '>' +
                        '</td>' +
                        '<td>' + escapeHtml(employee.full_name) + '</td>' +
                        '<td>' + escapeHtml(employee.station) + '</td>' +
                        '<td>' + escapeHtml(employee.division_unit) + '</td>' +
                        '<td>' + escapeHtml(employee.employment_status) + '</td>' +
                        '<td><span class="otos-status badge badge-' + badge + '">' + label + '</span></td>' +
                    '</tr>';
                }).join('');

                setMessage('', '');
                form.classList.remove('d-none');
            })
            .catch(function () {
                loadButton.disabled = false;
                setMessage('danger', 'Unable to load employees.');
            });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var includeIds = Array.prototype.slice.call(document.querySelectorAll('.otos-include:checked[data-already-included="0"]')).map(function (input) {
            return input.value;
        });

        var removeIds = Array.prototype.slice.call(document.querySelectorAll('.otos-include:not(:checked)[data-already-included="1"]')).map(function (input) {
            return input.value;
        });

        if (includeIds.length === 0 && removeIds.length === 0) {
            setMessage('warning', 'No changes selected.');
            return;
        }

        saveButton.disabled = true;
        setMessage('info', 'Saving selected changes...');

        saveOtosChanges(includeIds, removeIds, false);
    });

    function saveOtosChanges(includeIds, removeIds, confirmDuplicates) {
        saveButton.disabled = true;

        fetch('save_otos_inventory_people.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                include_otos_user_ids: includeIds,
                remove_otos_user_ids: removeIds,
                confirm_duplicates: confirmDuplicates
            })
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                saveButton.disabled = false;
                if (data.needs_confirmation) {
                    var duplicateMessage = (data.matches || []).map(function (match) {
                        return match.otos_name + ' may match ' + match.inventory_name + ' (' + match.similarity + '%)';
                    }).join('\n');

                    if (confirm((data.message || 'Possible duplicate names found.') + '\n\n' + duplicateMessage + '\n\nProceed anyway?')) {
                        setMessage('info', 'Saving confirmed changes...');
                        saveOtosChanges(includeIds, removeIds, true);
                    }
                    return;
                }

                if (!data.success) {
                    setMessage('danger', data.message || 'Unable to save employees.');
                    return;
                }
                setMessage('success', data.message || 'Selected employees saved.');
                loadEmployees();
            })
            .catch(function () {
                saveButton.disabled = false;
                setMessage('danger', 'Unable to save employees.');
            });
    }

    rows.addEventListener('change', function (event) {
        if (!event.target.classList.contains('otos-include')) {
            return;
        }

        var status = event.target.closest('tr').querySelector('.otos-status');
        var alreadyIncluded = event.target.getAttribute('data-already-included') === '1';

        if (alreadyIncluded) {
            if (event.target.checked) {
                status.textContent = 'Already included';
                status.className = 'otos-status badge badge-secondary';
            } else {
                status.textContent = 'Will be removed';
                status.className = 'otos-status badge badge-danger';
            }
            return;
        }

        if (event.target.checked) {
            status.textContent = 'Will be included';
            status.className = 'otos-status badge badge-primary';
        } else {
            status.textContent = 'Not included';
            status.className = 'otos-status badge badge-success';
        }
    });

    loadButton.addEventListener('click', loadEmployees);
    loadStations();
})();
</script>
