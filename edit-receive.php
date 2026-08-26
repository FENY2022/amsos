<?php

require_once 'connect_amsos.php';

// Set the default timezone to prevent potential date/time errors
date_default_timezone_set('Asia/Manila');

function fetchOllamaModels() {
    $fallback = ['deepseek-r1:latest', 'qwen3:latest', 'qwen3:4b', 'qwen2.5-coder:7b', 'MFDoom/deepseek-r1-tool-calling:8b', 'gemma4:latest', 'phi3:latest', 'tinyllama:latest'];

    if (!function_exists('curl_init')) {
        return $fallback;
    }

    $ch = curl_init('http://localhost:11434/api/tags');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return $fallback;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200) {
        return $fallback;
    }

    $data = json_decode($response, true);
    if (!is_array($data) || empty($data['models'])) {
        return $fallback;
    }

    $models = [];
    foreach ($data['models'] as $model) {
        $name = trim((string)($model['name'] ?? ''));
        $capabilitiesRaw = $model['capabilities'] ?? [];
        $capabilities = is_array($capabilitiesRaw) ? strtolower(implode(' ', $capabilitiesRaw)) : strtolower((string)$capabilitiesRaw);
        if ($name === '') {
            continue;
        }
        if ($capabilities !== '' && strpos($capabilities, 'completion') === false && strpos($capabilities, 'thinking') === false && strpos($capabilities, 'tools') === false) {
            continue;
        }
        $models[] = $name;
    }

    if (empty($models)) {
        return $fallback;
    }

    return array_values(array_unique($models));
}

$trackid = $_GET['id'];

// Fetch receive history
$sql = "SELECT id, name, date, time, details FROM srfhistory WHERE trackid = ? ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $trackid);
$stmt->execute();
$history_result = $stmt->get_result();

// Fetch RICTU Staff action history
$query = "SELECT
            srf_actiontaken.id,
            srf_actiontaken.date,
            srf_actiontaken.time,
            srf_actiontaken.remarks,
            srf_actiontaken.name,
            srfactionstaff.signature
          FROM srf_actiontaken
          INNER JOIN srfactionstaff ON srf_actiontaken.name = srfactionstaff.name
          WHERE srf_actiontaken.trackid = ?";

