<?php

function repairHistoryFetchInventory($conn, $inventoryId) {
    $inventoryId = intval($inventoryId);
    if ($inventoryId <= 0) {
        return null;
    }

    $stmt = $conn->prepare("SELECT id, propertyNumber, actualUser, equipmentType, brand, specifications FROM inv_inventory WHERE id = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $inventoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $inventory = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $inventory;
}

function repairHistoryIsRepairEquipment($equipmentType) {
    $equipmentType = strtolower((string) $equipmentType);
    $keywords = ['desktop', 'computer', 'laptop', 'printer', 'monitor', 'scanner', 'cpu'];

    foreach ($keywords as $keyword) {
        if (strpos($equipmentType, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

function repairHistoryInsertSrfRepair($conn, $srfId, $inventoryId, $requestorName, $requestType, $issueDescription, $status, $dateRecorded) {
    $inventory = repairHistoryFetchInventory($conn, $inventoryId);
    if (!$inventory || !repairHistoryIsRepairEquipment($inventory['equipmentType'])) {
        return false;
    }

    $recordType = 'SRF Repair';
    $sourceId = intval($srfId);
    $srfId = intval($srfId);
    $preventiveId = null;
    $inventoryId = intval($inventory['id']);
    $propertyNumber = $inventory['propertyNumber'] ?? '';
    $actualUser = $inventory['actualUser'] ?? '';
    $equipmentType = $inventory['equipmentType'] ?? '';
    $brand = $inventory['brand'] ?? '';
    $description = $inventory['specifications'] ?? '';
    $actionStaff = null;
    $actionTaken = null;
    $timeRecorded = date('h:i:s A');

    $stmt = $conn->prepare("INSERT INTO srf_repair_history
        (record_type, source_id, srf_id, preventive_id, inventory_id, property_number, actual_user, equipment_type, brand, description, requestor_name, request_type, issue_description, status, action_staff, action_taken, date_recorded, time_recorded)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            inventory_id = VALUES(inventory_id),
            property_number = VALUES(property_number),
            actual_user = VALUES(actual_user),
            equipment_type = VALUES(equipment_type),
            brand = VALUES(brand),
            description = VALUES(description),
            requestor_name = VALUES(requestor_name),
            request_type = VALUES(request_type),
            issue_description = VALUES(issue_description),
            status = VALUES(status),
            date_recorded = VALUES(date_recorded),
            time_recorded = VALUES(time_recorded)");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "siiiisssssssssssss",
        $recordType,
        $sourceId,
        $srfId,
        $preventiveId,
        $inventoryId,
        $propertyNumber,
        $actualUser,
        $equipmentType,
        $brand,
        $description,
        $requestorName,
        $requestType,
        $issueDescription,
        $status,
        $actionStaff,
        $actionTaken,
        $dateRecorded,
        $timeRecorded
    );

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function repairHistoryUpdateSrfRepairAction($conn, $srfId, $status, $actionStaff, $actionTaken, $dateRecorded = null, $timeRecorded = null) {
    $srfId = intval($srfId);
    if ($srfId <= 0) {
        return false;
    }

    $dateRecorded = $dateRecorded ?: date('Y-m-d');
    $timeRecorded = $timeRecorded ?: date('h:i:s A');
    $recordType = 'SRF Repair';

    $stmt = $conn->prepare("UPDATE srf_repair_history
        SET status = ?, action_staff = ?, action_taken = ?, date_recorded = ?, time_recorded = ?
        WHERE srf_id = ? AND record_type = ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("sssssis", $status, $actionStaff, $actionTaken, $dateRecorded, $timeRecorded, $srfId, $recordType);
    $success = $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();

    if ($success && $affectedRows === 0) {
        $srfStmt = $conn->prepare("SELECT id, name, requestType, description, status, date, equipment_id FROM srf WHERE id = ? LIMIT 1");
        if ($srfStmt) {
            $srfStmt->bind_param("i", $srfId);
            $srfStmt->execute();
            $srfResult = $srfStmt->get_result();
            $srf = $srfResult ? $srfResult->fetch_assoc() : null;
            $srfStmt->close();

            if ($srf && intval($srf['equipment_id']) > 0) {
                repairHistoryInsertSrfRepair(
                    $conn,
                    $srf['id'],
                    $srf['equipment_id'],
                    $srf['name'],
                    $srf['requestType'],
                    $srf['description'],
                    $srf['status'],
                    $srf['date']
                );

                $retryStmt = $conn->prepare("UPDATE srf_repair_history
                    SET status = ?, action_staff = ?, action_taken = ?, date_recorded = ?, time_recorded = ?
                    WHERE srf_id = ? AND record_type = ?");
                if ($retryStmt) {
                    $retryStmt->bind_param("sssssis", $status, $actionStaff, $actionTaken, $dateRecorded, $timeRecorded, $srfId, $recordType);
                    $success = $retryStmt->execute();
                    $retryStmt->close();
                }
            }
        }
    }

    return $success;
}

function repairHistoryInsertPreventiveMaintenance($conn, $inventoryId, $issueDescription, $status = 'Completed', $actionStaff = null, $actionTaken = null) {
    $inventory = repairHistoryFetchInventory($conn, $inventoryId);
    if (!$inventory || !repairHistoryIsRepairEquipment($inventory['equipmentType'])) {
        return false;
    }

    $recordType = 'Preventive Maintenance';
    $sourceId = intval($inventoryId);
    $srfId = null;
    $preventiveId = intval($inventoryId);
    $inventoryId = intval($inventory['id']);
    $propertyNumber = $inventory['propertyNumber'] ?? '';
    $actualUser = $inventory['actualUser'] ?? '';
    $equipmentType = $inventory['equipmentType'] ?? '';
    $brand = $inventory['brand'] ?? '';
    $description = $inventory['specifications'] ?? '';
    $requestorName = $actionStaff ?: '';
    $requestType = 'Preventive Maintenance';
    $dateRecorded = date('Y-m-d');
    $timeRecorded = date('h:i:s A');

    $stmt = $conn->prepare("INSERT INTO srf_repair_history
        (record_type, source_id, srf_id, preventive_id, inventory_id, property_number, actual_user, equipment_type, brand, description, requestor_name, request_type, issue_description, status, action_staff, action_taken, date_recorded, time_recorded)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "siiiisssssssssssss",
        $recordType,
        $sourceId,
        $srfId,
        $preventiveId,
        $inventoryId,
        $propertyNumber,
        $actualUser,
        $equipmentType,
        $brand,
        $description,
        $requestorName,
        $requestType,
        $issueDescription,
        $status,
        $actionStaff,
        $actionTaken,
        $dateRecorded,
        $timeRecorded
    );

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

?>
