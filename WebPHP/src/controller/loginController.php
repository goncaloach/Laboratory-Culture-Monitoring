<?php

// include '../includes/class-autoload.php';

class LoginController extends LoginModel
{
    private $username;
    private $password;

    public function __construct()
    {
        $arguments = func_get_args();
        $numberOfArguments = func_num_args();

        if (method_exists($this, $function = '__construct' . $numberOfArguments)) {
            call_user_func_array(array($this, $function), $arguments);
        }
    }

    public function __construct0()
    {
    }

    public function __construct2($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }


    public function loginUser()
    {
        $_SESSION['isLoggedIn'] = false;

        if ($this->emptyInput()) {
            header('location: ../../public/loginPage.php?errorType=empty_input');
            exit();
        }

        $canConnect = $this->canConnect($this->username, $this->password);
        if (is_string($canConnect)) {
            header('location: ../../public/loginPage.php?errorType=' . $canConnect);
            exit();
        }

        $_SESSION['username'] = $this->username;
        $_SESSION['password'] = $this->password;

        $userId = $this->getUserId($this->username);

        $_SESSION['userId'] = $userId;

        $_SESSION['isLoggedIn'] = true;
    }



    public function changePassword($password)
    {
        $this->setUserPassword($password);
        
        $_SESSION['password'] = $password;
    }

    private function emptyInput()
    {
        if (empty($this->username) || empty($this->password))
            $result = true;
        else
            $result = false;

        return $result;
    }
}
