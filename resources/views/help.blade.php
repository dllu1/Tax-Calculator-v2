@extends('layouts.app')
@section('title', __('Hướng Dẫn'))

@php
    $locale = app()->getLocale();

    // Mỗi section: id, key tiếng Việt (cho __()), content (đoạn HTML)
    $sections = [
        [
            'id' => 'overview',
            'title' => __('Tổng quan'),
            'body' => $locale === 'en' ? '
                <p><strong>Salary Gazette</strong> is a web app for managing employee records, daily attendance,
                and monthly payroll — including Vietnamese Personal Income Tax (PIT) on the 5-bracket progressive
                table. The interface is laid out like a printed gazette: warm cream palette, EB Garamond serif,
                Roman-numeral section rules, and ledger tables.</p>
                <p>Use the navigation bar at the top to switch between sections. The bar sticks to the top of the
                screen while you scroll. Use the controls in the top-right of the masthead to switch language
                (VI / EN) or toggle light / dark mode.</p>
            ' : '
                <p><strong>Niên Giám Lương</strong> là ứng dụng web quản lý hồ sơ nhân viên, chấm công hàng ngày
                và tính bảng lương hàng tháng — bao gồm thuế Thu nhập cá nhân (TNCN) theo biểu lũy tiến 5 bậc.
                Giao diện được trình bày như một tờ niên giám in: tông màu cream, serif EB Garamond, mục lục số
                La Mã và bảng ledger.</p>
                <p>Dùng thanh điều hướng ở trên cùng để chuyển giữa các trang. Thanh này dán dính khi bạn cuộn.
                Dùng các nút ở góc trên bên phải của masthead để chuyển ngôn ngữ (VI / EN) hoặc bật / tắt
                giao diện tối.</p>
            ',
        ],
        [
            'id' => 'employees',
            'title' => __('Quản lý nhân viên'),
            'body' => $locale === 'en' ? '
                <p>Open <a href="/employees">Employees</a> to see the full list. Each row shows the code,
                full name, position, base salary, social-insurance salary, number of dependants and active status.</p>
                <ul>
                    <li><strong>Add an employee</strong>: click <em>"Thêm Nhân Viên"</em> (Add Employee) and fill in the form.
                        Required fields are marked with <span style="color:var(--gz-accent)">*</span>.</li>
                    <li><strong>Edit / delete</strong>: use the icon buttons at the end of each row (pencil = edit,
                        trash = delete). Delete asks for confirmation.</li>
                    <li><strong>Open payslip</strong>: the receipt icon opens this employee\'s payslip for the
                        current month.</li>
                    <li><strong>Search</strong>: use the box above the table to filter by code or name
                        (case-insensitive, partial match).</li>
                </ul>
            ' : '
                <p>Vào <a href="/employees">Nhân Viên</a> để xem danh sách đầy đủ. Mỗi dòng hiển thị mã, họ tên,
                chức vụ, lương căn bản, lương BHXH, số người phụ thuộc và trạng thái.</p>
                <ul>
                    <li><strong>Thêm nhân viên</strong>: bấm <em>"Thêm Nhân Viên"</em> và điền form. Các trường có
                        dấu <span style="color:var(--gz-accent)">*</span> là bắt buộc.</li>
                    <li><strong>Sửa / xóa</strong>: dùng các nút icon ở cuối mỗi dòng (bút = sửa, thùng rác = xóa).
                        Xóa sẽ hỏi xác nhận.</li>
                    <li><strong>Mở phiếu lương</strong>: icon hóa đơn mở phiếu lương tháng hiện tại của nhân viên đó.</li>
                    <li><strong>Tìm kiếm</strong>: dùng ô bên trên bảng để lọc theo mã hoặc họ tên (không phân biệt
                        hoa thường, khớp một phần).</li>
                </ul>
            ',
        ],
        [
            'id' => 'import',
            'title' => __('Import nhân viên từ Excel'),
            'body' => $locale === 'en' ? '
                <p>For onboarding many employees at once, use the <em>"Import Excel"</em> button at the top of the
                employee list.</p>
                <ol>
                    <li>Click <em>"Tải File Mẫu"</em> (Download Template) to get a properly-formatted blank XLSX
                        with two example rows.</li>
                    <li>Fill in the file in Excel / Google Sheets / LibreOffice. Required columns:
                        <code>ma_nv</code>, <code>ho_va_ten</code>. Optional: position, department, salaries,
                        dependants, etc.</li>
                    <li>Click <em>"Import Excel"</em>, choose the file, and submit.</li>
                    <li>If any employee codes already exist, a <strong>side-by-side comparison popup</strong>
                        appears showing old vs new values for each duplicate. Choose:
                        <ul>
                            <li><strong>Keep existing data</strong> — skip the duplicates, only create new rows.</li>
                            <li><strong>Overwrite with new data</strong> — update all duplicates with the file values.</li>
                        </ul>
                    </li>
                </ol>
                <p>Headers accept Vietnamese with diacritics and snake_case. <code>ma_nv</code>, <code>M&atilde; NV</code>
                and <code>employee_code</code> are all recognised.</p>
            ' : '
                <p>Để nhập nhiều nhân viên cùng lúc, dùng nút <em>"Import Excel"</em> ở đầu danh sách nhân viên.</p>
                <ol>
                    <li>Bấm <em>"Tải File Mẫu"</em> để lấy file XLSX trống đúng định dạng kèm 2 dòng ví dụ.</li>
                    <li>Điền vào file bằng Excel / Google Sheets / LibreOffice. Cột bắt buộc:
                        <code>ma_nv</code>, <code>ho_va_ten</code>. Cột tuỳ chọn: chức vụ, phòng ban, lương,
                        người phụ thuộc, v.v.</li>
                    <li>Bấm <em>"Import Excel"</em>, chọn file, gửi đi.</li>
                    <li>Nếu có mã NV đã tồn tại, một <strong>popup so sánh cũ ↔ mới</strong> sẽ hiện ra.
                        Chọn 1 trong 2:
                        <ul>
                            <li><strong>Giữ lại dữ liệu cũ</strong> — bỏ qua các dòng trùng, chỉ tạo dòng mới.</li>
                            <li><strong>Ghi đè bằng dữ liệu mới</strong> — cập nhật tất cả các dòng trùng theo file.</li>
                        </ul>
                    </li>
                </ol>
                <p>Header chấp nhận tiếng Việt có dấu lẫn snake_case. <code>ma_nv</code>, <code>Mã NV</code> và
                <code>employee_code</code> đều được hiểu.</p>
            ',
        ],
        [
            'id' => 'attendance',
            'title' => __('Chấm công hàng ngày'),
            'body' => $locale === 'en' ? '
                <p>The <a href="/attendance">Attendance</a> page lets you record one of four statuses for each
                employee on a given day:</p>
                <ul>
                    <li><strong>Đi làm</strong> — normal working day</li>
                    <li><strong>Chủ nhật</strong> — Sunday work, counts as 2 days by default (configurable)</li>
                    <li><strong>Có phép</strong> — paid leave (no salary, no diligence penalty)</li>
                    <li><strong>Không phép</strong> — unpaid absence (disqualifies the diligence bonus)</li>
                </ul>
                <p>You can also record <strong>overtime shifts</strong> (each = 3 hours = ½ day by default)
                in the rightmost column.</p>
                <p>Use the <em>"Đi làm hết"</em> bulk button to mark all employees as present in one click.
                The <a href="/attendance/month">monthly view</a> shows the whole month at a glance — click any
                cell to jump to that day.</p>
            ' : '
                <p>Trang <a href="/attendance">Chấm Công</a> cho phép ghi 1 trong 4 trạng thái cho mỗi nhân viên
                trong 1 ngày:</p>
                <ul>
                    <li><strong>Đi làm</strong> — ngày làm việc bình thường</li>
                    <li><strong>Chủ nhật</strong> — đi làm CN, tính 2 ngày công (mặc định, có thể đổi)</li>
                    <li><strong>Có phép</strong> — nghỉ phép (không lương nhưng không mất chuyên cần)</li>
                    <li><strong>Không phép</strong> — nghỉ không phép (mất tiền chuyên cần)</li>
                </ul>
                <p>Cũng có thể nhập <strong>số ca tăng ca</strong> (mỗi ca = 3 giờ = ½ ngày công, mặc định) ở cột
                ngoài cùng bên phải.</p>
                <p>Dùng nút <em>"Đi làm hết"</em> để chấm cả danh sách đi làm bằng 1 click. <a href="/attendance/month">
                Xem cả tháng</a> hiển thị toàn cảnh — bấm vào bất kỳ ô nào để nhảy tới ngày đó.</p>
            ',
        ],
        [
            'id' => 'payroll',
            'title' => __('Bảng lương & phiếu lương'),
            'body' => $locale === 'en' ? '
                <p>The <a href="/payroll">Payroll</a> page shows the entire company\'s payroll for the selected
                month: working days, total income, social insurance, PIT, advances, and net pay.</p>
                <p>Click the eye icon next to any row to open that employee\'s <strong>detailed payslip</strong> with:</p>
                <ul>
                    <li>Large net pay & PIT figures at the top</li>
                    <li>4-cell overview strip (gross / SI / personal deductions / assessable income)</li>
                    <li>A 5-column "monthly cash-flow" strip (01 → 05) showing how gross income is reduced step-by-step
                        to net pay</li>
                    <li>Side-by-side ledger of salary components vs PIT calculation</li>
                    <li>Tabs to edit piece-rate salary, allowances and advances</li>
                </ul>
            ' : '
                <p>Trang <a href="/payroll">Bảng Lương</a> hiển thị bảng lương toàn công ty cho tháng đã chọn:
                ngày công, tổng thu nhập, BHXH, thuế TNCN, tạm ứng và lương còn lại.</p>
                <p>Bấm icon con mắt ở mỗi dòng để mở <strong>phiếu lương chi tiết</strong> của nhân viên đó với:</p>
                <ul>
                    <li>2 ô số lớn ở trên cùng: Thực nhận và Thuế TNCN</li>
                    <li>Dải 4 ô tổng quan (Tổng thực nhận / BHXH / Giảm trừ gia cảnh / TN tính thuế)</li>
                    <li>Dải "dòng tiền tháng" 5 cột (01 → 05) cho thấy thu nhập gộp giảm dần qua từng bước thành
                        lương thực nhận</li>
                    <li>Bảng ledger 2 cột: cấu thành lương ↔ tính thuế TNCN</li>
                    <li>Các tab để chỉnh lương sản phẩm, phụ cấp và tạm ứng</li>
                </ul>
            ',
        ],
        [
            'id' => 'settings',
            'title' => __('Cấu hình công thức tính'),
            'body' => $locale === 'en' ? '
                <p>The <a href="/settings">Settings</a> page lets you edit the numbers used in tax & payroll
                calculations <em>without touching code</em>. Useful when the law or company policy changes.</p>
                <ul>
                    <li><strong>Tab 1 — PIT formula parameters</strong>: personal deduction, dependant deduction,
                        SI rate.</li>
                    <li><strong>Tab 2 — Progressive PIT brackets</strong>: add / remove / edit each bracket
                        (limit, rate, subtractor). Set limit = 0 for the top open-ended bracket.</li>
                    <li><strong>Tab 3 — Payroll formula parameters</strong>: standard working days per month,
                        meal allowance per day & per OT shift, Sunday multiplier, overtime multiplier.</li>
                </ul>
                <p>Changes apply <strong>immediately</strong> — payrolls are recalculated on next view. The
                <em>"Restore defaults"</em> button reverts every parameter to its original value.</p>
            ' : '
                <p>Trang <a href="/settings">Cấu Hình</a> cho phép sửa các tham số trong công thức tính thuế &
                lương <em>không cần đụng code</em>. Tiện khi luật hoặc chính sách công ty thay đổi.</p>
                <ul>
                    <li><strong>Tab 1 — Tham số công thức thuế TNCN</strong>: giảm trừ bản thân, giảm trừ người
                        phụ thuộc, tỉ lệ BHXH.</li>
                    <li><strong>Tab 2 — Biểu thuế lũy tiến</strong>: thêm / xoá / sửa từng bậc thuế (giới hạn,
                        thuế suất, khấu trừ). Đặt giới hạn = 0 cho bậc cao nhất.</li>
                    <li><strong>Tab 3 — Tham số công thức tính lương</strong>: số công chuẩn / tháng, tiền ăn / ngày
                        & / ca tăng ca, hệ số Chủ nhật, hệ số tăng ca.</li>
                </ul>
                <p>Thay đổi áp dụng <strong>ngay lập tức</strong> — bảng lương sẽ tính lại lần xem tiếp theo. Nút
                <em>"Khôi phục mặc định"</em> đưa toàn bộ tham số về giá trị gốc.</p>
            ',
        ],
        [
            'id' => 'pdf',
            'title' => __('Xuất PDF phiếu lương'),
            'body' => $locale === 'en' ? '
                <p>Both the company-wide payroll table and each individual payslip have a <em>"Xuất PDF"</em>
                (Export PDF) button.</p>
                <p>Clicking it opens the browser\'s print dialog. Choose <strong>"Save as PDF"</strong> as the
                destination (Chrome, Edge, Firefox all support this natively).</p>
                <p>The PDF keeps the full gazette typography — no styling is lost. Navigation, action buttons
                and the data-editing tabs are hidden automatically when printing.</p>
            ' : '
                <p>Cả trang bảng lương toàn công ty và mỗi phiếu lương cá nhân đều có nút
                <em>"Xuất PDF"</em>.</p>
                <p>Bấm vào sẽ mở dialog Print của trình duyệt. Chọn <strong>"Save as PDF"</strong> ở phần
                Destination (Chrome, Edge, Firefox đều hỗ trợ sẵn).</p>
                <p>File PDF giữ nguyên typography gazette — không mất style nào. Navigation, các nút thao tác
                và tab chỉnh dữ liệu sẽ tự ẩn khi in.</p>
            ',
        ],
        [
            'id' => 'i18n-theme',
            'title' => __('Chuyển ngôn ngữ & giao diện'),
            'body' => $locale === 'en' ? '
                <p>The top-right of the masthead has three small buttons:</p>
                <ul>
                    <li><strong>VI</strong> / <strong>EN</strong> — switch the interface language. The choice is
                        remembered in your session for as long as the browser session lasts.</li>
                    <li><strong>Moon / Sun icon</strong> — toggle dark / light mode. The choice is saved in
                        <code>localStorage</code> and applied immediately on every page load (no flash).</li>
                </ul>
                <p>The dark theme keeps the gazette feel: deep brown background, warm cream ink, faded burgundy
                accents.</p>
            ' : '
                <p>Góc trên bên phải của masthead có 3 nút nhỏ:</p>
                <ul>
                    <li><strong>VI</strong> / <strong>EN</strong> — chuyển ngôn ngữ giao diện. Lựa chọn được nhớ
                        trong session đến hết phiên trình duyệt.</li>
                    <li><strong>Icon Trăng / Mặt trời</strong> — bật / tắt chế độ tối. Lựa chọn được lưu trong
                        <code>localStorage</code> và áp dụng ngay khi tải trang (không bị flash).</li>
                </ul>
                <p>Chế độ tối giữ cảm giác gazette: nền nâu đậm, mực kem ấm, accent burgundy nhạt.</p>
            ',
        ],
        [
            'id' => 'launcher',
            'title' => __('Khởi động bằng 1 click'),
            'body' => $locale === 'en' ? '
                <p>The project ships with two batch files at the project root:</p>
                <ul>
                    <li><code>start.bat</code> — double-click to auto-start MySQL, run pending migrations,
                        start the Laravel server, and open the browser.</li>
                    <li><code>stop.bat</code> — double-click to stop the server (MySQL keeps running so other
                        XAMPP apps are not disturbed).</li>
                </ul>
                <p>If you want a single <code>.exe</code>, wrap <code>start.bat</code> with the free
                <a href="https://www.battoexeconverter.com/" target="_blank" rel="noopener">Bat To Exe Converter</a>.</p>
            ' : '
                <p>Dự án có sẵn 2 file batch ở thư mục gốc:</p>
                <ul>
                    <li><code>start.bat</code> — double-click sẽ tự khởi động MySQL, chạy migration mới (nếu có),
                        chạy Laravel server và mở trình duyệt.</li>
                    <li><code>stop.bat</code> — double-click để dừng server (MySQL vẫn chạy để các app XAMPP khác
                        không bị ảnh hưởng).</li>
                </ul>
                <p>Muốn 1 file <code>.exe</code>? Wrap <code>start.bat</code> bằng tool miễn phí
                <a href="https://www.battoexeconverter.com/" target="_blank" rel="noopener">Bat To Exe Converter</a>.</p>
            ',
        ],
    ];
