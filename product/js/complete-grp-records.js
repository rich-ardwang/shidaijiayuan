/*
 * File name:   complete-grp-records.js
 * Desc:        Define functions used for page complete group records.
 * Author:      Richard Wang <wanglei_gmgc@hotmail.com>
 * Create:      2022-04-14
 */

// Define the control ID of all.
let GPI_TABLE_TOOL_BAR_ID = 'toolbar';
let GPI_CLOSED_RELOAD_BTN_ID = 'reloadClosedGpi';
let GPI_CLOSED_TABLE_ID = 'closedGpiTable';
let GPI_ORDERS_TABLE_ID = 'gpiOrdersTable';
let GPI_STAT_ORDERS_TABLE_ID = 'statOrdersTable';
let STAT_GPI_ORDERS_BTN_ID = 'statGpiOrders';
let SHOW_GPI_DETAIL_ID = 'showGpiDetail';
let SHOW_GPI_ALL_ORDERS = 'showGpiOrders';

// We add the operate icons for ervey line of the table.
function operateFormatter(value, row, index) {
    let operateHtml = '<button class="btn btn-sm btn-success mr-1" id="'+STAT_GPI_ORDERS_BTN_ID+'">';
        operateHtml += '报表统计</button>';
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
    html += '<tr><td>价格：</td><td>' + row['gpiPrice'] + '</td></tr>';
    html += '<tr><td>截止时间：</td><td>' + row['gpiEndTime'] + '</td></tr>';
    html += '<tr><td>订单数：</td><td>' + row['gpiOrdersCnt'] + '</td></tr>';
    html += '<tr><td>团长：</td><td>' + row['gpiPublisher'] + '</td></tr>';
    html += '<tr><td>封单时间：</td><td>' + row['gpiUpdTime'] + '</td></tr>';
    html += '</table>';
    return html;
}

// Handle the events for searching, ordering and retrieving operations.
window.operateEvents = {
    'click #showGpiDetail': function (e, value, row, index) {
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
    'click #statGpiOrders': function (e, value, row, index) {
        $.confirm({
            // Let the dialog box become fluid.
            containerFluid:'true',
            icon: 'mdi mdi-book-open-page-variant mdi-24px text-success',
            title: '订单统计',
            titleClass: 'text-success',
            type: 'green',
            theme: 'dark',
            draggable: 'true',
            content:
                '<div class="row">' +
                '<div class="col-md">' +
                '<table id="'+GPI_STAT_ORDERS_TABLE_ID+'" class="table-dark table-striped" data-mobile-responsive="true" data-show-export="true"></table>' +
                '</div>' +                                   
                '</div>',
            buttons: {
                cancel: {
                    text: '关闭',
                    btnClass: 'btn-green'
                }
            },
            onOpenBefore: function () {
                // Create request data and send to server.
                reqData = { 'gpiID':row['gpiID'] };
                ajaxRequest(2, reqData);
                return;
            }
        });
    }
};

// Define the gpi table columns for admin and common users.
let gpiTblcolumns = {
    'adminGpiTblCols':[
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
            field: 'gpiUpdTime',
            title: '封单时间',
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
    ],
    'userGpiTblCols':[
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
            field: 'gpiUpdTime',
            title: '封单时间',
            sortable: true,
            halign: 'left',
            align: 'left'
        }
    ]
};

// Create a new gpi table.
function createGpiTable(arrTblData) {
    let tblCols = gpiTblcolumns.adminGpiTblCols;
    if ('2' === getCookie('type')) {
        tblCols = gpiTblcolumns.userGpiTblCols;
    }
    $('#'+GPI_CLOSED_TABLE_ID).bootstrapTable({
        locale:'zh-CN',             // Table language is Chinese.
        sortReset:true,            // There are 3 states when sort field: increment, decrement and reset.
        cache:false,               // Don't use browser cache.
        toolbar:'#'+GPI_TABLE_TOOL_BAR_ID,     // Set toolbar id.
        search:false,               // Show the search text input.
        showRefresh:false,           // Show the refresh button.
        showToggle:true,           // Show the button to control toggle the card view and the table view.
        showFullscreen:false,        // Show the full screen button.
        showColumns:false,           // Show the filter button of columns.
        showColumnsToggleAll:false,  // Show the toggle all checkbox in above button.
        clickToSelect:false,         // True:When you click the row you will select radio box or check box automatically.
        detailView:true,            // Show the icon for viewing the detail.
        detailFormatter:detailFormatter,    // The callback function used for showing detail.
        minimumCountColumns:1,          // When you filter the columns, you must left the minimum count of the columns. 
        showPaginationSwitch:false,      // Show the button to control hide pagination or not.
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
        columns:tblCols
    });
}

