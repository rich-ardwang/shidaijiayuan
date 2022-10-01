<?php
/*
 * cancel-grp-records.php
 *
 * -handle the process of canceled group records.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-15
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once ("../dbfunc/db-conn.php");
require_once ("../dbfunc/user-funcs.php");
require_once ("../php/common.php");

$login_show = false;
$bid = $_COOKIE["bid"];
$fid = $_COOKIE["fid"];
$token = $_COOKIE["token"];
if (isset($bid) && !empty($bid) && isset($fid) && !empty($fid)
        && isset($token) && !empty($token)) {
    // Connect to database.
    $conn = db_connect();
    if ($conn) {
        // Get user id and type by building code and family code.
        $fields = [ usrID, usrPwd, usrType ];
        $arr_user_info = get_user_info_by_bid_fid($conn, $fields, $bid, $fid);
        if (is_array($arr_user_info)) {
            // Check token.
            if (check_token_direct($arr_user_info[0]['usrID'], $arr_user_info[0]['usrPwd'], $token)) {
                $user_type = $arr_user_info[0]['usrType'];
                $login_show = true;
            }
        }
    }
}
if ($login_show) {                  
?>

<!doctype html>
<html lang="zh-CN">
    <head>
        <!-- Required meta tags -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="author" content="Richard Wang [wanglei_gmgc@hotmail.com]">
        <meta name="description" content="Create: 2022-04-15">

        <!-- Import CSS files -->
        <link href="../vendor/fonts.googleapis.com/fonts.css" rel="stylesheet" />
        <link href="../vendor/fontawesome/css/all.css" rel="stylesheet" />
        <link href="../vendor/materialdesignicons/css/materialdesignicons.min.css" media="all" rel="stylesheet" type="text/css" />
        <link href="../vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
        <link href="../vendor/jquery-confirm/dist/jquery-confirm.min.css" rel="stylesheet" />
        <link href="../vendor/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" />
        <link href="../css/common.css" rel="stylesheet">
        <link href="../css/header.css" rel="stylesheet">
        <link href="../css/footer.css" rel="stylesheet">
        <title>已取消的团购</title>
    </head>

    <body class="bg-dark">
        <div class="container-fluid">
            <!-- menu content-->
            <div class="row">
                <div class="col-md">
                    <nav id="header" class="navbar navbar-expand-md">
                        <a href="../php/index.php" class="text-info mr-auto"><h4 class="text-info mr-auto" >绿地时代嘉苑团购网</h4></a>
                        <ul class="nav justify-content-end">
                            <li class="nav-item">
                                <a class="nav-link text-info" href="../php/index.php">首页</a>
                            </li>
                            
                            <?php
                            // Only admin can look at it.
                            if ("0" === $user_type || "1" === $user_type) {
                            ?>
                                <li class="nav-item">
                                    <a class="nav-link text-info" href="./add-group-purchase.php">发布团购</a>
                                </li>
                            <?php
                            }
                            ?>
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-info" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">团购信息</a>
                                <div class="dropdown-menu bg-secondary">
                                    <a class="dropdown-item text-primary" href="./doing-grp-records.php"><i class="mdi mdi-home-currency-usd text-primary pr-2"></i>进行中</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-white" href="./complete-grp-records.php"><i class="mdi mdi-pencil-box-multiple text-white pr-2"></i>已封单</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="./cancel-grp-records.php"><i class="mdi mdi-bookshelf text-danger pr-2"></i>已取消</a>

                                </div>
                            </li>
                         
                            <li class="nav-item">
                                <a class="nav-link text-info" href="./history-orders.php">历史订单</a>
                            </li>
                            
                            <li class="nav-item pl-4 pr-2">
                                <a href="./sign-out.html" class="btn btn-sm btn-danger my-sm-1" role="button" aria-pressed="true">
                                    <i class="mdi mdi-run-fast font-size-20 pr-2"></i>退出
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- header content -->
            <div class="row">
                <div class="col-md">
                    <div class="jumbotron jumbotron-fluid bg-danger">
                        <h1 class="display-5 text-center text-white">已取消的团购</h1>
                    </div>
                </div>
            </div>

            <!-- standard body -->
            <div class="row">
                <div class="col-md">
                    <div id="toolbar">
                        <button id="reloadCanceledGpi" class="btn btn-primary">立刻刷新</button>
                    </div>
                    <table id="canceledGpiTable" class="table-dark table-striped" data-mobile-responsive="true"></table>
                </div>                                       
            </div>
           
        </div>

        <!-- footer content-->
        <div id="footer" class="container-fluid mt-4">
            <div class="row">
                <div id="footerCopyright" class="col-md text-center">
                    <nav class="navbar-text">
                        <div class="text-info">Copyright © 2022 - <span id="copy-year"></span> 王雷先生个人设计开发 版权所有</div>
                    </nav>
                </div>
            </div>
        </div>

        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="../vendor/jquery/jquery.min.js"></script>
        <script src="../vendor/bootstrap/js/popper.min.js"></script>
        <script src="../vendor/bootstrap/js/bootstrap.js"></script>
        <script src="../vendor/jquery-confirm/dist/jquery-confirm.min.js"></script>
        <script src="../vendor/bootstrap-table/dist/bootstrap-table.min.js"></script>
        <script src="../vendor/bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile.min.js"></script>
        <script src="../vendor/bootstrap-table/dist/bootstrap-table-locale-all.min.js"></script>
        <script src="../js/common.js"></script>
        <script src="../js/cancel-grp-records.js"></script>
    </body>
</html>

<?php
} else {
    echo "<script>location.href='../php/index.php';</script>";
    exit;
}
?>
