<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmployeesTemplateExport implements FromArray, WithHeadings, WithEvents, WithTitle
{
    public function title(): string
    {
        return 'Nhân viên';
    }

    public function headings(): array
    {
        return [
            'ma_nv',
            'ho_va_ten',
            'chuc_vu',
            'phong_ban',
            'ngay_vao_lam',
            'so_nguoi_phu_thuoc',
            'luong_can_ban',
            'luong_bhxh',
            'chuyen_can',
            'trang_thai',
            'luong_san_pham',
            'ten_phu_cap',
            'phu_cap_chiu_thue',
            'phu_cap_khong_chiu_thue',
        ];
    }

    public function array(): array
    {
        // 2 dòng ví dụ để người dùng tham khảo định dạng
        return [
            ['NV101', 'Nguyễn Văn Mẫu', 'Công nhân SX', 'Sản xuất', '2026-01-15', 1, 8500000,  8500000,  300000, 1, 1500000, 'Phụ cấp xăng xe', 0,      500000],
            ['NV102', 'Trần Thị Ví Dụ', 'Tổ trưởng',    'Sản xuất', '2025-09-01', 2, 14000000, 14000000, 500000, 1, 0,       'Phụ cấp ăn trưa', 730000, 0],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'N';
                $headerRange = "A1:{$lastCol}1";

                // Style header
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1C1A17']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '8A7D68']]],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Auto-size columns
                foreach (range('A', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Border for example rows
                $sheet->getStyle("A2:{$lastCol}3")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'C9BB9A']]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FAF3E1']],
                ]);

                // Hướng dẫn ở dòng 5+
                $sheet->setCellValue('A5', '── Hướng dẫn ──');
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(10);

                $help = [
                    'ma_nv: BẮT BUỘC. Mã định danh duy nhất. Nếu mã đã tồn tại, hệ thống sẽ CẬP NHẬT thông tin.',
                    'ho_va_ten: BẮT BUỘC.',
                    'ngay_vao_lam: định dạng YYYY-MM-DD (hoặc để trống).',
                    'so_nguoi_phu_thuoc: số nguyên (0, 1, 2...).',
                    'luong_can_ban, luong_bhxh, chuyen_can: số (VND), không cần dấu phẩy.',
                    'trang_thai: 1 = Đang làm, 0 = Nghỉ.',
                    'luong_san_pham: VND. Ghi vào tháng/năm hiện tại lúc import; để 0 nếu không có. Có thể chỉnh sau trong Phiếu Lương.',
                    'ten_phu_cap: tên phụ cấp (VD: "Phụ cấp xăng xe"). Bắt buộc nếu có cột phu_cap_chiu_thue hoặc phu_cap_khong_chiu_thue > 0.',
                    'phu_cap_chiu_thue / phu_cap_khong_chiu_thue: VND. Cộng vào tổng thu nhập tháng; cột "chịu thuế" sẽ vào TN tính thuế TNCN.',
                    'Có thể xoá 2 dòng ví dụ trước khi import.',
                ];
                foreach ($help as $i => $line) {
                    $row = 6 + $i;
                    $sheet->setCellValue("A{$row}", $line);
                    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setSize(10)->setItalic(true);
                    $sheet->getStyle("A{$row}")->getFont()->getColor()->setRGB('5A5040');
                }

                // Freeze header row
                $sheet->freezePane('A2');
            },
        ];
    }
}
