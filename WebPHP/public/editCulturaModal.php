<?php
include("$_SERVER[DOCUMENT_ROOT]/includes/class-autoload.php");

$idCultura = $_GET['idCultura'];

$view = new CulturaView();
$cultura = $view->getCulturaInfo($idCultura);
?>

<div class="modal fade" id="editCulturaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar cultura</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="../includes/editCulturaAction.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="idCultura" value="<?= $idCultura; ?>" />
                    <p id="errormessage" class="text-danger font-weight-bold"></p>
                    <label for="nome">Nome: </label>
                    <input type="text" name="nome" value="<?= $cultura['NomeCultura']; ?>">
                    <br />
                    <label for="minutos">Tempo entre alertas: </label>
                    <input type="number" step="1" oninput="this.value = Math.round(this.value);" name="minutos" value="<?= $cultura['MinutosRealerta']; ?>">
                    <br />
                    <label for="ativo">Ativo: </label>
                    <input type="checkbox" name="ativo" <?= $cultura['Ativo'] == 0 ? "" : "checked"; ?> />
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


<script>
    $(document).ready(function(event) {
        $('#editCulturaModal').submit(function(event) {
            let nome = $('#editCulturaModal').find('input[name="nome"]').val();

            let error = '';
            if (nome.trim().length == 0) {
                error = 'Insira um nome para a cultura';
            }

            if (error != '') {
                $('#editCulturaModal #errormessage').text(error);
                console.log(error);
                event.preventDefault(); // Prevents the default submit
            }

        })
    });
</script>