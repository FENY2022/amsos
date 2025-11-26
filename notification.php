<!DOCTYPE html>
<html>
<head>
    <title>Chrome Notification Example</title>
</head>
<body>
    <button onclick="showNotification()">Show Notification</button>

    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.4/dist/tesseract.min.js"></script>
    <script>
        // Request notification permission on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        console.log('Notification permission granted.');
                    } else {
                        console.log('Notification permission denied.');
                    }
                }).catch(error => {
                    console.log('Notification permission request failed:', error);
                });
            }
        });


        // Function to show a notification
        function showNotification() {
            if (Notification.permission === 'granted') {
                const options = {
                    body: 'Your Travel Order Approved',
                    icon: 'icon.png' // Ensure 'icon.png' is in the same directory or provide the correct path
                };
                new Notification('Notification', options);
                console.log('Notification shown.');
            } else if (Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        showNotification(); // Retry showing the notification after permission is granted
                    } else {
                        console.log('Notification permission not granted.');
                    }
                }).catch(error => {
                    console.log('Notification permission request failed:', error);
                });
            } else {
                console.log('Notification permission not granted.');
            }
        }
    </script>
</body>
</html>
