<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keep Signature, Remove Name</title>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@4.0.2/dist/tesseract.min.js"></script>
</head>
<body>
    <div style="position: relative; width: fit-content;">
        <!-- The original image -->
        <img id="signatureImage" src="/mnt/data/image.png" alt="Signature" style="display: none;">

        <!-- Canvas overlay for editing -->
        <canvas id="canvasOverlay" style="position: absolute; top: 0; left: 0;"></canvas>
    </div>

    <script>
        window.onload = function () {
            const image = document.getElementById('signatureImage');
            const canvas = document.getElementById('canvasOverlay');
            const ctx = canvas.getContext('2d');

            // Set canvas dimensions to match the image
            canvas.width = image.width;
            canvas.height = image.height;

            // Load the Tesseract.js library to detect text
            Tesseract.recognize(
                image.src, // Image source
                'eng', // English language for OCR
                {
                    logger: (info) => console.log(info) // Log progress for debugging
                }
            ).then(({ data: { blocks } }) => {
                // Draw the original image on the canvas
                const img = new Image();
                img.src = image.src;
                img.onload = () => {
                    ctx.drawImage(img, 0, 0);

                    // Loop through detected blocks and erase only the name
                    blocks.forEach(block => {
                        const detectedText = block.text.trim();
                        if (detectedText.toUpperCase().includes("MARIA ALYZA JOCEL S. DOMINGO")) {
                            const { x0, y0, x1, y1 } = block.bbox; // Get bounding box
                            ctx.fillStyle = 'white'; // Set fill color to white
                            ctx.fillRect(x0, y0, x1 - x0, y1 - y0); // Draw rectangle to cover the name
                        }
                    });
                };
            }).catch((error) => {
                console.error("Error during OCR:", error);
            });
        };
    </script>
</body>
</html>
