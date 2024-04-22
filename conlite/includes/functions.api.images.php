<?php
/**
 * ConLite Image API functions
 *
 * @package ConLite\Includes\Functions\Api\Images
 * @version 2.0.0
 * @since 3.0.0 recoded from original functions Contenido 4.8.x
 * @author Ortwin Pinke <o.pinke@conlite.org>
 * @author Timo A. Hummel
 * @copyright 2012 - 2024 ConLite Team
 * @copyright  - 2012 four for business AG <www.4fb.de>
 * @link http://www.conlite.org ConLite.org
 * @license http://www.gnu.de/documents/gpl.en.html GPL v3 (english version)
 * @license http://www.gnu.de/documents/gpl.de.html GPL v3 (deutsche Version)
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * @param string $img
 * @param int $maxX
 * @param int $maxY
 * @param bool $crop
 * @param bool $expand
 * @param int $cacheTime
 * @param bool $wantHQ
 * @param int $quality
 * @param bool $keepType
 * @return bool|string
 */
function cApiImgScale(string $img, int $maxX, int $maxY, bool $crop = false, bool $expand = false, int $cacheTime = 10, bool $wantHQ = false, int $quality = 75, bool $keepType = true): bool|string
{
    if (!cFileHandler::exists($img)) {
        return false;
    }

    $clientConfig = cRegistry::getClientConfig(cRegistry::getClientId());
    $deleteAfter = false;

    $relativeImg = str_replace($clientConfig["upl"]["path"], "", $img);

    if (is_dbfs($relativeImg)) {
        $dbfs = new DBFSCollection();
        $file = basename($relativeImg);

        $dbfs->writeToFile($relativeImg, $clientConfig["path"]["frontend"] . "cache/" . $file);
        $img = $clientConfig["path"]["frontend"] . "cache/" . $file;
        $deleteAfter = true;
    } else if (!cFileHandler::exists($img)) {
        if (cFileHandler::exists($clientConfig['upl']['path'] . $img)
            && !cDirHandler::exists($clientConfig['upl']['path'] . $img)) {
            $img = $clientConfig['upl']['path'] . $img;
        } else {
            return false;
        }
    }

    $fileType = cString::toLowerCase(cFileHandler::getExtension($img));
    $quality = cApiImgGetCompressionRate($fileType, $quality);
    $imgEditingPossibility = cApiImageCheckImageEditingPossibility();

    if ($fileType == "svg") {
        $imgEditingPossibility = "untouched";
    }

    switch ($imgEditingPossibility) {
        case '1': // gd1
            $method = 'gd1';
            if (!function_exists('imagecreatefromgif') && $fileType == 'gif') {
                $method = 'failure';
            }
            if (!function_exists('imagecreatefromwebp') && $fileType == 'webp') {
                $method = 'failure';
            }
            break;
        case '2': // gd2
            $method = 'gd2';
            if (!function_exists('imagecreatefromgif') && $fileType == 'gif') {
                $method = 'failure';
            }
            if (!function_exists('imagecreatefromwebp') && $fileType == 'webp') {
                $method = 'failure';
            }
            break;
        case 'im': // ImageMagick
            $method = 'im';
            break;
        case 'untouched': // special routine for svg only
            $method = 'untouched';
            break;
        case '0':
        default:
            $method = 'failure';
            break;
    }

    switch ($method) {
        case 'gd1':
            $return = cApiImgScaleLQ($img, $maxX, $maxY, $crop, $expand, $cacheTime, $quality, $keepType);
            break;
        case 'gd2':
            $return = cApiImgScaleHQ($img, $maxX, $maxY, $crop, $expand, $cacheTime, $quality, $keepType);
            break;
        case 'im':
            $return = cApiImgScaleImageMagick($img, $maxX, $maxY, $crop, $expand, $cacheTime, $quality, $keepType);
            break;
        case 'failure':
        default:
            $return = str_replace(cRegistry::getFrontendPath(), cRegistry::getFrontendUrl(), $img);
            break;
    }

    if ($deleteAfter) {
        unlink($img);
    }

    return $return;
}

