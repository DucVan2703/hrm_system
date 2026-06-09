<?php
class DashboardModel extends BaseModel {
    public function getTongQuan() {
        $tq = [];
        
        $tq['tong_nv'] = (int)$this->db->query("SELECT COUNT(*) FROM nhan_vien")->fetchColumn();
        $tq['dang_lam'] = (int)$this->db->query("SELECT COUNT(*) FROM nhan_vien WHERE trang_thai = 'Đang làm'")->fetchColumn();
        $tq['nghi_viec'] = (int)$this->db->query("SELECT COUNT(*) FROM nhan_vien WHERE trang_thai = 'Đã nghỉ việc'")->fetchColumn();
        
        $thang = date('n');
        $nam = date('Y');
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(thuc_linh), 0) FROM bang_luong WHERE thang = ? AND nam = ?");
        $stmt->execute([$thang, $nam]);
        $tq['quy_luong'] = (float)$stmt->fetchColumn();
        
        $tq['hop_dong_hh'] = (int)$this->db->query("SELECT COUNT(*) FROM hop_dong WHERE trang_thai = 'Hiệu lực'")->fetchColumn();
        $tq['don_cho_duyet'] = (int)$this->db->query("SELECT COUNT(*) FROM don_nghi_phep WHERE trang_thai = 'Chờ duyệt'")->fetchColumn();
        
        return $tq;
    }

    public function getQuyLuongSauThang() {
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $ts = strtotime("-$i months");
            $m = date('n', $ts); 
            $y = date('Y', $ts);
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(thuc_linh), 0) as tong FROM bang_luong WHERE thang = ? AND nam = ?");
            $stmt->execute([$m, $y]);
            $tong = $stmt->fetchColumn();
            $chartData[] = ['label' => "T$m/$y", 'value' => (float)$tong];
        }
        return $chartData;
    }

    public function getNhanVienMoi() {
        return $this->db->query("SELECT nv.*, pb.ten_pb, cv.ten_cv FROM nhan_vien nv LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id ORDER BY nv.ngay_tao DESC LIMIT 5")->fetchAll();
    }

    public function getPhanBoPhongBan() {
        return $this->db->query("SELECT pb.ten_pb, COUNT(nv.id) as so_nv FROM phong_ban pb LEFT JOIN nhan_vien nv ON pb.id=nv.id_phong_ban AND nv.trang_thai='Đang làm' GROUP BY pb.id ORDER BY so_nv DESC")->fetchAll();
    }
}
