<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promo Pricing Calculator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: #e9f5e9;
            border-radius: 4px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Promo Pricing Calculator</h1>
        <div class="form-group">
            <label for="unitName">Unit Name</label>
            <input type="text" id="unitName" placeholder="Enter unit name">
        </div>
        <div class="form-group">
            <label for="srp">SRP</label>
            <input type="number" id="srp" placeholder="Enter SRP">
        </div>
        <div class="form-group">
            <label for="dp20">Promo DP 20%</label>
            <input type="text" id="dp20" readonly>
        </div>
        <div class="form-group">
            <label for="dp25">Promo DP 25%</label>
            <input type="text" id="dp25" readonly>
        </div>
        <div class="form-group">
            <label for="dp30">Promo DP 30%</label>
            <input type="text" id="dp30" readonly>
        </div>
        <div class="form-group">
            <label for="fiveYearsPrice">5 Years Price</label>
            <input type="text" id="fiveYearsPrice" readonly>
        </div>
        <button onclick="calculatePrices()">Calculate Prices</button>
        <div class="result" id="result"></div>
    </div>
    <script>
        function formatCurrency(amount) {
            return "₱" + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        function calculatePrices() {
            const srp = parseFloat(document.getElementById('srp').value);
            if (srp) {
                const dp20 = srp * 0.20;
                const dp25 = srp * 0.25;
                const dp30 = srp * 0.30;
                const fiveYearsPrice = srp * 5;

                document.getElementById('dp20').value = formatCurrency(dp20);
                document.getElementById('dp25').value = formatCurrency(dp25);
                document.getElementById('dp30').value = formatCurrency(dp30);
                document.getElementById('fiveYearsPrice').value = formatCurrency(fiveYearsPrice);

                document.getElementById('result').innerHTML = `
                    <p><strong>Unit Name:</strong> ${document.getElementById('unitName').value}</p>
                    <p><strong>SRP:</strong> ${formatCurrency(srp)}</p>
                    <p><strong>Promo DP 20%:</strong> ${formatCurrency(dp20)}</p>
                    <p><strong>Promo DP 25%:</strong> ${formatCurrency(dp25)}</p>
                    <p><strong>Promo DP 30%:</strong> ${formatCurrency(dp30)}</p>
                    <p><strong>5 Years Price:</strong> ${formatCurrency(fiveYearsPrice)}</p>
                `;
            } else {
                document.getElementById('result').innerHTML = '<p>Please enter the SRP.</p>';
            }
        }
    </script>
</body>
</html>
