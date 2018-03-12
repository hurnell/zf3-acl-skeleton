<?php

/**
 * Class RawResponsePlugin
 *
 * @package     Application\Mvc\Controller\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Application\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Controller plugin that serves image, audio, PDF and ZIP files as a raw response
 *
 * @package     Application\Mvc\Controller\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class RawResponsePlugin extends AbstractPlugin
{
    /**
     * Serve audio file based on location and name of audio file contained in array
     * 
     * @param array $fileArray containing location of file and name of file
     * @return Response That corresponds to the raw audio data
     */
    /* LATER MAYBE
      public function serveAudio($fileArray) {
      $response = $this->controller->getResponse();
      if (file_exists($fileArray['filepath'])) {
      $response->getHeaders()
      ->addHeaderLine('Content-Type', 'audio/mpeg')
      ->addHeaderLine('Content-Disposition', 'filename="' . $fileArray['filename'] . '"')
      ->addHeaderLine('Content-Length', filesize($fileArray['filepath']))
      ->addHeaderLine('Cache-Control', 'no-cache')
      ->addHeaderLine('Content-Transfer-Encoding', 'chunked')
      ->addHeaderLine('Accept-Ranges', 'bytes');
      $response->setContent(file_get_contents($fileArray['filepath']));
      }
      return $response;
      }// */

    /**
     * Serve image file based on location
     * 
     * @param string $filepath absolute location of the file on the server
     * @return Response That corresponds to the raw image data
     */
    public function serveImage($filepath)
    {
        $response = $this->controller->getResponse();
        if (file_exists($filepath)) {
            $response->getHeaders()
                    ->addHeaderLine('Content-Type', 'image/jpeg');
            $response->setContent(file_get_contents($filepath));
        }
        return $response;
    }

    /**
     * Serve image file based on location
     * 
     * @param array $fileArray containing location of file and name of file
     * @return Response That corresponds to the raw PDF data
     */
    /* LATER MAYBE
      public function servePdf($fileArray) {
      $response = $this->controller->getResponse();
      if (file_exists($fileArray ['filepath'])) {
      $response->getHeaders()
      ->addHeaderLine('Content-Type', 'application/pdf')
      ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileArray['filename'] . '"');
      $response->setContent(file_get_contents($fileArray['filepath']));
      }
      return $response;
      }// */

    /**
     * Serve zip file based on location
     * 
     * @param array $fileArray containing location of file and name of file
     * @return Response That corresponds to the raw ZIP data
     */
    /* LATER MAYBE
      public function serveZip($fileArray)
      {
      $response = $this->controller->getResponse();
      if (file_exists($fileArray ['path'])) {
      $response->getHeaders()
      ->addHeaderLine('Content-Description', 'File Transfer')
      ->addHeaderLine('Content-Type', 'application/octet-stream')
      ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileArray['name'] . '"')
      ->addHeaderLine('Content-Transfer-Encoding', 'binary')
      ->addHeaderLine('Content-Length', filesize($fileArray['path']));
      $response->setContent(file_get_contents($fileArray['path']));
      }
      return $response;
      }// */
}
