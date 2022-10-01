<?php

/*
 * common.php
 *
 * -We implement common functions here.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-09
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once ("../../conf/common-config.php");
require_once ("../dbfunc/user-funcs.php");
require_once ("../dbfunc/db-common.php");

/*---------------------------Token Algorithm-------------------------------------*/

function generate_token($text) {
    if (empty($text)) {
        return "";
    }
    global $private_key;
    $today = date('Y-m-d');
    $plain = $today . $text . $private_key;
    $cipher = hash('sha256', $plain);
    return $cipher;
}

function create_token($uid, $user_psw) {
    return generate_token($uid.$user_psw);
}

function check_token($conn, $user_bid, $user_fid, $token) {
    $retInfo = ['code'=>-1, 'usrID'=>'', 'usrType'=>''];
    // Get user id, password and user type by bid and fid.
    $fields = [ usrID, usrPwd, usrType ];
    $ret = get_user_info_by_bid_fid($conn, $fields, $user_bid, $user_fid);
    if (!is_array($ret)) {
        return $retInfo;
    }
    // Check token using the user ID and the password.
    if ($token === create_token($ret[0]['usrID'], $ret[0]['usrPwd'])) {
        $retInfo['code'] = 0;
        $retInfo['usrID'] = $ret[0]['usrID'];
        $retInfo['usrType'] = $ret[0]['usrType'];
    }
    return $retInfo;
}

function check_token_direct($usr_id, $usr_psw, $token) {
    // Check token using the user ID and the password.
    if ($token === create_token($usr_id, $usr_psw)) {
        return true;
    }
    return false;
}

/*---------------------------Date & Time-------------------------------------*/

// Get year from the string date.
// --strDate: format must be 'YYYY-MM-DD hh:mm:ss'.
function getYearFromString($strDate) {
    return date("Y", strtotime($strDate));
}

// Get month from the string date.
// --strDate: format must be 'YYYY-MM-DD hh:mm:ss'.
function getMonthFromString($strDate) {
    return date("m", strtotime($strDate));
}

// Get year and month from the string date.
// --strDate: format must be 'YYYY-MM-DD hh:mm:ss'.
function getYMFromString($strDate) {
    return date("Y-m", strtotime($strDate));
}
