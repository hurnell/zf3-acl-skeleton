 ## Mail Message (AclUser\Mail\MailMessage)

The MailMessage service is available in the UserManager (AclUser\Service\UserManager).


### Embedded image

There are several ways of embedding images with MailMessage:

1. Specify the view-script images and layout template images by calling setInlineImages() and setLayoutImages() respectively:
```PHP
    $mailMessage->setTo('email@mailserver.com', 'Recipient Name')
                ->setSubject('Email Subject')
                ->setViewScript('path/to/view-script')
                ->setLayoutTemplate('layout/layout-template')
                ->setViewParams(['user' => $user])
                ->setLayoutImages(['logo' => ['type' => 'image/png', 'filepath' => './public/img/logo.png']])
                ->setInlineImages(['old' => ['type' => 'image/png', 'filepath' => './public/img/old.png'],
                                  ['new' => ['type' => 'image/png', 'filepath' => '/somewhere/on/server/new.png']])
                ->sendEmailBasedOnViewScript();
```
```HTML
    -- /layout/layout-template.phtml
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN>
    <html>
        <head>
            <style>logo{width: 200px;}</style>
        </head>
        <body>
            <img id="logo-image" src="cid:<?= $logo ?>" />
            <?= $this->content; ?>
        </body>
    </html>
```
```HTML
    -- /path/to/view-script.phtml
    <p><?= $this->translate('Dear'); ?> <?= $user->getFullName(); ?>,</p>
    <p><?= $this->translate('The image has been changed:'); ?>:</p>
    <img id="old-image" src="cid:<?= $old; ?>" />
    <img id="new-image" src="cid:<?= $new; ?>" />
```
2. Give images the 'embed-image' class then call embedImageFromSrc() before sendEmailBasedOnViewScript():
```PHP
    $mailMessage->setTo('email@mailserver.com', 'Recipient Name')
                ->setSubject('Email Subject')
                ->setViewScript('path/to/view-script')
                ->setLayoutTemplate('layout/layout-template')
                ->setViewParams(['user' => $user, 'products'=>$products])
                ->embedImageFromSrc()
                ->sendEmailBasedOnViewScript();
```
```HTML
    -- /layout/layout-template.phtml
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN>
    <html>
        <head>
            <style>logo{width: 200px;}</style>
        </head>
        <body>
            <img id="logo" class="embed-image" src="http://www.example.com/img/logo/e-mail-header.png" />
            <?= $this->content; ?>
            <img id="footer-image" class="embed-image" src="/img/logo.png" />
        </body>
    </html>
```
```HTML
    -- /path/to/view-script.phtml
    <p><?= $this->translate('Dear'); ?> <?= $user->getFullName() ?>,</p>
    <p><?= $this->translate('Overview of order:'); ?>:</p>
    <?php foreach($products as $product): ?>
        <img class="embed-image" src="<?= $product->getSource(); ?>" />
         getName ...
         getPrice ...
    <?php endforeach; ?>
```

### Linked Images

 - You can always just include an image as a link (not inline - usually requires user to click download images or simular):
```HTML
    -- /layout/layout-template.phtml
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN>
    <html>
        <head>
            <style>logo{width: 200px;}</style>
        </head>
        <body>
            <img id="logo" src="http://www.example.com/img/logo/e-mail-header.png" />
            <?= $this->content; ?>
        </body>
    </html>
```

### Attachments

 - You can also attach any sort of file
```PHP
    $mailMessage->setTo('email@mailserver.com', 'Recipient Name')
                ->setSubject('Email Subject')
                ->setViewScript('path/to/view-script')
                ->setLayoutTemplate('layout/layout-template')
                ->setViewParams(['user' => $user, 'products'=>$products])
                ->setAttachments([
                        ['type' => 'image/png', 'filepath' => '/path/to/image/file.png'],
                        ['type' => 'application/pdf', 'filepath' => '/path/to/pdf/file.pdf'],
                        ['type' => 'application/octet-stream', 'filepath' => '/path/to/zip/file.zip']
                ])
                ->embedImageFromSrc()
                ->sendEmailBasedOnViewScript();
```