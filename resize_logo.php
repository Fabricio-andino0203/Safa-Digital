<?php
ini_set('memory_limit', '1024M');
$path = __DIR__ . '/public/images/logo.png';
if (file_exists($path)) {
    echo "Resizing logo...\n";
    $src = imagecreatefromstring(file_get_contents($path));
    $width = imagesx($src);
    $height = imagesy($src);
    echo "Original dimensions: {$width}x{$height}\n";
    
    $newWidth = 200;
    $newHeight = floor($height * ($newWidth / $width));
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency
    imagealphablending($thumb, false);
    imagesavealpha($thumb, true);
    $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
    imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);
    
    imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagepng($thumb, $path, 9);
    imagedestroy($thumb);
    imagedestroy($src);
    echo "Done! New size: " . filesize($path) . " bytes\n";
} else {
    echo "Logo not found.\n";
}
