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

/*
 * You can empty out this file, if you are certain that you match all requirements.
 */

/*
 * You can remove this if you are confident that your PHP version is sufficient.
 */
if (version_compare(PHP_VERSION, '5.6.0') < 0) {
    trigger_error('Your PHP version must be equal or higher than 5.6.0 to use CakePHP.' . PHP_EOL, E_USER_ERROR);
}

/*
 * You can remove this if you are confident you have intl installed.
 */
if (!extension_loaded('intl')) {
    trigger_error('You must enable the intl extension to use CakePHP.' . PHP_EOL, E_USER_ERROR);
}

/*
 * You can remove this if you are confident you have mbstring installed.
 */
if (!extension_loaded('mbstring')) {
    trigger_error('You must enable the mbstring extension to use CakePHP.' . PHP_EOL, E_USER_ERROR);
}
