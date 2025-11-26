<?php
// Include database connection
require_once 'connect.php';

// Check if the ID parameter is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "No equipment ID provided.";
    exit;
}

// Fetch data from the database
$id = intval($_GET['id']);
$query = "SELECT employeeName, equipmentType, yearAcquired, brand, amount, propertyNumber, id
          FROM inv_inventory
          WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No record found for the provided ID.";
    exit;
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <link rel="shortcut icon" type="image/x-icon" href="icon/amsos.ico">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --primary-blue: #007bff;
            --success-green: #28a745;
            --secondary-gray: #6c757d;
            --light-gray: #f8f9fa;
            --dark-text: #343a40;
            --border-color: #dee2e6;
        }

        body {
            font-family: 'Roboto', Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 20px;
            background-color: var(--light-gray);
            color: var(--dark-text);
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align to the top, allowing scrolling */
            min-height: 100vh;
            box-sizing: border-box;
        }

        /* Custom Scrollbar for a cleaner look */
        body::-webkit-scrollbar {
            width: 8px;
        }

        body::-webkit-scrollbar-track {
            background: var(--light-gray);
        }

        body::-webkit-scrollbar-thumb {
            background-color: var(--secondary-gray);
            border-radius: 10px;
            border: 2px solid var(--light-gray);
        }

        .container {
            background-color: #ffffff;
            margin: 20px auto;
            max-width: 600px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        h3 {
            color: var(--primary-blue);
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 1.8em;
        }

        .denr-logo {
            width: 220px; /* Slightly larger logo */
            height: auto;
            margin-bottom: 10px; /* Reduced margin */
        }

        .qr-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px; /* Spacing between details */
            padding-top: 15px; /* Added padding */
            border-top: 1px solid var(--border-color); /* Separator */
            margin-top: 15px;
        }

        .qr-detail {
            font-size: 1.1em;
            text-align: left; /* Align details to the left */
            width: 80%; /* Control width of detail lines */
            display: flex;
            justify-content: space-between; /* Space out key and value */
            padding: 4px 0;
            border-bottom: 1px dashed #eee; /* Subtle separator for details */
        }

        .qr-detail strong {
            color: var(--dark-text);
            min-width: 150px; /* Ensure key alignment */
        }
        
        .qr-detail span {
            flex-grow: 1;
            text-align: right;
            font-weight: 400;
        }

        #qrcode {
            margin: 25px auto; /* More vertical space around QR */
            border: 6px solid var(--primary-blue); /* Distinct border for QR */
            border-radius: 8px;
            padding: 5px; /* Inner padding for the border */
        }

        .btn-group {
            margin-top: 25px; /* More space above buttons */
            display: flex;
            flex-wrap: wrap; /* Allow buttons to wrap on smaller screens */
            justify-content: center;
            gap: 15px; /* Space between buttons */
            width: 100%;
        }

        .btn-group button,
        .btn-group a {
            padding: 12px 25px; /* Larger buttons */
            border: none;
            border-radius: 6px; /* Slightly more rounded corners */
            cursor: pointer;
            text-decoration: none;
            display: inline-flex; /* Use flex for centering content if needed */
            align-items: center;
            justify-content: center;
            font-size: 1em;
            font-weight: 500;
            transition: background-color 0.2s ease, transform 0.2s ease; /* Smooth transitions */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle button shadow */
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3; /* Darker shade on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        .btn-success {
            background-color: var(--success-green);
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--secondary-gray);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 15px;
            }
            h3 {
                font-size: 1.5em;
            }
            .qr-detail {
                font-size: 1em;
                width: 95%; /* Adjust width for smaller screens */
                flex-direction: column; /* Stack key and value on small screens */
                align-items: flex-start;
                padding-bottom: 10px;
            }
            .qr-detail strong {
                min-width: unset;
                margin-bottom: 3px;
            }
            .qr-detail span {
                text-align: left;
            }
            .btn-group {
                flex-direction: column; /* Stack buttons vertically */
                gap: 10px;
            }
            .btn-group button,
            .btn-group a {
                width: 100%; /* Full width buttons */
            }
        }

        /* Print-specific styles */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
                margin: 0;
                display: block; /* Revert flex for printing */
                min-height: auto;
            }
            .container {
                box-shadow: none;
                border: none;
                margin: 0 auto;
                padding: 10mm; /* Use physical units for print */
                page-break-after: always; /* Ensure each QR code prints on a new page if generated in a loop */
            }
            .btn-group, .denr-logo {
                display: none; /* Hide buttons and logo in print */
            }
            #qrcode {
                border: 2px solid #000; /* Simple border for print */
                padding: 0;
            }
            .qr-detail {
                border-bottom: none; /* No dashes in print */
                font-size: 0.9em;
                display: block; /* Don't use flex for print, simpler layout */
                text-align: left;
                width: auto;
            }
            .qr-detail strong {
                display: inline;
                margin-right: 5px;
            }
            .qr-detail span {
                display: inline;
                text-align: left;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <img src="logo/denr.png" alt="DENR Logo" class="denr-logo">

    <h3>QR Code for Equipment</h3>
    <div class="qr-container">
        <div class="qr-detail"><strong>Employee Name:</strong> <span><?php echo htmlspecialchars($row['employeeName']); ?></span></div>
        <div class="qr-detail"><strong>Equipment Type:</strong> <span><?php echo htmlspecialchars($row['equipmentType']); ?></span></div>
        <div class="qr-detail"><strong>Year Acquired:</strong> <span><?php echo htmlspecialchars($row['yearAcquired']); ?></span></div>
        <div class="qr-detail"><strong>Brand:</strong> <span><?php echo htmlspecialchars($row['brand']); ?></span></div>
        <div class="qr-detail"><strong>Amount:</strong> <span><?php echo htmlspecialchars($row['amount']); ?></span></div>
        <div class="qr-detail"><strong>Property Number:</strong> <span><?php echo htmlspecialchars($row['propertyNumber']); ?></span></div>
        <div class="qr-detail"><strong>ID:</strong> <span><?php echo htmlspecialchars($row['id']); ?></span></div>

        <div id="qrcode"></div>
    </div>

    <div class="btn-group">
        <button onclick="window.print()" class="btn btn-success">Print QR Code</button>
        <button id="downloadBtn" class="btn btn-primary">Download QR Code</button>
        <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ------------------------------------------------------------
    // 1)  Create the URL you want users to visit when they scan:
    //     Replace "http://yourdomain.com/details.php" with your own page.
    // ------------------------------------------------------------
    const qrLink = `http://102.103.104.123/icteq/details.php?id=<?php echo $row['id']; ?>`;

    // If you want to store additional data in the QR code, you can append it:
    // const qrLink = `http://yourdomain.com/details.php?id=<?php echo $row['id']; ?>&empName=<?php echo urlencode($row['employeeName']); ?>`;

    // ------------------------------------------------------------
    // 2)  Initialize the QR code using qrLink as the 'text' property
    // ------------------------------------------------------------
    const qrCodeContainer = document.getElementById('qrcode');
    const qrCode = new QRCode(qrCodeContainer, {
        text: qrLink,
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    // ------------------------------------------------------------
    // 3)  Allow downloading the QR code as a PNG with the extra design
    // ------------------------------------------------------------
    document.getElementById('downloadBtn').addEventListener('click', function () {
        // A small delay ensures the QR code canvas is fully rendered.
        setTimeout(() => {
            const qrCanvas = qrCodeContainer.querySelector('canvas');
            if (!qrCanvas) {
                console.error("QR Code canvas not found.");
                return;
            }

            const tempCanvas = document.createElement('canvas');
            const tempCtx = tempCanvas.getContext('2d');

            // Set canvas size for the downloadable image
            tempCanvas.width = 450;
            tempCanvas.height = 650;

            // Add Border
            tempCtx.fillStyle = "#ffffff";
            tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            tempCtx.strokeStyle = "#000000";
            tempCtx.lineWidth = 5;
            tempCtx.strokeRect(0, 0, tempCanvas.width, tempCanvas.height);

            // Add Logo
            const logo = new Image();
            logo.src = 'logo/denr.png'; // path to your logo
            logo.onload = function () {
                // Draw Logo
                tempCtx.drawImage(logo, 125, 20, 200, 70); // Logo scaled and centered

                // Add Title
                tempCtx.font = '20px Roboto, Arial, sans-serif'; /* Use Roboto */
                tempCtx.fillStyle = '#000000';
                tempCtx.textAlign = 'center';
                tempCtx.fillText('QR Code for Equipment', 225, 120);

                // Add QR Code
                tempCtx.drawImage(qrCanvas, 125, 140, 200, 200);

                // Add Equipment Details
                tempCtx.font = '14px Roboto, Arial, sans-serif'; /* Use Roboto */
                tempCtx.fillStyle = '#000000';
                tempCtx.textAlign = 'left';

                let startX = 50;
                let startY = 370;
                let lineSpacing = 25; /* Reduced line spacing for download image */

                const details = [
                    { label: 'Employee Name:', value: '<?php echo htmlspecialchars($row['employeeName']); ?>' },
                    { label: 'Equipment Type:', value: '<?php echo htmlspecialchars($row['equipmentType']); ?>' },
                    { label: 'Year Acquired:', value: '<?php echo htmlspecialchars($row['yearAcquired']); ?>' },
                    { label: 'Brand:', value: '<?php echo htmlspecialchars($row['brand']); ?>' },
                    { label: 'Amount:', value: '<?php echo htmlspecialchars($row['amount']); ?>' },
                    { label: 'Property Number:', value: '<?php echo htmlspecialchars($row['propertyNumber']); ?>' },
                    { label: 'ID:', value: '<?php echo htmlspecialchars($row['id']); ?>' }
                ];

                details.forEach((detail, index) => {
                    tempCtx.fillText(`${detail.label} ${detail.value}`, startX, startY + (lineSpacing * index));
                });

                // Download as Image
                const qrImage = tempCanvas.toDataURL("image/png");
                const downloadLink = document.createElement('a');
                downloadLink.href = qrImage;
                downloadLink.download = `QRCode_<?php echo htmlspecialchars($row['id']); ?>.png`;
                document.body.appendChild(downloadLink); // Append to body to make it clickable
                downloadLink.click();
                document.body.removeChild(downloadLink); // Clean up
            };
            logo.onerror = function() {
                console.error("Failed to load logo image.");
                // Proceed to download without logo if it fails
                const qrImage = qrCanvas.toDataURL("image/png");
                const downloadLink = document.createElement('a');
                downloadLink.href = qrImage;
                downloadLink.download = `QRCode_<?php echo htmlspecialchars($row['id']); ?>.png`;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            };
        }, 500); // Give QRCode.js a moment to render the canvas
    });
});
</script>

</body>
</html>