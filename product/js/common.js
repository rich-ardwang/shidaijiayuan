/*
 * File name:   common.js
 * Desc:        Define the common functions using as utils.
 * Author:      Richard Wang <wanglei_gmgc@hotmail.com>
 * Create:      2022-04-11
 */

// Get current time of this system.
function CurentTime() { 
    var now = new Date();
    var year = now.getFullYear();       //年
    var month = now.getMonth() + 1;     //月
    var day = now.getDate();            //日
    var hh = now.getHours();            //时
    var mm = now.getMinutes();          //分
    var ss = now.getSeconds();          //秒
    var clock = year + "-";
    if (month < 10)
        clock += "0";

    clock += month + "-";

    if (day < 10)
        clock += "0";

    clock += day + " ";

    if (hh < 10)
        clock += "0";

    clock += hh + ":";
    if (mm < 10) clock += '0'; 
    clock += mm + ":"; 

    if (ss < 10) clock += '0'; 
    clock += ss; 
    return clock;
}

// Get current year of this system.
function curentYear() {
    let now = new Date();
    return now.getFullYear();
}

// Get year from the given datetime.
function getYearFromDatetime(datetime) {
    let date = new Date(datetime);
    let year = date.getFullYear();
    return year;
}

// Get month from the given datetime.
function getMonthFromDatetime(datetime) {
    let date = new Date(datetime);
    let month = date.getMonth() + 1;
    if (month < 10)
        month = '0' + month;
    return month;
}

// Get year and month from the given datetime.
function getYearMonthFromDatetime(datetime) {
    let date = new Date(datetime);
    let year = date.getFullYear();
    let month = date.getMonth() + 1;
    let clock = year + "-";
    if (month < 10)
        clock += "0";
    clock += month + "-";
    return clock;
}

// Set copyright year.
function setCopyrightYear() {
    document.getElementById("copy-year").innerHTML = curentYear();
}

function setItemState(id, valid) {
    var idx = "#" + id;
    if (valid) {
        $(idx).removeClass("is-invalid").addClass("is-valid");
    } else {
        $(idx).removeClass("is-valid").addClass("is-invalid");
    }
}

function clearItemCheckState(id) {
    var idx = "#" + id;
    $(idx).removeClass("is-invalid");
    $(idx).removeClass("is-valid");
}

function checkItem(id, regexp) {
    var item = document.getElementById(id);
    var pattern = eval(regexp);
    if (pattern.test(item.value)) {
        return true;
    } else {
        return false;
    }
}

function checkItemByValue(value, regexp) {
    var pattern = eval(regexp);
    if (pattern.test(value)) {
        return true;
    } else {
        return false;
    }
}

function changeItemBgColor(object, color) {
    var clrStyle = "background-color:" + color + ";";
    $(object).attr("style", clrStyle);
}

function isEmpty(str){
    if (typeof str === "undefined" || str === null || str === "") {
        return true;
    } else {
        return false;
    }
}

function btnControl(id, state, value) {
    if (state) {
        $('#' + id).attr("disabled", false);
        $('#' + id).html(value);
    } else {
        $('#' + id).attr("disabled", true);
        $('#' + id).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>\n' + value);
    }
}

function setItemVisible(id, show) {
    if (show) {
        $('#' + id).removeClass('invisible').addClass('visible');
    } else {
        $('#' + id).removeClass('visible').addClass('invisible');
    }
}

// Update the inner html content of optional item.
// -id: The id of optional item.
// -optVal: The value of optional item.
// -repVal: If the optional value is empty, it'll be repalced to repVal.
function updOptionalItemHtml(id, optVal, repVal) {
    if (isEmpty(optVal)) {
        $('#' + id).html(repVal);
    } else {
        $('#' + id).html(optVal);
    }
}

//-----------------------------------Operate cookie using js.-----------------------------------------------//
function setCookie(cname, cval, exdays) {
    let cookie = cname + "=" + cval + ";";
    if (!isEmpty(exdays)) {
        let d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        let expires = "expires=" + d.toUTCString();
        cookie += expires + ";";
    }
    document.cookie = cookie + "path=/";
}

