<?php

namespace Classes;
use \PDO;
use Exception;
use RuntimeException;

class User
{
    const TABLE = "users";

    private $login;
    private $email;
    private $password_hash;
    private $password;
    private $admin = 0;
    private $queryBuilder;
    private $userId;

    function __construct(array $kwargs)
    {
        if ($kwargs["login"]) {
            $this->login = $kwargs["login"];
        }
        if ($kwargs["password"]) {
            $this->password = $kwargs["password"];
            $this->password_hash = hash("whirlpool", $kwargs["password"]);
        }
        if ($kwargs["email"]){
            $this->email = $kwargs["email"];
        }
        if ($kwargs["user_id"]) {
            $this->userId = $kwargs["user_id"];
        }
        $this->queryBuilder = new QueryBuilder(["db" => new PDO("mysql:host=db; dbname=myDb", "user", "test")]);
    }

    public function __set($name, $value)
    {
        echo "This attribute $name is private or couldn't be initialized with value $value\n";
    }

    public function __get($name)
    {
        echo "This attribute $name is private or doesn't exist\n";
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function setPassword($password)
    {
        $this->password_hash = hash("whirlpool", $password);
    }

    public function setAdmin($admin)
    {
        $this->admin = (is_numeric($admin)) ? $admin : 0;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getPassword()
    {
        return $this->password_hash;
    }

    public function getAdmin()
    {
        return $this->admin;
    }

    public function authUser()
    {
        if (!($user = $this->queryBuilder->filterDataByCol(USER::TABLE, "login", $this->login)[0])) {
            return false;
        }
        if (!$user["activated"])
            throw new RuntimeException("activation");
        return $user["password"] === $this->password_hash;
    }

    public function isUserAdmin()
    {
        if (!($user = $this->queryBuilder->filterDataByCol(USER::TABLE, "login", $this->login)[0])) {
            return false;
        }
        $this->admin = $user["admin"];
        return $user["admin"] === 1;
    }

    //add user data to $_SESSION
    public function loginUser()
    {
        try {
            $success = $this->authUser();
        } catch (Exception $exception) {
            throw $exception;
        }
        if ($success) {
            $_SESSION["logged"] = [
                "user_id" => $this->queryBuilder->filterDataByCol(USER::TABLE, "login", $this->login)[0]["id"],
                "login" => $this->login,
                "password" => $this->password_hash,
            ];
            $_SESSION["logged"]["admin"] = ($this->isUserAdmin()) ? 1 : 0;
            return true;
        }
        return false;
    }

    public function isLoggedUser()
    {
        return (isset($_SESSION["logged"])) ? true : false;
    }

    public function logoutUser()
    {
        unset($_SESSION["logged"]);
    }

    private function createRegisterEmail($hash)
    {
        $subject = "SignUp | Great Site !!";
        $message = '
        
        Thanks for signing up!
        Your account has been created, you can login with the following credentials after you have activated your account by pressing the url below.
        
        ------------------------
        Username: ' . $this->login . '
        Password: ' . $this->password . '
        ------------------------
        
        Please click this link to activate your account:
        '
            .$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"].'/verify?email=' . $this->email .
            '&hash='. $hash . '
        
        ';
        return ["subject" => $subject, "message" => $message];
    }

    //register
    public function registerUser()
    {
        if ($this->authUser()) {
            throw new Exception("login");
        }
        if ($this->queryBuilder->filterDataByCol("users", "email", $this->email)) {
            throw new Exception("email");
        }
        $path = "/var/www/html/data/$this->login";
        if (!file_exists($path))
            mkdir($path, 0777, true);
        try {
            $hash = bin2hex(random_bytes(16));
        } catch (Exception $exception) {
            $hash = bin2hex(openssl_random_pseudo_bytes(16));
        }
        if ($this->queryBuilder->insertDataIntoTable(USER::TABLE,
            [$this->login, $this->email, $this->password_hash, $this->admin, $hash, 0])) {
            $letter = $this->createRegisterEmail($hash);
            return mail($this->email,
                $letter["subject"], $letter["message"]);
        }
        return false;
    }

    //change login
    public function changeLoginUser($newLogin)
    {
        if ($this->queryBuilder->filterDataByCol(USER::TABLE, "login", $newLogin)) {
            return false;
        }
        return $this->queryBuilder->updateDataById(USER::TABLE, "id",
            $_SESSION["logged"]["user_id"], [
                "login" => $newLogin
            ]);
    }



    private function createChangeEmailLetter($hash, $newEmail)
    {
        $subject = $_SESSION["logged"]["login"] . " | Change Email";
        $message = '
        
        Hello, ' . $_SESSION["logged"]["login"] . '!
        
        
        Your email was changed to it.
        Please click this link to activate your account again:
        '
            .$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"].'/verify?email=' . $newEmail .
            '&hash='. $hash . '
        
        ';
        return ["subject" => $subject, "message" => $message];
    }

    //change Email
    public function changeEmailUser($newEmail)
    {
        $hash = $this->queryBuilder->filterDataByCol(USER::TABLE, "id", $_SESSION["logged"]["user_id"])[0]["hash"];
        if ($this->queryBuilder->filterDataByCol(USER::TABLE, "id", $newEmail)) {
            return false;
        }
        if (!$this->queryBuilder->updateDataById(USER::TABLE, "id",
            $_SESSION["logged"]["user_id"], [
                "email" => $newEmail,
                "activated" => 0
            ])) {
            return false;
        }
        $letter = $this->createChangeEmailLetter($hash, $newEmail);
        return mail($newEmail, $letter["subject"], $letter["message"]);
    }

    private function createChangePasswordLetter($hash)
    {
        $subject = $_SESSION["logged"]["login"] . " | Change Password";
        $message = '
        
        Hello, ' . $_SESSION["logged"]["login"] . '!
        
        
        You need to reset your password.
        Please click this link to change your password:
        '
            .$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"].'/store-password?email=' . $this->email .
            '&hash='. $hash . '
        
        ';
        return ["subject" => $subject, "message" => $message];
    }

    //change password
    public function changePasswordUser()
    {
        if (!($user = $this->queryBuilder->filterDataByCol(USER::TABLE, "id",
            $_SESSION["logged"]["user_id"]))) {
            return false;
        }

        try {
            $hash = bin2hex(random_bytes(10));
        } catch (Exception $exception) {
            $hash = bin2hex(openssl_random_pseudo_bytes(10));
        }

        $_SESSION["pass_hash"] = $hash;
        $letter = $this->createChangePasswordLetter($hash);
        return mail($this->email, $letter["subject"], $letter["message"]);
    }

    public function storePassword($newPassword)
    {
        $this->queryBuilder->updateDataById("users")
    }
}
