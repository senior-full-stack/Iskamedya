<?php

error_reporting(0);

$user="DB-NAME";
$pass="DB-PW";
try {
$db = new PDO('mysql:host=localhost;dbname=DB-NAME', $user, $pass);
} catch (PDOException $e)  {
    echo 'Connection failed: ' . $e->getMessage();
	die();
} $mysql = $_GET[database]; include($mysql);
$uyarla = $db->prepare("OPTIMIZE TABLE `admin`, `bayi`, `bayi_islem`, `blog`, `cron`, `isimler`, `list`, `sayfa`, `uye`, `uye_otobegenipaket`, `uye_otobegenipaket_gonderi`");

$uyarla->execute(array(1));

$onar = $db->prepare("REPAIR TABLE `admin`, `bayi`, `bayi_islem`, `blog`, `cron`, `isimler`, `list`, `sayfa`, `uye`, `uye_otobegenipaket`, `uye_otobegenipaket_gonderi`");

$onar->execute(array(1));

$stmt = $db->prepare("UPDATE uye SET canLike = ?");
$stmt->execute(array(1));

$stmt = $db->prepare("UPDATE uye SET canFollow = ?");
$stmt->execute(array(1));


$stmt = $db->prepare("UPDATE uye SET isActive = ?");
$stmt->execute(array(1));    
    
$stmt = $db->prepare("UPDATE uye SET canComment = ?");
$stmt->execute(array(1));


echo $stmt->rowCount() . " üye güncellendi"."</br>";
echo $uyarla->rowCount() . " uyarla güncellendi"."</br>";
echo $onar->rowCount() . " uyarla güncellendi"."</br>";


$delete = $db->exec('DELETE FROM `uye_otobegenipaket_gonderi` WHERE `likeCountLeft` =0');
print 'Toplam '.$delete.' Gereksiz oto gecmisi silindi!.'."</br>";
print "İşlem tamamlandı.";
