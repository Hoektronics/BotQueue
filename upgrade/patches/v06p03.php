<?php
include("../patches.php");

$patch = new Patch(28);

if (!$patch->exists()) {

    $statements = array(
        "ALTER TABLE bots MODIFY COLUMN oauth_token_id int(11) unsigned NULL",
        "ALTER TABLE bots MODIFY COLUMN client_name varchar(255) NULL",
        "ALTER TABLE bots MODIFY COLUMN client_uid varchar(255) NULL",
        "ALTER TABLE bots MODIFY COLUMN client_version varchar(255) NULL",
        "UPDATE bots set last_seen = '0000-01-01' where last_seen<'0000-01-01'",
        "ALTER TABLE bots MODIFY COLUMN last_seen datetime NULL",
        "ALTER TABLE bots MODIFY COLUMN slice_config_id int(11) NULL",
        "ALTER TABLE bots MODIFY COLUMN slice_engine_id int(11) NULL",
        "ALTER TABLE bots MODIFY COLUMN temperature_data longtext NULL",
        "ALTER TABLE bots MODIFY COLUMN remote_ip varchar(255) NULL",
        "ALTER TABLE bots MODIFY COLUMN local_ip varchar(255) NULL",
        "ALTER TABLE bots MODIFY COLUMN driver_config text NULL",
    );

    foreach ($statements as $sql) {
        db()->execute($sql);
    }

    $patch->finish("Fixing bots table");
}