function getCookie(cname) {
    let name = cname + "=";
    let ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function removeCookie(cname) {
    let d = new Date();
    d.setYear(d.getYear() - 20);
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=;" + expires + ";path=/";
}

//-----------------------------------Digital input control.-----------------------------------------------//
// When key up and down in input control, we do regular replacement.
function keyReplace(obj, bitCnt, minus) {
    // If dot is first character, replace it to empty.
    obj.value = obj.value.replace(/^[\.]/, '');
    // If there are many continous dots, only leave the first one.
    obj.value = obj.value.replace(/\.{2,}/g, '.');
    // Remove other dots, only leave the first one.
    obj.value = obj.value.replace(".", "$#$").replace(/\./g, "").replace("$#$", ".");
    if (minus) {
        // All are replaced to empty except number, dot and minus.
        obj.value = obj.value.replace(/[^\d.-]/g, '');
        // After dot there must be bitCnt decimal bits.
        obj.value = obj.value.replace(eval('/([0-9]+\\.[0-9]{' + bitCnt.toString() + '}|\\-\\.[0-9]{' + bitCnt.toString() + '})[0-9]*/'), "$1");        
        // If there are many continous minus, only leave the first one.
        obj.value = obj.value.replace(/[\-]{2,}/g, '-');
        // There must not be minus after number or dot.
        obj.value = obj.value.replace(/([0-9]+|\.)[\-]/, '$1');
    } else {
        // All are replaced to empty except number and dot.
        obj.value = obj.value.replace(/[^\d.]/g, '');
        // After dot there must be bitCnt decimal bits.
        obj.value = obj.value.replace(eval('/([0-9]+\\.[0-9]{' + bitCnt.toString() + '})[0-9]*/'), "$1");
    }
}

// Add classes used for input control to format decimal text.
function digitalInputControl(className, bitCnt, minus) {
    // Acquire focus.
    $('.' + className).bind('focus', function() {
        let num = Number(this.value);
        if (0 === num) {
            this.value = '';
        } else {
            this.value = String(num);
        }
    });
    // Key up and down.
    $('.' + className).bind('keyup', function() {
        keyReplace(this, bitCnt, minus);
    });
    $('.' + className).bind('keydown', function() {
        keyReplace(this, bitCnt, minus);
    });
    // Lose focus.
    $('.' + className).bind('blur', function() {
        if (minus) {
            // If the first charactor is minus or minus and dot, we replace them to zero.
            if (this.value === '-' || this.value === '-.') {
                this.value = 0;
            }
        }
        // Format decimal text.
        this.value = Number(this.value).toFixed(bitCnt);
    });
}

//-----------------------------------ICON PICKER-----------------------------------------------//
// Create icon picker by id.
function createIconPicker(btnId, showId, cols, rows) {
    $('#' + btnId).iconpicker()
        .iconpicker('setCols', cols)
        .iconpicker('setRows', rows)
        .iconpicker('setSelectedClass', 'btn-warning')
        .iconpicker('setUnselectedClass', 'btn-info')
        .on('change', function(e) {
            if ("empty" === e.icon) {
                let cls = "text-danger";
                let val = "请选择图标！";
                $('#' + showId).removeClass().addClass(cls);
                $('#' + showId).html(val);
            } else {
                let cls = "text-primary mdi-48px mdi " + e.icon;
                $('#' + showId).removeClass().addClass(cls);
                $('#' + showId).html('');
            }
            // Update the attribute data-icon of the icon picker btn. 
            $('#' + btnId).attr('data-icon', e.icon);
    });
}

//-----------------------------------Common Page Loading-----------------------------------------------//
// If no login yet, redirect to home page.
function noLoginRedirect() {
    if (isEmpty(getCookie("token")) || isEmpty(getCookie("bid")) || isEmpty(getCookie("fid"))) {
        $(location).attr('href', '../php/index.php');
    }
}

// Get the account book id from cookie, if it is not exist in cookie,
// we request it from the server.
// If there is a default account book id, the server will return it to us,
// and if not the server will return the latest one.
// If there is not account book id, we redirect to page account-book-mng.
// --completeHandler: When the abkID request completed, the handler will be revoked.
function getAbkId(completeHandler) {
    // Get the current account book id from cookie.
    if (!isEmpty(getCookie('abkID'))) {
        if (completeHandler !== undefined && typeof completeHandler === 'function') {
            completeHandler();
        }
        return;
    }
    
    // Get user name and token from local storage or cookie.
    let uname = getCookie('lname');
    let token = getCookie('token');

    // Create request data.
    let reqData = {
        'optID':4, 'un':uname, 'tok':token,
        'reqData':JSON.stringify({ 'flag':'dft' }) 
    };
    
    // We query the data from the server via AJAX.
    $.ajax({
	url: '../php/account-book.php',
	type: 'post',
	data: reqData,
	success: function(result) {
            if (0 === result.head.retCode) {
                // If success, we set the account book id to cookie.
                setCookie('abkID', result.payload.abkID);
                if (completeHandler !== 'undefined' && typeof completeHandler === 'function') {
                    completeHandler();
                }
            } else if (-64 === result.head.retCode) {
                // If there is not an account book id, we redirect to page account-book-mng.
                $.alert({
                    title: '温馨提示',
                    content: '你还没有创建账本，请先创建账本。',
                    buttons: {
                        '知道了': function () {
                            $(location).attr('href', '../html/account-book-mng.html');
                        }
                    }
                });
            } else if (-6 === result.head.retCode) {
                // If token auth fialed, we redirect to page sign-in.
                $(location).attr('href', '../html/sign-in.html');
            } else {
                $.alert('请求账本时发生错误，请刷新页面试一下。', '出错了！');
            }
	},
	error: function(jqXHR, textStatus, errorMsg) {
            // jqXHR is a XMLHttpRequest object which is packaged by jquery.
            // textStatus value： null、"timeout"、"error"、"abort" or "parsererror".
            // errorMsg value： "Not Found"、"Internal Server Error" and so on.
            console.log("请求失败：" + errorMsg);
            $.alert('网络或服务端状态异常，请稍后再试。', '出错了！');
	}
    });
}

// Set src of user's head image.
// -url: the url of the image of user's head.
function setHeadImgSrc(id, url) {
    if (!isEmpty(url)) {
        $("#" + id).attr('src', url);
    }
}

// Request user brief info data to the server and load them to page.
function loadUsrBriefInfo() {
    // Create request data.
    let reqData = {
        'optID':0, 'un':getCookie('lname'), 'tok':getCookie('token')
    };
    
    // We query the data from the server via AJAX.
    $.ajax({
	url: '../php/user.php',
	type: 'post',
	data: reqData,
	success: function(result) {
            if (0 === result.head.retCode) {
                // If success, we load the brief info of user.
                setHeadImgSrc("briefUsrHeadMenu", result.payload.urlUsrHeadImg);
                $("#briefNickNameMenu").html(result.payload.usrNickName);
                setHeadImgSrc("briefUsrHeadDropdown", result.payload.urlUsrHeadImg);
                $("#briefNickNameDropdown").html(result.payload.usrNickName + '<small class="pt-1 brief-user-info">' + result.payload.usrEmail + '</small>');
            } else if (-6 === result.head.retCode) {
                // If token auth fialed, we redirect to page sign-in.
                $(location).attr('href', '../html/sign-in.html');
            } else {
                $.alert('请求用户概要信息时发生错误，请刷新页面试一下。', '出错了！');
            }
	},
	error: function(jqXHR, textStatus, errorMsg) {
            // jqXHR is a XMLHttpRequest object which is packaged by jquery.
            // textStatus value： null、"timeout"、"error"、"abort" or "parsererror".
            // errorMsg value： "Not Found"、"Internal Server Error" and so on.
            console.log("请求失败：" + errorMsg);
            $.alert('网络或服务端状态异常，请稍后再试。', '出错了！');
	}
    });
}

//-----------------------------------Money Calculation-----------------------------------------------//
// Compare two money.
// Return value: 0:money = target, 1:money > target, -1:money < target.
function moneyCompare(target, money) {
    return math.compare(math.bignumber(money), math.bignumber(target)).toNumber();
}

// Add moneyA and moneyB and trans the result to number.
function moneyAdd2Num(mnyA, mnyB) {
    return math.add(math.bignumber(mnyA), math.bignumber(mnyB)).toNumber();
}

// Add moneyA and moneyB and trans the result to string.
function moneyAdd2String(mnyA, mnyB) {
    return math.add(math.bignumber(mnyA),math. bignumber(mnyB)).toString();
}

// Subtract moneyA and moneyB and trans the result to number.
function moneySub2Num(mnyA, mnyB) {
    return math.subtract(math.bignumber(mnyA), math.bignumber(mnyB)).toNumber();
}

// Subtract moneyA and moneyB and trans the result to string.
function moneySub2String(mnyA, mnyB) {
    return math.subtract(math.bignumber(mnyA), math.bignumber(mnyB)).toString();
}

// ABS function of money.
function moneyAbs2Num(money) {
    return math.abs(math.bignumber(money)).toNumber();
}

//-----------------------------------Object Operate-----------------------------------------------//
// Copy object.
function copyObject(obj) {
    let newObj = {};
    for (let i in obj) {
        newObj[i] = obj[i];
    }
    return newObj;
}

//-----------------------------------Bootstrap table operation.-----------------------------------------------//
// Reload the new data to this table and the old data will be removed.
function reloadTable(tblID, objNewData) {
    $('#' + tblID).bootstrapTable('load', objNewData);
}

// Get row data of the table by unique ID.
// --tblID: the table ID.
// --uniqueID: the unique ID which user set, not the index.
function getTableRowByUid(tblID, uniqueID) {
    return $('#' + tblID).bootstrapTable('getRowByUniqueId', uniqueID);
}

// Insert a row to the table.
// --tblID: the table ID.
// --row: the row data except the 'total' field.
function insertTableRow(tblID, row) {
    $('#' + tblID).bootstrapTable('insertRow', {
        index:0,
        row:row
    });
}

// Update the table row.
// --tblID: the table ID.
// --index: the table index is not the value of unique ID, it starts from zero.
// --newRow: the new row data except the 'total' field.
function updateTableRow(tblID, index, newRow) {
    $('#' + tblID).bootstrapTable('updateRow', {
        index:index,
        row:newRow
    });
}

// Remove a row from the table.
// --tblID: the table ID.
// --uniqueIDField: the field of unique ID.
// --uniqueIDValue: the value of unique ID, is not the table index.
function removeTableRow(tblID, uniqueIDField, uniqueIDValue) {
    $('#' + tblID).bootstrapTable('remove', {
        field:uniqueIDField,
        values:[uniqueIDValue]
    });
}

// Remove the table.
function dropTable(tblID) {
    $('#' + tblID).remove();
}

// Destroy the table itself.
function destroyTable(tblID) {
    $('#' + tblID).bootstrapTable('destroy');
}

//---------------------------------------------URL API--------------------------------------------------------//
// Get params from URL.
// Example:
// --URL: http://www.runoob.com/index.php?id=1&image=awesome.jpg
// --getParamsFromUrl(id) will return 1.
// --getParamsFromUrl(image) will return awesome.jpg.
// --If the key not exist, this function will return false.
function getParamsFromUrl(key) {
    let query = window.location.search.substring(1);
    let vars = query.split("&");
    for (let i=0; i<vars.length; i++) {
         let pair = vars[i].split("=");
         if (pair[0] === key) { return pair[1]; }
    }
    return false;
}
