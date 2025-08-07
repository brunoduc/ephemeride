<?php session_start();

function copyFolderContents(string $source, string $destination, bool $recursive = true, bool $overwrite = true): array {
    // Normalize paths to remove trailing slashes
    $source = rtrim($source, '/\\');
    $destination = rtrim($destination, '/\\');

    // Initialize results tracking
    $results = [
        'success' => true,
        'error' => null,
        'copied_files' => [],
        'failed_files' => []
    ];

    // Validate source directory
    if (!is_dir($source)) {
        $results['success'] = false;
        $results['error'] = "Source folder does not exist or is not a directory: $source";
        return $results;
    }

    // Create destination folder if it doesn't exist
    if (!file_exists($destination) && !mkdir($destination, 0755, true)) {
        $results['success'] = false;
        $results['error'] = "Cannot create destination folder: $destination";
        return $results;
    }

    // Ensure destination is a valid directory
    if (!is_dir($destination)) {
        $results['success'] = false;
        $results['error'] = "Destination is not a valid directory: $destination";
        return $results;
    }

    // Read contents of the source directory
    $items = scandir($source);
    if ($items === false) {
        $results['success'] = false;
        $results['error'] = "Failed to read source folder: $source";
        return $results;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $sourceItem = $source . DIRECTORY_SEPARATOR . $item;
        $destinationItem = $destination . DIRECTORY_SEPARATOR . $item;

        // Handle files
        if (is_file($sourceItem)) {
            if (!$overwrite && file_exists($destinationItem)) {
                // Skip overwriting the existing file
                $results['failed_files'][] = $destinationItem; // Track skipped files
                continue;
            }

            // Copy file and track the result
            if (@copy($sourceItem, $destinationItem)) {
                $results['copied_files'][] = $destinationItem;
            } else {
                $results['failed_files'][] = $sourceItem;
                $results['success'] = false;
            }
        }

        // Handle directories recursively
        elseif ($recursive && is_dir($sourceItem)) {
            $subResults = copyFolderContents($sourceItem, $destinationItem, true, $overwrite);

            // Merge results from subdirectories
            $results['copied_files'] = array_merge($results['copied_files'], $subResults['copied_files']);
            $results['failed_files'] = array_merge($results['failed_files'], $subResults['failed_files']);
            if (!$subResults['success']) {
                $results['success'] = false;
            }
        }
    }

    return $results;
}


include_once('../ephemeride.php');
$eph = new ephemeride($_SESSION['base']);

$version = $_GET['version'];
echo "version : $version";
$filename = "../$version.zip";
echo $filename;
clearstatcache();
$zip = new ZipArchive;
if ($zip->open("$filename") === TRUE) {
    $zip->extractTo("..");
    $zip->close();
    $a = substr($version, 1);
    $rep = "../ephemeride-$a";
    echo "rep = $rep";
    if (is_dir("$rep")) {
        $sourceFolder = $rep;
        $destinationFolder = '..';
        $overwrite = TRUE; // Set to true to enable overwriting files

        $copyResults = copyFolderContents($sourceFolder, $destinationFolder, true, $overwrite);

    }
} else {
    echo 'failed';
}




 
