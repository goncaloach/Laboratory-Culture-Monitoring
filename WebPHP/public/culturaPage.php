<?php
include("$_SERVER[DOCUMENT_ROOT]/includes/class-autoload.php");

$idCultura = $_GET['idCultura'];

$view = new CulturaView();
$cultura = $view->getCulturaInfo($idCultura);
error_log('Nome cultura = ' . $cultura['NomeCultura']);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="../public/assets/styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&family=Raleway:wght@100&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"> -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title><?php echo (isset($cultura['NomeCultura'])) ? $cultura['NomeCultura'] : 'Cultura'; ?></title>
</head>

<body>
    <div class="banner">
        <div class="navbar">
            <img src="assets/images/Logo.png" class="logo">
            <ul>
                <li> <a href="homePage.php">Home</a></li>
                <li> <a href="../includes/logoutAction.php">LOGOUT</a></li>
            </ul>
        </div>
        <br />
        <br />
        <div class="content cultura">
            <h2><?php echo (isset($cultura['NomeCultura'])) ? $cultura['NomeCultura'] : 'Cultura'; ?><button type="button" class="btn btn-info ml-5 editCultura" data-action="edit"><i class="fas fa-edit"></i></button>
                <button type="hidden" id="editCultura" class="d-none" data-toggle="modal" data-target="#editCulturaModal"></button></h2>

            <?php

            $array = $view->getTipoParametrosToRender($idCultura);
            // echo sizeof($array);
            foreach ($array as $row) : ?>
                <div class="mb-5">
                    <h3 class=mb-3"><?= $row['NomeParametro']; ?></h3>
                    <?php if (!(isset($row['Exists']) && $row['Exists'])) : ?>
                        <!-- create param -->
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#addParamModal" data-action="add" data-parametro="<?= $row['CodigoParametro']; ?>" data-nomeparametro="<?= $row['NomeParametro']; ?>">Adicionar <?= $row['NomeParametro']; ?></button>

                    <?php else : ?>
                        <!-- edit param -->
                        <button type="button" class="btn btn-info editParam" data-action="edit" data-parametro="<?= $row['CodigoParametro']; ?>" data-nomeparametro="<?= $row['NomeParametro']; ?>">Editar <?= $row['NomeParametro']; ?></button>
                        <button type="hidden" id="edit<?= $row['CodigoParametro']; ?>" class="d-none" data-toggle="modal" data-target="#editParamModal"></button>
                        <!-- delete param -->
                        <form action="../includes/removeParamAction.php" method="POST" class="d-inline-block">
                            <input type="hidden" name="idCultura" value="<?= $idCultura; ?>" />
                            <input type="hidden" name="codigoParam" value="<?= $row['CodigoParametro']; ?>" />
                            <button type="submit" name="submit" value="delete" class="btn btn-info" data-toggle="modal" data-target="#paramModal" data-action="delete" data-parametro="<?= $row['CodigoParametro']; ?>" data-nomeparametro="<?= $row['NomeParametro']; ?>">Eliminar <?= $row['NomeParametro']; ?></button>
                        </form>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="showEditModal">

    </div>

    <!-- Modal -->
    <div id="showEditCulturaModal">

    </div>

    <!-- <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Open Small Modal</button> -->
    <!-- Modal -->
    <div class="modal fade" id="addParamModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Adicionar parâmetro</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="../includes/addParamAction.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="idCultura" value="<?= $idCultura; ?>" />
                        <input type="hidden" class="addParam-codigo" name="codigoParam" value="" />
                        <p id="errormessage" class="text-danger font-weight-bold"></p>
                        <h6>Limites</h6>
                        <label for="maximo">Valor máximo: </label>
                        <input type="number" step=".01" name="maximo">
                        <br />
                        <label for="minimo">Valor mínimo: </label>
                        <input type="number" step=".01" name="minimo">
                        <br />
                        <br />
                        <h6>Limites de alertas</h6>
                        <label for="maximoUrgencia">Valor máximo urgência: </label>
                        <input type="number" step=".01" name="maximoUrgencia">
                        <br />
                        <label for="maximoAviso">Valor máximo aviso: </label>
                        <input type="number" step=".01" name="maximoAviso">
                        <br />
                        <br />
                        <label for="minimoUrgencia">Valor mínimo urgência: </label>
                        <input type="number" step=".01" name="minimoUrgencia">
                        <br />
                        <label for="minimoAviso">Valor mínimo aviso: </label>
                        <input type="number" step=".01" name="minimoAviso">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function(event) {
            $('#addParamModal').on('shown.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var param = button.data('nomeparametro'); // Extract info from data-* attributes
                var codeParam = button.data('parametro'); // Extract info from data-* attributes
                // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
                // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
                var modal = $(this);
                modal.find('.modal-title').text('Adicionar parâmetro ' + param);
                modal.find('.addParam-codigo').val(codeParam)
                // modal.find('.modal-body input').val(recipient)
                // $.ajax({
                //     type: 'GET',
                //     url: "calculator.php", //this file has the calculator function code
                //     success: function(data) {
                //         $('#showcal').html(data);
                //     }
                // });
            });

            $('#addParamModal').submit(function(event) {
                let minimo = $('#addParamModal').find('input[name="minimo"]').val();
                let maximo = $('#addParamModal').find('input[name="maximo"]').val();
                let minimoAviso = $('#addParamModal').find('input[name="minimoAviso"]').val();
                let minimoUrgencia = $('#addParamModal').find('input[name="minimoUrgencia"]').val();
                let maximoAviso = $('#addParamModal').find('input[name="maximoAviso"]').val();
                let maximoUrgencia = $('#addParamModal').find('input[name="maximoUrgencia"]').val();

                let error = '';
                if (minimo != '' && maximo != '' && minimo >= maximo) {
                    error = 'Valor mínimo tem de ser inferior ao valor máximo';
                } else if (minimo != '' && ((minimoAviso != '' && parseInt(minimo) >= parseInt(minimoAviso)) || (minimoUrgencia != '' && parseInt(minimo) >= parseInt(minimoUrgencia)))) {
                    error = 'Valor mínimo de Aviso e de Urgência tem de ser superior ao valor mínimo';
                } else if (minimo != '' && ((minimoAviso != '' && parseInt(minimo) >= parseInt(minimoAviso)) || (minimoUrgencia != '' && parseInt(minimo) >= parseInt(minimoUrgencia)))) {
                    error = 'Valor mínimo de Aviso e de Urgência tem de ser superior ao valor mínimo';
                } else if (maximo != '' && ((maximoAviso != '' && parseInt(maximoAviso) >= parseInt(maximo)) || (maximoUrgencia != '' && parseInt(maximoUrgencia) >= parseInt(maximo)))) {
                    error = 'Valor máximo de Aviso e de Urgência tem de ser inferior ao valor máximo';
                } else if (minimoAviso != '' && minimoUrgencia != '' && parseInt(minimoAviso) <= parseInt(minimoUrgencia)) {
                    error = 'Valor mínimo de Aviso tem de ser superior ao valor mínimo de Urgência';
                } else if (maximoAviso != '' && maximoUrgencia != '' && parseInt(maximoUrgencia) <= parseInt(maximoAviso)) {
                    error = 'Valor máximo de Aviso tem de ser inferior ao valor máximo de Urgência';
                } else if (maximoUrgencia != '' && ((minimoUrgencia != '' && parseInt(minimoUrgencia) >= parseInt(maximoUrgencia)) || (minimoAviso != '' && parseInt(minimoAviso) >= parseInt(maximoUrgencia)))) {
                    error = 'Valor mínimo de Aviso e de Urgência tem de ser inferior ao valor máximo de Urgência';
                } else if (maximoAviso != '' && ((minimoUrgencia != '' && parseInt(minimoUrgencia) >= parseInt(maximoAviso)) || (minimoAviso != '' && parseInt(minimoAviso) >= parseInt(maximoAviso)))) {
                    error = 'Valor mínimo de Aviso e de Urgência tem de ser superior ao valor máximo de Aviso';
                }

                if (error != '') {
                    $('#addParamModal #errormessage').text(error);
                    console.log(error);
                    event.preventDefault(); // Prevents the default submit

                }

                // $(this).unbind('submit').submit(); // continue the submit unbind preventDefault
            })

            $('.editParam').click(function(event) {
                var button = $(event.currentTarget); // Button that triggered the modal
                var param = button.data('nomeparametro'); // Extract info from data-* attributes
                var codeParam = button.data('parametro'); // Extract info from data-* attributes
                console.log(param);
                console.log(codeParam);
                $.ajax({
                    type: 'GET',
                    url: "parametroModal.php?idCultura=<?= $idCultura; ?>&codigoParam=" + codeParam + "&nomeParam=" + param, //this file has the calculator function code
                    success: function(data) {
                        $('#showEditModal').html(data);
                        $('#edit' + codeParam).click();
                    }
                });
            });


            $('.editCultura').click(function(event) {
                var button = $(event.currentTarget); // Button that triggered the modal
                $.ajax({
                    type: 'GET',
                    url: "editCulturaModal.php?idCultura=<?= $idCultura; ?>",
                    success: function(data) {
                        $('#showEditCulturaModal').html(data);
                        $('#editCultura').click();
                    }
                });
            });
        });
    </script>
</body>

</html>