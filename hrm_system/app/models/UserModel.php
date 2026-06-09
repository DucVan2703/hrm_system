<?php
class UserModel extends BaseModel {
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT tk.*, nv.ho_ten FROM tai_khoan tk LEFT JOIN nhan_vien nv ON tk.id_nhan_vien = nv.id WHERE tk.ten_dang_nhap = ? AND tk.trang_thai = 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE tai_khoan SET lan_dang_nhap_cuoi = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    }
}
