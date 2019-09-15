<?php
    return array(

        //Project Variables
        "project"  => array(
            "cookiePath"        => "./app/Cookies/",
            "licenseKey"        => "4f53743e324b5b3b21b058baecb46bf37bd9888e",
            "cronJobToken"      => "goodlike_V21",
            "onlyHttps"         => FALSE,
            "adminPrefix"       => "/admin",
            "resellerPrefix"    => "/bayi",
            "memberLoginPrefix" => "/member",
			"siteID" 			=> "999999999"
        ),

        //App Variables
        "app"      => array(
            "theme"                 => "default",
            "layout"                => "layout/default",
            "language"              => "en",
            "base_url"              => NULL,
            "handle_errors"         => TRUE,
            "log_errors"            => FALSE,
            "router_case_sensitive" => TRUE
        ),


        //Database Variables
        "database" => array(
            "DefaultConnection" => array(
                //mysql, sqlsrv, pgsql are tested connections and work perfect.
                "driver"   => "mysql",
                "host"     => "localhost",
                "port"     => "3306",
                "name"     => "DB-NAME",
                "user"     => "DB-NAME",
                "password" => "DB-NAME"
            )
        )
    );