/*
 * File name:   doing-grp-records.js
 * Desc:        Define functions used for page doing group records.
 * Author:      Richard Wang <wanglei_gmgc@hotmail.com>
 * Create:      2022-04-13
 */

// Define the control ID of all.
let GPI_TABLE_TOOL_BAR_ID = 'toolbar';
let GPI_DOING_RELOAD_BTN_ID = 'reloadDoingGpi';
let GPI_DOING_TABLE_ID = 'doingGpiTable';
let GPI_ORDERS_TABLE_ID = 'gpiOrdersTable';
let ADD_ORDER_BTN_ID = 'addOrder';
let CLOSE_GPI_ID = 'endGpi';
let CANCEL_GPI_ID = 'cancelGpi';
let SHOW_GPI_DETAIL_ID = 'showGpiDetail';
let SHOW_GPI_ALL_ORDERS = 'showGpiOrders';

// Define regular expression for item.
let REGEX_STR_BUY_CNT_PATTERN = "/^(?:[1-9]|[1-9][0-9]|[1-9][0-9][0-9]|1000)$/g";

// We add the operate icons for ervey line of the table.
function operateFormatter(value, row, index) {
    let operateHtml = '<button class="btn btn-sm btn-success mr-1" id="'+ADD_ORDER_BTN_ID+'">';
    operateHtml += '参团</button>';
    if ('2' !== getCookie('type')) {
        operateHtml += '<button class="btn btn-sm btn-info mr-1" id="'+CLOSE_GPI_ID+'">';
        operateHtml += '封单</button>';
        operateHtml += '<button class="btn btn-sm btn-danger mr-1" id="'+CANCEL_GPI_ID+'">';
        operateHtml += '撤团</button>';
    }
    return operateHtml;
}

// The formatter of GPI detail info.
function descFormatter(value, row, index) {
    return [
        '<button class="btn btn-sm btn-info mr-2" id="'+SHOW_GPI_DETAIL_ID+'">',
        '查看</button>'
    ].join('');
}

// The formatter of order's count.
function cntFormatter(value, row, index) {
    return [
        '<button class="btn btn-sm btn-primary mr-2" id="'+SHOW_GPI_ALL_ORDERS+'">',
        value,
        '</button>'
    ].join('');
}

// The detail viewer callback function.
function detailFormatter(index, row) {
    let html = '<table>';
    html += '<tr><td>团号：</td><td>' + row['gpiID'] +  '</td></tr>';
    html += '<tr><td>团名：</td><td>' + row['gpiName'] +  '</td></tr>';
    //html += '<tr><td>详情：</td><td><button class="btn btn-sm btn-info mr-2" id="rdtCopy">查看</button></td></tr>';
    html += '<tr><td>价格：</td><td>' + row['gpiPrice'] + '</td></tr>';
    html += '<tr><td>截止时间：</td><td>' + row['gpiEndTime'] + '</td></tr>';
    html += '<tr><td>订单数：</td><td>' + row['gpiOrdersCnt'] + '</td></tr>';
    html += '<tr><td>团长：</td><td>' + row['gpiPublisher'] + '</td></tr>';
    //html += '<tr><td>操作：</td><td><button class="btn btn-sm btn-success mr-1" id="">参团</button>';
    //html += '<button class="btn btn-sm btn-info mr-1" id="">截止</button>';
    //html += '<button class="btn btn-sm btn-danger mr-1" id="">取消</button></td></tr>';
    html += '</table>';
    return html;
}

