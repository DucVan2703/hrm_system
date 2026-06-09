<?php
class SalarySheetModel extends BaseModel {
    public function getSalarySheet($thang, $nam) {
        $stmt = $this->db->prepare("
            SELECT bl.*, nv.ma_nv, nv.ho_ten, nv.chu_ky, pb.ten_pb, cv.ten_cv
            FROM bang_luong bl
            JOIN nhan_vien nv ON bl.id_nhan_vien=nv.id
            LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id
            LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id
            WHERE bl.thang=? AND bl.nam=?
            ORDER BY cv.id ASC, nv.ma_nv ASC
        ");
        $stmt->execute([$thang, $nam]);
        return $stmt->fetchAll();
    }
}
