<?php
include("../patches.php");

$patch = new Patch(29);

if (!$patch->exists()) {

    $statements = array(
        "UPDATE s3_files set add_date = '0000-01-01' where add_date<'0000-01-01'",
        "ALTER TABLE s3_files MODIFY COLUMN parent_id int(11) NULL",
    );

    foreach ($statements as $sql) {
        db()->execute($sql);
    }

    $patch->finish("Fixing s3_files table");
}