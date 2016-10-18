<?php

/**
 * Class ApiController
 */
class ApiController extends AppController
{

    public $uses = false;

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    public function index()
    {
        // Dummy controller to allow access to swagger 2.0 API page
        $this->layout = 'wide';
    }
}
