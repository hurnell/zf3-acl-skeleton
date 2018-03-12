<?php

/**
 * Class AuthManagerServiceTest 
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Mail;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ServiceMockBuilder;
use AclUser\Mail\MailMessage;
use org\bovigo\vfs\vfsStream;

/**
 * Test various aspects of AclUser\Service\AuthManager
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class MailMessageServiceTest extends AbstractHttpControllerTestCase
{

    protected $builder;
    protected $email = 'email@mailserver.com';
    protected $name = 'Mail Recipient Name';
    protected $subject = 'Email Subject';

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        $this->setApplicationConfig(ServiceMockBuilder::getConfig());
        parent::setUp();
        $this->builder = new ServiceMockBuilder($this);
        $this->builder->initialiseEntityManagerMock();
        $this->vfs = vfsStream::setup('root', null, $this->builder->getFakeDirectoryStructure());
    }

    public function testMailMessageCanSetRecipientSuccessfully()
    {
        $mailMessage = $this->builder->getMailMessageService();
        $mailMessage->setTo($this->email, $this->name);
        $this->assertTrue($mailMessage->getTo() instanceof \Zend\Mail\AddressList, 'after setTo get to should return instance of \Zend\Mail\AddressList');
        $this->assertTrue($mailMessage->getTo()->count() == 1, 'Mail message should have only one recipient after setTo is called on MailMessage');
        $this->assertTrue($mailMessage->getTo()->current() instanceof \Zend\Mail\Address, '$mailMessage->getTo()->current() should return instance of  \Zend\Mail\Address');
        $this->assertEquals($this->email, $mailMessage->getTo()->current()->getEmail(), 'get current email should return same email that was passed to setTo mothod');
        $this->assertEquals($this->name, $mailMessage->getTo()->current()->getName(), 'get current name should return same name that was passed to setTo mothod');
    }

    public function testMailMessageCanSetSubjectSuccessfully()
    {
        $mailMessage = $this->builder->getMailMessageService();
        $mailMessage
                ->setTo($this->email, $this->name)
                ->setSubject($this->subject);
        $this->assertEquals($this->subject, $mailMessage->getSubject(), 'mail message get subject should return same value as was passed in setSubject');
    }

    public function testMailMessageObjectPropertyViewScriptCanBeSet()
    {
        $mailMessage = $this->builder->getMailMessageService();
        $viewScript = 'test-email.phtml';
        $mailMessage
                ->setTo($this->email, $this->name)
                ->setSubject($this->subject)
                ->setViewScript($viewScript);
        $reflector = new \ReflectionObject($mailMessage);
        $this->assertClassHasAttribute('viewScript', MailMessage::class);
        $this->expectException(\ReflectionException::class);
        $protectedProperty = $reflector->getProperty('viewScript');
        $this->expectExceptionMessage('Cannot access non-public member AclUser\Mail\MailMessage::viewScript');
        $protectedProperty->getValue();
        $protectedProperty->setAccessible(true);
        $this->assertEquals($viewScript, $protectedProperty->getValue());
    }

    public function testMailMessageObjectPropertyLayoutTemplateCanBeSet()
    {
        $mailMessage = $this->builder->getMailMessageService();
        $layoutTemplate = 'test-email';
        $mailMessage->setLayoutTemplate($layoutTemplate);
        $reflector = new \ReflectionObject($mailMessage);
        $this->assertClassHasAttribute('layoutTemplate', MailMessage::class);
        $this->expectException(\ReflectionException::class);
        $protectedProperty = $reflector->getProperty('layoutTemplate');
        $this->expectExceptionMessage('Cannot access non-public member AclUser\Mail\MailMessage::layoutTemplate');
        $protectedProperty->getValue();
        $protectedProperty->setAccessible(true);
        $this->assertEquals($layoutTemplate, $protectedProperty->getValue());
    }

    public function testMailMessageObjectPropertyViewParamsCanBeSet()
    {
        $mailMessage = $this->builder->getMailMessageService();
        $viewParams = ['user' => 'user::class', 'token' => 'token', 'social' => false];
        $mailMessage->setViewParams($viewParams);
        $reflector = new \ReflectionObject($mailMessage);
        $this->assertClassHasAttribute('viewParams', MailMessage::class);
        $this->expectException(\ReflectionException::class);
        $protectedProperty = $reflector->getProperty('viewParams');
        $this->expectExceptionMessage('Cannot access non-public member AclUser\Mail\MailMessage::viewParams');
        $protectedProperty->getValue();
        $protectedProperty->setAccessible(true);
        $this->assertEquals($viewParams, $protectedProperty->getValue());
    }

    public function testMailMessageThrowsExceptionWhenTryingToAssignInlineImagesWithInvalidFormat()
    {
        $mailMessage = $this->builder->getMailMessageService(false);
        $inlineImages = [['type' => 'image/png', 'filepath' => 'path to first image'], ['type' => 'image/png', 'filpath' => 'path to second image']];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('inline images array not in correct format');
        $mailMessage->setInlineImages($inlineImages);
    }

    public function testMailMessageObjectPropertyInlineImagesCanBeSet()
    {
        $mailMessage = $this->builder->getMailMessageService(true, false, false);
        $inlineImages = [['type' => 'image/png', 'filepath' => 'path to first image'], ['type' => 'image/png', 'filepath' => 'path to second image']];
        $mailMessage->setInlineImages($inlineImages);
        $reflector = new \ReflectionObject($mailMessage);
        $this->assertClassHasAttribute('inlineImages', MailMessage::class);
        $this->expectException(\ReflectionException::class);
        $protectedProperty = $reflector->getProperty('inlineImages');
        $this->expectExceptionMessage('Cannot access non-public member AclUser\Mail\MailMessage::inlineImages');
        $protectedProperty->getValue();
        $protectedProperty->setAccessible(true);
        $this->assertEquals($inlineImages, $protectedProperty->getValue());
    }

    public function testMailMessageThrowsExceptionWhenTryingToAssignLayoutImagesWithInvalidFormat()
    {
        $mailMessage = $this->builder->getMailMessageService(false);
        $layoutImages = [['type' => 'image/png', 'filepath' => 'path to first image'], ['type' => 'image/png', 'filpath' => 'path to second image']];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('layout images array not in correct format');
        $mailMessage->setLayoutImages($layoutImages);
    }

    public function testMailMessageObjectPropertyLayoutImagesCanBeSet()
    {
        $mailMessage = $this->builder->getMailMessageService(true, false);
        $layoutImages = [['type' => 'image/png', 'filepath' => 'path to first image'], ['type' => 'image/png', 'filepath' => 'path to second image']];
        $mailMessage->setLayoutImages($layoutImages);
        $reflector = new \ReflectionObject($mailMessage);
        $this->assertClassHasAttribute('layoutImages', MailMessage::class);
        $this->expectException(\ReflectionException::class);
        $protectedProperty = $reflector->getProperty('layoutImages');
        $this->expectExceptionMessage('Cannot access non-public member AclUser\Mail\MailMessage::layoutImages');
        $protectedProperty->getValue();
        $protectedProperty->setAccessible(true);
        $this->assertEquals($layoutImages, $protectedProperty->getValue());
    }

    public function testMailMessageObjectPropertyAttachmentsCanBeSet()
    {
        $mailMessage = $this->builder->getMailMessageService(false);
        $attachments = [['type' => 'attachment/pdf', 'filepath' => 'path to pdf'], ['type' => 'attachment/pdf', 'filpath' => 'path to second pdf']];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('attachment array not in correct format');
        $mailMessage->setAttachments($attachments);
    }

    public function testMailMessageThrowsExceptionWhenTryingToAssignAttachmentsWithInvalidFormat()
    {
        $mailMessage = $this->builder->getMailMessageService();
        $attachments = [['type' => 'attachment/pdf', 'filepath' => 'path to pdf'], ['type' => 'attachment/pdf', 'filepath' => 'path to second pdf']];
        $mailMessage->setAttachments($attachments);
        $reflector = new \ReflectionObject($mailMessage);
        $this->assertClassHasAttribute('attachments', MailMessage::class);
        $this->expectException(\ReflectionException::class);
        $protectedProperty = $reflector->getProperty('attachments');
        $this->expectExceptionMessage('Cannot access non-public member AclUser\Mail\MailMessage::attachments');
        $protectedProperty->getValue();
        $protectedProperty->setAccessible(true);
        $this->assertEquals($attachments, $protectedProperty->getValue());
    }

    public function testMailMessageCanSetInlineImages()
    {
        $mailMessage = $this->builder->getMailMessageService();
        $viewScript = 'test-email';
        $viewParams = ['user' => $this->builder->getNewUser(), 'token' => 'token', 'social' => false];
        $mailMessage->setViewScript($viewScript)
                ->setViewParams($viewParams)
                ->setLayoutTemplate('layout/email-layout-no-images')
                ->setInlineImages($this->builder->getInlineImages())
                ->embedImageFromSrc()
                ->sendEmailBasedOnViewScript();
        $this->checkParts($mailMessage->getBody()->getParts(), ['image/png' => ['getFileName' => ['viewImageOne.png', 'viewImageTwo.png']]]);
    }

    public function testMailMessageCanSetAttachments()
    {
        $mailMessage = $this->builder->getMailMessageService();

        $viewParams = ['user' => $this->builder->getNewUser(), 'token' => 'token', 'social' => false];
        $attachments = [
            'pdf-one' => ['type' => 'attachment/pdf', 'filepath' => vfsStream::url('root/path/to/pdfs/pdfOne.pdf')],
            'pdf-two' => ['type' => 'attachment/pdf', 'filepath' => vfsStream::url('root/path/to/pdfs/pdfTwo.pdf')],
        ];
        $mailMessage->setViewScript('test-email-no-images')
                ->setViewParams($viewParams)
                ->setLayoutTemplate('layout/email-layout-no-images')
                ->setAttachments($attachments)
                ->sendEmailBasedOnViewScript();
        $this->checkParts($mailMessage->getBody()->getParts(), ['attachment/pdf' => ['getFileName' => ['pdfOne.pdf', 'pdfTwo.pdf']]]);
    }

    public function testMailMessageCanSetLayoutImages()
    {
        $mailMessage = $this->builder->getMailMessageService();

        $viewParams = ['user' => $this->builder->getNewUser(), 'token' => 'token', 'social' => false];
        $mailMessage->setViewScript('test-email-no-images')
                ->setViewParams($viewParams)
                ->setLayoutImages($this->builder->getLayoutImages())
                ->sendEmailBasedOnViewScript();
        $this->checkParts($mailMessage->getBody()->getParts(), ['image/png' => ['getFileName' => ['layoutImageOne.png', 'layoutImageTwo.png']]]);
    }

    public function testMailMessageThrowsErrorWhenSendMailIsUsedInTest()
    {
        $mailMessage = $this->builder->getMailMessageService(false);

        $viewScript = 'test-email';
        $viewParams = ['user' => $this->builder->getNewUser(), 'token' => 'token', 'social' => false];
        $mailMessage->setViewScript($viewScript)
                ->setViewParams($viewParams)
                ->setInlineImages($this->builder->getInlineImages())
                ->setLayoutImages($this->builder->getLayoutImages());
        $this->expectException(\Zend\Mail\Transport\Exception\RuntimeException::class);

        $mailMessage->sendEmailBasedOnViewScript();
    }

    // */

    public function testExceptionIsThrownWhenNoViewScriptHasBeenSet()
    {
        $mailMessage = $this->builder->getMailMessageService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You must set the view script');
        $mailMessage->setLayoutImages($this->builder->getLayoutImages())
                ->sendEmailBasedOnViewScript();
    }

    public function testEmbedFromSrc()
    {
        $viewScript = 'test-email';
        $layout = 'layout/inline-email-layout';
        $viewParams = ['user' => $this->builder->getNewUser(), 'token' => 'token', 'social' => false];
        $mailMessage = $this->builder->getMailMessageService();
        $mailMessage
                ->embedImageFromSrc()
                ->setViewScript($viewScript)
                ->setLayoutTemplate($layout)
                ->setViewParams($viewParams)
                ->setInlineImages($this->builder->getInlineImages())
                ->sendEmailBasedOnViewScript();
        $this->checkParts($mailMessage->getBody()->getParts(), ['image/png' => ['getFileName' => ['viewImageOne.png', 'viewImageTwo.png', 'five.png', 'six.png']]]);
    }

    protected function checkParts($mimeParts, $testParts)
    {
        $inline = false;
        $inlineImages = [];
        foreach ($mimeParts as $part) {
            if ($part->getDisposition() === 'inline') {
                $inline = true;
                $inlineImages[] = $part->getId();
            }
            if (array_key_exists($part->getType(), $testParts)) {
                $tests = $testParts[$part->getType()];
                $this->carryOutTests($part, $tests);
            } else if ('text/html' == $part->getType() && !$inline) {
                $content = str_replace(["=\n"], '', $part->getContent());
                $this->assertContains('<p id=3D"not-inline">This layout template has no inline images</p>', $content);
                $this->assertContains('Peter Parker', $content);
            } else if ('text/html' == $part->getType()) {
                $content = str_replace(["=\n"], '', $part->getContent());
                foreach ($inlineImages as $inlineImage) {
                    $this->assertContains('src=3D"cid:' . $inlineImage . '"', $content);
                }
            } else {
                echo "\n" . $part->getType();
            }
        }
    }

    protected function carryOutTests($part, $tests)
    {
        $values = array_keys($tests);
        foreach ($tests as $method => $values) {
            $this->assertContains($part->$method(), $values);
        }
    }

}
