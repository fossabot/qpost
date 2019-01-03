<?php
/**
 * qpost (https://qpost.gigadrivegroup.com)
 * Copyright (c) Gigadrive Group (https://gigadrivegroup.com)
 *
 * Licensed under The GNUv3 License
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Gigadrive Group (https://gigadrivegroup.com)
 * @link      https://qpost.gigadrivegroup.com qpost
 * @license   https://opensource.org/licenses/GPL-3.0 GNU GENERAL PUBLIC LICENSE
 */

// Check platform requirements
require dirname(__DIR__) . '/config/requirements.php';

// For built-in server
if (PHP_SAPI === 'cli-server') {
    $_SERVER['PHP_SELF'] = '/' . basename(__FILE__);

    $url = parse_url(urldecode($_SERVER['REQUEST_URI']));
    $file = __DIR__ . $url['path'];
    if (strpos($url['path'], '..') === false && strpos($url['path'], '.') !== false && is_file($file)) {
        return false;
    }
}
require dirname(__DIR__) . '/vendor/autoload.php';

use qpost\Application;
use Cake\Http\Server;

// Bind your application to the server.
$server = new Server(new Application(dirname(__DIR__) . '/config'));

// Run the request/response through the application and emit the response.
$server->emit($server->run());
