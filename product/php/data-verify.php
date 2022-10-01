<?php

/*
 * data-verify.php
 *
 * -Check the form data from all sorts of pages.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-10
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

/*---------------------------Common definition-------------------------------------*/

// Define the common result code.
define("SUCCESS_CODE", 0);
define("ERR_CODE_EMPTY_DATA", -1);
define("ERR_CODE_PARSE_FAILED", -2);
define("ERR_CODE_WRONG_REQUEST_HEAD", -3);
define("ERR_CODE_DB_CONN_FAILED", -4);
define("ERR_CODE_DB_SQL_SYNTAX_WRONG", -5);
define("ERR_CODE_CHK_TOKEN_FAILED", -6);
define("ERR_CODE_WRONG_REQUEST_BODY", -7);
define("ERR_CODE_CHK_ADMIN_FAILED", -8);

// Define the common result message.
define("SUCCESS_MSG", "Handle success.");
define("ERR_MSG_EMPTY_DATA", "Form data is empty!");
define("ERR_MSG_PARSE_FAILED", "Request parse failed!");
define("ERR_MSG_WRONG_REQUEST_HEAD", "Illegal request data head!");
define("ERR_MSG_DB_CONN_FAILED", "DB connect failed!");
define("ERR_MSG_DB_SQL_SYNTAX_WRONG", "SQL syntax wrong!");
define("ERR_MSG_CHK_TOKEN_FAILED", "Token authentification failed!");
define("ERR_MSG_WRONG_REQUEST_BODY", "Illegal request data body!");
define("ERR_MSG_CHK_ADMIN_FAILED", "You are not admin!");

// Define the regular express syntax used for checking request data.
define("REGEX_STR_USR_BID_PATTERN", "/^(?:[1-9]|[1][0-9]|20)$/");
define("REGEX_STR_USR_FID_PATTERN", "/^(?:0[1-9]0[1-8]|[1-2][0-9]0[1-8]|300[1-8])$/");
define("REGEX_STR_PHONE_NUM_PATTERN", "/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/");
define("REGEX_STR_PASSWORD_PATTERN", "/^[A-Fa-f\d]{64}$/");
define("REGEX_STR_GPI_NAME_PATTERN", "/^[\x{4E00}-\x{9FA5}A-Za-z\d_-]{1,20}$/u");
define("REGEX_STR_GPI_PEICE_PATTERN", "/^[\\d]{1,5}[\\.][\\d]{2}$/");
define("REGEX_STR_DETAIL_PATTERN", "/^[\x{4E00}-\x{9FA5}A-Za-z\d\!\?\;\:\~\^\(\@\)\#\$\+\=\%\/\&\*\,\ \.\，\。\……\￥\！\、\？\；\：\_\-]{0,255}$/u");
define("REGEX_STR_DATE_TIME_PATTERN", "/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)\\s+([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/");
define("REGEX_STR_BUY_CNT_PATTERN", "/^(?:[1-9]|[1-9][0-9]|[1-9][0-9][0-9]|1000)$/");

// Respond the result to client as json data.
function response_json_data($head, $payload) {
    $responseData = ['head'=>$head];
    if (is_array($payload)) {
        $responseData['payload'] = $payload;
    }
    echo json_encode($responseData);
}

// Check the array keys of the request params.
// If there are some extra keys in $reqParamsArray which are not contained in $targetKeysArray,
// that is an illegal request.
// --$reqParamsArray: the array of the request params.
// --$targetKeysArray: the array of the target keys.
function check_req_array_keys($reqParamsArray, $targetKeysArray) {
    if (0 === count($reqParamsArray)) {
        return false;
    }
    foreach ($reqParamsArray as $key=>$val) {
        if (!array_key_exists($key, $targetKeysArray)) {
            return false;
        }
    }
    return true;
}

/*---------------------------Check the data from register form-------------------------------------*/

// Define the result code for register.
define("ERR_CODE_USR_BID_FAILED", -50);
define("ERR_CODE_USR_FID_FAILED", -51);
define("ERR_CODE_PASSWORD_FAILED", -52);
define("ERR_CODE_DB_USER_ALREADY_EXIST", -53);
define("ERR_CODE_DB_INSERT_USER_FAILED", -54);

