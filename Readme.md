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

For more information see http://gitlab.com/paul.visco/webapp-basic

# Gateway Request Routing

The default request is represented by the \sb\Gateway::$request object. It is rendered as follows.

e.g. yoursite.com/foo

* /public/foo/index.html or index.php if it exists
* OR \Controllers\Foo::index() if it exists and is @servable true
* OR \Controllers\Index::foo(); if it is @servable true
* OR /views/foo/index.view if it exists and \sb\Gateway::$allow_direct_view_rendering is not false (default = false).
* OR \Controllers\Index:notFound();

e.g. yoursite.com/foo/bar/

* /public/foo/bar/index.html or index.php if it exists
* OR if \Controlers\Foo::bar(); exists call that
* OR if /views/foo/bar.view if it exists and \sb\Gateway::$allow_direct_view_rendering is not false (default = false) call that.
* OR if \Controllers\Foo exists but @servable true bar() method does not exist and foo/bar.view does not exist /Controllers/Foo::notFound();
* OR \Controllers\Index::foo(); if it is @servable true
* OR \Controllers\Index::notFound();

There is no additional nesting. Any additional /anything after the first slash is assigned to the $request's args array e.g. /blog/read/5.

If you have questions you can email paul.visco@gmail.com

