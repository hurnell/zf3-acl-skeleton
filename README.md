# Zend Framework 3 ACL Skeleton Web Application

This is a Zend Framework 3 application that incorporates the following modules:
 - XML based navigation.
   - Parts of Zend's navigation view helper (Zend\View\Helper\Navigation\Menu) have been overridden to format the site menu.
 - Role/resource/privilege based access control system based on Zend ACL (linked to XML Navigation).
   - Resources: Generally (but not necessarily exclusively) resources correspond to Controller aliases.
   - Privileges: Generally (but not exclusively) privileges correspond to actions.
 - E-mail templating system based on view scripts and layout templates which creates HTML e-mails with linked or embedded images.
   - [Please see the MailMessage README file for details](./module/AclUser/src/Mail/README.md)
 - Social Login module that allows users to Sign in and Register through their favourite social media platform.
   - Supported Social Providers include: Facebook, Foursquare, Gmail, GitHub, LinkedIn, Twitter, Yahoo, Yandex and Windows Live (HTTPS only).
 - Internationalisation and translation module.

A working version is available here: [http://skeleton.hurnell.com/](http://skeleton.hurnell.com/)
 - Please note that the database is reset every hour on the hour so any changes that you make will be overwritten. 

The application is fully documented (phpDocumentor v2) here: [http://docs.hurnell.com/zf3-acl-skeleton/](http://docs.hurnell.com/zf3-acl-skeleton/)

The unit tests (PHPUnit) can be viewed here: [http://docs.hurnell.com/skeleton-tests/](http://docs.hurnell.com/skeleton-tests/)

## Installation

First clone zf3-acl-skeleton and install using composer: 
 - cd into server-root -- I used -- /srv/web-content/subdomains/skeleton -- 
 - git clone https://github.com/hurnell/zf3-acl-skeleton.git .
 - php composer.phar update

If you are asked "Please select which config file you wish to inject ..... into": 
    
Choose [0] Do not inject

### Virtual Host

    Set up the virtual host

    <VirtualHost  *:80>
        ServerName skeleton.example.com
        DocumentRoot /srv/web-content/subdomains/skeleton/public
        SetEnv APPLICATION_ENV "development"        
        ErrorLog  ${APACHE_LOG_DIR}/skeleton_error.log
        CustomLog ${APACHE_LOG_DIR}/skeleton_access.log combined
        <Directory /srv/web-content/subdomains/skeleton/public>
            DirectoryIndex index.php
            AllowOverride  All
            Order allow,deny
            Allow from all
            Require all granted
        </Directory>
    </VirtualHost>


### Doctrine

First create the database:
 - mysql -u root -h localhost -pyour_password
 - CREATE DATABASE skeleton;
 - GRANT ALL PRIVILEGES ON skeleton.* TO 'skeleton_user'@'localhost' IDENTIFIED BY 'skeleton_password';
 - quit;

Then configure doctrine:
 - cp config/autoload/doctrine.local.php.dist config/autoload/doctrine.local.php
 - update the user, password and dbname parameters in doctrine.local.php
 - then from the root directory run: ./vendor/bin/doctrine-module migrations:migrate --no-interaction
 - (Note that the database credentials in config/autoload/doctrine.local.php are cached in the data/cache directory so if you make a mistake with the doctrine.local file then you will need to clear the cache files from this directory).

Now copy the configuration dist files in config/autoload: 
 - cp config/autoload/development.local.php.dist config/autoload/development.local.php
 - cp config/autoload/social-config.local.php.dist config/autoload/social-config.local.php
 - cp config/autoload/social-config.global.php.dist config/autoload/social-config.global.php

And if you decide to use SMTP in place of sendmail then
 - cp config/autoload/smtp.local.php.dist config/autoload/smtp.local.php and configure the setting in this file.

Finally update social-config.local.php with the client ids and secrets that you set up for the social providers that you enabled in social-config.global.php:
 - The callback URL will be http(s)://subdomain.server.com/en_GB/social/redirected/(provider_name)
 - CamelCase provider names are converted to underscore LinkedIn becomes linked_in, GitHub becomes git_hub etc.

Once you have the application up and running log in with admin account (credentials on home page) and enable one or two languages Translate -- System Languages.
   