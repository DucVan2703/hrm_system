// ============================================
// HỆ THỐNG JAVASCRIPT TOÀN CỤC (GLOBAL JS)
// ============================================

// 1. SIDEBAR TOGGLE (Hỗ trợ thu gọn/mở rộng mượt mà trên cả Desktop và Mobile)
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (window.innerWidth <= 1024) {
        // Trên Mobile/Tablet: Bật/Tắt class 'open' để trượt sidebar ra/vào
        if (sidebar) {
            sidebar.classList.toggle('open');
        }
    } else {
        // Trên Desktop: Bật/Tắt 'collapsed' ở sidebar và 'expanded' ở main-content
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
        }
        if (mainContent) {
            mainContent.classList.toggle('expanded');
        }
    }
}

// 2. MODAL OVERLAY ACTIONS
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Đóng modal khi nhấp chuột ra ngoài vùng modal-content
window.addEventListener('click', function(e) {
    const overlays = document.querySelectorAll('.modal-overlay');
    overlays.forEach(overlay => {
        if (e.target === overlay) {
            overlay.style.display = 'none';
        }
    });
});

// 3. DYNAMIC CHATBOT (TRỢ LÝ ẢO TBU)
function createChatbot() {
    if (document.getElementById('chatWidget')) return;

    // Xác định basePath của hệ thống
    const basePath = window.location.pathname.startsWith('/hrm_system') ? '/hrm_system' : '';

    const chatWidget = document.createElement('div');
    chatWidget.id = 'chatWidget';
    chatWidget.className = 'chat-widget';
    chatWidget.style.cssText = 'position: fixed; bottom: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column; align-items: flex-end; gap: 15px;';

    chatWidget.innerHTML = `
        <div class="chat-window" id="chatWindow" style="display:none; flex-direction:column; width:360px; height:500px; border-radius:16px; background:#ffffff; box-shadow:0 10px 30px rgba(0,0,0,0.15); border:1px solid rgba(0,0,0,0.08); overflow:hidden;">
            <div class="chat-header" style="background:linear-gradient(135deg, #059669, #10b981); padding:16px 20px; color:#ffffff; display:flex; align-items:center; gap:12px; position:relative;">
                <img src="${basePath}/assets/images/logo.png" alt="Bot Logo" style="width:36px; height:36px; border-radius:50%; border:2px solid rgba(255,255,255,0.8); object-fit:contain; background:#ffffff;" onerror="this.src='https://cdn-icons-png.flaticon.com/512/4712/4712035.png'">
                <div class="chat-title">
                    <h4 style="margin:0; font-size:15px; font-weight:700;">Trợ lý TBU</h4>
                    <span style="font-size:11px; opacity:0.95; display:flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; background:#10b981; border-radius:50%; display:inline-block; border:1px solid #ffffff;"></span>Đang hoạt động</span>
                </div>
                <div class="close-chat" id="closeChat" style="position:absolute; right:20px; cursor:pointer; font-size:16px; opacity:0.8; transition:0.2s;"><i class="fas fa-times"></i></div>
            </div>
            
            <div class="chat-messages" id="chatMessages" style="flex:1; padding:20px; overflow-y:auto; display:flex; flex-direction:column; gap:12px; background:#f8fafc;">
                <div class="msg bot" style="max-width:80%; padding:10px 14px; border-radius:0 14px 14px 14px; font-size:13px; line-height:1.5; background:#ffffff; border:1px solid #e2e8f0; color:#334155; align-self:flex-start; box-shadow:0 1px 3px rgba(0,0,0,0.02);">Chào bạn! Tôi là trợ lý HR ảo của Đại học Thành Đông. Tôi có thể giúp gì cho bạn hôm nay?</div>
            </div>
            
            <div id="chatSuggestions" style="padding:0 20px 8px 20px; display:flex; gap:6px; flex-wrap:wrap; background:#f8fafc;"></div>
            
            <div class="chat-input-area" style="padding:12px 20px; display:flex; gap:10px; background:#ffffff; border-top:1px solid #e2e8f0;">
                <input type="text" id="chatInput" placeholder="Nhập câu hỏi của bạn..." style="flex:1; border:1px solid #cbd5e1; padding:8px 14px; border-radius:99px; font-size:13px; outline:none; transition:0.2s;">
                <button id="sendMsg" style="width:36px; height:36px; border-radius:50%; background:#059669; color:#ffffff; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.2s;"><i class="fas fa-paper-plane" style="font-size:13px;"></i></button>
            </div>
        </div>
        
        <div class="chat-btn" id="chatBtn" style="width: 56px; height: 56px; border-radius: 50%; background: #059669; display: flex; align-items: center; justify-content: center; color: #fff; cursor: pointer; box-shadow: 0 4px 16px rgba(5,150,105,0.3); border: 2px solid #ffffff; transition: transform 0.2s;">
            <i class="fas fa-comment-dots" style="font-size:24px;"></i>
        </div>
    `;
    
    document.body.appendChild(chatWidget);

    const chatBtn = document.getElementById('chatBtn');
    const chatWindow = document.getElementById('chatWindow');
    const chatHeader = chatWindow.querySelector('.chat-header');
    
    // Style both handles to show move cursor
    if (chatBtn) chatBtn.style.cursor = 'move';
    if (chatHeader) chatHeader.style.cursor = 'move';

    // Make both handles drag the parent chatWidget
    if (chatWidget) {
        makeDraggable(chatWidget, chatHeader);
        makeDraggable(chatWidget, chatBtn, toggleChatWindow); // Pass toggleChatWindow to handle click-vs-drag
    }

    const closeChat = document.getElementById('closeChat');
    const chatInput = document.getElementById('chatInput');
    const sendMsg = document.getElementById('sendMsg');
    const chatMessages = document.getElementById('chatMessages');
    const chatSuggestions = document.getElementById('chatSuggestions');

    // 1. Hiển thị / Ẩn khung chat (Chuyển thành hàm độc lập)
    function toggleChatWindow() {
        const isOpened = chatWindow.style.display === 'flex';
        chatWindow.style.display = isOpened ? 'none' : 'flex';
        if (!isOpened) {
            chatWindow.classList.add('open');
            chatInput.focus();
            
            // Tự động load lại các nút gợi ý mặc định
            showSuggestions(['👤 Hồ sơ của tôi', '⏱️ Chấm công', '💰 Lương tháng này', '📅 Nghỉ phép']);
        } else {
            chatWindow.classList.remove('open');
        }
    }

    closeChat.onclick = () => {
        chatWindow.style.display = 'none';
        chatWindow.classList.remove('open');
    };

    // 2. Thêm bong bóng tin nhắn
    function addMessage(text, side) {
        const msg = document.createElement('div');
        msg.className = `msg ${side}`;
        
        // CSS Style cho bong bóng
        if (side === 'user') {
            msg.style.cssText = 'max-width:80%; padding:10px 14px; border-radius:14px 14px 0 14px; font-size:13px; line-height:1.5; background:#059669; color:#ffffff; align-self:flex-end; box-shadow:0 1px 3px rgba(0,0,0,0.05);';
        } else {
            msg.style.cssText = 'max-width:80%; padding:10px 14px; border-radius:0 14px 14px 14px; font-size:13px; line-height:1.5; background:#ffffff; border:1px solid #e2e8f0; color:#334155; align-self:flex-start; box-shadow:0 1px 3px rgba(0,0,0,0.02);';
        }

        // Thay thế markdown đơn giản (**chữ đậm** và \n xuống dòng)
        msg.innerHTML = text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>').replace(/\n/g, '<br>');
        chatMessages.appendChild(msg);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return msg;
    }

    // 3. Hiển thị các nút gợi ý nhanh
    function showSuggestions(list) {
        chatSuggestions.innerHTML = '';
        if (!list || list.length === 0) return;
        
        list.forEach(sug => {
            const btn = document.createElement('div');
            btn.className = 'sug-btn';
            btn.style.cssText = 'padding:4px 12px; background:#ffffff; border:1px solid #cbd5e1; border-radius:12px; font-size:11.5px; color:#475569; cursor:pointer; font-weight:500; transition:0.2s;';
            btn.textContent = sug;
            
            // Hover effect
            btn.onmouseenter = () => btn.style.background = '#f1f5f9';
            btn.onmouseleave = () => btn.style.background = '#ffffff';
            
            btn.onclick = () => {
                sendMessage(sug);
            };
            chatSuggestions.appendChild(btn);
        });
    }

    // 4. Gửi tin nhắn đến máy chủ
    async function sendMessage(text) {
        if (!text || text.trim() === '') return;
        
        // Hiển thị tin nhắn của User
        addMessage(text, 'user');
        chatInput.value = '';
        showSuggestions([]); // Tạm ẩn các gợi ý cũ

        // Tạo bong bóng gõ chữ giả lập
        const typing = addMessage('Trợ lý ảo đang xử lý...', 'bot');
        typing.style.color = '#94a3b8';

        try {
            const formData = new FormData();
            formData.append('message', text);

            const targetURL = basePath ? basePath + '/troly.php' : '/troly.php';

            const response = await fetch(targetURL, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            // Xóa bong bóng giả lập và hiện kết quả thực
            typing.remove();
            
            addMessage(result.reply || 'Có lỗi xảy ra trong quá trình phản hồi! 😅', 'bot');
            
            if (result.suggestions && result.suggestions.length > 0) {
                showSuggestions(result.suggestions);
            }
        } catch (error) {
            typing.remove();
            addMessage('Xin lỗi, tôi gặp chút trục trặc về đường truyền kết nối CSDL! 😅', 'bot');
        }
    }

    // 5. Bắt sự kiện bàn phím/chuột gửi tin
    sendMsg.onclick = () => sendMessage(chatInput.value);
    chatInput.onkeydown = (e) => {
        if (e.key === 'Enter') {
            sendMessage(chatInput.value);
        }
    };
}

// Khởi chạy chatbot sau khi trang load xong hoàn toàn
window.addEventListener('DOMContentLoaded', () => {
    createChatbot();
});

// Thêm tính năng kéo thả (Drag and Drop) cho khung chat bằng Header (Hỗ trợ cả máy tính và điện thoại, phân biệt click và drag)
function makeDraggable(windowEl, headerEl, clickCallback) {
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    let startX = 0, startY = 0;
    let hasMoved = false;

    // Sự kiện kéo chuột (Desktop)
    headerEl.onmousedown = dragMouseDown;

    // Sự kiện kéo tay (Mobile/Touch)
    headerEl.addEventListener('touchstart', dragTouchStart, { passive: false });

    function dragMouseDown(e) {
        e = e || window.event;
        if (e.button !== 0) return; // Chỉ kéo bằng chuột trái
        
        startX = e.clientX;
        startY = e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        hasMoved = false;

        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;

        // Nếu di chuyển quá 5px thì xác định là đang kéo (drag)
        if (Math.abs(e.clientX - startX) > 5 || Math.abs(e.clientY - startY) > 5) {
            hasMoved = true;
        }

        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        
        if (hasMoved) {
            e.preventDefault();
            let newLeft = windowEl.offsetLeft - pos1;
            let newTop = windowEl.offsetTop - pos2;

            // Giới hạn trong viewport để không bị kéo ra ngoài màn hình
            const maxLeft = window.innerWidth - windowEl.offsetWidth;
            const maxTop = window.innerHeight - windowEl.offsetHeight;

            newLeft = Math.max(0, Math.min(maxLeft, newLeft));
            newTop = Math.max(0, Math.min(maxTop, newTop));

            windowEl.style.bottom = 'auto';
            windowEl.style.right = 'auto';
            windowEl.style.left = newLeft + 'px';
            windowEl.style.top = newTop + 'px';
        }
    }

    function dragTouchStart(e) {
        if (e.touches.length !== 1) return;
        
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        pos3 = e.touches[0].clientX;
        pos4 = e.touches[0].clientY;
        hasMoved = false;

        document.addEventListener('touchend', closeTouchElement);
        document.addEventListener('touchmove', elementTouchDrag, { passive: false });
    }

    function elementTouchDrag(e) {
        if (e.touches.length !== 1) return;

        if (Math.abs(e.touches[0].clientX - startX) > 5 || Math.abs(e.touches[0].clientY - startY) > 5) {
            hasMoved = true;
        }

        pos1 = pos3 - e.touches[0].clientX;
        pos2 = pos4 - e.touches[0].clientY;
        pos3 = e.touches[0].clientX;
        pos4 = e.touches[0].clientY;
        
        if (hasMoved) {
            if (e.cancelable) e.preventDefault(); // Ngăn trình duyệt cuộn trang
            
            let newLeft = windowEl.offsetLeft - pos1;
            let newTop = windowEl.offsetTop - pos2;

            // Giới hạn trong viewport
            const maxLeft = window.innerWidth - windowEl.offsetWidth;
            const maxTop = window.innerHeight - windowEl.offsetHeight;

            newLeft = Math.max(0, Math.min(maxLeft, newLeft));
            newTop = Math.max(0, Math.min(maxTop, newTop));

            windowEl.style.bottom = 'auto';
            windowEl.style.right = 'auto';
            windowEl.style.left = newLeft + 'px';
            windowEl.style.top = newTop + 'px';
        }
    }

    function closeDragElement(e) {
        document.onmouseup = null;
        document.onmousemove = null;

        // Nếu không kéo (hoặc kéo dưới 5px) và có clickCallback thì kích hoạt click
        if (!hasMoved) {
            if (typeof clickCallback === 'function') {
                clickCallback();
            }
        }
    }

    function closeTouchElement(e) {
        document.removeEventListener('touchend', closeTouchElement);
        document.removeEventListener('touchmove', elementTouchDrag);

        // Nếu không di chuyển và có clickCallback thì kích hoạt click
        if (!hasMoved) {
            if (typeof clickCallback === 'function') {
                clickCallback();
            }
        }
    }
}
