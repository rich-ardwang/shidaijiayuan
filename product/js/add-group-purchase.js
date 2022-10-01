/*
 * File name:   add-group-purchase.js
 * Desc:        Define functions used for page add group purchase.
 * Author:      Richard Wang <wanglei_gmgc@hotmail.com>
 * Create:      2022-04-12
 */

// Define the control ID of all.
let GPI_NAME_ID = 'gpiName';
let GPI_PRICE_ID = 'gpiPrice';
let GPI_END_TIME_ID = 'gpiEndTime';
let GPI_DETAIL_ID = 'gpiDetail';
let GPI_DETAIL_IMG_FILE_ID = 'detailImgFile';
let GPI_PUB_INFO_BTN_ID = 'pubGpiInfo';

// Define regular expression for item.
let REGEX_STR_GPI_NAME_PATTERN = "/^[\\u4E00-\\u9FA5A-Za-z\\d_-]{1,20}$/g";
let REGEX_STR_GPI_PEICE_PATTERN = "/^[\\d]{1,5}[\\.][\\d]{2}$/g";
let REGEX_STR_GPI_END_TIME_PATTERN = "/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)\\s+([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/g";
let REGEX_STR_GPI_DETAIL_PATTERN = "/^[\\u4E00-\\u9FA5A-Za-z\\d!?;:~^(@)#$+=%/&*, .，。……￥！、？；：_-]{0,255}$/g";

// Create datetimepicker object by html id.
function createDatetimePicker(id) {
    $('#' + id).datetimepicker({
        locale:'zh-CN',
        showTodayButton:true,
        showClear:true,
        showClose:true,
        format:'YYYY-MM-DD HH:mm:ss',
        icons: {
            time: 'mdi mdi-clock-time-three-outline',
            date: 'mdi mdi-calendar-month-outline',
            up: 'mdi mdi-arrow-up-drop-circle-outline',
            down: 'mdi mdi-arrow-down-drop-circle-outline',
            previous: 'mdi mdi-arrow-left-drop-circle-outline',
            next: 'mdi mdi-arrow-right-drop-circle-outline',
            today: 'mdi mdi-calendar-today',
            clear: 'mdi mdi-eraser-variant',
            close: 'mdi mdi-window-close'
        },
        tooltips: {
            today: '返回今天',
            clear: '清空',
            close: '关闭',
            selectMonth: '选择月份',
            prevMonth: '上一个月',
            nextMonth: '下一个月',
            selectYear: '选择年份',
            prevYear: '上一年',
            nextYear: '下一年',
            selectDecade: '选择十年',
            prevDecade: '上一个十年',
            nextDecade: '下一个十年',
            prevCentury: '上一个世纪',
            nextCentury: '下一个世纪',
            pickHour: '设置小时',
            incrementHour: '加一小时',
            decrementHour: '减一小时',
            pickMinute: '设置分钟',
            incrementMinute: '加一分钟',
            decrementMinute: '减一分钟',
            pickSecond: '设置秒',
            incrementSecond: '加一秒',
            decrementSecond: '减一秒',
            togglePeriod: '切换周期',
            selectTime: '选择时间',
            selectDate: '选择日期'
        }
    });
}

// Check the request data.
// --reqData: the request data.
function checkReqData(reqData) {
    let ret;
    // Check the GPI name.
    ret = checkItemByValue(reqData.gpiName, REGEX_STR_GPI_NAME_PATTERN);
    if (!ret) {
        $.alert('团购名称不能为空，只能由汉字、字母、数字、下划线和中划线组成，最大长度20。', '输入错误');
        return false;
    }
    // Check the price.
    ret = checkItemByValue(reqData.gpiPrice, REGEX_STR_GPI_PEICE_PATTERN);
    if (!ret) {
        $.alert('输入金额整数部分最大5位，小数部分最大2位，请重新输入。', '输入错误');
        return false;
    }
    // Check the datetime.
    ret = checkItemByValue(reqData.gpiEndTime, REGEX_STR_GPI_END_TIME_PATTERN);
    if (!ret) {
        $.alert('截止时间不规范，请重新输入。', '输入错误');
        return false;
    }
    // Check the detail.
    if (reqData.hasOwnProperty('gpiDetail')) {
        ret = checkItemByValue(reqData.gpiDetail, REGEX_STR_GPI_DETAIL_PATTERN);
        if (!ret) {
            $.alert('团购描述由汉字、字母、数字和符号组成，允许符号!?;:~^(@)#$+=%/&*, .，。……￥！、？；：_-，请重新输入。', '输入错误');
            return false;
        }
        // If this item is empty, we don't need send it.
        if (isEmpty(reqData['gpiDetail'])) {
            delete reqData.gpiDetail;
        }
    }
    return true;
}

// Clear all the data in the controls.
function clearControls() {
    $('#' + GPI_NAME_ID).val('');
    $('#' + GPI_PRICE_ID).val('');
    $('#' + GPI_END_TIME_ID).val(CurentTime());
    $('#' + GPI_DETAIL_ID).val('');
    $('#'+GPI_DETAIL_IMG_FILE_ID)[0].value = '';
}

