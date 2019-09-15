<?php
    /**
     * Wow Master Template
     *
     * @var \Wow\Template\View      $this
     * @var \App\Models\LogonPerson $logonPerson
     */
    $logonPerson = $this->get("logonPerson");
    $uyelik      = $logonPerson->member;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <?php $this->section("section_head"); ?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap-paper.min.css">
    <link type="text/css" rel="stylesheet" href="/assets/lightgallery/dist/css/lightgallery.min.css"/>
    <link rel="stylesheet" href="/assets/scripts/fancybox/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen"/>
    <link rel="stylesheet" href="/assets/style/font-awesome.min.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="/assets/style/paper.css?v=v3.1.0">
    <link rel="stylesheet" href="/assets/nprogress/nprogress.css">
    <link rel="shortcut icon" href="/assets/images/favicon.png" type="image/x-icon"/>
    <meta name="google-site-verification" content="u1DkZYeRYUH11kBL2eVX0Pijh_4POa4SboP_EvMLq_4"/>
    <title><?php if($this->has('title')) {
            echo $this->get('title') . " | ";
        }
            echo Wow::get("ayar/site_title"); ?></title>
    <?php if($this->has('description')) { ?>
        <meta name="description" content="<?php echo $this->get('description'); ?>"><?php } ?>
    <?php if($this->has('keywords')) { ?>
        <meta name="keywords" content="<?php echo $this->get('keywords'); ?>"><?php } ?>
    <?php $this->show(); ?>
