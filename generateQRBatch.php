<?php
require_once 'connect.php';
require_once 'session_checker.php';

$ids = [];
if (isset($_GET['ids'])) {
    $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $_GET['ids'])), function ($id) {
        return $id > 0;
    })));
}

if (empty($ids)) {
    echo 'No inventory records selected.';
    exit;
}

$allowedPerPage = [4, 6, 8, 10, 12, 15];
$perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 10;
if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 10;
}

$office = $_SESSION['OfficeSRF'] ?? '';
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$query = "SELECT employeeName, equipmentType, yearAcquired, brand, serialNumber, propertyNumber, officeDivision, id
          FROM inv_inventory
          WHERE Office = ? AND id IN ($placeholders)
          ORDER BY FIELD(id, $placeholders)";

$params = array_merge([$office], $ids, $ids);
$types = 's' . str_repeat('i', count($ids) * 2);
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo 'Unable to prepare QR batch.';
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$records = [];

while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

$stmt->close();

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . ($basePath === '' ? '' : $basePath);

function formatQrSerialNumber($serialNumber) {
    return trim(preg_replace('/^SN\s*:\s*/i', '', (string) $serialNumber));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printable Equipment QR Stickers</title>
    <link rel="shortcut icon" type="image/x-icon" href="icon/amsos.ico">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f2f4f7;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            padding: 12px;
            background: #1f2937;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .toolbar button {
            border: 0;
            border-radius: 5px;
            padding: 8px 14px;
            cursor: pointer;
            font-weight: 700;
        }

        .print-button {
            background: #16a34a;
            color: #fff;
        }

        .back-button {
            background: #e5e7eb;
            color: #111;
        }

        .page-wrap {
            padding: 12px 0;
        }

        .qr-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 12px;
            padding: 8mm;
            background: #fff;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(5, 1fr);
            gap: 3mm;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.15);
            page-break-after: always;
        }

        .qr-per-page-4 .qr-page {
            grid-template-rows: repeat(2, 1fr);
        }

        .qr-per-page-6 .qr-page {
            grid-template-rows: repeat(3, 1fr);
        }

        .qr-per-page-8 .qr-page {
            grid-template-rows: repeat(4, 1fr);
        }

        .qr-per-page-10 .qr-page {
            grid-template-rows: repeat(5, 1fr);
        }

        .qr-per-page-12 .qr-page {
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(4, 1fr);
        }

        .qr-per-page-15 .qr-page {
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(5, 1fr);
            gap: 2mm;
        }

        .sticker {
            border: 1px dashed #555;
            border-radius: 3mm;
            padding: 2.4mm;
            overflow: hidden;
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 1mm;
        }

        .sticker-header {
            display: flex;
            align-items: center;
            gap: 2mm;
            border-bottom: 1px solid #ddd;
            padding-bottom: 1mm;
            min-height: 8mm;
        }

        .sticker-logo {
            width: 24mm;
            max-height: 7mm;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .sticker-title {
            font-size: 11pt;
            line-height: 1.05;
            font-weight: 700;
            text-transform: uppercase;
        }

        .sticker-body {
            display: grid;
            grid-template-columns: 28mm 1fr;
            gap: 2mm;
            align-items: center;
        }

        .qr-code {
            width: 28mm;
            height: 28mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-code canvas,
        .qr-code img {
            width: 28mm !important;
            height: 28mm !important;
        }

        .sticker-details {
            min-width: 0;
            font-size: 9.6pt;
            line-height: 1.28;
        }

        .detail-line {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.8mm;
        }

        .property-number-line {
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            overflow-wrap: anywhere;
        }

        .property-number-line strong {
            display: block;
        }

        .detail-line strong {
            font-weight: 700;
        }

        .qr-per-page-12 .sticker,
        .qr-per-page-15 .sticker {
            padding: 1.8mm;
        }

        .qr-per-page-12 .sticker-header,
        .qr-per-page-15 .sticker-header {
            gap: 1mm;
            min-height: 7mm;
        }

        .qr-per-page-12 .sticker-logo,
        .qr-per-page-15 .sticker-logo {
            width: 16mm;
            max-height: 6mm;
        }

        .qr-per-page-12 .sticker-title,
        .qr-per-page-15 .sticker-title {
            font-size: 8pt;
        }

        .qr-per-page-12 .sticker-body,
        .qr-per-page-15 .sticker-body {
            grid-template-columns: 22mm 1fr;
            gap: 1mm;
        }

        .qr-per-page-12 .qr-code,
        .qr-per-page-12 .qr-code canvas,
        .qr-per-page-12 .qr-code img,
        .qr-per-page-15 .qr-code,
        .qr-per-page-15 .qr-code canvas,
        .qr-per-page-15 .qr-code img {
            width: 22mm !important;
            height: 22mm !important;
        }

        .qr-per-page-12 .sticker-details,
        .qr-per-page-15 .sticker-details {
            font-size: 7.2pt;
            line-height: 1.15;
        }

        .qr-per-page-12 .detail-line,
        .qr-per-page-15 .detail-line {
            margin-bottom: 0.45mm;
        }

        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .page-wrap {
                padding: 0;
            }

            .qr-page {
                width: auto;
                min-height: auto;
                height: calc(297mm - 16mm);
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .qr-page:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body class="qr-per-page-<?php echo $perPage; ?>">
    <div class="toolbar">
        <span><?php echo count($records); ?> QR sticker(s), <?php echo $perPage; ?> per bond paper</span>
        <button type="button" class="print-button" onclick="window.print()">Print All</button>
        <button type="button" class="back-button" onclick="window.close(); if (!window.closed) history.back();">Back</button>
    </div>

    <div class="page-wrap">
        <?php foreach (array_chunk($records, $perPage) as $recordChunk): ?>
            <section class="qr-page">
                <?php foreach ($recordChunk as $row): ?>
                    <?php $detailsUrl = $baseUrl . '/details.php?id=' . (int) $row['id']; ?>
                    <article class="sticker">
                        <div class="sticker-header">
                            <img src="logo/denr.png" alt="DENR Logo" class="sticker-logo">
                            <div class="sticker-title">ICT-AMSOS Equipment QR</div>
                        </div>
                        <div class="sticker-body">
                            <div class="qr-code" data-qr="<?php echo htmlspecialchars($detailsUrl); ?>"></div>
                            <div class="sticker-details">
                                <div class="detail-line"><strong>SN:</strong> <?php echo htmlspecialchars(formatQrSerialNumber($row['serialNumber'])); ?></div>
                                <div class="detail-line property-number-line"><strong>Property Number:</strong> <?php echo htmlspecialchars($row['propertyNumber']); ?></div>
                                <div class="detail-line"><strong>Division:</strong> <?php echo htmlspecialchars($row['officeDivision']); ?></div>
                                <div class="detail-line"><strong>Name:</strong> <strong><?php echo htmlspecialchars($row['employeeName']); ?></strong></div>
                                <div class="detail-line"><strong>Type:</strong> <?php echo htmlspecialchars($row['equipmentType']); ?></div>
                                <div class="detail-line"><strong>Brand:</strong> <?php echo htmlspecialchars($row['brand']); ?></div>
                                <div class="detail-line"><strong>Year:</strong> <?php echo htmlspecialchars($row['yearAcquired']); ?></div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.qr-code').forEach(function (container) {
            new QRCode(container, {
                text: container.dataset.qr,
                width: 106,
                height: 106,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    });
    </script>
</body>
</html>
