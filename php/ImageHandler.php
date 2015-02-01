<?php
/**
	The MIT License (MIT)
	
	Copyright (c) 2015 Ignacio Nieto Carvajal
	
	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

namespace creamy;
require_once('CRMDefaults.php');

/**
 * Class to handle all image manipulation.
 */
class ImageHandler {
	
    function __construct() {
    }

	private function generateThumbnailForImage($imgSrc, $imageFileType) {
		//getting the image dimensions
		list($width, $height) = getimagesize($imgSrc);
		
		//saving the image into memory (for manipulation with GD Library)
		if ($imageFileType == "jpg" || $imageFileType == "jpeg") $myImage = imagecreatefromjpeg($imgSrc);
		else if ($imageFileType == "png") $myImage = imagecreatefrompng($imgSrc);
		else if ($imageFileType == "gif") $myImage = imagecreatefromgif($imgSrc);
		
		// calculating the part of the image to use for thumbnail
		if ($width > $height) {
		  $y = 0;
		  $x = ($width - $height) / 2;
		  $smallestSide = $height;
		} else {
		  $x = 0;
		  $y = ($height - $width) / 2;
		  $smallestSide = $width;
		}
		
		// copying the part into thumbnail
		$thumbSize = AVATAR_IMAGE_DEFAULT_SIZE;
		$thumb = imagecreatetruecolor($thumbSize, $thumbSize);
		imagecopyresampled($thumb, $myImage, 0, 0, $x, $y, $thumbSize, $thumbSize, $smallestSide, $smallestSide);
		
		//final output
		header('Content-type: image/jpeg');
		return $thumb;	
	}
	
	public function generateProcessedImageFileFromSourceImage($imgSrc, $imageFileType) {
		// generate a random image name file (and make sure it's not in use.
		if (empty($imageFileType)) $imageFileType = AVATAR_IMAGE_FILENAME_EXTENSION;
		$filename = $this->randomNameForAvatarImage(AVATAR_IMAGE_FILENAME_LENGTH, $imageFileType);
		while (file_exists(AVATAR_IMAGE_FILEDIR.$filename)) {
			$filename = $this->randomNameForAvatarImage(AVATAR_IMAGE_FILENAME_LENGTH);
		}
		// touch file (to lock it from other processes trying to write that same filename).
		touch(AVATAR_IMAGE_FILEDIR.$filename);
		
		// process source image, generating a square image.
		$thumb = $this->generateThumbnailForImage($imgSrc, $imageFileType);
		// if successful, write the image to the generated path and return it.
		if ($thumb) {
			imagejpeg($thumb, AVATAR_IMAGE_FILEDIR.$filename);
			return $this->realPathForImagePath(AVATAR_IMAGE_FILEDIR.$filename);
		}
		return NULL;
	}

	private function randomNameForAvatarImage($length, $imageFileType = AVATAR_IMAGE_FILENAME_EXTENSION) {
	    $key = '';
	    $keys = array_merge(range(0, 9), range('a', 'z'));
	
	    for ($i = 0; $i < $length; $i++) {
	        $key .= $keys[array_rand($keys)];
	    }
	
	    return AVATAR_IMAGE_FILENAME_PREFIX.$key.".".$imageFileType;
	}
	
	private function realPathForImagePath($imagePath) {
		if ($this->startsWith($imagePath, "../")) {
			return str_replace("../", "./", $imagePath);
		}
	}
		
	private function imagePathForRealPath($realPath) {
		if ($this->startsWith($realPath, "./")) {
			return str_replace("./", "../", $realPath);
		}
	}
	
	private function startsWith($haystack, $needle)
	{
	     $length = strlen($needle);
	     return (substr($haystack, 0, $length) === $needle);
	}
	
	public function removeUserAvatar($avatarpath) {
		$imagePath = $this->imagePathForRealPath($avatarpath);
		if (!$this->startsWith($imagePath, AVATAR_IMAGE_DEFAULT_FILEDIR)) { // don't remove default avatars.
			return unlink($imagePath);
		}
		else return true;
	}

}

?>