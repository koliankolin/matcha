<?php

namespace Controllers;

use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Classes\Loader;

class LoaderController extends Controller
{
    private $loader;

    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $this->loader = new Loader($c);
    }

    public function loadPhotosToProfile(Request $request, Response $response)
    {
        $photos = $request->getUploadedFiles()["photos"];
        $dirName = "/var/www/html/data/" . $_SESSION["logged"]["login"] . "/";
        if ($this->loader->loadPhoto($photos, $dirName)) {
            return $response->withRedirect($this->router->pathFor("myProfile"));
        }
        return $response->write("Something went wrong");
    }
}
