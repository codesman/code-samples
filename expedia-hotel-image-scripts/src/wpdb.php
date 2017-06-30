<?php

$dbname = "hat_local_db";
$user   = "root";
$pass   = "";

$wpdb = new PDO( "mysql:host=localhost;dbname=$dbname;charset=utf8", $user, $pass );
