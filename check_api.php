<?php
// check_api.php
$apiKey = "AIzaSyDphvXfXu4rEKSz-ti6utad0Iu_waUDnxg";
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Tắt check SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

echo "<h3>Danh sách các Model bạn được dùng:</h3>";
if (isset($data['models'])) {
    echo "<ul>";
    foreach ($data['models'] as $model) {
        // Chỉ hiện các model hỗ trợ tạo nội dung (generateContent)
        if (in_array("generateContent", $model['supportedGenerationMethods'])) {
            echo "<li>Model Name: <b>" . str_replace("models/", "", $model['name']) . "</b></li>";
        }
    }
    echo "</ul>";
} else {
    echo "Lỗi: " . $response;
}