// Define the return message for register.
define("ERR_MSG_USR_BID_FAILED", "Building code is not correct!");
define("ERR_MSG_USR_FID_FAILED", "family code is not correct!");
define("ERR_MSG_PASSWORD_FAILED", "Password is not correct!");
define("ERR_MSG_DB_USER_ALREADY_EXIST", "User is already exist!");
define("ERR_MSG_DB_INSERT_USER_FAILED", "Register insert failed!");

// Define received count of register field.
define("REG_FIELD_CNT", 3);

// Check the array for register data.
function check_register_array($reg_data) {
    // Check the count of the array.
    $cnt = count($reg_data);
    if ($cnt !== REG_FIELD_CNT) {
        return false;
    }
    return true;
}

// Check register data which are from register page.
function check_register_data($reg_data) {
    if (!check_register_array($reg_data)) {
        return ['retCode'=>ERR_CODE_WRONG_REQUEST_HEAD, 'retMsg'=>ERR_MSG_WRONG_REQUEST_HEAD];
    }
    if (!preg_match(REGEX_STR_USR_BID_PATTERN, $reg_data[usrBid])) {
        return ['retCode'=>ERR_CODE_USR_BID_FAILED, 'retMsg'=>ERR_MSG_USR_BID_FAILED];
    }
    if (!preg_match(REGEX_STR_USR_FID_PATTERN, $reg_data[usrFid])) {
        return ['retCode'=>ERR_CODE_USR_FID_FAILED, 'retMsg'=>ERR_MSG_USR_FID_FAILED];
    }
    if (!preg_match(REGEX_STR_PASSWORD_PATTERN, $reg_data[usrPwd])) {
        return ['retCode'=>ERR_CODE_PASSWORD_FAILED, 'retMsg'=>ERR_MSG_PASSWORD_FAILED];
    }
    return ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
}

// Set insert data of a new user.
function create_reg_info($reg_data) {
    // Encrypt password as sha256 to md5.
    $md5Psw = md5($reg_data[usrPwd]);
    
    // Create now time, not use the time of DB server.
    $now_time = date('Y-m-d H:i:s');
    
    // Create register data info and return it.
    $info = [ usrBid=>$reg_data[usrBid], usrPwd=>$md5Psw,
        usrFid=>$reg_data[usrFid], usrType=>'2',
        usrCrtTime=>$now_time, usrUpdTime=>$now_time ];
    
    return $info;
}

/*---------------------------Check the data from sign in form-------------------------------------*/

// Define the result code and message for sign in.
define("ERR_CODE_BID_FID_PSW_FAILED", -70);
define("ERR_MSG_BID_FID_PSW_FAILED", "Bid or fid or password is not correct!");

// Check the array for login data.
function check_login_array($login_data) {
    // Check the count of the array.
    if (count($login_data) !== 4) {
        return false;
    }
    // Check login array keys.
    if (array_key_exists('opt', $login_data)) {
        $opt = intval($login_data['opt']);
    } else {
        return false;
    }
    $ret = array_key_exists(usrBid, $login_data);
    if ($opt == 0) {
        $ret &= array_key_exists(usrPwd, $login_data);
    } else if ($opt == 1) {
        $ret &= array_key_exists('token', $login_data);
    } else {
        return false;
    }
    return $ret;
}

// Check login data which are from login page.
function check_login_data($login_data) {
    if (!check_login_array($login_data)) {
        return ['retCode'=>ERR_CODE_WRONG_REQUEST_HEAD, 'retMsg'=>ERR_MSG_WRONG_REQUEST_HEAD];
    }
    if (!preg_match(REGEX_STR_USR_BID_PATTERN, $login_data[usrBid])) {
        return ['retCode'=>ERR_CODE_BID_FID_PSW_FAILED, 'retMsg'=>ERR_MSG_BID_FID_PSW_FAILED];
    }
    if (!preg_match(REGEX_STR_USR_FID_PATTERN, $login_data[usrFid])) {
        return ['retCode'=>ERR_CODE_BID_FID_PSW_FAILED, 'retMsg'=>ERR_MSG_BID_FID_PSW_FAILED];
    }
    $opt = intval($login_data['opt']);
    if ($opt == 0) {
        if (!preg_match(REGEX_STR_PASSWORD_PATTERN, $login_data[usrPwd])) {
            return ['retCode'=>ERR_CODE_BID_FID_PSW_FAILED, 'retMsg'=>ERR_MSG_BID_FID_PSW_FAILED];
        }
    } else if ($opt == 1) {
        // Check token.
        if (!preg_match(REGEX_STR_PASSWORD_PATTERN, $login_data['token'])) {
            return ['retCode'=>ERR_CODE_CHK_TOKEN_FAILED, 'retMsg'=>ERR_MSG_CHK_TOKEN_FAILED];
        }
    } else {
        return ['retCode'=>ERR_CODE_WRONG_REQUEST_HEAD, 'retMsg'=>ERR_MSG_WRONG_REQUEST_HEAD];
    }
    return ['retCode'=>SUCCESS_CODE, 'retMsg'=>SUCCESS_MSG];
}

