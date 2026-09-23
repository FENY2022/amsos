<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "connect.php";
require_once "connect_otos.php";
require_once "role_access.php";

if (!amsos_can_manage_configuration($_SESSION['User_RoleSRF'] ?? '')) {
    echo '<div class="alert alert-danger">You do not have permission to manage AMSOS users.</div>';
    exit;
}

$office = trim($_POST['office'] ?? '');
$officeDivision = trim($_POST['officeDivision'] ?? ($_POST['station'] ?? ''));
$fullname = trim($_POST['fullname'] ?? '');

$sql = "SELECT id, otos_user_id, full_name, office, officeDivision, employment_status, source, created_at
        FROM inventory_people
        WHERE office = ?";
$params = [$office];
$types = 's';

if ($officeDivision !== '') {
    $sql .= " AND officeDivision = ?";
    $params[] = $officeDivision;
    $types .= 's';
}

if ($fullname !== '') {
    $sql .= " AND full_name LIKE ?";
    $params[] = "%$fullname%";
    $types .= 's';
}

$sql .= " ORDER BY full_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$people = [];
$otosIds = [];

while ($row = $result->fetch_assoc()) {
    $people[] = $row;
    if (!empty($row['otos_user_id']) && is_numeric($row['otos_user_id'])) {
        $otosIds[] = (int)$row['otos_user_id'];
    }
}
$stmt->close();

$divisionOptions = [];
$divisionStmt = $conn->prepare("SELECT officeDivision FROM office_divisions WHERE office = ? AND officeDivision IS NOT NULL AND officeDivision != '' ORDER BY officeDivision ASC");
$divisionStmt->bind_param('s', $office);
$divisionStmt->execute();
$divisionResult = $divisionStmt->get_result();
while ($divisionRow = $divisionResult->fetch_assoc()) {
    $divisionOptions[] = $divisionRow['officeDivision'];
}
$divisionStmt->close();

if (empty($divisionOptions)) {
    $fallbackDivisionStmt = $conn->prepare("SELECT DISTINCT officeDivision FROM inventory_people WHERE office = ? AND officeDivision IS NOT NULL AND officeDivision != '' ORDER BY officeDivision ASC");
    $fallbackDivisionStmt->bind_param('s', $office);
    $fallbackDivisionStmt->execute();
    $fallbackDivisionResult = $fallbackDivisionStmt->get_result();
    while ($divisionRow = $fallbackDivisionResult->fetch_assoc()) {
        $divisionOptions[] = $divisionRow['officeDivision'];
    }
    $fallbackDivisionStmt->close();
}

$rolesByUserId = [];
$otosIds = array_values(array_unique($otosIds));

if (!empty($otosIds)) {
    $placeholders = implode(',', array_fill(0, count($otosIds), '?'));
    $idTypes = str_repeat('i', count($otosIds));

    $roleStmt = $conn_otos->prepare("SELECT id, Office, User_Role FROM useremployee WHERE id IN ($placeholders)");
    if ($roleStmt) {
        $roleStmt->bind_param($idTypes, ...$otosIds);
        $roleStmt->execute();
        $roleResult = $roleStmt->get_result();

        while ($roleRow = $roleResult->fetch_assoc()) {
            $rolesByUserId[(int)$roleRow['id']] = [
                'office' => trim((string)$roleRow['Office']),
                'role' => trim((string)$roleRow['User_Role']),
            ];
        }

        $roleStmt->close();
    }
}

$amsosRoleOverrides = [];

if (!empty($otosIds) && amsos_ensure_user_roles_table($conn)) {
    $placeholders = implode(',', array_fill(0, count($otosIds), '?'));
    $idTypes = str_repeat('i', count($otosIds));

    $overrideStmt = $conn->prepare("SELECT otos_user_id, role FROM amsos_user_roles WHERE otos_user_id IN ($placeholders)");
    if ($overrideStmt) {
        $overrideStmt->bind_param($idTypes, ...$otosIds);
        $overrideStmt->execute();
        $overrideResult = $overrideStmt->get_result();

        while ($overrideRow = $overrideResult->fetch_assoc()) {
            $amsosRoleOverrides[(int)$overrideRow['otos_user_id']] = trim((string)$overrideRow['role']);
        }

        $overrideStmt->close();
    }
}

$systemRoles = [];
$systemRolesResult = $conn_otos->query("SELECT DISTINCT User_Role FROM useremployee WHERE User_Role IS NOT NULL AND TRIM(User_Role) <> '' ORDER BY User_Role ASC");
if ($systemRolesResult) {
    while ($roleRow = $systemRolesResult->fetch_assoc()) {
        $roleValue = trim((string)$roleRow['User_Role']);
        if ($roleValue !== '') {
            $systemRoles[amsos_role_key($roleValue)] = $roleValue;
        }
    }
}
if (amsos_ensure_user_roles_table($conn)) {
    $localRolesResult = $conn->query("SELECT DISTINCT role FROM amsos_user_roles WHERE role IS NOT NULL AND TRIM(role) <> '' ORDER BY role ASC");
    if ($localRolesResult) {
        while ($roleRow = $localRolesResult->fetch_assoc()) {
            $roleValue = trim((string)$roleRow['role']);
            if ($roleValue !== '') {
                $systemRoles[amsos_role_key($roleValue)] = $roleValue;
            }
        }
    }
}
$systemRoles = array_values($systemRoles);

