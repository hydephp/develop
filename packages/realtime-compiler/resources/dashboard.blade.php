@php /** @var \Hyde\RealtimeCompiler\Http\DashboardController $dashboard */ @endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ $csrfToken }}">
    <title>{{ $title }}</title>
    <base target="_parent">
    <style>
        :root {
            --bg:            #0a0b0f;
            --surface:       #12141a;
            --surface-2:     #191c24;
            --border:        #23262f;
            --border-soft:   #1a1d25;
            --text:          #e8e9ee;
            --text-muted:    #8b8f9c;
            --text-faint:    #5c606c;

            --blue:          #5b8def;
            --blue-soft:     rgba(91, 141, 239, .14);
            --purple:        #a78bfa;
            --purple-soft:   rgba(167, 139, 250, .14);
            --teal:          #2dd4bf;
            --teal-soft:     rgba(45, 212, 191, .14);
            --amber:         #f0b429;
            --amber-soft:    rgba(240, 180, 41, .14);
            --orange:        #f2884b;
            --orange-soft:   rgba(242, 136, 75, .14);
            --red:           #f0575c;
            --red-soft:      rgba(240, 87, 92, .14);
            --green:         #34d399;
            --green-soft:    rgba(52, 211, 153, .14);

            --radius:        12px;
            --radius-sm:     8px;
            --font-sans:     -apple-system, ui-sans-serif, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            --font-mono:     ui-monospace, "JetBrains Mono", "SF Mono", "Cascadia Code", Menlo, Consolas, monospace;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--font-sans);
            font-size: 14px;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            color: var(--blue);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        code, .mono {
            font-family: var(--font-mono);
        }

        :focus-visible {
            outline: 2px solid var(--blue);
            outline-offset: 2px;
            border-radius: 4px;
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: .001ms !important;
                transition-duration: .001ms !important;
            }
        }

        /* ---------- Status ---------- */

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .pill-amber {
            background: var(--amber-soft);
            color: var(--amber);
        }

        /* ---------- Buttons ---------- */

        .btn {
            appearance: none;
            border: 1px solid var(--border);
            background: var(--surface-2);
            color: var(--text);
            font: inherit;
            font-size: 13px;
            font-weight: 500;
            padding: 7px 13px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            line-height: 1.2;
            transition: border-color .12s ease, background .12s ease, color .12s ease;
        }

        .btn:hover {
            border-color: #33384a;
            background: #1d212b;
            text-decoration: none;
        }

        .btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .btn svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        .btn-primary {
            background: var(--blue);
            border-color: var(--blue);
            color: #0a0b0f;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #7aa3f2;
            border-color: #7aa3f2;
        }

        .btn-primary:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .btn-danger {
            background: var(--red-soft);
            border-color: rgba(240, 87, 92, .34);
            color: #ffb3b5;
        }

        .btn-danger:hover {
            background: rgba(240, 87, 92, .22);
            border-color: var(--red);
            color: #ffd0d1;
        }

        .btn-ghost {
            background: transparent;
            border-color: transparent;
            color: var(--text-muted);
            padding: 6px 8px;
        }

        .btn-ghost:hover {
            background: var(--surface-2);
            color: var(--text);
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        /* ---------- Layout / cards ---------- */

        main {
            flex: 1;
            max-width: 1080px;
            width: 100%;
            margin: 0 auto;
            padding: 32px 24px 48px;
        }

        .intro {
            margin-bottom: 28px;
        }

        .intro-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 6px;
        }

        .intro-title {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .intro h1 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            letter-spacing: -.01em;
        }

        .intro-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .intro p {
            margin: 0;
            color: var(--text-muted);
            font-size: 13.5px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-soft);
        }

        .card-header h2 {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--text-faint);
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        /* ---------- Info chips (project info) ---------- */

        .chip-grid {
            display: grid;
            grid-template-columns: minmax(280px, 2fr) repeat(3, minmax(max-content, 1fr));
            gap: 18px 34px;
            align-items: start;
        }

        .info-chip {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
        }

        .info-chip:not(.info-chip-path) {
            justify-self: center;
            white-space: nowrap;
        }

        .info-chip .swatch {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .info-chip .swatch svg {
            width: 17px;
            height: 17px;
        }

        .info-chip .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-faint);
            margin-bottom: 3px;
        }

        .info-chip .value {
            font-size: 13px;
            color: var(--text);
            word-break: break-word;
        }

        .info-chip .value-row {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .info-chip-path .value-row {
            align-items: flex-start;
        }

        .info-chip-path .value-row span {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .copy-btn {
            border: none;
            background: transparent;
            color: var(--text-faint);
            cursor: pointer;
            padding: 2px;
            display: inline-flex;
            border-radius: 5px;
            flex-shrink: 0;
        }

        .copy-btn:hover {
            color: var(--text);
            background: var(--surface-2);
        }

        .copy-btn svg {
            width: 14px;
            height: 14px;
        }

        /* ---------- Route list ---------- */

        .route-list {
            overflow-x: auto;
            margin: -0.5rem 0;
        }

        .route-group-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--text-faint);
            padding: 10px 12px 8px;
            margin-top: 8px;
        }

        .route-group:first-child .route-group-label {
            margin-top: 0;
        }

        .route-group-label .count {
            font-weight: 500;
            letter-spacing: 0;
            text-transform: none;
            color: var(--text-faint);
        }

        .route-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 10px 12px;
            border-top: 1px solid var(--border-soft);
        }

        .route-row:hover {
            background: var(--surface-2);
        }

        .route-file {
            flex: 1;
            min-width: 0;
        }

        .route-path-line {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            white-space: nowrap;
        }

        .route-path-line svg {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
        }

        .route-path-line svg.icon-muted {
            color: var(--text-faint);
        }

        .route-path-src {
            color: var(--text);
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .route-path-src.editable {
            cursor: pointer;
        }

        .route-path-src.editable:hover {
            text-decoration: underline;
            text-decoration-style: dotted;
        }

        .route-path-out {
            color: var(--text-muted);
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .route-output-path {
            display: inline-flex;
            align-items: baseline;
            min-width: 0;
            overflow: hidden;
        }

        .route-output-path .route-path-out {
            min-width: 0;
        }

        .route-key-link {
            color: var(--text);
            font-weight: 500;
            text-decoration: none;
        }

        .route-key-link:hover {
            color: var(--blue);
            text-decoration: underline;
        }

        .route-actions-col {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 2px;
            flex-shrink: 0;
            border-left: 1px solid var(--border-soft);
            padding-left: 12px;
        }

        .route-actions-col .btn {
            padding: 6px;
        }

        .route-actions-col .route-action {
            position: relative;
            display: inline-flex;
        }

        .route-actions-col .route-action::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 50%;
            bottom: calc(100% + 6px);
            z-index: 20;
            transform: translateX(-50%);
            pointer-events: none;
            white-space: nowrap;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: #0f1117;
            color: var(--text);
            font-size: 11px;
            font-weight: 500;
            line-height: 1;
            padding: 6px 8px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .28);
            opacity: 0;
            transition: opacity .08s ease;
        }

        .route-actions-col .route-action::before {
            content: "";
            position: absolute;
            left: 50%;
            bottom: calc(100% + 1px);
            z-index: 21;
            width: 7px;
            height: 7px;
            transform: translateX(-50%) rotate(45deg);
            pointer-events: none;
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            background: #0f1117;
            opacity: 0;
            transition: opacity .08s ease;
        }

        .route-actions-col .route-action:hover::after,
        .route-actions-col .route-action:hover::before,
        .route-actions-col .route-action:focus-within::after,
        .route-actions-col .route-action:focus-within::before {
            opacity: 1;
        }

        .route-actions-col .btn svg {
            width: 15px;
            height: 15px;
        }

        .route-actions-col .btn-delete {
            color: var(--text-muted);
        }

        .route-actions-col .btn-delete:hover {
            color: var(--red);
            background: var(--red-soft);
        }

        .just-created {
            animation: rowFadeIn 2.2s ease-out;
        }

        @keyframes rowFadeIn {
            0% { background-color: var(--green-soft); }
            100% { background-color: transparent; }
        }

        /* ---------- Empty states ---------- */

        .empty-state {
            border: 1px dashed var(--border);
            border-radius: var(--radius-sm);
            padding: 28px 20px;
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
        }

        .empty-state strong {
            display: block;
            color: var(--text);
            font-size: 13.5px;
            margin-bottom: 3px;
        }

        /* ---------- Media library ---------- */

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
        }

        .media-card {
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            overflow: hidden;
            background: var(--surface-2);
            display: flex;
            flex-direction: column;
        }

        .media-preview {
            position: relative;
            height: 140px;
            background: #0d0f14;
        }

        .media-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .media-preview .file-preview {
            height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 11px;
            background:
                    radial-gradient(circle at 35% 20%, rgba(var(--media-brand-rgb), .32), transparent 42%),
                    linear-gradient(135deg, rgba(var(--media-brand-rgb), .20), rgba(var(--media-brand-rgb), .08));
        }

        .media-preview .file-preview-mark {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--media-brand);
            color: var(--media-mark-color, #fff);
            font-family: var(--font-mono);
            font-size: 17px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0;
            box-shadow: 0 12px 24px rgba(0, 0, 0, .22);
        }

        .media-preview .file-preview-label {
            max-width: calc(100% - 24px);
            color: var(--media-brand);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .media-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(10, 11, 15, 0);
            opacity: 0;
            transition: opacity .15s ease, background .15s ease;
        }

        .media-card:hover .media-overlay {
            opacity: 1;
            background: rgba(10, 11, 15, .55);
        }

        .media-overlay .btn {
            background: rgba(18, 20, 26, .9);
            backdrop-filter: blur(3px);
        }

        .media-meta {
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .file-chip {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            font-family: var(--font-mono);
            background: var(--blue-soft);
            color: var(--blue);
        }

        .file-chip[data-type="png"], .file-chip[data-type="jpg"], .file-chip[data-type="jpeg"],
        .file-chip[data-type="gif"], .file-chip[data-type="svg"], .file-chip[data-type="ico"],
        .file-chip[data-type="webp"] {
            background: var(--teal-soft);
            color: var(--teal);
        }

        .file-chip[data-type="js"], .file-chip[data-type="css"], .file-chip[data-type="json"],
        .file-chip[data-type="yml"], .file-chip[data-type="yaml"], .file-chip[data-type="xml"] {
            background: var(--purple-soft);
            color: var(--purple);
        }

        .file-chip[data-type="mp3"], .file-chip[data-type="wav"], .file-chip[data-type="flac"],
        .file-chip[data-type="m4a"] {
            background: var(--amber-soft);
            color: var(--amber);
        }

        .file-chip[data-type="mp4"], .file-chip[data-type="mov"], .file-chip[data-type="mkv"],
        .file-chip[data-type="avi"] {
            background: var(--red-soft);
            color: var(--red);
        }

        .media-meta .info {
            min-width: 0;
        }

        .media-meta .name {
            font-size: 12.5px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .media-meta .size {
            font-size: 11px;
            color: var(--text-faint);
        }

        /* ---------- Tip strip ---------- */

        .tip-strip {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            margin-top: 4px;
        }

        .tip-strip .swatch {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: var(--amber-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .tip-strip .swatch svg {
            width: 15px;
            height: 15px;
            stroke: var(--amber);
        }

        .tip-strip p {
            margin: 0;
            font-size: 13px;
            color: var(--text-muted);
        }

        .tip-strip p code {
            background: var(--surface-2);
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 12px;
        }

        /* ---------- Footer ---------- */

        footer {
            border-top: 1px solid var(--border);
            background: var(--surface);
            padding: 16px 24px;
            text-align: center;
        }

        footer p {
            margin: 0;
            font-size: 12px;
            color: var(--text-faint);
        }

        /* ---------- Modals (native dialog) ---------- */

        dialog {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            color: var(--text);
            padding: 0;
        }

        dialog::backdrop {
            background: rgba(6, 7, 10, .65);
        }

        dialog#createPageModal {
            width: min(560px, 92vw);
            max-height: 88vh;
        }

        dialog#deletePageModal {
            width: min(460px, 92vw);
        }

        dialog#quickViewModal {
            width: min(960px, 94vw);
            height: min(720px, 88vh);
        }

        dialog#quickViewModal[open] {
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-soft);
            flex-shrink: 0;
        }

        .modal-header h3 {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modal-header .modal-header-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .modal-body {
            padding: 20px 22px;
            overflow-y: auto;
            max-height: calc(88vh - 130px);
        }

        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding: 16px 22px;
            border-top: 1px solid var(--border-soft);
        }

        .modal-footer .feedback-link {
            margin-right: auto;
            font-size: 12px;
            color: var(--text-faint);
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .field input, .field select, .field textarea {
            width: 100%;
            background: var(--surface-2);
            border: 1px solid var(--border);
            color: var(--text);
            font: inherit;
            font-size: 13px;
            padding: 8px 10px;
            border-radius: var(--radius-sm);
        }

        .field textarea {
            font-family: var(--font-mono);
            resize: vertical;
            min-height: 130px;
        }

        .field-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0 16px;
        }

        .field-divider hr {
            flex: 1;
            border: none;
            border-top: 1px solid var(--border-soft);
            margin: 0;
        }

        .field-divider span {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-faint);
        }

        .field-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
        }

        .form-error {
            background: var(--red-soft);
            color: #ffb3b5;
            border-radius: var(--radius-sm);
            padding: 10px 12px;
            font-size: 12.5px;
            margin-bottom: 16px;
        }

        .delete-page-details {
            margin: 0;
            color: var(--text-muted);
            font-size: 13px;
        }

        .delete-page-details strong {
            color: var(--text);
        }

        .delete-page-details .mono {
            display: inline-block;
            margin-top: 8px;
            color: #ffb3b5;
            word-break: break-all;
        }

        /* Quick view */

        .quick-view-body {
            padding: 0;
            flex: 1;
            min-height: 0;
            max-height: none;
            overflow: hidden;
        }

        .quick-view-body iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
            background: #fff;
        }

        /* ---------- Toast ---------- */

        .toast-wrap {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 50;
        }

        #asyncErrorToast {
            background: var(--surface);
            border: 1px solid var(--border);
            border-left: 3px solid var(--red);
            border-radius: var(--radius-sm);
            width: 320px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .45);
            transform: translateY(12px);
            opacity: 0;
            pointer-events: none;
            transition: transform .18s ease, opacity .18s ease;
        }

        #asyncErrorToast.show {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .toast-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-soft);
        }

        .toast-header strong {
            font-size: 12.5px;
            color: var(--red);
        }

        .toast-body {
            padding: 12px;
            font-size: 12.5px;
            color: var(--text-muted);
        }

        @media (max-width: 900px) {
            .chip-grid {
                grid-template-columns: repeat(3, max-content);
            }

            .info-chip-path {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 640px) {
            main {
                padding-left: 16px;
                padding-right: 16px;
            }

            .chip-grid {
                grid-template-columns: 1fr;
            }

            .info-chip:not(.info-chip-path) {
                white-space: normal;
            }

            .intro-header {
                flex-direction: column;
                align-items: stretch;
            }

            .intro-actions .btn {
                justify-content: center;
            }

            .route-actions-col {
                flex-wrap: wrap;
            }

            dialog#quickViewModal {
                width: 96vw;
                height: 90vh;
            }
        }
    </style>
