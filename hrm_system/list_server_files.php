<?php
echo "Listing app/controllers:<br>\n";
$dir = __DIR__ . '/app/controllers';
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file) {
        echo $file . "<br>\n";
    }
} else {
    echo "Directory not found!<br>\n";
}
