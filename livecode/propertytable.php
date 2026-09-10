<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Equipment Table</title>
    <link rel="stylesheet" href="styles.css">
</head>

<style>

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #f5f5f5;
}

.table-container {
    width: 90%;
    height: 80vh;
    overflow: auto;
    background-color: #ffffff;
    border: 1px solid #dddddd;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    font-size: 16px;
    text-align: left;
}

thead tr {
    background-color: #009879;
    color: #ffffff;
    text-align: left;
    font-weight: bold;
}

th, td {
    padding: 12px 15px;
    border: 1px solid #dddddd;
}

tbody tr {
    border-bottom: 1px solid #dddddd;
}

tbody tr:nth-of-type(even) {
    background-color: #f3f3f3;
}

tbody tr:last-of-type {
    border-bottom: 2px solid #009879;
}

tbody tr:hover {
    background-color: #f1f1f1;
}

    </style>
<body>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Region</th>
                    <th>Type of ICT Equipment</th>
                    <th>Year Acquired</th>
                    <th>Shelf Life</th>
                    <th>Brand</th>
                    <th>Specifications / Descriptions</th>
                    <th>Range Category (for Computers)</th>
                    <th>Software Installed/Licensing Model</th>
                    <th>Serial Number</th>
                    <th>Property Number</th>
                    <th>Accountable Person</th>
                    <th>Sex</th>
                    <th>Office / Division</th>
                    <th>Status of Employment</th>
                    <th>Actual User</th>
                    <th>Sex</th>
                    <th>Status of Employment</th>
                    <th>Nature of Work</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Crista Jane N. Baltasar</td>
                    <td>Desktop Computers</td>
                    <td>2018</td>
                    <td>5 years</td>
                    <td>HP</td>
                    <td>V223 monitor, 21.5", Intel Core i7-6700 3.4Ghz, 8M, 2133 4C, 8GB, DDR4-2133 DIMM (1x8GB) RAM, 1TB 7200 RPM SATA 6G 3.5 HDD, NVIDIA GeForce GT 720, 2GB PCLE x8 GFX</td>
                    <td>Mid Level</td>
                    <td>Example Software</td>
                    <td>MTR: 3CQ731099G CPU: SGH734TQ34 KYB: BCYRU0CCP84961 MOUSE: FCYRV0AHD697FW</td>
                    <td>9876543210</td>
                    <td>John Doe</td>
                    <td>M</td>
                    <td>Example Division</td>
                    <td>Permanent</td>
                    <td>Jane Doe</td>
                    <td>F</td>
                    <td>Temporary</td>
                    <td>IT Support</td>
                    <td>Working Fine</td>
                </tr>
                <!-- Additional rows can be added here -->
            </tbody>
        </table>
    </div>
</body>
</html>
