<?php
/*
 * index.php
 *
 * -The home page of group purchase dealing.
 *
 * @author      Richard Wang <wanglei_gmgc@hotmail.com>
 * @date        2022-04-08
 * @version     1.0
 * @copyright   Copyright © 2022-2022 Richard Wang
 */

require_once ("../dbfunc/db-conn.php");
require_once ("../dbfunc/user-funcs.php");
require_once ("common.php");
?>

<!doctype html>
<html lang="zh-CN">
    <head>
        <!-- Required meta tags -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="author" content="Richard Wang [wanglei_gmgc@hotmail.com]">
        <meta name="description" content="Create: 2022-04-08">

        <!-- Import CSS files -->
        <link href="../vendor/fonts.googleapis.com/fonts.css" rel="stylesheet" />
        <link href="../vendor/materialdesignicons/css/materialdesignicons.min.css" media="all" rel="stylesheet" type="text/css" />
        <link href="../vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
        <link href="../vendor/jquery-confirm/dist/jquery-confirm.min.css" rel="stylesheet" />
        <link href="../css/common.css" rel="stylesheet">
        <link href="../css/header.css" rel="stylesheet">
        <link href="../css/footer.css" rel="stylesheet">

        <title>主页</title>
    </head>

    <body class="bg-dark">
        <div class="container-fluid">
            <!-- menu content-->
            <div class="row">
                <div class="col-md">
                    <nav id="header" class="navbar navbar-expand-md">
                        <h4 class="text-info mr-auto">绿地时代嘉苑团购网</h4>
                        <ul class="nav justify-content-end">
                            <li class="nav-item ml-1">
                                <a class="nav-link text-info" href="index.php">首页</a>
                            </li>
                            <?php
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
                                                $welcome_text = $bid . '号楼' . $fid . '，侬好！';
                                                $super_text = "您是超管，拥有至高无上的权力！";
                                                $admin_text = "您是团长，可发布团购信息，封团、撤团、快速核查，一键生成CSV、Excel等报表。";
                                                $member_text = "您是吃瓜群众，可浏览所有团购信息并下单，封团前可随时修改和撤单、查看历史订单等。";
                                                $user_type = $arr_user_info[0]['usrType'];
                                                $login_show = true;
                                            }
                                        }
                                    }
                                }
                                if ($login_show) {
                            ?>
                                <?php
                                    if ("0" === $user_type || "1" === $user_type) {
                                ?>
                                        <li class="nav-item ml-1">
                                            <a class="nav-link text-info" href="../html/add-group-purchase.php">发布团购</a>
                                        </li>
                                <?php
                                    }
                                ?>
                                    <li class="nav-item dropdown ml-1">
                                        <a class="nav-link dropdown-toggle text-info" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">团购信息</a>
                                        <div class="dropdown-menu bg-secondary">
                                            <a class="dropdown-item text-primary" href="../html/doing-grp-records.php"><i class="mdi mdi-home-currency-usd text-primary pr-2"></i>进行中</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-white" href="../html/complete-grp-records.php"><i class="mdi mdi-pencil-box-multiple text-white pr-2"></i>已封单</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="../html/cancel-grp-records.php"><i class="mdi mdi-bookshelf text-danger pr-2"></i>已取消</a>
                                        </div>
                                    </li>
                                    <li class="nav-item ml-1">
                                        <a class="nav-link text-info" href="../html/history-orders.php">历史订单</a>
                                    </li>                                  
                                    <li class="nav-item pl-4 pr-2">
                                        <a href="../html/sign-out.html" class="btn btn-sm btn-danger my-sm-1" role="button" aria-pressed="true">
                                            <i class="mdi mdi-run-fast font-size-20 pr-2"></i>退出
                                        </a>
                                    </li>
                            <?php
                                } else {
                            ?>   
                                <li class="nav-item pl-5 pr-2">
                                    <a href="../html/sign-in.html" class="btn btn-sm btn-info my-sm-1" role="button" aria-pressed="true">
                                        <i class="mdi mdi-login font-size-20 pr-2"></i>登录
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="../html/register.html" class="btn btn-sm btn-success my-sm-1" role="button" aria-pressed="true">
                                        <i class="mdi mdi-account-supervisor-circle font-size-20 pr-2"></i>注册
                                    </a>
                                </li>
                            <?php
                                }
                            ?>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- header content -->
            <div class="row">
                <div class="col-md">
                    <div class="jumbotron jumbotron-fluid bg-info">
                        <h1 class="display-5 text-center text-white">欢迎光临时代嘉苑团购网！</h1>
                    </div>
                    <?php
                        if ($login_show) {
                    ?>
                            <div class="jumbotron jumbotron-fluid bg-dark">
                                <h2 class="display-5 text-center text-info"><?php echo $welcome_text ?></h2>
                                <?php
                                    if ("1" === $user_type) {
                                ?>
                                        <h4 class="display-5 text-center text-primary"><?php echo $admin_text ?></h4>
                                <?php
                                    } else if ("2" === $user_type) {
                                ?>
                                        <h4 class="display-5 text-center text-primary"><?php echo $member_text ?></h4>
                                <?php
                                    } else if ("0" === $user_type) {
                                ?>
                                        <h4 class="display-5 text-center text-primary"><?php echo $super_text ?></h4>
                                <?php
                                    } else {}
                                ?>
                            </div>
                    <?php
                        } else {
                    ?>
                            <div class="row">
                                <div class="col-md">
                                    <div id="nvPics" class="carousel slide" data-ride="carousel">
                                        <ol class="carousel-indicators">
                                            <li data-target="#nvPics" data-slide-to="0" class="active"></li>
                                            <li data-target="#nvPics" data-slide-to="1"></li>
                                            <li data-target="#nvPics" data-slide-to="2"></li>
                                            <li data-target="#nvPics" data-slide-to="3"></li>
                                        </ol>
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="../img/timg1.jpg" class="d-block w-100" alt="第一张">
                                                <div class="carousel-caption d-none d-md-block">
                                                </div>
                                            </div>
                                            <div class="carousel-item">
                                                <img src="../img/timg2.jpg" class="d-block w-100" alt="第二张">
                                                <div class="carousel-caption d-none d-md-block">
                                                </div>
                                            </div>
                                            <div class="carousel-item">
                                                <img src="../img/timg3.jpg" class="d-block w-100" alt="第三张">
                                                <div class="carousel-caption d-none d-md-block">
                                                </div>
                                            </div>
                                            <div class="carousel-item">
                                                <img src="../img/timg4.jpg" class="d-block w-100" alt="第四张">
                                                <div class="carousel-caption d-none d-md-block">
                                                </div>
                                            </div>
                                        </div>
                                        <a class="carousel-control-prev" href="#nvPics" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">上页</span>
                                        </a>
                                        <a class="carousel-control-next" href="#nvPics" role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">下页</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    ?>
                </div>
            </div>

            <!-- standard body -->
           
        </div>

        <!-- footer content-->
        <div id="footer" class="container-fluid fixed-bottom">
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
        <script src="../vendor/bootstrap/js/bootstrap.js"></script>
        <script src="../vendor/jquery-confirm/dist/jquery-confirm.min.js"></script>
        <script src="../vendor/sleek/plugins/slimscrollbar/jquery.slimscroll.min.js"></script>
        <script src="../js/common.js"></script>
        <script>setCopyrightYear();</script>
    </body>
</html>
