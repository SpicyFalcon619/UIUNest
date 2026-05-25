<?php
$ch = curl_init('http://localhost/UIU-Nest/api/seeking.php');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id' => 2]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Cookie: PHPSESSID=' . session_id()
));
$result = curl_exec($ch);
echo "RESPONSE:\n";
echo $result;
if (curl_error($ch)) {
    echo "\nERROR: " . curl_error($ch);
}
curl_close($ch);
