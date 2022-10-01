<?php

/*
 * cancel-grp-records-handler.php
 *
 * -Dealing with business about page cancel-grp-records.
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
    // When optID is 0, we need get all the canceled group purchase data
    // and return them to the client.
    case 0:
        // Get all the canceled group purchase data of this system.
        $fields = [ gpiID, gpiName, gpiImgName, gpiDetail, gpiPrice, gpiEndTime, gpiPublisher, gpiUpdTime ];
        $res_arr = get_gpi_info_by_state($conn, $fields, '0');
        $payload = [ 'gpiData'=>[ 'total'=>0 ] ];
        if (is_array($res_arr)) {
            $payload['gpiData']['rows'] = [];
            for ($idx=0; $idx<count($res_arr); $idx++) {
                $payload['gpiData']['rows'][$idx]['gpiID'] = $res_arr[$idx]['gpiID'];
                $payload['gpiData']['rows'][$idx]['gpiName'] = $res_arr[$idx]['gpiName'];
                $payload['gpiData']['rows'][$idx]['gpiImgName'] = $res_arr[$idx]['gpiImgName'];
                $payload['gpiData']['rows'][$idx]['gpiDetail'] = $res_arr[$idx]['gpiDetail'];
                $payload['gpiData']['rows'][$idx]['gpiPrice'] = $res_arr[$idx]['gpiPrice'];
                $payload['gpiData']['rows'][$idx]['gpiEndTime'] = $res_arr[$idx]['gpiEndTime'];
                $payload['gpiData']['rows'][$idx]['gpiPublisher'] = $res_arr[$idx]['gpiPublisher'];
                $payload['gpiData']['rows'][$idx]['gpiUpdTime'] = $res_arr[$idx]['gpiUpdTime'];
                $payload['gpiData']['total']++;
            }
            $ret_head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
            response_json_data($ret_head, $payload);
        } else if (DB_CODE_SELECT_NO_RECORD === $res_arr) {
            $ret_head = ['retCode'=>ERR_CODE_CANCELED_GPI_NO_RECORD, 'retMsg'=>ERR_MSG_CANCELED_GPI_NO_RECORD];
            response_json_data($ret_head, $payload);
        } else {
            $ret_head = ['retCode'=>ERR_CODE_CANCELED_GPI_SELECT_FAILED, 'retMsg'=>ERR_MSG_CANCELED_GPI_SELECT_FAILED];
            response_json_data($ret_head, null);
        }
        exit;
    default:
        exit;
}
