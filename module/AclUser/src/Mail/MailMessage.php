<?php

/**
 * Class MailMessage
 *
 * @package     AclUser\Mail
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Mail;

use Zend\Mail\Message as ZendMailMessage;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Model\ViewModel;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as ZendMimePart;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Dom\Query;

/**
 * Class MailMessage send html mail messages based on view script
 * 
 * @package     AclUser\Mail
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class MailMessage extends ZendMailMessage
{

    /**
     * The PhpRender used to render view script
     * 
     * @var PhpRenderer
     */
    protected $renderer;

    /**
     * Transport used to send message
     * 
     * @var Zend\Mail\Transport\TransportInterface 
     */
    protected $transport;

    /**
     * The view script used for the e-mail message
     * 
     * @var string 
     */
    protected $viewScript;

    /**
     * Keep track of the number of inline images so that the correct mime type can be assigned
     * 
     * @var integer the number of inline images
     */
    protected $totalImages = 0;

    /**
     * The layout script used for this e-mail message
     * 
     * @var string default layout template
     */
    protected $layoutTemplate = 'layout/email-layout';

    /**
     * An array of key - value pairs to be inserted into the view script
     * 
     * @var array 
     */
    protected $viewParams = [];

    /**
     * Array of inline images with image type and file location to be added the the e-mail view
     * 
     * @var array 
     */
    protected $inlineImages = [];

    /**
     * Array of inline images with image type and file location to be added the the e-mail layout
     * 
     * @var array 
     */
    protected $layoutImages = [];

    /**
     * Array of attachments to be added to the message 
     * Tested with png, jpg, pdf and zip files
     * 
     * @var array 
     */
    protected $attachments = [];

    /**
     * Whether images should be embedded in the e-mail based on the IMG tag SRC attribute
     * IMG tag needs to have the class embed-image for this to take effect
     * 
     * @var boolean 
     */
    protected $embedImageFromSrc = false;

    /**
     * integer to avoid errors in CID value
     * 
     * @var integer 
     */
    protected $index = 1;

    /**
     * Constructor (requires ZF phprenderer)
     * 
     * @param PhpRenderer $renderer used to render view script
     */
    public function __construct(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Set transport using SMTP protocol
     * 
     * @param Zend\Mail\Transport\Smtp $transport transport used to send e-mail
     */
    public function setSmtpTransport(Smtp $transport)
    {
        $config = $transport->getOptions()->getConnectionConfig();
        if (array_key_exists('username', $config) && array_key_exists('sender',
                        $config)) {
            $this->setFrom($config['username'], $config['sender']);
        } else if (array_key_exists('username', $config)) {
            $this->setFrom($config['username'], $config['username']);
        } else {
            return;
        }
        $this->transport = $transport;
    }

    /**
     * Set the view script that should be used for this e-mail message
     * 
     * @param string $viewScript pointer to location of view script
     * @return $this AclUser\Mail\MailMessage 
     */
    public function setViewScript($viewScript)
    {
        $this->viewScript = $viewScript;
        return $this;
    }

    /**
     * Over ride default e-mail layout
     * 
     * {@source} 
     * @param string $layoutTemplate
     * @return $this AclUser\Mail\MailMessage 
     */
    public function setLayoutTemplate($layoutTemplate)
    {
        $this->layoutTemplate = $layoutTemplate;
        return $this;
    }

    /**
     * Set view params to be used within the e-mail message view script
     * 
     * @param array $viewParams key value pairs to be passed to view script
     * @return $this AclUser\Mail\MailMessage 
     * {@source}
     */
    public function setViewParams($viewParams)
    {
        $this->viewParams = $viewParams;
        return $this;
    }

    /**
     * Set inline attachments (of view script) to send with message
     * the key of each array must correspond to parameter passed to the view script
     * each item in array must contain array with keys type and filepath
     * that corresponds to parameters of Zend\Mime\Part parameters of same name
     * 
     * @source 
     * 
     * @param array $inlineImages  array in format given above
     * @return $this AclUser\Mail\MailMessage 
     * @throws \Exception if 
     */
    public function setInlineImages($inlineImages)
    {
        if (!$this->validateAttachmentFormat($inlineImages)) {
            throw new \Exception("inline images array not in correct format");
        }
        $this->inlineImages = $inlineImages;
        return $this;
    }

    /**
     * Specify inline images to be added to layout script 
     * the key of each array must correspond to the parameter passed to e-mail layout
     * each item in array must contain array with keys: type and filepath
     * that corresponds to Zend\Mime\Part parameters of same name.
     * 
     * Exception will be thrown if array's requirements are not met
     * 
     * @param array $layoutImages array in format given above
     * @return $this AclUser\Mail\MailMessage 
     * @throws \Exception 
     */
    public function setLayoutImages($layoutImages)
    {
        if (!$this->validateAttachmentFormat($layoutImages)) {
            throw new \Exception("layout images array not in correct format");
        }
        $this->layoutImages = $layoutImages;
        return $this;
    }

    /**
     * Set (non inline) attachments to send with message
     * each item in array must contain array with keys type and filepath
     * that corresponds to parameters of Zend\Mime\Part parameters of same name
     * TYPES:
     * PDF - application/pdf
     * ZIP - application/octet-stream
     * PNG - image/png
     * JPG - image/jpg
     * 
     * @param array $attachments array of attachments
     * @return $this AclUser\Mail\MailMessage 
     * @throws \Exception
     */
    public function setAttachments($attachments)
    {
        if (!$this->validateAttachmentFormat($attachments)) {
            throw new \Exception("attachment array not in correct format");
        }
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * Tell system whether to embed images based on IMG tag SRC attribute
     * note that the IMG tag must have a class of ember-image as well
     * 
     * @param boolean $embedImageFromSrc whether to search image src and embed images where possible
     * @return $this AclUser\Mail\MailMessage 
     */
    public function embedImageFromSrc($embedImageFromSrc = true)
    {
        $this->embedImageFromSrc = $embedImageFromSrc;
        return $this;
    }

    /**
     * Check whether images array passed to set methods has the correct format
     * 
     * @param array $attachments 
     * @return boolean
     */
    protected function validateAttachmentFormat($attachments)
    {
        $result = true;

        foreach ($attachments as $attachment) {
            if (!array_key_exists('type', $attachment) || !array_key_exists('filepath',
                            $attachment)) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Get the transport to be used to send the message
     * 
     * @return Zend\Mail\Transport\TransportInterface
     */
    protected function getTransport()
    {
        if (!isset($this->transport)) {
            return new Sendmail();
        }
        return $this->transport;
    }

    /**
     * Send the e-mail message based on the view script
     */
    public function sendEmailBasedOnViewScript()
    {
        $mimeMessage = $this->addAttachments($this->addInlinePartsToMimeMessage(new MimeMessage()));
        $this->setBody($mimeMessage);
        $mimeType = 0 === $this->totalImages ? Mime::TYPE_HTML : Mime::MULTIPART_MIXED;
        $this->getHeaders()->get('Content-Type')->setType($mimeType);
        $transport = $this->getTransport();
        $transport->send($this);
    }

    /**
     * Add all inline elements to the e-mail message
     * 
     * @param Zend\Mime\Message $mimeMessage
     * @return Zend\Mime\Message
     */
    protected function addInlinePartsToMimeMessage(MimeMessage $mimeMessage)
    {
        $view = new ViewModel($this->viewParams);
        if (!isset($this->viewScript)) {
            throw new \Exception("You must set the view script");
        }
        $view->setTemplate($this->viewScript);
        $this->addInlineImages($mimeMessage, $view);

        $content = $this->renderer->render($view);
        $viewLayout = new ViewModel();
        $viewLayout->setTemplate($this->layoutTemplate)
                ->setVariable('content', $content);
        $this->addLayoutImages($mimeMessage, $viewLayout);
        $rendered = $this->getImagesToEmbedFromSrc($this->renderer->render($viewLayout),
                $mimeMessage);
        $htmlMimePart = new ZendMimePart($rendered);
        $htmlMimePart->type = Mime::TYPE_HTML;
        $htmlMimePart->charset = 'utf-8';
        $htmlMimePart->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $mimeMessage->addPart($htmlMimePart);
        return $mimeMessage;
    }

    /**
     * Actually add the inline images to the layout of the e-mail message
     * 
     * @param Zend\Mime\Message $mimeMessage
     * @param ViewModel $viewLayout
     */
    protected function addLayoutImages(MimeMessage $mimeMessage, $viewLayout)
    {
        foreach ($this->layoutImages as $key => $layoutImage) {
            $image = $this->createMimePart(file_get_contents($layoutImage['filepath']),
                    basename($layoutImage['filepath']), $layoutImage['type'],
                    Mime::DISPOSITION_INLINE);
            $mimeMessage->addPart($image);
            $viewLayout->setVariable($key, $image->id);
            $this->totalImages++;
        }
    }

    /**
     * Actually add the inline images to the view of the e-mail message
     * 
     * @param Zend\Mime\Message $mimeMessage
     * @param ViewModel $view
     */
    protected function addInlineImages(MimeMessage $mimeMessage, $view)
    {
        foreach ($this->inlineImages as $key => $inlineImage) {
            $image = $this->createMimePart(file_get_contents($inlineImage['filepath']),
                    basename($inlineImage['filepath']), $inlineImage['type'],
                    Mime::DISPOSITION_INLINE);
            $mimeMessage->addPart($image);
            $view->setVariable($key, $image->id);
            $this->totalImages++;
        }
    }

    /**
     * Actually add the attachments of the e-mail message
     * 
     * @param Zend\Mime\Message $mimeMessage
     */
    protected function addAttachments(MimeMessage $mimeMessage)
    {
        foreach ($this->attachments as $attachment) {
            $emailAttachment = $this->createMimePart(
                    file_get_contents($attachment['filepath']),
                    basename($attachment['filepath']), $attachment['type'],
                    Mime::DISPOSITION_ATTACHMENT);
            $mimeMessage->addPart($emailAttachment);
        }
        return $mimeMessage;
    }

    /**
     * Search through IMG tags and embed them if embedImageFromSrc evaluates to true 
     * and the IMG tag has the embed-image class
     * 
     * @param string $rendered
     * @param MimeMessage $mimeMessage
     * @return string
     */
    protected function getImagesToEmbedFromSrc($rendered,
            MimeMessage $mimeMessage)
    {
        $chosenImagesNodes = [];
        if ($this->embedImageFromSrc) {
            $dom = new Query($rendered);
            $imageNodeList = $dom->execute('img');
            foreach ($imageNodeList as $imageNode) {
                /* only embed images with src attribute and with class embed-image */
                if ($imageNode->hasAttribute('src') && $imageNode->hasAttribute('class') && $imageNode->getAttribute('class') == 'embed-image') {
                    $chosenImagesNodes[] = $imageNode;
                }
            }
            $rendered = $this->completeEmbedImagesFromSrc($chosenImagesNodes,
                    $imageNodeList, $mimeMessage);
        }
        return $rendered;
    }

    /**
     * Actually embed inline images based on their source
     * 
     * @param array $chosenImagesNodes image nodes in document that need to have their src attribute substituted
     * @param Zend\Dom\NodeList $imageNodeList (all) image nodes in document
     * @param MimeMessage $mimeMessage
     * @return string the html e-mail string
     */
    protected function completeEmbedImagesFromSrc($chosenImagesNodes,
            $imageNodeList, MimeMessage $mimeMessage)
    {
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);

        foreach ($chosenImagesNodes as $imageNode) {
            $src = $imageNode->getAttribute('src');
            $content = $this->tryToGetImageFromSrc($src);
            if (false !== $content) {
                $mimePart = $this->createMimePart($content, basename($src),
                        $fileInfo->buffer($content), Mime::DISPOSITION_INLINE); //new ZendMimePart($content);
                $mimeMessage->addPart($mimePart);
                $imageNode->setAttribute('src', 'cid:' . $mimePart->id);
                $this->totalImages++;
            } else {
                $imageNode->parentNode->removeChild($imageNode);
            }
        }
        return $imageNodeList->getDocument()->saveHTML();
    }

    /**
     * Try to get image from file system based on IMG SRC attribute
     * Temporarily turn off warning reporting so that nothing appears in browser
     * when the image is not found.
     * 
     * @param string $src
     * @return boolean|image file contents
     */
    protected function tryToGetImageFromSrc($src)
    {
        /*
         * put application error reporting level in variable so that 
         * it can be set reinstated at end of function
         */
        $errorReportingLevel = error_reporting();
        error_reporting(E_ERROR | E_PARSE);
        $content = file_get_contents($src);
        if (false === $content) {
            $content = file_get_contents(PUBLIC_PATH . $src);
        }
        error_reporting($errorReportingLevel);
        return $content;
    }

    /**
     * Create individual mime part to be attached o the message
     * 
     * @param string $content the data of the file
     * @param string $filename the name of the file
     * @param string $type the mime type of the file
     * @param string $disposition disposition for (part) of header Content-Disposition
     * @return ZendMimePart
     */
    protected function createMimePart($content, $filename, $type, $disposition)
    {
        $mimePart = new ZendMimePart($content);
        $mimePart->id = md5(microtime() . $filename . $this->index++ . microtime());
        $mimePart->type = $type;
        $mimePart->filename = $filename;
        $mimePart->disposition = $disposition;
        $mimePart->encoding = Mime::ENCODING_BASE64;
        return $mimePart;
    }

}
