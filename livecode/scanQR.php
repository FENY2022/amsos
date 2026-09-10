<script src="https://cdn.jsdelivr.net/npm/html5-qrcode/minified/html5-qrcode.min.js"></script>

<style>
    .scanqr-page {
        min-height: calc(100vh - 40px);
        padding: 24px;
        border-radius: 28px;
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.22), transparent 34%),
            radial-gradient(circle at bottom right, rgba(20, 184, 166, 0.24), transparent 30%),
            linear-gradient(135deg, #eef5ff 0%, #f8fafc 48%, #ecfeff 100%);
        color: #0f172a;
    }

    .scanqr-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 22px;
    }

    .scanqr-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        margin-bottom: 12px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .scanqr-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(2rem, 4vw, 3.5rem);
        font-weight: 900;
        letter-spacing: -0.055em;
        line-height: 0.95;
    }

    .scanqr-subtitle {
        max-width: 660px;
        margin: 14px 0 0;
        color: #475569;
        font-size: 1.02rem;
        line-height: 1.7;
    }

    .scanqr-status-card {
        min-width: 230px;
        padding: 18px;
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.78);
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.09);
        backdrop-filter: blur(16px);
    }

    .scanqr-status-label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .scanqr-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 10px 13px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-weight: 800;
    }

    .scanqr-status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 0 6px rgba(22, 101, 52, 0.13);
    }

    .scanqr-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.08fr) minmax(320px, 0.92fr);
        gap: 22px;
        align-items: start;
    }

    .scanqr-panel,
    .scanqr-result-card,
    .scanqr-tips {
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.86);
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.11);
        backdrop-filter: blur(18px);
    }

    .scanqr-panel {
        overflow: hidden;
    }

    .scanqr-panel-header,
    .scanqr-result-header,
    .scanqr-tips {
        padding: 20px;
    }

    .scanqr-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.9);
    }

    .scanqr-panel-title,
    .scanqr-result-title,
    .scanqr-tips-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 900;
    }

    .scanqr-panel-copy,
    .scanqr-result-copy {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 0.92rem;
    }

    .scanqr-camera-wrap {
        position: relative;
        padding: 18px;
        background:
            linear-gradient(145deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.95)),
            radial-gradient(circle at center, rgba(59, 130, 246, 0.24), transparent 58%);
    }

    .scanqr-reader-shell {
        position: relative;
        min-height: 460px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 24px;
        background: #020617;
    }

    #reader {
        position: relative;
        z-index: 1;
        width: 100%;
        min-height: 460px;
        color: #ffffff;
    }

    #reader video {
        width: 100% !important;
        min-height: 460px;
        object-fit: cover;
    }

    #reader__scan_region img {
        display: none;
    }

    #reader__dashboard_section_swaplink,
    #reader__dashboard_section_csr button,
    #reader__dashboard_section_fsr button {
        border: 0 !important;
        border-radius: 999px !important;
        background: #2563eb !important;
        color: #ffffff !important;
        font-weight: 800 !important;
    }

    .scanqr-frame {
        position: absolute;
        inset: 13%;
        z-index: 2;
        pointer-events: none;
        border-radius: 28px;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.28), 0 0 0 999px rgba(2, 6, 23, 0.32);
    }

    .scanqr-frame::before,
    .scanqr-frame::after {
        content: "";
        position: absolute;
        left: 10%;
        right: 10%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #22d3ee, #60a5fa, transparent);
        box-shadow: 0 0 24px rgba(34, 211, 238, 0.9);
        animation: scanqrSweep 2.4s ease-in-out infinite;
    }

    .scanqr-frame::before {
        top: 18%;
    }

    .scanqr-frame::after {
        bottom: 18%;
        animation-delay: 1.2s;
    }

    .scanqr-corner {
        position: absolute;
        width: 42px;
        height: 42px;
        border-color: #67e8f9;
        border-style: solid;
        filter: drop-shadow(0 0 8px rgba(103, 232, 249, 0.75));
    }

    .scanqr-corner-tl {
        top: 0;
        left: 0;
        border-width: 4px 0 0 4px;
        border-top-left-radius: 18px;
    }

    .scanqr-corner-tr {
        top: 0;
        right: 0;
        border-width: 4px 4px 0 0;
        border-top-right-radius: 18px;
    }

    .scanqr-corner-bl {
        bottom: 0;
        left: 0;
        border-width: 0 0 4px 4px;
        border-bottom-left-radius: 18px;
    }

    .scanqr-corner-br {
        right: 0;
        bottom: 0;
        border-width: 0 4px 4px 0;
        border-bottom-right-radius: 18px;
    }

    .scanqr-message {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin: 16px 18px 18px;
        padding: 14px 15px;
        border-radius: 18px;
        background: #eff6ff;
        color: #1e40af;
        font-weight: 700;
        line-height: 1.45;
    }

    .scanqr-message.is-error {
        background: #fef2f2;
        color: #b91c1c;
    }

    .scanqr-message.is-success {
        background: #ecfdf5;
        color: #047857;
    }

    .scanqr-message.is-loading i {
        margin-top: 2px;
    }

    .scanqr-result-card {
        display: none;
        overflow: hidden;
    }

    .scanqr-result-card.is-visible {
        display: block;
    }

    .scanqr-result-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.9);
    }

    .scanqr-equipment-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 999px;
        background: #0f172a;
        color: #ffffff;
        font-weight: 900;
        white-space: nowrap;
    }

    .scanqr-detail-grid {
        display: grid;
        gap: 12px;
        padding: 18px;
    }

    .scanqr-detail-item {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
    }

    .scanqr-detail-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 1rem;
    }

    .scanqr-detail-label {
        margin-bottom: 2px;
        color: #64748b;
        font-size: 0.76rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .scanqr-detail-value {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .scanqr-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 0 18px 20px;
    }

    .scanqr-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 12px 16px;
        border: 0;
        border-radius: 14px;
        background: #2563eb;
        color: #ffffff;
        font-weight: 900;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .scanqr-btn:hover,
    .scanqr-btn:focus {
        color: #ffffff;
        background: #1d4ed8;
        box-shadow: 0 14px 30px rgba(37, 99, 235, 0.24);
        text-decoration: none;
        transform: translateY(-1px);
    }

    .scanqr-btn-secondary {
        background: #e2e8f0;
        color: #0f172a;
    }

    .scanqr-btn-secondary:hover,
    .scanqr-btn-secondary:focus {
        color: #0f172a;
        background: #cbd5e1;
        box-shadow: none;
    }

    .scanqr-side {
        display: grid;
        gap: 18px;
    }

    .scanqr-tips {
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
    }

    .scanqr-tips-list {
        display: grid;
        gap: 12px;
        margin: 15px 0 0;
        padding: 0;
        list-style: none;
    }

    .scanqr-tips-list li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #475569;
        line-height: 1.45;
    }

    .scanqr-tips-list i {
        margin-top: 3px;
        color: #2563eb;
    }

    @keyframes scanqrSweep {
        0%, 100% {
            transform: translateY(-48px);
            opacity: 0.35;
        }

        50% {
            transform: translateY(48px);
            opacity: 1;
        }
    }

    @media (max-width: 1100px) {
        .scanqr-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 10px;
        }

        .scanqr-page {
            padding: 16px;
            border-radius: 20px;
        }

        .scanqr-hero,
        .scanqr-panel-header,
        .scanqr-result-header {
            flex-direction: column;
            align-items: stretch;
        }

        .scanqr-status-card {
            min-width: 0;
        }

        .scanqr-reader-shell,
        #reader,
        #reader video {
            min-height: 360px;
        }

        .scanqr-camera-wrap {
            padding: 10px;
        }
    }
