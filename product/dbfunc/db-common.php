<?php

/*
 * db-common.php
 *
 * -The common definitions and functions for all table arranged here.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-10
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

// Define return code for db functions.
define("DB_CODE_OPT_OK", 0);
define("DB_CODE_CONN_DB_FAILED", -1);
define("DB_CODE_SELECT_FAILED", -2);
define("DB_CODE_INSERT_FAILED", -3);
define("DB_CODE_UPDATE_FAILED", -4);
define("DB_CODE_DELETE_FAILED", -5);
define("DB_CODE_SQL_SYNTAX_WRONG", -6);
define("DB_CODE_SELECT_NO_RECORD", -7);
define("DB_CODE_SQL_EXCUTE_FAILED", -8);

// Define return message for db functions.
define("DB_MSG_OPT_OK", "DB operation success.");
define("DB_MSG_CONN_DB_FAILED", "Connect DB failed!");
define("DB_MSG_SELECT_FAILED", "Select failed!");
define("DB_MSG_INSERT_FAILED", "Insert failed!");
define("DB_MSG_UPDATE_FAILED", "Update failed!");
define("DB_MSG_DELETE_FAILED", "Delete failed!");
define("DB_MSG_SQL_SYNTAX_WRONG", "SQL syntax is not correct!");
define("DB_MSG_SELECT_NO_RECORD", "There is no record!");
define("DB_MSG_SQL_EXCUTE_FAILED", "SQL excution failed!");

// Define options for tables.
define("DB_CODE_INSERT", 0);
define("DB_CODE_DELETE", 1);
define("DB_CODE_UPDATE", 2);
define("DB_CODE_INSERT_NO_STATE", 3);
define("DB_CODE_UPDATE_NO_STATE", 4);
define("DB_CODE_DELETE_NO_STATE", 5);

// Define keys for table object.
define("TBL_NAME", 0);
define("TBL_PRIMARY_KEY_NAME", 1);
define("TBL_PRIMARY_KEY_VALUE", 2);
define("TBL_SELECT_FIELDS", 3);
define("TBL_SELECT_SYNTAX", 4);
define("TBL_SET_FIELDS", 5);
define("TBL_WHERE_SYNTAX", 6);
define("TBL_FIELD_NAME", 0);
define("TBL_OPERATOR", 1);
define("TBL_FIELD_VALUE", 2);

// Define the tag for select all.
define("all", 0);

// Define field tag for the user table.
define("usrID", 1);
define("usrBid", 2);
define("usrFid", 3);
define("usrPwd", 4);
define("usrType", 5);
define("usrPhoneNum", 6);
define("usrState", 7);
define("usrCrtTime", 8);
define("usrUpdTime", 9);

// Define field tag for group purchase info table.
define("gpiID", 31);
define("gpiName", 32);
define("gpiImgName", 33);
define("gpiDetail", 34);
define("gpiEndTime", 35);
define("gpiState", 36);
define("gpiCrtTime", 37);
define("gpiUpdTime", 38);

// Define field tag for orders table.
define("ordID", 40);
define("buyCnt", 41);
define("ordPrice", 42);
define("buyTime", 43);
define("buyUpdCnt", 44);
define("ordState", 45);
define("ordCrtTime", 46);

// Define field tag for login history table.
define("ulhID", 50);
define("ulhLoginTime", 51);

// Basic handling for record operating such as insert into, update and delete.
function record_basic_handling($conn, $table, $record_info, $opt, $auto_commit) {
    // Create sql syntax.
    $table_name = $table[TBL_NAME];
    switch ($opt) {
        case DB_CODE_INSERT :
            $pk_name = $table[TBL_PRIMARY_KEY_NAME];
            $sqlSyntax = "insert into " . $table_name . " set " . $pk_name . "=null, ";
            $retErrCode = DB_CODE_INSERT_FAILED;
            break;
        case DB_CODE_UPDATE:
            $sqlSyntax = "update " . $table_name . " set ";
            $retErrCode = DB_CODE_UPDATE_FAILED;
            break;
        case DB_CODE_DELETE:
            // We do not need this case temporarily, because we update the state flag when delete.
            $sqlSyntax = "delete from " . $table_name;
            $retErrCode = DB_CODE_DELETE_FAILED;
            break;
        default:
            // to-do : Record error log.
            return DB_CODE_SQL_SYNTAX_WRONG;
    }
    if (DB_CODE_INSERT == $opt || DB_CODE_UPDATE == $opt) {
        $set_fields_info = $table[TBL_SET_FIELDS];
        foreach ($record_info as $record_key => $record_value) {
            $errFlag = true;
            foreach ($set_fields_info as $set_key => $set_value) {
                if ($record_key == $set_key) {
                    $sqlSyntax .= $set_value[TBL_FIELD_NAME] . "='";
                    $errFlag = false;
                    break;
                }
            }
            if ($errFlag) {
                // to-do : Record error log.
                return DB_CODE_SQL_SYNTAX_WRONG;
            }
            $sqlSyntax .= $conn->real_escape_string($record_value) . "', ";
        }
        $sqlSyntax = rtrim($sqlSyntax);
        $sqlSyntax = rtrim($sqlSyntax, ',');
    }
    if (DB_CODE_UPDATE == $opt || DB_CODE_DELETE == $opt) {
        if (array_key_exists(TBL_WHERE_SYNTAX, $table)) {
            $where_syntax = $table[TBL_WHERE_SYNTAX];
            $sqlSyntax .= " where " . $where_syntax;
        } else {
            $pk_name = $table[TBL_PRIMARY_KEY_NAME];
            $pk_value = $table[TBL_PRIMARY_KEY_VALUE];
            $sqlSyntax .= " where " . $pk_name . "='" . $conn->real_escape_string($pk_value) . "'";
        }
    }
    $sqlSyntax .= ";";
    if (DB_CODE_OPT_OK !== db_excute($conn, $sqlSyntax, $auto_commit)) {
        return $retErrCode;
    } else {
        return DB_CODE_OPT_OK;
    }
}

// Select records from table.
function get_records($conn, $table, $fields) {
    // Create query sql syntax.
    $querySQL = "select ";
    if (array_key_exists(TBL_SELECT_FIELDS, $table)) {
        $select_fields_info = $table[TBL_SELECT_FIELDS];
        foreach ($fields as $value) {
            $errFlag = true;
            foreach ($select_fields_info as $sel_key => $sel_value) {
                if ($value == $sel_key) {
                    $querySQL .= $sel_value[TBL_FIELD_NAME] . ", ";
                    $errFlag = false;
                    break;
                }
            }
            if ($errFlag) {
                // to-do : Record error log.
                return DB_CODE_SQL_SYNTAX_WRONG;
            }
        }
        $querySQL = rtrim($querySQL);
        $querySQL = rtrim($querySQL, ',');
    } else if (array_key_exists(TBL_SELECT_SYNTAX, $table)) {
        $select_syntax = $table[TBL_SELECT_SYNTAX];
        $querySQL .= $select_syntax;
    } else {
        // to-do : Record error log.
        return DB_CODE_SQL_SYNTAX_WRONG;
    }
    $table_name = $table[TBL_NAME];
    $querySQL .= " from " . $table_name;
    if (array_key_exists(TBL_JOIN_SYNTAX, $table)) {
        $join_syntax = $table[TBL_JOIN_SYNTAX];
        $querySQL .= " " . $join_syntax . " ";
    }
    if (array_key_exists(TBL_WHERE_SYNTAX, $table)) {
        $where_syntax = $table[TBL_WHERE_SYNTAX];
        $querySQL .= " where " . $where_syntax . ";";
    } else {
        $querySQL .= ";";
    }
    // Get records from the table.
    return sql_query($conn, $querySQL);
}

// This function will excute the complicated sql write syntax directly.
function db_excute($conn, $sql_syntax, $auto_commit) {
    if ($auto_commit) { $conn->autocommit(false); }
    $result = $conn->query($sql_syntax);
    if (!$result) {
        if ($auto_commit) { $conn->rollback(); }
        return DB_CODE_SQL_EXCUTE_FAILED;
    } else {
        if ($auto_commit) { $conn->commit(); }
        return DB_CODE_OPT_OK;
    }
}

// This function will excute the complicated sql query syntax directly.
function sql_query($conn, $sql_syntax) {
    $result = $conn->query($sql_syntax);
    if ($result) {
        if ($result->num_rows != 0) {
            return db_result_to_array($result);
        } else {
            return DB_CODE_SELECT_NO_RECORD;
        }
    } else {
        return DB_CODE_SELECT_FAILED;
    }
}
