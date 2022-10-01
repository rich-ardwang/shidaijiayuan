<?php

/*
 * add-group-purchase-handler.php
 *
 * -Dealing with business about uploading image and the data of group purchase info.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-12
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once ("../dbfunc/db-conn.php");
require_once ("../dbfunc/grp-purchase-info-funcs.php");
require_once ("data-verify.php");
require_once ("common.php");

// JS need not parse the response data to JSON.
header('Content-Type:application/json; charset=uft-8');

// Get the request head from front page add-group-purchase.
$optID = isset($_POST['optID']) ? $_POST['optID'] : '';
$usr_bid = isset($_POST['bid']) ? $_POST['bid'] : '';
$usr_fid = isset($_POST['fid']) ? $_POST['fid'] : '';
$token = isset($_POST['tok']) ? $_POST['tok'] : '';
$reqHead = ['usrBid'=>$usr_bid, 'usrFid'=>$usr_fid, 'token'=>$token, 'optID'=>$optID];

// Check request head.
if (!check_head($reqHead, 1, 1)) {
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

// Check the user type, it must be admin.
if ("0" !== $chk_tk_ret_info['usrType'] && "1" !== $chk_tk_ret_info['usrType']) {
    $head = ['retCode'=>ERR_CODE_CHK_ADMIN_FAILED, 'retMsg'=>ERR_MSG_CHK_ADMIN_FAILED];
    response_json_data($head, null);
    exit;
}

// First of all we handle the uploaded image file.
$uniname = '';
if (isset( $_FILES['gpiPic'])) {
    // Get file info and its extension.
    $fInfo = $_FILES['gpiPic'];
    $ext = strtolower(end(explode('.', $fInfo['name'])));

    // Check the uploaded image file.
    $head = check_gpi_img_file_info($fInfo, $ext);
    if (SUCCESS_CODE !== $head['retCode']) {
        response_json_data($head, null);
        exit;
    }
    unset($head['retCode']);

    // We store the images of gpi here.
    $path = '../upld/gpiPics';
    // If the path is not exist, we create one and chmod it.
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
        chmod($path, 0777);
    }

    // We create an unique file name with md5 algorithms for this image,
    // and move it from tmp path to our gpi img file path,
    // and finally insert all the information to the gpi table in DB.
    $uniname = md5(uniqid(microtime(true), true)) . '.' . $ext;
    $dest = $path . '/' . $uniname;

    // Store the effective image file of GPI.
    if (!move_uploaded_file($fInfo['tmp_name'], $dest)) {
        $head = ['retCode'=>ERR_CODE_MOVE_IMAGE_FILE_FAILED, 'retMsg'=>ERR_MSG_MOVE_IMAGE_FILE_FAILED];
        exit;
    }
}

// Finally we handle the data information for GPI.

// Get the request data.
$req_json_params = isset($_POST['reqData']) ? $_POST['reqData'] : '';
$req_data = json_decode($req_json_params, true);
if (!$req_data) {
    $head = ['retCode'=>ERR_CODE_PARSE_FAILED, 'retMsg'=>ERR_MSG_PARSE_FAILED];
    response_json_data($head, null);
    exit;
}

// Check the request params.
if (!check_gpi_req_params($req_data, $optID)) {
    $head = ['retCode'=>ERR_CODE_WRONG_REQUEST_BODY, 'retMsg'=>ERR_MSG_WRONG_REQUEST_BODY];
    response_json_data($head, null);
    exit;
}

// Set the publisher and its ID for this gpi.
$req_data['gpiPublisher'] = $usr_bid . '号楼' . $usr_fid;
$req_data['usrID'] = $chk_tk_ret_info['usrID'];

// Set the image name of gpi if it is exist.
if (!empty($uniname)) {
    $req_data['gpiImgName'] = $uniname;
}

// Add gpi data to DB.
$gpi_info = create_gpi_info($req_data, $optID);
$conn->autocommit(false);
$ret = insert_gpi_info($conn, $gpi_info, false);
if (DB_CODE_OPT_OK !== $ret) {
    $conn->rollback();
    $head = ['retCode'=>ERR_CODE_GPI_INSERT_FAILED, 'retMsg'=>ERR_MSG_GPI_INSERT_FAILED];               
    response_json_data($head, null);
    exit;
}

// Response the successful result to client.
$conn->commit();
$head = ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
response_json_data($head, null);
exit;

// Create GPI info for adding.
function create_gpi_info($reqParams) {
    $now_time = date('Y-m-d H:i:s');
    // Required items.
    $gpi_info = [ gpiName=>$reqParams['gpiName'], gpiPrice=>$reqParams['gpiPrice'], 
        gpiEndTime=>$reqParams['gpiEndTime'], usrID=>$reqParams['usrID'],
        gpiPublisher=>$reqParams['gpiPublisher'], gpiCrtTime=>$now_time, gpiUpdTime=>$now_time ];
    // Optional items.
    if (array_key_exists('gpiImgName', $reqParams)) {
        $gpi_info[gpiImgName] = $reqParams['gpiImgName'];
    }
    if (array_key_exists('gpiDetail', $reqParams)) {
        $gpi_info[gpiDetail] = $reqParams['gpiDetail'];
    }
    return $gpi_info;
}
