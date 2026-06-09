<?php
class PayrollModel extends BaseModel {
    public function getPayrollList($thang, $nam) {
        $stmt = $this->db->prepare("
            SELECT nv.id as nv_id, nv.ma_nv, nv.ho_ten, nv.luong_co_ban,
                   pb.ten_pb, cv.ten_cv,
                   cc.so_ngay_lam, cc.so_gio_tang_ca,
                   bl.*
            FROM nhan_vien nv
            LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id
            LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id
            LEFT JOIN cham_cong cc ON nv.id=cc.id_nhan_vien AND cc.thang=? AND cc.nam=?
            LEFT JOIN bang_luong bl ON nv.id=bl.id_nhan_vien AND bl.thang=? AND bl.nam=?
            WHERE nv.trang_thai='Đang làm'
            ORDER BY cv.id ASC, nv.ma_nv ASC
        ");
        $stmt->execute([$thang, $nam, $thang, $nam]);
        return $stmt->fetchAll();
    }

    public function getActiveEmployeesWithAttendance($thang, $nam) {
        $stmt = $this->db->prepare("
            SELECT nv.id, nv.luong_co_ban, 
                   COALESCE(cc.so_ngay_lam, 0) as so_ngay_lam, 
                   COALESCE(cc.so_gio_tang_ca, 0) as so_gio_tang_ca
            FROM nhan_vien nv
            LEFT JOIN cham_cong cc ON nv.id=cc.id_nhan_vien AND cc.thang=? AND cc.nam=?
            WHERE nv.trang_thai='Đang làm'
        ");
        $stmt->execute([$thang, $nam]);
        return $stmt->fetchAll();
    }

    public function checkExistingPayroll($id_nhan_vien, $thang, $nam) {
        $stmt = $this->db->prepare("SELECT id, trang_thai FROM bang_luong WHERE id_nhan_vien=? AND thang=? AND nam=?");
        $stmt->execute([$id_nhan_vien, $thang, $nam]);
        return $stmt->fetch();
    }

    public function insertPayroll($data) {
        $cols = implode(',', array_keys($data));
        $vals = implode(',', array_map(function($k) { return ":$k"; }, array_keys($data)));
        $sql = "INSERT INTO bang_luong ($cols) VALUES ($vals)";
        return $this->db->prepare($sql)->execute($data);
    }

    public function updatePayroll($data, $id_nhan_vien, $thang, $nam) {
        $sets = implode(',', array_map(function($k) { return "$k=:$k"; }, array_keys($data)));
        $sql = "UPDATE bang_luong SET $sets WHERE id_nhan_vien=:id_nv_p AND thang=:thang_p AND nam=:nam_p";
        $data['id_nv_p'] = $id_nhan_vien;
        $data['thang_p'] = $thang;
        $data['nam_p'] = $nam;
        return $this->db->prepare($sql)->execute($data);
    }

    public function approvePayroll($id) {
        $stmt = $this->db->prepare("UPDATE bang_luong SET trang_thai='Đã duyệt' WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function approveAllPayroll($thang, $nam) {
        $stmt = $this->db->prepare("UPDATE bang_luong SET trang_thai='Đã duyệt' WHERE thang=? AND nam=? AND trang_thai='Nháp'");
        return $stmt->execute([$thang, $nam]);
    }

    public function payAllPayroll($thang, $nam) {
        $stmt = $this->db->prepare("UPDATE bang_luong SET trang_thai='Đã thanh toán' WHERE thang=? AND nam=? AND trang_thai='Đã duyệt'");
        return $stmt->execute([$thang, $nam]);
    }
}
