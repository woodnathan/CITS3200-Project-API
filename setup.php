<?php

function ini_test($option, $expected_value)
{
    $value = ini_get($option);
    $value = strtolower(trim($value));
    $expected_value = strtolower(trim($expected_value));
    return ($value == $expected_value);
}

if (!(ini_test('display_errors', 1) && ini_test('error_reporting', E_ALL)))
{
    exit('Cannot be executed in production');
}

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/admin/scripts/db_connect.php');

$db = connect_db();

function execute($query)
{
    global $db;

    if (!$db->query($query))
    {
        printf("Error: %s\n", $db->error);
        printf("Query: %s\n", $query);
    }
}

execute('DROP TABLE `bbcs_v3`.`r_calc_feed_and_sample`;');
execute('DROP TABLE `bbcs_v3`.`sample_reading`;');
execute('DROP TABLE `bbcs_v3`.`mother_details`;');
execute('DROP TABLE `bbcs_v3`.`mother`;');

/**
 * Create tables
 */

execute(<<<SQL
    CREATE TABLE `bbcs_v3`.`mother` (
        MID VARCHAR(250) PRIMARY KEY,
        last_update_at DATETIME NULL
    );
SQL
);

execute(<<<SQL
    CREATE TABLE `bbcs_v3`.`mother_details` (
        MID VARCHAR(250) PRIMARY KEY,
        password VARCHAR(32) NOT NULL,
        FOREIGN KEY (MID) REFERENCES mother(MID)
    );
SQL
);

execute(<<<SQL
    CREATE TABLE `bbcs_v3`.`sample_reading` (
        SID INTEGER AUTO_INCREMENT PRIMARY KEY,
        time DATETIME NOT NULL,
        weight DECIMAL(6, 2) NOT NULL,
        fore_hind ENUM('B', 'A') NOT NULL,
        left_right ENUM('L', 'R') NULL,
        comment VARCHAR(250) NULL,
        feed_type ENUM('B', 'E', 'S') NOT NULL,
        complementary_type ENUM('E', 'F') NULL,
        ignore_calc ENUM('Y', 'N') NOT NULL,
        fore_sid INTEGER NULL
    );
SQL
);

execute(<<<SQL
    CREATE TABLE `bbcs_v3`.`r_calc_feed_and_sample` (
        MID VARCHAR(250),
        SID INTEGER NOT NULL,
        SNO INTEGER NOT NULL,
        PRIMARY KEY(MID, SID, SNO),
        FOREIGN KEY (MID) REFERENCES mother(MID),
        FOREIGN KEY (SID) REFERENCES sample_reading(SID)
    );
SQL
);

/**
 * Insert data
 */
execute(<<<SQL
    INSERT INTO `bbcs_v3`.`mother` (MID) VALUES ('p028');
SQL
);
    
execute(<<<SQL
    INSERT INTO `bbcs_v3`.`mother_details` (MID, password) VALUES ('p028', MD5('student'));
SQL
);

?>