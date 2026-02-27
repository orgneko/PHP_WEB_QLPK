/* File: js/script.js
    Chức năng: Xử lý khi click vào các ô dịch vụ
*/

// 1. KHO DỮ LIỆU NỘI DUNG (Bạn có thể sửa chữ trong này tùy ý)
const dataServices = {
    // 1. Thần kinh
    'thankinh': {
        title: 'Khoa Thần kinh',
        items: [
            'Tư vấn và điều trị chuyên sâu bệnh lý thần kinh',
            'Chăm sóc toàn diện não bộ',
            'Điều trị đau đầu, mất ngủ kéo dài',
            'Điều trị động kinh và co giật',
            'Đánh giá trí nhớ và suy giảm nhận thức',
            'Quản lý rối loạn vận động, Parkinson'
        ]
    },
    // 2. Tim mạch
    'timmach': {
        title: 'Khoa Tim mạch',
        items: [
            'Khám và tư vấn bệnh lý tim mạch',
            'Siêu âm tim màu 4D, Điện tâm đồ (ECG)',
            'Điều trị tăng huyết áp, suy tim',
            'Tầm soát bệnh mạch vành, nhồi máu cơ tim',
            'Holter điện tâm đồ 24h',
            'Phục hồi chức năng tim mạch sau phẫu thuật'
        ]
    },
    // 3. Chấn thương chỉnh hình
    'chanthuong': {
        title: 'Chấn thương chỉnh hình',
        items: [
            'Cấp cứu và xử lý gãy xương, bong gân',
            'Phẫu thuật nội soi khớp (gối, vai)',
            'Phẫu thuật thay khớp háng, khớp gối nhân tạo',
            'Điều trị thoát vị đĩa đệm, đau lưng',
            'Vật lý trị liệu phục hồi chức năng',
            'Điều trị loãng xương ở người cao tuổi'
        ]
    },
    // 4. Phẫu thuật
    'phauthuat': {
        title: 'Khoa Ngoại & Phẫu thuật',
        items: [
            'Phẫu thuật nội soi tiêu hóa (dạ dày, đại tràng)',
            'Phẫu thuật điều trị trĩ (Longo)',
            'Tiểu phẫu u bã đậu, u mỡ',
            'Phẫu thuật sỏi mật, sỏi thận',
            'Chăm sóc vết thương hậu phẫu chuẩn y khoa',
            'Tư vấn phẫu thuật thẩm mỹ'
        ]
    },
    // 5. Nha khoa
    'nhakhoa': {
        title: 'Nha khoa Thẩm mỹ',
        items: [
            'Cấy ghép Implant kỹ thuật cao',
            'Niềng răng mắc cài và trong suốt (Invisalign)',
            'Bọc răng sứ thẩm mỹ',
            'Tẩy trắng răng công nghệ Laser',
            'Điều trị tủy, nha chu, sâu răng',
            'Nhổ răng khôn không đau'
        ]
    },
    // 6. Chẩn đoán hình ảnh
    'chandoan': {
        title: 'Chẩn đoán hình ảnh',
        items: [
            'Chụp cộng hưởng từ (MRI) sọ não, cột sống',
            'Chụp cắt lớp vi tính (CT Scanner)',
            'Chụp X-quang kỹ thuật số',
            'Siêu âm màu 4D (bụng, thai, tuyến giáp)',
            'Nội soi tiêu hóa gây mê không đau',
            'Đo loãng xương toàn thân'
        ]
    },
    // 7. Tiết niệu
    'tietnieu': {
        title: 'Thận - Tiết niệu',
        items: [
            'Tán sỏi thận ngoài cơ thể',
            'Điều trị viêm đường tiết niệu',
            'Điều trị phì đại tuyến tiền liệt',
            'Tầm soát ung thư đường tiết niệu',
            'Chạy thận nhân tạo',
            'Nam khoa và sức khỏe sinh sản'
        ]
    },
    // 8. Nội khoa
    'noikhoa': {
        title: 'Nội khoa Tổng quát',
        items: [
            'Khám sức khỏe tổng quát định kỳ',
            'Điều trị tiểu đường (Đái tháo đường)',
            'Điều trị bệnh lý dạ dày, đại tràng',
            'Điều trị bệnh hô hấp (hen suyễn, COPD)',
            'Tiêm chủng vắc-xin cho người lớn và trẻ em',
            'Tư vấn dinh dưỡng lâm sàng'
        ]
    },
    // 9. Xem thêm (Các dịch vụ khác)
    'xemthem': {
        title: 'Dịch vụ Y tế Khác',
        items: [
            'Dịch vụ Bác sĩ gia đình',
            'Lấy mẫu xét nghiệm tại nhà',
            'Khám sức khỏe lái xe, đi làm',
            'Dịch vụ xe cấp cứu 24/7',
            'Nhà thuốc đạt chuẩn GPP',
            'Bảo hiểm y tế và bảo lãnh viện phí'
        ]
    },
    // Mặc định (đề phòng lỗi)
    'default': {
        title: 'Dịch vụ Y tế',
        items: ['Vui lòng chọn một dịch vụ để xem chi tiết.']
    }
};

