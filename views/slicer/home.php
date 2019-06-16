<div class="row">
    <div class="span12">
        <?php if (User::isAdmin()): ?>
            <p>
                <a class="btn btn-primary" href="/slicer/create">Create New Slice Engine</a>
                <a class="btn btn-primary" href="/slicer/import">Import from github</a>
            </p>
        <?php endif ?>
        <?php if (!empty($slicers)): ?>
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                <th>Slicer</th>
                <th>Added</th>
                <?php if (User::isAdmin()): ?>
                    <th>Manage</th>
                <?php endif ?>
                </thead>
                <tbody>
                <?php foreach ($slicers AS $row): ?>
                    <?php $engine = $row['SliceEngine'] ?>
                    <tr>
                        <td><?php echo $engine->getLink() ?></td>
                        <td><?php echo Utility::formatDateTime($engine->get('add_date')) ?></td>
                        <?php if (User::isAdmin()): ?>
                            <td>
                                <a class="btn btn-mini" href="<?php echo $engine->getUrl() ?>/edit"><i class="icon-cog"></i>
                                    edit</a>
                                <a class="btn btn-mini" href="<?php echo $engine->getUrl() ?>/delete"><i
                                        class="icon-remove"></i> delete</a>
                            </td>
                        <?php endif ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>
</div>
<?php if (User::isLoggedIn()): ?>
    <h2>My Slice Engine Configurations <a class="btn btn-primary" href="/slicer/createconfig">Create New Config</a></h2>
    <div class="row">
        <div class="span12">
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                <tr>
                    <th>Config Name</th>
                    <th>Slice Engine</th>
                    <th>Add Date</th>
                    <th>Edit Date</th>
                    <th>Manage</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($configs AS $row): ?>
                    <?php $config = $row['SliceConfig'] ?>
                    <?php $engine = $row['SliceEngine'] ?>
                    <tr>
                        <td><?php echo $config->getLink() ?></td>
                        <td><?php echo $engine->getLink() ?></td>
                        <td><?php echo Utility::formatDateTime($config->get('add_date')) ?></td>
                        <td><?php echo Utility::formatDateTime($config->get('edit_date')) ?></td>
                        <td>
                            <a class="btn btn-mini" href="<?php echo $config->getUrl() ?>/edit"><i class="icon-cog"></i>
                                edit</a>
                            <a class="btn btn-mini" href="<?php echo $config->getUrl() ?>/delete"><i class="icon-remove"></i>
                                delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif ?>
