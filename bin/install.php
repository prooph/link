<?php
/*
 * This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.02.15 - 21:45
 */
chdir(dirname(__DIR__));

if (! file_exists('vendor/autoload.php')) {
    echo "Please run 'php composer.phar install' first!\n";
    exit(1);
}

copy('config/autoload/local.php.dist', 'config/autoload/local.php');

if (! file_exists('config/autoload/local.php')) {
    echo "Failed to rename 'config/autoload/local.php.dist' to 'config/autoload/local.php'\nPlease check the write permissions and run the script again!\n";
    exit(1);
}

echo "prooph link was successfully installed. You can run the app now. Enjoy!\n";

exit(0);
