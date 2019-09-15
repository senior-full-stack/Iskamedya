<?php

    namespace App\Controllers\Admin;

    use App\Libraries\InstagramReaction;
    use App\Models\Notification;
    use BulkReaction;
    use Wow;
    use Wow\Net\Response;

    class InstamisController extends BaseController {

        function onActionExecuting() {
            if(($actionResponse = parent::onActionExecuting()) instanceof Response) {
                return $actionResponse;
            }
            //Üye girişi kontrolü.
            if(($pass = $this->middleware("logged")) instanceof Response) {
                return $pass;
            }
        }

        function IndexAction() {
            $data = array();

            $data["aktiveUser"] = $this->db->query("SELECT userActive,COUNT(tokenID) 'toplamSayi' FROM token WHERE uyeID is not null GROUP BY userActive ORDER BY toplamSayi DESC");

            $data["aktiveDevice"] = $this->db->query("SELECT deviceActive,COUNT(tokenID) 'toplamSayi' FROM token WHERE deviceID<>'' GROUP BY deviceActive ORDER BY toplamSayi DESC");

            $data["bekleyenTalep"]   = $this->db->single("SELECT COUNT(talepID) FROM talepler WHERE durum='0'");
            $data["tamamlananTalep"] = $this->db->single("SELECT COUNT(talepID) FROM talepler WHERE durum='1'");
            $data["yarimkalanTalep"] = $this->db->single("SELECT COUNT(talepID) FROM talepler WHERE durum='2'");

            return $this->view($data);
        }

        function TaleplerAction($id = NULL) {

            $data = array();

            if($id) {

                $sorgu = $this->db->row("SELECT * FROM talepler WHERE talepID=:talepid", array("talepid" => $id));

                $data["talepTip"] = $sorgu["talepTip"];

                $instagramReaction = new InstagramReaction($this->findAReactionUser());

                if(($sorgu["talepTip"] == "takip" || $sorgu["talepTip"] == "story") && !empty($sorgu["instaID"])) {

                    $data["instaID"] = $sorgu["instaID"];

                    $data["userData"] = $instagramReaction->objInstagram->getUserInfoById($data["instaID"]);

                    if($data["userData"]["status"] == "fail") {
                        $this->db->query("DELETE FROM talepler WHERE talepID=:talepid",array("talepid" => $id));
                        echo "<div style='padding:50px;text-align:center;'>Üyelik kaldırılmış.</div>";
                        exit();
                    }

                } else {

                    if($sorgu["talepTip"] == "yorum") {
                        $mediaID = $sorgu["yorumMediaID"];
                    } else {
                        $mediaID = $sorgu["begeniMediaID"];
                    }

                    $data["mediaData"] = $instagramReaction->objInstagram->getMediaInfo($mediaID);

                    if($data["mediaData"]["status"] == "fail") {
                        $this->db->query("DELETE FROM talepler WHERE talepID=:talepid",array("talepid" => $id));
                        echo "<div style='padding:50px;text-align:center;'>Gönderi kaldırılmış yada artık ulaşılamıyor.</div>";
                        exit();
                    }

                }

                return $this->partialView($data, "admin/instamis/talepid");

            }

            $data["talepler"] = $this->db->query("SELECT t.talepID,t.talepTip,t.instaID,t.yorumText,t.talepTarih,t.gonderilenAdet,t.adetMax,t.durum,m.kullaniciAdi FROM talepler AS t LEFT JOIN mobil_uye AS m ON t.uyeID=m.uyeID ORDER BY t.talepTarih DESC LIMIT 50");

            return $this->view($data);

        }

        function PasifTemizleUserAction() {

            $this->db->query("DELETE FROM token WHERE userActive=0 AND deviceActive=0");
            $this->db->query("UPDATE token SET uyeID=NULL WHERE userActive=0 AND deviceActive=1");

            return $this->redirectToUrl(Wow::get("project/adminPrefix") . "/instamis");

        }

        function PasifTemizleDeviceAction() {

            $this->db->query("DELETE FROM token WHERE deviceActive=0 AND userActive=0");
            $this->db->query("UPDATE token SET deviceID='' WHERE userActive=1 AND deviceActive=0");

            return $this->redirectToUrl(Wow::get("project/adminPrefix") . "/instamis");

        }

        function SendFollowerSaveAction() {

            $data = array("status" => 0);

            $talepAdet = $this->request->data->adet ? $this->request->data->adet : 0;
            $userID    = $this->request->data->userID ? $this->request->data->userID : 0;

            if($talepAdet > 0 && $userID > 0) {
                $this->db->query("INSERT INTO talepler (uyeID,instaID, adetMax,talepTip) VALUES(:uyeID,:instaID,:adetmax,'takip')", array(
                    "uyeID"   => "0",
                    "instaID" => $userID,
                    "adetmax" => $talepAdet
                ));

                if($this->db->lastInsertId() > 0) {
                    $data["status"] = 1;
                }
            }


            return $this->json($data);

        }

        function SendLikeSaveAction() {

            $data = array("status" => 0);

            $talepAdet = $this->request->data->adet ? $this->request->data->adet : 0;
            $mediaID   = $this->request->data->mediaID ? $this->request->data->mediaID : 0;

            if($talepAdet > 0 && $mediaID > 0) {
                $this->db->query("INSERT INTO talepler (uyeID,begeniMediaID, adetMax,talepTip) VALUES(:uyeID,:begeniMediaID,:adetmax,'begeni')", array(
                    "uyeID"         => "0",
                    "begeniMediaID" => $mediaID,
                    "adetmax"       => $talepAdet
                ));

                if($this->db->lastInsertId() > 0) {
                    $data["status"] = 1;
                }
            }


            return $this->json($data);

        }


        function SendCommentSaveAction() {

            $data = array("status" => 0);

            $yorum    = $this->request->data->yorum ? $this->request->data->yorum : "";
            $mediaID  = $this->request->data->mediaID ? $this->request->data->mediaID : 0;
            $yorumlar = count(explode("\n", $yorum)) > 0 ? explode("\n", $yorum) : explode("\r", $yorum);
            if(count($yorumlar) > 0 && $mediaID > 0) {
                foreach($yorumlar AS $y) {
                    if(!empty($y)) {
                        $this->db->query("INSERT INTO talepler (uyeID,yorumMediaID, yorumText,adetMax,talepTip) VALUES(:uyeID,:yorumMediaID,:yorumtext,:adetmax,'yorum')", array(
                            "uyeID"        => "0",
                            "yorumtext"    => $y,
                            "yorumMediaID" => $mediaID,
                            "adetmax"      => 1
                        ));
                    }

                }

                if($this->db->lastInsertId() > 0) {
                    $data["status"] = 1;
                }
            }

            return $this->json($data);

        }


        function StoryViewSaveAction() {

            $data = array("status" => 0);

            $talepAdet = $this->request->data->adet ? $this->request->data->adet : 0;
            $userID    = $this->request->data->userID ? $this->request->data->userID : 0;


            if($talepAdet > 0 && $userID > 0) {

                $instagramReaction = new InstagramReaction($this->findAReactionUser());

                $getItems = $instagramReaction->objInstagram->hikayecek($userID);

                if(count($getItems["reel"]["items"]) > 0) {
                    $items = array();
                    foreach($getItems["reel"]["items"] AS $item) {
                        $items[] = array(
                            "getTakenAt" => $item["taken_at"],
                            "itemID"     => $item["id"],
                            "userPK"     => $userID
                        );
                    }
                    $this->db->query("INSERT INTO talepler (uyeID,instaID, adetMax,storyData,talepTip) VALUES(:uyeID,:instaID,:adetmax,:storydata,'story')", array(
                        "uyeID"     => "0",
                        "instaID"   => $userID,
                        "adetmax"   => $talepAdet,
                        "storydata" => json_encode($items)
                    ));

                    if($this->db->lastInsertId() > 0) {
                        $data["status"] = 1;
                    }
                }

            }

            return $this->json($data);

        }


        function YeniIslemAction() {

            $data = array();

            if($this->request->method == "POST" && !empty($this->request->data["username"])) {
                $username                = trim($this->request->data["username"]);
                $this->instagramReaction = new InstagramReaction($this->findAReactionUser());
                $user                    = $this->instagramReaction->objInstagram->getUserInfoByName($username);
                if($user["status"] != "ok") {
                    $objNotification             = new Notification();
                    $objNotification->type       = $objNotification::PARAM_TYPE_WARNING;
                    $objNotification->title      = "Kullanıcı Yok!";
                    $objNotification->messages[] = $username . " kodlu kullanıcı instagram üzerinde bulunamadı! Hatalı girmediğinizden emin olun.";
                    $this->notifications[]       = $objNotification;
                } else {
                    $id = $user["user"]["pk"];

                    return $this->redirectToUrl(Wow::get("project/adminPrefix") . "/instamis/user/" . $id);
                }
            }

            return $this->view($data);

        }

        function UserAction($id) {
            $this->instagramReaction = new InstagramReaction($this->findAReactionUser());

            if($this->request->method == "POST") {
                switch($this->request->query->formType) {
                    case "more":
                        $data = $this->instagramReaction->objInstagram->getUserFeed($id, $this->request->data->maxid);
                        $this->view->set("ajaxLoaded", 1);

                        return $this->partialView($data, 'admin/instamis/list-media');
                        break;
                }
            }

            $accountInfo = $this->instagramReaction->objInstagram->getUserInfoById($id);

            if($accountInfo["status"] != "ok") {
                return $this->notFound();
            }

            $this->view->set("accountInfo", $accountInfo);

            $data = NULL;
            if($accountInfo["user"]["is_private"] != 1) {
                $data = $this->instagramReaction->objInstagram->getUserFeed($id);
            }


            return $this->view($data);
        }

        function AutoLikePackagesAction($page = 1) {
            if(!intval($page) > 0) {
                $page = 1;
            }

            $limitCount = 50;
            $limitStart = (($page * $limitCount) - $limitCount);

            $sqlWhere = "WHERE 1=1";
            $arrSorgu = array();

            $q = $this->request->query->q;
            if(!empty($q)) {
                $sqlWhere      .= " AND (userName LIKE :q)";
                $arrSorgu["q"] = "%" . $q . "%";
            }


            $isActive = $this->request->query->isActive;
            if(!is_null($isActive) && $isActive !== "") {
                $sqlWhere             .= " AND isActive = :isActive";
                $arrSorgu["isActive"] = $isActive;
            }

            $data = $this->db->query("SELECT * FROM uye_otobegenipaket_intamis " . $sqlWhere . " ORDER BY id DESC LIMIT :limitStart,:limitCount", array_merge($arrSorgu, array(
                "limitStart" => $limitStart,
                "limitCount" => $limitCount
            )));


            $totalRows    = $this->db->single("SELECT COUNT(id) FROM uye_otobegenipaket_intamis " . $sqlWhere, $arrSorgu);
            $previousPage = $page > 1 ? $page - 1 : NULL;
            $nextPage     = $totalRows > $limitStart + $limitCount ? $page + 1 : NULL;
            $totalPage    = ceil($totalRows / $limitCount);
            $endIndex     = ($limitStart + $limitCount) <= $totalRows ? ($limitStart + $limitCount) : $totalRows;
            $pagination   = array(
                "recordCount"  => $totalRows,
                "pageSize"     => $limitCount,
                "pageCount"    => $totalPage,
                "activePage"   => $page,
                "previousPage" => $previousPage,
                "nextPage"     => $nextPage,
                "startIndex"   => $limitStart + 1,
                "endIndex"     => $endIndex
            );
            $this->view->set("pagination", $pagination);

            return $this->view($data);
        }

        function EditAutoLikePackageAction($id) {
            $paketID = intval($id);
            $paket   = $this->db->row("SELECT * FROM uye_otobegenipaket_intamis WHERE id=:paketID", array("paketID" => $paketID));
            if(empty($paket)) {
                return $this->notFound();
            }

            if($this->request->method == "POST") {
                $startDate    = date("Y-m-d H:i:s", strtotime($this->request->data->startDate));
                $endDate      = date("Y-m-d H:i:s", strtotime($this->request->data->endDate));
                $likeCountMin = intval($this->request->data->likeCountMin);
                if($likeCountMin < 1) {
                    $likeCountMin = 1;
                }
                $likeCountMax = intval($this->request->data->likeCountMax);
                if($likeCountMin < 1) {
                    $likeCountMax = 1;
                }
                $minuteDelay = intval($this->request->data->minuteDelay);
                if($minuteDelay < 1) {
                    $minuteDelay = 1;
                }
                $krediDelay = intval($this->request->data->krediDelay);
                if($krediDelay < 1) {
                    $krediDelay = 100;
                }
                if($krediDelay < ceil($likeCountMax / 20)) {
                    $krediDelay = ceil($likeCountMax / 20);
                }
                if($krediDelay > 250) {
                    $krediDelay = 250;
                }
                $gender = NULL;
                if(intval($this->request->data->gender) > 0) {
                    $gender = intval($this->request->data->gender) == 2 ? 2 : 1;
                }
                $isActive = intval($this->request->data->isActive);
                $this->db->query("UPDATE uye_otobegenipaket_intamis SET startDate = :startDate, endDate = :endDate, likeCountMin = :likeCountMin, likeCountMax = :likeCountMax,minuteDelay=:minuteDelay,krediDelay=:krediDelay, isActive = :isActive, gender = :gender WHERE id=:id", array(
                    "id"           => $paketID,
                    "startDate"    => $startDate,
                    "endDate"      => $endDate,
                    "likeCountMin" => $likeCountMin,
                    "likeCountMax" => $likeCountMax,
                    "isActive"     => $isActive,
                    "minuteDelay"  => $minuteDelay,
                    "krediDelay"   => $krediDelay,
                    "gender"       => $gender
                ));
                $objNotification             = new Notification();
                $objNotification->title      = "Paket Değişiklikleri Kaydedildi.";
                $objNotification->messages[] = "Pakette yaptığınız değişiklikler kaydedildi.";
                $objNotification->type       = $objNotification::PARAM_TYPE_SUCCESS;
                $this->notifications[]       = $objNotification;

                return $this->redirectToUrl($this->request->referrer);
            }

            $paketdetay = $this->db->query("SELECT * FROM uye_otobegenipaket_gonderi_instamis WHERE paketID=:paketID", array("paketID" => $paketID));
            $data       = array(
                "paket"      => $paket,
                "paketdetay" => $paketdetay
            );

            return $this->partialView($data, "admin/instamis/edit-auto-like-package");
        }

        function AddAutoLikePackageAction($id) {
            if($this->request->method == "POST") {
                $startDate    = date("Y-m-d H:i:s", strtotime($this->request->data->startDate));
                $endDate      = date("Y-m-d H:i:s", strtotime($this->request->data->endDate));
                $likeCountMin = intval($this->request->data->likeCountMin);
                if($likeCountMin < 1) {
                    $likeCountMin = 1;
                }
                $likeCountMax = intval($this->request->data->likeCountMax);
                if($likeCountMin < 1) {
                    $likeCountMax = 1;
                }
                $minuteDelay = intval($this->request->data->minuteDelay);
                if($minuteDelay < 1) {
                    $minuteDelay = 1;
                }
                $krediDelay = intval($this->request->data->krediDelay);
                if($krediDelay < 1) {
                    $krediDelay = 100;
                }
                if($krediDelay < ceil($likeCountMax / 20)) {
                    $krediDelay = ceil($likeCountMax / 20);
                }
                if($krediDelay > 250) {
                    $krediDelay = 250;
                }
                $gender = NULL;
                if(intval($this->request->data->gender) > 0) {
                    $gender = intval($this->request->data->gender) == 2 ? 2 : 1;
                }
                $this->db->query("INSERT INTO uye_otobegenipaket_intamis (instaID,userName,imageUrl,startDate,endDate,lastControlDate,likeCountMin,likeCountMax,minuteDelay,krediDelay,isActive,gender) VALUES (:instaID,:userName,:imageUrl,:startDate,:endDate,NOW(),:likeCountMin, :likeCountMax,:minuteDelay,:krediDelay,1,:gender)", array(
                    "instaID"      => $id,
                    "userName"     => $this->request->data->userName,
                    "imageUrl"     => $this->request->data->imageUrl,
                    "startDate"    => $startDate,
                    "endDate"      => $endDate,
                    "likeCountMin" => $likeCountMin,
                    "likeCountMax" => $likeCountMax,
                    "minuteDelay"  => $minuteDelay,
                    "krediDelay"   => $krediDelay,
                    "gender"       => $gender
                ));
                $objNotification             = new Notification();
                $objNotification->title      = "Paket Tanımlandı.";
                $objNotification->messages[] = $id . " ID li instagram hesabı için paket tanımlandı.";
                $objNotification->type       = $objNotification::PARAM_TYPE_SUCCESS;
                $this->notifications[]       = $objNotification;

                return $this->redirectToUrl($this->request->referrer);
            }
            $this->instagramReaction = new InstagramReaction($this->findAReactionUser());
            $accountInfo             = $this->instagramReaction->objInstagram->getUserInfoById($id);

            return $this->partialView($accountInfo);
        }


        function SendLikeAction($id) {
            $this->instagramReaction = new InstagramReaction($this->findAReactionUser());

            if($this->request->method == "POST") {
                switch($this->request->query->formType) {
                    case "send":
                        $adet = intval($this->request->data->adet);
                        if($adet <= 0) {
                            $sonuc = array(
                                "status"  => "error",
                                "code"    => "nolimitdefined",
                                "message" => "Beğeni Eklenemedi. Adet tanımlanmadı!",
                                "users"   => array()
                            );

                            return $this->json($sonuc);
                        }

                        $gender = NULL;
                        if(intval($this->request->data->gender) > 0) {
                            $gender = intval($this->request->data->gender) == 2 ? 2 : 1;
                        }

                        $genderSql = "";
                        $arrGender = array();
                        if(!empty($gender)) {
                            $genderSql           = " AND gender=:gender";
                            $arrGender["gender"] = $gender;
                        }

                        if($adet > Wow::get("ayar/bayiPaketBasiIstek")) {
                            $adet = Wow::get("ayar/bayiPaketBasiIstek");
                        }

                        $likedUsers = array();
                        if(!isset($_SESSION["MediaLikersForMediaID" . $this->request->data->mediaID]) || !is_array($_SESSION["MediaLikersForMediaID" . $this->request->data->mediaID])) {
                            $likers = $this->instagramReaction->objInstagram->getMediaLikers($this->request->data->mediaID);
                            foreach($likers["users"] as $user) {
                                $likedUsers[] = $user["pk"];
                            }
                            $_SESSION["MediaLikersForMediaID" . $this->request->data->mediaID] = $likedUsers;
                        } else {
                            $likedUsers = (array)$_SESSION["MediaLikersForMediaID" . $this->request->data->mediaID];
                        }

                        $triedUserIDs = isset($_SESSION["TriedUsersForLikeMediaID" . $this->request->data->mediaID]) ? $_SESSION["TriedUsersForLikeMediaID" . $this->request->data->mediaID] : NULL;
                        if(empty($triedUserIDs)) {
                            $triedUserIDs = "0";
                        }
                        $userIDs = "0";
                        foreach(explode(",", $triedUserIDs) as $userID) {
                            if(intval($userID) > 0) {
                                $userIDs .= "," . intval($userID);
                            }
                        }


                        $instaIDs      = "0";
                        $likedInstaIDs = $likedUsers;
                        foreach($likedInstaIDs as $instaID) {
                            if(intval($instaID) > 0) {
                                $instaIDs .= "," . intval($instaID);
                            }
                        }

                        $users = $this->db->query("SELECT uyeID,instaID,kullaniciAdi,sifre FROM uye WHERE isActive=1 AND canLike=1 and isUsable=1 AND uyeID NOT IN($userIDs) AND instaID NOT IN($instaIDs) " . $genderSql . " ORDER BY sonOlayTarihi ASC LIMIT :adet", array_merge($arrGender, array("adet" => $adet)));
                        if(empty($users)) {
                            $sonuc = array(
                                "status"  => "error",
                                "code"    => "nouserleft",
                                "message" => "Beğeni Eklenemedi. Kullanıcı kalmadı!",
                                "users"   => array()
                            );

                            return $this->json($sonuc);
                        }

                        $allUserIDs = array_map(function($d) {
                            return $d["uyeID"];
                        }, $users);
                        $allUserIDs = implode(",", $allUserIDs);
                        $this->db->query("UPDATE uye SET sonOlayTarihi=NOW() WHERE uyeID IN (" . $allUserIDs . ")");
                        $this->db->CloseConnection();

                        $bulkReaction      = new BulkReaction($users, Wow::get("ayar/bayiEsZamanliIstek"));
                        $response          = $bulkReaction->like($this->request->data->mediaID, $this->request->data->extraParams);
                        $triedUsers        = $response["users"];
                        $totalSuccessCount = $response["totalSuccessCount"];
                        $allUserIDs        = array_map(function($d) {
                            return $d["userID"];
                        }, $triedUsers);
                        if(!empty($allUserIDs)) {
                            $allUserIDs                                                           = implode(",", $allUserIDs);
                            $userIDs                                                              .= "," . $allUserIDs;
                            $_SESSION["TriedUsersForLikeMediaID" . $this->request->data->mediaID] = $userIDs;
                        }
                        $allFailedUserIDs = array_filter(array_map(function($d) {
                            return $d["status"] == "fail" ? $d["userID"] : NULL;
                        }, $triedUsers), function($d) {
                            return $d !== NULL;
                        });
                        if(!empty($allFailedUserIDs)) {
                            $allFailedUserIDs = implode(",", $allFailedUserIDs);
                            $this->db->query("UPDATE uye SET canLike=0,canLikeControlDate=NOW() WHERE uyeID IN (" . $allFailedUserIDs . ")");
                        }

                        $sonuc = array(
                            "status"  => "success",
                            "message" => "Başarılı.",
                            "users"   => $triedUsers
                        );


                        return $this->json($sonuc);
                        break;
                }
            }
            //GET Method
            $media = $this->instagramReaction->objInstagram->getMediaInfo($id);
            if($media["status"] != "ok") {
                return $this->notFound();
            }

            return $this->partialView($media);

        }


        function SendCommentAction($id) {
            $this->instagramReaction = new InstagramReaction($this->findAReactionUser());

            if($this->request->method == "POST") {
                switch($this->request->query->formType) {
                    case "send":

                        $arrCommentg = preg_split("/\\r\\n|\\r|\\n/", $this->request->data->yorum);
                        if(!is_array($arrCommentg) || empty($arrCommentg)) {
                            $sonuc = array(
                                "status"  => "error",
                                "message" => "Yorum Eklenemedi. En az 1 yorum yazmalısınız!",
                                "userID"  => 0
                            );

                            return $this->json($sonuc);
                        }

                        $arrComment = [];
                        foreach($arrCommentg as $comment) {
                            if(!empty($comment)) {
                                $arrComment[] = trim($comment);
                            }
                        }
                        $commentedIndexes = $this->request->query->clearCommentedIndex == 1 ? [] : $_SESSION["CommentedIndexesForMediaID" . $this->request->data->mediaID];
                        if(!is_array($commentedIndexes) || empty($commentedIndexes)) {
                            $commentedIndexes = [];
                        }

                        $arrComment = array_diff_key($arrComment, $commentedIndexes);

                        if(empty($arrComment)) {
                            $sonuc = array(
                                "status"  => "error",
                                "message" => "Yorum Eklenemedi. En az 1 yorum yazmalısınız!",
                                "userID"  => 0
                            );

                            return $this->json($sonuc);
                        }

                        $gender = NULL;
                        if(intval($this->request->data->gender) > 0) {
                            $gender = intval($this->request->data->gender) == 2 ? 2 : 1;
                        }

                        $genderSql = "";
                        $arrGender = array();
                        if(!empty($gender)) {
                            $genderSql           = " AND gender=:gender";
                            $arrGender["gender"] = $gender;
                        }

                        $adet = count($arrComment);

                        if($adet > Wow::get("ayar/bayiPaketBasiIstek")) {
                            $adet = Wow::get("ayar/bayiPaketBasiIstek");
                        }


                        $triedUserIDs = isset($_SESSION["TriedUsersForCommentMediaID" . $this->request->data->mediaID]) ? $_SESSION["TriedUsersForCommentMediaID" . $this->request->data->mediaID] : NULL;
                        if(empty($triedUserIDs)) {
                            $triedUserIDs = "0";
                        }
                        $userIDs = "0";
                        foreach(explode(",", $triedUserIDs) as $userID) {
                            if(intval($userID) > 0) {
                                $userIDs .= "," . intval($userID);
                            }
                        }


                        $users = $this->db->query("SELECT uyeID,instaID,kullaniciAdi,sifre FROM uye WHERE isActive=1 AND canComment=1 and isUsable=1 AND uyeID NOT IN($userIDs) " . $genderSql . " ORDER BY sonOlayTarihi ASC LIMIT :adet", array_merge($arrGender, array("adet" => $adet)));

                        if(empty($users)) {
                            $sonuc = array(
                                "status"  => "error",
                                "code"    => "nouserleft",
                                "message" => "Yorum Eklenemedi. Kullanıcı kalmadı!",
                                "users"   => array()
                            );

                            return $this->json($sonuc);
                        }

                        $allUserIDs = array_map(function($d) {
                            return $d["uyeID"];
                        }, $users);
                        $allUserIDs = implode(",", $allUserIDs);
                        $this->db->query("UPDATE uye SET sonOlayTarihi=NOW() WHERE uyeID IN (" . $allUserIDs . ")");
                        $this->db->CloseConnection();

                        $bulkReaction      = new BulkReaction($users, Wow::get("ayar/bayiEsZamanliIstek"));
                        $response          = $bulkReaction->comment($this->request->data->mediaID, $this->request->data->mediaCode, $arrComment);
                        $triedUsers        = $response["users"];
                        $totalSuccessCount = $response["totalSuccessCount"];
                        $allUserIDs        = array_map(function($d) {
                            return $d["userID"];
                        }, $triedUsers);
                        if(!empty($allUserIDs)) {
                            $allUserIDs                                                              = implode(",", $allUserIDs);
                            $userIDs                                                                 .= "," . $allUserIDs;
                            $_SESSION["TriedUsersForCommentMediaID" . $this->request->data->mediaID] = $userIDs;
                        }
                        $allFailedUserIDs = array_filter(array_map(function($d) {
                            return $d["status"] == "fail" ? $d["userID"] : NULL;
                        }, $triedUsers), function($d) {
                            return $d !== NULL;
                        });
                        if(!empty($allFailedUserIDs)) {
                            $allFailedUserIDs = implode(",", $allFailedUserIDs);
                            $this->db->query("UPDATE uye SET canComment=0,canCommentControlDate=NOW() WHERE uyeID IN (" . $allFailedUserIDs . ")");
                        }

                        $sonuc = array(
                            "status"  => "success",
                            "message" => "Başarılı.",
                            "users"   => $triedUsers
                        );

                        foreach($triedUsers as $i => $v) {
                            if($v["status"] == "success") {
                                $commentedIndexes[$v["commentIndex"]] = TRUE;
                            }
                        }
                        $_SESSION["CommentedIndexesForMediaID" . $this->request->data->mediaID] = $commentedIndexes;


                        return $this->json($sonuc);
                        break;
                }
            }
            //GET Method
            $media = $this->instagramReaction->objInstagram->getMediaInfo($id);
            if($media["status"] != "ok") {
                return $this->notFound();
            }

            return $this->partialView($media);

        }


        function SendFollowerAction($id) {
            $this->instagramReaction = new InstagramReaction($this->findAReactionUser());

            if($this->request->method == "POST") {
                switch($this->request->query->formType) {
                    case "send":

                        $adet = intval($this->request->data->adet);
                        if($adet <= 0) {
                            $sonuc = array(
                                "status"  => "error",
                                "code"    => "nolimitdefined",
                                "message" => "Takipçi Eklenemedi. Adet tanımlanmadı!",
                                "users"   => array()
                            );

                            return $this->json($sonuc);
                        }

                        $gender = NULL;
                        if(intval($this->request->data->gender) > 0) {
                            $gender = intval($this->request->data->gender) == 2 ? 2 : 1;
                        }

                        $genderSql = "";
                        $arrGender = array();
                        if(!empty($gender)) {
                            $genderSql           = " AND gender=:gender";
                            $arrGender["gender"] = $gender;
                        }

                        if($adet > Wow::get("ayar/bayiPaketBasiIstek")) {
                            $adet = Wow::get("ayar/bayiPaketBasiIstek");
                        }

                        $followedUsers = array();
                        if(!isset($_SESSION["FollowerssForInstaID" . $this->request->data->userID]) || !is_array($_SESSION["FollowerssForInstaID" . $this->request->data->userID])) {
                            $nextMaxID = NULL;
                            $intLoop   = 0;
                            while($follower = $this->instagramReaction->objInstagram->getUserFollowers($this->request->data->userID, $nextMaxID)) {
                                $intLoop++;
                                foreach($follower["users"] as $user) {
                                    $followedUsers[] = $user["pk"];
                                }
                                if(!isset($follower["next_max_id"]) || $intLoop >= 8) {
                                    break;
                                } else {
                                    $nextMaxID = $follower["next_max_id"];
                                }
                            }


                        } else {
                            $followedUsers = (array)$_SESSION["FollowerssForInstaID" . $this->request->data->userID];
                        }


                        $triedUserIDs = isset($_SESSION["TriedUsersForFollowInstaID" . $this->request->data->userID]) ? $_SESSION["TriedUsersForFollowInstaID" . $this->request->data->userID] : NULL;
                        if(empty($triedUserIDs)) {
                            $triedUserIDs = "0";
                        }
                        $userIDs = "0";
                        foreach(explode(",", $triedUserIDs) as $userID) {
                            if(intval($userID) > 0) {
                                $userIDs .= "," . intval($userID);
                            }
                        }

                        $instaIDs         = "0";
                        $followedInstaIDs = $followedUsers;
                        foreach($followedInstaIDs as $instaID) {
                            if(intval($instaID) > 0) {
                                $instaIDs .= "," . intval($instaID);
                            }
                        }

                        $users = $this->db->query("SELECT uyeID,instaID,kullaniciAdi,sifre FROM uye WHERE isActive=1 AND canFollow=1 and isUsable=1 AND uyeID NOT IN($userIDs) AND instaID NOT IN($instaIDs) " . $genderSql . " ORDER BY sonOlayTarihi ASC LIMIT :adet", array_merge($arrGender, array("adet" => $adet)));


                        if(empty($users)) {
                            $sonuc = array(
                                "status"  => "error",
                                "code"    => "nouserleft",
                                "message" => "Takipçi Eklenemedi. Kullanıcı kalmadı!",
                                "users"   => array()
                            );

                            return $this->json($sonuc);
                        }

                        $allUserIDs = array_map(function($d) {
                            return $d["uyeID"];
                        }, $users);
                        $allUserIDs = implode(",", $allUserIDs);
                        $this->db->query("UPDATE uye SET sonOlayTarihi=NOW() WHERE uyeID IN (" . $allUserIDs . ")");
                        $this->db->CloseConnection();

                        $bulkReaction      = new BulkReaction($users, Wow::get("ayar/bayiEsZamanliIstek"));
                        $response          = $bulkReaction->follow($this->request->data->userID, $this->request->data->userName);
                        $triedUsers        = $response["users"];
                        $totalSuccessCount = $response["totalSuccessCount"];
                        $allUserIDs        = array_map(function($d) {
                            return $d["userID"];
                        }, $triedUsers);
                        if(!empty($allUserIDs)) {
                            $allUserIDs                                                            = implode(",", $allUserIDs);
                            $userIDs                                                               .= "," . $allUserIDs;
                            $_SESSION["TriedUsersForFollowInstaID" . $this->request->data->userID] = $userIDs;
                        }
                        $allFailedUserIDs = array_filter(array_map(function($d) {
                            return $d["status"] == "fail" ? $d["userID"] : NULL;
                        }, $triedUsers), function($d) {
                            return $d !== NULL;
                        });
                        if(!empty($allFailedUserIDs)) {
                            $allFailedUserIDs = implode(",", $allFailedUserIDs);
                            $this->db->query("UPDATE uye SET canFollow=0,canFollowControlDate=NOW() WHERE uyeID IN (" . $allFailedUserIDs . ")");
                        }


                        $sonuc = array(
                            "status"  => "success",
                            "message" => "Başarılı.",
                            "users"   => $triedUsers
                        );

                        return $this->json($sonuc);
                        break;
                }
            }
            //GET Method
            $user = $this->instagramReaction->objInstagram->getUserInfoById($id);
            if($user["status"] != "ok") {
                return $this->notFound();
            }

            return $this->partialView($user);

        }


        function StoryViewAction($id) {
            $this->instagramReaction = new InstagramReaction($this->findAReactionUser());

            if($this->request->method == "POST") {
                switch($this->request->query->formType) {
                    case "send":

                        $adet = intval($this->request->data->adet);
                        if($adet <= 0) {
                            $sonuc = array(
                                "status"  => "error",
                                "code"    => "nolimitdefined",
                                "message" => "Story Görüntülenme Eklenemedi. Adet tanımlanmadı!",
                                "users"   => array()
                            );

                            return $this->json($sonuc);
                        }

                        $gender = NULL;
                        if(intval($this->request->data->gender) > 0) {
                            $gender = intval($this->request->data->gender) == 2 ? 2 : 1;
                        }

                        $genderSql = "";
                        $arrGender = array();
                        if(!empty($gender)) {
                            $genderSql           = " AND gender=:gender";
                            $arrGender["gender"] = $gender;
                        }

                        if($adet > Wow::get("ayar/bayiPaketBasiIstek")) {
                            $adet = Wow::get("ayar/bayiPaketBasiIstek");
                        }

                        $triedUserIDs = isset($_SESSION["TriedUsersForStoryViewInstaID" . $this->request->data->userID]) ? $_SESSION["TriedUsersForStoryViewInstaID" . $this->request->data->userID] : NULL;
                        if(empty($triedUserIDs)) {
                            $triedUserIDs = "0";
                        }
                        $userIDs = "0";
                        foreach(explode(",", $triedUserIDs) as $userID) {
                            if(intval($userID) > 0) {
                                $userIDs .= "," . intval($userID);
                            }
                        }

                        $instaIDs = "0";

                        $users = $this->db->query("SELECT uyeID,instaID,kullaniciAdi,sifre FROM uye WHERE isActive=1 AND canStoryView=1 AND uyeID NOT IN($userIDs) AND instaID NOT IN($instaIDs) " . $genderSql . " ORDER BY sonOlayTarihi ASC LIMIT :adet", array_merge($arrGender, array("adet" => $adet)));

                        if(empty($users)) {
                            $sonuc = array(
                                "status"  => "error",
                                "code"    => "nouserleft",
                                "message" => "Story Görüntülenme Eklenemedi. Kullanıcı kalmadı!",
                                "users"   => array()
                            );

                            return $this->json($sonuc);
                        }

                        $allUserIDs = array_map(function($d) {
                            return $d["uyeID"];
                        }, $users);
                        $allUserIDs = implode(",", $allUserIDs);
                        $this->db->query("UPDATE uye SET sonOlayTarihi=NOW() WHERE uyeID IN (" . $allUserIDs . ")");
                        $this->db->CloseConnection();

                        $bulkReaction = new BulkReaction($users, Wow::get("ayar/bayiEsZamanliIstek"));

                        $items    = array();
                        $getItems = $this->instagramReaction->objInstagram->hikayecek($id);

                        if(count($getItems["reel"]["items"]) < 1) {
                            $sonuc = array(
                                "status"  => "error",
                                "code"    => "nolimitdefined",
                                "message" => "Kullanıcının aktif bir story paylaşımı bulunmamaktadır!",
                                "users"   => array()
                            );

                            return $this->json($sonuc);
                        }

                        foreach($getItems["reel"]["items"] AS $item) {
                            $items[] = array(
                                "getTakenAt" => $item["taken_at"],
                                "itemID"     => $item["id"],
                                "userPK"     => $id
                            );
                        }

                        $response = $bulkReaction->storyview($items);

                        $triedUsers        = $response["users"];
                        $totalSuccessCount = $response["totalSuccessCount"];
                        $allUserIDs        = array_map(function($d) {
                            return $d["userID"];
                        }, $triedUsers);
                        if(!empty($allUserIDs)) {
                            $allUserIDs                                                               = implode(",", $allUserIDs);
                            $userIDs                                                                  .= "," . $allUserIDs;
                            $_SESSION["TriedUsersForStoryViewInstaID" . $this->request->data->userID] = $userIDs;
                        }
                        $allFailedUserIDs = array_filter(array_map(function($d) {
                            return $d["status"] == "fail" ? $d["userID"] : NULL;
                        }, $triedUsers), function($d) {
                            return $d !== NULL;
                        });
                        if(!empty($allFailedUserIDs)) {
                            $allFailedUserIDs = implode(",", $allFailedUserIDs);
                            $this->db->query("UPDATE uye SET canStoryView=0,canStoryViewControlDate=NOW() WHERE uyeID IN (" . $allFailedUserIDs . ")");
                        }

                        $sonuc = array(
                            "status"  => "success",
                            "message" => "Başarılı.",
                            "users"   => $triedUsers
                        );

                        return $this->json($sonuc);
                        break;
                }
            }
            //GET Method
            $user = $this->instagramReaction->objInstagram->getUserInfoById($id);
            if($user["status"] != "ok") {
                return $this->notFound();
            }

            return $this->partialView($user);

        }
    }