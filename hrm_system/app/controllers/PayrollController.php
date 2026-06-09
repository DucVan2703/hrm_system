<?php
class PayrollController extends BaseController {
    public function index() {
        Helper::requireKetoan();

        $model = new PayrollModel();
        $thang = (int)($_GET['thang'] ?? date('n'));
        $nam = (int)($_GET['nam'] ?? date('Y'));

        // Cấu hình hằng số (chuyển đổi từ hằng số cũ)
        $ngay_chuan = 26;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['type'] ?? '';

            if ($type === 'tinh_tat_ca') {
                $t = (int)$_POST['thang']; 
                $n = (int)$_POST['nam'];
                $nvList = $model->getActiveEmployeesWithAttendance($t, $n);
                $count = 0;

                foreach ($nvList as $nv) {
                    $luong_theo_cong = round($nv['so_ngay_lam'] > 0 ? ($nv['luong_co_ban'] / $ngay_chuan * $nv['so_ngay_lam']) : 0);
                    $bhxh = round($nv['luong_co_ban'] * 0.08);
                    $bhyt = round($nv['luong_co_ban'] * 0.015);
                    $pc_an = 730000; 
                    $pc_xe = 500000;
                    $tong_thu_nhap = $luong_theo_cong + $pc_an + $pc_xe;
                    $tncn = round(($tong_thu_nhap - $bhxh - $bhyt) * 0.05);
                    if ($tncn < 0) $tncn = 0;
                    $tong_khau_tru = $bhxh + $bhyt + $tncn;
                    $thuc_linh = $tong_thu_nhap - $tong_khau_tru;

                    $existing = $model->checkExistingPayroll($nv['id'], $t, $n);
                    if ($existing && $existing['trang_thai'] !== 'Nháp') continue;

                    $payrollData = [
                        'luong_co_ban' => $nv['luong_co_ban'],
                        'so_ngay_lam' => $nv['so_ngay_lam'],
                        'so_ngay_chuan' => $ngay_chuan,
                        'luong_theo_cong' => $luong_theo_cong,
                        'phu_cap_an_trua' => $pc_an,
                        'phu_cap_xang_xe' => $pc_xe,
                        'bao_hiem_xa_hoi' => $bhxh,
                        'bao_hiem_y_te' => $bhyt,
                        'thue_tncn' => $tncn,
                        'tong_thu_nhap' => $tong_thu_nhap,
                        'tong_khau_tru' => $tong_khau_tru,
                        'thuc_linh' => $thuc_linh
                    ];

                    if ($existing) {
                        $model->updatePayroll($payrollData, $nv['id'], $t, $n);
                    } else {
                        $payrollData['id_nhan_vien'] = $nv['id'];
                        $payrollData['thang'] = $t;
                        $payrollData['nam'] = $n;
                        $payrollData['trang_thai'] = 'Nháp';
                        $model->insertPayroll($payrollData);
                    }
                    $count++;
                }

                $_SESSION['flash'] = ['type' => 'success', 'message' => "Đã tính lương cho $count nhân viên tháng $t/$n!"];
                $this->redirect("payroll?thang=$t&nam=$n");
            }

            if ($type === 'save_one') {
                $id_nv = (int)$_POST['id_nhan_vien'];
                $t = (int)$_POST['thang']; 
                $n = (int)$_POST['nam'];
                $data = [
                    'luong_co_ban' => (float)$_POST['luong_co_ban'],
                    'so_ngay_lam' => (float)$_POST['so_ngay_lam'],
                    'so_ngay_chuan' => (int)($_POST['so_ngay_chuan'] ?? $ngay_chuan),
                    'luong_theo_cong' => (float)$_POST['luong_theo_cong'],
                    'phu_cap_an_trua' => (float)$_POST['phu_cap_an_trua'],
                    'phu_cap_xang_xe' => (float)$_POST['phu_cap_xang_xe'],
                    'phu_cap_khac' => (float)$_POST['phu_cap_khac'],
                    'thuong_kpi' => (float)$_POST['thuong_kpi'],
                    'thuong_khac' => (float)$_POST['thuong_khac'],
                    'phat_di_muon' => (float)$_POST['phat_di_muon'],
                    'khau_tru_khac' => (float)$_POST['khau_tru_khac'],
                    'bao_hiem_xa_hoi' => (float)$_POST['bao_hiem_xa_hoi'],
                    'bao_hiem_y_te' => (float)$_POST['bao_hiem_y_te'],
                    'thue_tncn' => (float)$_POST['thue_tncn'],
                    'ghi_chu' => Helper::sanitize($_POST['ghi_chu'] ?? ''),
                ];
                $tong_thu = $data['luong_theo_cong'] + $data['phu_cap_an_trua'] + $data['phu_cap_xang_xe'] + $data['phu_cap_khac'] + $data['thuong_kpi'] + $data['thuong_khac'];
                $tong_khatr = $data['phat_di_muon'] + $data['khau_tru_khac'] + $data['bao_hiem_xa_hoi'] + $data['bao_hiem_y_te'] + $data['thue_tncn'];
                $data['tong_thu_nhap'] = $tong_thu;
                $data['tong_khau_tru'] = $tong_khatr;
                $data['thuc_linh'] = $tong_thu - $tong_khatr;

                $existing = $model->checkExistingPayroll($id_nv, $t, $n);
                if ($existing) {
                    $model->updatePayroll($data, $id_nv, $t, $n);
                } else {
                    $data['id_nhan_vien'] = $id_nv; 
                    $data['thang'] = $t; 
                    $data['nam'] = $n; 
                    $data['trang_thai'] = 'Nháp';
                    $model->insertPayroll($data);
                }
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Đã lưu bảng lương!'];
                $this->redirect("payroll?thang=$t&nam=$n");
            }

            if ($type === 'duyet') {
                $id = (int)$_POST['id'];
                $model->approvePayroll($id);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Đã duyệt bảng lương!'];
                $this->redirect("payroll?thang=$thang&nam=$nam");
            }

            if ($type === 'duyet_tat_ca') {
                $t = (int)$_POST['thang']; 
                $n = (int)$_POST['nam'];
                $model->approveAllPayroll($t, $n);
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Đã duyệt toàn bộ bảng lương tháng $t/$n!"];
                $this->redirect("payroll?thang=$t&nam=$n");
            }

            if ($type === 'thanh_toan_tat_ca') {
                $t = (int)$_POST['thang']; 
                $n = (int)$_POST['nam'];
                $model->payAllPayroll($t, $n);
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Đã xác nhận thanh toán toàn bộ bảng lương tháng $t/$n!"];
                $this->redirect("payroll?thang=$t&nam=$n");
            }
        }

        $list = $model->getPayrollList($thang, $nam);
        $months = range(1, 12); 
        $years = range(date('Y') - 2, date('Y') + 1);

        $this->view('payroll/index', [
            'pageTitle' => 'Tính lương',
            'list' => $list,
            'thang' => $thang,
            'nam' => $nam,
            'months' => $months,
            'years' => $years,
            'ngay_chuan' => $ngay_chuan
        ]);
    }
}
