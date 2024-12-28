<?php

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "posandinventory";

if(!$con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname))
{

	die("failed to connect!");
}