echo '<div style="max-height: 560px; overflow: auto; border: 1px solid #ccc; padding: 10px; border-radius: 8px;">';
echo '<table style="width: 100%; border-collapse: collapse;">';
echo '<tr style="background-color: #4CAF50; color: white;">';
echo '<th style="padding: 8px; text-align: left;">Full Name</th>';
echo '<th style="padding: 8px; text-align: left;">Office</th>';
echo '<th style="padding: 8px; text-align: left;">Office Division</th>';
echo '<th style="padding: 8px; text-align: left;">AMSOS Role</th>';
echo '<th style="padding: 8px; text-align: left;">Employment Status</th>';
echo '<th style="padding: 8px; text-align: left;">Source</th>';
echo '<th style="padding: 8px; text-align: left;">OTOS User ID</th>';
echo '<th style="padding: 8px; text-align: left;">Saved Date</th>';
echo '</tr>';

$rowCount = 0;

foreach ($people as $row) {
    $rowCount++;
    $otosUserId = !empty($row['otos_user_id']) && is_numeric($row['otos_user_id']) ? (int)$row['otos_user_id'] : 0;
    $roleInfo = $otosUserId > 0 && isset($rolesByUserId[$otosUserId]) ? $rolesByUserId[$otosUserId] : null;
    $roleOffice = $roleInfo && $roleInfo['office'] !== '' ? $roleInfo['office'] : $row['office'];
    $baseRole = $roleInfo ? $roleInfo['role'] : '';
    $currentRole = $otosUserId > 0 && isset($amsosRoleOverrides[$otosUserId])
        ? $amsosRoleOverrides[$otosUserId]
        : $baseRole;

    $roleOptions = amsos_filter_roles_for_office($systemRoles, $roleOffice);

    if ($currentRole !== '') {
        $hasCurrentRole = false;
        foreach ($roleOptions as $roleOption) {
            if (amsos_role_key($roleOption) === amsos_role_key($currentRole)) {
                $hasCurrentRole = true;
                break;
            }
        }

        if (!$hasCurrentRole) {
            array_unshift($roleOptions, $currentRole);
        }
    }

    echo '<tr style="border-bottom: 1px solid #ddd;">';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['full_name']) . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['office']) . '</td>';
    echo '<td style="padding: 8px; min-width: 220px;">';
    echo '<select class="division-update-select" data-id="' . (int)$row['id'] . '" data-name="' . htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8') . '" data-office="' . htmlspecialchars($row['office'], ENT_QUOTES, 'UTF-8') . '" data-original="' . htmlspecialchars($row['officeDivision'], ENT_QUOTES, 'UTF-8') . '" style="width: 100%; min-width: 190px; height: 38px; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff;">';
    foreach ($divisionOptions as $divisionOption) {
        $selected = $divisionOption === $row['officeDivision'] ? ' selected' : '';
        echo '<option value="' . htmlspecialchars($divisionOption, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($divisionOption) . '</option>';
    }
    if (!in_array($row['officeDivision'], $divisionOptions, true)) {
        echo '<option value="' . htmlspecialchars($row['officeDivision'], ENT_QUOTES, 'UTF-8') . '" selected>' . htmlspecialchars($row['officeDivision']) . '</option>';
    }
    echo '</select>';
    echo '</td>';

    echo '<td style="padding: 8px; min-width: 205px;">';
    if ($otosUserId > 0 && $roleInfo) {
        echo '<select class="user-role-select" data-user-id="' . $otosUserId . '" data-name="' . htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8') . '" data-office="' . htmlspecialchars($roleOffice, ENT_QUOTES, 'UTF-8') . '" data-original="' . htmlspecialchars($currentRole, ENT_QUOTES, 'UTF-8') . '" style="width: 100%; min-width: 180px; height: 38px; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff;">';

        if ($currentRole === '') {
            echo '<option value="" selected>-- Select Role --</option>';
        }

        foreach ($roleOptions as $roleOption) {
            $selected = amsos_role_key($roleOption) === amsos_role_key($currentRole) ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($roleOption) . '</option>';
        }

        echo '</select>';

        if (amsos_is_regional_office($roleOffice)) {
            echo '<div style="margin-top: 4px; font-size: 11px; color: #64748b;">Chief role: Division Chief</div>';
        } elseif (amsos_is_penro_or_cenro($roleOffice)) {
            echo '<div style="margin-top: 4px; font-size: 11px; color: #64748b;">Chief role: Section Chief</div>';
        }
    } else {
        echo '<span style="color: #64748b; font-size: 12px;">Not linked to an OTOS account</span>';
    }
    echo '</td>';

    echo '<td style="padding: 8px;">' . htmlspecialchars($row['employment_status']) . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['source']) . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['otos_user_id'] ?? '') . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['created_at']) . '</td>';
    echo '</tr>';
}

if ($rowCount === 0) {
    echo '<tr><td colspan="8" style="padding: 16px; text-align: center;">No records found.</td></tr>';
}

echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
echo '<td colspan="7" style="padding: 8px; text-align: right;">Total Rows:</td>';
echo '<td style="padding: 8px; text-align: left;">' . $rowCount . '</td>';
echo '</tr>';

echo '</table>';
echo '</div>';

$conn->close();
$conn_otos->close();
?>