<?php
/*
 * add-group-purchase.php
 *
 * -handle the process of publishing group purchase.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-12
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
if ($login_show && ("0" === $user_type || "1" === $user_type)) {                     
?>

<!doctype html>
<html lang="zh-CN">
    <head>
        <!-- Required meta tags -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="author" content="Richard Wang [wanglei_gmgc@hotmail.com]">
        <meta name="description" content="Create: 2022-04-11">

        <!-- Import CSS files -->
        <link href="../vendor/fonts.googleapis.com/fonts.css" rel="stylesheet" />
        <link href="../vendor/fontawesome/css/all.css" rel="stylesheet" />
        <link href="../vendor/materialdesignicons/css/materialdesignicons.min.css" media="all" rel="stylesheet" type="text/css" />
        <link href="../vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
        <link href="../vendor/bootstrap-datetimepicker/dist/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
        <link href="../vendor/jquery-confirm/dist/jquery-confirm.min.css" rel="stylesheet" />
        <link href="../css/common.css" rel="stylesheet">
        <link href="../css/header.css" rel="stylesheet">
        <link href="../css/footer.css" rel="stylesheet">

        <title>发布团购信息</title>
    </head>

    <body class="bg-dark">
        <div class="container-fluid">
            <!-- menu content-->
            <div class="row">
                <div class="col">
                    <nav id="header" class="navbar navbar-expand-md">
                        <a href="../php/index.php" class="text-info mr-auto"><h4 class="text-info mr-auto" >绿地时代嘉苑团购网</h4></a>
                        <ul class="nav justify-content-end">
                            <li class="nav-item">
                                <a class="nav-link text-info" href="../php/index.php">首页</a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link text-info" href="./add-group-purchase.php">发布团购</a>
                            </li>
                            
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
                <div class="col">
                    <div class="jumbotron jumbotron-fluid bg-success">
                        <h1 class="display-5 text-center text-white">发布团购信息</h1>
                    </div>
                    <form>
                        <div class="form-row">
                            <div class="form-group col-md">
                                <label class="text-primary" for="gpiName"><span class="text-danger">(*) </span>团购标题：</label>
                                <input type="text" class="form-control-sm" size="20" maxlength="20" id="gpiName" placeholder="请输入团购标题">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md">
                                <label class="text-primary" for="gpiPrice"><span class="text-danger">(*) </span>定价：</label>
                                <input type="text" class="gpi-price-cls form-control-sm" size="8" maxlength="8" id="gpiPrice" value="" placeholder="0.00">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md">
                                <label class="text-primary" for="gpiEndTime"><span class="text-danger">(*) </span>截止时间：</label>
                                <input class="text-primary" type="text" size="18" id="gpiEndTime" placeholder="截止时间">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md">
                                <label class="text-primary" for="gpiDetail">描述：</label>
                                <textarea id="gpiDetail" rows="2" cols="35" maxlength="255"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md">
                                <label for="detailImgFile" class="text-primary">截图：</label>
                                <input type="file" class="text-white" id="detailImgFile" accept="image/jpeg,image/png,image/tiff">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md">
                                <button id="pubGpiInfo" type="button" class="btn btn-primary btn-lg btn-block">发 布</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- standard body -->
           
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
        <script src="../vendor/jquery-confirm/dist/jquery-confirm.min.js"></script>
        <script src="../vendor/bootstrap/js/bootstrap.js"></script>
        <script src="../vendor/bootstrap-datetimepicker/dist/js/locales/moment-with-locales.min.js"></script>
        <script src="../vendor/bootstrap-datetimepicker/dist/js/bootstrap-datetimepicker.min.js"></script>
        <script src="../vendor/accounting.js/dist/js/accounting.min.js"></script>
        <script src="../vendor/math.js/dist/math.js"></script>
        <script src="../js/common.js"></script>
        <script src="../js/add-group-purchase.js"></script>
    </body>
</html>

<?php
} else {
    echo "<script>location.href='../php/index.php';</script>";
    exit;
}
?>
