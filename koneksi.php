<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "latihan1";
$port = null;
$candidate_ports = [3306, 3307];
foreach ($candidate_ports as $p) {
    if (@fsockopen($host, $p, $errno, $errstr, 2)) {
        $port = $p;
        break;
    }
}

$conn = mysqli_connect($host, $user, $pass, $db, $port ?? 3306);
if ($conn == false)
{
echo "Koneksi ke server gagal.";
die();
}
?>