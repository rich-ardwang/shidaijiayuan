<?php

/*
 * db-conn.php
 *
 * -Complement the DB function of connecting to MariaDB.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-09
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once ("../../conf/db-config.php");

function db_connect() {
    global $db_host, $db_user, $db_psw, $db_name, $db_port, $db_sock;
    $proxy = new mysqli($db_host, $db_user, $db_psw, $db_name, $db_port, $db_sock);
    if (!$proxy) {
        return false;
    }
    $proxy->autocommit(true);
    return $proxy;
}

function db_result_to_array($result) {
    $res_array = array();
    for ($count = 0; $row = $result->fetch_assoc(); $count++) {
        $res_array[$count] = $row;
    }
    return $res_array;
}
