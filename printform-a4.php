<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid SRF ID.';
    exit;
}

$allowedSizes = ['small', 'medium', 'large', 'xl', 'xxl', 'xxxl'];
$selectedSize = strtolower((string)($_GET['print_size'] ?? 'medium'));
if (!in_array($selectedSize, $allowedSizes, true)) {
    $selectedSize = 'medium';
}

$printUrl = 'printform-request.php?id=' . urlencode((string) $id) . '&print_a4=1&print_size=' . urlencode($selectedSize);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRF A4 Print #<?php echo htmlspecialchars((string) $id); ?></title>
    <link rel="icon" type="image/x-icon" href="icon/amsos.ico">
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

        .toolbar button,
        .toolbar a {
            border: 0;
            border-radius: 5px;
            padding: 8px 14px;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            line-height: 1.2;
            text-decoration: none;
        }

        .zoom-control {
            display: inline-flex;
            align-items: center;
            gap: 0;
            overflow: hidden;
            border: 1px solid #9ca3af;
            border-radius: 6px;
            background: #fff;
        }

        .zoom-control button {
            min-width: 42px;
            border-radius: 0;
            padding: 8px 12px;
            background: #fff;
            color: #34495e;
            border-right: 1px solid #d1d5db;
        }

        .zoom-control button:last-child {
            border-right: 0;
            border-left: 1px solid #d1d5db;
        }

        .zoom-control button:disabled {
            color: #9ca3af;
            cursor: not-allowed;
        }

        .zoom-label {
            min-width: 126px;
            padding: 8px 12px;
            color: #34495e;
            text-align: center;
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

        .a4-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 12px;
            padding: 8mm;
            background: #fff;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.15);
        }

        .a4-frame {
            display: block;
            width: 100%;
            height: calc(297mm - 16mm);
            border: 0;
            background: #fff;
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

            .a4-page {
                width: auto;
                min-height: auto;
                height: calc(297mm - 16mm);
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .a4-frame {
                height: calc(297mm - 16mm);
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span>SRF #<?php echo htmlspecialchars((string) $id); ?> A4 print preview</span>
        <div class="zoom-control" aria-label="Page size zoom">
            <button type="button" id="zoomOutBtn" aria-label="Zoom out">-</button>
            <span class="zoom-label" id="zoomLabel">Size: Medium</span>
            <button type="button" id="zoomInBtn" aria-label="Zoom in">+</button>
        </div>
        <button type="button" class="print-button" onclick="printSrfFrame()">Print</button>
        <a class="back-button" href="mainmenu.php?dir=printform&id=<?php echo urlencode((string) $id); ?>">Back</a>
    </div>

    <div class="page-wrap">
        <section class="a4-page">
            <iframe id="srfPrintFrame" class="a4-frame" src="<?php echo htmlspecialchars($printUrl, ENT_QUOTES, 'UTF-8'); ?>"></iframe>
        </section>
    </div>

    <script>
        const srfId = <?php echo json_encode((string) $id); ?>;
        const sizes = ['small', 'medium', 'large', 'xl', 'xxl', 'xxxl'];
        const sizeLabels = {
            small: 'Size: Small',
            medium: 'Size: Medium',
            large: 'Size: Large',
            xl: 'Size: XL',
            xxl: 'Size: XXL',
            xxxl: 'Size: XXXL'
        };
        let currentSize = <?php echo json_encode($selectedSize); ?>;
        const zoomOutBtn = document.getElementById('zoomOutBtn');
        const zoomInBtn = document.getElementById('zoomInBtn');
        const zoomLabel = document.getElementById('zoomLabel');

        function frameUrl(size) {
            return 'printform-request.php?id=' + encodeURIComponent(srfId) + '&print_a4=1&print_size=' + encodeURIComponent(size || 'medium');
        }

        function setPageSize(size) {
            if (sizes.indexOf(size) === -1) {
                size = 'medium';
            }

            currentSize = size;
            const currentIndex = sizes.indexOf(currentSize);

            if (zoomLabel) {
                zoomLabel.textContent = sizeLabels[currentSize] || 'Size: Medium';
            }
            if (zoomOutBtn) {
                zoomOutBtn.disabled = currentIndex <= 0;
            }
            if (zoomInBtn) {
                zoomInBtn.disabled = currentIndex >= sizes.length - 1;
            }

            const frame = document.getElementById('srfPrintFrame');
            if (frame) {
                frame.src = frameUrl(currentSize);
            }

            if (window.history && window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.set('print_size', currentSize);
                window.history.replaceState(null, '', url.toString());
            }
        }

        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', function () {
                const currentIndex = sizes.indexOf(currentSize);
                if (currentIndex > 0) {
                    setPageSize(sizes[currentIndex - 1]);
                }
            });
        }

        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', function () {
                const currentIndex = sizes.indexOf(currentSize);
                if (currentIndex < sizes.length - 1) {
                    setPageSize(sizes[currentIndex + 1]);
                }
            });
        }

        setPageSize(currentSize);

        function printSrfFrame() {
            const frame = document.getElementById('srfPrintFrame');
            if (!frame || !frame.contentWindow) {
                window.print();
                return;
            }

            frame.contentWindow.focus();
            frame.contentWindow.print();
        }
    </script>
</body>
</html>