// ToDo
function cApiImgScaleLQ(string $img, int $maxX, int $maxY, $crop = false, $expand = false, int $cacheTime = 10, int $quality = 0, bool $keepType = false): bool|string
{
    if (!cFileHandler::exists($img)) {
        return false;
    }

    $clientConfig = cRegistry::getClientConfig(cRegistry::getClientId());

    $fileType = cString::toLowerCase(cFileHandler::getExtension($img));
    $md5 = cApiImgScaleGetMD5CacheFile($img, $maxX, $maxY, $crop, $expand);
    $cacheFileName = cApiImageGetCacheFileName($md5, $fileType, $keepType);
    $cacheFile = $clientConfig['cache']['path'] . $cacheFileName;
    $webFile = cRegistry::getFrontendUrl() . 'cache/' . $cacheFileName;

    if (cApiImageCheckCachedImageValidity($cacheFile, $cacheTime)) {
        return $webFile;
    }

    // If we can't open the image, return false
    $imageHandle = cApiImgCreateImageResourceFromFile($img, $fileType);
    if (!$imageHandle) {
        return false;
    }

    $x = imagesx($imageHandle);
    $y = imagesy($imageHandle);

    list($targetX, $targetY) = cApiImageGetTargetDimensions($x, $y, $maxX, $maxY, $expand);

    // Create the target image with the target size, resize it afterwards.
    if ($crop) {
        // Create the target image with the max size, crop it afterwards.
        $targetImage = imagecreate($maxX, $maxY);
        imagecopy($targetImage, $imageHandle, 0, 0, 0, 0, $maxX, $maxY);
    } else {
        // Create the target image with the target size, resize it afterwards.
        $targetImage = imagecreate($targetX, $targetY);
        imagecopyresized($targetImage, $imageHandle, 0, 0, 0, 0, $targetX, $targetY, $x, $y);
    }

    // Save the cache file
    cApiImgSaveImageResourceToFile($targetImage, $cacheFile, $quality, $fileType, $keepType);

    // Return the web file
    return $webFile;
}

/**
 * @param string $img
 * @param int $maxX
 * @param int $maxY
 * @param bool $crop
 * @param bool $expand
 * @param int $cacheTime
 * @param int $quality
 * @param bool $keepType
 * @return bool|string
 */
function cApiImgScaleHQ(string $img, int $maxX, int $maxY, bool $crop = false, bool $expand = false, int $cacheTime = 10, int $quality = 0, bool $keepType = true): bool|string
{
    if (!cFileHandler::exists($img)) {
        return false;
    }

    $clientConfig = cRegistry::getClientConfig(cRegistry::getClientId());

    $fileType = cString::toLowerCase(cFileHandler::getExtension($img));
    $md5 = cApiImgScaleGetMD5CacheFile($img, $maxX, $maxY, $crop, $expand);
    $cacheFileName = cApiImageGetCacheFileName($md5, $fileType, $keepType);
    $cacheFile = $clientConfig['cache']['path'] . $cacheFileName;
    $webFile = cRegistry::getFrontendUrl() . 'cache' . DIRECTORY_SEPARATOR . $cacheFileName;

    if (cApiImageCheckCachedImageValidity($cacheFile, $cacheTime)) {
        return $webFile;
    }

    $imageHandle = cApiImgCreateImageResourceFromFile($img, $fileType);
    if (!$imageHandle) {
        return false;
    }

    $x = imagesx($imageHandle);
    $y = imagesy($imageHandle);

    if ($crop) {
        $targetImage = imagecreatetruecolor($maxX, $maxY);
    } else {
        list($targetX, $targetY) = cApiImageGetTargetDimensions($x, $y, $maxX, $maxY, $expand);
        $targetImage = imagecreatetruecolor($targetX, $targetY);
    }

    if ($fileType == 'gif' || $fileType == 'png' || $fileType == 'webp') {
        // Turn off alpha blending
        imagealphablending($imageHandle, false);
        imagealphablending($targetImage, false);
    }

    if ($crop) {
        // calculate canter of the image
        $srcX = ($x - $maxX) / 2;
        $srcY = ($y - $maxY) / 2;

        // crop image from center
        imagecopy($targetImage, $imageHandle, 0, 0, $srcX, $srcY, $maxX, $maxY);
    } else {
        imagecopyresampled($targetImage, $imageHandle, 0, 0, 0, 0, $targetX, $targetY, $x, $y);
    }

    if ($fileType == 'gif' || $fileType == 'png' || $fileType == 'webp') {
        // Set alpha flag
        imagesavealpha($png, true);
        imagesavealpha($targetImage, true);
    }

    // Save the cache file
    cApiImgSaveImageResourceToFile($targetImage, $cacheFile, $quality, $fileType, $keepType);

    // Return the web file
    return $webFile;
}

