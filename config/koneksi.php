<?php

$host     ='sql110.infinityfree.com';
$user     ='if0_43412687';
$password ='rinfah67';
$database ='if0_43412687_UKK_2026';

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die('koneksi database gagal: ' . mysqli_connect_error());
}
?>