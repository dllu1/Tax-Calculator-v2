# Niên Giám Lương — Tính thuế TNCN & Quản lý lương

> 🇻🇳 Tiếng Việt (bên dưới) · 🇬🇧 [English version](#english-version)

Ứng dụng web Laravel 11 tính thuế thu nhập cá nhân và quản lý lương theo quy định
Việt Nam, trình bày dưới giao diện *niên giám / báo cổ điển* với màu mực ấm,
typography serif và bố cục dòng tiền minh bạch.

## Tính năng

- Quản lý nhân viên (mã NV, lương căn bản, mức BHXH, số người phụ thuộc...)
- Chấm công theo ngày — 5 trạng thái: **đi làm thường / nửa ngày / chủ nhật (×2) /
  có phép / không phép**. Trạng thái **nửa ngày** trả thêm **½ tiền chuyên cần**
  cho mỗi half-day (không phá tiền chuyên cần cuối tháng).
- Tăng ca (3h = ½ ngày lương)
- Lương sản phẩm, phụ cấp (chịu thuế / không chịu thuế), tạm ứng
- Tự động tính BHXH 10,5%, thuế TNCN theo 5 bậc lũy tiến
- Phiếu lương chi tiết theo từng nhân viên / từng tháng
- **Cấu hình động công thức tính thuế &amp; lương** qua trang `/settings`:
  giảm trừ gia cảnh, tỉ lệ BHXH, các bậc thuế lũy tiến, số công chuẩn,
  tiền ăn, hệ số Chủ nhật &amp; tăng ca... — không cần sửa code khi luật thay đổi.
- **Giao diện "Niên Giám"** lấy cảm hứng từ báo in cổ điển: bảng màu cream/burgundy,
  serif EB Garamond, mục lục số La Mã, "dòng tiền tháng" 5 cột trên phiếu lương,
  bảng ledger với dotted rule và double-rule total. Thanh điều hướng **sticky** ở
  trên cùng khi cuộn, các nút thao tác (xem / sửa / xoá) gọn trên 1 hàng. Ở các
  trang nhiều dữ liệu, **nút "Lưu Chấm Công"** trên trang chấm công và **dòng
  "TỔNG CỘNG"** trên bảng lương được pin sticky ở đáy viewport — khỏi phải cuộn
  qua mấy chục nhân viên mới tới được nút lưu / tổng cộng.
- **Xuất PDF** phiếu lương cá nhân &amp; bảng lương cả công ty qua nút *"Xuất PDF"* —
  dùng CSS `@media print` + dialog *Save as PDF* của trình duyệt, không cần cài thêm
  thư viện. **Phiếu lương cá nhân** in ra theo **format compact 1 trang A4** giống
  mẫu giấy truyền thống của công ty: header có mã NV, tiêu đề "PHIẾU TÍNH LƯƠNG",
  bảng "Danh mục / Số ngày / Lương ngày / Số tiền" liệt kê từng khoản (lương ngày,
  tăng ca, ăn ca, từng phụ cấp riêng dòng…), kèm dòng BHXH chủ SD (21,5%) gạch
  ngang để tham chiếu, rồi đến block *Trừ tiền* và *Thực lãnh*. Trang phân tích
  trên web (5 section gazette) chỉ hiện khi xem online, **không xuất hiện trong PDF**.
  Bảng lương tháng có thêm **nút PDF từng dòng** để xuất phiếu lương cho riêng
  1 nhân viên (mở `?print=1` → dialog in tự bật). Bảng chấm công cả tháng cũng có
  nút xuất PDF với layout **A4 landscape**, in **đen trắng kiểu Excel** (xoá màu
  badge, giữ border rõ ràng, cột Chủ nhật tô xám nhạt) — chứa hết các ô ngày,
  không giới hạn số trang khi nhiều nhân viên.
- **Import nhân viên hàng loạt từ Excel** (`.xlsx` / `.xls` / `.csv`) qua nút
  *"Import Excel"* trên Sổ Nhân Viên. Có nút *"Tải file mẫu"* sinh file XLSX
  trống đúng định dạng. Khi phát hiện mã NV trùng, hệ thống hiện **popup so sánh
  từng trường (cũ ↔ mới)** và để người dùng chọn *Giữ nguyên dữ liệu cũ* hoặc
  *Ghi đè bằng dữ liệu mới*. Header chấp nhận cả tiếng Việt có dấu lẫn snake_case
  (`ma_nv`, `Mã NV`, `employee_code` đều được hiểu).
- **Trang Hướng Dẫn (`/help`)** — sổ tay sử dụng đặt ngay trong web, không cần
  mở tài liệu ngoài. Bố cục 2 cột: **phụ lục bên trái** (sticky, tự sáng-tô mục
  đang xem theo cuộn) + nội dung từng chức năng bên phải. Bấm tiêu đề trong
  phụ lục sẽ cuộn mượt đến phần tương ứng. **Ô tìm kiếm realtime** lọc các
  mục theo từ khoá (không phân biệt dấu) ngay khi gõ — không khớp mục nào sẽ
  hiện thông báo trống.
- **Đa ngôn ngữ Việt / English** — nút **VI / EN** ở góc phải masthead. Toàn bộ
  giao diện (masthead, nav, tất cả các trang Trang Nhất / Nhân Viên / Chấm Công /
  Bảng Lương / Phiếu Lương / Cấu Hình / Hướng Dẫn, kể cả label tham số cấu hình
  trong DB) được dịch qua hệ `__()` của Laravel với `lang/vi.json` &amp; `lang/en.json`.
  Ngôn ngữ lưu trong **session**, giữ nguyên giữa các trang.
- **Giao diện sáng / tối** — nút **🌙 / ☀** ngay cạnh chuyển ngôn ngữ. Light mode
  giữ bảng màu cream/burgundy gốc; dark mode chuyển sang nền than-mực với chữ
  cream-soft, giữ nguyên typography serif. Lựa chọn lưu trong `localStorage`,
  có **pre-flight script** trong `<head>` để áp theme trước khi render — không
  bị "flash" trắng khi tải trang.
- **Nhập / sửa / xoá không reload trang** — các form CRUD ở **Cấu Hình**, **Sổ
  Nhân Viên**, **Chấm Công**, và **Phiếu Lương** (Lương SP / Phụ cấp / Tạm ứng)
  được intercept bằng `fetch` rồi gửi `XMLHttpRequest` lên server. Server trả
  JSON `{ok, message}`, client hiện **toast nhỏ ở góc phải** thay vì điều
  hướng. Trang phiếu lương dùng kỹ thuật **soft-reload** (fetch lại HTML, thay
  `<main>`) để cập nhật totals mà không nháy trang. Xoá nhân viên xoá dòng
  table tại chỗ. Nhờ vậy thao tác trên trang dữ liệu tháng mượt như SPA.
- **Cổng đăng nhập một mật khẩu** — toàn bộ trang dữ liệu được bảo vệ bằng
  middleware `RequirePassword`. Lần đầu mở app, hệ thống yêu cầu **tạo mật khẩu**
  và sinh **mã khôi phục dạng `XXXX-XXXX-XXXX-XXXX`** (hiển thị **duy nhất một
  lần** — phải lưu ngay). Phiên đăng nhập **hết hạn khi đóng trình duyệt/app**
  (`SESSION_EXPIRE_ON_CLOSE=true`), tắt-bật là phải nhập lại. Quên mật khẩu thì
  nhập mã khôi phục ở `/auth/forgot` để đặt lại — đặt xong sẽ sinh **mã khôi phục
  mới** (mã cũ vô hiệu hoá). Mật khẩu lưu **bcrypt hash** trong bảng `settings`,
  không cần thêm migration. Có **rate-limit** 5 lần/phút cho login & 3 lần/5 phút
  cho recovery. Nút **Đăng xuất** nằm cạnh nút đổi theme ở masthead.

## Yêu cầu hệ thống

- **PHP ≥ 8.3** (Laravel 12 yêu cầu)
- **MySQL ≥ 5.7** (XAMPP / MariaDB đều OK) — cho chế độ web
- **Composer ≥ 2.2**
- **Node.js ≥ 22 + npm** — chỉ cần nếu build/dev với NativePHP (desktop app)

## Hướng dẫn cài đặt

### Bước 1: Cài PHP 8.3+ (nếu XAMPP đang cũ)

Lựa chọn dễ nhất: **tải XAMPP 8.3.x** tại https://www.apachefriends.org/download.html

Sau khi cài, kiểm tra:
```bash
php -v   # phải in: PHP 8.3.x hoặc cao hơn
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
- Sinh **10 nhân viên mẫu** (NV001 – NV010) với vai trò đa dạng: công nhân, tổ
  trưởng, quản đốc, kế toán, nhân sự, QA, phó GĐ, GĐ
- Chấm công **2 tháng** (tháng trước trọn vẹn + tháng hiện tại tới ngày hôm
  nay): đầy đủ 5 trạng thái (`normal`, `sunday`, `half`, `leave`, `absent`).
  NV007 cố tình có ngày `absent` để minh hoạ tiền chuyên cần bị mất; nhiều NV
  có ngày `half` để xem tiền nửa ngày được trả thế nào.
- Tăng ca cho công nhân & tổ trưởng (2 ca/5 ngày, 1 ca/3 ngày)
- Lương sản phẩm cho production workers (~20-25% lương căn bản)
- Phụ cấp đa dạng theo vai trò: trách nhiệm, điện thoại, xăng xe, ăn trưa,
  độc hại (cả `taxable` & `non_taxable`)
- Tạm ứng cho một số NV để test trừ lương cuối tháng
- Tự động seed các tham số mặc định cho bảng `settings` (lần đầu vào `/settings`)

→ Tổng cộng ~450 attendance, ~80 overtime, ~50 allowance, ~10 advance — đủ
phong phú để thử mọi tính năng (bảng lương cá nhân, bảng lương cả công ty,
so sánh tháng, xuất PDF, in chấm công…).

### Bước 6: Chạy server

```bash
php artisan serve
```

Mở trình duyệt: **http://localhost:8000**

#### Lần đầu mở app

Truy cập `http://localhost:8000` → bị chuyển hướng tới **`/auth/setup`** để
tạo mật khẩu (tối thiểu 6 ký tự). Sau khi tạo xong, app hiển thị **mã khôi
phục dạng `XXXX-XXXX-XXXX-XXXX`** — **bấm "Sao chép mã" và lưu lại ngay**
(vào trình quản lý mật khẩu, ghi chú giấy, hoặc bất kỳ nơi nào an toàn). Mã
chỉ hiển thị **một lần duy nhất**; rời khỏi trang là không xem lại được. Nếu
sau này quên mật khẩu, vào `/auth/forgot` nhập mã khôi phục để đặt lại — mỗi
lần reset sẽ sinh mã mới.

#### Khởi động bằng 1 click (Windows)

Trong thư mục dự án đã có sẵn 2 file:

- **`start.bat`** — double-click sẽ tự động:
  1. Khởi động MySQL của XAMPP (nếu chưa chạy)
  2. Chạy `php artisan migrate --force` để áp dụng migration mới (nếu có)
  3. Khởi động Laravel server tại `http://localhost:8000`
  4. Mở trình duyệt mặc định vào trang chủ
- **`stop.bat`** — dừng Laravel server (không tắt MySQL để tránh ảnh hưởng các app khác dùng chung XAMPP)

Mặc định script tìm XAMPP tại `C:\xampp`. Nếu bạn cài XAMPP ở chỗ khác, sửa biến `XAMPP_DIR` ở đầu `start.bat`.

#### Tạo file `.exe` từ `start.bat` (tuỳ chọn)

Nếu muốn 1 file `.exe` để đặt shortcut ra desktop, dùng tool miễn phí
[**Bat To Exe Converter**](https://www.battoexeconverter.com/) — mở `start.bat`,
chọn icon (nếu thích), bấm *Convert* sẽ ra `start.exe`. File `.exe` đó chỉ là
vỏ bọc đóng gói `.bat` nên không cần cài đặt thêm.

## Đóng gói thành Windows Desktop App (NativePHP / Electron)

Phần này biến app thành **một thư mục `.exe` chạy độc lập** — không cần XAMPP,
không cần MySQL, không cần PHP trên máy người dùng. Dùng [NativePHP for
Electron](https://nativephp.com/) bundle PHP runtime + SQLite + toàn bộ Laravel
app vào trong 1 package.

### Yêu cầu thêm
- **Node.js ≥ 22** & npm (xác minh: `node --version`)
- Lần build đầu sẽ tải Electron binary ~150MB

### Bước 1: Cài NativePHP (đã có sẵn trong composer.json)
Sau `composer install`, các script tự chạy: tải `nativephp/electron`, publish
config, cài npm dependencies trong `vendor/nativephp/electron/resources/js/`,
và áp một số patch tương thích qua [`scripts/apply-nativephp-patches.php`](tax-calculator/scripts/apply-nativephp-patches.php)
(downgrade `electron-store@10→8`, `get-port@7→5`, `electron-context-menu@4→3`,
xóa `type:module`, thêm `portNumbers` shim, sub-`package.json` ESM cho
`electron-plugin/dist/server/` — cần thiết vì NativePHP 1.3.0 + Node ≥20 có
bug ESM/CJS bridge).

### Bước 2: Dev mode (mở Electron window từ source)
```bash
# Trên Windows, double-click hoặc:
dev-native.bat
```
Script này:
1. Backup `.env` (MySQL config) sang `.env.bak`
2. Copy `.env.nativephp` (SQLite + NativePHP metadata) thành `.env`
3. Tạo `database/database.sqlite` nếu thiếu, chạy migrate + seed
4. Unset `ELECTRON_RUN_AS_NODE` (env var leak khiến Electron crash)
5. Chạy `php artisan native:serve` → cửa sổ Electron mở ra, hot-reload PHP
6. Khi tắt: restore `.env` về MySQL

### Bước 3: Build `.exe` cho production
```bash
# .env phải đang ở chế độ SQLite — chạy dev-native.bat một lần để swap
php artisan native:build win x64
```
- Mất 10–30 phút (download Electron binary, đóng gói asar, electron-builder)
- Kết quả: `dist/win-unpacked/tax-calculator.exe` (~178MB) — sẵn sàng chạy
- Toàn bộ thư mục `dist/win-unpacked/` (~390MB) có thể copy sang máy khác và
  chạy ngay, không cài đặt
- File `.env` bundled là `.env.nativephp` (SQLite). DB sẽ được tạo tự động
  trong `%APPDATA%\tax-calculator\database\database.sqlite` lần đầu mở
- App tự động chạy migrate + seed lần đầu (qua `NativeAppServiceProvider`)
  nên user thấy ngay 10 NV mẫu — có thể test feature mà chưa cần import

#### NSIS installer (`Tax Calculator-1.0.0-setup.exe`)
Mặc định electron-builder cố tạo installer NSIS nhưng cần extract
`winCodeSign` (chứa macOS dylib symlinks) — Windows yêu cầu admin/Developer
Mode để tạo symlink. Hai cách xử lý:
- **Bật Developer Mode**: Settings → System → For developers → Developer Mode = On
- **Hoặc chạy Terminal "Run as Administrator"** rồi build — cache extract xong,
  lần sau chạy thường được

Nếu chỉ cần app chạy được (không quan tâm installer), `dist/win-unpacked/` đã đủ.

### Lưu ý quan trọng
- **2 môi trường song song**:
  - `start.bat` → web mode (MySQL/XAMPP/`localhost:8000`) — dùng để dev nhanh
    với hot reload, debug qua browser DevTools
  - `dev-native.bat` → desktop mode (SQLite trong Electron window) — dùng để
    test feature như thật, kiểm tra trước khi build
- **Không trộn 2 DB**: dữ liệu MySQL và SQLite tách biệt. Khi switch qua
  `dev-native.bat`, đang dùng SQLite ở `database/database.sqlite`. Lần đầu
  Electron mở ra sẽ seed lại 10 NV mẫu vào SQLite.
- **Sau khi `composer update`**: script `apply-nativephp-patches.php` tự chạy
  qua `post-update-cmd` (đã cấu hình trong composer.json) để re-apply patches.

## Công thức tính lương & thuế

### Tiền lương thực nhận
```
Lương ngày     = (Lương căn bản / 26) × (Ngày thường + Ngày CN × 2)
Lương tăng ca  = (Lương căn bản / 26 / 2) × Số ca tăng ca 3h
Ăn giữa ca     = 30.000 × (Ngày thường + Ngày CN + Nửa ngày)
Ăn tăng ca     = 30.000 × Số ca tăng ca
Chuyên cần     = Nếu đủ công (không có nghỉ X) → cộng tiền chuyên cần
                 (nửa ngày KHÔNG phá chuyên cần)
Lương nửa ngày = Số nửa ngày × (Tiền chuyên cần / 2)

TỔNG THỰC NHẬN = Lương ngày + Tăng ca + Ăn ca + Ăn TC
               + Lương SP + Chuyên cần + Lương nửa ngày
               + Phụ cấp (cả 2 loại)
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
│   ├── Http/
│   │   ├── Controllers/     HomeController, EmployeeController, AttendanceController, PayrollController,
│   │   │                    SettingController, HelpController, LocaleController, AuthController
│   │   └── Middleware/      SetLocale (đọc ngôn ngữ từ session)
│   │                        RequirePassword (gác toàn bộ route, redirect tới /auth/setup hoặc /auth/login)
│   ├── Models/              Employee, Attendance, Overtime, ProductSalary, Allowance, Advance, Payroll, Setting
│   ├── Providers/           AppServiceProvider, NativeAppServiceProvider (cấu hình Electron window + auto-seed)
│   ├── Services/            TaxService, PayrollService, SettingService, AuthGate (hash & verify mật khẩu/recovery)
│   ├── Imports/             EmployeesImport (parse-only, bucket new/duplicate)
│   └── Exports/             EmployeesTemplateExport (sinh file mẫu XLSX)
├── database/
│   ├── migrations/          9 file migration (driver-aware: chạy được cả MySQL & SQLite)
│   └── seeders/             DatabaseSeeder (10 NV mẫu × 2 tháng, đủ 5 loại chấm công)
├── lang/
│   ├── vi.json              Bản gốc tiếng Việt
│   └── en.json              Bản dịch tiếng Anh (~220 key)
├── resources/views/
│   ├── layouts/app.blade.php   (gồm masthead + nút VI/EN + nút theme + nút Đăng xuất + pre-flight script)
│   ├── auth/                (layout, setup, setup-success, login, forgot — cổng đăng nhập 1 mật khẩu)
│   ├── home.blade.php
│   ├── help.blade.php       (trang hướng dẫn 2 cột: TOC sticky + nội dung)
│   ├── employees/           (gồm modal import + modal so sánh khi trùng mã)
│   ├── attendance/
│   ├── payroll/
│   └── settings/
├── scripts/
│   └── apply-nativephp-patches.php   (chạy auto sau composer install/update — patch vendor JS cho NativePHP)
├── routes/web.php
├── config/nativephp.php     Cấu hình NativePHP (window, updater, queue workers)
├── start.bat                Khởi động web mode 1-click (Windows) — MySQL/XAMPP
├── stop.bat                 Dừng Laravel server
├── dev-native.bat           Khởi động desktop mode (Electron + SQLite) — swap .env tự động
├── .env                     Cấu hình MySQL cho web mode
└── .env.nativephp           Cấu hình SQLite cho desktop mode (dev-native.bat dùng)
```

## Các URL chính

| URL | Chức năng |
|---|---|
| `/` | Trang chủ + ô tìm mã NV |
| `/employees` | Danh sách / CRUD nhân viên |
| `/attendance?year=YYYY&month=M` | Chấm công cả tháng |
| `/payroll?year=YYYY&month=M` | Bảng lương toàn công ty |
| `/payroll/{id}/{year}/{month}` | Phiếu lương chi tiết 1 NV |
| `/payroll/{id}/{year}/{month}?print=1` | Phiếu lương + tự bật dialog in PDF |
| `/settings` | Cấu hình công thức tính thuế &amp; lương |
| `/help` | Trang hướng dẫn sử dụng (TOC sticky + tìm kiếm realtime) |
| `/employees/template` | Tải file mẫu XLSX để chuẩn bị dữ liệu import |
| `POST /employees/import` | Upload file Excel (phase 1: phân tích trùng mã) |
| `POST /employees/import/commit` | Xác nhận giữ/ghi đè sau khi xem popup (phase 2) |
| `POST /locale/{vi\|en}` | Đổi ngôn ngữ (lưu vào session) |
| `GET /auth/setup` | Tạo mật khẩu lần đầu (chỉ hiện khi chưa có) |
| `POST /auth/setup` | Lưu mật khẩu mới + sinh mã khôi phục |
| `GET /auth/recovery` | Trang hiển thị mã khôi phục một lần duy nhất |
| `GET /auth/login` | Form đăng nhập 1-mật-khẩu |
| `POST /auth/login` | Xác thực mật khẩu (rate-limit 5 lần/phút) |
| `POST /auth/logout` | Đăng xuất, xoá session |
| `GET /auth/forgot` | Form khôi phục mật khẩu bằng mã |
| `POST /auth/forgot` | Đặt mật khẩu mới + sinh mã khôi phục mới (rate-limit 3 lần/5 phút) |

## Mã NV mẫu sau seed (10 nhân viên)

| Mã | Họ tên | Lương căn bản | Chức vụ | Phòng ban | NPT |
|---|---|---|---|---|---|
| NV001 | Nguyễn Văn An       |  8.000.000 | Công nhân SX     | Phân xưởng A | 1 |
| NV002 | Trần Thị Bích       | 15.000.000 | Tổ trưởng        | Phân xưởng A | 2 |
| NV003 | Lê Quốc Cường       | 25.000.000 | Quản đốc         | Phân xưởng B | 0 |
| NV004 | Phạm Thu Dung       | 12.000.000 | Kế toán          | Văn phòng    | 1 |
| NV005 | Hoàng Minh Đức      | 50.000.000 | Giám đốc         | Ban GĐ       | 2 |
| NV006 | Vũ Thị Hà           |  9.500.000 | Công nhân SX     | Phân xưởng A | 0 |
| NV007 | Đỗ Văn Khánh        | 10.000.000 | Công nhân SX     | Phân xưởng B | 2 |
| NV008 | Bùi Thị Lan         | 14.000.000 | Nhân viên QA     | Phân xưởng B | 1 |
| NV009 | Ngô Thanh Mai       | 13.500.000 | Nhân sự          | Văn phòng    | 3 |
| NV010 | Trịnh Quang Phú     | 35.000.000 | Phó GĐ kinh doanh| Ban GĐ       | 1 |

> **NV007 cố tình có ngày nghỉ không phép** trong tháng để minh hoạ tính
> năng *mất tiền chuyên cần*. Nhiều NV có ngày **nửa ngày** (`half`) để test
> tiền nửa ngày = tiền chuyên cần / 2. Dữ liệu rải đều **2 tháng** (tháng
> trước trọn vẹn + tháng hiện tại tới ngày hôm nay) để có thể so sánh bảng
> lương giữa 2 tháng.

## Reset database

Nếu muốn xóa toàn bộ và làm lại:
```bash
php artisan migrate:fresh --seed
```

---

## English version

# Niên Giám Lương — Vietnam PIT & Payroll Management System

A Laravel 11 web application that calculates Vietnamese personal income tax (PIT)
and manages monthly payroll according to current Vietnam regulations, presented
through a *classical gazette / almanac* interface with warm ink colors, serif
typography, and transparent cash-flow layouts.

## Features

- Employee management (employee code, base salary, social-insurance level, number of dependants, etc.)
- Daily attendance tracking — five states: **normal / half-day / Sunday (×2) /
  paid leave / unpaid leave**. The **half-day** state pays an extra **½ of the
  diligence bonus** per occurrence (and does not forfeit the end-of-month
  diligence bonus).
- Overtime tracking (3h shift = ½ day of salary)
- Piece-rate wages, allowances (taxable / non-taxable), salary advances
- Automatic calculation of 10.5% social insurance and PIT under the 5-bracket progressive table
- Detailed payslip per employee per month
- **Editable tax &amp; payroll formulas** via the `/settings` page:
  personal/dependant deductions, social-insurance rate, progressive PIT brackets,
  standard working days, meal allowances, Sunday &amp; overtime multipliers — no code
  change required when regulations or company policy change.
- **"Gazette" interface** inspired by classical print newspapers: cream/burgundy
  palette, EB Garamond serif, Roman-numeral section rules, a 5-column monthly
  cash-flow strip on each payslip, and ledger tables with dotted rules and
  double-rule totals. The top navigation bar **sticks** on scroll, and row
  actions (view / edit / delete) are laid out as a compact single-row group.
  On data-heavy pages, the **"Save Attendance"** button on the attendance input
  page and the **"TỔNG CỘNG" (Grand Total)** row on the payroll list are also
  pinned sticky to the viewport bottom — no need to scroll past dozens of
  employees just to reach the save button or see the totals.
- **PDF export** for individual payslips and the company-wide monthly payroll via
  a *"Xuất PDF"* button — implemented with CSS `@media print` and the browser's
  *Save as PDF* dialog, no extra library required. The **individual payslip**
  prints as a **compact single-page A4** matching the traditional company form:
  header with employee code, "PHIẾU TÍNH LƯƠNG" title, a single
  "Danh mục / Số ngày / Lương ngày / Số tiền" table listing every component
  (daily wage, overtime, meal, each allowance on its own line…), a strike-through
  row for the employer-side BHXH (21.5%) for reference, followed by the
  *Trừ tiền* (deductions) block and the final *Thực lãnh* (net pay) total. The
  rich on-screen analytical view (5 gazette sections) is hidden from the PDF.
  The payroll list also has a **per-row PDF icon** that opens the payslip with
  `?print=1` and auto-triggers the print dialog. The monthly attendance grid
  has its own PDF button using **A4 landscape** layout printed in **black &amp;
  white Excel style** (all badge colors stripped, clean borders, Sundays in
  light gray) — fits every day cell, no page limit when there are many
  employees.
- **Bulk employee import from Excel** (`.xlsx` / `.xls` / `.csv`) via the
  *"Import Excel"* button on the employee list, with a *"Download Template"*
  button that produces a properly-formatted blank XLSX. When duplicate employee
  codes are detected, a **side-by-side comparison popup** (old vs. new, per
  field) lets the user choose *Keep existing data* or *Overwrite with new data*.
  Headers accept both Vietnamese with diacritics and snake_case
  (`ma_nv`, `Mã NV`, `employee_code` are all recognized).
- **In-app User Guide (`/help`)** — a built-in manual, no need to open external
  docs. Two-column layout: a **sticky left sidebar TOC** (auto-highlights the
  section currently in view via `IntersectionObserver`) plus per-feature
  documentation on the right. Clicking a TOC entry smoothly scrolls to the
  target. A **realtime search box** filters sections by keyword (diacritic-
  insensitive) as you type — with an empty-state message when nothing matches.
- **Vietnamese / English bilingual UI** — a **VI / EN** toggle at the top-right
  of the masthead. Every page (Home, Employees, Attendance, Payroll, Payslip,
  Settings, Help — including parameter labels stored in the DB) is translated
  through Laravel's `__()` helper backed by `lang/vi.json` &amp; `lang/en.json`.
  The active language is stored in **session** and persists across pages.
- **Light / Dark theme** — a **🌙 / ☀** button next to the language toggle.
  Light mode keeps the original cream/burgundy gazette palette; dark mode
  switches to an ink-on-charcoal scheme while preserving the serif typography.
  The choice is stored in `localStorage` and applied by an in-`<head>`
  **pre-flight script** before render — so there is no flash of unstyled
  content when reloading.
- **No-reload CRUD** — every save / edit / delete form on **Settings**,
  **Employee Roster**, **Attendance**, and the **Payslip** (piece-rate /
  allowance / advance) is intercepted by `fetch` and posted as an
  `XMLHttpRequest`. The server replies with JSON `{ok, message}` and the
  client shows a **small toast in the top-right corner** instead of
  navigating. The payslip page uses a **soft-reload** technique (re-fetch
  HTML, swap `<main>`) so totals refresh without a full page flash. Deleting
  an employee removes the row in place. Working with monthly data feels like
  a single-page app.
- **Single-password sign-in gate** — every data page is protected by the
  `RequirePassword` middleware. On first launch, the app asks the user to
  **create a password** and generates an **`XXXX-XXXX-XXXX-XXXX` recovery
  code** displayed **exactly once** (it must be saved right away). The
  session **expires when the browser/app closes**
  (`SESSION_EXPIRE_ON_CLOSE=true`), so reopening always requires another
  sign-in. If the password is forgotten, entering the recovery code at
  `/auth/forgot` lets the user reset it — each successful reset also
  generates a **new recovery code** (the previous one is invalidated). The
  password is stored as a **bcrypt hash** in the `settings` table; no extra
  migration is required. **Rate limiting**: 5 login attempts/minute and
  3 recovery attempts/5 minutes. A **Sign-out** button sits next to the
  theme toggle in the masthead.

## System Requirements

- **PHP ≥ 8.3** (required by Laravel 12)
- **MySQL ≥ 5.7** (XAMPP / MariaDB both work) — for web mode
- **Composer ≥ 2.2**
- **Node.js ≥ 22 + npm** — only required if you build/dev with NativePHP (desktop app)

## Installation Guide

### Step 1: Install PHP 8.3+ (if your XAMPP is outdated)

Easiest option: **download XAMPP 8.3.x** from https://www.apachefriends.org/download.html

After installation, verify:
```bash
php -v   # must print: PHP 8.3.x or higher
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
- Seed **10 sample employees** (NV001–NV010) with diverse roles: production
  workers, team lead, foreman, accountant, HR, QA, vice-director, director
- Attendance for **two months** (full previous month + current month up to
  today): all five states (`normal`, `sunday`, `half`, `leave`, `absent`).
  NV007 has deliberate `absent` days to demonstrate diligence-bonus forfeit;
  several employees have `half` days to verify half-day pay computation.
- Overtime for workers & team leads (2 shifts every 5 days, 1 shift every 3 days)
- Piece-rate wages for production workers (~20–25% of base salary)
- A varied mix of allowances per role: responsibility, phone, transport, meal,
  hazard pay (both `taxable` & `non_taxable`)
- Salary advances for select employees, to verify end-of-month deductions
- Default values auto-seeded into the `settings` table on first visit to `/settings`

→ Roughly 450 attendance rows, 80 overtime rows, 50 allowances, 10 advances —
enough variety to exercise every feature (individual payslip, company-wide
payroll, month comparisons, PDF export, attendance grid print…).

### Step 6: Run the server

```bash
php artisan serve
```

Open your browser at: **http://localhost:8000**

#### First-time sign-in

Open `http://localhost:8000` → you'll be redirected to **`/auth/setup`** to
create a password (minimum 6 characters). Once created, the app shows your
**`XXXX-XXXX-XXXX-XXXX` recovery code** — **click "Copy code" and save it
immediately** (in your password manager, on paper, or anywhere safe). The
code is shown **exactly once**; leaving the page makes it unrecoverable. If
you later forget the password, go to `/auth/forgot`, enter the recovery
code, and reset — every reset issues a fresh recovery code.

#### One-click launcher (Windows)

The project ships with two batch files at the project root:

- **`start.bat`** — double-click to:
  1. Start XAMPP's MySQL (if not already running)
  2. Run `php artisan migrate --force` to apply any pending migrations
  3. Start the Laravel server on `http://localhost:8000`
  4. Open the default browser at the home page
- **`stop.bat`** — stops the Laravel server only (leaves MySQL running so other
  XAMPP-based apps are not affected)

The script defaults to `C:\xampp`. If XAMPP lives elsewhere, edit the
`XAMPP_DIR` variable at the top of `start.bat`.

#### Wrapping `start.bat` into a `.exe` (optional)

If you want a single `.exe` you can pin to the desktop, use the free
[**Bat To Exe Converter**](https://www.battoexeconverter.com/): open
`start.bat`, optionally pick an icon, then click *Convert* to produce
`start.exe`. The `.exe` is just a thin wrapper around the `.bat` — no
runtime installation required.

## Packaging as a Windows Desktop App (NativePHP / Electron)

This section turns the app into a **standalone `.exe` folder** that runs
without XAMPP, without MySQL, without PHP on the user's machine. It uses
[NativePHP for Electron](https://nativephp.com/) to bundle a PHP runtime +
SQLite + the entire Laravel app into a single distributable package.

### Additional requirements
- **Node.js ≥ 22** & npm (verify: `node --version`)
- First build downloads the Electron Windows binary (~150 MB)

### Step 1: Install NativePHP (already wired into composer.json)
After `composer install`, the post-install hooks download `nativephp/electron`,
publish its config, install npm dependencies under
`vendor/nativephp/electron/resources/js/`, and apply compatibility patches via
[`scripts/apply-nativephp-patches.php`](tax-calculator/scripts/apply-nativephp-patches.php)
(downgrade `electron-store@10→8`, `get-port@7→5`, `electron-context-menu@4→3`;
remove `type:module`; add a `portNumbers` shim; drop a sub-`package.json`
under `electron-plugin/dist/server/` that re-enables ESM there — all needed
because NativePHP 1.3.0 + Node ≥20 hits a known ESM/CJS bridge bug otherwise).

### Step 2: Dev mode (open the Electron window from source)
```bash
# Windows: double-click or run from a terminal:
dev-native.bat
```
This script:
1. Backs up `.env` (MySQL config) to `.env.bak`
2. Swaps in `.env.nativephp` (SQLite + NativePHP metadata) as `.env`
3. Creates `database/database.sqlite` if missing, runs migrate + seed
4. Unsets `ELECTRON_RUN_AS_NODE` (a stray env var that otherwise crashes Electron)
5. Runs `php artisan native:serve` → an Electron window opens with PHP hot-reload
6. On exit: restores `.env` to MySQL

### Step 3: Build the production `.exe`
```bash
# .env must currently be SQLite — run dev-native.bat once to swap
php artisan native:build win x64
```
- Takes 10–30 minutes (Electron binary download, asar bundling, electron-builder)
- Output: `dist/win-unpacked/tax-calculator.exe` (~178 MB) — ready to run
- The entire `dist/win-unpacked/` folder (~390 MB) can be copied to any other
  machine and launched directly, no install required
- The bundled `.env` is `.env.nativephp` (SQLite). The DB is created
  automatically at `%APPDATA%\tax-calculator\database\database.sqlite` on
  first launch
- The app auto-runs migrate + seed on first launch (via
  `NativeAppServiceProvider`), so the user sees 10 sample employees
  immediately — no manual import needed to test features

#### NSIS installer (`Tax Calculator-1.0.0-setup.exe`)
By default, electron-builder also tries to produce an NSIS installer, but it
needs to extract `winCodeSign` (which contains macOS dylib symlinks). Windows
refuses to create symlinks without admin or Developer Mode. Two workarounds:
- **Turn on Developer Mode**: Settings → System → For developers → Developer Mode = On
- **Or run the terminal as "Run as Administrator"** for one build — the cache
  extracts successfully and stays valid for subsequent normal-user builds

If you only need a runnable app (no installer), `dist/win-unpacked/` is
already sufficient.

### Important notes
- **Two parallel environments**:
  - `start.bat` → web mode (MySQL/XAMPP at `localhost:8000`) — best for fast
    dev with hot reload and browser DevTools
  - `dev-native.bat` → desktop mode (SQLite inside the Electron window) — best
    to feature-test the real packaged experience before building
- **Don't mix databases**: MySQL and SQLite data are separate. Switching to
  `dev-native.bat` swaps to SQLite at `database/database.sqlite`. First
  Electron launch will seed 10 sample employees into that SQLite file.
- **After `composer update`**: `apply-nativephp-patches.php` re-runs
  automatically via `post-update-cmd` (wired in composer.json) so the patches
  survive any vendor refresh.

## Salary & Tax Formulas

### Take-home pay
```
Daily salary    = (Base salary / 26) × (Normal days + Sunday days × 2)
Overtime pay    = (Base salary / 26 / 2) × Number of 3h overtime shifts
Meal allowance  = 30,000 × (Normal days + Sunday days + Half days)
OT meal         = 30,000 × Number of overtime shifts
Diligence bonus = If full attendance (no unpaid leave) → add diligence bonus
                  (half-days do NOT forfeit the bonus)
Half-day pay    = Half-day count × (Diligence bonus / 2)

GROSS TAKE-HOME = Daily salary + Overtime + Meal + OT meal
                + Piece-rate + Diligence bonus + Half-day pay
                + Allowances (both types)
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
│   ├── Http/
│   │   ├── Controllers/     HomeController, EmployeeController, AttendanceController, PayrollController,
│   │   │                    SettingController, HelpController, LocaleController, AuthController
│   │   └── Middleware/      SetLocale (reads the locale from the session)
│   │                        RequirePassword (guards every route, redirects to /auth/setup or /auth/login)
│   ├── Models/              Employee, Attendance, Overtime, ProductSalary, Allowance, Advance, Payroll, Setting
│   ├── Providers/           AppServiceProvider, NativeAppServiceProvider (Electron window config + auto-seed)
│   ├── Services/            TaxService, PayrollService, SettingService, AuthGate (hash & verify password/recovery)
│   ├── Imports/             EmployeesImport (parse-only, buckets new/duplicate rows)
│   └── Exports/             EmployeesTemplateExport (generates the blank XLSX template)
├── database/
│   ├── migrations/          9 migration files (driver-aware: work on both MySQL & SQLite)
│   └── seeders/             DatabaseSeeder (10 sample employees × 2 months, all 5 attendance types)
├── lang/
│   ├── vi.json              Vietnamese source strings
│   └── en.json              English translations (~220 keys)
├── resources/views/
│   ├── layouts/app.blade.php  (masthead + VI/EN toggle + theme toggle + sign-out + pre-flight script)
│   ├── auth/                (layout, setup, setup-success, login, forgot — single-password sign-in gate)
│   ├── home.blade.php
│   ├── help.blade.php       (2-column user guide: sticky TOC + content)
│   ├── employees/           (includes the import modal + duplicate-comparison modal)
│   ├── attendance/
│   ├── payroll/
│   └── settings/
├── scripts/
│   └── apply-nativephp-patches.php   (runs automatically after composer install/update — patches vendor JS)
├── routes/web.php
├── config/nativephp.php     NativePHP config (window, updater, queue workers)
├── start.bat                Web-mode launcher (Windows) — MySQL/XAMPP
├── stop.bat                 Stops the Laravel server
├── dev-native.bat           Desktop-mode launcher (Electron + SQLite) — swaps .env automatically
├── .env                     MySQL config for web mode
└── .env.nativephp           SQLite config for desktop mode (used by dev-native.bat)
```

## Main URLs

| URL | Purpose |
|---|---|
| `/` | Home page + employee-code search box |
| `/employees` | Employee list / CRUD |
| `/attendance?year=YYYY&month=M` | Monthly attendance |
| `/payroll?year=YYYY&month=M` | Company-wide payroll table |
| `/payroll/{id}/{year}/{month}` | Detailed payslip for one employee |
| `/payroll/{id}/{year}/{month}?print=1` | Payslip + auto-open the print dialog |
| `/settings` | Configure tax &amp; payroll formulas |
| `/help` | In-app user guide (sticky TOC + realtime search) |
| `/employees/template` | Download blank XLSX import template |
| `POST /employees/import` | Upload Excel file (phase 1: analyze duplicates) |
| `POST /employees/import/commit` | Confirm keep/overwrite after the popup (phase 2) |
| `POST /locale/{vi\|en}` | Switch language (persisted in session) |
| `GET /auth/setup` | First-time password creation (shown only when none exists) |
| `POST /auth/setup` | Save the new password and issue a recovery code |
| `GET /auth/recovery` | One-time display of the recovery code |
| `GET /auth/login` | Single-password sign-in form |
| `POST /auth/login` | Authenticate the password (rate-limited 5/min) |
| `POST /auth/logout` | Sign out and clear the session |
| `GET /auth/forgot` | Recovery-code-based password reset form |
| `POST /auth/forgot` | Reset the password and issue a new recovery code (rate-limited 3/5min) |

## Sample employee codes after seeding (10 employees)

| Code | Full name | Base salary (VND) | Position | Department | Dependants |
|---|---|---|---|---|---|
| NV001 | Nguyễn Văn An       |  8,000,000 | Production worker  | Workshop A | 1 |
| NV002 | Trần Thị Bích       | 15,000,000 | Team leader        | Workshop A | 2 |
| NV003 | Lê Quốc Cường       | 25,000,000 | Shop manager       | Workshop B | 0 |
| NV004 | Phạm Thu Dung       | 12,000,000 | Accountant         | Office     | 1 |
| NV005 | Hoàng Minh Đức      | 50,000,000 | Director           | Exec       | 2 |
| NV006 | Vũ Thị Hà           |  9,500,000 | Production worker  | Workshop A | 0 |
| NV007 | Đỗ Văn Khánh        | 10,000,000 | Production worker  | Workshop B | 2 |
| NV008 | Bùi Thị Lan         | 14,000,000 | QA technician      | Workshop B | 1 |
| NV009 | Ngô Thanh Mai       | 13,500,000 | HR officer         | Office     | 3 |
| NV010 | Trịnh Quang Phú     | 35,000,000 | VP Sales           | Exec       | 1 |

> **NV007 deliberately has unpaid leave days** during the month to demonstrate
> *diligence-bonus forfeiture*. Several employees have **half-day** records
> to test half-day pay (= diligence bonus / 2). Data covers **two months**
> (the full previous month + the current month up to today) so you can
> compare payrolls across months.

## Reset the database

To wipe everything and start fresh:
```bash
php artisan migrate:fresh --seed
```

## Tech Stack

- **Backend:** Laravel 12, PHP 8.3+
- **Database:** MySQL / MariaDB (web mode) · SQLite (desktop mode via NativePHP)
- **Frontend:** Blade templates, Bootstrap 5.3 (heavily themed)
- **Typography:** EB Garamond (serif body/figures), Inter (small-caps labels/buttons),
  IBM Plex Mono (keys, codes) — loaded from Google Fonts
- **Icons:** Bootstrap Icons 1.11
- **Excel I/O:** [maatwebsite/excel](https://github.com/SpartnerNL/Laravel-Excel) 3.1
  (built on PhpSpreadsheet) for the bulk-import &amp; template-download features
- **i18n:** Laravel JSON localization (`lang/vi.json` &amp; `lang/en.json`) with a
  session-backed `SetLocale` middleware
- **Theming:** CSS Custom Properties, `localStorage` + pre-flight `<script>` in
  the `<head>` to avoid FOUC
- **AJAX layer:** vanilla `fetch`-based `window.GZ` helper (`submitForm`,
  `ajaxDelete`, `softReload`) auto-wired to any `<form data-ajax="true">` or
  `[data-ajax-delete]` element — controllers return JSON when
  `$request->wantsJson()` is true, redirect otherwise (graceful fallback if
  JavaScript is disabled)
- **Authentication:** single-password gate (no username) — bcrypt-hashed
  password + recovery code stored in the `settings` table via the `AuthGate`
  service, guarded by the `RequirePassword` middleware, with rate limiting
  via Laravel's built-in `RateLimiter` facade. Session-only cookies
  (`SESSION_EXPIRE_ON_CLOSE=true`) so closing the browser ends the session.
- **Desktop packaging:** [NativePHP for Electron](https://nativephp.com/) 1.3.0
  bundles a PHP runtime + SQLite + the whole Laravel app into a single
  Electron app. The `NativeAppServiceProvider` configures the window
  (1280×820, remembers state, min 1024×640) and auto-seeds sample data on
  first launch. Compatibility patches applied via
  `scripts/apply-nativephp-patches.php` (re-run after every composer update).
- **Runtime:** XAMPP (web mode) · standalone .exe with bundled PHP (desktop mode)

## License

Internal project — for educational and reference purposes regarding Vietnamese PIT and payroll regulations.