// ToDo
function cApiImgScaleImageMagick($img, $maxX, $maxY, $crop = false, $expand = false, $cacheTime = 10, $quality = 0, $keepType = false) {
    if (!cFileHandler::exists($img)) {
        return false;
    } elseif (isFunctionDisabled('escapeshellarg') || isFunctionDisabled('exec')) {
        return false;
    }

    $cfgClient = cRegistry::getClientConfig();
    $client = cRegistry::getClientId();

    $fileName = $img;
    $maxX = cSecurity::toInteger($maxX);
    $maxY = cSecurity::toInteger($maxY);
    $cacheTime = cSecurity::toInteger($cacheTime);

    $frontendURL = cRegistry::getFrontendUrl();
    $fileType = cFileHandler::getExtension($fileName);
    $md5 = cApiImgScaleGetMD5CacheFile($img, $maxX, $maxY, $crop, $expand);
    $cacheFileName = cApiImageGetCacheFileName($md5, $fileType, $keepType);
    $cacheFile = $cfgClient[$client]['cache']['path'] . $cacheFileName;
    $webFile = $frontendURL . 'cache/' . $cacheFileName;

    if (cApiImageCheckCachedImageValidity($cacheFile, $cacheTime)) {
        return $webFile;
    }

    list($x, $y) = @getimagesize($fileName);
    if ($x == 0 || $y == 0) {
        return false;
    }

    list($targetX, $targetY) = cApiImageGetTargetDimensions($x, $y, $maxX, $maxY, $expand);

    // If is animated gif resize first frame
    if ($fileType == 'gif') {
        if (cApiImageIsAnimGif($fileName)) {
            $fileName .= '[0]';
        }
    }

    $cfg = cRegistry::getConfig();

    // Try to execute convert
    $output = [];
    $retVal = 0;
    $convertCommand = $cfg['images']['image_magick']['command'];
    $program = escapeshellarg($cfg['images']['image_magick']['path'] . $convertCommand);
    $source = escapeshellarg($fileName);
    $destination = escapeshellarg($cacheFile);
    $quality = cApiImgGetCompressionRate($fileType, $quality);
    if ($crop) {
        $cmd = "'{$program}' -gravity center -quality {$quality} -crop {$maxX}x{$maxY}+1+1 '{$source}' '{$destination}'";
    } else {
        $cmd = "'{$program}' -quality {$quality} -geometry {$targetX}x{$targetY} '{$source}' '{$destination}'";
    }

    exec($cmd, $output, $retVal);

    if (!cFileHandler::exists($cacheFile)) {
        return false;
    } else {
        return $webFile;
    }
}


function cApiImageCheckImageEditingPossibility(): string
{
    if (class_exists("Imagick")) {
        return 'im';
    } else {
        if (extension_loaded('gd')) {
            if (function_exists('gd_info')) {
                $gdInfo = gd_info();

                if (preg_match('#([0-9.])+#', $gdInfo['GD Version'], $strGDVersion)) {
                    if ($strGDVersion[0] >= '2') {
                        return '2';
                    }
                    return '1';
                }
                return '1';
            }
            return '1';
        }
        return '0';
    }
}

/**
 * @param string $imgType
 * @param int $quality
 * @return int
 */
function cApiImgGetCompressionRate(string $imgType, int $quality = 0): int
{
    if ($quality <= 0 || $quality > 100) {
        $quality = 75;
    }

    switch (cString::toLowerCase($imgType)) {
        case 'png':
            // Convert compression rate to PNG compression level
            $quality = ($quality - 100) / 11.111111;
            $quality = round(abs($quality));
            break;
        case 'svg':
        case 'webp':
        case 'jpg':
        case 'jpeg':
            // No action needed for jpg or jpeg
            break;
        default:
            $quality = 0;
    }

    return $quality;
}

