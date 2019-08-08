<?php

namespace Controllers;

use Classes\Finder;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use PDO;


class FinderController extends Controller
{
    private $finder;

    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $this->finder = new Finder($c);
    }

    public function findPerson(Request $request, Response $response)
    {
        $cond = $request->getParams();

        $foundUsers = $this->finder->findPerson($cond);

        $sql = "
            SELECT * FROM tags_users tu
            LEFT JOIN tags t ON t.id = tu.tag_id
            WHERE tu.user_id = {$_SESSION["logged"]["user_id"]}
            ";

        $interests = $this->c->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            "foundUsers" => $foundUsers,
            "interests" => $interests
        ];

        return $this->view->render($response, "find-person.twig", compact("data"));
    }
}