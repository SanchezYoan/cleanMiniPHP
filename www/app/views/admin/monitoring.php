<?php
/**
 * @var $logs                  int
 * @var $last_logs             array
 * @var $users_messages_status int
 * @var $stats_messages        int
 * @var $devices               int
 */
?>
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <h1>Monitoring :</h1>
            </div>
        </div>
        <div class="row row-cards">
            <div class="col-12 col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-baseline">
                            <div class="h1 m-0 pe-2"><?= $logs; ?></div>
                            <div class="subheader">logs</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-baseline">
                            <div class="h1 m-0 pe-2"><?= $devices; ?></div>
                            <div class="subheader">devices</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-baseline">
                            <div class="h1 m-0 pe-2"><?= $users_messages_status; ?></div>
                            <div class="subheader">users messages status</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-baseline">
                            <div class="h1 m-0 pe-2"><?= $stats_messages; ?></div>
                            <div class="subheader">stats messages</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title text-uppercase">50 derniers logs</h5>
                    </div>
                    <div id="table-logs" class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-responsive">
                            <thead>
                            <tr>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-id">ID
                                    </button>
                                </th>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-editor">Editor ID
                                    </button>
                                </th>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-user">User ID
                                    </button>
                                </th>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-date">Date
                                    </button>
                                </th>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-ip">IP
                                    </button>
                                </th>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-request">Request
                                    </button>
                                </th>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-level">Level
                                    </button>
                                </th>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-message">Message
                                    </button>
                                </th>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-file">File
                                    </button>
                                </th>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-line">Line
                                    </button>
                                </th>
                                <th>
                                    <button class="table-sort"
                                            data-sort="sort-trace">Trace
                                    </button>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="table-tbody">
                            <?php foreach ($last_logs as $log): ?>
                                <tr>
                                    <td class="sort-id">
                                        <?= $log->id; ?>
                                    </td>
                                    <td class="sort-editor">
                                        <?= $log->fk_editors_id; ?>
                                    </td>
                                    <td class="sort-user">
                                        <?= $log->fk_users_id; ?>
                                    </td>
                                    <td class="sort-date">
                                        <?= $log->date; ?>
                                    </td>
                                    <td class="sort-ip">
                                        <?= $log->ip; ?>
                                    </td>
                                    <td class="sort-request">
                                        <?= $log->request; ?>
                                    </td>
                                    <td class="sort-level">
                                        <?= $log->level; ?>
                                    </td>
                                    <td class="sort-message">
                                        <?= $log->message; ?>
                                    </td>
                                    <td class="sort-file">
                                        <?= $log->file; ?>
                                    </td>
                                    <td class="sort-line">
                                        <?= $log->line; ?>
                                    </td>
                                    <td class="sort-trace">
                                        <?= $log->trace; ?>
                                    </td>
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