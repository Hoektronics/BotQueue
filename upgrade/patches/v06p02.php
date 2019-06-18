<?php
include("../patches.php");

$patch = new Patch(27);

if (!$patch->exists()) {

    $statements = array(
        "ALTER TABLE users MODIFY COLUMN pass_reset_hash char(40) NULL",
        "ALTER TABLE users MODIFY COLUMN location varchar(255) NULL",
        "ALTER TABLE users MODIFY COLUMN birthday date NULL",
        "UPDATE users SET birthday=NULL",
        "UPDATE users set last_active = '0000-01-01' where last_active<'0000-01-01'",
        "ALTER TABLE users MODIFY COLUMN last_active date NULL",
    );

    foreach ($statements as $sql) {
        db()->execute($sql);
    }

    $patch->finish("Fixing users table");
}