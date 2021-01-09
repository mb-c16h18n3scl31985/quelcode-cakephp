<?php

namespace App\Controller;

use App\Controller\AppController;


class Hello2Controller extends AppController
{
    public function initialize()
    {
        $this->viewBuilder()->setLayout('hello2');
    }

    public function index(){
        $this->set('header',['subtitle'=>'from Controller']);
        $this->set('footer',['copyright'=>'佐藤花子']);
    }
}