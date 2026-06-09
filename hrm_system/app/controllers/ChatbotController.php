<?php
class ChatbotController extends BaseController {
    public function index() {
        Helper::requireOnlyAdmin();

        $model = new ChatBotModel();

        // Xử lý Xóa
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $model->delete($id);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Đã xóa luật phản hồi thành công!'];
            $this->redirect('chatbot');
        }

        // Xử lý Lưu thêm / sửa
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $data = [
                'keywords' => Helper::sanitize($_POST['keywords'] ?? ''),
                'reply' => Helper::sanitize($_POST['reply'] ?? ''),
                'suggestions' => Helper::sanitize($_POST['suggestions'] ?? ''),
                'nguoi_tao' => Helper::sanitize($_POST['nguoi_tao'] ?? 'Giám đốc')
            ];

            if (empty($data['keywords']) || empty($data['reply'])) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Vui lòng nhập đầy đủ các từ khóa và nội dung câu trả lời!'];
            } else {
                $model->save($data, $id);
                $_SESSION['flash'] = ['type' => 'success', 'message' => $id ? 'Đã cập nhật luật phản hồi thành công!' : 'Đã thêm luật phản hồi mới thành công!'];
            }
            $this->redirect('chatbot');
        }

        // Đọc luật để hiển thị
        $list = $model->getList();

        // Đọc luật cần sửa
        $editFaq = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $editId = (int)$_GET['id'];
            $editFaq = $model->getById($editId);
        }

        $this->view('chatbot/index', [
            'pageTitle' => 'Quản lý Trợ lý ảo TDU',
            'list' => $list,
            'editFaq' => $editFaq
        ]);
    }
}
