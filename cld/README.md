Compact Language Detector
===
CLD2 is an advanced language dectection library with a high reliability.

This addon depends on the CLD PHP module which is not included in any Linux distribution.
It needs to be built and installed by hand, which is not totally straightforward.

Prerequisite
---
To be able to build the extension, you need the CLD module and the files for the PHP module development.
On Debian you install the packages php-dev, libcld2-dev and libcld2-0.
Make sure to have installed the correct PHP version.
Means: When you have got both PHP 8.0 and 8.2 on your system, you have to install php8.0-dev as well.

Installation
---
The original PHP extension is https://github.com/fntlnz/cld2-php-ext.
However, it doesn't support PHP8.
So https://github.com/hiteule/cld2-php-ext/tree/support-php8 has to be used.

Download the source code:
```
wget https://github.com/hiteule/cld2-php-ext/archive/refs/heads/support-php8.zip
```

Unzip it:
```
unzip support-php8.zip
```

Change into the folder:
```
cd cld2-php-ext-support-php8/
```

Configure for the PHP Api version:
```
phpize
```
(if you have got several PHP versions on your system, execute the command with the version that you run Friendica with, e.g. `phpize8.0`)

Create the Makefile:
```
./configure --with-cld2=/usr/include/cld2
```

Have a look at the line `checking for PHP includes`.
When the output (for example `/usr/include/php/20220829` doesn't match the API version that you got from `phpize`, then you have to change all the version codes in your `Makefile` afterwards)

Create the module:
```
make -j
```

Install it:
```
sudo make install
```

Change to the folder with the available modules. When you use PHP 8.0 on Debian it is:
```
cd /etc/php/8.0/mods-available
```

Create the file `cld.ini` with this content:
```
; configuration for php cld2 module
; priority=20
extension=cld2.so
```

Change to the folder `conf.d` in the folder of your `php.ini`.
```
cd /etc/php/8.0/cgi/conf.d
```
This depends on the way you installed the PHP support for your webserver. Instead of `cgi` it could also be `apache2` or `fpm`.

Create a symbolic link to install the module:
```
ln -s /etc/php/8.0/mods-available/cld.ini
```

Then restart the apache or fpm (or whatever you use) to load the changed configuration.

Call `/admin/phpinfo` on your webserver.
You then see the PHP Info.
Search for "cld2".
The module is installed, when you find it here.
**Only proceed when the module is installed**

Now you can enable the addon.