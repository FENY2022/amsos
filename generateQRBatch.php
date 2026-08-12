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

$office = $_SESSION['OfficeSRF'] ?? '';
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$query = "SELECT employeeName, equipmentType, yearAcquired, brand, amount, propertyNumber, id
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
            font-size: 7.5pt;
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
            font-size: 7pt;
            line-height: 1.2;
        }

        .detail-line {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.8mm;
        }

        .detail-line strong {
            font-weight: 700;
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
<body>
    <div class="toolbar">
        <span><?php echo count($records); ?> QR sticker(s), 10 per A4 page</span>
        <button type="button" class="print-button" onclick="window.print()">Print All</button>
        <button type="button" class="back-button" onclick="window.close(); if (!window.closed) history.back();">Back</button>
    </div>

    <div class="page-wrap">
        <?php foreach (array_chunk($records, 10) as $recordChunk): ?>
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
                                <div class="detail-line"><strong>ID:</strong> <?php echo htmlspecialchars($row['id']); ?></div>
                                <div class="detail-line"><strong>PN:</strong> <?php echo htmlspecialchars($row['propertyNumber']); ?></div>
                                <div class="detail-line"><strong>Name:</strong> <?php echo htmlspecialchars($row['employeeName']); ?></div>
                                <div class="detail-line"><strong>Type:</strong> <?php echo htmlspecialchars($row['equipmentType']); ?></div>
                                <div class="detail-line"><strong>Brand:</strong> <?php echo htmlspecialchars($row['brand']); ?></div>
                                <div class="detail-line"><strong>Year:</strong> <?php echo htmlspecialchars($row['yearAcquired']); ?></div>
                                <div class="detail-line"><strong>Amount:</strong> <?php echo htmlspecialchars($row['amount']); ?></div>
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
