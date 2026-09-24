<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.blade\.php$/', RegexIterator::GET_MATCH);

$count = 0;
foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    
    // Check if the file starts with the UTF-8 BOM
    if (substr($content, 0, 3) === "\xef\xbb\xbf") {
        // Strip the BOM
        $content = substr($content, 3);
        
        // Fix the double-UTF-8 encoding (mojibake)
        $fixed = mb_convert_encoding($content, 'Windows-1252', 'UTF-8');
        
        // Save back without BOM
        file_put_contents($path, $fixed);
        $count++;
    }
}
echo "Successfully fixed mojibake and removed BOM in $count files.\n";
