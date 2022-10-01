<?php

/*
 * user-funcs.php
 *
 * -Complement the functions handing the data for tbl_user table.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-09
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once ("db-common.php");

// Define return code for the functions of user table.
define("DB_CODE_USER_NOT_EXIST", -31);
define("DB_CODE_USER_ALREADY_EXIST", -32);
define("DB_CODE_GET_USER_TYPE_WRONG", -33);
define("DB_CODE_HANDLE_USER_TYPE_WRONG", -34);

// Define return message for the functions of user table.
define("DB_MSG_USER_NOT_EXIST", "User is not exist!");
define("DB_MSG_USER_ALREADY_EXIST", "User is already exist!");
define("DB_MSG_GET_USER_TYPE_WRONG", "Get user type is not correct!");
define("DB_MSG_HANDLE_USER_TYPE_WRONG", "Handle user type is not correct!");

// Define flags for getting user info.
define("GET_ALL_USER_INFOS", 0);
define("GET_USER_INFO_BY_ID", 1);
define("GET_USER_INFO_BY_BID_FID", 2);
define("GET_ALL_USER_INFOS_NO_STATE", 3);
define("GET_USER_INFO_BY_ID_NO_STATE", 4);
define("GET_USER_INFO_BY_BID_FID_NO_STATE", 5);

// Get user info.
function get_user_info($conn, $fids, $vals, $get_type) {
    $table = [
        TBL_NAME=>"tbl_user",
        TBL_SELECT_FIELDS=>[
            usrID=>[TBL_FIELD_NAME=>"usrID"], usrBid=>[TBL_FIELD_NAME=>"usrBid"],
            usrFid=>[TBL_FIELD_NAME=>"usrFid"], usrPwd=>[TBL_FIELD_NAME=>"usrPwd"],
            usrType=>[TBL_FIELD_NAME=>"usrType"], usrPhoneNum=>[TBL_FIELD_NAME=>"usrPhoneNum"],
            usrState=>[TBL_FIELD_NAME=>"usrState"], usrCrtTime=>[TBL_FIELD_NAME=>"usrCrtTime"],
            usrUpdTime=>[TBL_FIELD_NAME=>"usrUpdTime"], all=>[TBL_FIELD_NAME=>"*"]
        ]
    ];
    $where_syntax_prefix = "usrState <> 0";
    if (GET_USER_INFO_BY_ID === $get_type) {
        $where_syntax = $where_syntax_prefix . " and usrID='" . $conn->real_escape_string($vals[usrID]) . "'";
        $table[TBL_WHERE_SYNTAX] = $where_syntax;
    } else if (GET_USER_INFO_BY_BID_FID === $get_type) {
        $where_syntax = $where_syntax_prefix . " and usrBid='" . $conn->real_escape_string($vals[usrBid])
                . "' and usrFid='" . $conn->real_escape_string($vals[usrFid]) . "'";
        $table[TBL_WHERE_SYNTAX] = $where_syntax;
    } else if (GET_ALL_USER_INFOS === $get_type) {
        $table[TBL_WHERE_SYNTAX] = $where_syntax_prefix;
    } else if (GET_USER_INFO_BY_ID_NO_STATE === $get_type) {
        $where_syntax = "usrID='" . $conn->real_escape_string($vals[usrID]) . "'";
        $table[TBL_WHERE_SYNTAX] = $where_syntax;
    } else if (GET_USER_INFO_BY_BID_FID_NO_STATE === $get_type) {
        $where_syntax = "usrBid='" . $conn->real_escape_string($vals[usrBid])
                . "' and usrFid='" . $conn->real_escape_string($vals[usrFid]) . "'";
        $table[TBL_WHERE_SYNTAX] = $where_syntax;
    } else if (GET_ALL_USER_INFOS_NO_STATE === $get_type) {
        // Do nothing.
    } else {
        return DB_CODE_GET_USER_TYPE_WRONG;
    }
    return get_records($conn, $table, $fids);
}

// Basic handler for user table such as insert, update and delete.
function user_basic_handler($conn, $user_info, $uid, $opt, $auto_commit) {
    $set_fields_info = [
        usrBid=>[TBL_FIELD_NAME=>"usrBid"], usrFid=>[TBL_FIELD_NAME=>"usrFid"],
        usrPwd=>[TBL_FIELD_NAME=>"usrPwd"], usrType=>[TBL_FIELD_NAME=>"usrType"],
        usrPhoneNum=>[TBL_FIELD_NAME=>"usrPhoneNum"], usrState=>[TBL_FIELD_NAME=>"usrState"],
        usrCrtTime=>[TBL_FIELD_NAME=>"usrCrtTime"], usrUpdTime=>[TBL_FIELD_NAME=>"usrUpdTime"]
    ];
    $table = [ TBL_NAME=>"tbl_user", TBL_PRIMARY_KEY_NAME=>"usrID" ];
    if (DB_CODE_INSERT === $opt || DB_CODE_INSERT_NO_STATE === $opt) {
        // Check user exist while opt is insert into.
        if (DB_CODE_INSERT === $opt) {
            $ret = check_user_exist_by_bid_fid($conn, $user_info[usrBid], $user_info[usrFid]);
        } else {
            $ret = check_user_exist_by_bid_fid_no_state($conn, $user_info[usrBid], $user_info[usrFid]);
        }
        if ($ret > 0) {
            return DB_CODE_USER_ALREADY_EXIST;
        }
        $opt = DB_CODE_INSERT;
        $table[TBL_SET_FIELDS] = $set_fields_info;
    } else if (DB_CODE_UPDATE === $opt || DB_CODE_UPDATE_NO_STATE === $opt) {
        // Check user exist while opt is update or delete
        if (DB_CODE_UPDATE === $opt) {
            $ret = check_user_exist_by_user_id($conn, $uid);
        } else {
            $ret = check_user_exist_by_user_id_no_state($conn, $uid);
        }
        if (DB_CODE_USER_NOT_EXIST === $ret) {
            return DB_CODE_USER_NOT_EXIST;
        }
        $opt = DB_CODE_UPDATE;
        $table[TBL_PRIMARY_KEY_VALUE] = $uid;
        $table[TBL_SET_FIELDS] = $set_fields_info;
    } else if (DB_CODE_DELETE === $opt || DB_CODE_DELETE_NO_STATE === $opt) {
        // Check user exist while opt is update or delete
        if (DB_CODE_DELETE === $opt) {
            $ret = check_user_exist_by_user_id($conn, $uid);
        } else {
            $ret = check_user_exist_by_user_id_no_state($conn, $uid);
        }
        if (DB_CODE_USER_NOT_EXIST == $ret) {
            return DB_CODE_USER_NOT_EXIST;
        }
        $opt = DB_CODE_DELETE;
        $table[TBL_PRIMARY_KEY_VALUE] = $uid;
    } else {
        return DB_CODE_HANDLE_USER_TYPE_WRONG;
    }
    return record_basic_handling($conn, $table, $user_info, $opt, $auto_commit);
}

// Get all user infos.
function get_all_user_infos($conn, $fids) {
    return get_user_info($conn, $fids, 0, GET_ALL_USER_INFOS);
}

// Get user info by user ID.
function get_user_info_by_uid($conn, $fids, $uid) {
    $vals = [usrID=>$uid];
    return get_user_info($conn, $fids, $vals, GET_USER_INFO_BY_ID);
}

// Get user info by building code and family code.
function get_user_info_by_bid_fid($conn, $fids, $bid, $fid) {
    $vals = [usrBid=>$bid, usrFid=>$fid];
    return get_user_info($conn, $fids, $vals, GET_USER_INFO_BY_BID_FID);
}

// Check user exist by building code and family code.
function check_user_exist_by_bid_fid($conn, $bid, $fid) {
    $fids = [ usrID ];
    $result = get_user_info_by_bid_fid($conn, $fids, $bid, $fid);
    if (is_array($result)) {
        return $result[0]['usrID'];
    } else {
        return DB_CODE_USER_NOT_EXIST;
    }
}

// Check user exist by user id.
function check_user_exist_by_user_id($conn, $uid) {
    $fids = [ usrID ];
    $result = get_user_info_by_uid($conn, $fids, $uid);
    if (is_array($result)) {
        return $result[0]['usrID'];
    } else {
        return DB_CODE_USER_NOT_EXIST;
    }
}

// Check user valid by user id.
function check_user_valid_by_user_id($conn, $uid) {
    $fids = [ usrState ];
    $result = get_user_info_by_uid($conn, $fids, $uid);
    if (is_array($result)) {
        if ($result[0]['usrState'] !== "1") {
            return true;
        }
    }
    return false;
}

// Insert into user to the table.
function insert_user($conn, $user_info, $auto_commit=true) {
    return user_basic_handler($conn, $user_info, 0, DB_CODE_INSERT, $auto_commit);
}

// Update user info by user id.
function update_user_info_by_uid($conn, $user_info, $uid, $auto_commit=true) {
    return user_basic_handler($conn, $user_info, $uid, DB_CODE_UPDATE, $auto_commit);
}

// Delete user info by user id.
function delete_user_by_uid($conn, $uid, $auto_commit=true) {
    $user_info = array(usrState=>"0", usrUpdTime=>date('Y-m-d H:i:s'));
    return user_basic_handler($conn, $user_info, $uid, DB_CODE_UPDATE, $auto_commit);
}

// Delete user info by user id really, not only update the state flag.
function real_delete_user_by_uid($conn, $uid, $auto_commit=true) {
    $user_info = array();
    return user_basic_handler($conn, $user_info, $uid, DB_CODE_DELETE, $auto_commit);
}

// Lock user account by user id.
function lock_user_by_uid($conn, $uid, $auto_commit=true) {
    $user_info = array(usrState=>"1");
    return user_basic_handler($conn, $user_info, $uid, DB_CODE_UPDATE, $auto_commit);
}

// Unlock user account by user id.
function unlock_user_by_uid($conn, $uid, $auto_commit=true) {
    $user_info = array(usrState=>"8");
    return user_basic_handler($conn, $user_info, $uid, DB_CODE_UPDATE, $auto_commit);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////
/// Functions will handle user info without checking state flag as below.
///

// Get all user infos without state.
function get_all_user_infos_no_state($conn, $fids) {
    return get_user_info($conn, $fids, 0, GET_ALL_USER_INFOS_NO_STATE);
}

// Get user info by user ID without state.
function get_user_info_by_uid_no_state($conn, $fids, $uid) {
    return get_user_info($conn, $fids, $uid, GET_USER_INFO_BY_ID_NO_STATE);
}

// Get user info by building code and family code without state.
function get_user_info_by_bid_fid_no_state($conn, $fids, $bid, $fid) {
    $vals = [usrBid=>$bid, usrFid=>$fid];
    return get_user_info($conn, $fids, $vals, GET_USER_INFO_BY_BID_FID_NO_STATE);
}

// Check user exist by building code and family code without state.
function check_user_exist_by_bid_fid_no_state($conn, $bid, $fid) {
    $fids = [ usrID ];
    $result = get_user_info_by_bid_fid_no_state($conn, $fids, $bid, $fid);
    if (is_array($result)) {
        return $result[0]['usrID'];
    } else {
        return DB_CODE_USER_NOT_EXIST;
    }
}

// Check user exist by user id without state.
function check_user_exist_by_user_id_no_state($conn, $uid) {
    $fids = [ usrID ];
    $result = get_user_info_by_uid_no_state($conn, $fids, $uid);
    if (is_array($result)) {
        return $result[0]['usrID'];
    } else {
        return DB_CODE_USER_NOT_EXIST;
    }
}

// Insert into user to the table without state.
function insert_user_no_state($conn, $user_info, $auto_commit=true) {
    return user_basic_handler($conn, $user_info, 0, DB_CODE_INSERT_NO_STATE, $auto_commit);
}

// Update user info by user id without state.
function update_user_info_by_uid_no_state($conn, $user_info, $uid, $auto_commit=true) {
    return user_basic_handler($conn, $user_info, $uid, DB_CODE_UPDATE_NO_STATE, $auto_commit);
}

// Delete user info by user id without state.
function delete_user_by_uid_no_state($conn, $uid, $auto_commit=true) {
    $user_info = array(usrState=>"0", usrUpdTime=>date('Y-m-d H:i:s'));
    return user_basic_handler($conn, $user_info, $uid, DB_CODE_UPDATE_NO_STATE, $auto_commit);
}

// Delete user info by user id without state really, not only update the state flag.
function real_delete_user_by_uid_no_state($conn, $uid, $auto_commit=true) {
    $user_info = array();
    return user_basic_handler($conn, $user_info, $uid, DB_CODE_DELETE_NO_STATE, $auto_commit);
}
