<?php
// include '../includes/class-autoload.php';


class LoginModel extends Dbh
{

    protected function canConnect($username, $password)
    {
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;

        $connectionOrError = $this->connect();

        if (is_string($connectionOrError)) {
            $result = $connectionOrError;
        } else {
            $result = true;
        }

        unset($_SESSION['username']);
        unset($_SESSION['password']);

        return $result;
    }

    protected function getUserId($username)
    {
        $stmt = $this->connect()->prepare('call ObterIdUtilizador(:Username)');
        $stmt->bindParam(':Username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $stmt = null;
            error_log("Error: " . __METHOD__ . ' User not found');

            header('location: ../../index.php?errorType=user_not_found');
            exit();
        }
        $procResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $userId = $procResults[0]['IdUtilizador'];

        return $userId;
    }

    
    protected function setUserPassword($password)
    {
        $stmt = $this->connect()->prepare('SET PASSWORD = PASSWORD(?);');
        $stmt->execute([$password]);

        $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // echo "Nr culturas: ".sizeof($queryResults)."<br>";

        return $queryResults[0];
    }

}
