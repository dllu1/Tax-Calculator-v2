<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Niên Giám Lương')) · {{ __('Niên Giám Lương') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        // Apply saved theme ASAP to avoid flash-of-wrong-theme
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
        html, body { transition: background 0.25s ease, color 0.25s ease; }

        html, body {
            background: var(--gz-bg);
            color: var(--gz-ink);
            font-family: 'EB Garamond', 'Cambria', Georgia, serif;
            font-size: 17px;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'EB Garamond', Georgia, serif;
            color: var(--gz-ink);
            font-weight: 600;
            letter-spacing: -0.01em;
        }
        h1 { font-size: 2.6rem; }
        h2 { font-size: 2rem; }
        h3 { font-size: 1.6rem; }
        h4 { font-size: 1.35rem; }
        h5 { font-size: 1.15rem; }

        a { color: var(--gz-accent); text-decoration: none; }
        a:hover { color: var(--gz-accent-2); text-decoration: underline; }

        code, pre, .mono {
            font-family: 'IBM Plex Mono', Consolas, monospace;
            font-size: 0.92em;
            color: var(--gz-ink-soft);
        }

        /* ===== MASTHEAD ===== */
        .gz-masthead {
            border-top: 3px solid var(--gz-ink);
            border-bottom: 1px solid var(--gz-rule);
            padding: 1.1rem 0 0.4rem;
            margin-bottom: 0;
            background: var(--gz-bg);
        }
        .gz-masthead-inner {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 1rem;
        }
        .gz-masthead-meta {
            font-family: 'Inter', sans-serif;
            font-size: 0.72rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--gz-ink-soft);
            line-height: 1.55;
        }
        .gz-masthead-meta strong { font-weight: 600; }
        .gz-masthead-meta.right { text-align: right; }
        .gz-masthead-title {
            font-family: 'EB Garamond', serif;
            font-size: 2.6rem;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
            text-align: center;
        }
        .gz-masthead-title em { font-style: italic; font-weight: 500; }
        .gz-masthead-title a { color: var(--gz-ink); text-decoration: none; }

        /* right column = tagline + controls */
        .gz-masthead-right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.9rem;
        }
        .gz-controls {
            display: inline-flex;
            gap: 0.35rem;
            align-items: center;
            border-left: 1px solid var(--gz-rule);
            padding-left: 0.9rem;
        }
        .gz-ctrl-btn {
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
            transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }
        .gz-ctrl-btn:hover {
            background: var(--gz-ink);
            color: var(--gz-surface);
            border-color: var(--gz-ink);
        }
        .gz-ctrl-btn.active {
            background: var(--gz-accent);
            color: #fff;
            border-color: var(--gz-accent);
        }
        .gz-ctrl-btn i { font-size: 0.95rem; }
        [data-theme="dark"] .gz-theme-light { display: inline-flex; }
        [data-theme="dark"] .gz-theme-dark { display: none; }
        [data-theme="light"] .gz-theme-light,
        :root:not([data-theme="dark"]) .gz-theme-light { display: none; }
        :root:not([data-theme="dark"]) .gz-theme-dark { display: inline-flex; }

        /* secondary rule under masthead */
        .gz-masthead-rule {
            border-top: 1px solid var(--gz-rule);
            margin-top: 4px;
        }

        /* ===== NAV ===== */
        .gz-nav {
            border-top: 1px solid var(--gz-rule);
            border-bottom: 1px solid var(--gz-rule);
            padding: 0.6rem 0;
            margin-bottom: 1.6rem;
            background: var(--gz-bg);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 8px -6px rgba(28, 26, 23, 0.15);
        }
        .gz-nav-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .gz-nav-links {
            display: flex;
            gap: 1.8rem;
            list-style: none;
            margin: 0; padding: 0;
            flex-wrap: wrap;
        }
        .gz-nav-links a {
            color: var(--gz-ink);
            font-family: 'Inter', sans-serif;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            padding-bottom: 2px;
            border-bottom: 2px solid transparent;
            text-decoration: none;
        }
        .gz-nav-links a:hover,
        .gz-nav-links a.active {
            border-bottom-color: var(--gz-accent);
            color: var(--gz-accent);
        }
        .gz-search input {
            background: transparent;
            border: none;
            border-bottom: 1px solid var(--gz-rule);
            border-radius: 0;
            font-family: 'EB Garamond', serif;
            font-style: italic;
            color: var(--gz-ink);
            padding: 0.15rem 0.4rem;
            min-width: 180px;
        }
        .gz-search input:focus {
            outline: none;
            border-bottom-color: var(--gz-accent);
            box-shadow: none;
            background: transparent;
        }
        .gz-search button {
            background: transparent;
            border: none;
            color: var(--gz-ink);
            padding: 0.15rem 0.5rem;
        }
        .gz-search button:hover { color: var(--gz-accent); }

        /* ===== SECTION HEADING (Roman + small caps) ===== */
        .gz-section-rule {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.8rem 0 0.6rem;
        }
        .gz-section-rule::before,
        .gz-section-rule::after {
            content: '';
            flex: 1;
            border-top: 1px solid var(--gz-rule);
        }
        .gz-section-rule-text {
            font-family: 'Inter', sans-serif;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.32em;
            text-transform: uppercase;
            color: var(--gz-ink-soft);
        }
        .gz-section-rule-text em {
            font-family: 'EB Garamond', serif;
            font-style: italic;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.05em;
            margin-right: 0.6rem;
            text-transform: none;
            color: var(--gz-ink);
        }
        .gz-section-title {
            font-family: 'EB Garamond', serif;
            font-weight: 600;
            font-size: 1.85rem;
            margin: 0 0 0.2rem;
            color: var(--gz-ink);
        }
        .gz-section-lede {
            font-style: italic;
            color: var(--gz-muted);
            margin-bottom: 1rem;
        }

        /* small caps label */
        .gz-label {
            font-family: 'Inter', sans-serif;
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gz-muted);
        }
        .gz-label.ink { color: var(--gz-ink); }

        /* ===== CARD / PANEL ===== */
        .gz-card {
            background: var(--gz-surface);
            border: 1px solid var(--gz-rule);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.3rem;
            box-shadow: none;
        }
        .gz-card-tight { padding: 0.9rem 1.1rem; }
        .gz-card-rule {
            border-top: 1px solid var(--gz-rule);
            margin: 1rem -1.5rem;
        }
        .gz-card-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 0.6rem;
            gap: 1rem;
        }

        /* ===== FIGURE (số liệu lớn) ===== */
        .gz-figure {
            font-family: 'EB Garamond', serif;
            font-weight: 500;
            font-size: 2.6rem;
            line-height: 1.05;
            letter-spacing: -0.01em;
            color: var(--gz-ink);
            font-variant-numeric: tabular-nums;
        }
        .gz-figure-sm {
            font-family: 'EB Garamond', serif;
            font-weight: 500;
            font-size: 1.6rem;
            line-height: 1.1;
            color: var(--gz-ink);
            font-variant-numeric: tabular-nums;
        }
        .gz-figure-unit {
            font-style: italic;
            font-size: 0.55em;
            color: var(--gz-muted);
            margin-left: 0.15rem;
            text-decoration: underline;
            text-decoration-color: var(--gz-rule);
            text-underline-offset: 0.25em;
        }
        .gz-figure-caption {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.75rem;
            color: var(--gz-muted);
            margin-top: 0.2rem;
        }
        .gz-figure-accent { color: var(--gz-accent); }
        .gz-figure-success { color: var(--gz-success); }

        /* ===== TABLES (gazette style — minimal rules) ===== */
        .gz-table {
            width: 100%;
            border-collapse: collapse;
            font-variant-numeric: tabular-nums;
        }
        .gz-table thead th {
            font-family: 'EB Garamond', serif;
            font-style: italic;
            font-weight: 500;
            color: var(--gz-muted);
            text-align: left;
            border-bottom: 1px solid var(--gz-rule);
            padding: 0.55rem 0.7rem;
            font-size: 0.95rem;
        }
        .gz-table tbody td {
            padding: 0.6rem 0.7rem;
            border-bottom: 1px solid var(--gz-rule-soft);
            vertical-align: middle;
        }
        .gz-table tbody tr:last-child td { border-bottom: 1px solid var(--gz-rule); }
        .gz-table .money { text-align: right; font-variant-numeric: tabular-nums; }
        .gz-table .num { text-align: center; }
        .gz-table tfoot td {
            padding: 0.7rem;
            border-top: 1px solid var(--gz-ink);
            border-bottom: 1px solid var(--gz-ink);
            font-weight: 600;
        }

        /* "ledger" two-column rows: label left, figure right */
        .gz-ledger { width: 100%; border-collapse: collapse; }
        .gz-ledger td {
            padding: 0.42rem 0;
            border-bottom: 1px dotted var(--gz-rule);
            vertical-align: baseline;
        }
        .gz-ledger td:first-child { color: var(--gz-ink-soft); }
        .gz-ledger td:last-child {
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-weight: 500;
        }
        .gz-ledger tr.section td {
            border-bottom: 1px solid var(--gz-rule);
            padding-top: 0.9rem;
            padding-bottom: 0.3rem;
            font-family: 'Inter', sans-serif;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gz-ink);
        }
        .gz-ledger tr.total td {
            border-top: 1px solid var(--gz-ink);
            border-bottom: 1px solid var(--gz-ink);
            padding-top: 0.7rem;
            padding-bottom: 0.7rem;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .gz-ledger tr.minus td:last-child { color: var(--gz-accent); }
        .gz-ledger tr.plus td:last-child { color: var(--gz-success); }

        /* ===== BUTTONS ===== */
        .btn {
            font-family: 'Inter', sans-serif;
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border-radius: 0;
            padding: 0.4rem 1.1rem;
            border-width: 1px;
            white-space: nowrap;
            line-height: 1.2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            flex-shrink: 0;
            min-height: 2.55rem;
        }
        .btn i { font-size: 1.05em; line-height: 1; }
        .btn-primary, .btn-primary:focus {
            background: var(--gz-ink);
            border-color: var(--gz-ink);
            color: var(--gz-surface);
        }
        .btn-primary:hover { background: var(--gz-accent); border-color: var(--gz-accent); color: #fff; }
        .btn-outline-primary {
            color: var(--gz-ink); border-color: var(--gz-ink); background: transparent;
        }
        .btn-outline-primary:hover { background: var(--gz-ink); color: var(--gz-surface); }
        .btn-outline-secondary {
            color: var(--gz-ink-soft); border-color: var(--gz-rule); background: transparent;
        }
        .btn-outline-secondary:hover { background: var(--gz-ink-soft); color: var(--gz-surface); border-color: var(--gz-ink-soft); }
        .btn-success, .btn-outline-success {
            color: var(--gz-success); border-color: var(--gz-success); background: transparent;
        }
        .btn-success { background: var(--gz-success); color: #fff; }
        .btn-success:hover { background: #244a2d; color: #fff; }
        .btn-outline-success:hover { background: var(--gz-success); color: #fff; }
        .btn-warning, .btn-outline-warning {
            color: var(--gz-warning); border-color: var(--gz-warning); background: transparent;
        }
        .btn-warning { background: var(--gz-warning); color: #fff; }
        .btn-outline-warning:hover { background: var(--gz-warning); color: #fff; }
        .btn-danger, .btn-outline-danger {
            color: var(--gz-danger); border-color: var(--gz-danger); background: transparent;
        }
        .btn-danger { background: var(--gz-danger); color: #fff; }
        .btn-outline-danger:hover { background: var(--gz-danger); color: #fff; }
        .btn-info, .btn-outline-info {
            color: var(--gz-ink); border-color: var(--gz-rule); background: transparent;
        }
        .btn-outline-info:hover { background: var(--gz-ink); color: var(--gz-surface); }
        .btn-light { background: transparent; border-color: var(--gz-rule); color: var(--gz-ink); }

        /* ===== BTN-CHECK: checked/active states (gazette theme overrides Bootstrap) ===== */
        .btn-check:checked + .btn-outline-success,
        .btn-check:active + .btn-outline-success,
        .btn-outline-success.active {
            background: var(--gz-success); border-color: var(--gz-success); color: #fff;
        }
        .btn-check:checked + .btn-outline-info,
        .btn-check:active + .btn-outline-info,
        .btn-outline-info.active {
            background: var(--gz-ink-soft); border-color: var(--gz-ink-soft); color: var(--gz-surface);
        }
        .btn-check:checked + .btn-outline-warning,
        .btn-check:active + .btn-outline-warning,
        .btn-outline-warning.active {
            background: var(--gz-warning); border-color: var(--gz-warning); color: #fff;
        }
        .btn-check:checked + .btn-outline-secondary,
        .btn-check:active + .btn-outline-secondary,
        .btn-outline-secondary.active {
            background: var(--gz-muted); border-color: var(--gz-muted); color: var(--gz-surface);
        }
        .btn-check:checked + .btn-outline-danger,
        .btn-check:active + .btn-outline-danger,
        .btn-outline-danger.active {
            background: var(--gz-accent); border-color: var(--gz-accent); color: #fff;
        }

        .btn-sm {
            font-size: 0.7rem;
            padding: 0.3rem 0.85rem;
            letter-spacing: 0.08em;
            gap: 0.35rem;
            min-height: 2.1rem;
        }
        .btn-lg {
            font-size: 0.85rem;
            padding: 0.55rem 1.6rem;
            letter-spacing: 0.12em;
            min-height: 3rem;
        }

        /* ===== FORMS ===== */
        .form-control, .form-select {
            background: var(--gz-surface);
            border: 1px solid var(--gz-rule);
            border-radius: 0;
            font-family: 'EB Garamond', serif;
            font-size: 1rem;
            color: var(--gz-ink);
            padding: 0.45rem 0.75rem;
            line-height: 1.5;
            min-height: 2.55rem;
        }
        .form-control:focus, .form-select:focus {
            background: #fff;
            border-color: var(--gz-ink);
            box-shadow: none;
            color: var(--gz-ink);
        }
        .form-control::placeholder { color: var(--gz-muted); font-style: italic; }
        .form-control-sm, .form-select-sm {
            font-size: 0.85rem;
            padding: 0.32rem 0.6rem;
            min-height: 2.1rem;
        }
        .form-control-lg, .form-select-lg {
            font-size: 1.15rem;
            padding: 0.55rem 0.9rem;
            min-height: 3rem;
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
        .form-check-input:checked {
            background-color: var(--gz-ink);
            border-color: var(--gz-ink);
        }
        .form-check-input { border-color: var(--gz-rule); }
        .input-group .form-control { border-right: 0; }
        .input-group .btn { border-color: var(--gz-rule); }

        /* ===== BADGES ===== */
        .badge {
            font-family: 'Inter', sans-serif;
            font-size: 0.65rem;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border-radius: 0;
            padding: 0.25rem 0.55rem;
            border: 1px solid currentColor;
            background: transparent !important;
        }
        .badge.bg-success, .bg-success-subtle { color: var(--gz-success) !important; }
        .badge.bg-warning, .badge.text-dark, .bg-warning-subtle { color: var(--gz-warning) !important; }
        .badge.bg-danger { color: var(--gz-danger) !important; }
        .badge.bg-secondary { color: var(--gz-muted) !important; }
        .badge.bg-primary { color: var(--gz-ink) !important; }
        .badge.solid {
            background: var(--gz-ink) !important;
            color: var(--gz-surface) !important;
            border-color: var(--gz-ink);
        }

        /* ===== ALERTS ===== */
        .alert {
            border-radius: 0;
            border: none;
            border-left: 3px solid var(--gz-ink);
            background: var(--gz-surface-2);
            color: var(--gz-ink);
            font-family: 'EB Garamond', serif;
            padding: 0.7rem 1rem;
        }
        .alert-success { border-left-color: var(--gz-success); }
        .alert-danger  { border-left-color: var(--gz-danger); }
        .alert-warning { border-left-color: var(--gz-warning); }
        .alert-info    { border-left-color: var(--gz-ink); }
        .alert-light   { border-left-color: var(--gz-rule); background: var(--gz-surface); }

        /* ===== TABS ===== */
        .nav-tabs {
            border-bottom: 1px solid var(--gz-rule);
        }
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--gz-muted);
            font-family: 'Inter', sans-serif;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            border-radius: 0;
            padding: 0.55rem 1rem;
            background: transparent;
        }
        .nav-tabs .nav-link:hover { color: var(--gz-ink); }
        .nav-tabs .nav-link.active {
            color: var(--gz-ink);
            background: transparent;
            border-bottom-color: var(--gz-accent);
        }
        .tab-content.gz-tab-body {
            border: 1px solid var(--gz-rule);
            border-top: none;
            background: var(--gz-surface);
            padding: 1.25rem 1.5rem;
        }

        /* ===== DROPCAP (lede paragraph) ===== */
        .gz-dropcap::first-letter {
            font-family: 'EB Garamond', serif;
            font-size: 4rem;
            font-weight: 600;
            float: left;
            line-height: 0.88;
            padding: 0.15rem 0.55rem 0 0;
            color: var(--gz-ink);
        }

        /* ===== FOOTER ===== */
        .gz-footer {
            margin-top: 3rem;
            border-top: 1px solid var(--gz-rule);
            padding: 1.2rem 0 1.6rem;
            text-align: center;
            font-style: italic;
            font-size: 0.85rem;
            color: var(--gz-muted);
        }

        /* helpers */
        .money { text-align: right; font-variant-numeric: tabular-nums; }
        .num   { text-align: center; font-variant-numeric: tabular-nums; }
        .text-muted, .text-secondary { color: var(--gz-muted) !important; }
        .text-success { color: var(--gz-success) !important; }
        .text-danger  { color: var(--gz-danger) !important; }
        .text-warning { color: var(--gz-warning) !important; }
        .text-primary { color: var(--gz-accent) !important; }
        .table-light, .bg-light { background: var(--gz-surface-2) !important; }
        .table { color: var(--gz-ink); }
        .shadow-sm, .shadow { box-shadow: none !important; }
        hr { border-color: var(--gz-rule); }

        /* attendance month grid colors (override badge-type-*) */
        .att-normal { background: #d6dfc8; color: var(--gz-ink); }
        .att-half   { background: #c7d4dc; color: var(--gz-ink); }
        .att-sunday { background: #e8d8b0; color: var(--gz-ink); }
        .att-leave  { background: #d6cdc0; color: var(--gz-ink-soft); }
        .att-absent { background: #c9a8a3; color: #4a1414; }
        .att-cell a { color: inherit !important; text-decoration: none; }

        /* attendance month — keep table simple bordered */
        .gz-grid-table { border-collapse: collapse; font-family: 'IBM Plex Mono', monospace; font-size: 0.78rem; }
        .gz-grid-table th, .gz-grid-table td {
            border: 1px solid var(--gz-rule);
            padding: 0.25rem;
            text-align: center;
            background: var(--gz-surface);
        }
        .gz-grid-table th { background: var(--gz-surface-2); font-weight: 500; color: var(--gz-ink); }
        .gz-grid-table .sunday-col { background: #ece2c4; }

        /* ===== PRINT / PDF ===== */
        @media print {
            @page {
                size: A4;
                margin: 12mm 14mm;
            }
            html, body {
                background: #fff !important;
                color: #000 !important;
                font-size: 11pt;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .gz-nav,
            .gz-footer,
            .no-print,
            .btn,
            .nav-tabs,
            form#attForm .btn-group { display: none !important; }
            /* keep masthead but tighter */
            .gz-masthead { padding-top: 0; margin-bottom: 0.5rem; }
            .gz-masthead-rule { margin-top: 2px; }
            main.container { padding: 0 !important; max-width: 100% !important; }
            .container { max-width: 100% !important; padding-left: 0; padding-right: 0; }
            .gz-card {
                background: var(--gz-surface) !important;
                break-inside: avoid;
                page-break-inside: avoid;
                margin-bottom: 0.6rem;
                padding: 0.6rem 0.9rem;
            }
            .gz-section-rule { margin: 0.8rem 0 0.3rem; break-after: avoid; }
            h1, h2, h3, h4 { break-after: avoid; }
            .gz-figure { font-size: 2.1rem; }
            .gz-figure-sm { font-size: 1.3rem; }
            .gz-ledger td { padding: 0.25rem 0; }
            .gz-table thead th { padding: 0.35rem 0.5rem; }
            .gz-table tbody td { padding: 0.35rem 0.5rem; }
            /* tab content always visible when printing */
            .tab-content > .tab-pane { display: block !important; opacity: 1 !important; }
            .tab-content { border: none !important; padding: 0 !important; background: transparent !important; }
            /* nice page break for the flow strip */
            .gz-cashflow { break-before: avoid; }
            a { color: inherit !important; text-decoration: none !important; }
            .alert { display: none !important; }
        }

        /* print-only watermark/footer info */
        .print-only { display: none; }
        @media print { .print-only { display: block; } }

        /* ===== PAYROLL SHOW — grid cells ===== */
        .gz-card-flush { padding: 0; }
        .gz-grid-cell {
            padding: 1rem 1.2rem;
            border-right: 1px solid var(--gz-rule);
        }
        .gz-grid-cell:last-child { border-right: none; }
        .gz-cashflow-cell {
            padding: 1rem;
            border-right: 1px solid var(--gz-rule);
        }
        .gz-cashflow-cell:last-child { border-right: none; }
        .gz-cashflow-cell.highlight { background: var(--gz-surface-2); }
        .gz-cashflow-step {
            font-style: italic;
            color: var(--gz-muted);
            font-size: 0.75rem;
        }
        .gz-cashflow-label {
            font-size: 0.85rem;
            color: var(--gz-ink-soft);
            margin-bottom: 0.4rem;
        }
        .gz-cashflow-delta {
            color: var(--gz-accent);
            font-size: 0.8rem;
            font-family: 'IBM Plex Mono', monospace;
        }

        /* row of icon-only action buttons in tables */
        .gz-actions {
            display: inline-flex;
            gap: 4px;
            flex-wrap: nowrap;
            justify-content: flex-end;
            vertical-align: middle;
        }
        .gz-actions .btn { padding: 0.3rem 0.55rem; min-width: 2.1rem; }
        .gz-actions form { display: inline-flex; margin: 0; }

        /* ===== ATTENDANCE SAVE BAR — sticky at viewport bottom ===== */
        /* Compact like the top nav. Default = container-width (natural position).
           When floating (.is-stuck added by JS), breaks out to full viewport width. */
        .att-save-bar {
            position: sticky;
            bottom: 0;
            background: var(--gz-bg);
            border-top: 1px solid var(--gz-rule);
            padding: 0.5rem 0.9rem;
            margin-top: 1rem;
            z-index: 100;
            transition: margin 0.18s ease, padding 0.18s ease, box-shadow 0.18s ease;
        }
        .att-save-bar.is-stuck {
            margin-left: calc(-1 * (100vw - 100%) / 2);
            margin-right: calc(-1 * (100vw - 100%) / 2);
            padding-left: calc((100vw - 100%) / 2);
            padding-right: calc((100vw - 100%) / 2);
            box-shadow: 0 -4px 8px -6px rgba(28, 26, 23, 0.18);
        }
        .att-save-bar small {
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--gz-muted);
        }
        .att-save-bar .btn {
            padding: 0.35rem 1.1rem;
            font-size: 0.75rem;
            min-height: 2.1rem;
        }
        @media print { .att-save-bar { display: none !important; } }

        /* ===== PAGINATION (Bootstrap 5) — restyled to gazette theme ===== */
        .pagination {
            font-family: 'Inter', sans-serif;
            font-size: 0.78rem;
            margin-bottom: 0;
            gap: 0;
        }
        .pagination .page-link {
            border: 1px solid var(--gz-rule);
            border-radius: 0 !important;
            background: var(--gz-surface);
            color: var(--gz-ink);
            padding: 0.35rem 0.8rem;
            margin-left: -1px;
            min-width: 2.1rem;
            text-align: center;
            font-weight: 500;
            letter-spacing: 0.04em;
            box-shadow: none;
            transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }
        .pagination .page-item:first-child .page-link { margin-left: 0; }
        .pagination .page-link:hover {
            background: var(--gz-ink);
            color: var(--gz-surface);
            border-color: var(--gz-ink);
            z-index: 2;
        }
        .pagination .page-link:focus {
            box-shadow: 0 0 0 2px var(--gz-rule);
            outline: none;
            z-index: 3;
        }
        .pagination .page-item.active .page-link {
            background: var(--gz-accent);
            border-color: var(--gz-accent);
            color: #fff;
            font-weight: 600;
            z-index: 1;
        }
        .pagination .page-item.disabled .page-link {
            background: var(--gz-surface-2);
            color: var(--gz-muted);
            border-color: var(--gz-rule);
            opacity: 0.6;
        }

        /* ===== PAYROLL INDEX — sticky header + totals footer inside scroll ===== */
        .payroll-scroll {
            border-bottom: 1px solid var(--gz-rule);
        }
        .payroll-table-sticky thead th {
            position: sticky;
            top: 0;
            background: var(--gz-surface-2);
            z-index: 5;
            box-shadow: 0 1px 0 var(--gz-rule);
        }
        .payroll-table-sticky tfoot tr {
            position: sticky;
            bottom: 0;
            background: var(--gz-surface-2);
            z-index: 5;
        }
        .payroll-table-sticky tfoot td {
            background: var(--gz-surface-2);
            border-top: 2px solid var(--gz-ink) !important;
            box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.08);
            font-weight: 700;
        }
        @media print {
            .payroll-scroll { max-height: none !important; overflow: visible !important; border: none !important; }
            .payroll-table-sticky thead th,
            .payroll-table-sticky tfoot tr,
            .payroll-table-sticky tfoot td {
                position: static !important;
                box-shadow: none !important;
            }
        }

        /* ===== PAYSLIP PRINT — compact 1-page payslip ===== */
        .payslip-print { display: none; }
        @media print {
            .payslip-print {
                display: block !important;
                font-family: 'Source Serif Pro', 'Times New Roman', serif;
                color: #000;
                font-size: 10pt;
                line-height: 1.35;
            }
            .payslip-header {
                position: relative;
                text-align: center;
                margin-bottom: 8pt;
            }
            .payslip-code-box {
                position: absolute;
                top: 0;
                right: 0;
                border: 0.7pt solid #000;
                padding: 2pt 10pt;
                font-size: 9.5pt;
                font-weight: 600;
                letter-spacing: 0.5pt;
            }
            .payslip-title {
                font-size: 16pt;
                font-weight: 700;
                margin: 0 0 2pt 0;
                letter-spacing: 1pt;
                font-family: 'Source Serif Pro', 'Times New Roman', serif;
            }
            .payslip-period {
                font-size: 11.5pt;
                font-style: italic;
                margin-bottom: 6pt;
            }
            .payslip-emp-row {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                margin: 6pt 0 8pt 0;
                font-size: 11pt;
            }
            .payslip-emp-name {
                font-weight: 700;
                letter-spacing: 0.5pt;
            }
            .payslip-emp-salary .lbl {
                font-style: italic;
                margin-right: 18pt;
                color: #000;
            }
            .payslip-emp-salary .val {
                font-weight: 600;
                font-variant-numeric: tabular-nums;
            }
            .payslip-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
                margin-bottom: 6pt;
            }
            .payslip-table thead th,
            .payslip-table tbody td {
                border: 0.6pt solid #000;
                padding: 3pt 6pt;
                vertical-align: middle;
            }
            .payslip-table thead th {
                font-weight: 600;
                font-style: italic;
                text-align: center;
                background: #fff;
            }
            .payslip-table .col-label { width: 40%; }
            .payslip-table .col-days { width: 14%; text-align: center; }
            .payslip-table .col-rate { width: 20%; text-align: right; }
            .payslip-table .col-amount { width: 26%; text-align: right; }
            .payslip-table td.num { text-align: right; font-variant-numeric: tabular-nums; }
            .payslip-table tr.strikethrough td {
                text-decoration: line-through;
                color: #444;
            }
            .payslip-table tr.total-row td {
                border-top: 1.2pt solid #000 !important;
                font-weight: 700;
                font-style: italic;
            }
            .payslip-table tr.total-row td.lbl { text-align: center; }
            .payslip-deduct td { border: 0.6pt solid #000; padding: 3pt 6pt; }
            .payslip-deduct td.rowhead {
                writing-mode: horizontal-tb;
                text-align: center;
                font-style: italic;
                font-weight: 600;
                width: 56pt;
            }
            .payslip-deduct td.num { text-align: right; font-variant-numeric: tabular-nums; }
            .payslip-deduct tr.net-row td {
                border-top: 1.2pt solid #000 !important;
                font-weight: 700;
            }
            .payslip-deduct tr.net-row td.lbl { text-align: center; font-style: italic; }

            .payslip-print { page-break-inside: auto; }
            .payslip-table tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<header class="gz-masthead">
    <div class="container">
        <div class="gz-masthead-inner">
            @php
                $now = now();
                $locale = app()->getLocale();
                if ($locale === 'en') {
                    $dow = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$now->dayOfWeek];
                    $monthName = ['','January','February','March','April','May','June','July','August','September','October','November','December'][$now->month];
                    $dateLine = $dow . ', ' . $monthName . ' ' . $now->day . ', ' . $now->year;
                } else {
                    $dow = ['Chủ Nhật','Thứ Hai','Thứ Ba','Thứ Tư','Thứ Năm','Thứ Sáu','Thứ Bảy'][$now->dayOfWeek];
                    $months = ['','Một','Hai','Ba','Tư','Năm','Sáu','Bảy','Tám','Chín','Mười','Mười Một','Mười Hai'];
                    $dateLine = $dow . ', ' . $now->day . ' Tháng ' . $months[$now->month] . ', ' . $now->year;
                }
            @endphp
            <div class="gz-masthead-meta">
                {{ __('Số') }} <strong>01</strong> · {{ __('Tập I') }}<br>
                {{ $dateLine }}
            </div>
            <h1 class="gz-masthead-title">
                <a href="{{ route('home') }}">@if ($locale === 'en') <em>Salary</em> Gazette @else Niên Giám <em>Lương</em> @endif</a>
            </h1>
            <div class="gz-masthead-right">
                <div class="gz-masthead-meta right">
                    {{ __('Máy tính TNCN') }}<br>
                    {{ __('& Quản lý lương') }}
                </div>
                <div class="gz-controls no-print">
                    {{-- Language toggle --}}
                    <form action="{{ route('locale.switch', 'vi') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="gz-ctrl-btn {{ $locale === 'vi' ? 'active' : '' }}"
                                title="Tiếng Việt">VI</button>
                    </form>
                    <form action="{{ route('locale.switch', 'en') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="gz-ctrl-btn {{ $locale === 'en' ? 'active' : '' }}"
                                title="English">EN</button>
                    </form>
                    {{-- Theme toggle --}}
                    <button type="button" id="themeToggle" class="gz-ctrl-btn" title="{{ __('Chế độ tối') }} / {{ __('Chế độ sáng') }}">
                        <i class="bi bi-moon-stars gz-theme-dark"></i>
                        <i class="bi bi-sun gz-theme-light"></i>
                    </button>
                    {{-- Logout --}}
                    <form action="{{ route('auth.logout') }}" method="POST" style="display:inline;"
                          onsubmit="return confirm('{{ __('Bạn có chắc muốn đăng xuất?') }}');">
                        @csrf
                        <button type="submit" class="gz-ctrl-btn" title="{{ __('Đăng xuất') }}">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="gz-masthead-rule"></div>
    </div>
</header>

<nav class="gz-nav">
    <div class="container gz-nav-inner">
        <ul class="gz-nav-links">
            @php $rn = request()->route() ? request()->route()->getName() : ''; @endphp
            <li><a href="{{ route('home') }}" class="{{ $rn === 'home' ? 'active' : '' }}">{{ __('Trang Nhất') }}</a></li>
            <li><a href="{{ route('employees.index') }}" class="{{ str_starts_with($rn, 'employees.') ? 'active' : '' }}">{{ __('Nhân Viên') }}</a></li>
            <li><a href="{{ route('attendance.index') }}" class="{{ str_starts_with($rn, 'attendance.') ? 'active' : '' }}">{{ __('Chấm Công') }}</a></li>
            <li><a href="{{ route('payroll.index') }}" class="{{ str_starts_with($rn, 'payroll.') ? 'active' : '' }}">{{ __('Bảng Lương') }}</a></li>
            <li><a href="{{ route('settings.index') }}" class="{{ str_starts_with($rn, 'settings.') ? 'active' : '' }}">{{ __('Cấu Hình') }}</a></li>
            <li><a href="{{ route('help.index') }}" class="{{ str_starts_with($rn, 'help.') ? 'active' : '' }}">{{ __('Hướng Dẫn') }}</a></li>
        </ul>
        <form action="{{ route('home.search') }}" method="POST" class="gz-search d-flex align-items-center">
            @csrf
            <input name="code" placeholder="{{ __('Tra cứu mã NV...') }}" required>
            <button type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>
</nav>

<main class="container pb-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="gz-footer container">
    {{ __('Niên Giám Lương') }} · {{ __('Máy tính TNCN') }} {{ __('& Quản lý lương') }} ·
    {{ __('In ngày') }} {{ now()->format('d/m/Y') }} ·
    Laravel {{ app()->version() }}
</footer>

{{-- Toast container for AJAX feedback --}}
<div id="gz-toast-stack" class="no-print" style="position:fixed; top:1rem; right:1rem; z-index:9999; display:flex; flex-direction:column; gap:0.5rem; pointer-events:none;"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ===== Theme toggle (persists in localStorage) =====
    (function () {
        const root = document.documentElement;
        const saved = localStorage.getItem('gz-theme') || 'light';
        root.setAttribute('data-theme', saved);
        const btn = document.getElementById('themeToggle');
        if (btn) {
            btn.addEventListener('click', () => {
                const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                localStorage.setItem('gz-theme', next);
            });
        }
    })();

    // ===== Global AJAX helpers =====
    window.GZ_I18N = {!! json_encode([
        'saved' => __('Đã lưu'),
        'deleted' => __('Đã xoá'),
        'error' => __('Có lỗi xảy ra'),
        'processing' => __('Đang xử lý...'),
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    window.GZ = (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const T = window.GZ_I18N || {};

        function toast(message, kind = 'success') {
            const stack = document.getElementById('gz-toast-stack');
            if (!stack) { console.log(message); return; }
            const el = document.createElement('div');
            const bg = kind === 'success' ? 'var(--gz-success, #2d5016)'
                     : kind === 'error'   ? 'var(--gz-accent, #6b1d1d)'
                     : 'var(--gz-ink, #2a2419)';
            el.style.cssText = `pointer-events:auto; background:${bg}; color:#f7eedd; padding:0.7rem 1rem; min-width:200px; max-width:380px; box-shadow:0 4px 12px rgba(0,0,0,.18); font-size:0.92rem; border-left:4px solid rgba(255,255,255,.35); opacity:0; transform:translateX(8px); transition:all 0.2s ease;`;
            el.textContent = message;
            stack.appendChild(el);
            requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'translateX(0)'; });
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'translateX(8px)';
                setTimeout(() => el.remove(), 250);
            }, 2800);
        }

        async function fetchJson(url, options = {}) {
            const opts = Object.assign({
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                credentials: 'same-origin',
            }, options);
            const res = await fetch(url, opts);
            const text = await res.text();
            let json = null;
            try { json = text ? JSON.parse(text) : {}; } catch (_) { json = { _raw: text }; }
            if (!res.ok) {
                const msg = json?.message || json?._raw?.slice(0, 200) || `HTTP ${res.status}`;
                throw new Error(msg);
            }
            return json;
        }

        async function submitForm(form, opts = {}) {
            const action = form.getAttribute('action') || location.href;
            const method = (form.getAttribute('method') || 'POST').toUpperCase();
            const fd = new FormData(form);
            // Honor Laravel's method spoofing via _method
            const spoof = fd.get('_method');
            const finalMethod = spoof ? String(spoof).toUpperCase() : method;
            const submitBtn = form.querySelector('button[type=submit], input[type=submit]');
            const oldLabel = submitBtn?.innerHTML;
            if (submitBtn) { submitBtn.disabled = true; submitBtn.dataset.gzBusy = '1'; }
            try {
                const data = await fetchJson(action, { method: finalMethod, body: fd });
                if (opts.onSuccess) opts.onSuccess(data, form);
                toast(data?.message || T.saved || 'Saved', 'success');
                return data;
            } catch (e) {
                toast(e.message || T.error || 'Error', 'error');
                throw e;
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    if (oldLabel) submitBtn.innerHTML = oldLabel;
                    delete submitBtn.dataset.gzBusy;
                }
            }
        }

        async function ajaxDelete(url, opts = {}) {
            const fd = new FormData();
            fd.append('_method', 'DELETE');
            try {
                const data = await fetchJson(url, { method: 'POST', body: fd });
                if (opts.onSuccess) opts.onSuccess(data);
                toast(data?.message || T.deleted || 'Deleted', 'success');
                return data;
            } catch (e) {
                toast(e.message || T.error || 'Error', 'error');
                throw e;
            }
        }

        async function softReload() {
            try {
                const res = await fetch(location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    credentials: 'same-origin',
                });
                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newMain = doc.querySelector('main');
                const curMain = document.querySelector('main');
                if (newMain && curMain) {
                    curMain.replaceWith(newMain);
                    // Notify pages so they can re-attach handlers to the swapped DOM.
                    document.dispatchEvent(new CustomEvent('gz:soft-reloaded'));
                }
            } catch (e) {
                console.warn('Soft reload failed:', e);
            }
        }

        // Auto-wire: any <form data-ajax="true"> intercepts submit
        document.addEventListener('submit', (e) => {
            const form = e.target.closest('form[data-ajax="true"]');
            if (!form) return;
            e.preventDefault();
            const confirmMsg = form.dataset.confirm;
            if (confirmMsg && !confirm(confirmMsg)) return;
            const softReloadAfter = form.dataset.softReload === 'true';
            const resetAfter = form.dataset.resetAfter === 'true';
            submitForm(form).then((data) => {
                if (resetAfter) form.reset();
                if (softReloadAfter) softReload();
            }).catch(() => {});
        });

        // Auto-wire: any element with [data-ajax-delete] sends DELETE on click
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-ajax-delete]');
            if (!btn) return;
            e.preventDefault();
            const url = btn.dataset.ajaxDelete || btn.getAttribute('href');
            const confirmMsg = btn.dataset.confirm;
            if (confirmMsg && !confirm(confirmMsg)) return;
            const rowSelector = btn.dataset.removeRow;
            const softReloadAfter = btn.dataset.softReload === 'true';
            ajaxDelete(url, {
                onSuccess: () => {
                    if (rowSelector) {
                        const row = btn.closest(rowSelector);
                        if (row) row.remove();
                    }
                    if (softReloadAfter) softReload();
                }
            }).catch(() => {});
        });

        return { toast, fetchJson, submitForm, ajaxDelete, softReload };
    })();

    // ===== exportPdf: open a print-ready route in the user's default browser =====
    // Electron's native print dialog has no preview; we POST to /pdf/open which
    // signs a temporary URL and hands it to Shell::openExternal — Edge/Chrome
    // then renders the page and the user presses Ctrl+P for a real print preview.
    window.exportPdf = async function (type, params) {
        const fd = new FormData();
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        fd.append('_token', csrf);
        fd.append('type', type);
        for (const [k, v] of Object.entries(params)) fd.append(k, v);
        try {
            await GZ.fetchJson('/pdf/open', { method: 'POST', body: fd });
            GZ.toast({!! json_encode(__('Đã mở trong trình duyệt — bấm Ctrl+P để in/lưu PDF'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}, 'success');
        } catch (e) {
            GZ.toast(e.message || {!! json_encode(__('Lỗi mở trang in'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}, 'error');
        }
    };

    // ===== openInSystem: trigger a file download via AJAX and open it with the OS default app =====
    // Replaces plain <a href> downloads, which don't fire a Save-As dialog inside Electron's
    // BrowserWindow. Server writes the file to ~/Downloads then Shell::openFile launches it.
    window.openInSystem = async function (url) {
        try {
            const res = await GZ.fetchJson(url, { method: 'GET' });
            const okMsg = res && res.path
                ? {!! json_encode(__('Đã lưu vào'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!} + ' ' + res.path
                : {!! json_encode(__('Đã mở file trong ứng dụng mặc định'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
            GZ.toast(okMsg, 'success');
        } catch (e) {
            GZ.toast(e.message || {!! json_encode(__('Lỗi mở file'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}, 'error');
        }
    };

    // ===== Preserve Bootstrap nav-tab state across soft-reloads =====
    // softReload() swaps <main>, so the server-rendered default (first tab)
    // wins unless we remember which tab the user was on.
    (function () {
        let activeTabSel = document.querySelector('.nav-tabs .nav-link.active')?.dataset.bsTarget || null;

        document.addEventListener('shown.bs.tab', (e) => {
            const target = e.target?.dataset?.bsTarget;
            if (target) activeTabSel = target;
        });

        document.addEventListener('gz:soft-reloaded', () => {
            if (!activeTabSel || typeof bootstrap === 'undefined') return;
            const trigger = document.querySelector(`.nav-tabs .nav-link[data-bs-target="${activeTabSel}"]`);
            if (trigger && !trigger.classList.contains('active')) {
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            }
        });
    })();
</script>
@stack('scripts')
</body>
</html>
