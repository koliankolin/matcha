<?php

namespace Classes;

use Interop\Container\ContainerInterface;

class MainClass
{
    protected $c;
    protected $qb;
    protected $db;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->qb = $c["qb"];
        $this->db = $c["db"];
    }
}
