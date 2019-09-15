<?php
    /**
     * @var \Wow\Template\View $this
     * @var array              $model
     */
    $helper = new \App\Libraries\Helpers();
?>
<div class="container">
    <?php echo isset($model["pageContent"]) ? $model["pageContent"] : ''; ?>
    <?php foreach($model["blogList"] AS $blog) { ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <a href="/blog/<?php echo $blog["seoLink"]; ?>" title="<?php echo $blog["baslik"]; ?>"><img src="<?php echo $blog["anaResim"]; ?>" class="img-bloglist"/></a>
                <a href="/blog/<?php echo $blog["seoLink"]; ?>" title="<?php echo $blog["baslik"]; ?>">
                    <h4><?php echo $blog["baslik"]; ?></h4>
                </a>
                <p><?php echo date("d-m-Y H:i", strtotime($blog["registerDate"])); ?></p>
                <p style="font-size: 15px;"><?php echo $helper->blogExcerpt($blog["icerik"], 400); ?></p>
                <p class="text-right">
                    <a class="btn btn-primary" href="/blog/<?php echo $blog["seoLink"]; ?>" title="<?php echo $blog["baslik"]; ?>"><i class="fa fa-eye"></i> Devamını Oku</a>
                </p>
            </div>
        </div>
    <?php } ?>
    <ul class="pager">
        <li class="previous<?php echo empty($this->get("previousPage")) ? ' disabled':''; ?>"><a href="<?php echo empty($this->get("previousPage")) ? 'javascript:;':'?page='.$this->get("previousPage"); ?>"><i class="fa fa-chevron-left"></i> Önceki</a></li>
        <li class="next<?php echo empty($this->get("nextPage")) ? ' disabled':''; ?>"><a href="<?php echo empty($this->get("nextPage")) ? 'javascript:;':'?page='.$this->get("nextPage"); ?>">Sonraki <i class="fa fa-chevron-right"></i></a></li>
    </ul>
</div>