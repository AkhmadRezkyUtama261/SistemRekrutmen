<?php
$oldConfig = "<script src="<?= BASE_URL ?>/assets/js/theme.js"></script>";
$newConfig = "<script src=\"<?= BASE_URL ?>/assets/js/theme.js\"></script>";

function replaceInDir($dir) {
    global $oldConfig, $newConfig;
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        if (is_dir($file)) {
            replaceInDir($file);
        } else if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($file);
            if (strpos($content, $oldConfig) !== false) {
                $content = str_replace($oldConfig, $newConfig, $content);
                file_put_contents($file, $content);
                echo "Replaced in: $file\n";
            }
        }
    }
}

replaceInDir(__DIR__);
echo "Done!\n";
