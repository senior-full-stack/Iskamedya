<?php
    /**
     * @var \Wow\Template\View $this
     */
    $bulkTasks      = array();
    $bulkTasks[]    = array(
        "link"   => "/bayi-tx/send-like",
        "text"   => "Beğeni Gönder",
        "action" => "SendLike",
        "icon"   => "fa fa-heart"
    );
    $bulkTasks[]    = array(
        "link"   => "/bayi-tx/send-follower",
        "text"   => "Takipçi Gönder",
        "action" => "SendFollower",
        "icon"   => "fa fa-user-plus"
    );
    $bulkTasks[]    = array(
        "link"   => "/bayi-tx/send-comment",
        "text"   => "Yorum Gönder",
        "action" => "SendComment",
        "icon"   => "fa fa-comment"
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
