<?php

namespace App\Imports;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Parse-only: chỉ đọc file Excel và phân loại
 *   - parsedRows: ['NV001' => employee payload, 'NV002' => ...]
 *   - parsedExtras: ['NV001' => monthly extras (product_salary + allowance)]
 *   - duplicateCodes: mã đã tồn tại trong DB
 *   - newCodes: mã sẽ tạo mới
 *   - errors: thông báo lỗi từng dòng
 * Việc ghi DB do controller xử lý (sau khi user chọn skip/overwrite).
 */
class EmployeesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    /** @var array<string, array> mã NV → employee payload (cho bảng employees) */
    public array $parsedRows = [];

    /** @var array<string, array> mã NV → monthly extras (product_salary + allowance) */
    public array $parsedExtras = [];

    /** @var string[] mã NV đã có sẵn trong DB */
    public array $duplicateCodes = [];

    /** @var string[] mã NV sẽ tạo mới */
    public array $newCodes = [];

    /** @var string[] thông báo lỗi từng dòng */
    public array $errors = [];

    private array $aliases = [
        'ma_nv' => 'employee_code', 'ma' => 'employee_code', 'employee_code' => 'employee_code',
        'ho_va_ten' => 'full_name', 'ho_ten' => 'full_name', 'full_name' => 'full_name',
        'chuc_vu' => 'position', 'position' => 'position',
        'phong_ban' => 'department', 'department' => 'department',
        'ngay_vao_lam' => 'joined_date', 'joined_date' => 'joined_date',
        'so_nguoi_phu_thuoc' => 'dependents', 'npt' => 'dependents', 'dependents' => 'dependents',
        'trang_thai' => 'is_active', 'is_active' => 'is_active', 'dang_lam' => 'is_active',
        'luong_can_ban' => 'basic_salary', 'basic_salary' => 'basic_salary',
        'luong_bhxh' => 'bhxh_salary', 'muc_bhxh' => 'bhxh_salary', 'bhxh_salary' => 'bhxh_salary',
        'chuyen_can' => 'diligence_bonus', 'tien_chuyen_can' => 'diligence_bonus', 'diligence_bonus' => 'diligence_bonus',

        // Monthly extras — gắn vào tháng/năm hiện tại lúc import
        'luong_san_pham' => 'product_salary', 'lsp' => 'product_salary', 'product_salary' => 'product_salary',
        'ten_phu_cap' => 'allowance_name', 'phu_cap' => 'allowance_name', 'allowance_name' => 'allowance_name',
        'phu_cap_chiu_thue' => 'allowance_taxable', 'pcct' => 'allowance_taxable', 'allowance_taxable' => 'allowance_taxable',
        'phu_cap_khong_chiu_thue' => 'allowance_non_taxable', 'pckct' => 'allowance_non_taxable', 'allowance_non_taxable' => 'allowance_non_taxable',
    ];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $data = $this->mapRow($row->toArray());

            if (empty($data['employee_code']) || empty($data['full_name'])) {
                $this->errors[] = "Dòng {$rowNum}: thiếu mã NV hoặc họ tên — bỏ qua.";
                continue;
            }

            $code = (string) $data['employee_code'];
            if (isset($this->parsedRows[$code])) {
                $this->errors[] = "Dòng {$rowNum}: mã {$code} bị trùng trong file Excel — chỉ giữ bản ghi đầu tiên.";
                continue;
            }

            $this->parsedRows[$code] = $this->preparePayload($data);
            $extras = $this->prepareExtras($data);
            if (!empty($extras)) {
                $this->parsedExtras[$code] = $extras;
            }
        }

        if (!empty($this->parsedRows)) {
            $codes = array_keys($this->parsedRows);
            $this->duplicateCodes = Employee::whereIn('employee_code', $codes)
                ->pluck('employee_code')
                ->all();
            $this->newCodes = array_values(array_diff($codes, $this->duplicateCodes));
        }
    }

    private function mapRow(array $raw): array
    {
        $mapped = [];
        foreach ($raw as $key => $value) {
            $normalized = $this->normalizeKey((string) $key);
            $field = $this->aliases[$normalized] ?? null;
            if ($field) {
                $mapped[$field] = is_string($value) ? trim($value) : $value;
            }
        }
        return $mapped;
    }

    private function normalizeKey(string $key): string
    {
        $key = mb_strtolower($key);
        $key = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/u', 'a', $key);
        $key = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $key);
        $key = preg_replace('/[íìỉĩị]/u', 'i', $key);
        $key = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $key);
        $key = preg_replace('/[úùủũụưứừửữự]/u', 'u', $key);
        $key = preg_replace('/[ýỳỷỹỵ]/u', 'y', $key);
        $key = str_replace('đ', 'd', $key);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        return trim($key, '_');
    }

    private function preparePayload(array $data): array
    {
        $payload = [
            'full_name' => $data['full_name'] ?? null,
            'position' => $data['position'] ?? null,
            'department' => $data['department'] ?? null,
            'basic_salary' => $this->toNumber($data['basic_salary'] ?? 0),
            'bhxh_salary' => $this->toNumber($data['bhxh_salary'] ?? 0),
            'diligence_bonus' => $this->toNumber($data['diligence_bonus'] ?? 0),
            'dependents' => (int) ($data['dependents'] ?? 0),
            'is_active' => $this->toBool($data['is_active'] ?? true),
        ];

        if (!empty($data['joined_date'])) {
            $payload['joined_date'] = $this->toDate($data['joined_date']);
        }

        return array_filter($payload, fn ($v) => $v !== null);
    }

    /**
     * Tách các trường liên quan tới dữ liệu tháng (lương SP + phụ cấp).
     * Trả về mảng rỗng nếu không có gì cần xử lý.
     */
    private function prepareExtras(array $data): array
    {
        $extras = [];

        $productSalary = $this->toNumber($data['product_salary'] ?? 0);
        if ($productSalary > 0) {
            $extras['product_salary'] = $productSalary;
        }

        $allowanceName = isset($data['allowance_name']) ? trim((string) $data['allowance_name']) : '';
        $taxable = $this->toNumber($data['allowance_taxable'] ?? 0);
        $nonTaxable = $this->toNumber($data['allowance_non_taxable'] ?? 0);
        if ($allowanceName !== '' && ($taxable > 0 || $nonTaxable > 0)) {
            $extras['allowance_name'] = $allowanceName;
            if ($taxable > 0) {
                $extras['allowance_taxable'] = $taxable;
            }
            if ($nonTaxable > 0) {
                $extras['allowance_non_taxable'] = $nonTaxable;
            }
        }

        return $extras;
    }

    private function toNumber(mixed $v): float
    {
        if (is_numeric($v)) return (float) $v;
        if (is_string($v)) {
            $v = preg_replace('/[^\d.-]/', '', str_replace(',', '', $v));
            return is_numeric($v) ? (float) $v : 0.0;
        }
        return 0.0;
    }

    private function toBool(mixed $v): bool
    {
        if (is_bool($v)) return $v;
        if (is_numeric($v)) return (int) $v === 1;
        $s = mb_strtolower(trim((string) $v));
        return in_array($s, ['1', 'true', 'yes', 'y', 'dang_lam', 'đang làm', 'active', 'hoat dong', 'hoạt động'], true);
    }

    private function toDate(mixed $v): ?string
    {
        if (empty($v)) return null;
        if (is_numeric($v)) {
            try {
                return Carbon::createFromTimestamp(($v - 25569) * 86400)->format('Y-m-d');
            } catch (\Throwable) { /* fall through */ }
        }
        try {
            return Carbon::parse($v)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
