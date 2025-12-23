<?php
$apiKey = 'AIzaSyDMWkLeKI5z1ryyPX4h9Ka5QaJRK8Xjf7Q';
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=' . $apiKey;

$data = [
    'contents' => [
        ['parts' => [['text' => 'Hola, soy un técnico de TI. ¿Cómo estás?']]]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

echo "<h2>Test Gemini 2.0 Flash Experimental</h2><pre>";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP: $httpCode\n\n";
echo "Response:\n$response";
curl_close($ch);
echo "</pre>";
?>
