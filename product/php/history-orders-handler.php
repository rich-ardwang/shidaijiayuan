<?php

/*
 * history-orders-handler.php
 *
 * -Dealing with business about page history-orders.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-15
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once ("../dbfunc/db-conn.php");
require_once ("../dbfunc/grp-purchase-info-funcs.php");
require_once ("../dbfunc/orders-funcs.php");
require_once ("data-verify.php");
require_once ("common.php");

// JS need not parse the response data to JSON.
header('Content-Type:application/json; charset=uft-8');

// Get the request head from front page doing-grp-records.
$optID = isset($_POST['optID']) ? $_POST['optID'] : '';
$usr_bid = isset($_POST['bid']) ? $_POST['bid'] : '';
$usr_fid = isset($_POST['fid']) ? $_POST['fid'] : '';
$token = isset($_POST['tok']) ? $_POST['tok'] : '';
$reqHead = ['usrBid'=>$usr_bid, 'usrFid'=>$usr_fid, 'token'=>$token, 'optID'=>$optID];

// Check request head.
if (!check_head($reqHead, 0, 0)) {
    $head = ['retCode'=>ERR_CODE_WRONG_REQUEST_HEAD, 'retMsg'=>ERR_MSG_WRONG_REQUEST_HEAD];
    response_json_data($head, null);
    exit;
}

// Connect to database.
$conn = db_connect();
if (!$conn) {
    $head = ['retCode'=>ERR_CODE_DB_CONN_FAILED, 'retMsg'=>ERR_MSG_DB_CONN_FAILED];
    response_json_data($head, null);
    exit;
}

// Check token, if success get the user ID and the user type.
$chk_tk_ret_info = check_token($conn, $usr_bid, $usr_fid, $token);
if ($chk_tk_ret_info['code'] < 0) {
    $head = ['retCode'=>ERR_CODE_CHK_TOKEN_FAILED, 'retMsg'=>ERR_MSG_CHK_TOKEN_FAILED];
    response_json_data($head, null);
    exit;
}

// Start to handle the request.
switch ($optID) {
    // When optID is 0, we need get all the history orders data of a user
    // and return them to the client.
    case 0:
        // Get all the history orders data of this user.
        $ordFields = [ ordID, gpiID, buyCnt, ordPrice, buyTime, ordState ];
        $ord_res_arr = get_orders_info_by_uid($conn, $ordFields, $chk_tk_ret_info['usrID']);
        $payload = [ 'hisOrdData'=>[ 'total'=>0 ] ];
        if (is_array($ord_res_arr)) {
            $payload['hisOrdData']['rows'] = [];
            for ($idx=0; $idx<count($ord_res_arr); $idx++) {
                $gpiFields = [ gpiName, gpiImgName, gpiDetail, gpiPublisher, gpiState, gpiPrice ];
                $gpi_res_arr = get_gpi_info_by_id($conn, $gpiFields, $ord_res_arr[$idx]['gpiID']);
                if (is_array($gpi_res_arr)) {
                    $payload['hisOrdData']['rows'][$idx]['ordID'] = $ord_res_arr[$idx]['ordID'];
                    $payload['hisOrdData']['rows'][$idx]['gpiID'] = $ord_res_arr[$idx]['gpiID'];
                    $payload['hisOrdData']['rows'][$idx]['gpiName'] = $gpi_res_arr[0]['gpiName'];
                    $payload['hisOrdData']['rows'][$idx]['gpiImgName'] = $gpi_res_arr[0]['gpiImgName'];
                    $payload['hisOrdData']['rows'][$idx]['gpiDetail'] = $gpi_res_arr[0]['gpiDetail'];
                    $payload['hisOrdData']['rows'][$idx]['gpiPublisher'] = $gpi_res_arr[0]['gpiPublisher'];
                    $gpiState = '已封团';
                    if ('0' === $gpi_res_arr[0]['gpiState']) {
                        $gpiState = '已撤消';
                    } else if ('1' === $gpi_res_arr[0]['gpiState']) {
                        $gpiState = '进行中';
                    }
                    $payload['hisOrdData']['rows'][$idx]['gpiState'] = $gpiState;
                    $payload['hisOrdData']['rows'][$idx]['gpiPrice'] = $gpi_res_arr[0]['gpiPrice'];
                    $payload['hisOrdData']['rows'][$idx]['buyCnt'] = $ord_res_arr[$idx]['buyCnt'];
                    $payload['hisOrdData']['rows'][$idx]['ordPrice'] = $ord_res_arr[$idx]['ordPrice'];
                    $payload['hisOrdData']['rows'][$idx]['buyTime'] = $ord_res_arr[$idx]['buyTime'];
                    if ('v' === $ord_res_arr[$idx]['ordState']) {
                        $ordState = '已完成';
                    } else {
                        $ordState = '已撤单';
                    }
                    $payload['hisOrdData']['rows'][$idx]['ordState'] = $ordState;
                    $payload['hisOrdData']['total']++;
                } else {
                    continue;
                }
            }
            $ret_head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
            response_json_data($ret_head, $payload);
        } else if (DB_CODE_SELECT_NO_RECORD === $ord_res_arr) {
            $ret_head = ['retCode'=>ERR_CODE_HIS_ORDER_NO_RECORD, 'retMsg'=>ERR_MSG_HIS_ORDER_NO_RECORD];
            response_json_data($ret_head, $payload);
        } else {
            $ret_head = ['retCode'=>ERR_CODE_HIS_ORDER_SELECT_FAILED, 'retMsg'=>ERR_MSG_HIS_ORDER_SELECT_FAILED];
            response_json_data($ret_head, null);
        }
        exit;
    default:
        exit;
}