// Handle the events for searching, ordering and retrieving operations.
window.operateEvents = {
    'click #showGpiDetail': function (e, value, row, index) {
        //alert('You click like action, row: ' + JSON.stringify(row));
        $.confirm({
            useBootstrap:false,
            boxWidth:'100%',
            icon: 'mdi mdi-gift-outline mdi-24px text-primary',
            title: '团购详情',
            titleClass: 'text-primary',
            type: 'green',
            theme: 'dark',
            draggable: 'true',
            content:
                '<table>' +
                '<tr><td><p style="color:green">描述：</p></td><tr>' + 
                '<tr><td><p>'+row['gpiDetail']+'</p></td></tr>' +
                '<tr><td calss="text-info"><p style="color:green">详情展示：</p></td><tr>' +
                '<tr><td><img src="../upld/gpiPics/'+row['gpiImgName']+'" class="img-fluid" alt="团长未发图片:("></td></tr>' +
                '</table>',
            buttons: {
                cancel: {
                    text: '关闭',
                    btnClass: 'btn-blue'
                }
            }
        });
    },
    'click #showGpiOrders': function (e, value, row, index) {
        //alert('You click like action, row: ' + JSON.stringify(row));
        $.confirm({
            // Let the dialog box become fluid.
            containerFluid:'true',
            icon: 'mdi mdi-human-male-female mdi-24px text-primary',
            title: '当前订单数',
            titleClass: 'text-primary',
            type: 'blue',
            theme: 'dark',
            draggable: 'true',
            content:
                '<div class="row">' +
                '<div class="col-md">' +
                '<table id="'+GPI_ORDERS_TABLE_ID+'" class="table-dark table-striped" data-mobile-responsive="true"></table>' +
                '</div>' +                                   
                '</div>',
            buttons: {
                cancel: {
                    text: '关闭',
                    btnClass: 'btn-blue'
                }
            },
            onOpenBefore: function () {
                // Create request data and send to server.
                reqData = { 'gpiID':row['gpiID'] };
                ajaxRequest(1, reqData);
                return;
            }
        });
    },
    'click #addOrder': function (e, value, row, index) {
        //alert('You click like action, row: ' + JSON.stringify(row));
        $.confirm({
            icon: 'mdi mdi-book-edit-outline mdi-24px text-success',
            title: '立即参团',
            titleClass: 'text-success',
            type: 'green',
            theme: 'dark',
            draggable: 'true',
            content:
                '<h6 class="text-info">No：'+row['gpiID']+'</h6>' +
                '<h6 class="text-info">团名：'+row['gpiName']+'</h6>' +
                '<h6 class="text-info">单价：'+row['gpiPrice']+'</h6>' +
                '<form style="width: 240px;">' +
                '<div class="form-row">' +
                '<div class="form-group col-md">' +
                '<label class="text-info" for="buyCntInput">订购份数：</label>' +
                '<input type="text" class="form-control-sm" size="4" maxlength="4" id="buyCntInput" value="" placeholder="1-1000份">' +
                '</div>' +
                '</div>' +
                '</form>',
            buttons: {
                orderAddUpdBtn: {
                    text: '下单',
                    btnClass: 'btn-green',
                    action: function () {
                        // Check the buy count.
                        let buyCnt = $('#buyCntInput').val();
                        if (!checkItemByValue(buyCnt, REGEX_STR_BUY_CNT_PATTERN)) {
                            $.alert('份数范围1-1000。', '输入错误');
                            return false;
                        }
                        // Create request data and send to server.
                        reqData = { 'gpiID':row['gpiID'], 'buyCnt':buyCnt };
                        ajaxRequest(2, reqData);
                        return;
                    }
                },
                cancel: {
                    text: '关闭',
                    btnClass: 'btn-blue'
                },
                retrieve: {
                    text: '撤单',
                    btnClass: 'btn-danger',
                    action: function () {
                        $.confirm({
                            theme: 'dark',
                            content: '是否确定撤销此订单？',
                            buttons: {
                                confirm: {
                                    text: '确定',
                                    btnClass: 'btn-red',
                                    action: function () {
                                        // Create request data and send to server.
                                        reqData = { 'gpiID':row['gpiID'] };
                                        ajaxRequest(3, reqData);
                                        return;
                                    }
                                },
                                cancel: {
                                    text: '取消',
                                    btnClass: 'btn-blue'
                                }
                            }
                        });
                    }
                }
            }
        });
    },
    'click #endGpi': function (e, value, row, index) {
        $.confirm({
            icon: 'mdi mdi-calendar-clock mdi-24px',
            title: '截止封单',
            titleClass: 'text-info',
            type: 'info',
            typeAnimated: 'true',
            theme: 'dark',
            content: '确实要提前截止团购吗，封单后订单将不再更新。</br>是否确定封单？',
            buttons: {
                confirm: {
                    text: '确定',
                    btnClass: 'btn-info',
                    action: function () {
                        // Create request data and send to server.
                        reqData = { 'gpiID':row['gpiID'] };
                        ajaxRequest(4, reqData);
                        return;
                    }
                },
                cancel: {
                    text: '取消',
                    btnClass: 'btn-blue'
                }
            }
        });
    },
    'click #cancelGpi': function (e, value, row, index) {
        $.confirm({
            icon: 'mdi mdi-delete-forever mdi-24px',
            title: '撤团确认',
            titleClass: 'text-danger',
            type: 'red',
            typeAnimated: 'true',
            theme: 'dark',
            content: '撤销团购后不可恢复，也不提供统计功能。</br>是否确定撤销团购？',
            buttons: {
                confirm: {
                    text: '确定',
                    btnClass: 'btn-red',
                    action: function () {
                        // Create request data and send to server.
                        reqData = { 'gpiID':row['gpiID'] };
                        ajaxRequest(5, reqData);
                        return;
                    }
                },
                cancel: {
                    text: '取消',
                    btnClass: 'btn-blue'
                }
            }
        });
    }
};

