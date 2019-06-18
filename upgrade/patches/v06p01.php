<?php
include("../patches.php");

$patch = new Patch(26);

if (!$patch->exists()) {

    $sql = "ALTER TABLE email_queue MODIFY COLUMN sent_date datetime NULL";
    db()->execute($sql);

    $patch->finish("Updating email_queue field to be nullable");
}