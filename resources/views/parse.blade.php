<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Specs</title>

    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<div class="container">
    <div class="d-flex justify-content-between">
        <div class="btn-group" role="group" aria-label="System Specs, Vulkan Layers and Game Info">
            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#systemSpecs" aria-expanded="true" aria-controls="systemSpecs">
                System Specs
            </button>
            <button class="btn btn-primary ml-1" type="button" data-toggle="collapse" data-target="#vulkanInfo" aria-expanded="true" aria-controls="vulkanLayers">
                Vulkan Info
            </button>
            <button class="btn btn-primary ml-1" type="button" data-toggle="collapse" data-target="#gameInfo" aria-expanded="true" aria-controls="gameInfo">
                Game Info
            </button>
            <button class="btn btn-primary ml-1" type="button" data-toggle="collapse" data-target="#errors" aria-expanded="true" aria-controls="errors">
                Errors
            </button>
            <button class="btn btn-primary ml-1" type="button" data-toggle="collapse" data-target="#repeatedLines" aria-expanded="true" aria-controls="repeatedLines">
                Repeated Lines
            </button>
        </div>
        <div class="d-flex">
            <a href="/download/{{ basename($currentUrl) }}" class="btn btn-primary">Download</a>
            <a href="/upload" class="btn btn-primary ml-1">Upload</a>
        </div>
    </div>
</div>

    <div class="collapse show" id="systemSpecs">
        <div class="card card-body">
            <h3>System Specs</h3>
            <pre>
            @foreach ($systemSpecs as $spec)
                {{ $spec }}
            @endforeach
            </pre>
        </div>
    </div>

    <div class="collapse show" id="vulkanInfo">
        <div class="card card-body">
            <h3>Vulkan Info</h3>
            <pre>
            @foreach($vulkanInfo as $info)
                {{ $info }}
            @endforeach
            </pre>
        </div>
    </div>

    <div class="collapse show" id="gameInfo">
        <div class="card card-body">
            <h3>Game Info</h3>
            <pre>
                            Game Version: {{ isset($gameInfo['version']) ? $gameInfo['version'] : 'Not available' }}
            </pre>
        </div>
    </div>

    <div class="collapse show" id="errors">
        <div class="card card-body">
            <h3>Errors</h3>
            <pre>
            @foreach($errors as $error)
                {{ $error }}
            @endforeach
            </pre>
        </div>
    </div>

    <div class="collapse show" id="repeatedLines">
        <div class="card card-body">
            <h3>Repeated Lines</h3>
            <pre>
            @foreach ($repeatedLines as $line => $count)
                {{ $line }}: {{ $count }}
            @endforeach
            </pre>
        </div>
    </div>
</div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script>
    // Check if the "Repeated Lines" section is empty
    var repeatedLinesContent = document.querySelector('#repeatedLines pre').textContent.trim();
    if (!repeatedLinesContent) {
        // If it's empty, hide the corresponding button and div
        document.querySelector('button[data-target="#repeatedLines"]').style.display = 'none';
        document.querySelector('#repeatedLines').style.display = 'none';
    }
    </script>
    
</body>
</html>