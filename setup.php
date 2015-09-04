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

if (!$db->query(<<<SQL
    CREATE TABLE `bbcs_v3`.`mother` (
        MID VARCHAR(250) PRIMARY KEY
    );
SQL
)) printf("Error: %s\n", $db->error);

if (!$db->query(<<<SQL
    CREATE TABLE `bbcs_v3`.`mother_details` (
        MID VARCHAR(250) PRIMARY KEY,
        password VARCHAR(32) NOT NULL,
        FOREIGN KEY (MID) REFERENCES mother(MID)
    );
SQL
)) printf("Error: %s\n", $db->error);

if (!$db->query(<<<SQL
    INSERT INTO `bbcs_v3`.`mother` (MID) VALUES ('p028');
SQL
)) printf("Error: %s\n", $db->error);
    
if (!$db->query(<<<SQL
    INSERT INTO `bbcs_v3`.`mother_details` (MID, password) VALUES ('p028', MD5('student'));
SQL
)) printf("Error: %s\n", $db->error);

?>