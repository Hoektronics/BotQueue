<?php
include("../patches.php");

$patch = new Patch(30);

if (!$patch->exists()) {

    $statements = array(
        "UPDATE jobs set created_time = '0000-01-01' where created_time<'0000-01-01'",
        "UPDATE jobs set taken_time = '0000-01-01' where taken_time<'0000-01-01'",
        "UPDATE jobs set downloaded_time = '0000-01-01' where downloaded_time<'0000-01-01'",
        "UPDATE jobs set finished_time = '0000-01-01' where finished_time<'0000-01-01'",
        "UPDATE jobs set slice_complete_time = '0000-01-01' where slice_complete_time<'0000-01-01'",
        "UPDATE jobs set verified_time = '0000-01-01' where verified_time<'0000-01-01'",
        "ALTER TABLE jobs
            MODIFY COLUMN taken_time datetime NULL,
            MODIFY COLUMN created_time datetime NULL,
            MODIFY COLUMN downloaded_time datetime NULL,
            MODIFY COLUMN finished_time datetime NULL,
            MODIFY COLUMN slice_complete_time datetime NULL,
            MODIFY COLUMN verified_time datetime NULL,
            MODIFY COLUMN temperature_data longtext NULL,
            MODIFY COLUMN webcam_images text NULL",
    );

    foreach ($statements as $sql) {
        db()->execute($sql);
    }

    $patch->finish("Fixing jobs table");
}