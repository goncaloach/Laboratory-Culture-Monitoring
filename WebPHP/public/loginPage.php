<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/styles/form.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&family=Raleway:wght@100&display=swap" rel="stylesheet">
    <title>Login</title>
</head>

<body>
    <div class="container">
        <div class="text">
            <h1> Login </h1>
        </div>
        <form action="../includes/loginAction.php" method="POST">
            <div class="data">
                <label> User
                    <input required type="text" name="username" />
                </label><br />
            </div>
            <div class="data">
                <label> Password
                    <input required type="password" name="password" />
                </label><br />
            </div>
            <div class="btn">
                <input required type="submit" value="Login" name="submit" />
            </div>
        </form>
    </div>
</body>

</html>