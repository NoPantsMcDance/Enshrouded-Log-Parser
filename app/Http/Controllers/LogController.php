<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogController extends Controller
{
    public function uploadForm()
    {
        return view('upload');
    }

    public function uploadSubmit(Request $request)
    {
        $request->validate([
            'log' => 'required|file|mimetypes:text/plain|max:102400', // 100MB = 102400KB
        ]);

        // Check if the file contains PHP tags
        $fileContent = file_get_contents($request->file('log')->getRealPath());
        if (strpos($fileContent, '<?php') !== false || strpos($fileContent, '?>') !== false) {
            return back()->withErrors(['log' => 'The file cannot contain PHP code.']);
        }

        // Generate a random string of 10 characters
        $randomString = Str::random(10);

        // Get the file extension
        $fileExtension = $request->file('log')->extension();

        // Create the new filename
        $newFilename = $randomString . '.' . $fileExtension;

        // Store the file with the new filename
        $path = $request->file('log')->storeAs('logs', $newFilename, 'public');

        return redirect()->route('parse', ['file' => $newFilename]);
    }

public function parse($file)
{
    $file = 'logs/' . $file;

    if (!Storage::disk('public')->exists($file)) {
        abort(404, 'File not found');
    }

    $content = Storage::disk('public')->get($file);

    $currentUrl = url()->current();


    $cpuInfoRegex = '/(CpuInfo:)(.*?)(?=MemoryInfo:|$)/s';
    $memoryInfoRegex = '/(MemoryInfo:)(.*?)(?=OsSystemInfo:|$)/s';
    $osSystemInfoRegex = '/(OsSystemInfo:)(.*?)(?=CPUs:|$)/s';
    $gpuInfoRegex = '/\[I \d{2}:\d{2}:\d{2},\d{3}\] \[graphics\] selected device \d+ \((.*?)\) for rendering!/';
    $driverNameRegex = '/\[I \d{2}:\d{2}:\d{2},\d{3}\] \[graphics\] - driver name: (.*?)$/m';
    $driverInfoRegex = '/\[I \d{2}:\d{2}:\d{2},\d{3}\] \[graphics\] - driver info: (.*?)$/m';
    $vramInfoRegex = '/"Heap 0": \{\s*\[I \d{2}:\d{2}:\d{2},\d{3}\].*?"Size": (\d+),\s*\[I \d{2}:\d{2}:\d{2},\d{3}\].*?"Budget": \{\s*\[I \d{2}:\d{2}:\d{2},\d{3}\].*?"BudgetBytes": (\d+),\s*\[I \d{2}:\d{2}:\d{2},\d{3}\].*?"UsageBytes": (\d+)/s';    
    $vulkanLayerRegex = '/\[graphics\] Found vulkan instance layer \'(.*?)\'/';
    $gameVersionRegex = '/\[I \d{2}:\d{2}:\d{2},\d{3}\] Game Version \(SVN\): (\d+)/';
    $vulkanInstaceRegex = '/\[graphics\] Vulkan instance version (.*) \(variant (.*)\)/';
    $errorRegex = '/\[(?:X|E) \d{2}:\d{2}:\d{2},\d{3}\] (.*)/';    
    $vulkanDeviceRegex = '/\[I \d{2}:\d{2}:\d{2},\d{3}\] \[graphics\] Vulkan device (\d+) \((.*)\)/';
    $apiVersionRegex = '/\[I \d{2}:\d{2}:\d{2},\d{3}\] \[graphics\] - api version   : (.*)/';
    $skippingDeviceRegex = '/\[I \d{2}:\d{2}:\d{2},\d{3}\] \[graphics\] skipping device because it does not support extension \'(.*?)\'!/';
    $asciiArtRegex = '/^\[.*?\]\s*(.*)$/m';

    $systemSpecs = [];
    $vulkanInfo = [];
    $errors = [];
    $gameInfo = [];
    $lineCounts = [];

    if (!preg_match_all($asciiArtRegex, $content, $matches)) {
        return view('errors.noascii', ['message' => 'Unable to verify full log content. Please ensure this is a complete log file']);
    }

    foreach ([$cpuInfoRegex, $osSystemInfoRegex] as $regex) {
        if (preg_match($regex, $content, $matches)) {
            $lines = explode("\n", trim($matches[2]));
            $systemSpecs[] = $matches[1];
            foreach ($lines as $line) {
                if (preg_match('/\[\w \d{2}:\d{2}:\d{2},\d{3}\]\s*(.*):\s*(.*)/', $line, $lineMatches)) {
                    $systemSpecs[] = "\t" . $lineMatches[1] . ": " . $lineMatches[2];
                }
            }
        }
    }

    if (preg_match($memoryInfoRegex, $content, $matches)) {
        $lines = explode("\n", trim($matches[2]));
        $systemSpecs[] = $matches[1];
        foreach ($lines as $line) {
            if (preg_match('/\[\w \d{2}:\d{2}:\d{2},\d{3}\]\s*(.*):\s*(.*)/', $line, $lineMatches)) {
                // Remove commas from the value
                $value = str_replace(',', '', $lineMatches[2]);
                // Check if the value is numeric
                if (is_numeric($value)) {
                    $valueInGB = round($value / (1024 * 1024 * 1024), 2);
                    $systemSpecs[] = "\t" . $lineMatches[1] . ": " . $valueInGB . " GB";
                } else {
                    // If the value is not numeric, add it to the array as is
                    $systemSpecs[] = "\t" . $lineMatches[1] . ": " . $lineMatches[2];
                }
            }
        }
    }

    if (preg_match_all($gpuInfoRegex, $content, $matches, PREG_SET_ORDER)) {
        $systemSpecs[] = "GPUInfo:";
        foreach ($matches as $match) {
            $systemSpecs[] = "\t" . "Selected Device: " . $match[1];
        }
    }

    if (preg_match($driverNameRegex, $content, $match)) {
        $systemSpecs[] = "\t" . "Driver Name: " . $match[1];
    }

    if (preg_match($driverInfoRegex, $content, $match)) {
        $systemSpecs[] = "\t" . "Driver Info: " . $match[1];
    }

    if (preg_match($vramInfoRegex, $content, $match)) {
        $systemSpecs[] = "\t" . "Heap 0 Size: " . round($match[1] / (1024 * 1024 * 1024), 2) . " GB";
        $systemSpecs[] = "\t" . "Heap 0 BudgetBytes: " . round($match[2] / (1024 * 1024 * 1024), 2) . " GB";
        $systemSpecs[] = "\t" . "Heap 0 UsageBytes: " . round($match[3] / (1024 * 1024 * 1024), 2) . " GB";
    }

    if (preg_match($vulkanInstaceRegex, $content, $matches)) {
        $vulkanInfo[] = "Vulkan Instance:";
        $vulkanInfo[] = "\tVersion: " . $matches[1];
        $vulkanInfo[] = "\tVariant: " . $matches[2];
    }

    if (preg_match_all($vulkanLayerRegex, $content, $matches, PREG_SET_ORDER)) {
        $vulkanInfo[] = "Vulkan Layers:";
        foreach ($matches as $match) {
            $vulkanInfo[] = "\t" . $match[1];
        }
    }

    // Match Vulkan devices
    if (preg_match_all($vulkanDeviceRegex, $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            // Store the device name and initialize its API version to an empty string
            $vulkanDevices[$match[1]] = ['name' => $match[2], 'apiVersion' => ''];
        }
    }

    // Match API versions
    if (preg_match_all($apiVersionRegex, $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            // Assign the API version to the corresponding device
            foreach ($vulkanDevices as $deviceNumber => $deviceInfo) {
                if (empty($deviceInfo['apiVersion'])) {
                    $vulkanDevices[$deviceNumber]['apiVersion'] = $match[1];
                    break;
                }
            }
        }
    }

    // Display Vulkan devices and their API versions
    $vulkanInfo[] = "Vulkan Devices:";
    if (isset($vulkanDevices) && is_array($vulkanDevices)) {
        foreach ($vulkanDevices as $deviceNumber => $deviceInfo) {
            $vulkanInfo[] = "\tDevice " . $deviceNumber . ": " . $deviceInfo['name'];
            $vulkanInfo[] = "\t    API Version: " . $deviceInfo['apiVersion'];
        }
    } else {
        // Handle error here
        $vulkanInfo[] = "\tNo Vulkan devices found or the variable is not set.";
    }

    if (preg_match($gameVersionRegex, $content, $match)) {
        $gameInfo['version'] = $match[1];
    }

    if (preg_match_all($errorRegex, $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            if (!in_array($match[1], $errors)) {
                $errors[] = $match[1];
            }
        }
    }

    $repeatingLines = explode("\n", $content);
    $insideStatsBlock = false;
    foreach ($repeatingLines as $line) {
        // Remove the timestamp from the line
        $lineWithoutTimestamp = preg_replace('/^\[.*?\]\s/', '', $line);

        // Skip lines that consist only of special characters
        if (!preg_match('/\w/', $lineWithoutTimestamp)) {
            continue;
        }

        // Check if the line starts the "Stats" block
        if (strpos($lineWithoutTimestamp, '"Stats": {') !== false) {
            $insideStatsBlock = true;
            continue;
        }

        // Check if the line ends the "Stats" block
        if ($insideStatsBlock && strpos($lineWithoutTimestamp, '}') !== false) {
            $insideStatsBlock = false;
            continue;
        }

        // Skip lines inside the "Stats" block
        if ($insideStatsBlock) {
            continue;
        }

        // If the line is already in the array, increment its count
        if (isset($lineCounts[$lineWithoutTimestamp])) {
            $lineCounts[$lineWithoutTimestamp]++;
        }
        // Otherwise, add the line to the array with a count of 1
        else {
            $lineCounts[$lineWithoutTimestamp] = 1;
        }
    }
    $repeatedLines = array_filter($lineCounts, function($count) {
        return $count > 9;
    });

    if (preg_match_all($skippingDeviceRegex, $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $errors[] = "Skipping device because it does not support extension: " . $match[1];
        }
    }

    return view('parse', ['systemSpecs' => $systemSpecs, 'vulkanInfo' => $vulkanInfo, 'gameInfo' => $gameInfo, 'errors' => $errors, 'repeatedLines' => $repeatedLines, 'currentUrl' => $currentUrl]);
}
}