<?php
$ch = curl_init('http://localhost/UIU-Nest/api/register.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['name'=>'Test', 'email'=>'test@uiu.ac.bd','password'=>'password']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
echo "REGISTER RESPONSE:\n";
var_dump($res);