</style>

<section class="scanqr-page" aria-labelledby="scanqrTitle">
    <div class="scanqr-hero">
        <div>
            <div class="scanqr-eyebrow"><i class="fas fa-qrcode"></i> Inventory QR Scanner</div>
            <h1 class="scanqr-title" id="scanqrTitle">Scan Equipment Instantly</h1>
            <p class="scanqr-subtitle">Point the camera at an ICT-AMSOS inventory QR code to pull up assigned equipment details without typing the property number manually.</p>
        </div>
        <div class="scanqr-status-card" aria-live="polite">
            <span class="scanqr-status-label">Scanner Status</span>
            <span class="scanqr-status-pill" id="scannerStatus"><span class="scanqr-status-dot"></span> Starting camera</span>
        </div>
    </div>

    <div class="scanqr-layout">
        <div class="scanqr-panel">
            <div class="scanqr-panel-header">
                <div>
                    <h2 class="scanqr-panel-title">Live Camera</h2>
                    <p class="scanqr-panel-copy">Center the QR code inside the glowing frame and hold steady.</p>
                </div>
                <button class="scanqr-btn scanqr-btn-secondary" type="button" id="restartScannerBtn"><i class="fas fa-rotate-right"></i> Restart</button>
            </div>
            <div class="scanqr-camera-wrap">
                <div class="scanqr-reader-shell">
                    <div id="reader"></div>
                    <div class="scanqr-frame" aria-hidden="true">
                        <span class="scanqr-corner scanqr-corner-tl"></span>
                        <span class="scanqr-corner scanqr-corner-tr"></span>
                        <span class="scanqr-corner scanqr-corner-bl"></span>
                        <span class="scanqr-corner scanqr-corner-br"></span>
                    </div>
                </div>
            </div>
            <div class="scanqr-message is-loading" id="scanqrMessage" role="status" aria-live="polite">
                <i class="fas fa-circle-notch fa-spin"></i>
                <span>Requesting camera access. Allow camera permission when prompted.</span>
            </div>
        </div>

        <aside class="scanqr-side">
            <div class="scanqr-result-card" id="result" aria-live="polite">
                <div class="scanqr-result-header">
                    <div>
                        <h2 class="scanqr-result-title">Equipment Found</h2>
                        <p class="scanqr-result-copy">Verified from your office inventory records.</p>
                    </div>
                    <span class="scanqr-equipment-badge" id="equipmentBadge"><i class="fas fa-hashtag"></i> --</span>
                </div>
                <div class="scanqr-detail-grid" id="equipmentDetails"></div>
                <div class="scanqr-actions">
                    <button class="scanqr-btn" type="button" onclick="restartScanner()"><i class="fas fa-qrcode"></i> Scan Again</button>
                </div>
            </div>

            <div class="scanqr-tips">
                <h2 class="scanqr-tips-title">Best Scan Tips</h2>
                <ul class="scanqr-tips-list">
                    <li><i class="fas fa-lightbulb"></i><span>Use good lighting and avoid glare on laminated stickers.</span></li>
                    <li><i class="fas fa-mobile-screen"></i><span>Keep the QR sticker flat inside the frame for faster detection.</span></li>
                    <li><i class="fas fa-shield-halved"></i><span>Only equipment assigned to your office will show details.</span></li>
                </ul>
            </div>
        </aside>
    </div>