// Create a new gpi table.
function createGpiTable(arrTblData) {
    $('#'+GPI_DOING_TABLE_ID).bootstrapTable({
        locale:'zh-CN',             // Table language is Chinese.
        sortReset:true,            // There are 3 states when sort field: increment, decrement and reset.
        cache:false,               // Don't use browser cache.
        toolbar:'#'+GPI_TABLE_TOOL_BAR_ID,     // Set toolbar id.
//      search:true,               // Show the search text input.
//      showRefresh:true,           // Show the refresh button.
        showToggle:true,           // Show the button to control toggle the card view and the table view.
//      showFullscreen:true,        // Show the full screen button.
//      showColumns:true,           // Show the filter button of columns.
//      showColumnsToggleAll:true,  // Show the toggle all checkbox in above button.
//      clickToSelect:true,         // True:When you click the row you will select radio box or check box automatically.
        detailView:true,            // Show the icon for viewing the detail.
        detailFormatter:detailFormatter,    // The callback function used for showing detail.
        minimumCountColumns:1,          // When you filter the columns, you must left the minimum count of the columns. 
//      showPaginationSwitch:true,      // Show the button to control hide pagination or not.
        pagination:true,               // Show the pagination detail information.
        idField:'gpiID',               // Set the unique id of column.
        pageSize:10,                   // Set the default page size of pagination.
        pageList:[5, 10, 15, 25, 50, 100, 'all'],   // Set the page size array used for pagination.
        showFooter:false,             // Show the column footer or not.
        sidePagination:'client',       // Select where to pagination, 'client' or 'server'.           
//      url: '../vendor/bootstrap-table/data/gpiData.json',     // If you select pagination in server, you should set the data url of the server.
//      ajax: loadRunningInfoForAcc,        // If you select pagination in server, you also can rewrite the ajax function.
//      responseHandler: responseHandler,   // Set the response handler (callback) function, after the server response, this function will be revoked.
        paginationPreText:'上一页',     // Replace the pre text to Chinese.
        paginationNextText:'下一页',    // Replace the next text to Chinese.
        data:arrTblData,                // Load the data to table.
        columns: [
            {
                field: 'gpiID',
                title: '团号',
                sortable: true,
                halign: 'left',
                align: 'left'
            },
            {
                field: 'gpiName',
                title: '团名',
                sortable: true,
                halign: 'left',
                align: 'left'
            },
            {
                field: 'gpiDesc',
                title: '详情',
                halign: 'left',
                align: 'left',
                formatter: descFormatter,
                events: window.operateEvents
            },
            {
                field: 'gpiPrice',
                title: '价格',
                sortable: true,
                halign: 'left',
                align: 'left'
            },
            {
                field: 'gpiEndTime',
                title: '截止时间',
                sortable: true,
                halign: 'left',
                align: 'left'
            },
            {
                field: 'gpiOrdersCnt',
                title: '订单数',
                sortable: true,
                halign: 'left',
                align: 'left',
                formatter: cntFormatter,
                events: window.operateEvents
            },
            {
                field: 'gpiPublisher',
                title: '团长',
                sortable: true,
                halign: 'left',
                align: 'left'
            },
            {
                field: 'gpiOperate',
                title: '操作',
                halign: 'left',
                align: 'left',
                formatter: operateFormatter,
                events: window.operateEvents
            }
        ]
    });
}

