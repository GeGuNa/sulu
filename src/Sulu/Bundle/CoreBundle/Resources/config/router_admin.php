<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/*
 * This file implements rewrite rules for PHP built-in web server.
 *
 * See: http://www.php.net/manual/en/features.commandline.webserver.php
 */

\defined('SYMFONY_ENV') || \define('SYMFONY_ENV', \getenv('SYMFONY_ENV') ?: 'dev');

if (\is_file($_SERVER['DOCUMENT_ROOT'] . \DIRECTORY_SEPARATOR . $_SERVER['SCRIPT_NAME'])) {
    return false;
}

$_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . \DIRECTORY_SEPARATOR . 'admin.php';

require 'admin.php';