@endphp

@section('content')

<style>
    .help-layout {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 2rem;
        align-items: start;
    }
    @media (max-width: 768px) {
        .help-layout { grid-template-columns: 1fr; }
    }
    .help-sidebar {
        position: sticky;
        top: 90px;
        border: 1px solid var(--gz-rule);
        background: var(--gz-surface);
        padding: 1rem;
        max-height: calc(100vh - 110px);
        overflow-y: auto;
    }
    .help-sidebar .gz-label { margin-bottom: 0.5rem; }
    .help-search {
        width: 100%;
        padding: 0.45rem 0.7rem;
        background: var(--gz-surface-2);
        border: 1px solid var(--gz-rule);
        font-family: 'EB Garamond', serif;
        font-style: italic;
        font-size: 0.95rem;
        color: var(--gz-ink);
        margin-bottom: 0.75rem;
    }
    .help-search:focus {
        outline: none;
        border-color: var(--gz-ink);
        background: var(--gz-surface);
    }
    .help-toc {
        list-style: none;
        padding: 0;
        margin: 0;
        counter-reset: toc;
    }
    .help-toc li { counter-increment: toc; }
    .help-toc a {
        display: block;
        padding: 0.4rem 0.5rem;
        color: var(--gz-ink-soft);
        text-decoration: none;
        font-size: 0.92rem;
        border-left: 2px solid transparent;
        border-bottom: 1px dotted var(--gz-rule-soft);
        transition: background 0.15s, border-color 0.15s, color 0.15s;
    }
    .help-toc a::before {
        content: counter(toc, upper-roman) ". ";
        font-style: italic;
        color: var(--gz-muted);
        margin-right: 0.3rem;
        font-size: 0.85em;
    }
    .help-toc a:hover {
        background: var(--gz-surface-2);
        border-left-color: var(--gz-accent);
        color: var(--gz-ink);
    }
    .help-toc a.active {
        border-left-color: var(--gz-accent);
        background: var(--gz-surface-2);
        color: var(--gz-ink);
        font-weight: 600;
    }
    .help-toc li.hidden, .help-section.hidden { display: none; }
    .help-section {
        margin-bottom: 2.5rem;
        scroll-margin-top: 90px;
    }
    .help-section h3 {
        font-size: 1.7rem;
        margin-bottom: 0.4rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--gz-rule);
    }
    .help-section p { margin-bottom: 0.7rem; }
    .help-section ul, .help-section ol { margin-bottom: 0.7rem; padding-left: 1.5rem; }
    .help-section li { margin-bottom: 0.35rem; }
    .help-section code {
        background: var(--gz-surface-2);
        padding: 0.05rem 0.35rem;
        border: 1px solid var(--gz-rule-soft);
    }
    .help-empty {
        color: var(--gz-muted);
        font-style: italic;
        padding: 1rem;
        text-align: center;
        display: none;
        border: 1px dashed var(--gz-rule);
    }
    .help-empty.show { display: block; }