/**
 * Returns the MD5 filename used for caching.
 *
 * @param string $img Path to image
 * @param int $maxX Maximum image x size
 * @param int $maxY Maximum image y size
 * @param bool $crop Flag to crop image
 * @param bool $expand Flag to expand image
 * @return false|string Path to the resulting image or false if image file does not exists
 */
function cApiImgScaleGetMD5CacheFile(string $img, int $maxX, int $maxY, bool $crop, bool $expand): bool|string
{
    if (!cFileHandler::exists($img)) {
        return false;
    }

    $fileSize = filesize($img);
    $nameParts = [$img, $fileSize, $maxX, $maxY, $crop, $expand];

    if (function_exists('md5_file')) {
        array_shift($nameParts);
        array_unshift($nameParts, $img, md5_file($img));
    }

    return md5(implode("", $nameParts));
}

/**
 * Returns cache file name.
 *
 * @param string $md5
 * @param string $fileType
 * @param bool $keepType
 * @return string
 */
function cApiImageGetCacheFileName(string $md5, string $fileType, bool $keepType): string
{
    if ($keepType) {
        switch (cString::toLowerCase($fileType)) {
            case 'png':
                $fileName = $md5 . '.png';
                break;
            case 'gif':
                $fileName = $md5 . '.gif';
                break;
            case 'webp':
                $fileName = $md5 . '.webp';
                break;
            default:
                $fileName = $md5 . '.jpg';
        }
    } else {
        $fileName = $md5 . '.jpg';
    }

    return $fileName;
}

/**
 * Validates cache version of a image.
 *
 * @param string $cacheFile
 * @param int $cacheTime
 * @return bool Returns true, if cache file exists and/or is still valid or false
 */
function cApiImageCheckCachedImageValidity(string $cacheFile, int $cacheTime): bool
{
    if (cFileHandler::exists($cacheFile)) {
        if ($cacheTime == 0) {
            return true;
        } else {
            if ((filemtime($cacheFile) + (60 * $cacheTime)) < time()) {
                unlink($cacheFile);
            } else {
                return true;
            }
        }
    }
    return false;
}

/**
 * Returns image resource by file name.
 *
 * @param string $fileName Path to image
 * @param string|null $fileType File type (extension)
 * @return resource|null Created image resource or null
 */
function cApiImgCreateImageResourceFromFile(string $fileName, string $fileType = null)
{
    if (!cFileHandler::exists($fileName)) {
        return null;
    }

    if (is_null($fileType)) {
        $fileType = cFileHandler::getExtension($fileName);
    }

    // Find out which file we have
    switch (cString::toLowerCase($fileType)) {
        case 'gif':
            $function = 'imagecreatefromgif';
            break;
        case 'png':
            $function = 'imagecreatefrompng';
            break;
        case 'jpeg':
        case 'jpg':
            $function = 'imagecreatefromjpeg';
            break;
        case 'webp':
            $function = 'imagecreatefromwebp';
            break;
        default:
            return null;
    }

    return function_exists($function) ? @$function($fileName) : null;
}

/**
 * Saves the given image resource to file
 *
 * @param GdImage $targetImage
 * @param string $saveTo
 * @param int $quality
 * @param string $fileType
 * @param bool $keepType
 * @return bool
 */
function cApiImgSaveImageResourceToFile($targetImage, $saveTo, $quality, $fileType, $keepType): bool
{
    // save the file
    if ($keepType) {
        switch (cString::toLowerCase($fileType)) {
            case 'png':
                $quality = cApiImgGetCompressionRate($fileType, $quality);
                return imagepng($targetImage, $saveTo, $quality);
            case 'gif':
                return imagegif($targetImage, $saveTo);
            case 'webp':
                $quality = cApiImgGetCompressionRate($fileType, $quality);
                return imagewebp($targetImage, $saveTo, $quality);
            default:
                $quality = cApiImgGetCompressionRate($fileType, $quality);
                return imagejpeg($targetImage, $saveTo, $quality);
        }
    } else {
        $quality = cApiImgGetCompressionRate($fileType, $quality);
        return imagejpeg($targetImage, $saveTo, $quality);
    }
}