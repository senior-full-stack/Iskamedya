<?php
    /**
     * @var \Wow\Template\View $this
     * @var array              $model
     */
    $logonPerson = $this->get("logonPerson");
?>
<div class="container">
    <div class="cl10"></div>
    <div class="row">
        <div class="col-sm-8 col-md-9">
            <?php echo isset($model["pageContent"]) ? $model["pageContent"] : ''; ?>
        </div>
        <div class="col-sm-4 col-md-3">
            <?php $this->renderView("tools/sidebar"); ?>
        </div>
    </div>
</div>