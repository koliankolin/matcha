<?php

namespace Middleware;
use Slim\Interfaces\RouterInterface;

class Middleware
{
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
}