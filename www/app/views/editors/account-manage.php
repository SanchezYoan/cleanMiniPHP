<?php
/**
 * @var $account         User
 */
$lang = $this->controller->lang;
?>
<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-md-8 offset-md-2">
                <h1>Mon compte</h1>
            </div>
        </div>
        <input type="hidden" id="accountId" value="<?= $account->getId(); ?>">
        <form class="" method="post" id="form-edit-account">
            <div class="row row-cards">
                <div class="col-md-8 offset-md-2">
                    <div class="card card-md" id="card-form-messages">
                        <div class="card-body" id="form-edit-accounts-options">
                            <div class="form-container-super">
                                <h3>Modifier mon mot de passe</h3>
                                <div class="group-select-editor">
                                    <label for="editor" class="form-label">Ancien mot de passe :</label>
                                    <input type="password" id="old-password" name="old-password" class="form-control">
                                    <label for="editor" class="form-label mt-2">Nouveau mot de passe :</label>
                                    <input type="password" id="new-password" name="new-password" class="form-control">
                                </div>
                                <div class="col d-flex justify-content-center mt-4">
                                    <button type="button" class="btn btn-success" id="changePasswordConfirm">
                                        Modifier
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>