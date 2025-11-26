<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<style>
  /* Style the entire form */
  .myForm {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 20px auto;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

  label {
    font-weight: bold;
  }

  input[type="date"], select {
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 3px;
  }

  /* Dropdown button styling */
  .action-btn {
    background-color: #4CAF50;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }

  .action-menu {
    display: none;
    position: absolute;
    background-color: white;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    border-radius: 5px;
    z-index: 1;
  }

  .action-menu button {
    background: none;
    border: none;
    color: #333;
    padding: 10px 20px;
    text-align: left;
    cursor: pointer;
    width: 100%;
  }

  .action-menu button:hover {
    background-color: #f1f1f1;
  }
</style>

<body>

    <div style="width: 75%; margin: auto;">
        <canvas id="equipmentChart"></canvas>

        <form action="/process_data.php" method="post">
            <label for="dateInput">Enter a date:</label>
            <input type="date" id="dateInput" name="date">

            <label for="DivisionSection">Division Section</label>
            <select id="DivisionSection" name="DivisionSection">
                <option value="apple">ADMIN</option>
                <option value="banana">CDD</option>
                <option value="orange">PMD</option>
                <option value="mango">LPDD</option>
            </select>

            <button type="submit">Submit</button>
        </form>

        <!-- Dropdown Action Button -->
        <div style="position: relative; display: inline-block;">
            <button class="action-btn" onclick="toggleMenu()">Actions</button>
            <div id="actionMenu" class="action-menu">
                <button onclick="showTable()">Show Table</button>
                <button onclick="showGraph()">Show Graph</button>
                <button onclick="printPage()">Print</button>
            </div>
        </div>
    </div>

    <script>
        // Toggle action menu visibility
        function toggleMenu() {
            const menu = document.getElementById("actionMenu");
            menu.style.display = menu.style.display === "block" ? "none" : "block";
        }

        // Close the dropdown menu if clicked outside
        window.onclick = function(event) {
            if (!event.target.matches('.action-btn')) {
                const menu = document.getElementById("actionMenu");
                if (menu.style.display === "block") {
                    menu.style.display = "none";
                }
            }
        };

        // Action functions
        function showTable() {
            alert("Displaying Table...");
            // Add functionality here to display the table
        }

        function showGraph() {
            alert("Displaying Graph...");
            // Add functionality here to display the graph
        }

        function printPage() {
            window.print(); // Prints the current page
        }

        // Fetch data from the PHP script for the chart
        fetch('data.php')
            .then(response => response.json())
            .then(data => {
                const labels = Object.keys(data);
                const values = Object.values(data);

                const ctx = document.getElementById('equipmentChart').getContext('2d');
                const equipmentChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total of Returned Equipment Year 2024',
                            data: values,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
    </script>
</body>
</html>
