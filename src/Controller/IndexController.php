<?php
/**
 * Phire Cache Module
 *
 * @link       https://github.com/phirecms/phire-content
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Cache\Controller;

use Phire\Cache\Model;
use Phire\Controller\AbstractController;

/**
 * Cache Index Controller class
 *
 * @category   Phire\Cache
 * @package    Phire\Cache
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $this->prepareView('cache/index.phtml');
        $cache = new Model\Cache();

        if ($this->request->isPost()) {
            $cache->save($this->request->getPost());
            $this->sess->setRequestValue('saved', true);
            $this->redirect(BASE_PATH . APP_URI . '/cache');
        } else {
            $this->view->title       = 'Cache Configuration';
            $this->view->cacheConfig = $cache->getConfig();
        }

        $this->send();
    }

    /**
     * Prepare view
     *
     * @param  string $member
     * @return void
     */
    protected function prepareView($member)
    {
        $this->viewPath = __DIR__ . '/../../view';
        parent::prepareView($member);
    }

}
