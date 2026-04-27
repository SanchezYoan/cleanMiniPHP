<?php
/**
 * @var $this    View
 * @var $user    User
 * @var $token   string
 */
?>
<div class="page page-center flex-grow-1">
    <div class="container container-tight py-4">
        <div class="card card-md">
            <div class="card-body">
                <div class="text-center mb-4">
                    <a href="/" class="navbar-brand navbar-brand-autodark">
                        <img src="/assets/img/logo-site-generique.svg" width="110" height="32" alt="Tabler"
                             class="navbar-brand-image">
                    </a>
                </div>
                <h2 class="h2 text-center mb-4"><?= \NGine\Translate::get("nouveauprojet.reset.title"); ?></h2>
                <input type="hidden" id="user_id" value="<?= $user->getId(); ?>">
                <input type="hidden" id="reset_token" value="<?= htmlspecialchars($token, ENT_QUOTES); ?>">
                <form id="reset-form" autocomplete="off" novalidate="">
                    <input type="hidden" name="csrf_token" value="<?= Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label"><?= \NGine\Translate::get("nouveauprojet.reset.password"); ?></label>
                        <div class="input-group input-group-flat">
                            <input id="password" type="password" class="form-control"
                                   placeholder="<?= \NGine\Translate::get("nouveauprojet.login.password.placeholder"); ?>"
                                   autocomplete="off">
                            <span class="input-group-text">
                                <a href="#" id="togglePassword" class="link-secondary" data-bs-toggle="tooltip"
                                   aria-label="Show password" data-bs-original-title="Show password"><!-- Download SVG icon from http://tabler-icons.io/i/eye -->
                                  <svg id="iconPassword" xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                       height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                       fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none"
                                                                                                        d="M0 0h24v24H0z"
                                                                                                        fill="none"></path><path
                                              d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path
                                              d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path></svg>
                                </a>
                            </span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">
                            <?= \NGine\Translate::get("nouveauprojet.reset.confirm"); ?>
                        </label>
                        <div class="input-group input-group-flat">
                            <input id="confirm" type="password" class="form-control"
                                   placeholder="<?= \NGine\Translate::get("nouveauprojet.login.password.placeholder"); ?>"
                                   autocomplete="off">
                            <span class="input-group-text">
                                <a href="#" id="toggleConfirm" class="link-secondary" data-bs-toggle="tooltip"
                                   aria-label="Show password" data-bs-original-title="Show password"><!-- Download SVG icon from http://tabler-icons.io/i/eye -->
                                  <svg id="iconConfirm" xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                       height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                       fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none"
                                                                                                        d="M0 0h24v24H0z"
                                                                                                        fill="none"></path><path
                                              d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path
                                              d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path></svg>
                                </a>
                            </span>
                        </div>
                    </div>
                    <div class="form-footer">
                        <button type="submit"
                                class="btn btn-primary w-100"><?= \NGine\Translate::get("nouveauprojet.reset.validate"); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