/*---------------------------Check the common request data.-------------------------------------*/

// Check the request header.
function check_head($head, $optMinID, $optMaxID) {
    // Check user building ID.
    if (!preg_match(REGEX_STR_USR_BID_PATTERN, $head['usrBid'])) {       
        return false;
    }
    // Check user family ID.
    if (!preg_match(REGEX_STR_USR_FID_PATTERN, $head['usrFid'])) {       
        return false;
    }
    // Check token.
    if (!preg_match(REGEX_STR_PASSWORD_PATTERN, $head['token'])) {
        return false;
    }
    // Check opt id.
    if ($head['optID'] < $optMinID || $head['optID'] > $optMaxID) {
        return false;
    }
    return true;
}

/*---------------------------Check the image file from page add-group-purchase.-------------------------------------*/

// Define the result code and message for it.
define("ERR_CODE_UPLOAD_IMAGE_FILE_FAILED", -80);
define("ERR_CODE_IMAGE_FILE_SIZE_TOO_LARGE", -81);
define("ERR_CODE_IMAGE_FILE_EXT_WRONG", -82);
define("ERR_CODE_UPLOAD_IMAGE_FILE_METHOD_WRONG", -83);
define("ERR_CODE_IMAGE_FILE_TYPE_WRONG", -84);
define("ERR_CODE_MOVE_IMAGE_FILE_FAILED", -85);
define("ERR_CODE_UPDATE_IMAGE_NAME_TO_DB_FAILED", -86);
define("ERR_MSG_UPLOAD_IMAGE_FILE_FAILED", 'Upload image file failed!');
define("ERR_MSG_IMAGE_FILE_SIZE_TOO_LARGE", "File size must be smaller than 6M!");
define("ERR_MSG_IMAGE_FILE_EXT_WRONG", "Allow image type is jpe, jpeg, jpg, png, tif or tiff!");
define("ERR_MSG_UPLOAD_IMAGE_FILE_METHOD_WRONG", "File uploaded illegally!");
define("ERR_MSG_IMAGE_FILE_TYPE_WRONG", "File type is not real!");
define("ERR_MSG_APT_NAME_ALREADY_EXIST", "The project name already exist!");
define("ERR_MSG_MOVE_IMAGE_FILE_FAILED", "Move the uploaded image file failed!");
define("ERR_MSG_UPDATE_IMAGE_NAME_TO_DB_FAILED", "Update image name of user head to DB failed!");

// Define the fixed rules for image upload.
define("F_MAX_SIZE", 6 * 1024 * 1024);   // 6M size
define("ARR_ALLOW_EXT", array('jpe', 'jpeg', 'jpg', 'png', 'tif', 'tiff'));

