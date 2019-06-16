<? if ($megaerror): ?>
    <?php echo Controller::byName('htmltemplate')->renderView('errorbar', array('message' => $megaerror)) ?>
<? else: ?>
    <div class="row">
        <div class="span12">
            <table class="table table-striped table-bordered table-condensed">
                <tbody>
                <? if (User::isAdmin()): ?>
                    <tr>
                        <th>Manage:</th>
                        <td>
                            <a class="btn btn-mini" href="<?php echo $engine->getUrl() ?>/edit"><i class="icon-cog"></i>
                                edit</a>
                            <a class="btn btn-mini" href="<?php echo $engine->getUrl() ?>/delete"><i class="icon-remove"></i>
                                delete</a>
                        </td>
                    </tr>
                <? endif ?>
                <tr>
                    <th>Engine Name:</th>
                    <td><?php echo $engine->getLink() ?></td>
                </tr>
                <tr>
                    <th>Is Public:</th>
                    <td><?php echo $engine->get('is_public') ? 'yes' : 'no' ?></td>
                </tr>
                <tr>
                    <th>Is Featured:</th>
                    <td><?php echo $engine->get('is_featured') ? 'yes' : 'no' ?></td>
                </tr>
                <tr>
                    <th>Add Date:</th>
                    <td><?php echo Utility::formatDateTime($engine->get('add_date')) ?></td>
                </tr>
                <tr>
                    <th>Engine Path:</th>
                    <td><?php echo $engine->get('engine_path') ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <h2>Slice Engine Configurations <a class="btn btn-primary" href="<?php echo $engine->getUrl() ?>/createconfig">Create New
            Config</a></h2>
    <div class="row">
        <div class="span12">
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                <tr>
                    <th>Config Name</th>
                    <? if (User::isAdmin()): ?>
                        <th>User</th>
                    <? endif ?>
                    <th>Add Date</th>
                    <th>Edit Date</th>
                    <th>Manage</th>
                </tr>
                </thead>
                <tbody>
                <? foreach ($configs AS $row): ?>
                    <? $config = $row['SliceConfig'] ?>
                    <tr>
                        <td><?php echo $config->getLink() ?></td>
                        <? if (User::isAdmin()): ?>
                            <td><?php echo $config->getUser()->getLink() ?></td>
                        <? endif ?>
                        <td><?php echo Utility::formatDateTime($config->get('add_date')) ?></td>
                        <td><?php echo Utility::formatDateTime($config->get('edit_date')) ?></td>
                        <td>
                            <a class="btn btn-mini" href="<?php echo $config->getUrl() ?>/edit"><i class="icon-cog"></i>
                                edit</a>
                            <a class="btn btn-mini" href="<?php echo $config->getUrl() ?>/delete"><i class="icon-remove"></i>
                                delete</a>
                        </td>
                    </tr>
                <? endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
<? endif ?>