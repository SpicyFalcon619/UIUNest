<?php
$ch = curl_init('http://localhost/UIU-Nest/api/login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email'=>'student1@uiu.ac.bd','password'=>'password']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
echo "RESPONSE:\n";
var_dump($res);
