<?php

namespace Controllers;

use Interop\Container\ContainerInterface;

class Controller
{
    protected $c;
    protected $router;
    protected $view;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->router = $c["router"];
        $this->view = $c["view"];
    }
}