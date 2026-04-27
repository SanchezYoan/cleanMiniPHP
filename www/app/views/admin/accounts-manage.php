<?php
/**
 * @var $accounts         int
 */
$lang = $this->controller->lang;
?>
<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-md-8 offset-md-2">
                <h1>Gestion des comptes</h1>
            </div>
        </div>
        <div class="row row-cards">
            <div class="col-md-8 offset-md-2 d-flex">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-baseline">
                                <div class="h1 m-0 pe-2"><?= count($accounts); ?></div>
                                <div class="subheader">compte(s)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <form class="" method="post" id="form-edit-accounts">
            <div class="row row-cards">
                <div class="col-md-8 offset-md-2">
                    <div class="card card-md" id="card-form-messages">
                        <div class="card-body" id="form-edit-accounts-options">
                            <div class="form-container-super">
                                <div class="table-responsive mt-4">
                                    <label for="editor" class="form-label">Liste des comptes</label>
                                    <table class="table table-vcenter card-table border-top border-start border-end">
                                        <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Rôle</th>
                                            <th>Nom d'utilisateur</th>
                                            <th>Date de création</th>
                                            <th class="w-1"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if (empty($accounts)): ?>
                                            <tr>
                                                <td colspan="4"
                                                    class="no-accounts-message text-center text-red fw-bold">
                                                    Aucun compte !
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($accounts as $account): ?>
                                            <?php if (
                                                ($account->getLogin() !== 'adminsu' && $this->admin->checkAccess($this->admin->getLevel(), USER::ADMIN))
                                                || ($account->getLogin() === 'adminsu' && $this->admin->checkAccess($this->admin->getLevel(), USER::ADMINSU))
                                                ): ?>
                                                    
                                                    <tr id="account-<?= $account->getId(); ?>">
                                                    <td class="account-login"><?= $account->getLogin(); ?></td>
                                                    <td class="account-role text-azure"><?= $account->getLevel(); ?></td>
                                                    <td class="account-email"><?= $account->getEmail(); ?></td>
                                                    <td class="account-created-at"><?= $account->getCreatedAt()->format("d/m/Y"); ?></td>
                                                    <td>
                                                        <div class="btn-list flex-nowrap">
                                                            <button type="button"
                                                                    class="btn btn-outline-warning btn-icon"
                                                                    data-account-id="<?= $account->getId(); ?>"
                                                                    data-login="<?= $account->getLogin(); ?>"
                                                                    data-email="<?= $account->getEmail(); ?>"
                                                                    data-role="<?= $account->getLevel(); ?>">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                     height="24" viewBox="0 0 24 24" fill="none"
                                                                     stroke="currentColor" stroke-width="2"
                                                                     stroke-linecap="round" stroke-linejoin="round"
                                                                     class="icon icon-tabler icons-tabler-outline icon-tabler-pencil">
                                                                    <path stroke="none" d="M0 0h24v24H0z"
                                                                          fill="none"></path>
                                                                    <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"></path>
                                                                    <path d="M13.5 6.5l4 4"></path>
                                                                </svg>
                                                            </button>
                                                            <button type="button"
                                                                    class="btn btn-outline-danger btn-icon"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#confirmDeleteModal"
                                                                    data-account-id="<?= $account->getId(); ?>">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                     height="24" viewBox="0 0 24 24" fill="none"
                                                                     stroke="currentColor" stroke-width="2"
                                                                     stroke-linecap="round" stroke-linejoin="round"
                                                                     class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                    <path d="M4 7l16 0"/>
                                                                    <path d="M10 11l0 6"/>
                                                                    <path d="M14 11l0 6"/>
                                                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                                <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col d-flex justify-content-center mt-4">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#createAccountModal">
                                        Créer un compte
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

<!-- Modal -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAccountModalLabel">Créer un compte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createAccountForm">
                    <div class="mb-3">
                        <label for="login" class="form-label">Rôle</label>
                        <!-- Gestion des rôles -->
                        <select class="form-select operator-select" id="roleSelected" name="role">
                            <option value="admin">Admin</option>
                            <option value="usersu">UserSu</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="login" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="login" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer le compte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer ce compte ? Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal update account -->
<div class="modal fade" id="updateAccountModal" tabindex="-1" aria-labelledby="updateAccountModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateAccountModalLabel">Modifier le compte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateAccountForm" autocomplete="off">
                    <div class="mb-3">
                        <label for="stl-login-update" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="stl-login-update" required autocomplete="off"
                               data-lpignore="true">
                    </div>
                    <?php if ($this->admin->checkAccess($this->admin->getLevel(), USER::ADMINSU)): ?>
                        <div class="mb-3">
                            <label for="stl-role-update" class="form-label">Rôle</label>
                            <select class="form-select operator-select" id="stl-role-update" name="role">
                                <option value="adminsu" >adminsu</option>
                                <option value="admin">admin</option>
                                <option value="usersu">usersu</option>
                                <option value="user">user</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="stl-email-update" class="form-label">Email</label>
                        <input type="email" class="form-control" id="stl-email-update" required autocomplete="off"
                               data-lpignore="true">
                    </div>
                    <div class="mb-3">
                        <label for="stl-password-update" class="form-label">Mot de passe</label>
                        <small class="form-text text-muted">Si vous ne souhaitez pas changer le mot de passe, laissez ce
                            champ vide.</small>
                        <input type="password" class="form-control" id="stl-password-update" autocomplete="off"
                               data-lpignore="true">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="confirmUpdateBtn">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>