<?php

/*
 * complete-grp-records-handler.php
 *
 * -Dealing with business about page complete-grp-records.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-14
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
if (!check_head($reqHead, 0, 2)) {
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

// Parse the request params.
if ('0' !== $optID) {
    $req_json_params = isset($_POST['reqData']) ? $_POST['reqData'] : '';
    $req_data = json_decode($req_json_params, true);
    if (!$req_data) {
        $head = ['retCode'=>ERR_CODE_PARSE_FAILED, 'retMsg'=>ERR_MSG_PARSE_FAILED];
        response_json_data($head, null);
        exit;
    }
}

// Start to handle the request.
switch ($optID) {
    // When optID is 0, we need get all the completed group purchase data
    // and return them to the client.
    case 0:
        // Get all the completed group purchase data of this system.
        $fields = [ gpiID, gpiName, gpiImgName, gpiDetail, gpiPrice, gpiEndTime, gpiPublisher, gpiUpdTime ];
        $res_arr = get_gpi_info_by_state($conn, $fields, '2');
        $payload = [ 'gpiData'=>[ 'total'=>0 ] ];
        if (is_array($res_arr)) {
            $payload['gpiData']['rows'] = [];
            for ($idx=0; $idx<count($res_arr); $idx++) {
                $ordCnt = get_orders_cnt_by_gpi_id($conn, $res_arr[$idx]['gpiID']);
                if (is_array($ordCnt)) {
                    $payload['gpiData']['rows'][$idx]['gpiID'] = $res_arr[$idx]['gpiID'];
                    $payload['gpiData']['rows'][$idx]['gpiName'] = $res_arr[$idx]['gpiName'];
                    $payload['gpiData']['rows'][$idx]['gpiImgName'] = $res_arr[$idx]['gpiImgName'];
                    $payload['gpiData']['rows'][$idx]['gpiDetail'] = $res_arr[$idx]['gpiDetail'];
                    $payload['gpiData']['rows'][$idx]['gpiPrice'] = $res_arr[$idx]['gpiPrice'];
                    $payload['gpiData']['rows'][$idx]['gpiEndTime'] = $res_arr[$idx]['gpiEndTime'];
                    $payload['gpiData']['rows'][$idx]['gpiOrdersCnt'] = $ordCnt[0]['gpiOrdersCnt'];
                    $payload['gpiData']['rows'][$idx]['gpiPublisher'] = $res_arr[$idx]['gpiPublisher'];
                    $payload['gpiData']['rows'][$idx]['gpiUpdTime'] = $res_arr[$idx]['gpiUpdTime'];
                    $payload['gpiData']['total']++;
                } else {
                    continue;
                }
            }
            $ret_head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
            response_json_data($ret_head, $payload);
        } else if (DB_CODE_SELECT_NO_RECORD === $res_arr) {
            $ret_head = ['retCode'=>ERR_CODE_CLOSED_GPI_NO_RECORD, 'retMsg'=>ERR_MSG_CLOSED_GPI_NO_RECORD];
            response_json_data($ret_head, $payload);
        } else {
            $ret_head = ['retCode'=>ERR_CODE_CLOSED_GPI_SELECT_FAILED, 'retMsg'=>ERR_MSG_CLOSED_GPI_SELECT_FAILED];
            response_json_data($ret_head, null);
        }
        exit;
    // When optID is 1, we need search all the orders of an GPI.
    case 1:
        // Check the request params.
        if (!check_closed_gpi_req_params($req_data)) {
            $head = ['retCode'=>ERR_CODE_WRONG_REQUEST_BODY, 'retMsg'=>ERR_MSG_WRONG_REQUEST_BODY];
            response_json_data($head, null);
            exit;
        }
        // Check the state of the GPI.
        $fields = [ gpiState ];
        $res_arr = get_gpi_info_by_id($conn, $fields, $req_data['gpiID']);
        if (is_array($res_arr)) {
            if ('2' === $res_arr[0]['gpiState']) {
                // Search all the orders of this GPI and the relative information.
                $ordFields = [ usrID, buyCnt, ordPrice, buyTime ];
                $ord_res_arr = get_orders_info_by_gpi_id($conn, $ordFields, $req_data['gpiID']);
                $payload = [ 'ordData'=>[ 'total'=>0 ] ];
                if (is_array($ord_res_arr)) {
                    // Get the building id and the family id of every order's owner.
                    $payload['ordData']['rows'] = [];
                    for ($idx=0; $idx<count($ord_res_arr); $idx++) {
                        $usrFields = [ usrBid, usrFid ];
                        $usr_res_arr = get_user_info_by_uid($conn, $usrFields, $ord_res_arr[$idx]['usrID']);
                        if (is_array($usr_res_arr)) {
                            // Set the home name for every order.
                            $homeName = '#' . $usr_res_arr[0]['usrBid'] . '-' . $usr_res_arr[0]['usrFid'];
                            $payload['ordData']['rows'][$idx]['ordHomeName'] = $homeName;
                            // Set the others.
                            $payload['ordData']['rows'][$idx]['buyCnt'] = '+' . $ord_res_arr[$idx]['buyCnt'];
                            $payload['ordData']['rows'][$idx]['ordPrice'] = $ord_res_arr[$idx]['ordPrice'] . '元';
                            $payload['ordData']['rows'][$idx]['buyTime'] = $ord_res_arr[$idx]['buyTime'];
                            $payload['ordData']['total']++;
                        } else {
                            continue;
                        }
                    }
                    $head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
                    response_json_data($head, $payload);
                } else if (DB_CODE_SELECT_NO_RECORD === $ord_res_arr) {
                    $head = ['retCode'=>ERR_CODE_ORDER_RECORD_NOT_EXIST, 'retMsg'=>ERR_MSG_ORDER_RECORD_NOT_EXIST];
                    response_json_data($head, $payload);
                } else {
                    $head = ['retCode'=>ERR_CODE_ORDER_SEARCH_FAILED, 'retMsg'=>ERR_MSG_ORDER_SEARCH_FAILED];
                    response_json_data($head, $payload);
                }
            } else {
                $head = ['retCode'=>ERR_CODE_CLOSED_GPI_ID_INVALID, 'retMsg'=>ERR_MSG_CLOSED_GPI_ID_INVALID];
                response_json_data($head, null);
            }
        } else {
            $head = ['retCode'=>ERR_CODE_CLOSED_GPI_ID_INVALID, 'retMsg'=>ERR_MSG_CLOSED_GPI_ID_INVALID];
            response_json_data($head, null);
        }
        exit;
    // When optID is 2, we need stat all the orders for admin users.
    case 2:
        // Check if the user is admin.
        if ("0" === $chk_tk_ret_info['usrType'] || "1" === $chk_tk_ret_info['usrType']) {
            // Check the request params.
            if (!check_closed_gpi_req_params($req_data)) {
                $head = ['retCode'=>ERR_CODE_WRONG_REQUEST_BODY, 'retMsg'=>ERR_MSG_WRONG_REQUEST_BODY];
                response_json_data($head, null);
                exit;
            }
            // Check the state of the GPI.
            $gpiFields = [ gpiState, gpiName, gpiPrice ];
            $gpi_res_arr = get_gpi_info_by_id($conn, $gpiFields, $req_data['gpiID']);
            if (is_array($gpi_res_arr)) {
                if ('2' === $gpi_res_arr[0]['gpiState']) {
                    // Search all the orders of this GPI and the relative information.
                    $ordFields = [ usrID, ordID, buyCnt, ordPrice, buyTime ];
                    $ord_res_arr = get_orders_info_by_gpi_id($conn, $ordFields, $req_data['gpiID']);
                    $payload = [ 'ordData'=>[ 'total'=>0 ] ];
                    if (is_array($ord_res_arr)) {
                        // Get the building id and the family id of every order's owner.
                        $payload['ordData']['rows'] = [];
                        for ($idx=0; $idx<count($ord_res_arr); $idx++) {
                            $usrFields = [ usrBid, usrFid ];
                            $usr_res_arr = get_user_info_by_uid($conn, $usrFields, $ord_res_arr[$idx]['usrID']);
                            if (is_array($usr_res_arr)) {
                                // Set the gpi info.
                                $payload['ordData']['rows'][$idx]['gpiID'] = $req_data['gpiID'];
                                $payload['ordData']['rows'][$idx]['gpiName'] = $gpi_res_arr[0]['gpiName'];
                                $payload['ordData']['rows'][$idx]['gpiPrice'] = $gpi_res_arr[0]['gpiPrice'];
                                // Set the home name for every order.
                                $homeName = '#' . $usr_res_arr[0]['usrBid'] . '-' . $usr_res_arr[0]['usrFid'];
                                $payload['ordData']['rows'][$idx]['ordHomeName'] = $homeName;
                                // Set the others.
                                $payload['ordData']['rows'][$idx]['ordID'] = $ord_res_arr[$idx]['ordID'];
                                $payload['ordData']['rows'][$idx]['buyCnt'] = '+' . $ord_res_arr[$idx]['buyCnt'];
                                $payload['ordData']['rows'][$idx]['ordPrice'] = $ord_res_arr[$idx]['ordPrice'];
                                $payload['ordData']['rows'][$idx]['buyTime'] = $ord_res_arr[$idx]['buyTime'];
                                $payload['ordData']['total']++;
                            } else {
                                continue;
                            }
                        }
                        $head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
                        response_json_data($head, $payload);
                    } else if (DB_CODE_SELECT_NO_RECORD === $ord_res_arr) {
                        $head = ['retCode'=>ERR_CODE_ORDER_RECORD_NOT_EXIST, 'retMsg'=>ERR_MSG_ORDER_RECORD_NOT_EXIST];
                        response_json_data($head, $payload);
                    } else {
                        $head = ['retCode'=>ERR_CODE_ORDER_SEARCH_FAILED, 'retMsg'=>ERR_MSG_ORDER_SEARCH_FAILED];
                        response_json_data($head, $payload);
                    }
                } else {
                    $head = ['retCode'=>ERR_CODE_CLOSED_GPI_ID_INVALID, 'retMsg'=>ERR_MSG_CLOSED_GPI_ID_INVALID];
                    response_json_data($head, null);
                }
            } else {
                $head = ['retCode'=>ERR_CODE_CLOSED_GPI_ID_INVALID, 'retMsg'=>ERR_MSG_CLOSED_GPI_ID_INVALID];
                response_json_data($head, null);
            }
        } else {
            $head = ['retCode'=>ERR_CODE_AUTH_CHECK_FAILED, 'retMsg'=>ERR_MSG_AUTH_CHECK_FAILED];
            response_json_data($head, null);
        }
        exit;
    default:
        exit;
}
