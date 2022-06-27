<?php
include("$_SERVER[DOCUMENT_ROOT]/includes/class-autoload.php");

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../public/assets/styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&family=Raleway:wght@100&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <title>Login</title>
</head>

<body>
    <div class="banner">
        <div class="navbar">
            <img src="assets/images/Logo.png" class="logo">
            <ul>
                <li> <a href="" data-toggle="modal" data-target="#changePasswordModal">Alterar Password</a></li>
                <li> <a href="../includes/logoutAction.php">LOGOUT</a></li>
            </ul>
        </div>
        <div class="content">
            <h2>Minhas culturas</h2>
            <div class="list">
                <?php
                $view = new CulturaView();
                $array = $view->getCurrentUserCulturas();
                // echo sizeof($array);
                foreach ($array as $row) : ?>
                    <br />
                    <br />
                    <a href="culturaPage.php?idCultura=<?= $row['IdCultura']; ?>"><?= $row['NomeCultura']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Alterar password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="../includes/changePasswordAction.php" method="POST">
                    <div class="modal-body">
                        <p id="errormessage" class="text-danger font-weight-bold"></p>
                        <label for="password">Nova password: </label>
                        <input type="password" name="password" minlength="1" required>
                        <br />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>