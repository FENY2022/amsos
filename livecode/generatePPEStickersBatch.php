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
$perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 8;
if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 8;
}

$office = $_SESSION['OfficeSRF'] ?? '';
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$query = "SELECT id, employeeName, equipmentType, yearAcquired, brand, specifications, computer_specs, softwareInstalled,
                 serialNumber, propertyNumber, officeDivision, amount
          FROM inv_inventory
          WHERE Office = ? AND id IN ($placeholders)
          ORDER BY FIELD(id, $placeholders)";

$params = array_merge([$office], $ids, $ids);
$types = 's' . str_repeat('i', count($ids) * 2);
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo 'Unable to prepare PPE sticker batch.';
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

function ppeValue($value) {
    return htmlspecialchars((string) ($value ?? ''));
}

function ppeAmount($amount) {
    if ($amount === null || $amount === '') {
        return '';
    }

    $numericAmount = (float) str_replace(',', '', (string) $amount);
    if ($numericAmount <= 0) {
        return ppeValue($amount);
    }

    return 'Php ' . number_format($numericAmount, 2);
}

function ppeArticleDescription($row) {
    $titleParts = array_filter([$row['equipmentType'] ?? '', $row['brand'] ?? ''], function ($value) {
        return trim((string) $value) !== '';
    });

    $detailParts = array_filter([$row['computer_specs'] ?? '', $row['specifications'] ?? '', $row['softwareInstalled'] ?? ''], function ($value) {
        return trim((string) $value) !== '';
    });

    $description = implode(' - ', $titleParts);
    if (!empty($detailParts)) {
        $description .= ($description !== '' ? "\n\n" : '') . implode("\n", $detailParts);
    }

    return $description;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printable PPE Stickers</title>
    <link rel="shortcut icon" type="image/x-icon" href="icon/amsos.ico">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f2f4f7;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
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

        .ppe-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 12px;
            padding: 8mm;
            background: #fff;
            column-count: 2;
            column-gap: 4mm;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.15);
            page-break-after: always;
        }

        .ppe-sticker {
            width: 100%;
            min-height: 65mm;
            margin: 0 0 3mm;
            background: #d60000;
            color: #fff;
            display: grid;
            grid-template-columns: 36mm 1fr;
            grid-template-rows: 14mm auto;
            border: 1px solid #d60000;
            font-size: 7pt;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .agency-block {
            display: grid;
            grid-template-columns: 12mm 1fr;
            gap: 1mm;
            align-items: start;
            padding: 0.8mm;
            border-right: 1px solid #b80000;
        }

        .agency-block img {
            width: 11mm;
            height: 11mm;
            object-fit: contain;
            background: #fff;
            border: 1px solid #111;
        }

        .agency-text {
            font-weight: 700;
            line-height: 1.25;
            font-size: 6.5pt;
            text-transform: uppercase;
            padding-bottom: 1.5mm;
        }

        .property-title {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 700;
            line-height: 1.45;
            text-transform: uppercase;
        }

        .ppe-fields {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 36mm 1fr;
            align-content: start;
        }

        .ppe-label,
        .ppe-value {
            min-height: 4.4mm;
            border-top: 1px solid #d60000;
            line-height: 1.12;
        }

        .ppe-label {
            padding: 0.55mm 0.7mm;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
        }

        .ppe-value {
            padding: 0.5mm 0.7mm;
            background: #fff;
            color: #000;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .article-label,
        .article-value {
            min-height: 21mm;
        }

        .ppe-value[contenteditable="true"] {
            outline: 1px dashed transparent;
        }

        .ppe-value[contenteditable="true"]:focus {
            outline-color: #ffd966;
            background: #fffbea;
        }

        .ppe-per-page-4 .ppe-sticker {
            min-height: 128mm;
            margin-bottom: 4mm;
            grid-template-columns: 39mm 1fr;
            grid-template-rows: 18mm auto;
            font-size: 8pt;
        }

        .ppe-per-page-4 .agency-block {
            grid-template-columns: 14mm 1fr;
        }

        .ppe-per-page-4 .agency-block img {
            width: 13mm;
            height: 13mm;
        }

        .ppe-per-page-4 .agency-text {
            font-size: 7pt;
        }

        .ppe-per-page-4 .ppe-fields {
            grid-template-columns: 39mm 1fr;
        }

        .ppe-per-page-4 .ppe-label,
        .ppe-per-page-4 .ppe-value {
            min-height: 7mm;
        }

        .ppe-per-page-4 .article-label,
        .ppe-per-page-4 .article-value {
            min-height: 49mm;
        }

        .ppe-per-page-6 .ppe-sticker {
            min-height: 84mm;
            margin-bottom: 3mm;
            grid-template-rows: 14mm auto;
        }

        .ppe-per-page-6 .article-label,
        .ppe-per-page-6 .article-value {
            min-height: 38mm;
        }

        .ppe-per-page-10 .ppe-sticker {
            min-height: 53mm;
            margin-bottom: 2mm;
            grid-template-columns: 31mm 1fr;
            grid-template-rows: 12mm auto;
            font-size: 6pt;
        }

        .ppe-per-page-10 .agency-block {
            grid-template-columns: 10mm 1fr;
            gap: 0.7mm;
            padding: 0.6mm;
        }

        .ppe-per-page-10 .agency-block img {
            width: 9mm;
            height: 9mm;
        }

        .ppe-per-page-10 .agency-text {
            line-height: 1.15;
            font-size: 5.6pt;
            padding-bottom: 0.8mm;
        }

        .ppe-per-page-10 .property-title {
            line-height: 1.25;
        }

        .ppe-per-page-10 .ppe-fields {
            grid-template-columns: 31mm 1fr;
        }

        .ppe-per-page-10 .ppe-label,
        .ppe-per-page-10 .ppe-value {
            min-height: 3.6mm;
            line-height: 1.05;
        }

        .ppe-per-page-10 .ppe-label {
            padding: 0.4mm 0.55mm;
        }

        .ppe-per-page-10 .ppe-value {
            padding: 0.35mm 0.55mm;
        }

        .ppe-per-page-10 .article-label,
        .ppe-per-page-10 .article-value {
            min-height: 16mm;
        }

        .ppe-per-page-12 .ppe-sticker,
        .ppe-per-page-15 .ppe-sticker {
            min-height: 44mm;
            margin-bottom: 2mm;
            grid-template-columns: 29mm 1fr;
            grid-template-rows: 10mm auto;
            font-size: 5.5pt;
        }

        .ppe-per-page-15 .ppe-sticker {
            min-height: 35mm;
            margin-bottom: 1.6mm;
            grid-template-columns: 26mm 1fr;
            grid-template-rows: 8.5mm auto;
            font-size: 4.8pt;
        }

        .ppe-per-page-12 .agency-block,
        .ppe-per-page-15 .agency-block {
            grid-template-columns: 8mm 1fr;
            gap: 0.5mm;
            padding: 0.45mm;
        }

        .ppe-per-page-12 .agency-block img,
        .ppe-per-page-15 .agency-block img {
            width: 7.5mm;
            height: 7.5mm;
        }

        .ppe-per-page-12 .agency-text,
        .ppe-per-page-15 .agency-text {
            line-height: 1.08;
            font-size: 4.7pt;
            padding-bottom: 0.5mm;
        }

        .ppe-per-page-15 .agency-text {
            font-size: 4.1pt;
        }

        .ppe-per-page-12 .property-title,
        .ppe-per-page-15 .property-title {
            line-height: 1.12;
        }

        .ppe-per-page-12 .ppe-fields {
            grid-template-columns: 29mm 1fr;
        }

        .ppe-per-page-15 .ppe-fields {
            grid-template-columns: 26mm 1fr;
        }

        .ppe-per-page-12 .ppe-label,
        .ppe-per-page-12 .ppe-value,
        .ppe-per-page-15 .ppe-label,
        .ppe-per-page-15 .ppe-value {
            min-height: 2.6mm;
            line-height: 1;
        }

        .ppe-per-page-12 .ppe-label,
        .ppe-per-page-12 .ppe-value {
            padding: 0.25mm 0.4mm;
        }

        .ppe-per-page-15 .ppe-label,
        .ppe-per-page-15 .ppe-value {
            padding: 0.18mm 0.32mm;
        }

        .ppe-per-page-12 .article-label,
        .ppe-per-page-12 .article-value {
            min-height: 12mm;
        }

        .ppe-per-page-15 .article-label,
        .ppe-per-page-15 .article-value {
            min-height: 9mm;
        }

        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        @media print {
            html,
            body {
                width: 210mm;
                min-height: 297mm;
                background: #fff;
                overflow: visible;
            }

            .toolbar {
                display: none;
            }

            .page-wrap {
                padding: 0;
            }

            .ppe-page {
                width: 194mm;
                min-height: 281mm;
                height: calc(297mm - 16mm);
                margin: 0;
                padding: 0;
                box-shadow: none;
                overflow: hidden;
            }

            .ppe-page:last-child {
                page-break-after: auto;
            }

            .ppe-value[contenteditable="true"]:focus {
                outline: none;
                background: #fff;
            }
        }
    </style>
</head>
<body class="ppe-per-page-<?php echo $perPage; ?>">
    <div class="toolbar">
        <span><?php echo count($records); ?> editable PPE sticker(s), <?php echo $perPage; ?> per bond paper</span>
        <span>Click any white field to edit before printing.</span>
        <button type="button" class="print-button" onclick="printPpeStickers()">Print All</button>
        <button type="button" class="back-button" onclick="window.close(); if (!window.closed) history.back();">Back</button>
    </div>

    <div class="page-wrap">
        <?php foreach (array_chunk($records, $perPage) as $recordChunk): ?>
            <section class="ppe-page">
                <?php foreach ($recordChunk as $row): ?>
                    <article class="ppe-sticker">
                        <div class="agency-block">
                            <img src="logo/denrlogo.png" alt="DENR Logo">
                            <div class="agency-text">Department of Environment<br>and Natural Resources</div>
                        </div>
                        <div class="property-title">RP Government<br>Property</div>

                        <div class="ppe-fields">
                            <div class="ppe-label">Unit / Office :</div>
                            <div class="ppe-value" contenteditable="true"><?php echo ppeValue($row['officeDivision']); ?></div>

                            <div class="ppe-label article-label">Article Description</div>
                            <div class="ppe-value article-value" contenteditable="true"><?php echo ppeValue(ppeArticleDescription($row)); ?></div>

                            <div class="ppe-label">Property No. :</div>
                            <div class="ppe-value" contenteditable="true"><?php echo ppeValue($row['propertyNumber']); ?></div>

                            <div class="ppe-label">Serial No. :</div>
                            <div class="ppe-value" contenteditable="true"><?php echo ppeValue($row['serialNumber']); ?></div>

                            <div class="ppe-label">Date Acquired :</div>
                            <div class="ppe-value" contenteditable="true"><?php echo ppeValue($row['yearAcquired']); ?></div>

                            <div class="ppe-label">Purchased From :</div>
                            <div class="ppe-value" contenteditable="true"></div>

                            <div class="ppe-label">Amount :</div>
                            <div class="ppe-value" contenteditable="true"><?php echo ppeAmount($row['amount']); ?></div>

                            <div class="ppe-label">Issued To :</div>
                            <div class="ppe-value" contenteditable="true"><?php echo ppeValue($row['employeeName']); ?></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>

    <script>
    function printPpeStickers() {
        window.print();
    }
    </script>
</body>
</html>
