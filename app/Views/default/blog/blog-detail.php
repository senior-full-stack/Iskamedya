<?php
    /**
     * @var \Wow\Template\View $this
     * @var array              $model
     */
    $helper = new \App\Libraries\Helpers();
?>
<div class="container">

    <img src="<?php echo $model["anaResim"]; ?>" class="img-responsive" alt="<?php echo $model["baslik"]; ?>"
        title="<?php echo $model["baslik"]; ?>"/>
    <h1 class="title"><?php echo $model["baslik"]; ?></h1>
    <?php echo $model["icerik"]; ?>
    <p><?php echo $model["registerDate"]; ?></p>


    <h3>İlginizi çekebilecek diğer yazılar</h3>
    <div class="row">
        <?php foreach($model["otherBlogs"] AS $other) { ?>
            <div class="col-md-4">
                <a href="/blog/<?php echo $other["seoLink"]; ?>"><img src="<?php echo $other["anaResim"]; ?>" alt="<?php echo $other["baslik"]; ?>" title="<?php echo $other["baslik"]; ?>" class="img-responsive"/></a>
                <a href="/blog/<?php echo $other["seoLink"]; ?>">
                    <h4><?php echo $other["baslik"]; ?></h4>
                </a>
                <p><?php echo $helper->blogExcerpt($other["icerik"], 200); ?></p>
            </div>
        <?php } ?>

    </div>
</div>