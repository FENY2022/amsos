<?php
require_once 'connect_amsos.php';

function normalizeDateOrDefault($value, $default)
{
    $value = trim((string)$value);
    if ($value === '') {
        return $default;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        return $default;
    }

    return $value;
}

$defaultStartDate = (new DateTime('first day of this month'))->format('Y-m-d');
$defaultEndDate = (new DateTime())->format('Y-m-d');

$startDate = normalizeDateOrDefault($_GET['start_date'] ?? '', $defaultStartDate);
$endDate = normalizeDateOrDefault($_GET['end_date'] ?? '', $defaultEndDate);

if ($startDate > $endDate) {
    [$startDate, $endDate] = [$endDate, $startDate];
}

$completedSql = "
    SELECT
        srf.id,
        srf.ticketNumber,
        srf.date,
        srf.name,
        srf.requestType,
        srf.office,
        srf.status,
        fb.feedback AS rate
    FROM srf
    INNER JOIN (
        SELECT sf1.*
        FROM srffeedback sf1
        INNER JOIN (
            SELECT srf_id, MAX(id) AS max_id
            FROM srffeedback
            GROUP BY srf_id
        ) latest ON latest.max_id = sf1.id
    ) fb ON srf.id = fb.srf_id
    WHERE srf.status = 'Completed'
      AND fb.feedback IS NOT NULL
      AND fb.feedback <> ''
      AND STR_TO_DATE(srf.date, '%Y-%m-%d') BETWEEN ? AND ?
    ORDER BY STR_TO_DATE(srf.date, '%Y-%m-%d') DESC, srf.id DESC
";

$notCompletedSql = "
    SELECT
        srf.id,
        srf.ticketNumber,
        srf.date,
        srf.name,
        srf.requestType,
        srf.office,
        srf.status
    FROM srf
    WHERE COALESCE(srf.status, '') <> 'Completed'
      AND STR_TO_DATE(srf.date, '%Y-%m-%d') BETWEEN ? AND ?
    ORDER BY STR_TO_DATE(srf.date, '%Y-%m-%d') DESC, srf.id DESC
";

$completedStmt = $conn->prepare($completedSql);
$completedStmt->bind_param('ss', $startDate, $endDate);
$completedStmt->execute();
$completedResult = $completedStmt->get_result();

$notCompletedStmt = $conn->prepare($notCompletedSql);
$notCompletedStmt->bind_param('ss', $startDate, $endDate);
$notCompletedStmt->execute();
$notCompletedResult = $notCompletedStmt->get_result();

$completedCount = $completedResult ? $completedResult->num_rows : 0;
$notCompletedCount = $notCompletedResult ? $notCompletedResult->num_rows : 0;
$totalCount = $completedCount + $notCompletedCount;

function ratingBadgeClass($rating)
{
    switch ($rating) {
        case 'Excellent': return 'success';
        case 'Very Satisfactory': return 'primary';
        case 'Satisfactory': return 'info';
        case 'Below Satisfactory': return 'warning';
        case 'Poor': return 'danger';
        default: return 'secondary';
    }
}

