<?php
include("$_SERVER[DOCUMENT_ROOT]/includes/class-autoload.php");

$idCultura = $_GET['idCultura'];
$codigoParam = $_GET['codigoParam'];
$nomeParam = $_GET['nomeParam'];

$view = new CulturaView();
$parametro = $view->getParametroCulturaInfo($idCultura, $codigoParam);

?>

<div class="modal fade" id="editParamModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar <?= $nomeParam; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="../includes/editParamAction.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="idCultura" value="<?= $idCultura; ?>" />
                    <input type="hidden" class="addParam-codigo" name="codigoParam" value="<?= $codigoParam; ?>" />
                    <p id="errormessage" class="text-danger font-weight-bold"></p>
                    <h6>Limites</h6>
                    <label for="maximo">Valor máximo: </label>
                    <input type="number" step=".01" name="maximo" value="<?= $parametro['Max']; ?>">
                    <br />
                    <label for="minimo">Valor mínimo: </label>
                    <input type="number" step=".01" name="minimo" value="<?= $parametro['Min']; ?>">
                    <br />
                    <br />
                    <h6>Limites de alertas</h6>
                    <label for="maximoUrgencia">Valor máximo urgência: </label>
                    <input type="number" step=".01" name="maximoUrgencia" value="<?= $parametro['Max_Urgente']; ?>">
                    <br />
                    <label for="maximoAviso">Valor máximo aviso: </label>
                    <input type="number" step=".01" name="maximoAviso" value="<?= $parametro['Max_Aviso']; ?>">
                    <br />
                    <br />
                    <label for="minimoUrgencia">Valor mínimo urgência: </label>
                    <input type="number" step=".01" name="minimoUrgencia" value="<?= $parametro['Min_Urgente']; ?>">
                    <br />
                    <label for="minimoAviso">Valor mínimo aviso: </label>
                    <input type="number" step=".01" name="minimoAviso" value="<?= $parametro['Min_Aviso']; ?>">
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
        $('#editParamModal').on('shown.bs.modal', function(event) {

        });

        $('#editParamModal').submit(function(event) {
            let minimo = $('#editParamModal').find('input[name="minimo"]').val();
            let maximo = $('#editParamModal').find('input[name="maximo"]').val();
            let minimoAviso = $('#editParamModal').find('input[name="minimoAviso"]').val();
            let minimoUrgencia = $('#editParamModal').find('input[name="minimoUrgencia"]').val();
            let maximoAviso = $('#editParamModal').find('input[name="maximoAviso"]').val();
            let maximoUrgencia = $('#editParamModal').find('input[name="maximoUrgencia"]').val();

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
                $('#editParamModal #errormessage').text(error);
                console.log(error);
                event.preventDefault(); // Prevents the default submit
            }

            // $(this).unbind('submit').submit(); // continue the submit unbind preventDefault
        })
    });
</script>