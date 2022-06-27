<?php

class Dbh
{
    private $host = "localhost";
    private $user = "";
    private $pwd = "";
    private $dbName = "mylab";

    protected function connect()
    {
        try {
            $error = $this->handleCredentials();

            if ($error) {
                print "Error: " . $error . '<br/>';
                header('location: ../public/loginPage.php?errorType='.'no_credentials');
                throw new Exception('no_credentials');
            }

            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbName;
            $pdo = new PDO($dsn, $this->user, $this->pwd);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $pdo;
        } catch (PDOException $e) {
            error_log("PDO Error: " . __METHOD__ . ' ' . $e->getMessage()); // log a test error
            header('location: ../public/loginPage.php?errorType='.'cant_connect');
            return 'cant_connect';
            //die();
        } catch (Exception $e) {
            error_log("Error: " . __METHOD__ . ' ' . $e->getMessage());
            return $e->getMessage();
            //die();
        }
    }

    protected function handleCredentials()
    {
        if ($this->credentialsExist())
            $this->assignCredentials();
        else
            return "There aren't credentials to create connection";

        return null;
    }

    protected function credentialsExist()
    {
        return isset($_SESSION['username']) && isset($_SESSION['password']) && $_SESSION['username'] && $_SESSION['password'];
    }

    protected function assignCredentials()
    {
        $this->user = $_SESSION['username'];
        $this->pwd = $_SESSION['password'];
    }
}
