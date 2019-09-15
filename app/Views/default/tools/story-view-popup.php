<?php
	/**
	 * @var \Wow\Template\View      $this
	 * @var array                   $model
	 * @var \App\Models\LogonPerson $logonPerson
	 */
	$media = $model;
	$logonPerson  = $this->get("logonPerson");
	if($media["items"][0]["user"]["is_private"] == 1){ ?>
        <p class="text-danger">Uppps! Profiliniz gizli. Gizli profillerin gönderilerine ulaşılamadığından, Hikaye izlenme de gönderilememektedir.</p>
	<?php } else { ?>
        <div class="panel panel-default" style="margin-bottom: 0;">
            <div class="panel-heading">
                Story Görüntülenme Gönder
                <button type="button" class="close pull-right" data-dismiss="modal" aria-hidden="true">&times;</button>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <form id="formBegeni" class="form" onsubmit="return false;">
                    <div class="form-group">
                        <label>Gönderi:</label>
						<?php $item = $media["items"][0]; ?>
                        <img src="<?php echo $logonPerson->member["profilFoto"]; ?>" class="img-responsive" style="max-height: 200px;"/>
                    </div>
                    <div class="form-group">
                        <label>Story Görüntülenme Sayısı:</label>
                        <input type="text" name="adet" class="form-control" placeholder="10" value="10">
                    </div>
                    <input type="hidden" name="mediaID" value="<?php echo $item["id"]; ?>">
                    <input type="hidden" name="mediaCode" value="<?php echo $item["code"]; ?>">
                    <button type="button" id="formBegeniSubmitButton" class="btn btn-success" onclick="sendBegeni();">Gönderimi Başlat</button>
                </form>
                <div class="cl10"></div>
                <div id="userList"></div>
            </div>
        </div>
	<?php } ?>
<script type="text/javascript">
    var countBegeni, countBegeniMax;

    function sendBegeni() {
        countBegeni    = 0;
        countBegeniMax = parseInt($('#formBegeni input[name=adet]').val());
        if(isNaN(countBegeniMax) || countBegeniMax <= 0) {
            alert('Beğeni adedi girin!');
            return false;
        }
        $('#formBegeniSubmitButton').html('<i class="fa fa-spinner fa-spin fa-2x"></i> Gönderimi Başlat');
        $('#formBegeni input').attr('readonly', 'readonly');
        $('#formBegeni button').attr('disabled', 'disabled');
        $('#userList').html('');
        sendBegeniRC();
    }

    function sendBegeniRC() {
        $.ajax({type: 'POST', dataType: 'json', url: '?formType=send', data: $('#formBegeni').serialize()}).done(function(data) {
            if(data.status == 'error') {
                $('#userList').prepend('<p class="text-danger">' + data.message + '</p>');
                sendBegeniComplete();
            }
            else {
                for(var i = 0; i < data.users.length; i++) {
                    var user = data.users[i];
                    if(user.status == 'success') {
                        $('#userList').prepend('<p>' + user.userNick + ' kullanıcı denendi. Sonuç: <span class="label label-success">Başarılı</span></p>');
                        countBegeni++;
                        $('#formBegeni input[name=adet]').val(countBegeniMax - countBegeni);
                        $('#begeniKrediCount').html(data.begeniKredi);

                    }
                    else {
                        //$('#userList').prepend('<p><a href="/user/' + user.instaID + '">' + user.userNick + '</a> kullanıcı denendi. Sonuç: <span class="label label-danger">Başarısız</span></p>');
                    }
                }
                if(countBegeni < countBegeniMax) {
                    sendBegeniRC();
                }
                else {
                    sendBegeniComplete();
                }
            }
        });
    }

    function sendBegeniComplete() {
        $('#formBegeniSubmitButton').html('Gönderimi Başlat');
        $('#formBegeni input').removeAttr('readonly');
        $('#formBegeni button').prop("disabled", false);
        $('#formBegeni input[name=adet]').val('10');
        $('#userList').prepend('<p class="text-success">Gönderilen toplam izlenme adedi: ' + countBegeni + '</p>');
    }
</script>
