<?php

/*
 * register.php
 *
 * -Receive data from the form submitted by register and handle it.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2020-04-09
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once ("../dbfunc/db-conn.php");
require_once ("../dbfunc/user-funcs.php");
require_once ("../dbfunc/db-common.php");
require_once ("data-verify.php");

// JS need not parse the response data to JSON.
header("Content-Language: charset=zh-cn");
header('Content-Type: application/json; charset=uft-8');

// Get the data from the register form.
$reg_form_data = isset($_POST['register_data']) ? $_POST['register_data'] : '';
if (empty($reg_form_data)) {
    $head = ['retCode'=>ERR_CODE_EMPTY_DATA, 'retMsg'=>ERR_MSG_EMPTY_DATA];
    response_json_data($head, null);
    exit;
}

// Decode the form data from json string to php array.
$reg_data = json_decode($reg_form_data, true);
if (!$reg_data) {
    $head = ['retCode'=>ERR_CODE_PARSE_FAILED, 'retMsg'=>ERR_MSG_PARSE_FAILED];
    response_json_data($head, null);
    exit;
}

// Check the data from the register form and return check result if necessary.
$chk_head = check_register_data($reg_data);
if (0 != $chk_head.ret_code) {
    response_json_data($chk_head, null);
    exit;
}

// Connect to database.
$conn = db_connect();
if (!$conn) {
    $head = ['retCode'=>ERR_CODE_DB_CONN_FAILED, 'retMsg'=>ERR_MSG_DB_CONN_FAILED];
    response_json_data($head, null);
    exit;
}

// Register a new user for group purchase system to DB.
$reg_info = create_reg_info($reg_data);
$ret_code = insert_user($conn, $reg_info);
$ret_head = array();
switch ($ret_code) {
    case DB_CODE_USER_ALREADY_EXIST:
        $ret_head = ['retCode'=>ERR_CODE_DB_USER_ALREADY_EXIST, 'retMsg'=>ERR_MSG_DB_USER_ALREADY_EXIST];
        break;
    case DB_CODE_SQL_SYNTAX_WRONG:
        $ret_head = ['retCode'=>ERR_CODE_DB_SQL_SYNTAX_WRONG, 'retMsg'=>ERR_MSG_DB_SQL_SYNTAX_WRONG];
        break;
    case DB_CODE_INSERT_FAILED:
        $ret_head = ['retCode'=>ERR_CODE_DB_INSERT_USER_FAILED, 'retMsg'=>ERR_MSG_DB_INSERT_USER_FAILED];
        break;
    case DB_CODE_OPT_OK:
        $ret_head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
        break;
    default:
        // Nothing to do.
}

// Return the register result to JS client.
response_json_data($ret_head, null);