</head>
<body>
<header>
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header pull-left">
                <a class="navbar-brand" href="<?php echo $logonPerson->isLoggedIn() ? '/tools' : '/'; ?>"><img alt="instagram takip" src="/assets/images/logo.png"/></a>
            </div>
            <div class="navbar-header pull-right">
                <ul class="nav navbar-nav pull-left">
                    <?php if(!$logonPerson->isLoggedIn()) { ?>
                        <li><p class="navbar-btn">
                                <a id="loginAsUser" class="btn btn-primary" href="<?php echo Wow::get("project/memberLoginPrefix"); ?>"><i class="fa fa-sign-in"></i> GİRİŞ</a>
                            </p></li>
                    <?php } else { ?>
                        <li class="dropdown pull-right">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="text-transform: none;">
                                <img src="<?php echo str_replace("http:", "https:", $uyelik["profilFoto"]); ?>" alt="<?php $uyelik["kullaniciAdi"]; ?>" style="max-height:30px;"> <?php echo (strlen($uyelik["fullName"]) > 10) ? substr($uyelik["fullName"], 0, 5) . ".." : $uyelik["fullName"]; ?>
                                <span class="caret"></span></a>
                            <ul role="menu" class="dropdown-menu dropdown-light fadeInUpShort">
                                <?php if($logonPerson->member->isBayi == 1) { ?>
                                    <li><a href="/bayi-tx" class="menu-toggler"> Bayi Paneli </a></li>
                                    <li class="divider"></li>
                                <?php } ?>
                                <li>
                                    <a href="/user/<?php echo $logonPerson->member->instaID; ?>" class="menu-toggler"> Profilim </a>
                                </li>
                                <li><a href="/account/settings" class="menu-toggler"> Hesap Ayarları </a></li>
                                <li class="divider"></li>
                                <li><a href="/account/logout" class="menu-toggler"> Çıkış Yap </a></li>
                            </ul>
                        </li>
                        <li class="pull-right">
                            <a href="/messages" title="Mesaj Kutum" style="text-transform: none;">
                                <img src="/assets/images/direct_icon.png" style="max-height:30px;">
                                <span class="badge<?php echo isset($_SESSION["NonReadThreadCount"]) && intval($_SESSION["NonReadThreadCount"]) > 0 ? '' : ' hidden'; ?>" id="nonReadThreadCount"><?php echo isset($_SESSION["NonReadThreadCount"]) ? $_SESSION["NonReadThreadCount"] : 0; ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>


                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="navbar-collapse collapse navbar-left">
                <ul class="nav navbar-nav">
                    <li<?php echo $this->route->params["controller"] == "Tools" ? ' class="active"' : ''; ?>>
                        <a href="/tools"><?php echo $this->translate("instagram/menu/tools"); ?></a></li>
                    <li<?php echo $this->route->params["controller"] == "Packages" ? ' class="active"' : ''; ?>>
                        <a href="/packages"><?php echo $this->translate("instagram/menu/packages"); ?></a></li>
                    <?php if(!$logonPerson->isLoggedIn()) { ?>
                    <li<?php echo $this->route->params["controller"] == "Blog" ? ' class="active"' : ''; ?>>
                            <a href="/blog">Blog</a></li><?php } ?>
                </ul>
            </div>
            <?php if($logonPerson->isLoggedIn()) { ?>
                <div class="navbar-collapse collapse navbar-right">
                    <?php if($logonPerson->isLoggedIn()) { ?>
                        <div class="pull-left">
                            <form class="navbar-form" role="search" action="/account/search">
                                <input type="hidden" name="tab" value="<?php echo $this->route->params["controller"] == "Account" && ($this->route->params["action"] == "Search" || $this->route->params["action"] == "Tag" || $this->route->params["action"] == "Location") ? $this->get("tab") : ""; ?>">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Ara" name="q" value="<?php echo $this->route->params["controller"] == "Account" && ($this->route->params["action"] == "Search" || $this->route->params["action"] == "Tag" || $this->route->params["action"] == "Location") ? $this->get("q") : ""; ?>" required>
                                    <div class="input-group-btn">
                                        <button class="btn btn-default" type="submit">
                                            <i class="glyphicon glyphicon-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </nav>
</header>
<?php
    if($logonPerson->isLoggedIn()) {
        $this->renderView("shared/account-bar");
    }
    if($this->has("navigation")) {
        $this->renderView("shared/navigation", $this->get("navigation"));
    }
    if(count($this->get('notifications')) > 0) {
        $this->renderView("shared/notifications", $this->get('notifications'));
    }
    $this->renderBody();
?>
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <img class="img-responsive" src="/assets/images/logo.png"/>
                <p>
                    <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http') . "://" . $_SERVER['SERVER_NAME']; ?>">instagram beğeni ve takipçi sitesi</a>
                </p>
                <ul class="list-unstyled">
                    <li><a href="/tools">Araçlar</a></li>
                    <li><a href="/packages">Paketler</a></li>
                    <li><a href="/blog">Blog</a></li>
                </ul>
                <p>Copyright &copy; <?php echo date("Y"); ?>
                    <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http') . "://" . $_SERVER['SERVER_NAME']; ?>"><?php echo $_SERVER['SERVER_NAME']; ?></a>
                </p>
            </div>
            <div class="col-md-3">
                <h5>Nasıl Çalışır</h5>
                <p>Kredileriniz ile dilediğiniz paylaşımınıza beğeni ve profilinize takipçi gönderebilirsiniz.
                    <a href="/packages">Paketler</a> bölümünden uygun fiyatlar ile bir paket satın alabilirsiniz.</p>
            </div>
            <div class="col-md-3">
                <h5>Kimler Kullanabilir</h5>
                <p>Instagram üyeliği olan herkes sistemi kullanabilir. Instagram hesabınızla giriş yapın ve hemen kullanmaya başlayın. Kullanım ücretsizdir. Kredi satın almadıkça hiçbir ücret ödemezsiniz.</p>
            </div>
            <div class="col-md-3">
                <h5>Bize Ulaşın</h5>
                <p>Her türlü soru ve görüşleriniz için
                    <a href="#modalContact" data-toggle="modal">İletişim</a> kanallarımızdan bizimle irtibat kurabilirsiniz.
                </p>
            </div>
        </div>
    </div>
</footer>
<?php $this->section("section_modals");
    if($logonPerson->isLoggedIn()) { ?>
        <div class="modal" id="modalEditMedia" style="z-index: 1051;">
            <div class="modal-dialog">
                <div class="modal-content" id="modalEditMediaInner">
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalNewMessage">
            <div class="modal-dialog">
                <div class="modal-content" id="modalNewMessageInner">
                </div>
            </div>
        </div>
    <?php } ?>
<div class="modal fade" id="modalContact" style="z-index: 1051;">
    <div class="modal-dialog">
        <div class="modal-content" id="infoModal">
            <div class="modal-body">
                <h3>İletişim Bilgileri</h3>
                <p>Kredi satın almak için aşağıda bulunan iletişim kanallarından bize ulaşabilirsiniz.</p>
                <?php if(Wow::get("ayar/contact_whatsapp") != "") { ?>
                    <p><span style="color:#43d854"><i class="fa fa-whatsapp" aria-hidden="true"></i> Whatsapp </span>:
                        <b><?php echo Wow::get("ayar/contact_whatsapp"); ?></b></p>
                <?php } ?>
                <?php if(Wow::has("ayar/contact_skype") != "") { ?>
                    <p><span style="color:#00aff0"><i class="fa fa-skype" aria-hidden="true"></i> Skype </span> :
                        <b><?php echo Wow::get("ayar/contact_skype"); ?></b></p>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php $this->show(); ?>
<?php $this->section('section_scripts'); ?>
<script src="/assets/jquery/2.2.4/jquery.min.js"></script>
<script src="/assets/bootstrap/js/bootstrap.min.js"></script>
<script src="/assets/scripts/fancybox/source/jquery.fancybox.pack.js"></script>
<script src="/assets/lightgallery/dist/js/lightgallery.min.js"></script>
<script src="/assets/lightgallery/dist/js/lg-video.min.js"></script>
<script src="/assets/lazyload/jquery.lazyload.min.js"></script>
<script src="/assets/nprogress/nprogress.js"></script>
<script src="/assets/core/core.js?v=3.1.10"></script>
<?php $this->show(); ?>
<script type="text/javascript">
    <?php if(Wow::has("ayar/googleanalyticscode") != "") { ?>
    (function(i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function() {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src   = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
    ga('create', '<?php echo Wow::get("ayar/googleanalyticscode"); ?>', 'auto');
    ga('send', 'pageview');
    <?php } ?>
    initProject();
</script>
<style>
    /* The Modal (background) */
    .modal {
        display          : none; /* Hidden by default */
        position         : fixed; /* Stay in place */
        z-index          : 1; /* Sit on top */
        padding-top      : 100px; /* Location of the box */
        left             : 0;
        top              : 0;
        width            : 100%; /* Full width */
        height           : 100%; /* Full height */
        overflow         : auto; /* Enable scroll if needed */
        background-color : rgb(0, 0, 0); /* Fallback color */
        background-color : rgba(0, 0, 0, 0.4); /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content {
        background-color : #fefefe;
        margin           : auto;
        padding          : 20px;
        border           : 1px solid #888;
        width            : 80%;
    }

    /* The Close Button */
    .close {
        color       : #aaaaaa;
        float       : right;
        font-size   : 28px;
        font-weight : bold;
    }

    .close:hover,
    .close:focus {
        color           : #000;
        text-decoration : none;
        cursor          : pointer;
    }

    .modal-open .modal {
        overflow-x : hidden;
        overflow-y : auto;
        z-index    : 9999;
    }
</style>
</body>
</html>