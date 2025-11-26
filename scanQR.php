<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>QR Code Scanner</title>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <style>
        #reader {
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        #reader video {
            width: 100%;
            height: auto;
        }
        #result {
            margin: 20px auto;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            display: none;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .data-table th {
            text-align: left;
            background: #f8f9fa;
            width: 40%;
        }
        .data-table th, .data-table td {
            padding: 12px;
            border: 1px solid #dee2e6;
            word-break: break-word;
        }
        .scan-again-btn {
            display: block;
            margin: 20px auto;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        @media (orientation: portrait) {
            #reader {
                height: 60vh;
            }
        }
        @media (orientation: landscape) {
            #reader {
                height: 50vh;
            }
        }
    </style>
</head>
<body>
    <h1>QR Code Scanner</h1>
    <div id="reader"></div>
    
    <div id="result">
        <h2>Equipment Details</h2>
        <table class="data-table">
            <tbody></tbody>
        </table>
        <button class="scan-again-btn" onclick="restartScanner()">Scan Again</button>
    </div>
    <script>
        let html5QrCode = null;

        function initializeScanner() {
            html5QrCode = new Html5Qrcode("reader");
            const config = { 
                fps: 10, 
                qrbox: (width, height) => {
                    const minDim = Math.min(width, height);
                    return Math.floor(minDim * 0.7);
                }
            };
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanError
            ).catch(error => {
                handleCameraError(error);
            });
        }

        function onScanSuccess(qrCodeMessage) {
            html5QrCode.stop();
            fetchData(qrCodeMessage);
        }

        function onScanError(errorMessage) {
            console.error('Scan error:', errorMessage);
        }

        function handleCameraError(error) {
            console.error('Camera error:', error);
            document.getElementById('reader').innerHTML = `
                <p style="text-align:center; color:red;">Camera access denied. Please enable camera permissions and refresh the page.</p>
            `;
        }

        function fetchData(equipment_id) {
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'none';
            document.body.innerHTML += '<div class="loading">Loading details...</div>';

            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_equipment.php?equipment_id=' + encodeURIComponent(equipment_id), true);

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        document.querySelector('.loading').remove();
                        if (data.success) {
                            displayData(data.row);
                            resultDiv.style.display = 'block';
                        } else {
                            alert('No data found for this QR code');
                            restartScanner();
                        }
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        document.querySelector('.loading').remove();
                        alert('Error parsing data');
                        restartScanner();
                    }
                } else {
                    document.querySelector('.loading').remove();
                    alert('Error fetching data');
                    restartScanner();
                }
            };

            xhr.onerror = function () {
                document.querySelector('.loading').remove();
                alert('Error fetching data');
                restartScanner();
            };

            xhr.send();
        }

        function displayData(row) {
            const tableBody = document.querySelector('.data-table tbody');
            tableBody.innerHTML = `
                <tr><th>ID</th><td>${row.id}</td></tr>
                <tr><th>Ticket Number</th><td>${row.ticketNumber}</td></tr>
                <tr><th>Date</th><td>${row.date}</td></tr>
                <tr><th>Name</th><td>${row.name}</td></tr>
                <tr><th>Division/Section/Unit</th><td>${row.divSecUnit}</td></tr>
                <tr><th>Office</th><td>${row.office}</td></tr>
                <tr><th>Position</th><td>${row.position}</td></tr>
                <tr><th>Contact Number</th><td>${row.contactNumber}</td></tr>
                <tr><th>Email</th><td>${row.email}</td></tr>
                <tr><th>Request Type</th><td>${row.requestType}</td></tr>
                <tr><th>Description</th><td>${row.description}</td></tr>
                <tr><th>Status</th><td>${row.status}</td></tr>
                <tr><th>Station</th><td>${row.station}</td></tr>
                <tr><th>Remarks</th><td>${row.remarks}</td></tr>
            `;
        }

        function restartScanner() {
            document.getElementById('result').style.display = 'none';
            initializeScanner();
        }

        // Initialize scanner when page loads
        document.addEventListener('DOMContentLoaded', initializeScanner);

        // Handle orientation changes
        window.addEventListener('orientationchange', () => {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    initializeScanner();
                });
            }
        });
    </script>
</body>
</html>