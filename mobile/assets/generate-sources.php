<?php

/**
 * One-off script (not part of the app runtime) that composites the
 * existing BluePeak Fintech logo onto brand-colored canvases at the sizes
 * @capacitor/assets expects as *source* images, from which it then
 * generates every actual Android/iOS icon and splash resolution.
 *
 * Run: php generate-sources.php
 *
 * The source logo here is only 484x495, so these are upscaled and will
 * look soft blown up to app-icon size -- swap in a real 1024x1024 icon.png
 * (and optionally a nicer splash background) before shipping to the
 * stores; re-run `npm run assets` afterwards to regenerate.
 */

$brandNavy = [12, 32, 83]; // #0c2053
$logoPath = __DIR__.'/../../public/images/logo-icon.png';

function canvas(int $size, array $rgb): GdImage
{
    $img = imagecreatetruecolor($size, $size);
    $color = imagecolorallocate($img, ...$rgb);
    imagefill($img, 0, 0, $color);

    return $img;
}

function pasteLogoCentered(GdImage $canvasImg, string $logoPath, int $canvasSize, float $logoScale): void
{
    $logo = imagecreatefrompng($logoPath);
    imagesavealpha($logo, true);

    $logoW = imagesx($logo);
    $logoH = imagesy($logo);
    $targetW = (int) ($canvasSize * $logoScale);
    $targetH = (int) ($logoH * ($targetW / $logoW));

    $resized = imagecreatetruecolor($targetW, $targetH);
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
    imagefill($resized, 0, 0, $transparent);
    imagecopyresampled($resized, $logo, 0, 0, 0, 0, $targetW, $targetH, $logoW, $logoH);

    imagealphablending($canvasImg, true);
    imagecopy($canvasImg, $resized, (int) (($canvasSize - $targetW) / 2), (int) (($canvasSize - $targetH) / 2), 0, 0, $targetW, $targetH);
}

// App icon: 1024x1024, logo fills most of the frame (platforms apply
// their own corner-rounding/masking on top of this square source).
$icon = canvas(1024, $brandNavy);
pasteLogoCentered($icon, $logoPath, 1024, 0.62);
imagepng($icon, __DIR__.'/icon.png');

// Splash: 2732x2732, logo small and centered with generous padding.
$splash = canvas(2732, $brandNavy);
pasteLogoCentered($splash, $logoPath, 2732, 0.32);
imagepng($splash, __DIR__.'/splash.png');

echo "Wrote assets/icon.png and assets/splash.png\n";
