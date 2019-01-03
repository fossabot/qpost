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
namespace qpost\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize(){
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        $this->loadComponent('Flash');

        $this->loadComponent('Security');
    }
}
