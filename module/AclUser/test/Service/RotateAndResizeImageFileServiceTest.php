<?php

/**
 * Class RotateAndResizeImageFileServiceTest 
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ServiceMockBuilder;
use Zend\Authentication\Result;
use AclUser\Service\RotateAndResizeImageFile;

/**
 * Test various aspects of AclUser\Service\RotateAndResizeImageFile
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class RotateAndResizeImageFileServiceTest extends AbstractHttpControllerTestCase
{

    protected $userId = 99999;
    protected $origin = __DIR__ . '/../Mocked/';
    protected $location = './data/media/upload/';
    protected $builder;
    protected $errorMessages = [];
    protected $badFile = 'This type of file is not supported.';
    protected $missingParams = 'One or more parameters were missing from the posted data.';
    protected $notUploaded = 'The file has not been uploaded.';

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        parent::setUp();
        $this->rotator = new RotateAndResizeImageFile();
    }

    protected function getErrorMessages()
    {
        $this->errorMessages = $this->rotator->getErrorMessages();
    }

    protected function copyImageOver($ext)
    {
        $name = $this->getFileName($ext);
        $imagepath = $this->origin . $name;
        if (realpath($imagepath)) {
            $loc = $this->location . $name;
            copy($imagepath, $loc);
        }
    }

    protected function getFileName($ext)
    {
        return $ext . '_image.' . $ext;
    }

    protected function deleteAllImages()
    {
        $exts = ['png', 'jpg', 'png'];
        foreach ($exts as $ext) {
            $name = $this->getFileName($ext);
            $this->deleteImage($this->location . $name);
            $this->deleteImage($this->location . 'rotated/' . $name);
        }
        $this->deleteImage("./data/media/user-images/{$this->userId}.png");
    }

    protected function deleteImage($imagepath)
    {
        if (file_exists($imagepath)) {
            unlink($imagepath);
        }
    }

    protected function getFileParams($ext, $rotation = '0')
    {
        $src_w = $src_h = 100;
        $name = $this->getFileName($ext);
        if (file_exists($this->origin . $name)) {
            list($src_w, $src_h) = getimagesize($this->origin . $name);
        }
        return [
            'file-name' => $name,
            'dst_x' => 0,
            'dst_y' => 0,
            'src_x' => 0,
            'src_y' => 0,
            'dst_w' => 120,
            'dst_h' => 160,
            'src_w' => $src_w,
            'src_h' => $src_h,
            'rotation' => $rotation,
            'ratio' => 1
        ];
    }

    public function testRotatorServiceWillReturnFalseWithEmptyParams()
    {
        $params = ['file-name' => 'noname.zip'];
        $result = $this->rotator->rotateAndResize($params, $this->userId);
        $this->getErrorMessages();
        $this->assertNotTrue($result, 'rotator should return false with empty params');
        $this->assertTrue(in_array($this->badFile, $this->errorMessages), 'rotator should have error message: ' . $this->badFile);
        $this->assertTrue(in_array($this->missingParams, $this->errorMessages), 'rotator should have error message: ' . $this->missingParams);
    }

    public function testRotatorServiceWillReturnFalseWithWrongImageType()
    {
        //$this->copyImageOver('png');
        $params = $this->getFileParams('zip');
        $result = $this->rotator->rotateAndResize($params, $this->userId);
        $this->getErrorMessages();
        $this->assertNotTrue($result, 'rotator should return false with empty params');
        $this->assertTrue(in_array($this->badFile, $this->errorMessages), 'rotator should have error message: ' . $this->badFile);
    }

    public function testRotatorServiceWillReturnFalseWhenFileNotUploaded()
    {
        $params = $this->getFileParams('png');
        $result = $this->rotator->rotateAndResize($params, $this->userId);
        $this->getErrorMessages();
        $this->assertNotTrue($result, 'rotator should return false when file not uploaded');
        $this->assertTrue(in_array($this->notUploaded, $this->errorMessages), 'rotator should have error message: ' . $this->notUploaded);
        $this->deleteAllImages();
    }

    public function testRotatorServiceWillReturnTrueWhenPngImageFileNotRotated()
    {
        $this->copyImageOver('png');
        $params = $this->getFileParams('png');
        $result = $this->rotator->rotateAndResize($params, $this->userId);
        $this->getErrorMessages();
        $this->assertTrue($result, 'rotator should return file was uploaded');
        $this->deleteAllImages();
    }

    public function testRotatorServiceWillReturnTrueWhenGifImageFileNotRotated()
    {
        $this->copyImageOver('gif');
        $params = $this->getFileParams('gif');
        $result = $this->rotator->rotateAndResize($params, $this->userId);
        $this->getErrorMessages();
        $this->assertTrue($result, 'rotator should return file was uploaded');
        $this->deleteAllImages();
    }

    public function testRotatorServiceWillReturnTrueWhenJpgImageFileNotRotated()
    {
        $this->copyImageOver('jpg');
        $params = $this->getFileParams('jpg');
        $result = $this->rotator->rotateAndResize($params, $this->userId);
        $this->getErrorMessages();
        $this->assertTrue($result, 'rotator should return file was uploaded');
        $this->deleteAllImages();
    }

    public function testRotatorServiceWillReturnTrueWhenPngImageFileRotated()
    {
        $this->copyImageOver('png');
        $params = $this->getFileParams('png', 90);
        $result = $this->rotator->rotateAndResize($params, $this->userId);
        $this->getErrorMessages();
        $this->assertTrue($result, 'rotator should return file was uploaded');
        $this->deleteAllImages();
    }

    public function testRotatorServiceWillReturnTrueWhenGifImageFileRotated()
    {
        $this->copyImageOver('gif');
        $params = $this->getFileParams('gif', 90);
        $result = $this->rotator->rotateAndResize($params, $this->userId);
        $this->getErrorMessages();
        $this->assertTrue($result, 'rotator should return file was uploaded');
        $this->deleteAllImages();
    }

    public function testRotatorServiceWillReturnTrueWhenJpgImageFileRotated()
    {
        $this->copyImageOver('jpg');
        $params = $this->getFileParams('jpg', 90);
        $result = $this->rotator->rotateAndResize($params, $this->userId);
        $this->getErrorMessages();
        $this->assertTrue($result, 'rotator should return file was uploaded');
        $this->deleteAllImages();
    }

}