function renderSummaryRows($result, $completed = false)
{
    if (!$result || $result->num_rows === 0) {
        $message = $completed ? 'No completed and rated requests found for this date range.' : 'No not completed requests found for this date range.';
        echo '<tr><td colspan="8"><div class="empty-state">' . htmlspecialchars($message) . '</div></td></tr>';
        return;
    }

    while ($row = $result->fetch_assoc()) {
        $status = (string)($row['status'] ?? '');
        $statusClass = $status === 'Completed' ? 'success' : 'secondary';
        $rate = $completed ? (string)($row['rate'] ?? 'N/A') : 'No rating';
        $rateClass = $completed ? ratingBadgeClass($rate) : 'dark';
        $printUrl = 'mainmenu.php?dir=printform&id=' . urlencode((string)$row['id']);
        $printTitle = 'Ticket #' . (string)($row['ticketNumber'] ?? '') . ' - ' . (string)($row['name'] ?? '');

        echo '<tr>';
        echo '<td><span class="ticket-pill">' . htmlspecialchars($row['ticketNumber'] ?? '') . '</span></td>';
        echo '<td>' . htmlspecialchars($row['name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['requestType'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['office'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['date'] ?? '') . '</td>';
        echo '<td><span class="status-pill bg-' . $statusClass . '">' . htmlspecialchars($status) . '</span></td>';
        echo '<td><span class="status-pill bg-' . $rateClass . '">' . htmlspecialchars($rate) . '</span></td>';
        echo '<td><button type="button" class="btn btn-sm btn-primary action-btn js-print-preview" data-print-url="' . htmlspecialchars($printUrl) . '" data-print-title="' . htmlspecialchars($printTitle) . '"><i class="fas fa-print mr-1"></i> Print</button></td>';
        echo '</tr>';
    }
}

$printAllUrl = 'print-all.php?start_date=' . urlencode($startDate) . '&end_date=' . urlencode($endDate);
$dateRangeLabel = date('M j, Y', strtotime($startDate)) . ' - ' . date('M j, Y', strtotime($endDate));
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">

<style>
    .print-summary-shell { padding: 18px; }
    .print-summary-hero {
        position: relative; overflow: hidden; border: 0; border-radius: 24px;
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 52%, #38bdf8 100%);
        color: #fff; box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18); margin-bottom: 18px;
    }
    .print-summary-hero::after {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at top right, rgba(255,255,255,.16), transparent 35%), radial-gradient(circle at bottom left, rgba(255,255,255,.12), transparent 30%);
        pointer-events: none;
    }
    .hero-inner {
        position: relative; z-index: 1; padding: 24px; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;
    }
    .hero-kicker {
        display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px;
        background: rgba(255,255,255,.15); backdrop-filter: blur(8px); font-size: .82rem; letter-spacing: .03em; text-transform: uppercase; margin-bottom: 10px;
    }
    .hero-title { margin: 0; font-size: clamp(1.35rem, 2vw, 2rem); font-weight: 800; }
    .hero-subtitle { margin: 8px 0 0; color: rgba(255,255,255,.86); max-width: 70ch; }
    .hero-meta { margin-top: 10px; font-size: .92rem; color: rgba(255,255,255,.82); }
    .hero-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: flex-end; }
    .hero-btn { border: 0; border-radius: 999px; padding: 12px 18px; font-weight: 700; transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease; }
    .hero-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(0,0,0,.16); }
    .metric-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .metric-card { background: #fff; border: 1px solid rgba(15, 23, 42, 0.06); border-radius: 18px; padding: 16px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06); }
    .metric-label { font-size: .84rem; color: #64748b; margin-bottom: 8px; }
    .metric-value { font-size: 1.55rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .metric-note { margin-top: 6px; font-size: .82rem; color: #94a3b8; }
    .surface-card { border: 0; border-radius: 24px; box-shadow: 0 16px 46px rgba(15, 23, 42, 0.08); overflow: hidden; background: #fff; }
    .surface-topbar { padding: 18px 18px 0; }
    .tabbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .nav-pills .nav-link { border-radius: 999px; padding: 10px 16px; font-weight: 700; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; margin-right: 8px; }
    .nav-pills .nav-link.active { color: #fff; background: linear-gradient(135deg, #1d4ed8, #0f172a); border-color: transparent; box-shadow: 0 10px 20px rgba(29, 78, 216, .18); }
    .toolbar { margin-left: auto; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .search-wrap { position: relative; min-width: min(360px, 100%); flex: 1 1 280px; }
    .search-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-input {
        width: 100%; border-radius: 999px; border: 1px solid #dbe4f0; background: #f8fafc; padding: 12px 16px 12px 40px;
        outline: none; transition: box-shadow .15s ease, border-color .15s ease;
    }
    .search-input:focus { border-color: #93c5fd; box-shadow: 0 0 0 4px rgba(59,130,246,.12); background: #fff; }
    .date-wrap { min-width: min(360px, 100%); flex: 1 1 280px; }
    .range-control {
        width: 100%; border-radius: 999px; border: 1px solid #dbe4f0; background: #fff; padding: 12px 16px 12px 40px;
        outline: none; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }
    .date-input-group { position: relative; }
    .date-input-group i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; z-index: 2; }
    .tab-content { padding: 18px; }
    .table-wrap { overflow: auto; border: 1px solid #e2e8f0; border-radius: 18px; }
    .modern-table { margin: 0; min-width: 980px; background: #fff; }
    .modern-table thead th {
        position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #334155; font-size: .82rem; text-transform: uppercase; letter-spacing: .04em;
        border-bottom: 1px solid #e2e8f0; white-space: nowrap;
    }
    .modern-table td, .modern-table th { vertical-align: middle; border-color: #edf2f7; padding: 14px 12px; }
    .modern-table tbody tr { transition: background-color .15s ease; }
    .modern-table tbody tr:hover { background: #f8fbff; }
    .ticket-pill, .status-pill {
        display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 6px 12px; font-weight: 700; font-size: .82rem; white-space: nowrap;
    }
    .ticket-pill { background: #e0f2fe; color: #075985; }
    .status-pill.bg-success { background: #dcfce7 !important; color: #166534 !important; }
    .status-pill.bg-secondary { background: #e2e8f0 !important; color: #334155 !important; }
    .status-pill.bg-primary { background: #dbeafe !important; color: #1d4ed8 !important; }
    .status-pill.bg-info { background: #cffafe !important; color: #0e7490 !important; }
    .status-pill.bg-warning { background: #fef3c7 !important; color: #92400e !important; }
    .status-pill.bg-danger { background: #fee2e2 !important; color: #b91c1c !important; }
    .status-pill.bg-dark { background: #e2e8f0 !important; color: #0f172a !important; }
    .action-btn { border-radius: 999px; padding-inline: 14px; font-weight: 700; box-shadow: 0 8px 18px rgba(29, 78, 216, .16); }
    .empty-state { padding: 36px 18px; text-align: center; color: #64748b; background: linear-gradient(180deg, #ffffff, #f8fafc); border-radius: 16px; }
    .filter-bar { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; margin-top: 14px; }
    .filter-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .range-chip {
        display: inline-flex; align-items: center; gap: 8px; border-radius: 999px; padding: 10px 14px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.16); color: #fff;
        font-weight: 700;
    }
    @media (max-width: 992px) { .metric-grid { grid-template-columns: 1fr; } .hero-inner { padding: 20px; } .toolbar { margin-left: 0; width: 100%; } }
    @media (max-width: 576px) { .print-summary-shell { padding: 12px; } .surface-topbar, .tab-content { padding-left: 12px; padding-right: 12px; } }
</style>

<div class="print-summary-shell">
    <div class="card print-summary-hero">
        <div class="hero-inner">
            <div>
                <div class="hero-kicker"><i class="fas fa-print"></i> Print Dashboard</div>
                <h1 class="hero-title">SRF Print Queue</h1>
                <p class="hero-subtitle">Completed and rated requests appear first for printing. Non-completed requests stay in a separate tab for quick access.</p>
                <div class="hero-meta">Filtered range: <strong><?php echo htmlspecialchars($dateRangeLabel); ?></strong></div>
            </div>
            <div class="hero-actions">
                <button id="printAllBtn" type="button" class="btn btn-light hero-btn js-print-preview" data-print-url="<?php echo htmlspecialchars($printAllUrl); ?>" data-print-title="Print All Rated">
                    <i class="fas fa-layer-group mr-1"></i> Print All Rated
                </button>
                <a class="btn btn-outline-light hero-btn" href="mainmenu.php?dir=printform">
                    <i class="fas fa-rotate-left mr-1"></i> Reset
                </a>
            </div>
        </div>
    </div>

    <div class="metric-grid">
        <div class="metric-card"><div class="metric-label">Completed + Rated</div><div class="metric-value"><?php echo (int)$completedCount; ?></div><div class="metric-note">Ready for print</div></div>
        <div class="metric-card"><div class="metric-label">Not Completed</div><div class="metric-value"><?php echo (int)$notCompletedCount; ?></div><div class="metric-note">Printable individually</div></div>
        <div class="metric-card"><div class="metric-label">Total Visible</div><div class="metric-value"><?php echo (int)$totalCount; ?></div><div class="metric-note">Within selected date range</div></div>
    </div>

    <div class="card surface-card">
        <div class="surface-topbar">
            <div class="tabbar">
                <ul class="nav nav-pills" id="printTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" id="completed-tab" data-toggle="tab" href="#completed" role="tab" aria-controls="completed" aria-selected="true">Completed + Rated <span class="badge badge-light ml-1"><?php echo (int)$completedCount; ?></span></a></li>
                    <li class="nav-item"><a class="nav-link" id="not-completed-tab" data-toggle="tab" href="#not-completed" role="tab" aria-controls="not-completed" aria-selected="false">Not Completed <span class="badge badge-light ml-1"><?php echo (int)$notCompletedCount; ?></span></a></li>
                </ul>

                <div class="toolbar">
                    <div class="date-wrap">
                        <div class="date-input-group">
                            <i class="fas fa-calendar-week"></i>
                            <input type="text" id="daterange" class="range-control" value="<?php echo htmlspecialchars($startDate); ?> - <?php echo htmlspecialchars($endDate); ?>" autocomplete="off">
                            <input type="hidden" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                            <input type="hidden" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                        </div>
                    </div>
                    <div class="search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" class="search-input" id="tableSearch" placeholder="Search ticket, name, request type, office, status...">
                    </div>
                    <div class="filter-actions">
                        <button type="button" class="btn btn-primary hero-btn" id="applyFilterBtn"><i class="fas fa-filter mr-1"></i> Apply</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="completed" role="tabpanel" aria-labelledby="completed-tab">
                <div class="table-wrap">
                    <table class="table modern-table table-hover mb-0" data-table-key="completed">
                        <thead><tr><th>Ticket No.</th><th>Name</th><th>Request Type</th><th>Office</th><th>Date</th><th>Status</th><th>Rating</th><th>Action</th></tr></thead>
                        <tbody><?php renderSummaryRows($completedResult, true); ?></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="not-completed" role="tabpanel" aria-labelledby="not-completed-tab">
                <div class="table-wrap">
                    <table class="table modern-table table-hover mb-0" data-table-key="not-completed">
                        <thead><tr><th>Ticket No.</th><th>Name</th><th>Request Type</th><th>Office</th><th>Date</th><th>Status</th><th>Rating</th><th>Action</th></tr></thead>
                        <tbody><?php renderSummaryRows($notCompletedResult, false); ?></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="printPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content" style="border:0;border-radius:22px;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,.25);">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 52%,#38bdf8 100%);color:#fff;">
                <div>
                    <h5 class="modal-title mb-0" id="printPreviewTitle">Print Preview</h5>
                    <small class="text-white-50">Preview the report, then print from the modal.</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:1;text-shadow:none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="background:#f8fafc;">
                <iframe id="printPreviewFrame" src="about:blank" style="width:100%;height:78vh;border:0;background:#fff;"></iframe>
            </div>
            <div class="modal-footer border-0" style="background:#fff;">
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printPreviewBtn"><i class="fas fa-print mr-1"></i> Print</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {
    var searchInput = document.getElementById('tableSearch');
    var printAllBtn = document.getElementById('printAllBtn');
    var completedTab = document.getElementById('completed-tab');
    var applyFilterBtn = document.getElementById('applyFilterBtn');
    var startDateInput = document.getElementById('start_date');
    var endDateInput = document.getElementById('end_date');
    var previewModal = $('#printPreviewModal');
    var previewFrame = document.getElementById('printPreviewFrame');
    var previewTitle = document.getElementById('printPreviewTitle');
    var previewPrintBtn = document.getElementById('printPreviewBtn');

    var currentPreviewUrl = '';

    flatpickr('#daterange', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'M j, Y',
        defaultDate: ['<?php echo $startDate; ?>', '<?php echo $endDate; ?>'],
        showMonths: 2,
        conjunction: ' - ',
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                startDateInput.value = instance.formatDate(selectedDates[0], 'Y-m-d');
                endDateInput.value = instance.formatDate(selectedDates[1], 'Y-m-d');
            }
        }
    });

    function activeTable() {
        var pane = document.querySelector('.tab-pane.active.show');
        return pane ? pane.querySelector('table') : null;
    }

    function filterRows() {
        var table = activeTable();
        if (!table) return;
        var term = (searchInput.value || '').toLowerCase().trim();
        var rows = table.querySelectorAll('tbody tr');

        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = !term || text.indexOf(term) !== -1 ? '' : 'none';
        });
    }

    function goToFiltered() {
        var url = new URL(window.location.href);
        url.searchParams.set('start_date', startDateInput.value);
        url.searchParams.set('end_date', endDateInput.value);
        window.location.href = url.toString();
    }

    function syncPrimaryAction() {
        var onCompleted = completedTab.classList.contains('active');
        printAllBtn.style.display = onCompleted ? 'inline-flex' : 'none';
        printAllBtn.disabled = !onCompleted;
        printAllBtn.setAttribute('aria-hidden', onCompleted ? 'false' : 'true');
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }

    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', goToFiltered);
    }

    document.querySelectorAll('.js-print-preview').forEach(function (button) {
        button.addEventListener('click', function () {
            currentPreviewUrl = button.getAttribute('data-print-url') || '';
            var title = button.getAttribute('data-print-title') || 'Print Preview';
            previewTitle.textContent = title;
            previewFrame.src = currentPreviewUrl || 'about:blank';
            previewModal.modal('show');
        });
    });

    if (previewPrintBtn) {
        previewPrintBtn.addEventListener('click', function () {
            if (!previewFrame || !previewFrame.contentWindow) return;
            previewFrame.contentWindow.focus();
            previewFrame.contentWindow.print();
        });
    }

    if (previewModal) {
        previewModal.on('hidden.bs.modal', function () {
            if (previewFrame) {
                previewFrame.src = 'about:blank';
            }
            currentPreviewUrl = '';
        });
    }

    document.querySelectorAll('[data-toggle="tab"]').forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function () {
            filterRows();
            syncPrimaryAction();
        });
    });

    syncPrimaryAction();
})();
</script>
