<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Đăng nhập')) · {{ __('Niên Giám Lương') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        (function () {
            try {
                var t = localStorage.getItem('gz-theme') || 'light';
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) { document.documentElement.setAttribute('data-theme', 'light'); }
        })();
    </script>
    <style>
        :root, [data-theme="light"] {
            --gz-bg:        #efe5cd;
            --gz-surface:   #faf3e1;
            --gz-surface-2: #f3e9cf;
            --gz-ink:       #1c1a17;
            --gz-ink-soft:  #3a3026;
            --gz-muted:     #8a7d68;
            --gz-rule:      #c9bb9a;
            --gz-rule-soft: #d9cdaf;
            --gz-accent:    #7a1f1f;
            --gz-accent-2:  #5a1818;
            --gz-success:   #2f5d3a;
            --gz-warning:   #8a5a1f;
            --gz-danger:    #7a1f1f;
        }
        [data-theme="dark"] {
            --gz-bg:        #1a1612;
            --gz-surface:   #261f18;
            --gz-surface-2: #322a23;
            --gz-ink:       #ebe1cc;
            --gz-ink-soft:  #d9cdaf;
            --gz-muted:     #9e8f76;
            --gz-rule:      #4a3f33;
            --gz-rule-soft: #3a3128;
            --gz-accent:    #d68a8a;
            --gz-accent-2:  #f0a8a8;
            --gz-success:   #95c5a3;
            --gz-warning:   #d4a364;
            --gz-danger:    #d68a8a;
        }
        html, body {
            background: var(--gz-bg);
            color: var(--gz-ink);
            font-family: 'EB Garamond', 'Cambria', Georgia, serif;
            font-size: 17px;
            line-height: 1.55;
            min-height: 100vh;
            transition: background 0.25s ease, color 0.25s ease;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3 { font-family: 'EB Garamond', Georgia, serif; color: var(--gz-ink); font-weight: 600; }
        a { color: var(--gz-accent); text-decoration: none; }
        a:hover { color: var(--gz-accent-2); text-decoration: underline; }

        .auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: var(--gz-surface);
            border: 1px solid var(--gz-rule);
            border-top: 3px solid var(--gz-ink);
            padding: 2.4rem 2.6rem;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 10px 40px -20px rgba(28, 26, 23, 0.25);
        }
        .auth-masthead {
            text-align: center;
            border-bottom: 1px solid var(--gz-rule);
            padding-bottom: 1rem;
            margin-bottom: 1.6rem;
        }
        .auth-masthead-meta {
            font-family: 'Inter', sans-serif;
            font-size: 0.68rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gz-muted);
            margin-bottom: 0.4rem;
        }
        .auth-masthead-title {
            font-family: 'EB Garamond', serif;
            font-size: 1.9rem;
            font-weight: 600;
            line-height: 1.1;
            color: var(--gz-ink);
            margin: 0;
        }
        .auth-masthead-title em { font-style: italic; font-weight: 500; }
        .auth-title {
            font-size: 1.45rem;
            text-align: center;
            margin: 0 0 0.4rem;
        }
        .auth-lede {
            font-style: italic;
            text-align: center;
            color: var(--gz-muted);
            margin-bottom: 1.4rem;
            font-size: 0.95rem;
        }
        .form-label {
            font-family: 'Inter', sans-serif;
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--gz-muted);
            margin-bottom: 0.3rem;
        }
        .form-control {
            background: var(--gz-bg);
            border: 1px solid var(--gz-rule);
            border-radius: 0;
            font-family: 'EB Garamond', serif;
            font-size: 1rem;
            color: var(--gz-ink);
            padding: 0.5rem 0.75rem;
            min-height: 2.65rem;
        }
        .form-control:focus {
            background: #fff;
            border-color: var(--gz-ink);
            box-shadow: none;
            color: var(--gz-ink);
        }
        .btn {
            font-family: 'Inter', sans-serif;
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            border-radius: 0;
            padding: 0.55rem 1.2rem;
            border-width: 1px;
            min-height: 2.7rem;
        }
        .btn-primary, .btn-primary:focus {
            background: var(--gz-ink);
            border-color: var(--gz-ink);
            color: var(--gz-surface);
        }
        .btn-primary:hover {
            background: var(--gz-accent);
            border-color: var(--gz-accent);
            color: #fff;
        }
        .btn-outline-secondary {
            color: var(--gz-ink-soft);
            border-color: var(--gz-rule);
            background: transparent;
        }
        .btn-outline-secondary:hover {
            background: var(--gz-ink-soft);
            color: var(--gz-surface);
            border-color: var(--gz-ink-soft);
        }
        .alert {
            border-radius: 0;
            border: none;
            border-left: 3px solid var(--gz-ink);
            background: var(--gz-surface-2);
            color: var(--gz-ink);
            font-family: 'EB Garamond', serif;
            padding: 0.6rem 0.9rem;
            font-size: 0.95rem;
        }
        .alert-danger { border-left-color: var(--gz-danger); }
        .alert-success { border-left-color: var(--gz-success); }
        .alert-warning { border-left-color: var(--gz-warning); }
        .invalid-feedback {
            display: block;
            font-style: italic;
            font-size: 0.88rem;
            color: var(--gz-danger);
            margin-top: 0.25rem;
        }

        .auth-footer {
            margin-top: 1.6rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gz-rule);
            text-align: center;
            font-size: 0.85rem;
            color: var(--gz-muted);
            font-style: italic;
        }
        .auth-corner {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.3rem;
        }
        .auth-corner-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: 1px solid var(--gz-rule);
            background: var(--gz-surface);
            color: var(--gz-ink);
            font-family: 'Inter', sans-serif;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            cursor: pointer;
            text-decoration: none;
        }
        .auth-corner-btn.active {
            background: var(--gz-accent);
            color: #fff;
            border-color: var(--gz-accent);
        }
        .auth-corner-btn:hover {
            background: var(--gz-ink);
            color: var(--gz-surface);
            border-color: var(--gz-ink);
        }
        [data-theme="dark"] .gz-theme-light { display: inline-flex; }
        [data-theme="dark"] .gz-theme-dark { display: none; }
        [data-theme="light"] .gz-theme-light,
        :root:not([data-theme="dark"]) .gz-theme-light { display: none; }
        :root:not([data-theme="dark"]) .gz-theme-dark { display: inline-flex; }

        .recovery-code {
            font-family: 'IBM Plex Mono', Consolas, monospace;
            font-size: 1.5rem;
            font-weight: 500;
            text-align: center;
            padding: 1rem 0.5rem;
            background: var(--gz-bg);
            border: 1px dashed var(--gz-ink);
            color: var(--gz-ink);
            letter-spacing: 0.12em;
            user-select: all;
            margin: 1rem 0;
        }
        .recovery-warn {
            background: var(--gz-surface-2);
            border-left: 3px solid var(--gz-warning);
            padding: 0.7rem 0.9rem;
            font-size: 0.9rem;
            color: var(--gz-ink-soft);
            font-style: italic;
            margin-bottom: 1rem;
        }
        .meta-link {
            text-align: center;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

<div class="auth-corner">
    @php $locale = app()->getLocale(); @endphp
    <form action="{{ route('locale.switch', 'vi') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="auth-corner-btn {{ $locale === 'vi' ? 'active' : '' }}" title="Tiếng Việt">VI</button>
    </form>
    <form action="{{ route('locale.switch', 'en') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="auth-corner-btn {{ $locale === 'en' ? 'active' : '' }}" title="English">EN</button>
    </form>
    <button type="button" id="themeToggle" class="auth-corner-btn" title="{{ __('Chế độ tối') }} / {{ __('Chế độ sáng') }}">
        <i class="bi bi-moon-stars gz-theme-dark"></i>
        <i class="bi bi-sun gz-theme-light"></i>
    </button>
</div>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-masthead">
            <div class="auth-masthead-meta">{{ __('Máy tính TNCN') }} · {{ __('& Quản lý lương') }}</div>
            <h1 class="auth-masthead-title">
                @if ($locale === 'en') <em>Salary</em> Gazette @else Niên Giám <em>Lương</em> @endif
            </h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @yield('content')

        <div class="auth-footer">
            {{ __('In ngày') }} {{ now()->format('d/m/Y') }} · Laravel {{ app()->version() }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const root = document.documentElement;
        const btn = document.getElementById('themeToggle');
        if (btn) {
            btn.addEventListener('click', () => {
                const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                localStorage.setItem('gz-theme', next);
            });
        }
    })();
</script>
@stack('scripts')
</body>
</html>
