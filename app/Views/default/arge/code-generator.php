<?php
    /**
     * @var \Wow\Template\View $this
     * @var array              $model
     */
?>
<div class="container">
    <div class="row">
        <div class="col-md-6">

            <h3>Gerekli Bilgiler</h3>
            <form method="post">
                <div class="form-group">
                    <label>Site:</label>
                    <input type="text" name="site" class="form-control" value="<?php echo $this->e($this->request->data->site);?>" required>
                    <span class="help-block">htttp ve www ön eki kullanmadan yazın. Örn: site.com</span>
                </div>
                <div class="form-group">
                    <label>Site Lisans Ip Adresi:</label>
                    <input type="text" name="siteip" class="form-control" value="<?php echo $this->e($this->request->data->siteip);?>" required>
                    <span class="help-block">Lisans serverda yer alan ip yi girin. Sadece tek ip adresi! Örn: 217.195.204.188</span>
                </div>
                <div class="form-group">
                    <label>Proxy Kullanıcı Adı:</label>
                    <input type="text" name="user" class="form-control" value="<?php echo $this->e($this->request->data->user);?>" required>
                    <span class="help-block">Sadece harf. Örn: sitecom</span>
                </div>
                <div class="form-group">
                    <label>Proxy Kullanıcı Şifre:</label>
                    <input type="text" name="pass" class="form-control" value="<?php echo $this->e($this->request->data->pass);?>" required>
                    <span class="help-block">Sadece harf ve rakam. Örn: StCm854hysdgJDF</span>
                </div>
                <div class="form-group">
                    <label>Port Başlangıçı:</label>
                    <input type="number" name="portstart" class="form-control" value="<?php echo $this->e($this->request->data->portstart);?>" required>
                    <span class="help-block">Örn: 30750</span>
                </div>
                <div class="form-group">
                    <label>Port Bitişi:</label>
                    <input type="number" name="portend" class="form-control" value="<?php echo $this->e($this->request->data->portend);?>" required>
                    <span class="help-block">Örn: 31000</span>
                </div>
                <div class="form-group">
                    <button class="btn btn-lg btn-success" type="submit">Oluştur</button>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <h3>Sonuç</h3>
            <?php if(empty($model)){ ?>
                <p>Oluşturulan kod ve yönergeler burada gösterilecek.</p>
            <?php }
            else { ?>
                <h5>1. Firewall Tanımları</h5>
                <p>İlgili portlar için, firewallda ipye izin vermemiz gerekecek. Proxy serverda /etc/sysconfig/iptables dosyasını açıp, kullanıcı bazlı port aralık tanımlarına aşağıdaki bloğu yazacağız.</p>
                <pre># <?php echo $model["params"]["site"]."\n"; ?>-A INPUT -p tcp --dport <?php echo $model["params"]["portstart"] + 1; ?>:<?php echo $model["params"]["portend"]; ?> -s <?php echo $model["params"]["siteip"]; ?> -j ACCEPT</pre>
                <p>Değişikliğin yansıması için firewall ı restart etmemiz gerekiyor. Aşağıdaki kodu terminalde işleteceğiz.</p>
                <pre>service iptables restart</pre>
                <hr />
                <h5>2. Proxy Server Tanımları</h5>
                <p>Proxy serverda /usr/local/etc/3proxy/3proxy.cfg dosyasını açın. Kullanıcıların listelendiği bölüme aşağıdaki bloğu ilave edin.</p>
                <pre># <?php echo $model["params"]["site"]."\n"; ?>users <?php echo $model["params"]["user"]; ?>:CL:<?php echo $model["params"]["pass"]; ?></pre>
                <p>Aynı dosyada, izinlerin başladığı kısıma aşağıdaki bloğu ilave edin.</p>
                <pre>allow <?php echo $model["params"]["user"]; ?> <?php echo $model["params"]["siteip"]; ?> *instagram.com</pre>
                <p>Bu şekilde proxy serverımızda gerekli izinleri vermiş olduk. Değişikliklerin etkili olması için dosyayı kaydedip, terminalde aşağıdaki komutları işleteceğiz.</p>
                <pre>/usr/bin/killall 3proxy<?php echo "\n"; ?>/usr/local/etc/3proxy/bin/3proxy /usr/local/etc/3proxy/3proxy.cfg</pre>
                <hr/>
                <h5>3. Kullanıcı Tarafı</h5>
                <p>Kullanıcı kendisine ait admin paneline giriş yaparak, Ayarlar > Sistem Ayarları > Proxy Durumu kısmını Enetegre olarak seçecek. Aynı bölümdeki Proxy Listesi alanına aşağıdaki kodu yapıştıracak ve kaydedecek. Proxy Listesi alanında sadece bu kod olacak başka hiçbirşey olmayacak!</p>
                <pre><?php echo $model["code"]; ?></pre>
                <p class="text-success">Hepsi bu kadar. Bir önemli not: eğer kullanıcı sunucu değiştirirse, ip değişeceği için proxy servera erişemeyebilir!</p>
            <?php }?>
        </div>
    </div>
</div>