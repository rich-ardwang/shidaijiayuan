/*
 * File name:   sign-in.js
 * Desc:        Define functions used for signing in.
 * Author:      Richard Wang <wanglei_gmgc@hotmail.com>
 * Create:      2022-04-10
 */

let REGEX_STR_LOGIN_BID_PATTERN = "/^(?:[1-9]|[1][0-9]|20)$/g";
let REGEX_STR_LOGIN_FID_PATTERN = "/^(?:0[1-9]0[1-8]|[1-2][0-9]0[1-8]|300[1-8])$/g";
let REGEX_STR_FIRST_PSW_PATTERN = "/^[A-Za-z\\d@$!%*#?&]{6,20}$/g";
let LOGIN_BID_ID = "bid";
let LOGIN_FID_ID = "fid";
let USER_PASSWORD = "inputPassword";
let SIGN_IN_FORM = "signinForm";
let REMEMBER_ME = "rememberMe";

function checkInput() {
    let ret = checkItem(LOGIN_BID_ID, REGEX_STR_LOGIN_BID_PATTERN);
    ret &= checkItem(LOGIN_FID_ID, REGEX_STR_LOGIN_FID_PATTERN);
    ret &= checkItem(USER_PASSWORD, REGEX_STR_FIRST_PSW_PATTERN);
    if (ret) {
        return true;
    }
    return false;
}

function setCheckState(state) {
    setItemState(LOGIN_BID_ID, state);
    setItemState(LOGIN_FID_ID, state);
    setItemState(USER_PASSWORD, state);
}

function clearAllItemState() {
    clearItemCheckState(LOGIN_BID_ID);
    clearItemCheckState(LOGIN_FID_ID);
    clearItemCheckState(USER_PASSWORD);
}

function enableSubmitBtn(state) {
    if (state) {
        btnControl("btnLogin", state, "登录");
    } else {
        btnControl("btnLogin", state, "登录中...");
    }
}

function ajaxPostRequest($reqData) {
    $.post('../php/sign-in.php',
        { 'login_data':JSON.stringify($reqData) },
        function(result) {
            let retCode = result.head.retCode;
            if ($reqData['opt'] === 0) {
                if (retCode !== 0) {
                    if (-70 === retCode) {
                        $('#output').html("楼号、房间号或密码不正确！");
                    } else if (-1 === retCode || -2 === retCode || -3 === retCode) {
                        $('#output').html("请求参数不正确！");
                    } else if (-4 === retCode) {
                        $('#output').html("数据库链接失败，请联系客服！");
                    } else {
                        $('#output').html("请不要进行非法操作！");
                    }
                    enableSubmitBtn(true);
                } else {
                    // Get the response token and save it to the cookie.
                    //let remember = document.getElementById(REMEMBER_ME).checked;
                    //let remember = $('#' + REMEMBER_ME).prop("checked");
                    let remember = $('#' + REMEMBER_ME).is(':checked');
                    if (remember) {
                        // If login has been success, we don't need login again today.
                        setCookie("token", result.payload.token, 1);
                        setCookie("type", result.payload.type, 1);
                        setCookie("bid", $reqData['2'], 1);
                        setCookie("fid", $reqData['3'], 1);
                    } else {
                        // After the browser has been closed, the cookies not exist.
                        setCookie("token", result.payload.token);
                        setCookie("type", result.payload.type);
                        setCookie("bid", $reqData['2']);
                        setCookie("fid", $reqData['3']);
                    }
                    
                    // Sign in success, we need to reset the form and initial this page.
                    $("#" + SIGN_IN_FORM)[0].reset();
                    clearAllItemState();

                    // Redirect to index page.
                    $(location).attr('href', '../php/index.php');
                }
            } else if ($reqData['opt'] === 1) {
                if (retCode === 0) {
                    // Redirect to index page.
                    $(location).attr('href', '../php/index.php');
                }
            }
    }, 'json')
    .fail(function(response) {
        $('#output').html('网络或服务端异常，请稍后重试！');
        console.log('[错误信息：' + response.responseText + ']。');
        enableSubmitBtn(true);
    });
}

$(function() {
    // Read the cookie and request to auth the token.
    // If token auth success, we redirect to index page.
    // If token auth failed, we view this page and the user can login.
    let tok = getCookie("token");
    let bid = getCookie("bid");
    let fid = getCookie("fid");
    if (!isEmpty(tok) && !isEmpty(bid) && !isEmpty(fid)) {
        data = { "opt":1, "2":bid, "3":fid, "token":tok };
        ajaxPostRequest(data);
    }
    
    // Set copyright year.
    setCopyrightYear();
});

$('#btnLogin').on('click', function(event) {
    event.preventDefault();

    // Check input data.
    if (!checkInput()) {
        setCheckState(false);
        return;
    }
    setCheckState(true);

    // Create login data.
    var bid = $('#' + LOGIN_BID_ID).val();
    var fid = $('#' + LOGIN_FID_ID).val();
    var psw = $('#' + USER_PASSWORD).val();
    var hash = CryptoJS.SHA256(psw);
    var data = { "opt":0, "2":bid, "3":fid ,"4":hash.toString() };
    
    // Before login complete, we unable the submit button.
    enableSubmitBtn(false);

    // Post the request.
    ajaxPostRequest(data);
});
