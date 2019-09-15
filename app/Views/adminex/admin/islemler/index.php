<?php
    /**
     * @var \Wow\Template\View $this
     * @var array              $model
     */
    $this->set("title", "Pasif Kullanıcı Temizleme");
?>
<div class="panel panel-default">
    <div class="panel-heading">
        Pasif Kullanıcıları Temizleme
    </div>
    <div class="panel-body">
        <p>Pasif Kullanıcılar, artık cookie'leri çalışmayan veya kullanıcı adı & şifrelerini değiştirmiş olan kullanıcıları ifade eder. Bu kullanıcılar sistemde tutmanın kimseye bir yararı yoktur. Temizlemek en iyisi olacaktır! Aynı zamanda pasif kullanıcıları silerek, sql sorgularının hızlanmasına katkıda bulunabilirsiniz.</p>
        <?php if($model["countPassiveUsers"] == 0) { ?>
            <p class="text-success">Tebrikler, sistemde hiç pasif kullanıcı yok.</p>
        <?php } else { ?>
            <p class="text-danger">Tespit edilen pasif kullanıcı sayısı:
                <strong><?php echo $model["countPassiveUsers"]; ?> adet</strong></p>
            <p>
                <a href="javascript:void(0);" id="btnRemovePassiveUsers" onclick="removePassiveUsers();" class="btn btn-primary">TEMİZLE</a>
            </p>
        <?php } ?>
    </div>
</div>

<?php $this->section("section_scripts");
    $this->parent(); ?>
<script type="text/javascript">
    function removePassiveUsers() {
        $('#btnRemovePassiveUsers').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin fa-3x"></i> TEMİZLENİYOR..');
        $.ajax({type: 'POST', dataType: 'json', url: '?formType=removePassiveUsers'}).done(function(data) {
            window.location.href = window.location.href;
        });
    }
</script>
<?php $this->endSection(); ?>
