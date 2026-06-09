<?php

class AdminCrudModel extends BaseModel
{
    public function all($table, $orderBy = 'id DESC')
    {
        return $this->db->query("SELECT * FROM {$table} ORDER BY {$orderBy}")->fetchAll();
    }

    public function find($table, $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function insert($table, array $data)
    {
        $cols = array_keys($data);
        $sql = "INSERT INTO {$table} (" . implode(',', $cols) . ") VALUES (:" . implode(',:', $cols) . ")";
        return $this->db->prepare($sql)->execute($data);
    }

    public function update($table, array $data, $id)
    {
        $sets = [];
        foreach (array_keys($data) as $col) {
            $sets[] = "{$col} = :{$col}";
        }
        $data['id'] = (int)$id;
        $sql = "UPDATE {$table} SET " . implode(',', $sets) . " WHERE id = :id";
        return $this->db->prepare($sql)->execute($data);
    }

    public function delete($table, $id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function employees()
    {
        return $this->db->query("SELECT id, ma_nv, ho_ten FROM nhan_vien ORDER BY ho_ten")->fetchAll();
    }

    public function users()
    {
        return $this->db->query("
            SELECT tk.*, nv.ho_ten, nv.ma_nv, pb.ten_pb, pb.id AS id_phong_ban
            FROM tai_khoan tk
            LEFT JOIN nhan_vien nv ON tk.id_nhan_vien = nv.id
            LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id
            ORDER BY tk.id DESC
        ")->fetchAll();
    }

    public function contracts()
    {
        return $this->db->query("
            SELECT hd.*, nv.ho_ten, nv.ma_nv
            FROM hop_dong hd
            JOIN nhan_vien nv ON hd.id_nhan_vien = nv.id
            ORDER BY hd.id DESC
        ")->fetchAll();
    }

    public function leaves()
    {
        return $this->db->query("
            SELECT dp.*, nv.ho_ten, nv.ma_nv
            FROM don_nghi_phep dp
            JOIN nhan_vien nv ON dp.id_nhan_vien = nv.id
            ORDER BY dp.id DESC
        ")->fetchAll();
    }

    public function attendance($month, $year)
    {
        $stmt = $this->db->prepare("
            SELECT 
                nv.id AS id_nhan_vien, 
                nv.ma_nv, 
                nv.ho_ten, 
                nv.hinh_anh,
                pb.ten_pb,
                cc.id AS id_cham_cong,
                cc.so_ngay_lam,
                cc.so_ngay_nghi,
                cc.so_ngay_phep,
                cc.so_ngay_vang,
                cc.so_gio_tang_ca,
                cc.ghi_chu
            FROM nhan_vien nv
            LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id
            LEFT JOIN cham_cong cc ON nv.id = cc.id_nhan_vien AND cc.thang = ? AND cc.nam = ?
            WHERE nv.trang_thai = 'Đang làm'
            ORDER BY nv.ma_nv
        ");
        $stmt->execute([(int)$month, (int)$year]);
        return $stmt->fetchAll();
    }

    public function employeeExists($id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM nhan_vien WHERE id = ?");
        $stmt->execute([(int)$id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function saveAttendance(array $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO cham_cong
                (id_nhan_vien, thang, nam, so_ngay_lam, so_ngay_nghi, so_ngay_phep, so_ngay_vang, so_gio_tang_ca, ghi_chu, nguoi_cap_nhat)
            VALUES
                (:id_nhan_vien, :thang, :nam, :so_ngay_lam, :so_ngay_nghi, :so_ngay_phep, :so_ngay_vang, :so_gio_tang_ca, :ghi_chu, :nguoi_cap_nhat)
            ON DUPLICATE KEY UPDATE
                so_ngay_lam = VALUES(so_ngay_lam),
                so_ngay_nghi = VALUES(so_ngay_nghi),
                so_ngay_phep = VALUES(so_ngay_phep),
                so_ngay_vang = VALUES(so_ngay_vang),
                so_gio_tang_ca = VALUES(so_gio_tang_ca),
                ghi_chu = VALUES(ghi_chu),
                nguoi_cap_nhat = VALUES(nguoi_cap_nhat)
        ");
        return $stmt->execute($data);
    }

    public function dashboardReports($month, $year)
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(thuc_linh),0) FROM bang_luong WHERE thang=? AND nam=?");
        $stmt->execute([(int)$month, (int)$year]);
        $payroll = (float)$stmt->fetchColumn();

        return [
            'employees' => (int)$this->db->query("SELECT COUNT(*) FROM nhan_vien")->fetchColumn(),
            'active' => (int)$this->db->query("SELECT COUNT(*) FROM nhan_vien WHERE trang_thai='Đang làm'")->fetchColumn(),
            'departments' => (int)$this->db->query("SELECT COUNT(*) FROM phong_ban WHERE trang_thai=1")->fetchColumn(),
            'payroll' => $payroll,
            'leaves_pending' => (int)$this->db->query("SELECT COUNT(*) FROM don_nghi_phep WHERE trang_thai='Chờ duyệt'")->fetchColumn(),
        ];
    }
}
