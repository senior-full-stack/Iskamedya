<?php
    /**
     * @var \Wow\Template\View $this
     */
    $bulkTasks   = array();
    $bulkTasks[] = array(
        "link"   => "/tools/send-like",
        "text"   => "Beğeni Gönder",
        "action" => "SendLike",
        "icon"   => "fa fa-heart"
    );
    $bulkTasks[] = array(
        "link"   => "/tools/send-follower",
        "text"   => "Takipçi Gönder",
        "action" => "SendFollower",
        "icon"   => "fa fa-user-plus"
    );
    $bulkTasks[] = array(
        "link"   => "/tools/send-comment",
        "text"   => "Yorum Gönder",
        "action" => "SendComment",
        "icon"   => "fa fa-comment"
    );
    $bulkTasks[] = array(
        "link"   => "/tools/story-view",
        "text"   => "Story Görüntülenme Gönder",
        "action" => "StoryView",
        "icon"   => "fa fa-instagram"
    );

    $bulkTasks[] = array(
        "link"   => "/tools/send-view-video",
        "text"   => "Video Görüntülenme Gönder",
        "action" => "SendViewVideo",
        "icon"   => "fa fa-video-camera"
    );

    $bulkTasks[] = array(
        "link"   => "/tools/send-save",
        "text"   => "Kaydetme Gönder",
        "action" => "SendSave",
        "icon"   => "fa fa-save"
    );

    $premiumTools   = array();
    $premiumTools[] = array(
        "link"   => "/tools/auto-like-packages",
        "text"   => "Oto Beğeni Paketleri",
        "action" => "AutoLikePackages",
        "icon"   => "fa fa-heartbeat"
    );
    $premiumTools[] = array(
        "link"   => "/tools/nonfollow-users",
        "text"   => "Non Followers",
        "action" => "NonfollowUsers",
        "icon"   => "fa fa-users"
    );
?>
<div class="panel panel-default">
    <div class="panel-heading">Toplu İşlemler</div>
    <div class="panel-body" style="padding: 0;">
        <div class="list-group" style="margin-bottom: 0;">
            <?php foreach($bulkTasks as $menu) { ?>
                <a href="<?php echo $menu["link"]; ?>" class="list-group-item<?php echo $this->route->params["action"] == $menu["action"] ? ' active' : ''; ?>">
                    <i class="<?php echo $menu["icon"]; ?>"></i> <?php echo $menu["text"]; ?>
                </a>
            <?php } ?>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">Premium Araçlar</div>
    <div class="panel-body" style="padding: 0;">
        <div class="list-group" style="margin-bottom: 0;">
            <?php foreach($premiumTools as $menu) { ?>
                <a href="<?php echo $menu["link"]; ?>" class="list-group-item<?php echo $this->route->params["action"] == $menu["action"] ? ' active' : ''; ?>">
                    <i class="<?php echo $menu["icon"]; ?>"></i> <?php echo $menu["text"]; ?>
                </a>
            <?php } ?>
        </div>
    </div>
</div>

