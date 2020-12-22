<?php

namespace Nanotube\Common\Utility;

final class Graphics {
    const FONT_PATH_NIX = '/usr/share/fonts/truetype/freefont/FreeMono.ttf';

    public static function getCaptchaBadgeBlob($text): string {
        $image = @imagecreatetruecolor(30 + strlen($text) * 30, 50);
        $image = self::addRandomGeometricNoise($image, 10, 200);
        // Text
        $font = self::FONT_PATH_NIX;
        $fontSize = 24;
        $lightFontColor = imagecolorallocate($image, 222, 222, 222);
        $shadowFontColor = imagecolorallocate($image, random_int(0, 55), random_int(0, 55), random_int(0, 55));
        for ($i = 0; $i < strlen($text); $i++) {
            $center = ($i + 1) * 30 - 15;
            $x = random_int($center - 5, $center + 5);
            $y = 50 - random_int(8, 15);
            $angle = random_int(0, 1) === 0 ? random_int(0, 30) : random_int(335, 365);
            imagettftext($image, $fontSize, $angle, $x, $y, $shadowFontColor, $font, $text[$i]);
            imagettftext($image, $fontSize, $angle, $x - 1, $y - 1, $lightFontColor, $font, $text[$i]);
        }
        // More "confetti" :)
        $image = self::addRandomGeometricNoise($image, 5, 90);
        $tempImageFile = tempnam(sys_get_temp_dir(), 'captcha');
        imagepng($image, $tempImageFile);
        imagedestroy($image);
        return file_get_contents($tempImageFile);
    }

    public static function addRandomGeometricNoise(&$imageRef, $amount, $colorThreshold) {
        $width = imagesx($imageRef);
        $height = imagesy($imageRef);
        for ($i = 0; $i < $amount; $i++) {
            $color = imagecolorallocate($imageRef,
                random_int(0, $colorThreshold),
                random_int(0, $colorThreshold),
                random_int(0, $colorThreshold)                  
            );
            switch (random_int(0, 4)) {
                case 0:
                    imageellipse($imageRef, random_int(15, $width - 15), random_int(0, $height),
                        random_int((int) ($width / 5), (int) ($width / 3)),
                        random_int((int) ($width / 5), (int) ($width / 3)),
                        $color
                    );
                    break;
                case 1:
                    $x1 = random_int(0, $width);
                    imagedashedline($imageRef, $x1, 0, $x1 + random_int(-$width / 2, $width / 2), $height, $color);
                    break;
                case 2:
                    $x1 = random_int(0, $width / 2);
                    $y1 = random_int(0, $height / 2);
                    imagerectangle($imageRef, $x1, $y1,
                        random_int($x1, $width - $x1), random_int($y1, $height - $y1), $color
                    );
                    break;
                default: // Line got more chance to be drawn
                    $x1 = random_int(0, $width);
                    imageline($imageRef, $x1, 0, $x1 + random_int(-$width / 2, $width / 2), $height, $color);
            }
        }
        return $imageRef;
    }
}