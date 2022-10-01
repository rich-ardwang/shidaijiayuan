<?php

/*
 * doing-grp-records-handler.php
 *
 * -Dealing with business about page doing-grp-records.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-13
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
if (!check_head($reqHead, 0, 5)) {
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
    // When optID is 0, we need get all the doing group purchase data
    // and return them to the client.
    case 0:
        // Get all the doing group purchase data of this system.
        $fields = [ gpiID, gpiName, gpiImgName, gpiDetail, gpiPrice, gpiEndTime, gpiPublisher ];
        $res_arr = get_gpi_info_by_state($conn, $fields, '1');
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
                    $payload['gpiData']['total']++;
                } else {
                    continue;
                }
            }
            $ret_head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
            response_json_data($ret_head, $payload);
        } else if (DB_CODE_SELECT_NO_RECORD === $res_arr) {
            $ret_head = ['retCode'=>ERR_CODE_DOING_GPI_NO_RECORD, 'retMsg'=>ERR_MSG_DOING_GPI_NO_RECORD];
            response_json_data($ret_head, $payload);
        } else {
            $ret_head = ['retCode'=>ERR_CODE_DOING_GPI_SELECT_FAILED, 'retMsg'=>ERR_MSG_DOING_GPI_SELECT_FAILED];
            response_json_data($ret_head, null);
        }
        exit;
    // When optID is 1, we need search all the orders of an GPI.
    case 1:
        // Check the request params.
        if (!check_doing_gpi_req_params($req_data, $optID)) {
            $head = ['retCode'=>ERR_CODE_WRONG_REQUEST_BODY, 'retMsg'=>ERR_MSG_WRONG_REQUEST_BODY];
            response_json_data($head, null);
            exit;
        }
        // Check the state of the GPI.
        $fields = [ gpiState ];
        $res_arr = get_gpi_info_by_id($conn, $fields, $req_data['gpiID']);
        if (is_array($res_arr)) {
            if ('1' === $res_arr[0]['gpiState']) {
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
                $head = ['retCode'=>ERR_CODE_DOING_GPI_STATE_CHANGED, 'retMsg'=>ERR_MSG_DOING_GPI_STATE_CHANGED];
                response_json_data($head, null);
            }
        } else {
            $head = ['retCode'=>ERR_CODE_DOING_GPI_ID_INVALID, 'retMsg'=>ERR_MSG_DOING_GPI_ID_INVALID];
            response_json_data($head, null);
        }
        exit;
    // When optID is 2, we need add a new order to DB or update it.
    case 2:
        // Check the request params.
        if (!check_doing_gpi_req_params($req_data, $optID)) {
            $head = ['retCode'=>ERR_CODE_WRONG_REQUEST_BODY, 'retMsg'=>ERR_MSG_WRONG_REQUEST_BODY];
            response_json_data($head, null);
            exit;
        }
        // Check if the gpi is valid.
        $fields = [ gpiPrice, gpiState ];
        $res_arr = get_gpi_info_by_id($conn, $fields, $req_data['gpiID']);
        if (is_array($res_arr)) {
            if ('1' === $res_arr[0]['gpiState']) {
                // Calc the total price.
                $singlePrice = doubleval($res_arr[0]['gpiPrice']);
                $totalPrice = bcmul($singlePrice, doubleval($req_data['buyCnt']), 2);
                // If the order is exist, we add a new order to DB.
                // Otherwise we just update buyCnt, ordPrice, buyTime, buyUpdCnt and ordState.
                $ordFields = [ ordID, buyUpdCnt ];
                $ord_res_arr = get_order_info_by_usr_gpi_id($conn, $ordFields, $chk_tk_ret_info['usrID'], $req_data['gpiID']);
                if (is_array($ord_res_arr)) {
                    // Update the order.
                    $tempBuyCnt = intval($ord_res_arr[0]['buyUpdCnt']);
                    $tempBuyCnt++;
                    $upd_ord_info = [ buyCnt=>$req_data['buyCnt'], ordPrice=>$totalPrice,
                        buyTime=>date('Y-m-d H:i:s'), buyUpdCnt=>$tempBuyCnt, ordState=>'v' ];
                    $upd_ord_ret = update_order_info_by_id($conn, $upd_ord_info, $ord_res_arr[0]['ordID']);
                    if (DB_CODE_OPT_OK === $upd_ord_ret) {
                        $head = ['retCode'=>CODE_ORDER_UPD_SUCCESS, 'retMsg'=>MSG_ORDER_UPD_SUCCESS];
                        response_json_data($head, null);
                    } else {
                        $head = ['retCode'=>ERR_CODE_ORDER_UPD_FAILED, 'retMsg'=>ERR_MSG_ORDER_UPD_FAILED];
                        response_json_data($head, null);
                    }
                } else if (DB_CODE_SELECT_NO_RECORD === $ord_res_arr) {
                    // Add a new order.
                    $now_time = date('Y-m-d H:i:s');
                    $add_ord_info = [ usrID=>$chk_tk_ret_info['usrID'], gpiID=>$req_data['gpiID'],
                        buyCnt=>$req_data['buyCnt'], ordPrice=>$totalPrice, buyTime=>$now_time,
                        ordCrtTime=>$now_time ];
                    $add_ord_ret = insert_order($conn, $add_ord_info);
                    if (DB_CODE_OPT_OK === $add_ord_ret) {
                        $head = ['retCode'=>CODE_ORDER_ADD_SUCCESS, 'retMsg'=>MSG_ORDER_ADD_SUCCESS];
                        response_json_data($head, null);
                    } else {
                        $head = ['retCode'=>ERR_CODE_ORDER_ADD_FAILED, 'retMsg'=>ERR_MSG_ORDER_ADD_FAILED];
                        response_json_data($head, null);
                    }
                } else {
                    // Add or update order failed.
                    $head = ['retCode'=>ERR_CODE_ORDER_ADD_FAILED, 'retMsg'=>ERR_MSG_ORDER_ADD_FAILED];
                    response_json_data($head, null);
                }
            } else {
                $head = ['retCode'=>ERR_CODE_DOING_GPI_STATE_CHANGED, 'retMsg'=>ERR_MSG_DOING_GPI_STATE_CHANGED];
                response_json_data($head, null);
            }
        } else {
            $head = ['retCode'=>ERR_CODE_DOING_GPI_ID_INVALID, 'retMsg'=>ERR_MSG_DOING_GPI_ID_INVALID];
            response_json_data($head, null);
        }
        exit;
    // When optID is 3, we need retrieve an order for a user.
    case 3:
        // Check the request params.
        if (!check_doing_gpi_req_params($req_data, $optID)) {
            $head = ['retCode'=>ERR_CODE_WRONG_REQUEST_BODY, 'retMsg'=>ERR_MSG_WRONG_REQUEST_BODY];
            response_json_data($head, null);
            exit;
        }
        // Check if the gpi is valid.
        $gpiFields = [ gpiState ];
        $gpi_res_arr = get_gpi_info_by_id($conn, $gpiFields, $req_data['gpiID']);
        if (is_array($gpi_res_arr)) {
            if ('1' === $gpi_res_arr[0]['gpiState']) {
                // Search the order.
                $ordFields = [ ordID, ordState, buyUpdCnt ];
                $ord_res_arr = get_order_info_by_usr_gpi_id($conn, $ordFields, $chk_tk_ret_info['usrID'], $req_data['gpiID']);
                if (is_array($ord_res_arr)) {
                    // Retrieve this order.
                    if ('v' === $ord_res_arr[0]['ordState']) {
                        // Update the ordState and the relative info.
                        $tempBuyCnt = intval($ord_res_arr[0]['buyUpdCnt']);
                        $tempBuyCnt++;
                        $upd_ord_info = [ buyTime=>date('Y-m-d H:i:s'), buyUpdCnt=>$tempBuyCnt, ordState=>'d' ];
                        $upd_ord_ret = update_order_info_by_id($conn, $upd_ord_info, $ord_res_arr[0]['ordID']);
                        if (DB_CODE_OPT_OK !== $upd_ord_ret) {
                            $head = ['retCode'=>ERR_CODE_ORDER_RETRIEVE_FAILED, 'retMsg'=>ERR_MSG_ORDER_RETRIEVE_FAILED];
                            response_json_data($head, null);
                            exit;
                        }
                    }
                    // The order has already been retrieved.
                    $head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
                    response_json_data($head, null);
                } else {
                    // The order id is invalid.
                    $head = ['retCode'=>ERR_CODE_ORDER_ID_INVALID, 'retMsg'=>ERR_MSG_ORDER_ID_INVALID];
                    response_json_data($head, null);
                }
            } else {
                $head = ['retCode'=>ERR_CODE_DOING_GPI_STATE_CHANGED, 'retMsg'=>ERR_MSG_DOING_GPI_STATE_CHANGED];
                response_json_data($head, null);
            }
        } else {
            $head = ['retCode'=>ERR_CODE_DOING_GPI_ID_INVALID, 'retMsg'=>ERR_MSG_DOING_GPI_ID_INVALID];
            response_json_data($head, null);
        }
        exit;
    // When optID is 4, we need close a group purchase before its ending time.
    // Only admin can do this.
    case 4:
        // Check if the user is admin.
        if ("0" === $chk_tk_ret_info['usrType'] || "1" === $chk_tk_ret_info['usrType']) {
            // Get gpi info.
            $gpiFields = [ gpiState, usrID ];
            $gpi_res_arr = get_gpi_info_by_id($conn, $gpiFields, $req_data['gpiID']);
            if (is_array($gpi_res_arr)) {
                // Check the gpi state.
                if ('1' === $gpi_res_arr[0]['gpiState']) {
                    // If the user is common admin, we check if this order's publisher is he.
                    // He can only close his own gpi.
                    if ("1" === $chk_tk_ret_info['usrType']) {
                        if ($chk_tk_ret_info['usrID'] !== $gpi_res_arr[0]['usrID']) {
                            $head = ['retCode'=>ERR_CODE_AUTH_CHECK_FAILED, 'retMsg'=>ERR_MSG_AUTH_CHECK_FAILED];
                            response_json_data($head, null);
                            exit;
                        }
                    }
                    // Close the gpi.
                    $end_gpi_res = end_gpi_info_by_id($conn, $req_data['gpiID']);
                    if (DB_CODE_OPT_OK !== $end_gpi_res) {
                        $head = ['retCode'=>ERR_CODE_CLOSE_GPI_FAILED, 'retMsg'=>ERR_MSG_CLOSE_GPI_FAILED];
                        response_json_data($head, null);
                    } else {
                        $head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
                        response_json_data($head, null);
                    }
                } else {
                    $head = ['retCode'=>ERR_CODE_DOING_GPI_STATE_CHANGED, 'retMsg'=>ERR_MSG_DOING_GPI_STATE_CHANGED];
                    response_json_data($head, null);
                }
            } else {
                $head = ['retCode'=>ERR_CODE_DOING_GPI_ID_INVALID, 'retMsg'=>ERR_MSG_DOING_GPI_ID_INVALID];
                response_json_data($head, null);
            }
        } else {
            $head = ['retCode'=>ERR_CODE_AUTH_CHECK_FAILED, 'retMsg'=>ERR_MSG_AUTH_CHECK_FAILED];
            response_json_data($head, null);
        }
        exit;
    // When optID is 5, we need cancel a group purchase.
    // When a group purchase was canecled, it couldn't be doing again.
    // Only admin can do this.
    case 5:
        // Check if the user is admin.
        if ("0" === $chk_tk_ret_info['usrType'] || "1" === $chk_tk_ret_info['usrType']) {
            // Get gpi info.
            $gpiFields = [ gpiState, usrID ];
            $gpi_res_arr = get_gpi_info_by_id($conn, $gpiFields, $req_data['gpiID']);
            if (is_array($gpi_res_arr)) {
                // Check the gpi state.
                if ('1' === $gpi_res_arr[0]['gpiState']) {
                    // If the user is common admin, we check if this order's publisher is he.
                    // He can only cancel his own gpi.
                    if ("1" === $chk_tk_ret_info['usrType']) {
                        if ($chk_tk_ret_info['usrID'] !== $gpi_res_arr[0]['usrID']) {
                            $head = ['retCode'=>ERR_CODE_AUTH_CHECK_FAILED, 'retMsg'=>ERR_MSG_AUTH_CHECK_FAILED];
                            response_json_data($head, null);
                            exit;
                        }
                    }
                    // Cancel the gpi.
                    $end_gpi_res = cancel_gpi_info_by_id($conn, $req_data['gpiID']);
                    if (DB_CODE_OPT_OK !== $end_gpi_res) {
                        $head = ['retCode'=>ERR_CODE_CANCEL_GPI_FAILED, 'retMsg'=>ERR_MSG_CANCEL_GPI_FAILED];
                        response_json_data($head, null);
                    } else {
                        $head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
                        response_json_data($head, null);
                    }
                } else {
                    $head = ['retCode'=>ERR_CODE_DOING_GPI_STATE_CHANGED, 'retMsg'=>ERR_MSG_DOING_GPI_STATE_CHANGED];
                    response_json_data($head, null);
                }
            } else {
                $head = ['retCode'=>ERR_CODE_DOING_GPI_ID_INVALID, 'retMsg'=>ERR_MSG_DOING_GPI_ID_INVALID];
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