// 2. HÀM XỬ LÝ CHÍNH (Đừng sửa phần này nếu không cần thiết)
function changeService(serviceId, element) {
    // Bước 1: Tìm tất cả các thẻ có class 'service-card' và xóa class 'active' đi
    // Mục đích: Để tắt màu xanh của ô cũ
    let cards = document.querySelectorAll('.service-card');
    cards.forEach(function(card) {
        card.classList.remove('active');
    });

    // Bước 2: Thêm class 'active' vào thẻ vừa được click
    // Mục đích: Để ô mới sáng màu xanh lên
    element.classList.add('active');

    // Bước 3: Lấy dữ liệu tương ứng từ kho dữ liệu bên trên
    // Nếu không tìm thấy id thì lấy cái 'default'
    let data = dataServices[serviceId] || dataServices['default'];

    // Bước 4: Tạo danh sách HTML (các dòng có dấu cộng +)
    let listHTML = '';
    if (data.items && data.items.length > 0) {
        // Dùng vòng lặp để tạo từng dòng li
        data.items.forEach(function(item) {
            listHTML += `<li class="mb-3">
                            <i class="fas fa-plus-circle text-primary mr-2 small"></i> 
                            ${item}
                         </li>`;
        });
    }

    // Bước 5: Tìm cái khung bên phải (Panel) và thay đổi nội dung bên trong
    let panel = document.getElementById('service-detail-panel');
    
    // Gán nội dung mới vào
    panel.innerHTML = `
        <h3 class="text-primary font-weight-bold mb-4" style="border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;">
            ${data.title}
        </h3>
        <ul class="list-unstyled text-secondary" style="font-size: 16px; line-height: 1.8;">
            ${listHTML}
        </ul>
        <div class="mt-4">
            <a href="views/booking.php" class="btn btn-primary rounded-pill px-4 py-2 font-weight-bold shadow-sm">
                Đặt lịch khám ngay
            </a>

            <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2 font-weight-bold ml-2 shadow-sm" onclick="openModal()">
                Tư vấn miễn phí
            </button>
        </div>
    `;

    // Hiệu ứng làm mờ nhẹ để người dùng biết nội dung đã đổi
    panel.style.opacity = 0;
    setTimeout(function() {
        panel.style.opacity = 1;
    }, 100);
    panel.style.transition = "opacity 0.4s ease-in-out";
}
// Hàm mở Modal
function openModal() {
    document.getElementById("consultation-modal").style.display = "block";
}

// Hàm đóng Modal
function closeModal() {
    document.getElementById("consultation-modal").style.display = "none";
}

