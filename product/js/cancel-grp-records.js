/*
 * File name:   cancel-grp-records.js
 * Desc:        Define functions used for page cancel group records.
 * Author:      Richard Wang <wanglei_gmgc@hotmail.com>
 * Create:      2022-04-15
 */

// Define the control ID of all.
let GPI_TABLE_TOOL_BAR_ID = 'toolbar';
let GPI_CANCELED_RELOAD_BTN_ID = 'reloadCanceledGpi';
let GPI_CANCELED_TABLE_ID = 'canceledGpiTable';
let SHOW_GPI_DETAIL_ID = 'showGpiDetail';

// The formatter of GPI detail info.
function descFormatter(value, row, index) {
    return [
        '<button class="btn btn-sm btn-info mr-2" id="'+SHOW_GPI_DETAIL_ID+'">',
        '查看</button>'
    ].join('');
}

// The detail viewer callback function.
function detailFormatter(index, row) {
    let html = '<table>';
    html += '<tr><td>团号：</td><td>' + row['gpiID'] +  '</td></tr>';
    html += '<tr><td>团名：</td><td>' + row['gpiName'] +  '</td></tr>';
    html += '<tr><td>价格：</td><td>' + row['gpiPrice'] + '</td></tr>';
    html += '<tr><td>截止时间：</td><td>' + row['gpiEndTime'] + '</td></tr>';
    html += '<tr><td>团长：</td><td>' + row['gpiPublisher'] + '</td></tr>';
    html += '<tr><td>封单时间：</td><td>' + row['gpiUpdTime'] + '</td></tr>';
    html += '</table>';
    return html;
}

// Handle the events for searching operations.
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
    }
};

// Define the gpi table columns for all users.
let gpiTblcolumns = {
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
            field: 'gpiPublisher',
            title: '团长',
            sortable: true,
            halign: 'left',
            align: 'left'
        },
        {
            field: 'gpiUpdTime',
            title: '撤团时间',
            sortable: true,
            halign: 'left',
            align: 'left'
        }
    ]
};

// Create a new gpi table.
function createGpiTable(arrTblData) {
    $('#'+GPI_CANCELED_TABLE_ID).bootstrapTable({
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
        columns:gpiTblcolumns.userGpiTblCols
    });
}

// AJAX request function just for select.
// optID: 0=Load all the canceled gpi data.
// data: the request data array.
function ajaxRequest(optID, data) {
    // Set request data.
    // Get user name and token from local storage or cookie.
    let reqData = { 'optID':optID, 'bid':getCookie('bid'), 'fid':getCookie('fid'), 'tok':getCookie('token') };
    
    // We query the data from the server via AJAX.
    $.ajax({
	url: '../php/cancel-grp-records-handler.php',
	type: 'post',
	data: reqData,
	success: function(result) {
            // If token auth fialed or request header failed, we redirect to page sign-in.
            if (-6 === result.head.retCode || -3 === result.head.retCode) {
                $(location).attr('href', '../html/sign-in.html');
            }
            switch (optID) {
                case 0:
                    if (0 === result.head.retCode || -130 === result.head.retCode) {
                        createGpiTable(result.payload.gpiData);
                    } else if (-4 === result.head.retCode) {
                        $.alert('数据库链接失败，请联系客服！', '出错了！');
                    } else if (-132 === result.head.retCode) {
                        $.alert('数据载入失败，请稍后刷新重试！', '出错了！');
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

// Load the canceled group purchase to this page.
function loadGpiData() {
    ajaxRequest(0, 0);
}

// Reload the canceled group purchase to this page.
function reloadGpiData() {
    destroyTable(GPI_CANCELED_TABLE_ID);
    loadGpiData();
}

// Run the operations as below when loading the document.
$(function() {
    // Set copyright year.
    setCopyrightYear();
    
    // Load GpiData.
    loadGpiData();
    
    // Reload GPI data to this page.
    $('#'+GPI_CANCELED_RELOAD_BTN_ID).click(function() {
        reloadGpiData();
        $.alert('已录已更新！', '通知');
    });
});
