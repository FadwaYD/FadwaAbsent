<?php
session_start();
if (isset($_SESSION["matricul"]) && isset($_SESSION["mot_de_passe"])){
    session_unset();
    session_destroy();
    header('location:login.php');

}else{
    header('location:login.php');

}