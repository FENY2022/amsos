<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slideshow</title>
    <link rel="stylesheet" href="css/imgeviewer.css">
</head>

<body>
    <div class="container">
        <div class="slideshow">
            <img class="slide" src="imagedb/image1.jpg" alt="Slide 1">
            <img class="slide" src="imagedb/image2.jpg" alt="Slide 2">
            <img class="slide" src="imagedb/image3.jpg" alt="Slide 3">
            <img class="slide" src="imagedb/image4.jpg" alt="Slide 4">
        </div>
        <button class="prev" onclick="prevSlide()">❮</button>
        <button class="next" onclick="nextSlide()">❯</button>
    </div>
    <script src="js/imgeviewer.js"></script>
</body>
</html>