// Create a new gpi orders table.
function createGpiOrdersTable(arrTblData) {
    $('#'+GPI_ORDERS_TABLE_ID).bootstrapTable({
        locale:'zh-CN',             // Table language is Chinese.
        sortReset:true,            // There are 3 states when sort field: increment, decrement and reset.
        cache:false,               // Don't use browser cache.
        toolbar:'',                 // Set toolbar id.
        search:false,               // Show the search text input.
        showRefresh:false,           // Show the refresh button.
        showToggle:true,           // Show the button to control toggle the card view and the table view.
        showFullscreen:false,        // Show the full screen button.
        showColumns:false,           // Show the filter button of columns.
        showColumnsToggleAll:false,  // Show the toggle all checkbox in above button.
        clickToSelect:false,         // True:When you click the row you will select radio box or check box automatically.
        detailView:false,            // Show the icon for viewing the detail.
        detailFormatter:'',            // The callback function used for showing detail.
//      minimumCountColumns:1,          // When you filter the columns, you must left the minimum count of the columns. 
        showPaginationSwitch:false,      // Show the button to control hide pagination or not.
        pagination:false,               // Show the pagination detail information.
        idField:'ordID',               // Set the unique id of column.
//        pageSize:10,                   // Set the default page size of pagination.
//        pageList:[5, 10, 15, 25, 50, 100, 'all'],   // Set the page size array used for pagination.
        showFooter:false,             // Show the column footer or not.
        sidePagination:'client',       // Select where to pagination, 'client' or 'server'.
//        data:objData,                  // Load the data to table.
//      url: '../vendor/bootstrap-table/data/gpiData.json',     // If you select pagination in server, you should set the data url of the server.
//      ajax: loadRunningInfoForAcc,        // If you select pagination in server, you also can rewrite the ajax function.
//      responseHandler: responseHandler,   // Set the response handler (callback) function, after the server response, this function will be revoked.
        paginationPreText:'上一页',     // Replace the pre text to Chinese.
        paginationNextText:'下一页',    // Replace the next text to Chinese.
        data:arrTblData,
        columns: [
            {
                field: 'ordHomeName',
                title: '户号',
                sortable: true,
                halign: 'left',
                align: 'left'
            },
            {
                field: 'buyCnt',
                title: '份数',
                sortable: true,
                halign: 'left',
                align: 'left'
            },
            {
                field: 'ordPrice',
                title: '付款',
                sortable: true,
                halign: 'left',
                align: 'left'
            },
            {
                field: 'buyTime',
                title: '下单时间',
                sortable: true,
                halign: 'left',
                align: 'left'
            }
        ]
    });
}

