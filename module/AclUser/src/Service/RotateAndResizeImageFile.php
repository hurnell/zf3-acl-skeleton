<?php

/**
 * Class RotateAndResizeImageFile
 *
 * @package     AclUser\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUser\Service;

/**
 * Class that is used to apply users requested changes to the image that they
 * have uploaded as their profile picture
 * 
 * @package     AclUser\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class RotateAndResizeImageFile
{

    /**
     * An array of messages created when something goes wrong with the image manipulation
     * 
     * @var array
     */
    protected $errorMessages = [];

    /**
     * Location where the initial image has been saved
     * 
     * @var string 
     */
    protected $initialSaveLocation = './data/media/upload/';

    /**
     * the final path and (file) name where the image will end up
     * 
     * @var string  
     */
    protected $finalSavePath;

    /**
     * Rotate, resize image and convert to final image format and save where required
     * 
     * @param array $params merged post and file parameters
     * @param integer $userId user's id which will be the name of the file on success.
     * @return boolean whether the whole procedure was a success
     */
    public function rotateAndResize($params, $userId)
    {
        $fileName = $params['file-name'];
        $extension = $this->getExtension($fileName);
        $this->finalSavePath = $this->initialSaveLocation . $fileName;
        if ($this->checkParams($params) && $extension && $this->checkFileExists() ) {
            if ('0' !== $params['rotation']) {
                $source = $this->getSource($extension);
                $this->rotateImage($source, $fileName, $params['rotation'], $extension);
            }
            $src_image = $this->getSource($extension);
            $dst_image = imagecreatetruecolor($params['dst_w'], $params['dst_h']);
            imagecopyresampled($dst_image, $src_image, (int) $params['dst_x'], (int) $params['dst_y'], $params['src_x'], $params['src_y'], $params['dst_w'], $params['dst_h'], $params['src_w'], $params['src_h']);
            $imagePath = "./data/media/user-images/{$userId}.png";
            imagepng($dst_image, $imagePath);
            file_exists($this->finalSavePath) ? unlink($this->finalSavePath) : null;
            return true;
        }
        return false;
    }

    /**
     * Get an array of error message when something goes wrong
     * 
     * @return array containing error messages
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * Rotate the image and save to new location in rotated folder
     * the remove the original (unrotated) image file
     * 
     * @param resource $source image resource identifier
     * @param string $fileName the name of the file
     * @param integer $rotation rotation (multiple of 90)
     * @param string $extension the file extension (lowercase)
     */
    protected function rotateImage($source, $fileName, $rotation, $extension)
    {
        $original = $this->finalSavePath;
        $this->finalSavePath = $this->initialSaveLocation . 'rotated/' . $fileName;
        $rotate = imagerotate($source, - $rotation, 0);
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($rotate, $this->finalSavePath);
                break;
            case 'png':
                imagepng($rotate, $this->finalSavePath);
                break;
            case 'gif':
                imagegif($rotate, $this->finalSavePath);
                break;
        }

        file_exists($original) ? unlink($original) : null;
    }

    /**
     * Check whether all the parameters that are required by the class are present
     * 
     * @param array $params merged post and file parameters
     * @return boolean whether all parameters are present
     */
    public function checkParams($params)
    {
        $keys = ['file-name', 'dst_x', 'dst_y', 'src_x', 'src_y', 'dst_w', 'dst_h', 'src_w', 'src_h', 'rotation', 'ratio'];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $params)) {
                $this->errorMessages[] = 'One or more parameters were missing from the posted data.';
                return false;
            }
        }
        return true;
    }

    /**
     * Check whether the file exists on the server
     * 
     * @return boolean whether the file exists
     */
    protected function checkFileExists()
    {
        $fileExists = file_exists($this->finalSavePath);
        if (!$fileExists) {
            $this->errorMessages[] = 'The file has not been uploaded.';
        }
        return $fileExists;
    }

    /**
     * Get the extension (lowercase) or false if it is not supported
     * 
     * @param string $fileName the name of the file
     * @return string|false if the extension is not supported
     */
    protected function getExtension($fileName)
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $parts = explode('.', $fileName);
        $end = end($parts);
        $ext = strtolower($end);
        if (!in_array($ext, $extensions)) {
            $this->errorMessages[] = 'This type of file is not supported.';
        }
        return in_array($ext, $extensions) ? $ext : false;
    }

    /**
     * Get the Returns an image resource identifier.
     * 
     * @param string $extension
     * @return resource  an image resource identifier on success, FALSE on errors.
     */
    public function getSource($extension)
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($this->finalSavePath);
                break;
            case 'png':
                $source = imagecreatefrompng($this->finalSavePath);
                break;
            case 'gif':
                $source = imagecreatefromgif($this->finalSavePath);
                break;
        }
        return $source;
    }

}
