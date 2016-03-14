# Getting Started

## Composer
Download composer in order to download the required dependencies.

Composer can be found at http://getcomposer.org/download/ OR you can download it using curl

```bash
curl -s https://getcomposer.org/installer | php
```

Then use composer to start a new surebert web project with the framework modules required.
```bash
composer create-project surebert/webapp-basic -s dev $YOUR_APPLICATION_NAME 
```

# Serving your project
Once they are installed you can begin working on your project.  You will need to serve your applications /public
directory.  This can be done via apache on nginx in production but you can simply user php 5.4 new built in server for testing.  To do so cd into the /public directory and type

```bash
cd /PATH/TO/YOUR/PROJECT/public
php -S 127.0.0.1:9000
```

Immediately you will see you projects /private/view/home/index.view file served.

If serving with apache add the site to your httpd.conf or httpd-vhosts.conf file
```text
<VirtualHost *:80>
    ServerName YOUR.DOMAIN
    DocumentRoot /PATH/TO/$YOUR_APPLICATION_NAME/public
    CustomLog /PATH/TO/$YOUR_APPLICATION_NAME_access.log combined
    ErrorLog  /PATH/TO/$YOUR_APPLICATION_NAME_error.log
    RewriteEngine On
    RewriteBase /
    RewriteCond %{SCRIPT_FILENAME} !-f
    RewriteCond %{SCRIPT_FILENAME} !-d
    RewriteRule .* index.php [L]
</VirtualHost>
```

For more information see the wiki on gitlab https://gitlab.com/paulvisco/surebert-framework/wikis/home