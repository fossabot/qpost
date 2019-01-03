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
namespace qpost\View;

use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;

/**
 * A view class that is used for AJAX responses.
 * Currently only switches the default layout and sets the response type -
 * which just maps to text/html by default.
 */
class AjaxView extends AppView
{

    /**
     * The name of the layout file to render the view inside of. The name
     * specified is the filename of the layout in /src/Template/Layout without
     * the .ctp extension.
     *
     * @var string
     */
    public $layout = 'ajax';

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(){
        parent::initialize();

        $this->response = $this->response->withType('ajax');
    }
}
