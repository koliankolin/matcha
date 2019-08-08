<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Controllers\UserController;
use Controllers\LoaderController;
use Controllers\DeleterController;
use Middleware\RedirectIfAuth;
use Middleware\RedirectIfUnAuth;
use Middleware\RedirectIfNoToken;
use Controllers\LikerController;
use Controllers\ChatController;
use Controllers\FinderController;
use Classes\Finder;

// Home Route
$app->get("/", function (Request $request, Response $response) {
    if(!isset($_SESSION["logged"]))
        return $this->view->render($response, "home.twig");
    return $this->view->render($response, "home-authorized.twig");
})->setName("home");

// Users Route

$app->group("", function () {
    // Login
    // Get
    $this->get("/login", function (Request $request, Response $response) {
        return $this->view->render($response, "login.twig");
    })->setName("login");
    // Post
    $this->post("/login", UserController::class . ":login");

    // Register
    // get
    $this->get("/register", function (Request $request, Response $response) {
        return $this->view->render($response, "register.twig");
    })->setName("register");
    // post
    $this->post("/register", UserController::class . ":register");

    //Verify
    $this->get("/verify", UserController::class . ":verify");

    //Send email
    $this->get("/email-sent", function (Request $request, Response $response) {
        return $this->view->render($response, "email-sent.twig");
    })->setName("email-sent");

    //Verify email
    $this->get("/account-activated", function (Request $request, Response $response) {
        return $this->view->render($response, "account-activated.twig");
    })->setName("account-activated");

    //Forgot password
    $this->group("/forgot-password", function () {
        $this->get("", function (Request $request, Response $response) {
            return $this->view->render($response, "forgot-password.twig");
        })->setName("forgot-password");

        $this->post("", UserController::class . ":changePassword");
    });
})->add(new RedirectIfAuth($container["router"]));


