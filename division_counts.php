<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_division_counts'])) {
    $counts = $_POST['division_counts'] ?? [];

    $stmt = $conn->prepare("UPDATE office_divisions SET division_counts = ? WHERE id = ?");
    if (!$stmt) {
        $_SESSION['division_counts_message'] = 'Failed to prepare update statement.';
        $_SESSION['division_counts_type'] = 'danger';
        header('Location: mainmenu.php?dir=division_counts');
        exit;
    }

    foreach ($counts as $id => $value) {
        $id = (int) $id;
        $value = trim((string) $value);
        $count = ($value === '') ? 0 : max(0, (int) $value);

        $stmt->bind_param('ii', $count, $id);
        $stmt->execute();
    }

    $stmt->close();

    $_SESSION['division_counts_message'] = 'Division counts updated successfully.';
    $_SESSION['division_counts_type'] = 'success';
    header('Location: mainmenu.php?dir=division_counts');
    exit;
}

$message = $_SESSION['division_counts_message'] ?? '';
$messageType = $_SESSION['division_counts_type'] ?? 'success';
unset($_SESSION['division_counts_message'], $_SESSION['division_counts_type']);

$rows = [];
$result = $conn->query("SELECT id, office, officeDivision, division_counts FROM office_divisions ORDER BY office ASC, officeDivision ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
?>

<style>
    .division-counts-shell {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .division-counts-head {
        padding: 20px 22px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #0f172a, #1e3a8a);
        color: #fff;
        border-radius: 16px 16px 0 0;
    }

    .division-counts-body {
        padding: 18px;
    }

    .division-counts-table th {
        background: #eef2ff;
        white-space: nowrap;
    }

    .division-counts-table td,
    .division-counts-table th {
        vertical-align: middle;
    }

    .division-counts-table input[type="number"] {
        width: 120px;
    }
</style>

<div class="division-counts-shell">
    <div class="division-counts-head d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h4 class="mb-1">Division Counts</h4>
            <small>Update the default count for each division.</small>
        </div>
        <span class="badge badge-light text-dark px-3 py-2"><?php echo count($rows); ?> records</span>
    </div>

    <div class="division-counts-body">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType === 'danger' ? 'danger' : $messageType, ENT_QUOTES, 'UTF-8'); ?> mb-3" role="alert">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="table-responsive">
                <table class="table table-bordered table-hover division-counts-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:70px;">#</th>
                            <th>Office</th>
                            <th>Division</th>
                            <th style="width:160px;">Division Counts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No divisions found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $index => $row): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($row['office'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['officeDivision'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <input
                                            type="number"
                                            name="division_counts[<?php echo (int) $row['id']; ?>]"
                                            class="form-control form-control-sm"
                                            min="0"
                                            step="1"
                                            value="<?php echo (int) ($row['division_counts'] ?? 0); ?>"
                                        >
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" name="save_division_counts" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Save Counts
                </button>
            </div>
        </form>
    </div>
</div>