// AJAX request function for loading, adding, updating and deleting etc.
// --opt: 1=add GPI info.
// --reqFormData: the request form data.
function ajaxPubGPIRequest(opt, reqFormData) {
    // Set the other request data.
    reqFormData.append('optID', opt);
    reqFormData.append('bid', getCookie('bid'));
    reqFormData.append('fid', getCookie('fid'));
    reqFormData.append('tok', getCookie('token'));
    
    // We query the data from the server via AJAX.
    $.ajax({
	url: '../php/add-group-purchase-handler.php',
	type: 'post',
        cache: false,     // we don't need cache when upload file.
        processData: false, // data not serialized.
        contentType: false, // must false.
        data: reqFormData,
	success: function(result) {
            // Request and response ok.
            if (-6 === result.head.retCode) {
                // If token auth fialed, we redirect to page sign-in.
                $(location).attr('href', '../html/sign-in.html');
            } else if (-3 === result.head.retCode || -7 === result.head.retCode) {
                $.alert('请求参数错误！', '出错了！');
            } else if (-4 === result.head.retCode) {
                $.alert('数据库连接错误！', '出错了！');
            } else if (-81 === result.head.retCode) {
                $.alert('图片大小不能超过6M！', '出错了！');
            } else if (-82 === result.head.retCode) {
                $.alert('图片格式不正确！仅支持jpe, jpeg, jpg, png, tif or tiff。', '出错了！');
            } else if (-83 === result.head.retCode) {
                $.alert('这不是一张真正的图片！', '出错了！');
            } else if (-84 === result.head.retCode) {
                $.alert('图片文件类型不真实！', '出错了！');
            } else if (-80 === result.head.retCode) {
                $.alert('图片文件不合法！', '出错了！');
            } else if (-2 === result.head.retCode) {
                $.alert('请求数据解码错误！', '出错了！');
            } else if (-91 === result.head.retCode) {
                $.alert('团购信息发布失败！', '出错了！');
            } else if (-8 === result.head.retCode) {
                $.alert('无发布权限！', '出错了！');
            } else if (0 === result.head.retCode) {
                $.alert('['+$('#'+GPI_NAME_ID).val()+']，发布成功！', '成功 😄');
                // Clear all the data in controls.
                clearControls();
            } else {
                $.alert('请不要进行非法操作！', '出错了！');
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

// Run the operations as below when loading the document.
$(function() {
    // Set copyright year.
    setCopyrightYear();
    
    // Create classes of input control.
    digitalInputControl('gpi-price-cls', 2, false);
    
    // Create datetime picker.
    createDatetimePicker(GPI_END_TIME_ID);
    $('#' + GPI_END_TIME_ID).val(CurentTime());
    
    // When the image file was picked, we need check the file.
    $('#' + GPI_DETAIL_IMG_FILE_ID).change(function() {
        let file = $('#'+GPI_DETAIL_IMG_FILE_ID)[0].files[0];
        if (file) {
            const fileSize = file.size;
            const isLt6M = (fileSize / 1024 / 1024) < 6;
            if (!isLt6M) {
                $.alert('图片大小不能超过6M，请重新选择。', '出错了！');
                $('#'+GPI_DETAIL_IMG_FILE_ID)[0].value = '';
                return;
            }
            const fileName = file.name;
            const fileExt = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
            if (
                fileExt !== 'jpe' && fileExt !== 'jpeg' && fileExt !== 'jpg' &&
                fileExt !== 'png' && fileName !== 'tif' && fileName !== 'tiff'
            ) {
                $.alert('仅支持图片格式：jpe、jpeg、jpg、png、tif、tiff。', '出错了！');
                $('#'+GPI_DETAIL_IMG_FILE_ID)[0].value = '';
                return;
            }
        }
    });
    
    // Monitor the event of the publish info button.
    $('#' + GPI_PUB_INFO_BTN_ID).click(function() {
        // Create request data.
        let reqData = {
            'gpiName':$('#'+GPI_NAME_ID).val(), 'gpiPrice':$('#'+GPI_PRICE_ID).val(),
            'gpiEndTime':$('#'+GPI_END_TIME_ID).val(), 'gpiDetail':$('#'+GPI_DETAIL_ID).val()
        };
        // Check request data.
        if (!checkReqData(reqData)) {
            return;
        }
        // Create form data of all.
        let formData = new FormData();
        formData.append('reqData', JSON.stringify(reqData));
        // If user don't select any image files, we don't need to send it.
        let fileItem = $('#'+GPI_DETAIL_IMG_FILE_ID)[0];
        if (!isEmpty(fileItem.value)) {
            formData.append('gpiPic', fileItem.files[0]);
        }
        // Send the form data to server.
        ajaxPubGPIRequest(1, formData);
    }); 
});