// Define the order table columns for common orders and stat orders.
let ordTblcolumns = {
    'commonOrdTblCols': [
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
    ],
    'statOrdTblCols': [
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
            field: 'gpiPrice',
            title: '单价(元)',
            sortable: true,
            halign: 'left',
            align: 'left'
        },
        {
            field: 'ordID',
            title: '单号',
            sortable: true,
            halign: 'left',
            align: 'left'
        },
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
            title: '付款(元)',
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
};

// Create a new orders table.
function createGpiOrdersTable(arrTblData, tblID, tblType) {
    let tblCols = ordTblcolumns.commonOrdTblCols;
    if ('stat' === tblType) {
        if ('2' !== getCookie('token')) {
            tblCols = ordTblcolumns.statOrdTblCols;
        }
    }
    $('#'+tblID).bootstrapTable({
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
        columns:tblCols
    });
}

// AJAX request function for select, add, update, delete etc.
// optID: 0=Load all the gpi data, 1=Search the orders of one group purchase,
//        2=Statistics analysis for one gpi.
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
	url: '../php/complete-grp-records-handler.php',
	type: 'post',
	data: reqData,
	success: function(result) {
            // If token auth fialed or request header failed, we redirect to page sign-in.
            if (-6 === result.head.retCode || -3 === result.head.retCode) {
                $(location).attr('href', '../html/sign-in.html');
            }
            switch (optID) {
                case 0:
                    if (0 === result.head.retCode || -120 === result.head.retCode) {
                        createGpiTable(result.payload.gpiData);
                    } else if (-4 === result.head.retCode) {
                        $.alert('数据库链接失败，请联系客服！', '出错了！');
                    } else if (-121 === result.head.retCode) {
                        $.alert('数据载入失败，请稍后刷新重试！', '出错了！');
                    } else {
                        $.alert('请不要进行非法操作！', '出错了！');
                    }
                    break;
                case 1:
                    if (0 === result.head.retCode || -106 === result.head.retCode
                        || -107 === result.head.retCode) {
                        // Create GPI orders table.
                        createGpiOrdersTable(result.payload.ordData, GPI_ORDERS_TABLE_ID, '');
                    } else if (-4 === result.head.retCode) {
                        $.alert('数据库链接失败，请联系客服！', '出错了！');
                    } else if (-2 === result.head.retCode || -7 === result.head.retCode) {
                        $.alert('请求参数不合法！', '出错了！');
                    } else if (-122 === result.head.retCode) {
                        $.alert('团购编号无效！', '出错了！');
                    } else {
                        $.alert('请不要进行非法操作！', '出错了！');
                    }
                    break;
                case 2:
                    if (0 === result.head.retCode || -106 === result.head.retCode
                        || -107 === result.head.retCode) {
                        // Create GPI stat orders table.
                        createGpiOrdersTable(result.payload.ordData, GPI_STAT_ORDERS_TABLE_ID, 'stat');
                    } else if (-4 === result.head.retCode) {
                        $.alert('数据库链接失败，请联系客服！', '出错了！');
                    } else if (-2 === result.head.retCode || -7 === result.head.retCode) {
                        $.alert('请求参数不合法！', '出错了！');
                    } else if (-110 === result.head.retCode) {
                        $.alert('您无权进行统计操作！', '出错了！');
                    } else if (-122 === result.head.retCode) {
                        $.alert('团购编号无效！', '出错了！');
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
    destroyTable(GPI_CLOSED_TABLE_ID);
    loadGpiData();
}

// Run the operations as below when loading the document.
$(function() {
    // Set copyright year.
    setCopyrightYear();
    
    // Load GpiData.
    loadGpiData();
    
    // Reload GPI data to this page.
    $('#'+GPI_CLOSED_RELOAD_BTN_ID).click(function() {
        reloadGpiData();
        $.alert('已录已更新！', '通知');
    });
});
