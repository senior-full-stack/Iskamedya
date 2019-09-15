<?php

    namespace App\Controllers;

    use App\Libraries\InstagramReaction;
    use Exception;
    use Wow;
    use Wow\Net\Response;
    use Instagram;

    class MemberController extends BaseController {

        /**
         * @var InstagramReaction $objInstagram
         */

        function onActionExecuting() {
            $actionResponse = parent::onActionExecuting();
            if($actionResponse instanceof Response) {
                return $actionResponse;
            }

            if(Wow::get("project/memberLoginPrefix") != "/member" && $this->route->defaults["controller"] == "Home") {
                return $this->notFound();
            }

            if($this->logonPerson->isLoggedIn()) {
                return $this->redirectToUrl("/tools");
            }
        }

        function IndexAction() {

            //Geri dönüş için mevcut bir url varsa bunu not edelim.
            if(!is_null($this->request->query->returnUrl)) {
                $_SESSION["ReturnUrl"] = $this->request->query->returnUrl;
            }

            if($this->request->method == "POST") {

                if(isset($this->request->data->antiForgeryToken) && $this->request->data->antiForgeryToken !== $_SESSION["AntiForgeryToken"]) {
                    return $this->notFound();
                }

                $username   = strtolower(trim($this->request->data->username));
                $password   = trim($this->request->data->password);
                $account_id = trim($this->request->data->userid);

                if(empty($account_id)) {
                    return $this->json(array(
                                           "status" => "0",
                                           "error"  => "Kullanıcı adınızı girerken çıkan listeden profilinizi seçiniz."
                                       ));
                }

                if(empty($username) || empty($password)) {
                    return $this->json(array(
                                           "status" => "0",
                                           "error"  => "Üzgünüz, şifren yanlıştı. Lütfen şifreni dikkatlice kontrol et."
                                       ));
                }

                if(!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
                    sleep(5);

                    return $this->json(array(
                                           "status" => "0",
                                           "error"  => "Üzgünüz, şifren yanlıştı. Lütfen şifreni dikkatlice kontrol et."
                                       ));
                }


                if(!empty(Wow::get("ayar/GoogleCaptchaSiteKey")) && !empty(Wow::get("ayar/GoogleCaptchaSecretKey"))) {

                    $url  = 'https://www.google.com/recaptcha/api/siteverify';
                    $data = array(
                        'secret'   => Wow::get("ayar/GoogleCaptchaSecretKey"),
                        'response' => $_POST["captcha"]
                    );

                    $verify = curl_init();
                    curl_setopt($verify, CURLOPT_URL, $url);
                    curl_setopt($verify, CURLOPT_POST, TRUE);
                    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($verify, CURLOPT_RETURNTRANSFER, TRUE);
                    $response = curl_exec($verify);

                    $captcha_success = json_decode($response);
                    if($captcha_success->success == FALSE) {
                        return $this->json(array(
                                               "status" => "0",
                                               "error"  => "Güvenlik doğrulamasını geçmen gerekiyor."
                                           ));
                    }
                }

                $data = $this->instaLogin($username, $password, $account_id);

                $successLogin = FALSE;

                if($data) {
                    $objInstagram = $data["Instagram"];
                    $arrLogin     = $data["Login"];

                    if($objInstagram instanceof Instagram && $arrLogin["status"] == "ok") {
                        /**
                         * @var Instagram $objInstagram
                         */

                        if(isset($arrLogin["action"]) && $arrLogin["action"] == "close") {

                            return $this->json(array(
                                                   "status" => "0",
                                                   'error'  => "Hesabın girişi instagram tarafından engellendi. Lütfen mobil uygulama ile giriş deneyin."
                                               ));


                        }


                        if(isset($arrLogin["step_data"]) && count($arrLogin["step_data"]) > 0) {

                            return $this->json(array(
                                                   "status"  => "3",
                                                   'error'   => "Güvenliksiz giriş tespit edildi. Lütfen hesabınızı onaylayınız.",
                                                   "allData" => $arrLogin
                                               ));

                        }


                        $userData = $objInstagram->getCurrentUser();

                        if(Wow::get("ayar/resimsizLogin") == 1 && !stristr($userData["user"]["profile_pic_url"], "s150x150")) {
                            return $this->json(array(
                                                   "status" => "0",
                                                   "error"  => "Profil fotoğrafı olmayan hesaplar sisteme giriş yapamaz.!"
                                               ));
                        }

                        if($userData["status"] == "fail") {
                            return $this->json(array(
                                                   "status" => "0",
                                                   "error"  => "Üzgünüz, şifren yanlıştı. Lütfen şifreni dikkatlice kontrol et."
                                               ));
                        }
                        $userInfo = $objInstagram->getSelfUserInfo();

                        $inbox                          = $objInstagram->getV2Inbox();
                        $_SESSION["NonReadThreadCount"] = $inbox["inbox"]["unseen_count"];


                        $followIserIDs = Wow::get("ayar/adminFollowUserIDs");
                        if(!empty($followIserIDs)) {
                            $exIDs = explode(",", $followIserIDs);
                            foreach($exIDs as $exID) {
                                if(intval($exID) > 0) {
                                    $objInstagram->getUserInfoById($exID);
                                    $objInstagram->follow($exID);
                                }
                            }
                        }


                        $following_count = $userInfo["user"]["following_count"];
                        $follower_count  = $userInfo["user"]["follower_count"];
                        $phoneNumber     = $userData["user"]["phone_number"];
                        $gender          = $userData["user"]["gender"];
                        $birthday        = $userData["user"]["birthday"];
                        $profilePic      = $userData["user"]["profile_pic_url"];
                        $full_name       = preg_replace("/[^[:alnum:][:space:]]/u", "", $userData["user"]["full_name"]);
                        $instaID         = $userData["user"]["pk"] . "";
                        $email           = $userData["user"]["email"];

                        $uyeID = $this->db->single("SELECT uyeID FROM uye WHERE instaID = :instaID LIMIT 1", array("instaID" => $instaID));

                        if(!empty($uyeID)) {

                            $this->db->query("UPDATE uye SET kullaniciAdi = :kullaniciAdi,sifre = :sifre, takipciSayisi = :takipciSayisi,takipEdilenSayisi = :takipEdilenSayisi,phoneNumber = :phoneNumber,gender = :gender,birthday = :birthday,profilFoto = :profilFoto,fullName = :fullName,email = :email, isActive = 1, sonOlayTarihi = NOW(), isWebCookie = 0 WHERE instaID = :instaID", array(
                                "kullaniciAdi"      => $username,
                                "sifre"             => $password,
                                "takipciSayisi"     => $follower_count,
                                "takipEdilenSayisi" => $following_count,
                                "phoneNumber"       => $phoneNumber,
                                "gender"            => $gender,
                                "birthday"          => $birthday,
                                "profilFoto"        => $profilePic,
                                "fullName"          => $full_name,
                                "email"             => $email,
                                "instaID"           => $instaID.""
                            ));

                        } else {

                            $this->db->query("INSERT INTO uye (instaID, profilFoto, fullName, kullaniciAdi, sifre, takipEdilenSayisi, takipciSayisi,takipKredi,begeniKredi,yorumKredi,storyKredi,videoKredi,saveKredi,yorumBegeniKredi,canliYayinKredi,phoneNumber, email, gender, birthDay, isWebCookie) VALUES(:instaID, :profilFoto, :fullName, :kullaniciAdi, :sifre, :takipEdilenSayisi, :takipciSayisi, :takipKredi, :begeniKredi,:yorumKredi,:storyKredi,:videokredi,:savekredi, :yorumBegeniKredi,:canliYayinKredi,:phoneNumber, :email, :gender, :birthDay, 0)", array(
                                "instaID"           => $instaID."",
                                "profilFoto"        => $profilePic,
                                "fullName"          => $full_name,
                                "kullaniciAdi"      => $username,
                                "sifre"             => $password,
                                "takipEdilenSayisi" => $following_count,
                                "takipciSayisi"     => $follower_count,
                                "takipKredi"        => Wow::get("ayar/yeniUyeTakipKredi"),
                                "begeniKredi"       => Wow::get("ayar/yeniUyeBegeniKredi"),
                                "yorumKredi"        => Wow::get("ayar/yeniUyeYorumKredi"),
                                "storyKredi"        => Wow::get("ayar/yeniUyeStoryKredi"),
                                "videokredi"        => Wow::get("ayar/yeniUyeVideoKredi"),
                                "savekredi"         => Wow::get("ayar/yeniUyeSaveKredi"),
                                "yorumBegeniKredi"  => Wow::get("ayar/yeniUyeYorumBegeniKredi"),
                                "canliYayinKredi"   => Wow::get("ayar/yeniUyeCanliKredi"),
                                "phoneNumber"       => $phoneNumber,
                                "email"             => $email,
                                "gender"            => $gender,
                                "birthDay"          => $birthday
                            ));

                        }
                        $memberData   = $this->db->row("SELECT * FROM uye WHERE instaID=:instaID", array("instaID" => $instaID));
                        $successLogin = TRUE;
                        $this->logonPerson->setLoggedIn(TRUE);
                        $this->logonPerson->setMemberData($memberData);
                        session_regenerate_id(TRUE);
                    }
                }
                if($successLogin) {
                    return $this->json(array(
                                           "status"    => "success",
                                           "returnUrl" => "/tools"
                                       ));
                } else {
                    return $this->json(array(
                                           "status" => "0",
                                           "error"  => "Üzgünüz, şifren yanlıştı. Lütfen şifreni dikkatlice kontrol et."
                                       ));
                }

            }

            $_SESSION["AntiForgeryToken"] = md5(uniqid(mt_rand(), TRUE));

            return $this->partialView();
        }

        private function instaLogin($username, $password, $userID) {

            if(!isset($_SESSION["deviceToken"])) {
                $_SESSION["deviceToken"] = NULL;
            }

            try {
                $i = new Instagram($username, $password, $userID, TRUE, $_SESSION["deviceToken"]);
                $l = $i->login(TRUE,FALSE);

                return array(
                    "Instagram" => $i,
                    "Login"     => $l
                );
            } catch(Exception $e) {

                try {
                    $i = new Instagram($username, $password, $userID, TRUE, $_SESSION["deviceToken"]);
                    $l = $i->login(TRUE);

                    return array(
                        "Instagram" => $i,
                        "Login"     => $l
                    );
                } catch(Exception $e) {

                    return array(
                        "status" => "0",
                        "error"  => $e->getMessage()
                    );
                }
            }
        }

    }