</section>

<script>
    let html5QrCode = null;
    let isFetchingEquipment = false;

    const scannerStatus = document.getElementById('scannerStatus');
    const scanqrMessage = document.getElementById('scanqrMessage');
    const resultCard = document.getElementById('result');
    const equipmentDetails = document.getElementById('equipmentDetails');
    const equipmentBadge = document.getElementById('equipmentBadge');
    const restartScannerBtn = document.getElementById('restartScannerBtn');

    function setStatus(text, tone = 'ready') {
        const colors = {
            ready: ['#dcfce7', '#166534'],
            loading: ['#dbeafe', '#1d4ed8'],
            warning: ['#fef3c7', '#92400e'],
            error: ['#fee2e2', '#991b1b']
        };
        const selected = colors[tone] || colors.ready;
        scannerStatus.style.background = selected[0];
        scannerStatus.style.color = selected[1];
        scannerStatus.innerHTML = '<span class="scanqr-status-dot"></span>' + text;
    }

    function setMessage(message, type = 'loading') {
        const icons = {
            loading: 'fa-circle-notch fa-spin',
            error: 'fa-triangle-exclamation',
            success: 'fa-circle-check',
            info: 'fa-circle-info'
        };

        scanqrMessage.className = 'scanqr-message';
        if (type === 'loading') {
            scanqrMessage.classList.add('is-loading');
        } else if (type === 'error') {
            scanqrMessage.classList.add('is-error');
        } else if (type === 'success') {
            scanqrMessage.classList.add('is-success');
        }

        scanqrMessage.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + '"></i><span>' + escapeHtml(message) + '</span>';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function initializeScanner() {
        if (typeof Html5Qrcode === 'undefined') {
            setStatus('Scanner unavailable', 'error');
            setMessage('The QR scanner library did not load. Please check your internet connection and refresh.', 'error');
            return;
        }

        setStatus('Starting camera', 'loading');
        setMessage('Requesting camera access. Allow camera permission when prompted.', 'loading');
        resultCard.classList.remove('is-visible');
        isFetchingEquipment = false;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode('reader');
        }

        const config = {
            fps: 10,
            qrbox: function(width, height) {
                const minDim = Math.min(width, height);
                const boxSize = Math.max(220, Math.floor(minDim * 0.68));
                return { width: boxSize, height: boxSize };
            },
            aspectRatio: 1.35
        };

        html5QrCode.start(
            { facingMode: 'environment' },
            config,
            onScanSuccess,
            onScanError
        ).then(function() {
            setStatus('Ready to scan', 'ready');
            setMessage('Camera is active. Place the QR code inside the frame.', 'info');
        }).catch(handleCameraError);
    }

    function stopScanner() {
        if (!html5QrCode || !html5QrCode.isScanning) {
            return Promise.resolve();
        }

        return html5QrCode.stop().catch(function(error) {
            console.error('Scanner stop error:', error);
        });
    }

    function onScanSuccess(qrCodeMessage) {
        if (isFetchingEquipment) {
            return;
        }

        isFetchingEquipment = true;
        setStatus('QR detected', 'loading');
        setMessage('QR code detected. Looking up equipment details...', 'loading');
        stopScanner().then(function() {
            fetchData(qrCodeMessage);
        });
    }

    function onScanError() {
        // The library fires this repeatedly while searching, so keep the UI quiet.
    }

    function handleCameraError(error) {
        console.error('Camera error:', error);
        setStatus('Camera blocked', 'error');
        setMessage('Camera access is unavailable. Allow camera permission, close other camera apps, then press Restart.', 'error');
    }

    function fetchData(equipmentId) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_equipment.php?equipment_id=' + encodeURIComponent(equipmentId), true);

        xhr.onload = function() {
            isFetchingEquipment = false;

            if (xhr.status < 200 || xhr.status >= 300) {
                setStatus('Lookup failed', 'error');
                setMessage('Unable to fetch equipment details. Please try scanning again.', 'error');
                return;
            }

            try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    displayData(data.row);
                    resultCard.classList.add('is-visible');
                    setStatus('Equipment found', 'ready');
                    setMessage('Equipment details loaded successfully.', 'success');
                } else {
                    setStatus('No record found', 'warning');
                    setMessage(data.message || 'No inventory record was found for this QR code.', 'error');
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                setStatus('Invalid response', 'error');
                setMessage('The server returned an invalid response. Please try again.', 'error');
            }
        };

        xhr.onerror = function() {
            isFetchingEquipment = false;
            setStatus('Network error', 'error');
            setMessage('Network error while fetching equipment details. Please try again.', 'error');
        };

        xhr.send();
    }

    function displayData(row) {
        const fields = [
            ['ID', row.id, 'fa-hashtag'],
            ['Employee Name', row.employeeName, 'fa-user'],
            ['Equipment Type', row.equipmentType, 'fa-laptop'],
            ['Year Acquired', row.yearAcquired, 'fa-calendar'],
            ['Brand', row.brand, 'fa-tag'],
            ['Amount', row.amount, 'fa-peso-sign'],
            ['Property Number', row.propertyNumber, 'fa-barcode']
        ];

        equipmentBadge.innerHTML = '<i class="fas fa-hashtag"></i> ' + escapeHtml(row.id || '--');
        equipmentDetails.innerHTML = fields.map(function(field) {
            return '<div class="scanqr-detail-item">'
                + '<span class="scanqr-detail-icon"><i class="fas ' + field[2] + '"></i></span>'
                + '<div><div class="scanqr-detail-label">' + escapeHtml(field[0]) + '</div>'
                + '<div class="scanqr-detail-value">' + escapeHtml(field[1] || 'Not specified') + '</div></div>'
                + '</div>';
        }).join('');
    }

    function restartScanner() {
        setStatus('Restarting', 'loading');
        setMessage('Restarting camera scanner...', 'loading');
        resultCard.classList.remove('is-visible');
        stopScanner().then(initializeScanner);
    }

    restartScannerBtn.addEventListener('click', restartScanner);
    document.addEventListener('DOMContentLoaded', initializeScanner);

    window.addEventListener('orientationchange', function() {
        if (!html5QrCode || !html5QrCode.isScanning) {
            return;
        }

        setStatus('Adjusting camera', 'loading');
        stopScanner().then(function() {
            window.setTimeout(initializeScanner, 350);
        });
    });
</script>
