<?php
/**
 * @var $datas             array
 * @var $level             string
 * @var $countToday        array
 */
$lang = $this->controller->lang;
?>
<div class="page-body">
    <div class="container-fluid">
        
        <div class="row row-cards">
            <div class="col-md-8 offset-md-2">
                <h1><?= \NGine\Translate::get("nouveauprojet.dashboard.logs.title"); ?></h1>
            </div>
        </div>
        
        <div class="row row-cards">
            <div class="col-md-8 offset-md-2">
                <div class="card card-md" id="card-form-messages">
                    <div class="card-body" id="form-edit-campaigns-options">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <form method="get" action="" class="form">
                                    <label for="level" class="form-label">
                                        <?= \NGine\Translate::get("nouveauprojet.dashboard.logs.filter.level"); ?>
                                    </label>
                                    <select name="level" id="level" class="form-select" onchange="this.form.submit()">
                                        <option value="ALL" <?= $level === 'ALL' ? 'selected' : '' ?>>ALL</option>
                                        <option value="NOTICE" <?= $level === 'NOTICE' ? 'selected' : '' ?>>NOTICE</option>
                                        <option value="DEBUG" <?= $level === 'DEBUG' ? 'selected' : '' ?>>DEBUG</option>
                                        <option value="WARNING" <?= $level === 'WARNING' ? 'selected' : '' ?>>WARNING</option>
                                        <option value="ERROR" <?= $level === 'ERROR' ? 'selected' : '' ?>>ERROR</option>
                                        <option value="CRITICAL" <?= $level === 'CRITICAL' ? 'selected' : '' ?>>CRITICAL</option>
                                        <option value="SECURITY" <?= $level === 'SECURITY' ? 'selected' : '' ?>>SECURITY</option>
                                    </select>
                                </form>
                            </div>
                            <div class="col-md-4 offset-md-1">
                                <?php echo $this->render(VIEWS . "/admin/logs-today.php", ["countToday" => $countToday]); ?>
                            </div>
                        </div>
                        
                        <div class="form-container-super">
                            <div class="table-responsive mt-4">
                                
                                <label class="form-label">
                                    <?= \NGine\Translate::get("nouveauprojet.dashboard.logs.listLogs.label"); ?>
                                </label>
                                
                                <table class="table table-vcenter card-table border-top border-start border-end">
                                    <thead>
                                    <tr>
                                        <th><?= \NGine\Translate::get("nouveauprojet.dashboard.logs.listLogs.date"); ?></th>
                                        <th><?= \NGine\Translate::get("nouveauprojet.dashboard.logs.listLogs.ip"); ?></th>
                                        <th><?= \NGine\Translate::get("nouveauprojet.dashboard.logs.listLogs.request"); ?></th>
                                        <th><?= \NGine\Translate::get("nouveauprojet.dashboard.logs.listLogs.level"); ?></th>
                                        <th><?= \NGine\Translate::get("nouveauprojet.dashboard.logs.listLogs.message"); ?></th>
                                    </tr>
                                    </thead>
                                    
                                    <tbody>
                                    <?php foreach ($datas as $data): ?>
                                        <tr>
                                            <td><?= $data->date; ?></td>
                                            <td><?= $data->ip; ?></td>
                                            <td><?= $data->request; ?></td>
                                            <td><?= $data->level; ?></td>
                                            <td><?= $data->message; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                
                                </table>
                            </div>
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>
    
    </div>
</div>