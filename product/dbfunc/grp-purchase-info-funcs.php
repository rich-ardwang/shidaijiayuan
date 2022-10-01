<?php

/*
 * grp-purchase-info-funcs.php
 *
 * -Complement the functions handing the data for tbl_grp_purchase_info table.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-10
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once("db-common.php");

// Define return code for the functions of group purchase info table.
define("DB_CODE_GPI_NOT_EXIST", -41);
define("DB_CODE_GET_GPI_TYPE_WRONG", -42);
define("DB_CODE_HANDLE_GPI_TYPE_WRONG", -43);

// Define return message for the functions of group purchase info table.
define("DB_MSG_GPI_NOT_EXIST", "Group purchase info is not exist!");
define("DB_MSG_GET_GPI_TYPE_WRONG", "Get Group purchase info type is not correct!");
define("DB_MSG_HANDLE_GPI_TYPE_WRONG", "Handle Group purchase info type is not correct!");

// Define flags for getting group purchase info.
define("GET_ALL_GPI_INFOS", 0);
define("GET_GPI_INFO_BY_ID", 1);
define("GET_GPI_INFO_BY_STATE", 2);

// Get group purchase info.
function get_gpi_info($conn, $fids, $vals, $get_type) {
    $table = [
        TBL_NAME=>"tbl_grp_purchase_info",
        TBL_SELECT_FIELDS=>[
            gpiID=>[TBL_FIELD_NAME=>"gpiID"], gpiName=>[TBL_FIELD_NAME=>"gpiName"],
            gpiImgName=>[TBL_FIELD_NAME=>"gpiImgName"], gpiDetail=>[TBL_FIELD_NAME=>"gpiDetail"],
            gpiPrice=>[TBL_FIELD_NAME=>"gpiPrice"], usrID=>[TBL_FIELD_NAME=>"usrID"],
            gpiPublisher=>[TBL_FIELD_NAME=>"gpiPublisher"], gpiEndTime=>[TBL_FIELD_NAME=>"gpiEndTime"], 
            gpiState=>[TBL_FIELD_NAME=>"gpiState"], gpiCrtTime=>[TBL_FIELD_NAME=>"gpiCrtTime"],
            gpiUpdTime=>[TBL_FIELD_NAME=>"gpiUpdTime"], all=>[TBL_FIELD_NAME=>"*"]
        ]
    ];
    $where_syntax_suffix = " order by gpiCrtTime desc";
    if (GET_GPI_INFO_BY_ID == $get_type) {
        $where_syntax = "gpiID='" . $conn->real_escape_string($vals[gpiID]) . "'" . $where_syntax_suffix;
        $table[TBL_WHERE_SYNTAX] = $where_syntax;
    } else if (GET_GPI_INFO_BY_STATE == $get_type) {
        $where_syntax = "gpiState='" . $conn->real_escape_string($vals[gpiState]) . "'" . $where_syntax_suffix;
        $table[TBL_WHERE_SYNTAX] = $where_syntax;
    } else if (GET_ALL_GPI_INFOS == $get_type) {
        $table[TBL_WHERE_SYNTAX] = $where_syntax_suffix;
    } else {
        return DB_CODE_GET_ACCOUNT_TYPE_WRONG;
    }
    return get_records($conn, $table, $fids);
}

// Basic handler for group purchase info table such as insert, update and delete.
function gpi_basic_handler($conn, $gpi_info, $gpi_id, $opt, $auto_commit) {
    $set_fields_info = [
        gpiName=>[TBL_FIELD_NAME=>"gpiName"], gpiImgName=>[TBL_FIELD_NAME=>"gpiImgName"],
        gpiDetail=>[TBL_FIELD_NAME=>"gpiDetail"], gpiPrice=>[TBL_FIELD_NAME=>"gpiPrice"],
        gpiEndTime=>[TBL_FIELD_NAME=>"gpiEndTime"], gpiState=>[TBL_FIELD_NAME=>"gpiState"],
        usrID=>[TBL_FIELD_NAME=>"usrID"], gpiPublisher=>[TBL_FIELD_NAME=>"gpiPublisher"],
        gpiCrtTime=>[TBL_FIELD_NAME=>"gpiCrtTime"], gpiUpdTime=>[TBL_FIELD_NAME=>"gpiUpdTime"]
    ];
    $table = [ TBL_NAME=>"tbl_grp_purchase_info", TBL_PRIMARY_KEY_NAME=>"gpiID" ];
    if (DB_CODE_INSERT == $opt) {
        $table[TBL_SET_FIELDS] = $set_fields_info;
    } else if (DB_CODE_UPDATE == $opt) {
        $ret = check_gpi_exist_by_id($conn, $gpi_id);
        if (DB_CODE_GPI_NOT_EXIST == $ret) {
            return DB_CODE_GPI_NOT_EXIST;
        }
        $table[TBL_SET_FIELDS] = $set_fields_info;
        $table[TBL_PRIMARY_KEY_VALUE] = $conn->real_escape_string($gpi_id);
    } else {
        return DB_CODE_HANDLE_GPI_TYPE_WRONG;
    }
    return record_basic_handling($conn, $table, $gpi_info, $opt, $auto_commit);
}

// Get all group purchase infos.
function get_all_gpi_infos($conn, $fids) {
    return get_gpi_info($conn, $fids, 0, GET_ALL_GPI_INFOS);
}

// Get group purchase info by ID.
function get_gpi_info_by_id($conn, $fids, $gpi_id) {
    $vals = [ gpiID=>$gpi_id ];
    return get_gpi_info($conn, $fids, $vals, GET_GPI_INFO_BY_ID);
}

// Get group purchase info by state.
function get_gpi_info_by_state($conn, $fids, $gpi_state) {
    $vals = [ gpiState=>$gpi_state ];
    return get_gpi_info($conn, $fids, $vals, GET_GPI_INFO_BY_STATE);
}

// Check group purchase info exist by id.
function check_gpi_exist_by_id($conn, $gpi_id) {
    $fids = [ gpiID ];
    $result = get_gpi_info_by_id($conn, $fids, $gpi_id);
    if (is_array($result)) {
        return $result[0]['gpiID'];
    } else {
        return DB_MSG_GPI_NOT_EXIST;
    }
}

// Insert into gpi info to the table.
function insert_gpi_info($conn, $gpi_info, $auto_commit=true) {
    return gpi_basic_handler($conn, $gpi_info, 0, DB_CODE_INSERT, $auto_commit);
}

// Update gpi info by id.
function update_gpi_info_by_id($conn, $gpi_info, $gpi_id, $auto_commit=true) {
    return gpi_basic_handler($conn, $gpi_info, $gpi_id, DB_CODE_UPDATE, $auto_commit);
}

// Cancel group purchase info by id.
function cancel_gpi_info_by_id($conn, $gpi_id, $auto_commit=true) {
    $gpi_info = array(gpiState=>0, gpiUpdTime=>date('Y-m-d H:i:s'));
    return gpi_basic_handler($conn, $gpi_info, $gpi_id, DB_CODE_UPDATE, $auto_commit);
}

// End group purchase info by id.
function end_gpi_info_by_id($conn, $gpi_id, $auto_commit=true) {
    $gpi_info = array(gpiState=>2, gpiUpdTime=>date('Y-m-d H:i:s'));
    return gpi_basic_handler($conn, $gpi_info, $gpi_id, DB_CODE_UPDATE, $auto_commit);
}