</style>

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>{{ $locale === 'en' ? 'I' : 'I' }}</em> {{ __('Hướng dẫn sử dụng') }}</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">{{ __('Hướng dẫn sử dụng') }}</h2>
        <p class="gz-section-lede mb-0">
            {{ __('Sổ tay tra cứu nhanh tất cả các chức năng — bấm vào tiêu đề bên trái để cuộn đến phần tương ứng, hoặc gõ từ khoá vào ô tìm kiếm.') }}
        </p>
    </div>
</div>

<div class="help-layout">
    {{-- ============ SIDEBAR ============ --}}
    <aside class="help-sidebar" id="helpSidebar">
        <div class="gz-label">{{ __('Mục lục') }}</div>
        <input
            type="search"
            id="helpSearch"
            class="help-search"
            placeholder="{{ __('Tìm kiếm trong hướng dẫn...') }}"
            autocomplete="off">
        <ul class="help-toc" id="helpToc">
            @foreach ($sections as $s)
                <li data-title="{{ mb_strtolower($s['title']) }}">
                    <a href="#{{ $s['id'] }}">{{ $s['title'] }}</a>
                </li>
            @endforeach
        </ul>
        <div class="help-empty" id="helpEmptyToc">{{ __('Không tìm thấy mục nào khớp với từ khoá.') }}</div>
    </aside>

    {{-- ============ CONTENT ============ --}}
    <article id="helpContent">
        @foreach ($sections as $i => $s)
            <section class="help-section" id="{{ $s['id'] }}" data-title="{{ mb_strtolower($s['title']) }}">
                <h3>{{ $i + 1 }}. {{ $s['title'] }}</h3>
                {!! $s['body'] !!}
            </section>
        @endforeach
        <div class="help-empty" id="helpEmptyContent">{{ __('Không tìm thấy mục nào khớp với từ khoá.') }}</div>
    </article>
