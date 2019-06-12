<?php

namespace Controllers;

use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Classes\User;
use Exception;

class UserController extends Controller
{
    private $user;

    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $this->user = new User([
            "login" => $this->c->request->getParam("login"),
//                (!isset($_SESSION["logged"]["login"])) ?
//                         :
//                        $_SESSION["logged"]["login"],
            "password" => $this->c->request->getParam("password")
        ]);
    }

    public function login(Request $request, Response $response)
    {
        try {
            if ($this->user->loginUser()) {
                return $response->withRedirect($this->router->pathFor("home"));
            }
            return $response->write("Invalid login or password");
        } catch (Exception $exception) {
            return $response->write($exception->getMessage());
        }
    }

    public function register(Request $request, Response $response)
    {
        $email = $request->getParam("email");
        if (filter_var($email, FILTER_VALIDATE_EMAIL))
            $this->user->setEmail($email);
        else
            return $response->write("Invalid email");
        try {
            if ($this->user->registerUser()) {
                return $response->withRedirect($this->router->pathFor("email-sent"));
            }
            return $response->write("Something went wrong");
        } catch (Exception $exception) {
            return $response->write($exception->getMessage());
        }
    }

    public function verify(Request $request, Response $response)
    {
        $hash = $request->getParam("hash");

        if ($this->c->qb->updateDataById("users", "hash", $hash, [
            "activated" => 1
        ])) {
            return $response->withRedirect($this->router->pathFor("account-activated"));
        }
        return $response->write("Something went wrong");
    }

    public function changeLogin(Request $request, Response $response)
    {
        $newLogin = $request->getParam("newLogin");
        //TODO : add some check login
        if ($this->user->changeLoginUser($newLogin)) {
            return $response->withRedirect($this->router->pathFor("logout"));
        }
        return $response->write("Login already exists");
    }

    public function changeEmail(Request $request, Response $response)
    {
        $newEmail = $request->getParam("newEmail");
        if ($this->user->changeEmailUser($newEmail)) {
            unset($_SESSION["logged"]);
            return $response->withRedirect($this->router->pathFor("email-sent"));
        }
        return $response->write("Email already exists");
    }

    public function changeInfo(Request $request, Response $response)
    {
        $data = $request->getParams();
        $tags = $data["tags"];
        unset($data["tags"]);

        if (!isset($data["sex"])) {
            $data["sex"] = 3;
        }
        if (!isset($data["sex_pref"])) {
            $data["sex_pref"] = 4;
        }

        $data = [
            "user_id" => $_SESSION["logged"]["user_id"],
            "sex" => $data["sex"],
            "first_name" => $data["first_name"],
            "surname" => $data["surname"],
            "sex_pref_id" => $data["sex_pref"],
            "biography" => $data["biography"]
        ];


        $userInfo = $this->c->qb->filterDataByCol("users_info", "user_id", $_SESSION["logged"]["user_id"]);

        if (empty($userInfo)) {
            $success = $this->c->qb->insertDataIntoTable("users_info", $data, false, true);
        } else
            $success = $this->c->qb->updateDataById("users_info", "user_id",
                $_SESSION["logged"]["user_id"], $data);

        if ($success) {
            if ($this->addTags($tags)) {
                return $response->withRedirect($this->router->pathFor("myProfile"));
            }
            return $response->write("Something went wrong 1");
        }
        return $response->write("Something went wrong 2");
    }

    private function addTags($tags)
    {
        $this->c->qb->deleteRowByCond("tags_users", [
           "user_id" => $_SESSION["logged"]["user_id"]
        ]);
        $tags = preg_replace("/[^#a-zA-Z0-9]/", "", $tags);
        $tags = array_filter(explode("#", $tags), function ($elem) {
            return !empty($elem);
        });
        if ($tags) {
            foreach ($tags as $tag) {
                $tagBase = $this->c->qb->filterDataByCol("tags", "name", $tag)[0];
                if (empty($tagBase)) {
                    if (!$this->c->qb->insertDataIntoTable("tags", [
                        "name" => $tag
                    ], false, true)) {
                        return false;
                    }
                    $idTag = $this->c->qb->filterDataByCol("tags", "name", $tag)[0]["id"];
                    if (!$this->c->qb->insertDataIntoTable("tags_users", [
                        "tag_id" => $idTag,
                        "user_id" => $_SESSION["logged"]["user_id"]
                    ], false, true)) {
                        return false;
                    }
                } else {
                    if (!$this->c->qb->insertDataIntoTable("tags_users", [
                        "tag_id" => $tagBase["id"],
                        "user_id" => $_SESSION["logged"]["user_id"]
                    ], false, true)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function changePassword(Request $request, Response $response)
    {
        $email = $request->getParam("email");
        $this->user->changePasswordUser($email);
//        $this->user->logoutUser();
        return $response->withRedirect($this->router->pathFor("email-sent-password"));
    }

    public function storePassword(Request $request, Response $response)
    {
        // Check hashes
        if ($_SESSION["pass_hash"] !== $request->getParam("hash")) {
            return $response->withRedirect($this->router->pathFor("home"));
        }
        unset($_SESSION["pass_hash"]);

        $newPassword = $request->getParam("newPassword");
        if ($this->user->storePassword($newPassword)) {
            $this->user->logoutUser();
            return $response->write("Password was changed");
        }
        return $response->write("Something went wrong");
    }
}
