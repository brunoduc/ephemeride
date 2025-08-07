<?php session_start();

function rmdirRecursive($dir) {
    if (!is_dir($dir)) {
        return false; // Le chemin n'est pas un répertoire
    }
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue; // Ignorer les pointeurs
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            rmdirRecursive($path); // Appel récursif pour les sous-répertoires
        } else {
            unlink($path); // Supprimer les fichiers
        }
    }
    return rmdir($dir); // Supprimer le répertoire une fois vide
}

function moveFolderContents(string $source, string $destination, bool $recursive = true, bool $overwrite = true): array {
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
        $results['error'] = "Le répertoire source n'existe pas ou n'est pas un répertoire : $source";
        return $results;
    }

    // Create destination folder if it doesn't exist
    if (!file_exists($destination) && !mkdir($destination, 0755, true)) {
        $results['success'] = false;
        $results['error'] = "Imposibilité de créer le répertoire de destination : $destination";
        return $results;
    }

    // Ensure destination is a valid directory
    if (!is_dir($destination)) {
        $results['success'] = false;
        $results['error'] = "La destination n'est pas un répertoire valide : $destination";
        return $results;
    }

    // Read contents of the source directory
    $items = scandir($source);
    if ($items === false) {
        $results['success'] = false;
        $results['error'] = "Imposibilité de lire le répertoire source : $source";
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

            // Move file and track the result
            if (@rename($sourceItem, $destinationItem)) {
                $results['copied_files'][] = $destinationItem;
            } else {
                $results['failed_files'][] = $sourceItem;
                $results['success'] = false;
            }
        }

        // Handle directories recursively
        elseif ($recursive && is_dir($sourceItem)) {
            $subResults = moveFolderContents($sourceItem, $destinationItem, true, $overwrite);

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
$filename = "../$version.zip";
clearstatcache();
$zip = new ZipArchive;
if ($zip->open("$filename") === TRUE) {
    $zip->extractTo("..");
    $zip->close();
    unlink($filename);
    $rep = "../ephemeride-$version";
    if (is_dir("$rep")) {
        $sourceFolder = $rep;
        $destinationFolder = '..';
        $overwrite = TRUE; // Set to true to enable overwriting files

        $copyResults = moveFolderContents($sourceFolder, $destinationFolder, true, $overwrite);

        echo "Mise à jour $version effectuée";

        rmdirRecursive($rep);

    }
} else {
    echo 'failed';
}




 