// Check the detail info of the uploading image file.
function check_gpi_img_file_info($fileInfo, $fileExt) {
    switch ($fileInfo['error']) {
        case 0:
            // Check file size.
            if ($fileInfo['size'] > F_MAX_SIZE) {
                return ['retCode'=>ERR_CODE_IMAGE_FILE_SIZE_TOO_LARGE, 'retMsg'=>ERR_MSG_IMAGE_FILE_SIZE_TOO_LARGE];
            }
            // Check file extension.
            if (!in_array($fileExt, ARR_ALLOW_EXT)) {
               return ['retCode'=>ERR_CODE_IMAGE_FILE_EXT_WRONG, 'retMsg'=>ERR_MSG_IMAGE_FILE_EXT_WRONG];
            }
            // Check that if the file was uploaded by http post or not.
            if (!is_uploaded_file($fileInfo['tmp_name'])) {
                return ['retCode'=>ERR_CODE_UPLOAD_IMAGE_FILE_METHOD_WRONG, 'retMsg'=>ERR_MSG_UPLOAD_IMAGE_FILE_METHOD_WRONG];
            }
            // Check that if the file type is real or not.
            if (!getimagesize($fileInfo['tmp_name'])) {
                return ['retCode'=>ERR_CODE_IMAGE_FILE_TYPE_WRONG, 'retMsg'=>ERR_MSG_IMAGE_FILE_TYPE_WRONG];
            }
            return ['retCode'=>SUCCESS_CODE];
        case 1:
            //echo "File size is more than the value of upload_max_filesize in php config!";
            break;
        case 2:
            //echo "File size is more MAX_FILE_SIZE in form data!";
            break;
        case 3:
            //echo "File uploaded partly!";
            break;
        case 4:
            //echo "No select a uploaded file!";
            break;
        case 6:
            //echo "Not find the tmp path!";
            break;
        case 7:
        case 8:
            //echo "System error!";
            break;
    }
    return ['retCode'=>ERR_CODE_UPLOAD_IMAGE_FILE_FAILED, 'retMsg'=>ERR_MSG_UPLOAD_IMAGE_FILE_FAILED];
}

/*---------------------------Check the data from page add-group-purchase.-------------------------------------*/

// Define the result code and message for it.
define("ERR_CODE_GPI_NO_RECORD", -90);
define("ERR_CODE_GPI_INSERT_FAILED", -91);
define("ERR_CODE_GPI_CLOSE_FAILED", -92);
define("ERR_CODE_GPI_CANCEL_FAILED", -93);
define("ERR_MSG_GPI_NO_RECORD", "There is no gpi record!");
define("ERR_MSG_GPI_INSERT_FAILED", "Insert gpi failed!");
define("ERR_MSG_GPI_CLOSE_FAILED", "Close gpi failed!");
define("ERR_MSG_GPI_CANCEL_FAILED", "Cancel gpi failed!");

// Check the request params about page add-group-purchase.
function check_gpi_req_params($params) {
    // Check the orginal count of request params.
    $paramCnt = count($params);
    if ($paramCnt <= 0) {
        return false;
    }
    // The accumulate count of request params.
    $accumCnt = 0;
   
    // Check the name of gpi.
    if (!preg_match(REGEX_STR_GPI_NAME_PATTERN, $params['gpiName'])) {
        return false;
    }
    $accumCnt++;
    
    // Check the price of gpi.
    if (!preg_match(REGEX_STR_GPI_PEICE_PATTERN, $params['gpiPrice'])) {
        return false;
    }
    $accumCnt++;
    
    // Check the close datetime of gpi.
    if (!preg_match(REGEX_STR_DATE_TIME_PATTERN, $params['gpiEndTime'])) {
        return false;
    }
    $accumCnt++;
    
    // Check the detail of gpi.
    if (array_key_exists('gpiDetail', $params)) {
        if (!preg_match(REGEX_STR_DETAIL_PATTERN, $params['gpiDetail'])) {
            return false;
        }
        $accumCnt++;
    }

    // When all items checked complete, we need check if the original count of params is
    // the same with the accumulate count of params or not.
    if ($accumCnt !== $paramCnt) {
        return false;
    }
    return true;
}

/*---------------------------Check the data from page doing-grp-records.-------------------------------------*/

// Define the result code and message for it.
define("CODE_ORDER_ADD_SUCCESS", 100);
define("CODE_ORDER_UPD_SUCCESS", 101);
define("ERR_CODE_DOING_GPI_NO_RECORD", -100);
define("ERR_CODE_DOING_GPI_SELECT_FAILED", -101);
define("ERR_CODE_DOING_GPI_STATE_CHANGED", -102);
define("ERR_CODE_DOING_GPI_ID_INVALID", -103);
define("ERR_CODE_ORDER_ADD_FAILED", -104);
define("ERR_CODE_ORDER_UPD_FAILED", -105);
define("ERR_CODE_ORDER_RECORD_NOT_EXIST", -106);
define("ERR_CODE_ORDER_SEARCH_FAILED", -107);
define("ERR_CODE_ORDER_RETRIEVE_FAILED", -108);
define("ERR_CODE_ORDER_ID_INVALID", -109);
define("ERR_CODE_AUTH_CHECK_FAILED", -110);
define("ERR_CODE_CLOSE_GPI_FAILED", -111);
define("ERR_CODE_CANCEL_GPI_FAILED", -112);