// AJAX request function for select, add, update, delete etc.
// optID: 0=Load all the gpi data, 1=Search the orders of one group purchase,
//        2=Make a deal or update it, 3=Cancel the order, 4=Close the GPI,
//        5=Cancel the GPI.
// data: the request data array.
function ajaxRequest(optID, data) {
    // Set request data.
    // Get user name and token from local storage or cookie.
    let reqData = { 'optID':optID, 'bid':getCookie('bid'), 'fid':getCookie('fid'), 'tok':getCookie('token') };
    if (0 !== optID) {
        reqData['reqData'] = JSON.stringify(data);
    }
    
    // We query the data from the server via AJAX.
    $.ajax({
	url: '../php/doing-grp-records-handler.php',
	type: 'post',
	data: reqData,
	success: function(result) {
            // If token auth fialed or request header failed, we redirect to page sign-in.
            if (-6 === result.head.retCode || -3 === result.head.retCode) {
                $(location).attr('href', '../html/sign-in.html');
            }
            switch (optID) {
                case 0:
                    if (0 === result.head.retCode || -100 === result.head.retCode) {
                        createGpiTable(result.payload.gpiData);
                    } else if (-4 === result.head.retCode) {
                        $.alert('数据库链接失败，请联系客服！', '出错了！');
                    } else if (-101 === result.head.retCode) {
                        $.alert('数据载入失败，请稍后刷新重试！', '出错了！');
                    } else {
                        $.alert('请不要进行非法操作！', '出错了！');
                    }
                    break;
                case 1:
                    if (0 === result.head.retCode || -106 === result.head.retCode
                        || -107 === result.head.retCode) {
                        // Create GPI orders table.
                        createGpiOrdersTable(result.payload.ordData);
                        // Refresh the GPI data.
                        reloadGpiData();
                    } else if (-4 === result.head.retCode) {
                        $.alert('数据库链接失败，请联系客服！', '出错了！');
                    } else if (-2 === result.head.retCode || -7 === result.head.retCode) {
                        $.alert('请求参数不合法！', '出错了！');
                    } else if (-102 === result.head.retCode) {
                        // Refresh the GPI data.
                        reloadGpiData();
                        $.alert('团购已截止或取消！', '出错了！');
                    } else if (-103 === result.head.retCode) {
                        $.alert('团购编号无效！', '出错了！');
                    } else {
                        $.alert('请不要进行非法操作！', '出错了！');
                    }
                    break;
                case 2:
                    if (100 === result.head.retCode) {
                        // Refresh the GPI data.
                        reloadGpiData();
                        $.alert('下单成功。', '通知');
                    } else if (101 === result.head.retCode) {
                        // Refresh the GPI data.
                        reloadGpiData();
                        $.alert('您的订单已更新。', '通知');
                    } else if (-4 === result.head.retCode) {
                        $.alert('数据库链接失败，请联系客服！', '出错了！');
                    } else if (-2 === result.head.retCode || -7 === result.head.retCode) {
                        $.alert('请求参数不合法！', '出错了！');
                    } else if (-104 === result.head.retCode) {
                        $.alert('下单失败！', '出错了！');
                    } else if (-105 === result.head.retCode) {
                        $.alert('订单更新失败！', '出错了！');
                    } else if (-102 === result.head.retCode) {
                        // Refresh the GPI data.
                        reloadGpiData();
                        $.alert('团购已截止或取消，无法下单或修改！', '出错了！');
                    } else if (-103 === result.head.retCode) {
                        $.alert('团购编号无效！', '出错了！');
                    } else {
                        $.alert('请不要进行非法操作！', '出错了！');
                    }
                    break;
                case 3:
                    if (0 === result.head.retCode) {
                        // Refresh the GPI data.
                        reloadGpiData();
                        $.alert('您的订单已成功撤销。', '通知');
                    } else if (-4 === result.head.retCode) {
                        $.alert('数据库链接失败，请联系客服！', '出错了！');
                    } else if (-2 === result.head.retCode || -7 === result.head.retCode) {
                        $.alert('请求参数不合法！', '出错了！');
                    } else if (-102 === result.head.retCode) {
                        // Refresh the GPI data.
                        reloadGpiData();
                        $.alert('团购已截止或取消，无法执行撤单！', '出错了！');
                    } else if (-103 === result.head.retCode) {
                        $.alert('团购编号无效！', '出错了！');
                    } else if (-108 === result.head.retCode) {
                        $.alert('撤销订单失败！', '出错了！');
                    } else if (-109 === result.head.retCode) {
                        $.alert('您还没有下单！', '出错了！');
                    } else {
                        $.alert('请不要进行非法操作！', '出错了！');
                    }
                    break;
                case 4:
                    if (0 === result.head.retCode) {
                        // Refresh the GPI data.
                        reloadGpiData();
                        $.alert('您发布的团购已提前封单。', '通知');
                    } else if (-4 === result.head.retCode) {
                        $.alert('数据库链接失败，请联系客服！', '出错了！');
                    } else if (-2 === result.head.retCode || -7 === result.head.retCode) {
                        $.alert('请求参数不合法！', '出错了！');
                    } else if (-102 === result.head.retCode) {
                        // Refresh the GPI data.
                        reloadGpiData();
                        $.alert('团购已封单或撤团！', '出错了！');
                    } else if (-103 === result.head.retCode) {
                        $.alert('团购编号无效！', '出错了！');
                    } else if (-110 === result.head.retCode) {
                        $.alert('您不是这个团的团长，无权封单！', '出错了！');
                    } else if (-111 === result.head.retCode) {
                        $.alert('封单团购失败！', '出错了！');
                    } else {
                        $.alert('请不要进行非法操作！', '出错了！');
                    }
                    break;
                case 5:
                    if (0 === result.head.retCode) {
                        // Refresh the GPI data.
                        reloadGpiData();
                        $.alert('您发布的团购已撤团成功。', '通知');
                    } else if (-4 === result.head.retCode) {
                        $.alert('数据库链接失败，请联系客服！', '出错了！');
                    } else if (-2 === result.head.retCode || -7 === result.head.retCode) {
                        $.alert('请求参数不合法！', '出错了！');
                    } else if (-102 === result.head.retCode) {
                        // Refresh the GPI data.
                        reloadGpiData();
                        $.alert('团购已封单或撤团！', '出错了！');
                    } else if (-103 === result.head.retCode) {
                        $.alert('团购编号无效！', '出错了！');
                    } else if (-110 === result.head.retCode) {
                        $.alert('您不是这个团的团长，无权撤消！', '出错了！');
                    } else if (-112 === result.head.retCode) {
                        $.alert('撤团失败！', '出错了！');
                    } else {
                        $.alert('请不要进行非法操作！', '出错了！');
                    }
                    break;
                default:
                    console.log("Operation ID is wrong! optID:[" + optID + "].");                  
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

// Load the doing group purchase to this page.
function loadGpiData() {
    ajaxRequest(0, 0);
}

// Reload the doing group purchase to this page.
function reloadGpiData() {
    destroyTable(GPI_DOING_TABLE_ID);
    loadGpiData();
}

// Run the operations as below when loading the document.
$(function() {
    // Set copyright year.
    setCopyrightYear();
    
    // Load GpiData.
    loadGpiData();
    
    // Reload GPI data to this page.
    $('#'+GPI_DOING_RELOAD_BTN_ID).click(function() {
        reloadGpiData();
        $.alert('已录已更新！', '通知');
    }); 
    
    
});
