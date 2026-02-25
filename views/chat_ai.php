<?php
// chat_ai.php
require_once '../config/config_key.php';

// 2. Nhận tin nhắn từ file Javascript gửi lên
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Lỗi: Không có nội dung tin nhắn.']);
    exit;
}

// 3. Cấu hình "Tính cách" cho AI (System Instruction)
// Dòng này giúp AI biết nó là ai.
$systemInstruction = "Bạn là Trợ lý ảo AI của Phòng khám BHH. Hãy trả lời ngắn gọn, lịch sự, đóng vai nhân viên y tế. Nếu vấn đề khẩn cấp, hãy khuyên gọi 115. Không trả lời các vấn đề ngoài y tế.";

// 4. Chuẩn bị dữ liệu gửi sang Google Gemini
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;

$data = [
    "contents" => [
        [
            "parts" => [
                // Kết hợp tính cách và câu hỏi người dùng
                ["text" => $systemInstruction . "\n\nKhách hàng hỏi: " . $userMessage]
            ]
        ]
    ]
];

// 5. Gửi yêu cầu bằng CURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bỏ qua kiểm tra chứng chỉ
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Bỏ qua kiểm tra host

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['reply' => 'Lỗi CURL: ' . curl_error($ch)]);
    exit;
}

curl_close($ch);

// 6. Xử lý kết quả trả về
if ($response) {
    $json = json_decode($response, true);

    // [DEBUG] Kiểm tra xem có lỗi từ Google không
    if (isset($json['error'])) {
        $loiGoogle = "Google báo lỗi: " . $json['error']['message'];
        echo json_encode(['reply' => $loiGoogle]);
        exit;
    }

    // Lấy nội dung text
    $botReply = $json['candidates'][0]['content']['parts'][0]['text'] ?? 'Không lấy được nội dung. Phản hồi gốc: ' . $response;
    echo json_encode(['reply' => $botReply]);
} else {
    echo json_encode(['reply' => 'Lỗi kết nối đến server AI (Response rỗng).']);
}
