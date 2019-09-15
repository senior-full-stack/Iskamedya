<?php
    /**
     * @var \Wow\Template\View      $this
     * @var array                   $model
     * @var array                   $media
     * @var \App\Models\LogonPerson $logonPerson
     */
    $isAjaxLoaded = intval($this->get("ajaxLoaded")) == 1 ? TRUE : FALSE;
    $logonPerson  = $this->get("logonPerson");
    if(!$isAjaxLoaded) { ?>
<div class="container">
    <div class="cl10"></div>
    <div class="row">
        <div class="col-sm-8 col-md-9">
            <h4 style="margin-top: 0;">Story Görüntülenme Aracı</h4>
            <div class="alert alert-warning">Atılan görüntülenmeler o an aktif tüm storylerinize gitmektedir.</div>
            <p>Maximum story krediniz kadar, story görüntülenmesi gönderebilirsiniz!</p>
            <p>Profiliniz gizli olmamalıdır! Gizli profillerin gönderilerine ulaşılamadığından, story görüntülenme de gönderilememektedir.</p>
            <div id="entry-list-container">
                <div class="row" id="entry-list-row">
                    <?php }
					try
					{
                        foreach($model["reel"]["items"] as $item) {
                                ?>
                                <div class="entry-layout col-lg-4 col-md-4 col-sm-6 col-xs-12" id="entry<?php echo $item["id"]; ?>">
                                    <div class="entry-thumb transition">
                                        <div class="entry-media">
                                            <div class="image">
                                                <a href="#modalMediaPopup" data-toggle="modal" onclick="openPopupMedia('<?php echo $item["id"]; ?>');"><img src="<?php echo $logonPerson->member["profilFoto"]; ?>" class="img-responsive" /></a>
                                            </div>
                                        </div>
                                        <div class="action-links">
                                            <div class="btn-group btn-group-justified">
                                                <a class="btn btn-default" href="#modalMediaPopup" data-toggle="modal" onclick="openPopupMedia('<?php echo $item["id"]; ?>');"><i class="fa fa-youtube-play"></i>
                                                    Görüntülenme Gönder</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                        }
					}
					catch (Exception $e)
					{
						print_r(@'
						Üzgünüz, Son 24 Saat İçerisinde Hikaye Paylaşımı Bulunamadı.
						');
					}
                    ?>
                    <?php if(isset($model["more_available"]) && $model["more_available"] == 1) { ?>
                        <button id="btnLoadMore" onclick="loadMore('<?php echo $model["next_max_id"]; ?>');" class="btn btn-block btn-success" type="button">
                            <i class="fa fa-plus"></i> Daha Fazla Yükle
                        </button>
                    <?php }
                        if(!$isAjaxLoaded) {
                    ?>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-md-3">
            <?php $this->renderView("tools/sidebar"); ?>
        </div>
    </div>
</div>
<?php } ?>


<?php $this->section("section_modals");
    $this->parent(); ?>
<div class="modal fade" id="modalMediaPopup" style="z-index: 1051;">
    <div class="modal-dialog">
        <div class="modal-content" id="modalMediaPopupInner">
        </div>
    </div>
</div>
<?php $this->endSection();
    $this->section("section_scripts");
    $this->parent(); ?>
<script type="text/javascript">
    function openPopupMedia(mediaID) {
        $('#modalMediaPopupInner').html('<div class="modal-body"><h2>Bekleyin..</h2></div>');
        $.ajax({url: '/tools/story-view/' + mediaID, type: 'GET'}).done(function(data) {
            $('#modalMediaPopupInner').html(data);
        });
    }
</script>
<?php $this->endSection(); ?>
