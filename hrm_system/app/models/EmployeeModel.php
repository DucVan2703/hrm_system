<?php

class EmployeeModel extends BaseModel
{
    public function getList($search = '', $pb_filter = 0, $tt_filter = '', $offset = 0, $limit = 10)
    {
        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(nv.ho_ten LIKE ? OR nv.ma_nv LIKE ? OR nv.email LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
        }

        if ($pb_filter) {
            $where[] = "nv.id_phong_ban = ?";
            $params[] = $pb_filter;
        }

        if ($tt_filter) {
            $where[] = "nv.trang_thai = ?";
            $params[] = $tt_filter;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT nv.*, pb.ten_pb, cv.ten_cv
                FROM nhan_vien nv
                LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id
                LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id
                $whereStr
                ORDER BY cv.id ASC, nv.id DESC
                LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getTotal($search = '', $pb_filter = 0, $tt_filter = '')
    {
        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(nv.ho_ten LIKE ? OR nv.ma_nv LIKE ? OR nv.email LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
        }

        if ($pb_filter) {
            $where[] = "nv.id_phong_ban = ?";
            $params[] = $pb_filter;
        }

        if ($tt_filter) {
            $where[] = "nv.trang_thai = ?";
            $params[] = $tt_filter;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) FROM nhan_vien nv $whereStr";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    public function getPhongBans()
    {
        return $this->db->query(
            "SELECT * FROM phong_ban WHERE trang_thai = 1 ORDER BY ten_pb"
        )->fetchAll();
    }

    public function getChucVus()
    {
        return $this->db->query(
            "SELECT * FROM chuc_vu WHERE trang_thai = 1 ORDER BY ten_cv"
        )->fetchAll();
    }

    public function save($data, $id = 0)
    {
        if ($id > 0) {

            $cols = [];

            foreach ($data as $k => $v) {
                $cols[] = "$k = :$k";
            }

            $sql = "UPDATE nhan_vien
                    SET " . implode(',', $cols) . "
                    WHERE id = :id";

            $data['id'] = $id;

            return $this->db->prepare($sql)->execute($data);

        } else {

            $data['ma_nv'] = $this->genMaNV();

            $cols = implode(',', array_keys($data));
            $vals = ':' . implode(',:', array_keys($data));

            $sql = "INSERT INTO nhan_vien ($cols)
                    VALUES ($vals)";

            return $this->db->prepare($sql)->execute($data);
        }
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM nhan_vien WHERE id = ?"
        );

        return $stmt->execute([$id]);
    }

    public function getById($id)
    {
        $sql = "SELECT nv.*, pb.ten_pb, cv.ten_cv
                FROM nhan_vien nv
                LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id
                LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id
                WHERE nv.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    private function genMaNV()
    {
        $prefix = 'TD';

        $stmt = $this->db->query(
            "SELECT ma_nv
             FROM nhan_vien
             ORDER BY id DESC
             LIMIT 1"
        );

        $last = $stmt->fetchColumn();

        if ($last && preg_match('/TD(\d+)/', $last, $m)) {
            $num = intval($m[1]) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}