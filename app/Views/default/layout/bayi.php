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
    <link rel="stylesheet" href="/assets/style/font-awesome.min.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="/assets/style/paper.css?v=2.1">
    <link rel="shortcut icon" href="/assets/images/favicon.png" type="image/x-icon"/>
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
                <a class="navbar-brand" href="/bayi"><img alt="instagram takip" src="/assets/images/logo.png"/></a>
            </div>
            <div class="navbar-header pull-right">
                <?php if($logonPerson->isLoggedIn()) { ?>
                    <ul class="nav navbar-nav pull-left">
                        <li class="dropdown pull-right">
                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="text-transform: none;">
                                <img src="/assets/images/avatar.jpg" style="max-height:30px;"> <?php echo (strlen($uyelik["username"]) > 10) ? substr($uyelik["username"], 0, 5) . ".." : $uyelik["username"]; ?>
                                <span class="caret"></span></a>
                            <ul role="menu" class="dropdown-menu dropdown-light fadeInUpShort">
                                <li><a href="/bayi/account/logout" class="menu-toggler"> Çıkış Yap </a></li>
                            </ul>
                        </li>
                    </ul>
                <?php } ?>
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
                        <a href="/bayi">Toplu İşlemler</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<?php
    if(count($this->get('notifications')) > 0) {
        $this->renderView("shared/notifications", $this->get('notifications'));
    }
    $this->renderBody();
?>
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Limitiniz Bitti mi?</h5>
                <p>Günlük limitiniz yetersiz geliyor ise
                    <a href="#modalContact" data-toggle="modal">İletişim</a> bölümünden bizimle temas kurun. En iyi fiyat teklifini alın.</p>
            </div>
            <div class="col-md-6">
                <h5>Bize Ulaşın</h5>
                <p>Her türlü soru ve görüşleriniz için
                    <a href="#modalContact" data-toggle="modal">İletişim</a> kanallarımızdan bizimle irtibat kurabilirsiniz.
                </p>
            </div>
        </div>
    </div>
</footer>
<?php $this->section("section_modals"); ?>
<div class="modal fade" id="modalNewMessage">
    <div class="modal-dialog">
        <div class="modal-content" id="modalNewMessageInner">
        </div>
    </div>
</div>
<div class="modal fade" id="modalContact" style="z-index: 1051;">
    <div class="modal-dialog">
        <div class="modal-content" id="infoModal">
            <div class="modal-body">
                <h3>İletişim Bilgileri</h3>
                <p>Her konuda (limit arttırma, görüş, öneri, talep, şikayet) iletişim kanallarından bize ulaşabilirsiniz.</p>
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
<?php $this->show(); ?>
<?php if(Wow::has("ayar/googleanalyticscode") && Wow::get("ayar/googleanalyticscode") != "") { ?>
    <script type="text/javascript">
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
    </script>
<?php } ?>
</body>
</html>