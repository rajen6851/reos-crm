<?php
$content = file_get_contents(__DIR__ . '/resources/views/layouts/reos.blade.php');

// Remove BOM if present
$hasBom = false;
if (substr($content, 0, 3) === "\xef\xbb\xbf") {
    $hasBom = true;
    $content = substr($content, 3);
}

$fixed = mb_convert_encoding($content, 'Windows-1252', 'UTF-8');

if ($hasBom) {
    // Add it back? Wait, usually we don't want BOM. Let's omit it.
    // Actually, maybe the whole file was just UTF-8 without BOM?
}

file_put_contents(__DIR__ . '/resources/views/layouts/reos.blade.php', $fixed);
echo "Fixed. BOM was present: " . ($hasBom ? 'Yes' : 'No') . "\n";