// Khi click ra ngoài vùng modal thì cũng đóng luôn
window.onclick = function(event) {
    let modal = document.getElementById("consultation-modal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
// Bật tắt khung chat
function toggleChat() {
    let chatWidget = document.getElementById("chat-widget");
    if (chatWidget.style.display === "flex") {
        chatWidget.style.display = "none";
    } else {
        chatWidget.style.display = "flex";
    }
}

// Xử lý khi chọn câu hỏi gợi ý
function botReply(type) {
    let chatBody = document.getElementById("chat-body");
    let replyText = "";

    // 1. Hiển thị câu người dùng chọn (Giả vờ như người dùng chat)
    let userText = "";
    if(type === 'price') userText = "💰 Bảng giá khám";
    if(type === 'address') userText = "📍 Địa chỉ ở đâu?";
    if(type === 'book') userText = "📅 Đặt lịch thế nào?";
    if(type === 'human') userText = "👨‍⚕️ Gặp tư vấn viên";

    let userMsgHTML = `<div class="message user-message">${userText}</div>`;
    chatBody.insertAdjacentHTML('beforeend', userMsgHTML);

    // 2. Bot trả lời (Sau 0.5 giây cho thật)
    setTimeout(() => {
        if (type === 'price') {
            replyText = "Giá khám tổng quát là 200.000đ. Khám chuyên khoa từ 300.000đ ạ.";
        } else if (type === 'address') {
            replyText = "Phòng khám ở số 36 Đường ABC, Thành phố Thanh Hóa ạ.";
        } else if (type === 'book') {
            replyText = "Bạn có thể ấn nút 'Đặt lịch khám' màu xanh ở trên menu nhé!";
        } else if (type === 'human') {
            replyText = "Dạ, bạn vui lòng để lại SĐT ở phần 'Tư vấn miễn phí', nhân viên sẽ gọi lại ngay ạ.";
        }

        let botMsgHTML = `<div class="message bot-message">${replyText}</div>`;
        chatBody.insertAdjacentHTML('beforeend', botMsgHTML);
        
        // Tự động cuộn xuống cuối
        chatBody.scrollTop = chatBody.scrollHeight;
    }, 600);
}
/* --- THÊM VÀO CUỐI FILE js/script.js --- */

// 1. Hàm xử lý khi ấn phím Enter
function handleEnter(event) {
    if (event.key === "Enter") {
        sendMessage();
    }
}

// 2. Hàm gửi tin nhắn đi
async function sendMessage() {
    let inputField = document.getElementById("chat-input");
    let message = inputField.value.trim();
    let chatBody = document.getElementById("chat-body");

    if (message === "") return;

    // A. Hiển thị tin nhắn của Người dùng lên màn hình
    let userMsgHTML = `<div class="message user-message">${message}</div>`;
    chatBody.insertAdjacentHTML('beforeend', userMsgHTML);
    inputField.value = ""; // Xóa ô nhập
    chatBody.scrollTop = chatBody.scrollHeight; // Cuộn xuống cuối

    // B. Hiển thị trạng thái "Đang gõ..." (Loading)
    let loadingHTML = `<div class="message bot-message" id="loading-dots">AI đang trả lời... <i class="fas fa-spinner fa-spin"></i></div>`;
    chatBody.insertAdjacentHTML('beforeend', loadingHTML);
    chatBody.scrollTop = chatBody.scrollHeight;

    try {
        // C. Gửi tin nhắn sang file PHP (Backend)
        const response = await fetch('views/chat_ai.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: message })
        });

        const data = await response.json();

        // D. Xóa cái "Đang gõ..." và hiện câu trả lời thật
        document.getElementById("loading-dots").remove();
        
        // Chuyển đổi xuống dòng (\n) thành thẻ <br> để hiển thị đẹp
        let botText = data.reply.replace(/\n/g, "<br>");
        
        let botMsgHTML = `<div class="message bot-message">${botText}</div>`;
        chatBody.insertAdjacentHTML('beforeend', botMsgHTML);

    } catch (error) {
        document.getElementById("loading-dots").remove();
        let errorHTML = `<div class="message bot-message text-danger">Lỗi kết nối! Vui lòng thử lại.</div>`;
        chatBody.insertAdjacentHTML('beforeend', errorHTML);
    }

    // Cuộn xuống cuối lần nữa
    chatBody.scrollTop = chatBody.scrollHeight;
}