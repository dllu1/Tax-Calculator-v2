# Hệ thống Tính thuế TNCN & Quản lý lương

> 🇻🇳 Tiếng Việt (bên dưới) · 🇬🇧 [English version](#english-version)

Ứng dụng web Laravel 11 tính thuế thu nhập cá nhân và quản lý lương theo quy định Việt Nam.

## Tính năng

- Quản lý nhân viên (mã NV, lương căn bản, mức BHXH, số người phụ thuộc...)
- Chấm công theo ngày (thường / chủ nhật ×2 / nghỉ phép / nghỉ không phép)
- Tăng ca (3h = ½ ngày lương)
- Lương sản phẩm, phụ cấp (chịu thuế / không chịu thuế), tạm ứng
- Tự động tính BHXH 10,5%, thuế TNCN theo 5 bậc lũy tiến
- Phiếu lương chi tiết theo từng nhân viên / từng tháng
- **Cấu hình động công thức tính thuế &amp; lương** qua trang `/settings`:
  giảm trừ gia cảnh, tỉ lệ BHXH, các bậc thuế lũy tiến, số công chuẩn,
  tiền ăn, hệ số Chủ nhật &amp; tăng ca... — không cần sửa code khi luật thay đổi.

## Yêu cầu hệ thống

- **PHP ≥ 8.2** (Laravel 11 yêu cầu)
- **MySQL ≥ 5.7** (XAMPP / MariaDB đều OK)
- **Composer ≥ 2.5**

## Hướng dẫn cài đặt

### Bước 1: Cài PHP 8.2 (nếu XAMPP đang cũ)

Lựa chọn dễ nhất: **tải XAMPP 8.2.x** tại https://www.apachefriends.org/download.html

Sau khi cài, kiểm tra:
```bash
php -v   # phải in: PHP 8.2.x hoặc cao hơn
```

### Bước 2: Cài đặt dependencies

```bash
cd C:\xampp\htdocs\tax-calculator
composer install
```

### Bước 3: Tạo database

Vào phpMyAdmin (http://localhost/phpmyadmin) → **New** → tạo database tên **`tax_calculator`** với collation `utf8mb4_unicode_ci`.

Hoặc dùng SQL:
```sql
CREATE DATABASE tax_calculator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Bước 4: Cấu hình `.env`

File `.env` đã được tạo sẵn với cấu hình MySQL mặc định của XAMPP (root, không mật khẩu). Nếu khác, hãy sửa:

```env
DB_DATABASE=tax_calculator
DB_USERNAME=root
DB_PASSWORD=
```

Sau đó sinh APP_KEY:
```bash
php artisan key:generate
```

### Bước 5: Tạo bảng + dữ liệu mẫu

```bash
php artisan migrate --seed
```

Lệnh này sẽ:
- Tạo 8 bảng: `employees`, `attendances`, `overtimes`, `product_salaries`, `allowances`, `advances`, `payrolls`, `settings`
- Tạo 5 nhân viên mẫu (NV001 - NV005) kèm dữ liệu chấm công 20 ngày
- Tự động seed các tham số mặc định cho bảng `settings` (lần đầu vào `/settings`)

### Bước 6: Chạy server

```bash
php artisan serve
```

Mở trình duyệt: **http://localhost:8000**

## Công thức tính lương & thuế

### Tiền lương thực nhận
```
Lương ngày     = (Lương căn bản / 26) × (Ngày thường + Ngày CN × 2)
Lương tăng ca  = (Lương căn bản / 26 / 2) × Số ca tăng ca 3h
Ăn giữa ca     = 30.000 × Số ngày làm
Ăn tăng ca     = 30.000 × Số ca tăng ca
Chuyên cần     = Nếu đủ công (không nghỉ X) → cộng tiền chuyên cần

TỔNG THỰC NHẬN = Lương ngày + Tăng ca + Ăn ca + Ăn TC
               + Lương SP + Chuyên cần + Phụ cấp (cả 2 loại)
```

### Thuế TNCN
```
TN tính thuế   = Lương căn bản + Lương SP + Phụ cấp chịu thuế
TN chịu thuế   = TN tính thuế − 11.000.000 − (4.400.000 × NPT) − BHXH 10,5%
```

**Biểu thuế lũy tiến (rút gọn):**

| Bậc | TN chịu thuế/tháng | Công thức |
|---|---|---|
| 1 | ≤ 10 triệu | TNCT × 5% |
| 2 | 10 - 30 triệu | TNCT × 10% − 500.000 |
| 3 | 30 - 60 triệu | TNCT × 20% − 3.500.000 |
| 4 | 60 - 100 triệu | TNCT × 30% − 9.500.000 |
| 5 | > 100 triệu | TNCT × 35% − 14.500.000 |

### Tiền lương còn lại
```
CÒN LẠI = Tổng thực nhận − Tạm ứng − BHXH 10,5% − Thuế TNCN
```

## Cấu hình công thức (`/settings`)

Toàn bộ con số trong các công thức trên đều có thể chỉnh sửa **trực tiếp trên web**,
không cần đụng đến code. Khi luật thuế hoặc chính sách công ty thay đổi, vào menu
**Cấu hình** để cập nhật — bảng lương sẽ tự tính lại theo công thức mới ngay lần xem tiếp theo.

Trang `/settings` có 3 tab:

**1. Công thức thuế TNCN**

| Tham số | Mặc định | Ý nghĩa |
|---|---|---|
| `tax.personal_deduction` | 11.000.000 | Giảm trừ bản thân / tháng |
| `tax.dependent_deduction` | 4.400.000 | Giảm trừ / người phụ thuộc / tháng |
| `tax.bhxh_rate` | 0.105 | Tỉ lệ BHXH/BHYT/BHTN nhân viên đóng |

**2. Biểu thuế lũy tiến** — thêm / xoá / sửa từng bậc thuế (Giới hạn / Thuế suất / Khấu trừ).
Đặt **Giới hạn = 0** cho bậc cao nhất (không giới hạn). Hệ thống tự sắp xếp lại
theo `limit` tăng dần khi lưu.

**3. Công thức tính lương**

| Tham số | Mặc định | Ý nghĩa |
|---|---|---|
| `payroll.standard_days` | 26 | Số công chuẩn / tháng |
| `payroll.meal_per_day` | 30.000 | Tiền ăn / ngày công |
| `payroll.meal_per_ot_shift` | 30.000 | Tiền ăn / ca tăng ca |
| `payroll.sunday_multiplier` | 2 | Hệ số công Chủ nhật |
| `payroll.overtime_multiplier` | 0.5 | Hệ số 1 ca tăng ca (theo ngày công) |

Có nút **Khôi phục mặc định** để đưa toàn bộ tham số về giá trị gốc.

## Cấu trúc thư mục

```
tax-calculator/
├── app/
│   ├── Http/Controllers/    HomeController, EmployeeController, AttendanceController, PayrollController, SettingController
│   ├── Models/              Employee, Attendance, Overtime, ProductSalary, Allowance, Advance, Payroll, Setting
│   └── Services/            TaxService, PayrollService, SettingService
├── database/
│   ├── migrations/          8 file migration
│   └── seeders/             DatabaseSeeder (5 NV mẫu)
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── home.blade.php
│   ├── employees/
│   ├── attendance/
│   ├── payroll/
│   └── settings/
├── routes/web.php
└── .env
```

## Các URL chính

| URL | Chức năng |
|---|---|
| `/` | Trang chủ + ô tìm mã NV |
| `/employees` | Danh sách / CRUD nhân viên |
| `/attendance?year=YYYY&month=M` | Chấm công cả tháng |
| `/payroll?year=YYYY&month=M` | Bảng lương toàn công ty |
| `/payroll/{id}/{year}/{month}` | Phiếu lương chi tiết 1 NV |
| `/settings` | Cấu hình công thức tính thuế &amp; lương |

## Mã NV mẫu sau seed

| Mã | Họ tên | Lương căn bản | Chức vụ |
|---|---|---|---|
| NV001 | Nguyễn Văn An | 8.000.000 | Công nhân SX |
| NV002 | Trần Thị Bích | 15.000.000 | Tổ trưởng |
| NV003 | Lê Quốc Cường | 25.000.000 | Quản đốc |
| NV004 | Phạm Thu Dung | 12.000.000 | Kế toán |
| NV005 | Hoàng Minh Đức | 50.000.000 | Giám đốc |

## Reset database

Nếu muốn xóa toàn bộ và làm lại:
```bash
php artisan migrate:fresh --seed
```

---

## English version

# Vietnam Personal Income Tax (PIT) & Payroll Management System

A Laravel 11 web application that calculates Vietnamese personal income tax (PIT) and manages monthly payroll according to current Vietnam regulations.

## Features

- Employee management (employee code, base salary, social-insurance level, number of dependants, etc.)
- Daily attendance tracking (normal day / Sunday ×2 / paid leave / unpaid leave)
- Overtime tracking (3h shift = ½ day of salary)
- Piece-rate wages, allowances (taxable / non-taxable), salary advances
- Automatic calculation of 10.5% social insurance and PIT under the 5-bracket progressive table
- Detailed payslip per employee per month
- **Editable tax &amp; payroll formulas** via the `/settings` page:
  personal/dependant deductions, social-insurance rate, progressive PIT brackets,
  standard working days, meal allowances, Sunday &amp; overtime multipliers — no code
  change required when regulations or company policy change.

## System Requirements

- **PHP ≥ 8.2** (required by Laravel 11)
- **MySQL ≥ 5.7** (XAMPP / MariaDB both work)
- **Composer ≥ 2.5**

## Installation Guide

### Step 1: Install PHP 8.2 (if your XAMPP is outdated)

Easiest option: **download XAMPP 8.2.x** from https://www.apachefriends.org/download.html

After installation, verify:
```bash
php -v   # must print: PHP 8.2.x or higher
```

### Step 2: Install dependencies

```bash
cd C:\xampp\htdocs\tax-calculator
composer install
```

### Step 3: Create the database

Open phpMyAdmin (http://localhost/phpmyadmin) → **New** → create a database named **`tax_calculator`** with collation `utf8mb4_unicode_ci`.

Or use SQL:
```sql
CREATE DATABASE tax_calculator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 4: Configure `.env`

A `.env` file is provided with the default XAMPP MySQL setup (root, no password). Adjust if needed:

```env
DB_DATABASE=tax_calculator
DB_USERNAME=root
DB_PASSWORD=
```

Then generate the APP_KEY:
```bash
php artisan key:generate
```

### Step 5: Create tables + sample data

```bash
php artisan migrate --seed
```

This command will:
- Create 8 tables: `employees`, `attendances`, `overtimes`, `product_salaries`, `allowances`, `advances`, `payrolls`, `settings`
- Create 5 sample employees (NV001 - NV005) with 20 days of attendance data
- Auto-seed default values into the `settings` table on first visit to `/settings`

### Step 6: Run the server

```bash
php artisan serve
```

Open your browser at: **http://localhost:8000**

## Salary & Tax Formulas

### Take-home pay
```
Daily salary    = (Base salary / 26) × (Normal days + Sunday days × 2)
Overtime pay    = (Base salary / 26 / 2) × Number of 3h overtime shifts
Meal allowance  = 30,000 × Number of working days
OT meal         = 30,000 × Number of overtime shifts
Diligence bonus = If full attendance (no unpaid leave) → add diligence bonus

GROSS TAKE-HOME = Daily salary + Overtime + Meal + OT meal
                + Piece-rate + Diligence bonus + Allowances (both types)
```

### Personal Income Tax (PIT)
```
Taxable income     = Base salary + Piece-rate + Taxable allowances
Assessable income  = Taxable income − 11,000,000 − (4,400,000 × dependants) − 10.5% SI
```

**Progressive PIT table (simplified):**

| Bracket | Monthly assessable income | Formula |
|---|---|---|
| 1 | ≤ 10M VND | AI × 5% |
| 2 | 10 - 30M VND | AI × 10% − 500,000 |
| 3 | 30 - 60M VND | AI × 20% − 3,500,000 |
| 4 | 60 - 100M VND | AI × 30% − 9,500,000 |
| 5 | > 100M VND | AI × 35% − 14,500,000 |

### Net pay
```
NET PAY = Gross take-home − Advances − 10.5% SI − PIT
```

## Configurable Formulas (`/settings`)

All numbers used in the formulas above can be edited **directly in the web UI**,
no code change required. When the tax law or company policy changes, open the
**Settings** menu to update — payrolls will be recalculated on next view.

The `/settings` page has 3 tabs:

**1. PIT formula parameters**

| Key | Default | Meaning |
|---|---|---|
| `tax.personal_deduction` | 11,000,000 | Personal deduction / month |
| `tax.dependent_deduction` | 4,400,000 | Deduction / dependant / month |
| `tax.bhxh_rate` | 0.105 | Employee social-insurance contribution rate |

**2. Progressive PIT brackets** — add / remove / edit each bracket
(Limit / Rate / Subtractor). Set **Limit = 0** for the top open-ended bracket;
brackets are auto-sorted by `limit` ascending on save.

**3. Payroll formula parameters**

| Key | Default | Meaning |
|---|---|---|
| `payroll.standard_days` | 26 | Standard working days / month |
| `payroll.meal_per_day` | 30,000 | Meal allowance / workday |
| `payroll.meal_per_ot_shift` | 30,000 | Meal allowance / overtime shift |
| `payroll.sunday_multiplier` | 2 | Sunday work multiplier |
| `payroll.overtime_multiplier` | 0.5 | Overtime shift multiplier (in workday units) |

A **Reset to defaults** button restores every parameter to its original value.

## Folder Structure

```
tax-calculator/
├── app/
│   ├── Http/Controllers/    HomeController, EmployeeController, AttendanceController, PayrollController, SettingController
│   ├── Models/              Employee, Attendance, Overtime, ProductSalary, Allowance, Advance, Payroll, Setting
│   └── Services/            TaxService, PayrollService, SettingService
├── database/
│   ├── migrations/          8 migration files
│   └── seeders/             DatabaseSeeder (5 sample employees)
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── home.blade.php
│   ├── employees/
│   ├── attendance/
│   ├── payroll/
│   └── settings/
├── routes/web.php
└── .env
```

## Main URLs

| URL | Purpose |
|---|---|
| `/` | Home page + employee-code search box |
| `/employees` | Employee list / CRUD |
| `/attendance?year=YYYY&month=M` | Monthly attendance |
| `/payroll?year=YYYY&month=M` | Company-wide payroll table |
| `/payroll/{id}/{year}/{month}` | Detailed payslip for one employee |
| `/settings` | Configure tax &amp; payroll formulas |

## Sample employee codes after seeding

| Code | Full name | Base salary (VND) | Position |
|---|---|---|---|
| NV001 | Nguyễn Văn An | 8,000,000 | Production worker |
| NV002 | Trần Thị Bích | 15,000,000 | Team leader |
| NV003 | Lê Quốc Cường | 25,000,000 | Shop manager |
| NV004 | Phạm Thu Dung | 12,000,000 | Accountant |
| NV005 | Hoàng Minh Đức | 50,000,000 | Director |

## Reset the database

To wipe everything and start fresh:
```bash
php artisan migrate:fresh --seed
```

## Tech Stack

- **Backend:** Laravel 11, PHP 8.2+
- **Database:** MySQL / MariaDB
- **Frontend:** Blade templates
- **Runtime:** XAMPP (Apache + MySQL) on Windows

## License

Internal project — for educational and reference purposes regarding Vietnamese PIT and payroll regulations.