<?php
$dsn="mysql:host=localhost;dbname=gestion_des_absences";
$dbusername="root";
$dbpassword="0000";

try{
    $pdo=new PDO($dsn,$dbusername,$dbpassword);
    $pdo-> setATTribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);


}catch(PDOException $e){
    echo "connexion faild" .$e->getMessage();
} 