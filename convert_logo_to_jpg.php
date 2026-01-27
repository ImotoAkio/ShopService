<?php
$src = __DIR__ . '/public/assets/img/logo.png';
$dest = __DIR__ . '/public/assets/img/logo.jpg';

if (!file_exists($src)) {
    die("Source file not found: $src\n");
}

$image = imagecreatefrompng($src);
if (!$image) {
    die("Failed to load PNG.\n");
}

// Create white background for transparency
$bg = imagecreatetruecolor(imagesx($image), imagesy($image));
$white = imagecolorallocate($bg, 255, 255, 255);
imagefill($bg, 0, 0, $white);
imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

if (imagejpeg($bg, $dest, 90)) {
    echo "Converted to $dest\n";
} else {
    echo "Failed to save JPEG.\n";
}

imagedestroy($image);
imagedestroy($bg);
