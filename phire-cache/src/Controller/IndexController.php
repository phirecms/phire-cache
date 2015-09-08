<?php

namespace Phire\Cache\Controller;

use Phire\Cache\Model;
use Phire\Controller\AbstractController;

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
            $this->sess->setRequestValue('saved', true, 1);
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
