<?php
/**
 * @var $operation Operation
 * @var $this      View
 */


$curPage = Config::getJsConfig('curPage');

if (Session::isLogedIn()) {
   
    
    if ($this->User->isAdmin()) { ?>
        
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
                
                <?php if (ENV !== "PROD") { ?>
                    <li class="nav-item">
                        <span class="text"><?= ENV; ?></span>
                    </li>
                <?php } ?>
                
                
                <li class="nav-item <?= $curPage === 'admin' ? 'active' : null; ?>">
                    <a class="nav-link" href="/admin"><i class="fa fa-fw fa-cogs"></i>&nbsp;<span class="text">Administration</span></a>
                </li>
                
                <!-- Menu "Profil" -->
                <li style="justify-content: end;" class="nav-item item-right dropdown <?= in_array($curPage, ['profile', 'sourceEdit', 'source'], true) ? 'active' : null; ?>">
                    <a class="nav-link dropdown-toggle" id="navbarDropdownAdmin" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="nav-user-name">
                            <i class="fas fa-fw fa-user-circle"></i>&nbsp;<span class="text">Admin</span>
                        </div>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                        <a class="dropdown-item <?= $curPage === "profile" ? 'active' : null; ?>" href="/editor/profil">
                            <i class="fas fa-fw fa-user-circle"></i>&nbsp;Mon profil</a>
                        <a class="dropdown-item" href="/login/logout">
                            <i class="fas fa-fw fa-sign-out-alt"></i>&nbsp;Déconnexion
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    
    <?php } else { ?>
        
        
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
                <?php if (ENV !== "PROD") { ?>
                    <li class="nav-item">
                        <span class="text"><?= ENV; ?></span>
                    </li>
                <?php } ?>
               
                
                <?php
                /***********************************
                 ***** MENU PROFILE / LOGOUT *******
                 ***********************************/
                ?>
                <li class="nav-item item-right dropdown <?= "profile" === $curPage ? 'active' : null; ?>">
                    <a class="nav-link dropdown-toggle" id="navbarDropdownProfil" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="nav-user-name">
                            Editor name
                        </div>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownProfil">
                        <a class="dropdown-item <?= $curPage === "profile" ? 'active' : null; ?>" href="/editor/profil">
                            <i class="fa fa-fw fa-user-alt"></i>&nbsp;Mon profil</a>
                        <a class="dropdown-item" href="/login/logout">
                            <i class="fas fa-fw fa-sign-out-alt"></i>&nbsp;Déconnexion
                        </a>
                    </div>
                </li>
            
            
            </ul>
        </div>
    
    <?php } ?>


<?php } else { ?>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav public">
            
            <?php if (ENV !== "PROD") { ?>
                <li class="nav-item">
                    <span class="text"><?= ENV; ?></span>
                </li>
            <?php } ?>
            
            <li class="nav-item item-right login <?= $curPage === 'login' ? 'active' : null; ?>">
                <a class="nav-link " href="/login"><i class="fa fa-fw fa-user-o"></i>
                    <span class="text  hidden-md">&nbsp;Connexion</span>
                </a>
            </li>
        
        
        </ul>
    </div>
<?php } ?>