</div>

@push('scripts')
<script>
(function () {
    const tocLinks = document.querySelectorAll('#helpToc a');
    const tocItems = document.querySelectorAll('#helpToc li');
    const sections = document.querySelectorAll('.help-section');
    const search   = document.getElementById('helpSearch');
    const emptyToc = document.getElementById('helpEmptyToc');
    const emptyContent = document.getElementById('helpEmptyContent');

    // ===== Smooth scroll on TOC click + active highlight =====
    tocLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const target = document.querySelector(link.getAttribute('href'));
            if (!target) return;
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.replaceState(null, '', link.getAttribute('href'));
        });
    });

    // ===== Realtime search =====
    function normalize(s) {
        return (s || '').toString().toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/đ/g, 'd');
    }

    search?.addEventListener('input', () => {
        const q = normalize(search.value.trim());
        let visibleCount = 0;
        tocItems.forEach((li) => {
            const title = normalize(li.dataset.title);
            const matches = q === '' || title.includes(q);
            li.classList.toggle('hidden', !matches);
            if (matches) visibleCount++;
        });
        sections.forEach((sec) => {
            const title = normalize(sec.dataset.title);
            const body  = normalize(sec.textContent);
            const matches = q === '' || title.includes(q) || body.includes(q);
            sec.classList.toggle('hidden', !matches);
        });
        emptyToc.classList.toggle('show', visibleCount === 0 && q !== '');
        const anySection = [...sections].some(s => !s.classList.contains('hidden'));
        emptyContent.classList.toggle('show', !anySection && q !== '');
    });

    // ===== Scroll-spy: highlight TOC item of section in view =====
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                tocLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === '#' + id));
            }
        });
    }, { rootMargin: '-30% 0px -60% 0px', threshold: 0 });
    sections.forEach(s => observer.observe(s));

    // ===== Activate from hash on load =====
    if (location.hash) {
        const link = document.querySelector('#helpToc a[href="' + location.hash + '"]');
        if (link) link.classList.add('active');
    }
})();
</script>
@endpush
@endsection
