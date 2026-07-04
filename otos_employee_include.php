<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$userOffice = $_SESSION['OfficeSRF'] ?? '';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
    .otos-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    .otos-header {
        background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
        border-radius: 16px 16px 0 0;
        padding: 24px 32px;
        color: #fff;
    }
    .otos-header h4 {
        font-weight: 700;
        margin: 0;
        color: #fff;
    }
    .otos-header .office-badge {
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(4px);
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .otos-body {
        background: #fff;
        border-radius: 0 0 16px 16px;
        padding: 28px 32px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }
    .info-card {
        background: #f8f9ff;
        border: 1px solid #e8eaff;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 24px;
    }
    .info-card i {
        font-size: 1.5rem;
        color: #3949ab;
        margin-top: 2px;
    }
    .info-card p {
        margin: 0;
        font-size: 0.9rem;
        color: #424242;
        line-height: 1.5;
    }
    .control-card {
        background: #f5f7fa;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 16px;
    }
    .control-card .form-group-custom {
        flex: 1 1 200px;
        min-width: 0;
    }
    .control-card .field-office,
    .control-card .field-station {
        flex: 1 1 320px;
    }
    .control-card .field-action {
        flex: 0 0 210px;
    }
    .control-card label {
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #616161;
        margin-bottom: 6px;
        display: block;
        min-height: 19px;
    }
    .control-card .form-control, .control-card .form-select {
        border-radius: 8px;
        border: 1px solid #d0d0d0;
        padding: 10px 14px;
        font-size: 0.95rem;
        background: #fff;
        height: 44px;
    }
    .control-card .btn-load {
        height: 44px;
        border-radius: 8px;
        padding: 0 28px;
        font-weight: 600;
        white-space: nowrap;
        width: 100%;
    }
    .summary-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }
    .summary-item {
        background: #f5f7fa;
        border-radius: 10px;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
    }
    .summary-item .num {
        font-weight: 700;
        font-size: 1.2rem;
        min-width: 28px;
        text-align: center;
    }
    .summary-item.total .num { color: #1a237e; }
    .summary-item.included .num { color: #2e7d32; }
    .summary-item.pending-add .num { color: #1565c0; }
    .summary-item.pending-remove .num { color: #c62828; }
    .summary-item.excluded .num { color: #e65100; }
    .search-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }
    .search-toolbar .search-box {
        flex: 1 1 260px;
        position: relative;
    }
    .search-toolbar .search-box input {
        padding-left: 38px;
        border-radius: 8px;
        border: 1px solid #d0d0d0;
        height: 40px;
    }
    .search-toolbar .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9e9e9e;
    }
    .search-toolbar .btn-sm-custom {
        height: 40px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
    }
    .otos-table-wrap {
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        overflow: hidden;
        max-height: 480px;
        overflow-y: auto;
    }
    .otos-table-wrap table {
        margin: 0;
    }
    .otos-table-wrap thead {
        position: sticky;
        top: 0;
        z-index: 5;
    }
    .otos-table-wrap thead th {
        background: #e8eaf6;
        color: #1a237e;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 14px 16px;
        border-bottom: 2px solid #c5cae9;
        vertical-align: middle;
    }
    .otos-table-wrap tbody td {
        padding: 12px 16px;
        vertical-align: middle;
        font-size: 0.92rem;
    }
    .otos-table-wrap tbody tr:hover {
        background: #f5f7ff;
    }
    .otos-table-wrap tbody tr:not(:last-child) td {
        border-bottom: 1px solid #f0f0f0;
    }
    .form-check-input.otos-include {
        position: static;
        width: 20px;
        height: 20px;
        cursor: pointer;
        border-radius: 4px;
        border: 2px solid #9e9e9e;
        transition: all 0.15s;
    }
    .form-check-input.otos-include:checked {
        background-color: #3949ab;
        border-color: #3949ab;
    }
    .badge-otos {
        font-size: 0.8rem;
        padding: 4px 12px;
        border-radius: 12px;
        font-weight: 500;
    }
    .btn-save-bar {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    .btn-save-bar .btn {
        border-radius: 8px;
        padding: 10px 32px;
        font-weight: 600;
    }
    @media (max-width: 768px) {
        .otos-body { padding: 16px; }
        .control-card { flex-direction: column; }
        .control-card .form-group-custom { flex: 1 1 auto; }
        .summary-bar { gap: 8px; }
        .summary-item { flex: 1 1 calc(50% - 8px); }
    }
</style>

<div class="otos-container">
    <div class="otos-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4><i class="fas fa-users me-2"></i>OTOS Employee Include</h4>
        </div>
        <div class="office-badge">
            <i class="fas fa-building"></i> <?php echo htmlspecialchars($userOffice ?: 'N/A'); ?>
        </div>
    </div>

    <div class="otos-body">
        <div class="info-card">
            <i class="fas fa-info-circle"></i>
            <p><strong>How it works:</strong> Employees with OTOS accounts can be included in the AMSOS inventory system.
            Check the box to <strong>include</strong> a new employee, or uncheck to <strong>remove</strong> an already included employee.
            Changes are saved only after clicking <strong>"Save Changes"</strong>.</p>
        </div>

        <div class="control-card">
            <div class="form-group-custom field-office">
                <label><i class="fas fa-map-marker-alt me-1"></i> Office</label>
                <input type="text" class="form-control" id="otosOffice" value="<?php echo htmlspecialchars($userOffice, ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            <div class="form-group-custom field-station">
                <label><i class="fas fa-location-dot me-1"></i> Station</label>
                <select class="form-select" id="otosStation" <?php echo $userOffice === '' ? 'disabled' : ''; ?>>
                    <option value="">Select Station</option>
                </select>
            </div>
            <div class="form-group-custom field-action">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-primary btn-load" id="loadOtosEmployees" <?php echo $userOffice === '' ? 'disabled' : ''; ?>>
                    <i class="fas fa-cloud-download-alt me-1"></i> Load Employees
                </button>
            </div>
        </div>

        <div id="otosMessage"></div>

        <div id="otosContent" class="d-none">
            <div class="summary-bar" id="summaryBar"></div>

            <div class="search-toolbar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="otosSearch" placeholder="Search by name, station, or division...">
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm-custom" id="selectAllNotIncluded">
                    <i class="fas fa-check-double me-1"></i> Select All New
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm-custom" id="clearSelections">
                    <i class="fas fa-undo me-1"></i> Clear Selections
                </button>
            </div>

            <form id="otosIncludeForm">
                <div class="otos-table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 80px; text-align: center;">Include</th>
                                <th>Name</th>
                                <th>Station</th>
                                <th>Division / Unit</th>
                                <th>Employment Status</th>
                                <th style="width: 150px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="otosEmployeeRows"></tbody>
                    </table>
                </div>

                <div class="btn-save-bar">
                    <span class="text-muted align-self-center me-auto" id="changesInfo"></span>
                    <button type="submit" class="btn btn-primary" id="saveOtosEmployees">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="otosMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="otosMessageModalHeader">
                <h5 class="modal-title" id="otosMessageModalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="otosMessageModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Confirmation Modal -->
<div class="modal fade" id="duplicateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Possible Duplicate Found</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="duplicateMessage" class="mb-3"></p>
                <div id="duplicateList" class="mb-3"></div>
                <p class="mb-0 text-muted small">Proceed anyway?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning text-white" id="confirmDuplicateBtn">
                    <i class="fas fa-check me-1"></i> Proceed Anyway
                </button>
            </div>
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
    var content = document.getElementById('otosContent');
    var searchInput = document.getElementById('otosSearch');
    var summaryBar = document.getElementById('summaryBar');
    var changesInfo = document.getElementById('changesInfo');
    var tableWrap = document.querySelector('.otos-table-wrap');
    var messageModalElement = document.getElementById('otosMessageModal');
    var messageModalHeader = document.getElementById('otosMessageModalHeader');
    var messageModalTitle = document.getElementById('otosMessageModalTitle');
    var messageModalBody = document.getElementById('otosMessageModalBody');
    var duplicateModalElement = document.getElementById('duplicateModal');
    var pendingSave = null;
    var storageKeys = {
        station: 'otosInclude.station',
        search: 'otosInclude.search',
        scrollTop: 'otosInclude.scrollTop'
    };
    var hasRestoredSavedStation = false;

    function hasBootstrap5ModalApi() {
        return !!(window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function');
    }

    function showModalElement(modalElement) {
        if (window.jQuery && jQuery.fn.modal) {
            jQuery(modalElement).modal('show');
            return;
        }
        if (hasBootstrap5ModalApi()) {
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
            return;
        }
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.removeAttribute('aria-hidden');
    }

    function hideModalElement(modalElement) {
        if (window.jQuery && jQuery.fn.modal) {
            jQuery(modalElement).modal('hide');
            return;
        }
        if (hasBootstrap5ModalApi()) {
            bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            return;
        }
        modalElement.classList.remove('show');
        modalElement.style.display = 'none';
        modalElement.setAttribute('aria-hidden', 'true');
    }

    function showDuplicateModal() {
        showModalElement(duplicateModalElement);
    }

    function hideDuplicateModal() {
        hideModalElement(duplicateModalElement);
    }

    function setMessage(type, text) {
        if (!text) {
            message.innerHTML = '';
            hideModalElement(messageModalElement);
            return;
        }

        var config = {
            success: { icon: 'check-circle', title: 'Success', headerClass: 'modal-header bg-success text-white' },
            danger: { icon: 'times-circle', title: 'Error', headerClass: 'modal-header bg-danger text-white' },
            warning: { icon: 'exclamation-triangle', title: 'Warning', headerClass: 'modal-header bg-warning text-white' },
            info: { icon: 'info-circle', title: 'Please wait', headerClass: 'modal-header bg-info text-white' }
        }[type] || { icon: 'info-circle', title: 'Notice', headerClass: 'modal-header bg-secondary text-white' };

        messageModalHeader.className = config.headerClass;
        messageModalTitle.innerHTML = '<i class="fas fa-' + config.icon + ' mr-2"></i>' + config.title;
        messageModalBody.innerHTML = text;
        showModalElement(messageModalElement);
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>'"]/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'})[c];
        });
    }

    function updateSummary() {
        var allRows = rows.querySelectorAll('tr');
        var total = allRows.length;
        var included = 0, excluded = 0, pendingAdd = 0, pendingRemove = 0;
        var changes = 0;

        allRows.forEach(function (tr) {
            var cb = tr.querySelector('.otos-include');
            if (!cb) return;
            var already = cb.getAttribute('data-already-included') === '1';

            if (cb.checked && already) { included++; }
            else if (!cb.checked && !already) { excluded++; }
            else if (cb.checked && !already) { pendingAdd++; changes++; }
            else if (!cb.checked && already) { pendingRemove++; changes++; }
        });

        summaryBar.innerHTML =
            '<div class="summary-item total"><span class="num">' + total + '</span> Total</div>' +
            '<div class="summary-item included"><span class="num">' + included + '</span> Included</div>' +
            '<div class="summary-item excluded"><span class="num">' + excluded + '</span> Not Included</div>' +
            (pendingAdd > 0 ? '<div class="summary-item pending-add"><span class="num">+' + pendingAdd + '</span> To Include</div>' : '') +
            (pendingRemove > 0 ? '<div class="summary-item pending-remove"><span class="num">-' + pendingRemove + '</span> To Remove</div>' : '');

        changesInfo.textContent = changes > 0 ? changes + ' pending change' + (changes > 1 ? 's' : '') : 'No changes';
    }

    function filterTable() {
        var q = searchInput.value.toLowerCase().trim();
        rows.querySelectorAll('tr').forEach(function (tr) {
            var name = (tr.cells[1]?.textContent || '').toLowerCase();
            var station = (tr.cells[2]?.textContent || '').toLowerCase();
            var div = (tr.cells[3]?.textContent || '').toLowerCase();
            tr.style.display = (!q || name.indexOf(q) > -1 || station.indexOf(q) > -1 || div.indexOf(q) > -1) ? '' : 'none';
        });
    }

    function restoreTableState() {
        var savedSearch = localStorage.getItem(storageKeys.search) || '';
        if (searchInput.value !== savedSearch) {
            searchInput.value = savedSearch;
        }
        filterTable();

        if (tableWrap) {
            var savedScrollTop = parseInt(localStorage.getItem(storageKeys.scrollTop) || '0', 10);
            tableWrap.scrollTop = Number.isNaN(savedScrollTop) ? 0 : savedScrollTop;
        }
    }

    searchInput.addEventListener('input', function () {
        localStorage.setItem(storageKeys.search, searchInput.value);
        filterTable();
    });

    if (tableWrap) {
        tableWrap.addEventListener('scroll', function () {
            localStorage.setItem(storageKeys.scrollTop, String(tableWrap.scrollTop));
        });
    }

    document.getElementById('selectAllNotIncluded').addEventListener('click', function () {
        rows.querySelectorAll('.otos-include').forEach(function (cb) {
            if (cb.getAttribute('data-already-included') !== '1') {
                cb.checked = true;
                updateRowStatus(cb);
            }
        });
        updateSummary();
    });

    document.getElementById('clearSelections').addEventListener('click', function () {
        rows.querySelectorAll('.otos-include').forEach(function (cb) {
            var already = cb.getAttribute('data-already-included') === '1';
            cb.checked = already;
            updateRowStatus(cb);
        });
        updateSummary();
    });

    function updateRowStatus(cb) {
        var tr = cb.closest('tr');
        if (!tr) return;
        var status = tr.querySelector('.otos-status');
        if (!status) return;
        var already = cb.getAttribute('data-already-included') === '1';

        if (already) {
            if (cb.checked) {
                status.textContent = 'Already included';
                status.className = 'badge badge-otos bg-secondary';
            } else {
                status.textContent = 'Will be removed';
                status.className = 'badge badge-otos bg-danger';
            }
        } else {
            if (cb.checked) {
                status.textContent = 'Will be included';
                status.className = 'badge badge-otos bg-primary';
            } else {
                status.textContent = 'Not included';
                status.className = 'badge badge-otos bg-success';
            }
        }
    }

    function loadStations() {
        if (!office) {
            setMessage('warning', 'No office found in your session. Please login again.');
            return;
        }
        stationSelect.innerHTML = '<option value="">Loading stations...</option>';
        stationSelect.disabled = true;
        fetch('get_otos_stations.php')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                stationSelect.innerHTML = '<option value="">Select Station</option>';
                stationSelect.disabled = false;
                if (!data.success) {
                    setMessage('danger', data.message || 'Unable to load stations.');
                    return;
                }
                if (!data.stations.length) {
                    stationSelect.innerHTML = '<option value="">No stations found</option>';
                    return;
                }
                data.stations.forEach(function (s) {
                    var opt = document.createElement('option');
                    opt.value = s; opt.textContent = s;
                    stationSelect.appendChild(opt);
                });

                var savedStation = localStorage.getItem(storageKeys.station);
                if (savedStation && Array.prototype.some.call(stationSelect.options, function (option) { return option.value === savedStation; })) {
                    stationSelect.value = savedStation;
                    if (!hasRestoredSavedStation) {
                        hasRestoredSavedStation = true;
                        loadEmployees(true);
                    }
                }
            })
            .catch(function () {
                stationSelect.innerHTML = '<option value="">Unable to load stations</option>';
                stationSelect.disabled = false;
                setMessage('danger', 'Unable to load stations.');
            });
    }

    function loadEmployees(skipLoadingModal) {
        var station = stationSelect.value;
        form.classList.add('d-none');
        content.classList.add('d-none');
        rows.innerHTML = '';

        if (!station) {
            setMessage('warning', 'Please select a station.');
            return;
        }

        localStorage.setItem(storageKeys.station, station);

        if (!skipLoadingModal) {
            setMessage('info', '<i class="fas fa-spinner fa-spin me-2"></i>Loading employees...');
        }
        loadButton.disabled = true;

        fetch('get_otos_employees.php?station=' + encodeURIComponent(station))
            .then(function (r) { return r.json(); })
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

                rows.innerHTML = data.employees.map(function (emp) {
                    var checked = emp.already_included ? 'checked' : '';
                    var label = emp.already_included ? 'Already included' : 'Not included';
                    var badge = emp.already_included ? 'bg-secondary' : 'bg-success';
                    var alreadyVal = emp.already_included ? '1' : '0';

                    return '<tr>' +
                        '<td class="text-center">' +
                            '<input type="checkbox" class="form-check-input otos-include" value="' + escapeHtml(emp.id) + '" data-already-included="' + alreadyVal + '" ' + checked + '>' +
                        '</td>' +
                        '<td><strong>' + escapeHtml(emp.full_name) + '</strong></td>' +
                        '<td>' + escapeHtml(emp.station) + '</td>' +
                        '<td>' + escapeHtml(emp.division_unit) + '</td>' +
                        '<td>' + escapeHtml(emp.employment_status) + '</td>' +
                        '<td><span class="badge badge-otos ' + badge + ' otos-status">' + label + '</span></td>' +
                    '</tr>';
                }).join('');

                setMessage('', '');
                content.classList.remove('d-none');
                form.classList.remove('d-none');
                updateSummary();
                restoreTableState();
            })
            .catch(function () {
                loadButton.disabled = false;
                setMessage('danger', 'Unable to load employees.');
            });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var includeIds = [];
        var removeIds = [];

        rows.querySelectorAll('.otos-include').forEach(function (cb) {
            var already = cb.getAttribute('data-already-included') === '1';
            if (cb.checked && !already) includeIds.push(cb.value);
            if (!cb.checked && already) removeIds.push(cb.value);
        });

        if (includeIds.length === 0 && removeIds.length === 0) {
            setMessage('warning', 'No changes selected.');
            return;
        }

        saveButton.disabled = true;
        setMessage('info', '<i class="fas fa-spinner fa-spin me-2"></i>Saving changes...');
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
        .then(function (r) { return r.json(); })
        .then(function (data) {
            saveButton.disabled = false;
            if (data.needs_confirmation) {
                var msg = data.message || 'Possible duplicate names found:';
                var listHtml = '';
                (data.matches || []).forEach(function (m) {
                    listHtml += '<div class="alert alert-warning py-2 px-3 mb-2 small">' +
                        '<i class="fas fa-user me-1"></i> <strong>' + escapeHtml(m.otos_name) + '</strong>' +
                        ' may match <strong>' + escapeHtml(m.inventory_name) + '</strong> (' + m.similarity + '%)' +
                        '</div>';
                });
                document.getElementById('duplicateMessage').textContent = msg;
                document.getElementById('duplicateList').innerHTML = listHtml;
                pendingSave = { includeIds: includeIds, removeIds: removeIds };
                showDuplicateModal();
                return;
            }
            if (!data.success) {
                setMessage('danger', data.message || 'Unable to save employees.');
                return;
            }
            setMessage('success', '<i class="fas fa-check-circle me-1"></i> ' + (data.message || 'Selected employees saved.'));
            loadEmployees();
        })
        .catch(function () {
            saveButton.disabled = false;
            setMessage('danger', 'Unable to save employees.');
        });
    }

    document.getElementById('confirmDuplicateBtn').addEventListener('click', function () {
        hideDuplicateModal();
        if (pendingSave) {
            setMessage('info', '<i class="fas fa-spinner fa-spin me-2"></i>Saving confirmed changes...');
            saveOtosChanges(pendingSave.includeIds, pendingSave.removeIds, true);
            pendingSave = null;
        }
    });

    rows.addEventListener('change', function (event) {
        if (!event.target.classList.contains('otos-include')) return;
        updateRowStatus(event.target);
        updateSummary();
    });

    stationSelect.addEventListener('change', function () {
        localStorage.setItem(storageKeys.station, stationSelect.value);
        localStorage.setItem(storageKeys.scrollTop, '0');
    });

    loadButton.addEventListener('click', loadEmployees);
    loadStations();
})();
</script>
<?php if (session_status() === PHP_SESSION_ACTIVE) { /* keep session */ } ?>
