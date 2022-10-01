<?php

/*
 * orders-funcs.php
 *
 * -Complement the functions handing the data for tbl_orders table.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-10
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once("db-common.php");

// Define return code for the functions of orders table.
define("DB_CODE_ORDER_NOT_EXIST", -51);
define("DB_CODE_ORDER_ALREADY_EXIST", -52);
define("DB_CODE_GET_ORDERS_TYPE_WRONG", -53);
define("DB_CODE_HANDLE_ORDERS_TYPE_WRONG", -54);

// Define return message for the functions of orders table.
define("DB_MSG_ORDER_NOT_EXIST", "Order is not exist!");
define("DB_MSG_ORDER_ALREADY_EXIST", "Order is already exist!");
define("DB_MSG_GET_ORDERS_TYPE_WRONG", "Get orders type is not correct!");
define("DB_MSG_HANDLE_ORDERS_TYPE_WRONG", "Handle orders type is not correct!");

// Define flags for getting orders info.
define("GET_ORDER_INFO_BY_ID", 0);
define("GET_ORDERS_INFO_BY_USR_ID", 1);
define("GET_ORDERS_INFO_BY_GPI_ID", 2);
define("GET_ORDERS_COUNT_BY_GPI_ID", 3);
define("GET_ORDER_INFO_BY_USRID_GPIID", 4);

// Get orders info from tbl_orders.
function get_orders_info($conn, $fids, $vals, $get_type) {
    $table = [
        TBL_NAME=>"tbl_orders",
        TBL_SELECT_FIELDS=>[
            ordID=>[TBL_FIELD_NAME=>"ordID"], usrID=>[TBL_FIELD_NAME=>"usrID"],
            gpiID=>[TBL_FIELD_NAME=>"gpiID"], buyCnt=>[TBL_FIELD_NAME=>"buyCnt"],
            ordPrice=>[TBL_FIELD_NAME=>"ordPrice"], buyTime=>[TBL_FIELD_NAME=>"buyTime"],
            buyUpdCnt=>[TBL_FIELD_NAME=>"buyUpdCnt"], ordState=>[TBL_FIELD_NAME=>"ordState"],
            ordCrtTime=>[TBL_FIELD_NAME=>"ordCrtTime"], all=>[TBL_FIELD_NAME=>"*"]
        ]
    ];
    if (GET_ORDER_INFO_BY_ID == $get_type) {
        $where_syntax = "ordID='" . $conn->real_escape_string($vals[ordID]) . "'";
        $table[TBL_WHERE_SYNTAX] = $where_syntax;
    } else if (GET_ORDERS_INFO_BY_USR_ID == $get_type) {
        $where_syntax = "usrID='" . $conn->real_escape_string($vals[usrID])
            . "' order by buyTime desc";
        $table[TBL_WHERE_SYNTAX] = $where_syntax;
    } else if (GET_ORDERS_INFO_BY_GPI_ID == $get_type) {
        $table[TBL_WHERE_SYNTAX] = "ordState='v' and gpiID='" . $conn->real_escape_string($vals[gpiID])
            . "' order by buyTime desc";
    } else if (GET_ORDERS_COUNT_BY_GPI_ID == $get_type) {
        // We don't need this element of the table array,
        // instead of using TBL_SELECT_SYNTAX.
        unset($table[TBL_SELECT_FIELDS]);
        $table[TBL_SELECT_SYNTAX] = "count(*) as gpiOrdersCnt";
        $table[TBL_WHERE_SYNTAX] = "ordState='v' and gpiID='" . $conn->real_escape_string($vals[gpiID]) . "'";
    } else if (GET_ORDER_INFO_BY_USRID_GPIID == $get_type) {
        $table[TBL_WHERE_SYNTAX] = "usrID='" . $conn->real_escape_string($vals[usrID])
            . "' and gpiID='" . $conn->real_escape_string($vals[gpiID]) . "'";
    } else {
        return DB_CODE_GET_ORDERS_TYPE_WRONG;
    }
    return get_records($conn, $table, $fids);
}

// Basic handler for orders table such as insert, update and delete.
function orders_basic_handler($conn, $ord_info, $ord_id, $opt, $auto_commit) {
    $set_fields_info = [
        usrID=>[TBL_FIELD_NAME=>"usrID"], gpiID=>[TBL_FIELD_NAME=>"gpiID"],
        buyCnt=>[TBL_FIELD_NAME=>"buyCnt"], ordPrice=>[TBL_FIELD_NAME=>"ordPrice"],
        buyTime=>[TBL_FIELD_NAME=>"buyTime"], buyUpdCnt=>[TBL_FIELD_NAME=>"buyUpdCnt"],
        ordState=>[TBL_FIELD_NAME=>"ordState"], ordCrtTime=>[TBL_FIELD_NAME=>"ordCrtTime"]
    ];
    $table = [ TBL_NAME=>"tbl_orders", TBL_PRIMARY_KEY_NAME=>"ordID" ];
    if (DB_CODE_INSERT == $opt) {
        $ret = check_order_exist_by_usr_gpi_id($conn, $ord_id[usrID], $ord_id[gpiID]);
        if ($ret > 0) {
            return DB_CODE_ORDER_ALREADY_EXIST;
        }
        $table[TBL_SET_FIELDS] = $set_fields_info;
    } else if (DB_CODE_UPDATE == $opt) {
        $ret = check_order_exist_by_id($conn, $ord_id);
        if (DB_CODE_ORDER_NOT_EXIST == $ret) {
            return DB_CODE_ORDER_NOT_EXIST;
        }
        $table[TBL_SET_FIELDS] = $set_fields_info;
        $table[TBL_PRIMARY_KEY_VALUE] = $ord_id;
    } else if (DB_CODE_DELETE == $opt) {
        $ret = check_order_exist_by_id($conn, $ord_id);
        if (DB_CODE_ORDER_NOT_EXIST == $ret) {
            return DB_CODE_ORDER_NOT_EXIST;
        }
        $table[TBL_PRIMARY_KEY_VALUE] = $ord_id;
    } else {
        return DB_CODE_HANDLE_ORDERS_TYPE_WRONG;
    }
    return record_basic_handling($conn, $table, $ord_info, $opt, $auto_commit);
}

// Get order info by ordID.
function get_order_info_by_id($conn, $fids, $ord_id) {
    $vals = [ ordID=>$ord_id ];
    return get_orders_info($conn, $fids, $vals, GET_ORDER_INFO_BY_ID);
}

// Get orders info by usrID.
function get_orders_info_by_uid($conn, $fids, $usr_id) {
    $vals = [ usrID=>$usr_id ];
    return get_orders_info($conn, $fids, $vals, GET_ORDERS_INFO_BY_USR_ID);
}

// Get orders info by gpiID.
function get_orders_info_by_gpi_id($conn, $fids, $gpi_id) {
    $vals = [ gpiID=>$gpi_id ];
    return get_orders_info($conn, $fids, $vals, GET_ORDERS_INFO_BY_GPI_ID);
}

// Get orders count by gpiID.
function get_orders_cnt_by_gpi_id($conn, $gpi_id) {
    $vals = [ gpiID=>$gpi_id ];
    return get_orders_info($conn, 0, $vals, GET_ORDERS_COUNT_BY_GPI_ID);
}

// Get order info by usrID and gpiID.
function get_order_info_by_usr_gpi_id($conn, $fids, $usr_id, $gpi_id) {
    $vals = [ usrID=>$usr_id, gpiID=>$gpi_id ];
    return get_orders_info($conn, $fids, $vals, GET_ORDER_INFO_BY_USRID_GPIID);
}

// Check order exist by usrID and gpiID.
function check_order_exist_by_usr_gpi_id($conn, $usr_id, $gpi_id) {
    $fids = [ ordID ];
    $result = get_order_info_by_usr_gpi_id($conn, $fids, $usr_id, $gpi_id);
    if (is_array($result)) {
        return $result[0]['ordID'];
    } else {
        return DB_CODE_ORDER_NOT_EXIST;
    }
}

// Check order exist by id.
function check_order_exist_by_id($conn, $ord_id) {
    $fids = [ ordID ];
    $result = get_order_info_by_id($conn, $fids, $ord_id);
    if (is_array($result)) {
        return $result[0]['ordID'];
    } else {
        return DB_CODE_ORDER_NOT_EXIST;
    }
}

// Insert into order to the table.
function insert_order($conn, $ord_info, $auto_commit=true) {
    return orders_basic_handler($conn, $ord_info, 0, DB_CODE_INSERT, $auto_commit);
}

// Update order info by id.
function update_order_info_by_id($conn, $ord_info, $ord_id, $auto_commit=true) {
    return orders_basic_handler($conn, $ord_info, $ord_id, DB_CODE_UPDATE, $auto_commit);
}

// Cancel an order by id.
function cancel_order_by_id($conn, $ord_id, $auto_commit=true) {
    $ord_info = [ordState=>"d", buyTime=>date('Y-m-d H:i:s')];
    return update_order_info_by_id($conn, $ord_info, $ord_id, DB_CODE_UPDATE, $auto_commit);
}
