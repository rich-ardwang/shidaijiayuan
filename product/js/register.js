/*
 * File name:   register.js
 * Desc:        Define functions used for register.
 * Author:      Richard Wang <wanglei_gmgc@hotmail.com>
 * Create:      2022-04-10
 */

// Define item id.
let LOGIN_BID = "bid";
let LOGIN_FID = "fid";
let FIRST_PSW_ID = "firstInputPwd";
let AGAIN_PSW_ID = "againInputPwd";
let LOGIN_CHK_ID = "loginCheck";
let BTN_REGISTER = "btnReg";
let ERROR_BOARD = "errorBoard";
let PAGE_ERROR_MSG = "pageError";
let REGISTER_FORM = "regForm";

// Define regular expression for item.
let REGEX_STR_LOGIN_BID_PATTERN = "/^(?:[1-9]|[1][0-9]|20)$/g";
let REGEX_STR_LOGIN_FID_PATTERN = "/^(?:0[1-9]0[1-8]|[1-2][0-9]0[1-8]|300[1-8])$/g";
let REGEX_STR_FIRST_PSW_PATTERN = "/^[A-Za-z\\d@$!%*#?&]{6,20}$/g";

// Set copyright year.
setCopyrightYear();

// Cache item state for the form using function closure.
let cacheItemsState = (function () {
    let items_state = {
        bid_state:false,
        fid_state:false,
        frtPsw_state:false,
        againPsw_state:false,
        lcheck_state:true
    };

    return {
        resetItemState: function() {
            items_state.bid_state = false;
            items_state.fid_state = false;
            items_state.frtPsw_state = false;
            items_state.againPsw_state = false;
            items_state.lcheck_state = true;
        },
        getItemState: function (id) {
            switch (id) {
                case LOGIN_BID:
                    return items_state.lname_state;
                case LOGIN_FID:
                    return items_state.pnum_state;
                case FIRST_PSW_ID:
                    return items_state.frtPsw_state;
                case AGAIN_PSW_ID:
                    return items_state.againPsw_state;
                case LOGIN_CHK_ID:
                    return items_state.lcheck_state;
                default:
                    return false;
            }
        },
        setItemState: function (id, state) {
            switch (id) {
                case LOGIN_BID:
                    items_state.lname_state = state;
                    break;
                case LOGIN_FID:
                    items_state.pnum_state = state;
                    break;
                case FIRST_PSW_ID:
                    items_state.frtPsw_state = state;
                    break;
                case AGAIN_PSW_ID:
                    items_state.againPsw_state = state;
                    break;
                case LOGIN_CHK_ID:
                    items_state.lcheck_state = state;
                    break;
                default:
                    break;
            }
        }
    };
})();

function checkAndUpdateItemState(id, regexp) {
    let ret = checkItem(id, regexp);
    setItemState(id, ret);
    cacheItemsState.setItemState(id, ret);
    updateRegBtn();
}

function updateItemState(id, state) {
    setItemState(id, state);
    cacheItemsState.setItemState(id, state);
    updateRegBtn();
}

function checkLoginBid() {
    checkAndUpdateItemState(LOGIN_BID, REGEX_STR_LOGIN_BID_PATTERN);
}

function checkLoginFid() {
    checkAndUpdateItemState(LOGIN_FID, REGEX_STR_LOGIN_FID_PATTERN);
}

function checkFirstInputPsw() {
    checkAndUpdateItemState(FIRST_PSW_ID, REGEX_STR_FIRST_PSW_PATTERN);
}

function checkAgainInputPsw() {
    let firstPsw = document.getElementById(FIRST_PSW_ID).value;
    if (isEmpty(firstPsw)) {
        return;
    }
    let againPsw = document.getElementById(AGAIN_PSW_ID).value;
    if (firstPsw === againPsw) {
        updateItemState(AGAIN_PSW_ID, true);
    } else {
        updateItemState(AGAIN_PSW_ID, false);
    }
}

function checkLoginChk() {
    if (document.getElementById(LOGIN_CHK_ID).checked) {
        updateItemState(LOGIN_CHK_ID, true);
    } else {
        updateItemState(LOGIN_CHK_ID, false);
    }
}

function clearAllCheckState() {
    clearItemCheckState(LOGIN_BID);
    clearItemCheckState(LOGIN_FID);
    clearItemCheckState(FIRST_PSW_ID);
    clearItemCheckState(AGAIN_PSW_ID);
    clearItemCheckState(LOGIN_CHK_ID);
    cacheItemsState.resetItemState();
    updateRegBtn();
}

function getItemsFinalState() {
    let finalState = cacheItemsState.getItemState(LOGIN_BID);
    finalState &= cacheItemsState.getItemState(LOGIN_FID);
    finalState &= cacheItemsState.getItemState(FIRST_PSW_ID);
    finalState &= cacheItemsState.getItemState(AGAIN_PSW_ID);
    finalState &= cacheItemsState.getItemState(LOGIN_CHK_ID);
    return finalState;
}

function updateRegBtn() {
    if (getItemsFinalState()) {
        $('#btnReg').attr("disabled", false);
    } else {
        $('#btnReg').attr("disabled", true);
    }
}

function getFormData() {
    let bid = $('#' + LOGIN_BID).val();
    let fid = $('#' + LOGIN_FID).val();
    let psw = $('#' + FIRST_PSW_ID).val();
    let pswHash = CryptoJS.SHA256(psw);
    let data = {
        "2":bid, "3":fid, "4":pswHash.toString()
    };
    return data;
}

function enableSubmitBtn(state) {
    if (state) {
        btnControl(BTN_REGISTER, state, "注册");
    } else {
        btnControl(BTN_REGISTER, state, "注册中...");
    }
}

function showPageError(msg) {
    let errBoard = "<div class=\"alert alert-danger alert-dismissible fade show mt-3\" role=\"alert\">\n" +
        "                                <strong id=\"pageError\"></strong>\n" +
        "                                <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">\n" +
        "                                    <span aria-hidden=\"true\">&times;</span>\n" +
        "                                </button>\n" +
        "                            </div>";
    $('#' + ERROR_BOARD).html(errBoard);
    $('#' + PAGE_ERROR_MSG).html(msg);
}

$('#' + BTN_REGISTER).on('click', function(event) {
    event.preventDefault();

    // Get form values.
    var regData = getFormData();

    // Before send data, we invalid the submit button.
    enableSubmitBtn(false);

    $.post('../php/register.php',
        { 'register_data':JSON.stringify(regData) },
        function(result) {
            if (result.head.retCode !== 0) {
                enableSubmitBtn(true);
                if (-1 === result.head.retCode || -2 === result.head.retCode
                    || -3 === result.head.retCode) {
                    showPageError('请求参数错误！');
                } else if (-50 === result.head.retCode || -51 === result.head.retCode
                    || -52 === result.head.retCode) {
                    showPageError('楼号、房间号或密码输入有误！');
                } else if (-4 === result.head.retCode) {
                    showPageError('数据库链接失败，请联系客服！');
                } else if (-53 === result.head.retCode) {
                    showPageError('该房号已经被注册！');
                } else {
                    showPageError('请不要进行非法操作！');
                }
            } else {
                // Register success, we need to reset the form and initial this page.
                $("#" + REGISTER_FORM)[0].reset();
                clearAllCheckState();

                // Redirect a new jump page.
                $(location).attr('href', '../html/jump.html');
            }   
    }, 'json')
    .fail(function(response) {
        showPageError('网络或服务端异常，请稍后重试！');
        console.log('[错误信息：' + response.responseText + ']。');
        enableSubmitBtn(true);
    });
});