$stmt_action = $conn->prepare($query);
if ($stmt_action === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt_action->bind_param("s", $trackid);
if (!$stmt_action->execute()) {
    die('Execute failed: ' . htmlspecialchars($stmt_action->error));
}
$action_result = $stmt_action->get_result();

$ollamaModels = fetchOllamaModels();
$defaultModel = in_array('tinyllama:latest', $ollamaModels, true) ? 'tinyllama:latest' : ($ollamaModels[0] ?? 'tinyllama:latest');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Receive Status</title>
    <link rel="icon" type="image/x-icon" href="icon/amsos.ico">
    <link rel="shortcut icon" type="image/x-icon" href="icon/amsos.ico">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Responsive table styling for mobile */
        @media (max-width: 768px) {
            .responsive-table thead {
                display: none;
            }
            .responsive-table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e5e7eb;
                border-radius: 0.5rem;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            }
            .responsive-table td {
                display: block;
                text-align: right;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #e5e7eb;
            }
            .responsive-table td:before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                text-transform: uppercase;
                color: #4b5563;
            }
            .responsive-table td:last-child {
                border-bottom: 0;
            }
        }
        
        body::-webkit-scrollbar { width: 8px; }
        body::-webkit-scrollbar-track { background: #f1f1f1; }
        body::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        body::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>
<body class="bg-gray-100 p-4 min-h-screen flex flex-col items-center">

    <div class="container max-w-4xl mx-auto bg-white rounded-lg shadow-xl p-6 mb-8">
        <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
            <h1 class="text-3xl font-bold text-gray-800">Receive History</h1>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1 pr-3 border-r border-gray-300">
                    <select id="ollamaModelSelect" class="select select-bordered select-xs max-w-36">
                        <?php foreach ($ollamaModels as $model): ?>
                            <option value="<?= htmlspecialchars($model) ?>" <?= $model === $defaultModel ? 'selected' : '' ?>><?= htmlspecialchars($model) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button id="autoAdjustTimelineBtn" onclick="autoAdjustTimeline()" class="btn btn-sm btn-warning text-white gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2l-2 7h7l-8 13 2-8H5l8-12z"/></svg>
                        Auto edit Timeline
                    </button>
                </div>
                <button onclick="autoAdjustReceiveDate()" class="btn btn-sm btn-outline btn-primary">Auto Adjust Date</button>
                <button onclick="autoAdjustReceiveTime()" class="btn btn-sm btn-accent text-white">Auto Adjust Time</button>
                <button id="saveAllReceiveBtn" onclick="saveAllReceive()" class="btn btn-sm btn-success text-white gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Save All
                </button>
            </div>
        </div>
        <?php if ($history_result->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table id="receiveHistoryTable" class="responsive-table table w-full text-left">
                    <thead>
                        <tr class="bg-blue-600 text-white">
                            <th class="p-3 rounded-tl-lg">ID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Details</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Time</th>
                            <th class="p-3 rounded-tr-lg">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $history_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 border-b border-gray-200">
                                <td class="p-3" data-label="ID"><?= htmlspecialchars($row['id']) ?></td>
                                <td class="p-3" data-label="Name">
                                    <span id="name_<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></span>
                                    <input type="text" id="name_input_<?= $row['id'] ?>" value="<?= htmlspecialchars($row['name']) ?>" class="input input-bordered w-full hidden">
                                </td>
                                <td class="p-3" data-label="Details">
                                    <span id="details_<?= $row['id'] ?>"><?= htmlspecialchars($row['details']) ?></span>
                                    <input type="text" id="details_input_<?= $row['id'] ?>" value="<?= htmlspecialchars($row['details']) ?>" class="input input-bordered w-full hidden">
                                </td>
                                <td class="p-3" data-label="Date">
                                    <span id="date_<?= $row['id'] ?>"><?= htmlspecialchars($row['date']) ?></span>
                                    <input type="date" id="date_input_<?= $row['id'] ?>" value="<?= htmlspecialchars($row['date']) ?>" class="input input-bordered w-full hidden">
                                </td>
                                <td class="p-3" data-label="Time">
                                    <span id="time_<?= $row['id'] ?>"><?= date("h:i A", strtotime($row['time'])) ?></span>
                                    <input type="text" id="time_input_<?= $row['id'] ?>" value="<?= date("h:i A", strtotime($row['time'])) ?>" class="input input-bordered w-full hidden">
                                </td>
                                <td class="p-3 flex flex-col gap-2 md:flex-row md:gap-1" data-label="Actions">
                                    <button onclick="toggleEdit(<?= $row['id'] ?>)" id="edit_btn_<?= $row['id'] ?>" class="btn btn-sm btn-info text-white"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" /></svg>Edit</button>
                                    <button onclick="saveChanges(<?= $row['id'] ?>)" id="save_btn_<?= $row['id'] ?>" class="btn btn-sm btn-success hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Save</button>
                                    <button onclick="cancelEdit(<?= $row['id'] ?>)" id="cancel_btn_<?= $row['id'] ?>" class="btn btn-sm btn-error hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>Cancel</button>
                                    <a href="delete-receive.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" onclick='return confirm("Are you sure?")'><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <script>
                function toggleEdit(id) {
                    ['name', 'date', 'time', 'details'].forEach(field => {
                        document.getElementById(`${field}_${id}`).classList.toggle('hidden');
                        document.getElementById(`${field}_input_${id}`).classList.toggle('hidden');
                    });
                    ['edit_btn', 'save_btn', 'cancel_btn'].forEach(btn => document.getElementById(`${btn}_${id}`).classList.toggle('hidden'));
                }

                function cancelEdit(id) { toggleEdit(id); }

                function saveChanges(id) {
                    var btn = document.getElementById('save_btn_' + id);
                    var originalHtml = btn.innerHTML;
                    
                    // Show Loading State
                    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving...';
                    btn.disabled = true;

                    var name = document.getElementById('name_input_' + id).value;
                    var date = document.getElementById('date_input_' + id).value;
                    var time = document.getElementById('time_input_' + id).value; 
                    var details = document.getElementById('details_input_' + id).value;

                    return fetch('update-receive.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
                        body: `id=${id}&name=${name}&date=${date}&time=${time}&details=${details}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('name_' + id).textContent = name;
                            document.getElementById('date_' + id).textContent = date;
                            document.getElementById('time_' + id).textContent = time;
                            document.getElementById('details_' + id).textContent = details;
                            cancelEdit(id);
                        } else { alert('Update failed'); }
                    })
                    .finally(() => {
                        // Restore Button State
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    });
                }
            </script>
        <?php else: ?>
            <p class="text-gray-600 text-center">No receive history records found.</p>
        <?php endif; ?>
    </div>

    <div class="container max-w-4xl mx-auto bg-white rounded-lg shadow-xl p-6">
        <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
            <h1 class="text-3xl font-bold text-gray-800">RICTU Staff Actions</h1>
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="autoAdjustActionDate()" class="btn btn-sm btn-outline btn-primary">Auto Adjust Date</button>
                <button onclick="autoAdjustActionTime()" class="btn btn-sm btn-accent text-white">Auto Adjust Time</button>
                <button id="saveAllActionBtn" onclick="saveAllAction()" class="btn btn-sm btn-success text-white gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Save All
                </button>
            </div>
        </div>
        <?php if ($action_result && $action_result->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table id="staffActionsTable" class="responsive-table table w-full text-left">
                    <thead>
                        <tr class="bg-blue-600 text-white">
                            <th class="p-3 rounded-tl-lg">ID</th> <th class="p-3">Date</th> <th class="p-3">Time</th> <th class="p-3">Remarks</th>
                            <th class="p-3">Name</th> <th class="p-3">Signature</th> <th class="p-3 rounded-tr-lg">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $action_result->fetch_assoc()): $id = $row['id']; ?>
                            <tr id='row_<?= $id ?>' class="hover:bg-gray-50 border-b border-gray-200">
                                <td class="p-3" data-label="ID"><?= htmlspecialchars($row['id']) ?></td>
                                <td class="p-3" data-label="Date">
                                    <span id='date_<?= $id ?>'><?= date("F j, Y", strtotime($row['date'])) ?></span>
                                    <input type="date" id='date_input_<?= $id ?>' value="<?= htmlspecialchars($row['date']) ?>" class="input input-bordered w-full hidden">
                                </td>
                                <td class="p-3" data-label="Time">
                                    <span id='time_<?= $id ?>'><?= date("h:i A", strtotime($row['time'])) ?></span>
                                    <input type="text" id='time_input_<?= $id ?>' value="<?= date("h:i A", strtotime($row['time'])) ?>" class="input input-bordered w-full hidden">
                                </td>
                                <td class="p-3" data-label="Remarks">
                                    <span id='remarks_<?= $id ?>'><?= htmlspecialchars($row['remarks']) ?></span>
                                    <input type="text" id='remarks_input_<?= $id ?>' value="<?= htmlspecialchars($row['remarks']) ?>" class="input input-bordered w-full hidden">
                                </td>
                                <td class="p-3" data-label="Name">
                                    <span id='name_<?= $id ?>'><?= htmlspecialchars($row['name']) ?></span>
                                    <input type="text" id='name_input_<?= $id ?>' value="<?= htmlspecialchars($row['name']) ?>" class="input input-bordered w-full hidden">
                                </td>
                                <td class="p-3" data-label="Signature">
                                    <img src='srfsigner/<?= htmlspecialchars($row['signature']) ?>' alt='Signature' class='w-24 h-auto object-contain'>
                                </td>
                                <td class="p-3 flex flex-col gap-2 md:flex-row md:gap-1">
                                    <button onclick='editRow(<?= $id ?>)' class='btn btn-sm btn-info text-white edit-action-btn-<?= $id ?>'><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>Edit</button>
                                    <button id="save_action_btn_<?= $id ?>" onclick='saveRow(<?= $id ?>)' class='btn btn-sm btn-success hidden save-action-btn-<?= $id ?>'><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Save</button>
                                    <button onclick='cancelActionEdit(<?= $id ?>)' class='btn btn-sm btn-error hidden cancel-action-btn-<?= $id ?>'><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>Cancel</button>
                                    <button onclick='deleteActionRow(<?= $id ?>)' class='btn btn-sm btn-warning text-white'><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <script>
                function editRow(id) {
                    ['date', 'time', 'remarks', 'name'].forEach(field => {
                        document.getElementById(`${field}_${id}`).classList.toggle('hidden');
                        document.getElementById(`${field}_input_${id}`).classList.toggle('hidden');
                    });
                    document.querySelector('.edit-action-btn-' + id).classList.toggle('hidden');
                    document.querySelector('.save-action-btn-' + id).classList.toggle('hidden');
                    document.querySelector('.cancel-action-btn-' + id).classList.toggle('hidden');
                }
                function cancelActionEdit(id) { editRow(id); }

                function saveRow(id) {
                    var btn = document.getElementById('save_action_btn_' + id);
                    var originalHtml = btn.innerHTML;
                    
                    // Show Loading State
                    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving...';
                    btn.disabled = true;

                    var date = document.getElementById('date_input_' + id).value;
                    var time = document.getElementById('time_input_' + id).value;
                    var remarks = document.getElementById('remarks_input_' + id).value;
                    var name = document.getElementById('name_input_' + id).value;

                    return fetch('update_action.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', },
                        body: JSON.stringify({ id, date, time, remarks, name })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const newDate = new Date(date + 'T00:00:00');
                            const formattedDate = newDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                            document.getElementById('date_' + id).textContent = formattedDate;
                            document.getElementById('time_' + id).textContent = time;
                            document.getElementById('remarks_' + id).textContent = remarks;
                            document.getElementById('name_' + id).textContent = name;
                            cancelActionEdit(id);
                        } else { alert('Update failed'); }
                    })
                    .finally(() => {
                        // Restore Button State
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    });
                }

                function deleteActionRow(id) {
                    if (confirm('Are you sure you want to delete this record?')) {
                        fetch('delete_action.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', },
                            body: JSON.stringify({ id: id })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('row_' + id).remove();
                            } else { alert('Delete failed'); }
                        });
                    }
                }
            </script>
        <?php else: ?>
            <p class="text-gray-600 text-center">No records found.</p>
        <?php endif; $stmt_action->close(); ?>

        <div class="flex justify-center mt-8">
            <button type="button" class="btn btn-primary" onclick="addModal.showModal()">Feedback</button>
        </div>

        <dialog id="addModal" class="modal">
            <div class="modal-box w-11/12 max-w-4xl p-0">
                <div class="modal-header p-4 flex justify-between items-center border-b">
                    <h3 class="font-bold text-lg">View Document</h3>
                    <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost">✕</button></form>
                </div>
                <div class="modal-body p-0" style="height: 70vh;">
                    <iframe src="edit-feedback.php?id=<?= htmlspecialchars($trackid) ?>" class="w-full h-full border-none"></iframe>
                </div>
            </div>
        </dialog>
    </div>

    <dialog id="adjustSummaryModal" class="modal">
        <div class="modal-box w-11/12 max-w-5xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-xl">Timeline Adjustments Applied</h3>
                <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost">✕</button></form>
            </div>
            <div id="summaryContent" class="overflow-x-auto max-h-[65vh] overflow-y-auto"></div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-primary" onclick="window.location.reload()">OK, Reload Page</button>
                </form>
            </div>
        </div>
    </dialog>

    <script>
        /**
         * Global Save All Functions
         */
        async function autoAdjustTimeline() {
            if (!confirm('Auto-adjust all dates/times for this SRF within one day and set feedback to Excellent?')) return;

            const btn = document.getElementById('autoAdjustTimelineBtn');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Adjusting...';
            btn.disabled = true;

            try {
                const response = await fetch('auto_adjust_srf_timeline.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ trackid: <?= json_encode((int)$trackid) ?>, model: document.getElementById('ollamaModelSelect').value })
                });
                const data = await response.json();

                if (!data.success) {
                    alert(data.error || 'Auto-adjust failed.');
                    return;
                }

                showSummaryModal(data);
            } catch (error) {
                console.error(error);
                alert('Auto-adjust failed. Please try again.');
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }

        function showSummaryModal(data) {
            const modal = document.getElementById('adjustSummaryModal');
            const content = document.getElementById('summaryContent');

            const modeLabel = data.mode === 'ollama' ? 'Ollama AI' : 'Fallback (PHP)';
            const profileLabel = data.profile.charAt(0).toUpperCase() + data.profile.slice(1);

            let html = `
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4 p-4 bg-gray-50 rounded-lg text-sm">
                    <div><span class="font-semibold">Mode:</span> ${modeLabel}</div>
                    <div><span class="font-semibold">Model:</span> ${data.model || 'N/A'}</div>
                    <div><span class="font-semibold">Profile:</span> ${profileLabel}</div>
                    <div><span class="font-semibold">Anchor:</span> ${data.anchor}</div>
                </div>
            `;

            if (data.aiError) {
                html += `<div class="alert alert-warning mb-4 text-sm">AI error: ${data.aiError} &mdash; fallback used.</div>`;
            }

            const timeline = data.timeline || [];
            const oldValues = data.oldValues || { srfhistory: [], srf_actiontaken: [], srffeedback: [] };

            const historyItems = timeline.filter(t => t.table === 'srfhistory');
            if (historyItems.length) {
                html += buildDiffTable('Receive History', historyItems, oldValues.srfhistory, ['date', 'time']);
            }

            const actionItems = timeline.filter(t => t.table === 'srf_actiontaken');
            if (actionItems.length) {
                html += buildDiffTable('RICTU Staff Actions', actionItems, oldValues.srf_actiontaken, ['date', 'time']);
            }

            const feedbackItems = timeline.filter(t => t.table === 'srffeedback');
            if (feedbackItems.length) {
                html += buildFeedbackDiffTable('Edit Feedback Entries', feedbackItems, oldValues.srffeedback);
            }

            content.innerHTML = html;
            modal.showModal();
        }

        function buildDiffTable(title, newItems, oldItems, fields) {
            const oldMap = {};
            oldItems.forEach(item => { oldMap[item.id] = item; });

            let rows = '';
            newItems.forEach(item => {
                const old = oldMap[item.id] || {};
                fields.forEach(f => {
                    const oldVal = old[f] || '';
                    const newVal = item[f] || '';
                    const changed = oldVal !== newVal;
                    rows += `<tr class="${changed ? 'bg-yellow-50' : ''} border-b border-gray-200">`;
                    rows += `<td class="p-2 font-mono text-center text-sm">${item.id}</td>`;
                    rows += `<td class="p-2 font-medium text-gray-600 text-sm capitalize">${f}</td>`;
                    rows += `<td class="p-2 text-sm ${changed ? 'text-red-500 line-through' : 'text-gray-500'}">${oldVal || '<span class="text-gray-400 italic">blank</span>'}</td>`;
                    rows += `<td class="p-2 text-sm ${changed ? 'text-green-600 font-semibold' : 'text-gray-500'}">${newVal || '<span class="text-gray-400 italic">blank</span>'}</td>`;
                    rows += `</tr>`;
                });
            });

            const changed = Array.from(document.querySelectorAll('#summaryContent .bg-yellow-50')).length > 0;

            return `
                <h4 class="font-bold text-base mt-4 mb-2">${title} <span class="font-normal text-gray-500 text-sm">(${newItems.length} row${newItems.length > 1 ? 's' : ''})</span></h4>
                <div class="overflow-x-auto border rounded-lg">
                    <table class="table table-xs w-full">
                        <thead>
                            <tr class="bg-blue-600 text-white text-sm">
                                <th class="p-2 w-16">ID</th>
                                <th class="p-2 w-20">Field</th>
                                <th class="p-2">Old Value</th>
                                <th class="p-2">New Value</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }

        function buildFeedbackDiffTable(title, newItems, oldItems) {
            const oldMap = {};
            oldItems.forEach(item => { oldMap[item.id] = item; });

            const fieldDefs = [
                { f: 'feedback', label: 'Rating' },
                { f: 'created_at', label: 'Created At' },
                { f: 'date_rated', label: 'Date Rated' },
            ];

            let rows = '';
            newItems.forEach(item => {
                const old = oldMap[item.id] || {};
                fieldDefs.forEach(({ f, label }) => {
                    const oldVal = old[f] || '';
                    const newVal = item[f] || '';
                    const changed = oldVal !== newVal;
                    rows += `<tr class="${changed ? 'bg-yellow-50' : ''} border-b border-gray-200">`;
                    rows += `<td class="p-2 font-mono text-center text-sm">${item.id}</td>`;
                    rows += `<td class="p-2 font-medium text-gray-600 text-sm">${label}</td>`;
                    rows += `<td class="p-2 text-sm ${changed ? 'text-red-500 line-through' : 'text-gray-500'}">${oldVal || '<span class="text-gray-400 italic">blank</span>'}</td>`;
                    rows += `<td class="p-2 text-sm ${changed ? 'text-green-600 font-semibold' : 'text-gray-500'}">${newVal || '<span class="text-gray-400 italic">blank</span>'}</td>`;
                    rows += `</tr>`;
                });
            });

            return `
                <h4 class="font-bold text-base mt-4 mb-2">${title} <span class="font-normal text-gray-500 text-sm">(${newItems.length} row${newItems.length > 1 ? 's' : ''})</span></h4>
                <div class="overflow-x-auto border rounded-lg">
                    <table class="table table-xs w-full">
                        <thead>
                            <tr class="bg-blue-600 text-white text-sm">
                                <th class="p-2 w-16">ID</th>
                                <th class="p-2 w-20">Field</th>
                                <th class="p-2">Old Value</th>
                                <th class="p-2">New Value</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }

        async function saveAllReceive() {
            const saveBtn = document.getElementById('saveAllReceiveBtn');
            const originalText = saveBtn.innerHTML;
            const activeRows = document.querySelectorAll('button[id^="save_btn_"]:not(.hidden)');
            
            if(activeRows.length === 0) {
                alert("No rows are currently in edit mode.");
                return;
            }

            saveBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving All...';
            saveBtn.disabled = true;

            const promises = Array.from(activeRows).map(btn => {
                const id = btn.id.split('_').pop();
                return saveChanges(id); // Re-uses our updated save logic
            });

            try {
                await Promise.all(promises);
            } catch (error) {
                console.error("Error saving some rows:", error);
                alert("Some rows failed to save. Please review them.");
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }

        async function saveAllAction() {
            const saveBtn = document.getElementById('saveAllActionBtn');
            const originalText = saveBtn.innerHTML;
            const activeRows = document.querySelectorAll('button[id^="save_action_btn_"]:not(.hidden)');
            
            if(activeRows.length === 0) {
                alert("No rows are currently in edit mode.");
                return;
            }

            saveBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving All...';
            saveBtn.disabled = true;

            const promises = Array.from(activeRows).map(btn => {
                const id = btn.id.split('_').pop();
                return saveRow(id); // Re-uses our updated save logic
            });

            try {
                await Promise.all(promises);
            } catch (error) {
                console.error("Error saving some rows:", error);
                alert("Some rows failed to save. Please review them.");
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }

        /**
         * Converts 24-hour time string (HH:MM) to 12-hour (hh:mm AM/PM).
         */
        function formatTo12Hour(timeString24) {
            const [hours, minutes] = timeString24.split(':');
            const h = parseInt(hours, 10);
            const ampm = h >= 12 ? 'PM' : 'AM';
            let formattedHours = h % 12;
            formattedHours = formattedHours || 12;
            return `${String(formattedHours).padStart(2, '0')}:${minutes} ${ampm}`;
        }

        /**
         * Converts 12-hour time string (hh:mm AM/PM) to 24-hour (HH:MM:SS) for calculations.
         */
        function formatTo24HourForCalc(timeString12) {
            const [time, modifier] = timeString12.split(' ');
            let [hours, minutes] = time.split(':');
            hours = parseInt(hours, 10);
            if (modifier.toUpperCase() === 'PM' && hours < 12) hours += 12;
            if (modifier.toUpperCase() === 'AM' && hours === 12) hours = 0;
            return `${String(hours).padStart(2, '0')}:${minutes}:00`;
        }

        /**
         * Sets all dates to match the date of the first row.
         */
        function autoAdjustDate(tableId) {
            const rows = document.querySelectorAll(`#${tableId} tbody tr`);
            if (rows.length < 2) return;

            const firstDateInput = rows[0].querySelector('input[id^="date_input_"]');
            const targetDate = firstDateInput.value;
            
            if (!targetDate) {
                alert("Please ensure the first row has a valid date.");
                return;
            }
            
            if (!confirm(`Set all rows to match the first row's date (${targetDate})?`)) return;

            for (let i = 1; i < rows.length; i++) {
                const dateInput = rows[i].querySelector('input[id^="date_input_"]');
                if (dateInput) {
                    dateInput.value = targetDate;
                    const id = dateInput.id.split('_').pop();
                    
                    const editBtn = document.getElementById(`edit_btn_${id}`) || document.querySelector(`.edit-action-btn-${id}`);
                    if (editBtn && !editBtn.classList.contains('hidden')) {
                        tableId === 'receiveHistoryTable' ? toggleEdit(id) : editRow(id);
                    }
                }
            }
        }

        function autoAdjustTime(tableId) {
            const incrementInput = prompt("Enter the time increment in minutes:", "32");
            if (incrementInput === null) return;

            const minutes = parseInt(incrementInput, 10);
            if (isNaN(minutes) || minutes <= 0) {
                alert("Please enter a positive number for minutes.");
                return;
            }
            
            if (!confirm(`Adjust times by ${minutes} minutes?`)) return;

            const rows = document.querySelectorAll(`#${tableId} tbody tr`);
            if (rows.length < 2) return;

            const firstTimeInput = rows[0].querySelector('input[id^="time_input_"]');
            const initialTime24 = formatTo24HourForCalc(firstTimeInput.value);
            
            const timeParts = initialTime24.split(':');
            let currentTime = new Date();
            currentTime.setHours(parseInt(timeParts[0], 10), parseInt(timeParts[1], 10), 0);
            
            for (let i = 1; i < rows.length; i++) {
                currentTime.setMinutes(currentTime.getMinutes() + minutes);
                const newHours = String(currentTime.getHours()).padStart(2, '0');
                const newMinutes = String(currentTime.getMinutes()).padStart(2, '0');
                const newTimeString12 = formatTo12Hour(`${newHours}:${newMinutes}`);

                const timeInput = rows[i].querySelector('input[id^="time_input_"]');
                if (timeInput) {
                    timeInput.value = newTimeString12;
                    const id = timeInput.id.split('_').pop();
                    const editBtn = document.getElementById(`edit_btn_${id}`) || document.querySelector(`.edit-action-btn-${id}`);
                    if (editBtn && !editBtn.classList.contains('hidden')) {
                        tableId === 'receiveHistoryTable' ? toggleEdit(id) : editRow(id);
                    }
                }
            }
            alert('Times have been adjusted.');
        }

        function autoAdjustReceiveTime() { autoAdjustTime('receiveHistoryTable'); }
        function autoAdjustActionTime() { autoAdjustTime('staffActionsTable'); }
        function autoAdjustReceiveDate() { autoAdjustDate('receiveHistoryTable'); }
        function autoAdjustActionDate() { autoAdjustDate('staffActionsTable'); }
    </script>
</body>
</html>