</head>
<body>
<main>
    <div class="intro">
        <div class="intro-header">
            <div class="intro-title">
                <h1>Welcome to your site dashboard!</h1>
                @if(! $dashboard->isInteractive())
                    <span class="pill pill-amber" title="This dashboard is readonly. You can change this in the `hyde.php` config.">Readonly</span>
                @endif
            </div>
            <div class="intro-actions">
                @if($request->embedded)
                    <a href="/dashboard" class="btn btn-ghost btn-sm">Open full page dashboard</a>
                @else
                    <a href="/" class="btn btn-ghost btn-sm">Back to site</a>
                @endif
            </div>
        </div>
        <p>This page is served by the Realtime Compiler and won't be saved to your static site.</p>
    </div>

    {{-- Project information --}}
    <section class="card">
        <div class="card-header">
            <h2>Project information</h2>
            @if($dashboard->isInteractive())
                <form class="buttonActionForm" action="" method="POST">
                    <input type="hidden" name="_token" value="{{ $csrfToken }}">
                    <input type="hidden" name="action" value="openInExplorer">
                    <button type="submit" class="btn btn-sm" title="Open project in system file explorer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"></path></svg>
                        Open folder
                    </button>
                </form>
            @endif
        </div>

        <div class="card-body">
            <div class="chip-grid">
                @php $swatches = ['blue', 'purple', 'teal']; $i = 0; @endphp
                @foreach($dashboard->getProjectInformation() as $type => $info)
                    @php $swatch = $swatches[$i % count($swatches)]; $i++; $isProjectPath = $type === 'Project Path'; @endphp
                    <div @class(['info-chip', 'info-chip-path' => $isProjectPath])>
                            <span class="swatch" style="background: var(--{{ $swatch }}-soft)">
                                @if($isProjectPath)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--{{ $swatch }})" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V5a2 2 0 0 1 2-2h2M4 17v2a2 2 0 0 0 2 2h2M20 7V5a2 2 0 0 0-2-2h-2M20 17v2a2 2 0 0 1-2 2h-2"></path></svg>
                                @elseif($loop->first)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--{{ $swatch }})" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7l9 5 9-5-9-5Z"></path><path d="M3 12l9 5 9-5"></path></svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--{{ $swatch }})" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="3"></rect><path d="M9 9h6v6H9z"></path></svg>
                                @endif
                            </span>

                        <div>
                            <div class="label">{{ $type }}</div>
                            <div class="value">
                                @if($isProjectPath)
                                    <div class="value-row mono">
                                        <span>{{ $info }}</span>
                                        <button id="copyPathToClipboardButton" class="copy-btn" data-copy-value="{{ $info }}" title="Copy path to clipboard">
                                            <svg id="copyPathToClipboardButtonIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2"></rect><path d="M4 16V5a2 2 0 0 1 2-2h9"></path></svg>
                                            <svg id="copyPathToClipboardButtonIconSuccess" style="display:none" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
                                        </button>
                                    </div>
                                @else
                                    {{ $info }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Pages & routes --}}
    <section class="card">
        <div class="card-header">
            <h2>Site pages &amp; routes</h2>
            @if($dashboard->isInteractive())
                <noscript><style>#createPageModalButton { display: none; }</style></noscript>

                <button id="createPageModalButton" type="button" class="btn btn-primary btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                    Create page
                </button>

                <dialog id="createPageModal" aria-labelledby="createPageModalLabel">
                    <form id="createPageForm" action="" method="POST">
                        <input type="hidden" name="_token" value="{{ $csrfToken }}">
                        <input type="hidden" name="action" value="createPage">

                        <div class="modal-header">
                            <h3 id="createPageModalLabel">Create new page</h3>
                            <button type="button" class="btn btn-ghost btn-sm" data-dialog-close aria-label="Close">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div id="createPageFormError" class="form-error" style="display: none;">
                                <strong>Error:</strong>
                                <span id="createPageFormErrorContents"></span>
                            </div>

                            <div class="field">
                                <label for="pageTypeSelection">Page type</label>
                                <select id="pageTypeSelection" name="pageTypeSelection">
                                    <option selected disabled>Select page type</option>
                                    @foreach(['HtmlPage', 'BladePage', 'MarkdownPage', 'MarkdownPost', 'DocumentationPage'] as $page)
                                        <option value="{{ str($page)->kebab() }}">{{ $page }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="page-creation-group" id="baseInfo" style="display: none">
                                <div class="field-divider"><hr><span>Required details</span><hr></div>

                                <div class="field">
                                    <label for="titleInput" id="titleInputLabel">Page title</label>
                                    <input type="text" id="titleInput" name="titleInput" placeholder="Enter a title" required>
                                </div>

                                <div class="field">
                                    <label for="contentInput" id="contentInputLabel">Markdown text</label>
                                    <textarea id="contentInput" name="contentInput" rows="8" placeholder="Enter your Markdown text" required></textarea>
                                </div>
                            </div>

                            <div class="page-creation-group" id="createsPost" style="display: none">
                                <div class="field-divider"><hr><span>Extra details</span><hr></div>

                                <div class="field">
                                    <label for="postDescription">Post description</label>
                                    <input type="text" id="postDescription" name="postDescription" placeholder="Enter a post description (optional)">
                                </div>

                                <div class="field-row">
                                    <div class="field">
                                        <label for="postCategory">Post category</label>
                                        <input type="text" id="postCategory" name="postCategory" placeholder="Optional">
                                    </div>
                                    <div class="field">
                                        <label for="postAuthor">Post author</label>
                                        <input type="text" id="postAuthor" name="postAuthor" placeholder="Optional">
                                    </div>
                                    <div class="field">
                                        <label for="postDate">Post date</label>
                                        <input type="datetime-local" id="postDate" name="postDate" value="{{ date('Y-m-d H:i') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            @php
                                $feedbackQuery = http_build_query([
                                    'title' => 'Feedback on the dashboard create page modal',
                                    'body' => 'Write something nice!'
                                ]);
                            @endphp
                            <a class="feedback-link" href="https://github.com/hydephp/realtime-compiler/issues/new?{{ $feedbackQuery }}" title="This is a new feature, we'd love your feedback!" target="_blank" rel="noopener">Send feedback</a>
                            <button type="button" class="btn btn-sm" data-dialog-close>Close</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="createPageButton" title="Please select a page type first" disabled>Create page</button>
                        </div>
                    </form>
                </dialog>
            @endif
        </div>

        <div class="card-body">
            @if(empty($dashboard->getPageList()))
                <div class="empty-state">
                    <strong>No pages yet</strong>
                    There are no pages yet.
                    @if($dashboard->isInteractive())
                        Create one using the button above.
                    @else
                        Why not create some?
                    @endif
                </div>
            @else
                <div class="route-list">
                    @php
                        $typeColors = [
                            'BladePage'         => 'red',
                            'MarkdownPage'      => 'blue',
                            'MarkdownPost'      => 'teal',
                            'DocumentationPage' => 'amber',
                            'HtmlPage'          => 'orange',
                        ];

                        // Extra inner SVG paths layered onto the base file icon to hint at page type.
                        $typeIcons = [
                            'HtmlPage'          => '<path d="M10 13l-1.5 1.5L10 16"></path><path d="M14 13l1.5 1.5L14 16"></path>',
                            'BladePage'         => '<path d="M9.5 12.5c.8 0 .8.7.8 1.5s0 1.5-.8 1.5"></path><path d="M14.5 12.5c-.8 0-.8.7-.8 1.5s0 1.5.8 1.5"></path>',
                            'MarkdownPage'      => '<path d="M9.5 12v4M14.5 12v4M8.5 14h7"></path>',
                            'MarkdownPost'      => '<path d="M9.5 12v4M14.5 12v4M8.5 14h7"></path>',
                            'DocumentationPage' => '<path d="M8.5 12.5h7"></path><path d="M8.5 15.5h4.5"></path>',
                        ];

                        $groups = [];
                        foreach ($dashboard->getPageList() as $route) {
                            $isMemory = $route->getPage() instanceof \Hyde\Pages\InMemoryPage;

                            if ($isMemory) {
                                $dir = 'Dynamically generated';
                            } else {
                                $sourcePath = $route->getSourcePath();
                                $dir = str_contains($sourcePath, '/') ? explode('/', $sourcePath, 2)[0] : $sourcePath;
                            }

                            $groups[$dir][] = $route;
                        }

                        // Keep source directories in a stable order, with dynamically generated routes listed last.
                        uksort($groups, function ($a, $b) {
                            if ($a === 'Dynamically generated') return 1;
                            if ($b === 'Dynamically generated') return -1;
                            return strcmp($a, $b);
                        });
                    @endphp

                    @foreach($groups as $dir => $routes)
                        <div class="route-group">
                            <div class="route-group-label">{{ $dir }} <span class="count">{{ count($routes) }}</span></div>

                            @foreach($routes as $route)
                                @php
                                    $typeKey = class_basename($route->getPageClass());
                                    $color = $typeColors[$typeKey] ?? 'purple';
                                    $isMemory = $route->getPage() instanceof \Hyde\Pages\InMemoryPage;
                                    $editFormId = 'editPageForm-'.md5($route->getRouteKey());
                                @endphp

                                <div id="pageRow-{{ $route->getRouteKey() }}"
                                        @class(['route-row', $dashboard->getFlash('justCreatedPage') === $route->getRouteKey() ? 'justCreatedPage just-created' : ''])>

                                    <div class="route-file">
                                        <div class="route-path-line">
                                            @if($isMemory)
                                                <svg class="icon-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" title="This page is generated dynamically and does not have a source file."><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"></path></svg>
                                                <span class="route-path-out mono" style="font-style: italic;">generated</span>
                                            @else
                                                <svg style="color: color-mix(in srgb, var(--{{ $color }}) 55%, var(--text-muted))" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" title="{{ $typeKey }}"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path>{!! $typeIcons[$typeKey] ?? '' !!}</svg>
                                                <span
                                                    class="route-path-src mono{{ $dashboard->isInteractive() ? ' editable edit-page-source' : '' }}"
                                                    @if($dashboard->isInteractive())
                                                        data-route-key="{{ $route->getRouteKey() }}"
                                                        data-edit-form-id="{{ $editFormId }}"
                                                        title="{{ $route->getSourcePath() }} &middot; open in editor"
                                                    @else
                                                        title="{{ $route->getSourcePath() }}"
                                                    @endif
                                                >{{ $route->getSourcePath() }}</span>
                                            @endif

                                            <svg class="icon-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>

                                            @php
                                                $outputPath = $route->getOutputPath();
                                                $routeKey = $route->getRouteKey();
                                                $suffix = str_starts_with($outputPath, $routeKey) ? substr($outputPath, strlen($routeKey)) : '';
                                            @endphp

                                            <span class="route-output-path" title="{{ $outputPath }}"><a href="{{ $dashboard->getRoutePreviewLink($route) }}" class="route-key-link mono" title="Open this page &middot; route key: {{ $routeKey }}">{{ $routeKey }}</a><span class="route-path-out mono">{{ $suffix }}</span></span>
                                        </div>
                                    </div>

                                    <div class="route-actions-col">
                                        @if($dashboard->isInteractive())
                                            <form id="{{ $editFormId }}" class="buttonActionForm route-action" action="" method="POST" data-tooltip="{{ $isMemory ? 'Cannot edit in-memory pages' : 'Open in editor' }}">
                                                <input type="hidden" name="_token" value="{{ $csrfToken }}">
                                                <input type="hidden" name="action" value="openPageInEditor">
                                                <input type="hidden" name="routeKey" value="{{ $route->getRouteKey() }}">

                                                @if($isMemory)
                                                    <button type="submit" class="btn btn-ghost btn-sm" style="cursor: not-allowed" disabled aria-label="Cannot edit in-memory page {{ $route->getRouteKey() }}">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path></svg>
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-ghost btn-sm edit-page-btn" data-route-key="{{ $route->getRouteKey() }}" aria-label="Open {{ $route->getRouteKey() }} in editor">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path></svg>
                                                    </button>
                                                @endif
                                            </form>

                                            @if($isMemory)
                                                <span class="route-action" data-tooltip="Cannot delete in-memory pages">
                                                    <button type="button" class="btn btn-ghost btn-sm" style="cursor: not-allowed" disabled aria-label="Cannot delete in-memory page {{ $route->getRouteKey() }}">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"></path></svg>
                                                    </button>
                                                </span>
                                            @else
                                                <span class="route-action" data-tooltip="Delete source file">
                                                    <button type="button"
                                                            class="btn btn-ghost btn-sm btn-delete delete-page-btn"
                                                            data-route-key="{{ $route->getRouteKey() }}"
                                                            data-source-path="{{ $route->getSourcePath() }}"
                                                            aria-label="Delete source file for {{ $route->getRouteKey() }}">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"></path></svg>
                                                    </button>
                                                </span>
                                            @endif
                                        @endif

                                        <span class="route-action" data-tooltip="Quick view">
                                            <button type="button" class="btn btn-ghost btn-sm quick-view-btn" data-preview-url="{{ $dashboard->getRoutePreviewLink($route) }}" data-preview-label="{{ $route->getRouteKey() }}" aria-label="Quick view {{ $route->getRouteKey() }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Media library --}}
    <section class="card">
        <div class="card-header">
            <h2>Media library</h2>
        </div>

        <div class="card-body">
            @if(empty(\Hyde\Support\Filesystem\MediaFile::all()))
                <div class="empty-state">
                    <strong>No media files yet</strong>
                    Why not add some?
                </div>
            @else
                <div class="media-grid">
                    @foreach(\Hyde\Support\Filesystem\MediaFile::all() as $mediaFile)
                        <div class="media-card">
                            <div class="media-preview">
                                @if(in_array($mediaFile->getExtension(), ['svg', 'png', 'jpg', 'jpeg', 'gif', 'ico']))
                                    <img loading="lazy" src="{{ $dashboard->getMediaPreviewLink($mediaFile) }}" alt="{{ $mediaFile->getName() }}">
                                @else
                                    @php($mediaPlaceholder = $dashboard::getMediaPlaceholder($mediaFile->getExtension()))
                                    <div
                                            class="file-preview"
                                            role="img"
                                            aria-label="{{ $mediaPlaceholder['label'] }} file"
                                            style="--media-brand: {{ $mediaPlaceholder['color'] }}; --media-brand-rgb: {{ $mediaPlaceholder['rgb'] }}; {{ $mediaPlaceholder['label'] === 'JavaScript' ? '--media-mark-color: #1c1e24;' : '' }}"
                                    >
                                        <div class="file-preview-mark">{{ $mediaPlaceholder['mark'] }}</div>
                                        <div class="file-preview-label">{{ $mediaPlaceholder['label'] }}</div>
                                    </div>
                                @endif

                                <div class="media-overlay">
                                    <a href="{{ $dashboard->getMediaPreviewLink($mediaFile) }}" class="btn btn-sm" title="Open this image in the browser" target="_blank">Fullscreen</a>
                                    @if($dashboard->isInteractive())
                                        <form class="buttonActionForm" action="" method="POST" style="margin:0">
                                            <input type="hidden" name="_token" value="{{ $csrfToken }}">
                                            <input type="hidden" name="action" value="openMediaFileInEditor">
                                            <input type="hidden" name="identifier" value="{{ $mediaFile->getIdentifier() }}">
                                            <button type="submit" class="btn btn-sm" title="Open this image in the system editor">Edit</button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <div class="media-meta">
                                <span class="file-chip" data-type="{{ $mediaFile->getExtension() }}">{{ $mediaFile->getExtension() }}</span>
                                <div class="info">
                                    <div class="name" title="{{ $mediaFile->getPath() }}">{{ $mediaFile->getName() }}</div>
                                    <div class="size">{{ $dashboard::bytesToHuman($mediaFile->getLength()) }}</div>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if($dashboard->showTips())
        <div class="tip-strip">
                <span class="swatch">
                    <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6M10 22h4M12 2a6 6 0 0 0-4 10.5c.6.55 1 1.4 1 2.3V16h6v-1.2c0-.9.4-1.75 1-2.3A6 6 0 0 0 12 2Z"></path></svg>
                </span>
            <p><strong style="color: var(--text-muted)">Tip:</strong> {{ $dashboard->getTip() }}</p>
        </div>
    @endif
</main>

<footer>
    <p>HydePHP Realtime Compiler <span class="mono">{{ $dashboard->getVersion() }}</span></p>
</footer>

{{-- Quick view is read-only, so it's available even when the dashboard is not interactive --}}
<dialog id="quickViewModal" aria-labelledby="quickViewModalLabel">
    <div class="modal-header">
        <h3 id="quickViewModalLabel">Preview</h3>
        <div class="modal-header-actions">
            <a id="quickViewOpenLink" href="#" target="_blank" class="btn btn-ghost btn-sm" title="Open in a new tab">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M10 14 20 4M18 13v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5"></path></svg>
            </a>
            <button type="button" class="btn btn-ghost btn-sm" data-dialog-close aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>
    <div class="modal-body quick-view-body">
        <iframe id="quickViewFrame" src="about:blank" title="Page preview"></iframe>
    </div>
</dialog>

@if($dashboard->isInteractive())
    <dialog id="deletePageModal" aria-labelledby="deletePageModalLabel">
        <form id="deletePageForm" action="" method="POST">
            <input type="hidden" name="_token" value="{{ $csrfToken }}">
            <input type="hidden" name="action" value="deletePage">
            <input type="hidden" name="routeKey" id="deletePageRouteKeyInput" value="">

            <div class="modal-header">
                <h3 id="deletePageModalLabel">Delete page</h3>
                <button type="button" class="btn btn-ghost btn-sm" data-dialog-close aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="modal-body">
                <div id="deletePageFormError" class="form-error" style="display: none;">
                    <strong>Error:</strong>
                    <span id="deletePageFormErrorContents"></span>
                </div>

                <p class="delete-page-details">
                    Delete <strong id="deletePageRouteKey"></strong>? This permanently removes the source file:
                    <span id="deletePageSourcePath" class="mono"></span>
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm" data-dialog-close>Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm" id="deletePageButton">Delete page</button>
            </div>
        </form>
    </dialog>

    <div class="toast-wrap">
        <div id="asyncErrorToast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong id="asyncErrorToastHeader">Error</strong>
                <button type="button" class="btn btn-ghost btn-sm" data-toast-close aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                </button>
            </div>
            <div id="asyncErrorToastBody" class="toast-body"></div>
        </div>
    </div>
@endif

<script>
    (() => {
        'use strict';

        function toTitleCase(value) {
            return value.replace(/-/g, ' ').replace(/^\w/, c => c.toUpperCase());
        }

        /* ---------- copy to clipboard ---------- */

        document.getElementById('copyPathToClipboardButton')?.addEventListener('click', async function () {
            const icon = document.getElementById('copyPathToClipboardButtonIcon');
            const iconSuccess = document.getElementById('copyPathToClipboardButtonIconSuccess');

            try {
                await navigator.clipboard.writeText(this.dataset.copyValue);
                icon.style.display = 'none';
                iconSuccess.style.display = 'inline';

                setTimeout(() => {
                    icon.style.display = 'inline';
                    iconSuccess.style.display = 'none';
                }, 3000);
            } catch (error) {
                window.prompt('Copy to clipboard: Ctrl+C', this.dataset.copyValue);
            }
        });

        /* ---------- dialogs ---------- */

        document.querySelectorAll('dialog').forEach(dialog => {
            dialog.addEventListener('click', function (event) {
                if (event.target === this) this.close();
            });
        });

        document.querySelectorAll('[data-dialog-close]').forEach(button => {
            button.addEventListener('click', function () {
                this.closest('dialog')?.close();
            });
        });

        /* ---------- quick view ---------- */

        document.querySelectorAll('.quick-view-btn').forEach(button => {
            button.addEventListener('click', function () {
                const modal = document.getElementById('quickViewModal');
                const label = document.getElementById('quickViewModalLabel');
                const openLink = document.getElementById('quickViewOpenLink');
                const frame = document.getElementById('quickViewFrame');

                if (!modal || !label || !openLink || !frame) {
                    console.error('Quick view: one or more modal elements are missing from the DOM.');
                    return;
                }

                label.innerText = this.dataset.previewLabel || 'Preview';
                openLink.href = this.dataset.previewUrl || '#';
                frame.src = this.dataset.previewUrl || 'about:blank';
                modal.showModal();
            });
        });

        document.getElementById('quickViewModal')?.addEventListener('close', () => {
            document.getElementById('quickViewFrame').src = 'about:blank';
        });

        @if($dashboard->isInteractive())
        /* ---------- error toast ---------- */

        const toast = document.getElementById('asyncErrorToast');

        function showErrorToast(title, message) {
            document.getElementById('asyncErrorToastHeader').innerText = title;
            document.getElementById('asyncErrorToastBody').innerText = message;
            toast.classList.add('show');

            clearTimeout(toast._hideTimer);
            toast._hideTimer = setTimeout(() => toast.classList.remove('show'), 6000);
        }

        document.querySelector('[data-toast-close]')?.addEventListener('click', () => {
            toast.classList.remove('show');
        });

        /* ---------- create page modal open ---------- */

        document.getElementById('createPageModalButton')?.addEventListener('click', () => {
            const modal = document.getElementById('createPageModal');
            modal.showModal();
            document.getElementById('pageTypeSelection').focus();
        });

        /* ---------- async forms ---------- */

        function registerAsyncForm(form, okHandler = null, errorHandler = null, beforeCallHandler = null, afterCallHandler = null) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                beforeCallHandler?.();

                fetch('', {
                    method: 'POST',
                    body: new FormData(event.target),
                    headers: new Headers({ 'Accept': 'application/json' }),
                }).then(async response => {
                    if (response.ok) {
                        okHandler?.(response);
                    } else if (errorHandler) {
                        errorHandler(response);
                    } else {
                        const data = await response.json();
                        showErrorToast(`Error: ${response.status} ${response.statusText}`, data.error);
                    }
                }).catch(error => {
                    console.error('Network error:', error);
                }).finally(() => {
                    afterCallHandler?.();
                });
            });
        }

        document.querySelectorAll('.buttonActionForm').forEach(form => registerAsyncForm(form));

        document.querySelectorAll('.edit-page-source').forEach(el => {
            el.addEventListener('click', function () {
                document.getElementById(this.dataset.editFormId)?.requestSubmit();
            });
        });

        /* ---------- delete page form ---------- */

        const deletePageForm = document.getElementById('deletePageForm');

        if (deletePageForm) {
            const deletePageModal = document.getElementById('deletePageModal');
            const deletePageRouteKeyInput = document.getElementById('deletePageRouteKeyInput');
            const deletePageRouteKey = document.getElementById('deletePageRouteKey');
            const deletePageSourcePath = document.getElementById('deletePageSourcePath');
            const deletePageButton = document.getElementById('deletePageButton');
            const deletePageFormError = document.getElementById('deletePageFormError');
            const deletePageFormErrorContents = document.getElementById('deletePageFormErrorContents');

            document.querySelectorAll('.delete-page-btn').forEach(button => {
                button.addEventListener('click', function () {
                    deletePageRouteKeyInput.value = this.dataset.routeKey || '';
                    deletePageRouteKey.innerText = this.dataset.routeKey || 'this page';
                    deletePageSourcePath.innerText = this.dataset.sourcePath || '';
                    deletePageFormError.style.display = 'none';
                    deletePageFormErrorContents.innerText = '';
                    deletePageButton.disabled = false;
                    deletePageModal.showModal();
                });
            });

            registerAsyncForm(
                deletePageForm,
                () => {
                    deletePageModal.close();
                    location.reload();
                },
                async response => {
                    const data = await response.json();
                    deletePageFormError.style.display = 'block';
                    deletePageFormErrorContents.innerText = data.error;
                },
                () => {
                    deletePageButton.disabled = true;
                    deletePageFormError.style.display = 'none';
                    deletePageFormErrorContents.innerText = '';
                },
                () => {
                    deletePageButton.disabled = false;
                }
            );
        }

        /* ---------- create page form ---------- */

        const createPageForm = document.getElementById('createPageForm');

        if (createPageForm) {
            const createPageButton = document.getElementById('createPageButton');
            const createPageFormError = document.getElementById('createPageFormError');
            const createPageFormErrorContents = document.getElementById('createPageFormErrorContents');

            registerAsyncForm(
                createPageForm,
                () => {
                    document.getElementById('createPageModal').close();
                    createPageForm.reset();
                    location.reload();
                },
                async response => {
                    const data = await response.json();
                    createPageFormError.style.display = 'block';
                    createPageFormErrorContents.innerText = data.error;
                },
                () => {
                    createPageButton.disabled = true;
                    createPageFormError.style.display = 'none';
                    createPageFormErrorContents.innerText = '';
                },
                () => {
                    createPageButton.disabled = false;
                }
            );

            /* ---------- page type field switching ---------- */

            const createPageModalLabel = document.getElementById('createPageModalLabel');
            const titleInputLabel = document.getElementById('titleInputLabel');
            const contentInputLabel = document.getElementById('contentInputLabel');
            const contentInput = document.getElementById('contentInput');

            const pageTypeSelection = document.getElementById('pageTypeSelection');
            const baseInfo = document.getElementById('baseInfo');
            const createsPost = document.getElementById('createsPost');

            const defaults = {
                modalLabel: createPageModalLabel.innerText,
                titleLabel: titleInputLabel.innerText,
                contentLabel: contentInputLabel.innerText,
                contentPlaceholder: contentInput.placeholder,
            };

            pageTypeSelection.addEventListener('change', function (event) {
                createPageModalLabel.innerText = defaults.modalLabel;
                titleInputLabel.innerText = defaults.titleLabel;
                contentInputLabel.innerText = defaults.contentLabel;
                contentInput.placeholder = defaults.contentPlaceholder;

                createPageButton.disabled = false;
                createPageButton.title = '';

                baseInfo.style.display = 'none';
                createsPost.style.display = 'none';

                const selection = event.target.value;

                if (selection === 'markdown-post') {
                    baseInfo.style.display = 'block';
                    createsPost.style.display = 'block';
                    createPageModalLabel.innerText = 'Creating a new Markdown post';
                    titleInputLabel.innerText = 'Post title';
                } else {
                    baseInfo.style.display = 'block';
                    createPageModalLabel.innerText = 'Creating a new ' + toTitleCase(selection);
                }

                if (selection === 'blade-page') {
                    contentInputLabel.innerText = 'Blade content';
                    contentInput.placeholder = 'Enter your Blade content';
                } else if (selection === 'html-page') {
                    createPageModalLabel.innerText = 'Creating a new HTML page';
                    contentInputLabel.innerText = 'HTML content';
                    contentInput.placeholder = 'Enter your HTML content';
                }
            });
        }
        @endif
    })();
</script>
</body>
</html>