define("MSG_ORDER_ADD_SUCCESS", "Add order success.");
define("MSG_ORDER_UPD_SUCCESS", "Update order success.");
define("ERR_MSG_DOING_GPI_NO_RECORD", "There is no doing gpi record!");
define("ERR_MSG_DOING_GPI_SELECT_FAILED", "Select doing gpi failed!");
define("ERR_MSG_DOING_GPI_STATE_CHANGED", "Gpi state changed, can't order!");
define("ERR_MSG_DOING_GPI_ID_INVALID", "GpiID is invalid!");
define("ERR_MSG_ORDER_ADD_FAILED", "Add order failed!");
define("ERR_MSG_ORDER_UPD_FAILED", "Update order failed!");
define("ERR_MSG_ORDER_RECORD_NOT_EXIST", "Order record is not exist!");
define("ERR_MSG_ORDER_SEARCH_FAILED", "Order record search failed!");
define("ERR_MSG_ORDER_RETRIEVE_FAILED", "Order retrieve failed!");
define("ERR_MSG_ORDER_ID_INVALID", "OrderID is invalid!");
define("ERR_MSG_AUTH_CHECK_FAILED", "Auth check failed!");
define("ERR_MSG_CLOSE_GPI_FAILED", "Close gpi failed!");
define("ERR_MSG_CANCEL_GPI_FAILED", "Cancel gpi failed!");

// Check the request params about page doing-grp-records when opt id is 1-5.
function check_doing_gpi_req_params($params, $optID) {
    // Check the orginal count of request params.
    $paramCnt = count($params);
    if (2 == $optID) {
        if (2 !== $paramCnt) {
            return false;
        }
    } else {
        if (1 !== $paramCnt) {
            return false;
        }
    }

    // The the other items of request params.
    switch ($optID) {
        case 1:
            // Check the gpiID.
            if (intval($params['gpiID']) < 1) {
                return false;
            }
            break;
        case 2:
            // Check the gpiID.
            if (intval($params['gpiID']) < 1) {
                return false;
            }
            // Check the buy count.
            if (!preg_match(REGEX_STR_BUY_CNT_PATTERN, $params['buyCnt'])) {
                return false;
            }
            break;
        case 3:
        case 4:
        case 5:
            // Check the gpiID.
            if (intval($params['gpiID']) < 1) {
                return false;
            }
            break;
        default:
            return false;
    }
    
    return true;
}

/*---------------------------Check the data from page complete-grp-records.-------------------------------------*/

// Define the result code and message for it.
define("ERR_CODE_CLOSED_GPI_NO_RECORD", -120);
define("ERR_CODE_CLOSED_GPI_SELECT_FAILED", -121);
define("ERR_CODE_CLOSED_GPI_ID_INVALID", -122);

define("ERR_MSG_CLOSED_GPI_NO_RECORD", "There is no closed gpi record!");
define("ERR_MSG_CLOSED_GPI_SELECT_FAILED", "Select completed gpi failed!");
define("ERR_MSG_CLOSED_GPI_ID_INVALID", "Closed gpiID is invalid!");

// Check the request params about page complete-grp-records when opt id is 1-2.
function check_closed_gpi_req_params($params) {
    // Check the orginal count of request params.
    $paramCnt = count($params);
    if (1 !== $paramCnt) {
        return false;
    }
    // Check the gpiID.
    if (intval($params['gpiID']) < 1) {
        return false;
    }
    return true;
}

/*---------------------------Check the data from page cancel-grp-records.-------------------------------------*/

// Define the result code and message for it.
define("ERR_CODE_CANCELED_GPI_NO_RECORD", -130);
define("ERR_CODE_CANCELED_GPI_SELECT_FAILED", -131);

define("ERR_MSG_CANCELED_GPI_NO_RECORD", "There is no canceled gpi record!");
define("ERR_MSG_CANCELED_GPI_SELECT_FAILED", "Select canceled gpi failed!");

/*---------------------------Check the data from page history-orders.-------------------------------------*/

// Define the result code and message for it.
define("ERR_CODE_HIS_ORDER_NO_RECORD", -140);
define("ERR_CODE_HIS_ORDER_SELECT_FAILED", -141);

define("ERR_MSG_HIS_ORDER_NO_RECORD", "There is no history order record!");
define("ERR_MSG_HIS_ORDER_SELECT_FAILED", "Select history orders failed!");