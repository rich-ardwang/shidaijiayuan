<?php

/*
 * sign-in.php
 *
 * -Receive data from the form submitted by sign in and handle it.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-09
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once ("../dbfunc/db-conn.php");
require_once ("../dbfunc/user-login-history-funcs.php");
require_once ("data-verify.php");
require_once ("common.php");

// JS need not parse the response data to JSON.
header('Content-Type:application/json; charset=uft-8');

// Get the data from the sign in form.
$login_form_data = isset($_POST['login_data']) ? $_POST['login_data'] : '';
if (empty($login_form_data)) {
    $head = ['retCode'=>ERR_CODE_EMPTY_DATA, 'retMsg'=>ERR_MSG_EMPTY_DATA];
    response_json_data($head, null);
    exit;
}

// Parse the form data from json string to php array.
$login_data = json_decode($login_form_data, true);
if (!$login_data) {
    $head = ['retCode'=>ERR_CODE_PARSE_FAILED, 'retMsg'=>ERR_MSG_PARSE_FAILED];
    response_json_data($head, null);
    exit;
}

// Check the data from the sign in form and return check result if necessary.
$chk_head = check_login_data($login_data);
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

// Handle the request of token auth.
$optID = intval($login_data['opt']);
if ($optID == 1) {
    // Check token.
    $ret_info = check_token($conn, $login_data[usrBid], $login_data[usrFid], $login_data['token']);
    if ($ret_info['code'] < 0) {
        $head = ['retCode'=>ERR_CODE_CHK_TOKEN_FAILED, 'retMsg'=>ERR_MSG_CHK_TOKEN_FAILED];
        response_json_data($head, null);
        exit;
    }
    
    // Return the success auth result to client.
    $head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
    response_json_data($head, null);
    exit;
}

// Validate the building code and family code and get user id from DB.
$user_id = check_user_exist_by_bid_fid($conn, $login_data[usrBid], $login_data[usrFid]);
if (DB_CODE_USER_NOT_EXIST == $user_id) {
    $head = ['retCode'=>ERR_CODE_BID_FID_PSW_FAILED, 'retMsg'=>ERR_MSG_BID_FID_PSW_FAILED];
    response_json_data($head, null);
    exit;
}

// Get user info from DB.
$db_psw = "";
$fids = [ usrPwd, usrType ];
$ret = get_user_info_by_uid($conn, $fids, $user_id);
if (is_array($ret)) {
    $db_psw = $ret[0]['usrPwd'];
} else {
    $head = ['retCode'=>ERR_CODE_BID_FID_PSW_FAILED, 'retMsg'=>ERR_MSG_BID_FID_PSW_FAILED];
    response_json_data($head, null);
    exit;
}

// Validate the received password.
$md5Psw = md5($login_data[usrPwd]);
if ($db_psw != $md5Psw) {
    $head = ['retCode'=>ERR_CODE_BID_FID_PSW_FAILED, 'retMsg'=>ERR_MSG_BID_FID_PSW_FAILED];
    response_json_data($head, null);
    exit;
}

// All validations are passed and we create token.
$token = create_token($user_id, $db_psw);

// Return the login result to JS client.
$ret_head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
$payload = [ 'token'=>$token, 'type'=>$ret[0]['usrType'] ];
response_json_data($ret_head, $payload);

// Record the history of user login.
$ulh_info = [ usrID=>$user_id, ulhLoginTime=>date('Y-m-d H:i:s') ];
insert_login_history($conn, $ulh_info);
