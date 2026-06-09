<?php
class ChatBotModel extends BaseModel {
    public function getList() {
        return $this->db->query("SELECT * FROM chatbot_faq ORDER BY id DESC")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM chatbot_faq WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function save($data, $id = null) {
        if ($id) {
            $stmt = $this->db->prepare("UPDATE chatbot_faq SET keywords = ?, reply = ?, suggestions = ?, nguoi_tao = ? WHERE id = ?");
            return $stmt->execute([$data['keywords'], $data['reply'], $data['suggestions'], $data['nguoi_tao'], $id]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO chatbot_faq (keywords, reply, suggestions, nguoi_tao) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$data['keywords'], $data['reply'], $data['suggestions'], $data['nguoi_tao']]);
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM chatbot_faq WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
