<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <style>
        @keyframes slide {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: slide 15s ease infinite;
            margin: 0;
            padding: 40px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            box-sizing: border-box;
            position: relative;
        }
        .container {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        #drop-text {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.7);
            text-align: center;
            margin: 0 auto 20px auto; /* Add 20px margin to the bottom */
            width: 80%;
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .logo {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="https://suhosting.net/" target="_blank">
        <img src="/logo.png" alt="Logo" class="logo"> <!-- Logo links to https://suhosting.net/ -->
    </a>
    <h2>Enshrouded Log Upload</h2>
    <form id="upload-form" method="POST" action="/upload" enctype="multipart/form-data">
        @csrf
        <div id="drop-text">
            <p>Drag and drop files here or click to select files</p>
        </div>
        <input type="file" id="file-input" name="log" required style="display: none;">
        <div class="text-center">
            <button type="submit" class="btn-primary">Upload</button>
        </div>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    var dropZone = $('body');
    var fileInput = $('#file-input');
    var dropText = $('#drop-text');

    dropZone.on('dragover', function(e) {
        e.preventDefault();
    });

    dropZone.on('drop', function(e) {
        e.preventDefault();

        var files = e.originalEvent.dataTransfer.files;
        fileInput.prop('files', files);

        // Update the text inside the drop-text div
        if (files.length > 1) {
            dropText.text(files.length + " files added.");
        } else {
            dropText.text(files[0].name + " added.");
        }
    });

    fileInput.on('change', function() {
        var files = fileInput[0].files;

        // Update the text inside the drop-text div
        if (files.length > 1) {
            dropText.text(files.length + " files added.");
        } else {
            dropText.text(files[0].name + " added.");
        }
    });

    // Make the drop-text div clickable for file selection
    dropText.on('click', function() {
        fileInput.click();
    });
});
</script>

</body>
</html>