$app->group("", function () use ($container) {
    //Logout
    $this->get("/logout", function (Request $request, Response $response) {
      unset($_SESSION["logged"]);
      return $response->withRedirect("/");
    })->setName("logout");

    //My Profile
    $this->group("/id". $_SESSION["logged"]["user_id"], function () {
        $this->get("", function (Request $request, Response $response) {
            $userInfo = $this->qb->filterDataByCol("users_info", "user_id", $_SESSION["logged"]["user_id"])[0];
            if (isset($userInfo)) {
                $userInfo = array_merge(["login" => $_SESSION["logged"]["login"]], $userInfo);
            } else
                $userInfo["login"] = $_SESSION["logged"]["login"];

            //Tags TODO: refactor this code
            $tags = $this->db->query("
                            SELECT * FROM tags_users tu
                            LEFT JOIN tags t ON tu.tag_id=t.id
                            WHERE tu.user_id = " . $_SESSION["logged"]["user_id"])
                ->fetchAll(PDO::FETCH_ASSOC);
            $userInfo["tags"] = "";
            if (isset($tags)) {
                foreach ($tags as $tag) {
                    $userInfo["tags"] .= "#" . $tag["name"] . ", ";
                }
                $userInfo["tags"] = rtrim($userInfo["tags"], ", ");
            }

            //photos
            $photos = $this->qb->filterDataByCol("photos", "user_id", $_SESSION["logged"]["user_id"]);

            //Avatar
            $avatar = $this->qb->filterDataByCol("avatars", "user_id", $_SESSION["logged"]["user_id"])[0];
            $data = array_merge(compact("userInfo"), compact("photos"), compact("avatar"));
            return $this->view->render($response, "my-profile.twig",
                compact("data"));
        })->setName("myProfile");

        $this->post("/photo/upload", LoaderController::class . ":loadPhotosToProfile");
        $this->post("/photo/delete", DeleterController::class . ":deletePhoto");
        $this->post("/avatar/upload", LoaderController::class . ":loadAvatar");
        $this->post("/avatar/delete", DeleterController::class . ":deleteAvatar");
    });

    //Change Login
    $this->group("/change-login", function () {
       $this->get("", function (Request $request, Response $response) {
          return $this->view->render($response, "change-login.twig");
       })->setName("change-login");

       $this->post("",  UserController::class . ":changeLogin");
    });

    //Change Email
    $this->group("/change-email", function () {
        $this->get("", function (Request $request, Response $response) {
            return $this->view->render($response, "change-email.twig");
        })->setName("change-email");

       $this->post("", UserController::class . ":changeEmail");
    });

    //Profile
    $this->get("/id{userId}", function (Request $request, Response $response, $args) {
        //TODO: find out how to do it with function
        if ($user = $this->qb->filterDataByCol("users", "id", $args["userId"])[0]) {

            $userInfo = $this->qb->filterDataByCol("users_info", "user_id", $user["id"])[0];
            if (isset($userInfo)) {
                $userInfo = array_merge(["login" => $user["login"]], $userInfo);
            } else {
                $userInfo["login"] = $user["login"];
                $userInfo["id"] = $args["userId"];
            }


            //Tags TODO: refactor this code
            $tags = $this->db->query("
                            SELECT * FROM tags_users tu
                            LEFT JOIN tags t ON tu.tag_id=t.id
                            WHERE tu.user_id = " . $user["id"])
                ->fetchAll(PDO::FETCH_ASSOC);
            $userInfo["tags"] = "";
            if (isset($tags)) {
                foreach ($tags as $tag) {
                    $userInfo["tags"] .= "#" . $tag["name"] . ", ";
                }
                $userInfo["tags"] = rtrim($userInfo["tags"], ", ");
            }

            //photos
            $photos = $this->qb->filterDataByCol("photos", "user_id", $user["id"]);

            //check like
            $isLiked = (empty($this->qb->filterDataByCond("likes", [
                "user_id_to" => $args["userId"],
                "user_id_from" => $_SESSION["logged"]["user_id"]
            ])[0])) ? false : true;

            //avatar
            $avatar = $this->qb->filterDataByCol("avatars", "user_id", $user["id"])[0];
            $data = array_merge(
                compact("userInfo"),
                compact("photos"),
                compact("avatar"),
                compact("isLiked")
            );



            //add view TODO: This code add new view with like coz of refreshing page
            $this->qb->insertDataIntoTable("views", [
                "user_id_from" => $_SESSION["logged"]["user_id"],
                "user_id_to" => $user["id"],
                "created_at" => date("Y-m-d H:i:s")
            ]);
            return $this->view->render($response, "profile.twig", compact("data"));

        }
        return $this->view->render($response, "user-not-found.twig")->withStatus(404);
    })->setName("profile");

    $this->post("/id{userId}/person/like", LikerController::class . ":addLike");

    //Add Personal Info
    $this->group("/change-info", function () {
        $this->get("", function (Request $request, Response $response) {
            $userInfo = $this->qb->filterDataByCol("users_info", "user_id", $_SESSION["logged"]["user_id"])[0];
            //TODO : do search name tag with joins
            $tagsIds = $this->qb->filterDataByCol("tags_users", "user_id", $_SESSION["logged"]["user_id"]);
            $tagsStr = "";
            foreach ($tagsIds as $tagId) {
                $tagName = $this->qb->filterDataByCol("tags", "id", $tagId["tag_id"])[0]["name"];
                $tagsStr .= "#" . $tagName . ", ";
            }
            $userInfo["tags"] = rtrim($tagsStr, ", ");
            return $this->view->render($response, "change-info.twig", compact("userInfo"));
        })->setName("change-info");
        $this->post("", UserController::class . ":changeInfo");
    });

    //Change Password
    $this->group("/change-password", function () {
       $this->get("", function (Request $request, Response $response) {
           return $this->view->render($response, "change-password.twig");
       })->setName("change-password");

       $this->post("", UserController::class . ":changePassword");
    });

    $this->get("/email-sent-password", function (Request $request, Response $response) {
        return $this->view->render($response, "email-sent.twig");
    })->setName("email-sent-password")->add(new RedirectIfNoToken($container["router"]));

    $this->group("/store-password", function () {
       $this->get("", function (Request $request, Response $response) {
          return $this->view->render($response, "store-password.twig");
       })->setName("store-password");

       $this->post("", UserController::class . ":storePassword");
    })->add(new RedirectIfNoToken($container["router"]));

    // View all views and likes
    $this->group("/views-and-likes", function () {
        $this->get("", function (Request $request, Response $response) {
            $viewsSql = "
            SELECT * 
            FROM views v 
            LEFT JOIN users u 
                ON v.user_id_from = u.id 
            WHERE v.user_id_to = {$_SESSION["logged"]["user_id"]}
            ";

            $likesSql = "
            SELECT * 
            FROM likes l 
            LEFT JOIN users u 
                ON l.user_id_from = u.id 
            WHERE l.user_id_to = {$_SESSION["logged"]["user_id"]}
            ";

            $views = ["views" => $this->db->query($viewsSql)->fetchALL(PDO::FETCH_ASSOC)];
            $likes = $this->db->query($likesSql)->fetchALL(PDO::FETCH_ASSOC);
            $usersWhoLiked = $this->qb->filterDataByCol("likes", "user_id_from", $_SESSION["logged"]["user_id"]);
            // For each like check opposite like

            for ($i = 0; $i < count($likes); $i++) {
                $userIdWhoseLike = $likes[$i]["user_id_from"];
                foreach ($usersWhoLiked as $user) {
                    $userIdToWhomLike = $user["user_id_to"];
                    if ($userIdToWhomLike === $userIdWhoseLike) {
                        $likes[$i]["both_liked"] = true;
                    }
                }
            }

            $data = array_merge($views, ["likes" => $likes]);

            return $this->view->render($response, "views-and-likes.twig", compact("data"));
        })->setName("views-and-likes");
    });

    //Chat
    $this->group("/chat", function () {
       $this->get("", function (Request $request, Response $response) {
           $messagesFrom = $this->qb->filterDataByCond("messages", [
               "user_id_from" => $_SESSION["logged"]["user_id"],
               "user_id_to" => $request->getParam("user_id_to")
           ]);

           $messagesTo = $this->qb->filterDataByCond("messages", [
               "user_id_from" => $request->getParam("user_id_to"),
               "user_id_to" => $_SESSION["logged"]["user_id"]
           ]);

           $messages = array_merge($messagesFrom, $messagesTo);
           uasort($messages, function ($a, $b) {
               if ($a["created_at"] === $b["created_at"]) {
                   return 0;
               }
               return ($a["created_at"] < $b["created_at"]) ? -1 : 1;}
               );

           $data = [
               "messages" => $messages,
               "userFrom" => $_SESSION["logged"]["user_id"],
               "userTo" => $request->getParam("user_id_to")
           ];

           return $this->view->render($response, "chat.twig", compact("data"));
       })->setName("chat");
       $this->post("", ChatController::class . ":sendMessage");
    });


    // Find Person
    $this->group("/find-person", function () {
        $this->get("", function (Request $request, Response $response) {
            $sql = "
            SELECT * FROM tags_users tu
            LEFT JOIN tags t ON t.id = tu.tag_id
            WHERE tu.user_id = {$_SESSION["logged"]["user_id"]}
            ";

            $interests = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);






            $data = [
                "interests" => $interests,
            ];


            return $this->view->render($response, "find-person.twig", compact("data"));
        })->setName("find-person");

        $this->post("", FinderController::class . ":findPerson");
    });


})->add(new RedirectIfUnAuth($container["router"]));
