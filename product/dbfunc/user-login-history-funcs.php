<?php

/*
 * user-login-history-funcs.php
 *
 * -Complement the functions handing the data for tbl_user_login_history table.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-09
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once("db-common.php");

// Define return code for the functions of user login history table.
define("DB_CODE_GET_LOGIN_HISTORY_TYPE_WRONG", -61);
define("DB_CODE_HANDLE_LOGIN_HISTORY_TYPE_WRONG", -62);

// Define return message for the functions of user login history table.
define("DB_MSG_GET_LOGIN_HISTORY_TYPE_WRONG", "Get user login history type is not correct!");
define("DB_MSG_HANDLE_LOGIN_HISTORY_TYPE_WRONG", "Handle user login history type is not correct!");

// Define flags for getting user login history info.
define("GET_ALL_LOGIN_HISTORY_INFOS", 0);
define("GET_LOGIN_HISTORY_INFOS_BY_USER_ID", 1);
define("GET_LOGIN_HISTORY_INFOS_BY_LOGIN_TIME", 2);
define("GET_LOGIN_HISTORY_INFOS_BY_UID_TIME", 3);

// Get user login history info.
function get_login_history_info($conn, $fids, $vals, $get_type) {
    $table = [
        TBL_NAME=>"tbl_user_login_history",
        TBL_SELECT_FIELDS=>[
            ulhID=>[TBL_FIELD_NAME=>"ulhID"], usrID=>[TBL_FIELD_NAME=>"usrID"],
            ulhLoginTime=>[TBL_FIELD_NAME=>"ulhLoginTime"], all=>[TBL_FIELD_NAME=>"*"]
        ]
    ];
    if (GET_LOGIN_HISTORY_INFOS_BY_USER_ID == $get_type) {
        $table[TBL_WHERE_SYNTAX] = "usrID='" . $conn->real_escape_string($vals[usrID]) . "'";
    } else if (GET_LOGIN_HISTORY_INFOS_BY_LOGIN_TIME == $get_type) {
        $table[TBL_WHERE_SYNTAX] = "ulhLoginTime='" . $conn->real_escape_string($vals[ulhLoginTime]) . "'";
    } else if (GET_LOGIN_HISTORY_INFOS_BY_UID_TIME == $get_type) {
        $table[TBL_WHERE_SYNTAX] = "ulhLoginTime='" . $conn->real_escape_string($vals[ulhLoginTime]) . "'"
                . " and usrID='" . $conn->real_escape_string($vals[usrID]) . "'";
    } else if (GET_ALL_LOGIN_HISTORY_INFOS == $get_type) {
        // Do nothing.
    } else {
        return DB_CODE_GET_LOGIN_HISTORY_TYPE_WRONG;
    }
    return get_records($conn, $table, $fids);
}

// Basic handler for user login history table, only support to insert.
function login_history_basic_handler($conn, $ulh_info, $opt, $auto_commit) {
    $set_fields_info = [
        usrID=>[TBL_FIELD_NAME=>"usrID"], ulhLoginTime=>[TBL_FIELD_NAME=>"ulhLoginTime"]
    ];
    $table = [ TBL_NAME=>"tbl_user_login_history" ];
    if (DB_CODE_INSERT == $opt) {
        $table[TBL_PRIMARY_KEY_NAME] = "ulhID";
        $table[TBL_SET_FIELDS] = $set_fields_info;
    } else {
        return DB_CODE_HANDLE_LOGIN_HISTORY_TYPE_WRONG;
    }
    return record_basic_handling($conn, $table, $ulh_info, $opt, $auto_commit);
}

// Get all user login history infos.
function get_all_login_history_infos($conn, $fids) {
    $vals = array();
    return get_login_history_info($conn, $fids, $vals, GET_ALL_LOGIN_HISTORY_INFOS);
}

// Get user login history infos by user ID.
function get_login_history_infos_by_user_id($conn, $fids, $user_id) {
    $vals = [ usrID=>$user_id ];
    return get_login_history_info($conn, $fids, $vals, GET_LOGIN_HISTORY_INFOS_BY_USER_ID);
}

// Get  user login history info by login time.
function get_login_history_info_by_login_time($conn, $fids, $login_time) {
    $vals = [ ulhLoginTime=>$login_time ];
    return get_login_history_info($conn, $fids, $vals, GET_LOGIN_HISTORY_INFOS_BY_LOGIN_TIME);
}

// Get  user login history info by user ID and login time.
function get_login_history_info_by_uid_time($conn, $fids, $user_id, $login_time) {
    $vals = [ usrID=>$user_id, ulhLoginTime=>$login_time ];
    return get_login_history_info($conn, $fids, $vals, GET_LOGIN_HISTORY_INFOS_BY_UID_TIME);
}

// Insert into user login history to the table.
function insert_login_history($conn, $ulh_info, $auto_commit=true) {
    return login_history_basic_handler($conn, $ulh_info, DB_CODE_INSERT, $auto_commit);
}
