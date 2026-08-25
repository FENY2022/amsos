<?php
// Database configuration
require_once 'connect.php';
require_once 'session_checker.php';
require_once 'calendar_event_helpers.php';

calendarEnsureSrfZoomSchema($conn);

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    @import url('https://fonts.googleapis.com/icon?family=Material+Icons+Outlined');

    :root {
        --primary: #4f46e5;
        --primary-light: #818cf8;
        --primary-dark: #3730a3;
        --primary-bg: #eef2ff;
        --success: #10b981;
        --success-bg: #ecfdf5;
        --warning: #f59e0b;
        --warning-bg: #fffbeb;
        --danger: #ef4444;
        --danger-bg: #fef2f2;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --font: 'Inter', system-ui, -apple-system, sans-serif;
        --radius: 12px;
        --radius-lg: 16px;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    * { box-sizing: border-box; }

    .sr-container {
        font-family: var(--font);
        max-width: 1440px;
        margin: 0 auto;
        padding: 1.5rem;
        color: var(--gray-800);
    }

    /* ── Header ── */
    .sr-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .sr-header-left h1 {
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: -0.025em;
        color: var(--gray-800);
        margin: 0;
    }
    .sr-header-left p {
        color: var(--gray-500);
        font-size: 0.9rem;
        margin: 0.25rem 0 0 0;
    }
    .sr-header-right {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .sr-header-right .material-icons-outlined {
        font-size: 1.5rem;
        color: var(--gray-400);
    }
    .sr-refresh-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--gray-200);
        background: white;
        color: var(--gray-600);
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .sr-refresh-btn:hover {
        background: var(--gray-50);
        border-color: var(--gray-300);
    }

    /* ── Stats Row ── */
    .sr-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .sr-stat-card {
        background: white;
        border-radius: var(--radius);
        padding: 1.25rem 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-100);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.25s;
    }
    .sr-stat-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    .sr-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .sr-stat-icon .material-icons-outlined { font-size: 1.35rem; }
    .sr-stat-icon.primary { background: var(--primary-bg); color: var(--primary); }
    .sr-stat-icon.success { background: var(--success-bg); color: var(--success); }
    .sr-stat-icon.warning { background: var(--warning-bg); color: var(--warning); }
    .sr-stat-icon.danger { background: var(--danger-bg); color: var(--danger); }
    .sr-stat-info h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
        color: var(--gray-800);
    }
    .sr-stat-info p {
        margin: 0;
        font-size: 0.8rem;
        color: var(--gray-500);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* ── Search / Filter Bar ── */
    .sr-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 2rem;
        background: white;
        border-radius: var(--radius);
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-100);
        box-shadow: var(--shadow-sm);
    }
    .sr-search-wrap {
        flex: 1;
        min-width: 200px;
        position: relative;
    }
    .sr-search-wrap .material-icons-outlined {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: 1.25rem;
        pointer-events: none;
    }
    .sr-search-wrap input {
        width: 100%;
        padding: 0.6rem 0.75rem 0.6rem 2.5rem;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        font-family: var(--font);
        font-size: 0.9rem;
        color: var(--gray-700);
        background: var(--gray-50);
        transition: all 0.2s;
        outline: none;
    }
    .sr-search-wrap input:focus {
        border-color: var(--primary-light);
        background: white;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .sr-search-wrap input::placeholder { color: var(--gray-400); }

    .sr-filter-tabs {
        display: flex;
        gap: 0.25rem;
        background: var(--gray-100);
        border-radius: 8px;
        padding: 0.2rem;
        flex-wrap: wrap;
    }
    .sr-filter-tab {
        padding: 0.4rem 0.85rem;
        border-radius: 6px;
        border: none;
        background: transparent;
        color: var(--gray-500);
        font-family: var(--font);
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .sr-filter-tab:hover { color: var(--gray-700); }
    .sr-filter-tab.active {
        background: white;
        color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .sr-count-badge {
        font-size: 0.7rem;
        background: var(--gray-200);
        color: var(--gray-600);
        border-radius: 999px;
        padding: 0.1rem 0.45rem;
        margin-left: 0.25rem;
        font-weight: 600;
    }
    .sr-filter-tab.active .sr-count-badge {
        background: var(--primary-bg);
        color: var(--primary);
    }

    /* ── Cards Grid ── */
    .sr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.25rem;
    }

    .sr-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-100);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 0;
        transform: translateY(16px);
        animation: srFadeIn 0.4s ease forwards;
    }
    .sr-card:hover {
        box-shadow: var(--shadow-xl);
        transform: translateY(-4px);
    }
    .sr-card.hidden { display: none; }

    @keyframes srFadeIn {
        to { opacity: 1; transform: translateY(0); }
    }

    .sr-card-accent {
        height: 4px;
        width: 100%;
    }
    .sr-card-accent.primary { background: var(--primary); }
    .sr-card-accent.success { background: var(--success); }
    .sr-card-accent.warning { background: var(--warning); }
    .sr-card-accent.danger { background: var(--danger); }
    .sr-card-accent.default { background: var(--gray-300); }

    .sr-card-body { padding: 1.25rem 1.5rem 1rem; }

    .sr-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
    }
    .sr-card-ticket {
        font-weight: 700;
        font-size: 0.8rem;
        color: var(--primary);
        letter-spacing: 0.025em;
        background: var(--primary-bg);
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
    }
    .sr-status {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.65rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        white-space: nowrap;
    }
    .sr-status .material-icons-outlined { font-size: 0.85rem; }
    .sr-status.primary { background: var(--primary-bg); color: var(--primary); }
    .sr-status.success { background: var(--success-bg); color: var(--success); }
    .sr-status.warning { background: var(--warning-bg); color: var(--warning); }
    .sr-status.danger { background: var(--danger-bg); color: var(--danger); }
    .sr-status.default { background: var(--gray-100); color: var(--gray-500); }

    .sr-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-800);
        margin: 0 0 0.75rem 0;
        line-height: 1.4;
    }

    .sr-card-details {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    .sr-detail-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.82rem;
        color: var(--gray-600);
    }
    .sr-detail-row .material-icons-outlined {
        font-size: 1.1rem;
        color: var(--gray-400);
        flex-shrink: 0;
    }
    .sr-detail-row span { line-height: 1.3; }

    .sr-card-desc {
        font-size: 0.82rem;
        color: var(--gray-500);
        line-height: 1.5;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .sr-card-footer {
        padding: 0.75rem 1.5rem 1.25rem;
        border-top: 1px solid var(--gray-100);
        display: flex;
        justify-content: flex-end;
    }

    /* ── Dropdown / Action Button ── */
    .sr-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--gray-200);
        background: white;
        color: var(--gray-600);
        font-family: var(--font);
        font-size: 0.82rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .sr-action-btn:hover {
        background: var(--gray-50);
        border-color: var(--gray-300);
    }
    .sr-action-btn.primary {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }
    .sr-action-btn.primary:hover {
        background: var(--primary-dark);
        border-color: var(--primary-dark);
    }
    .sr-action-btn.warning {
        background: var(--warning);
        border-color: var(--warning);
        color: white;
    }
    .sr-action-btn.warning:hover { background: #d97706; border-color: #d97706; }

    .sr-dropdown-menu {
        border: 1px solid var(--gray-100) !important;
        border-radius: var(--radius) !important;
        box-shadow: var(--shadow-lg) !important;
        padding: 0.35rem !important;
        min-width: 180px !important;
        border: none;
    }
    .sr-dropdown-menu .dropdown-item {
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-size: 0.82rem;
        font-family: var(--font);
        color: var(--gray-600);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.15s;
    }
    .sr-dropdown-menu .dropdown-item:hover {
        background: var(--gray-50);
        color: var(--gray-800);
    }
    .sr-dropdown-menu .dropdown-item.text-danger:hover {
        background: var(--danger-bg);
        color: var(--danger) !important;
    }
    .sr-dropdown-menu .dropdown-divider {
        border-color: var(--gray-100);
        margin: 0.25rem 0;
    }
    .sr-dropdown-menu .dropdown-item .material-icons-outlined {
        font-size: 1.1rem;
        color: var(--gray-400);
    }
    .sr-dropdown-menu .dropdown-item.text-danger .material-icons-outlined {
        color: var(--danger);
    }

    /* ── Empty State ── */
    .sr-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
    }
    .sr-empty-icon {
        font-size: 4rem;
        color: var(--gray-300);
        margin-bottom: 1rem;
    }
    .sr-empty h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-600);
        margin: 0 0 0.5rem;
    }
    .sr-empty p {
        color: var(--gray-400);
        font-size: 0.9rem;
        margin: 0;
    }

    /* ── Loading Spinner ── */
    .sr-loading {
        grid-column: 1 / -1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding: 4rem 2rem;
    }
    .sr-spinner {
        width: 36px;
        height: 36px;
        border: 3px solid var(--gray-200);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: srSpin 0.7s linear infinite;
    }
    @keyframes srSpin { to { transform: rotate(360deg); } }
    .sr-loading p { color: var(--gray-400); font-size: 0.9rem; margin: 0; }

    /* ── Modal Overrides ── */
    .modal-content {
        border: none;
        border-radius: var(--radius-lg) !important;
        box-shadow: var(--shadow-xl) !important;
    }
    .modal-header {
        border-bottom: 1px solid var(--gray-100);
        padding: 1.25rem 1.5rem;
    }
    .modal-header .modal-title {
        font-weight: 600;
        font-size: 1.05rem;
        color: var(--gray-800);
    }
    .modal-header .btn-close {
        background: var(--gray-100);
        border-radius: 50%;
        padding: 0.5rem;
        opacity: 1;
        transition: all 0.2s;
    }
    .modal-header .btn-close:hover {
        background: var(--gray-200);
        transform: rotate(90deg);
    }
    .modal-body { padding: 1.5rem; }
    .modal-footer {
        border-top: 1px solid var(--gray-100);
        padding: 1rem 1.5rem;
    }

    /* ── Responsive ── */
    @media (max-width: 991px) {
        .modal-dialog { margin: 0.5rem; }
        .modal-dialog.modal-xl { max-width: calc(100% - 1rem); }
        .modal-dialog.modal-xl .modal-body iframe { height: 60vh; }
        .modal-header { padding: 1rem 1.25rem; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { padding: 0.75rem 1.25rem; flex-wrap: wrap; gap: 0.5rem; }
        .modal-footer .btn { font-size: 0.82rem; padding: 0.4rem 0.75rem; }
        .modal-footer .d-inline-block { flex: 1 1 auto; }
        .modal-footer .d-inline-block .btn { width: 100%; }
        .sr-dropdown-menu { min-width: 160px !important; }
    }

    @media (max-width: 768px) {
        .sr-container { padding: 0.75rem; }
        .sr-header { flex-direction: column; align-items: stretch; }
        .sr-header-left h1 { font-size: 1.35rem; }
        .sr-header-left p { font-size: 0.8rem; }
        .sr-stats { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
        .sr-stat-card { padding: 1rem; gap: 0.75rem; }
        .sr-stat-icon { width: 38px; height: 38px; }
        .sr-stat-info h3 { font-size: 1.25rem; }
        .sr-stat-info p { font-size: 0.7rem; }
        .sr-grid { grid-template-columns: 1fr; gap: 1rem; }
        .sr-card-body { padding: 1rem 1.25rem 0.75rem; }
        .sr-card-footer { padding: 0.6rem 1.25rem 1rem; }
        .sr-toolbar { flex-direction: column; align-items: stretch; padding: 0.6rem 0.75rem; }
        .sr-filter-tabs { overflow-x: auto; flex-wrap: nowrap; -webkit-overflow-scrolling: touch; }
        .sr-filter-tab { font-size: 0.75rem; padding: 0.35rem 0.65rem; flex-shrink: 0; }
        .sr-search-wrap input { font-size: 0.85rem; }
        .sr-refresh-btn { font-size: 0.8rem; padding: 0.4rem 0.75rem; }
        .sr-toast-container { top: 0.75rem; right: 0.75rem; left: 0.75rem; }
        .sr-toast { min-width: 0; max-width: 100%; padding: 0.75rem 1rem; font-size: 0.85rem; }
        .modal-dialog { margin: 0.5rem; }
        .modal-dialog.modal-xl { max-width: calc(100% - 1rem); }
        .modal-dialog.modal-xl .modal-body iframe { height: 50vh; }
        .modal-header { padding: 0.75rem 1rem; }
        .modal-header .modal-title { font-size: 0.95rem; }
        .modal-body { padding: 1rem; }
        .modal-footer { padding: 0.75rem 1rem; flex-wrap: wrap; gap: 0.5rem; }
        .modal-footer .btn { font-size: 0.8rem; padding: 0.35rem 0.6rem; }
        .modal-footer .d-inline-block { flex: 1 1 auto; }
        .modal-footer .d-inline-block .btn { width: 100%; }
        .sr-dropdown-menu { min-width: 150px !important; }
        .sr-empty { padding: 2rem 1rem; }
        .sr-empty-icon { font-size: 3rem; }
        .sr-empty h3 { font-size: 1.1rem; }
        .sr-loading { padding: 2rem 1rem; }
        .sr-card-details .sr-detail-row { font-size: 0.78rem; }
        .sr-card-title { font-size: 0.92rem; }
        .sr-card-desc { font-size: 0.78rem; }
        .modal-body .row.g-3 > [class*="col-"] { flex: 0 0 100%; max-width: 100%; }
    }

    @media (max-width: 480px) {
        .sr-container { padding: 0.5rem; }
        .sr-stats { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
        .sr-stat-card { padding: 0.75rem; }
        .sr-stat-icon { width: 32px; height: 32px; }
        .sr-stat-icon .material-icons-outlined { font-size: 1.1rem; }
        .sr-stat-info h3 { font-size: 1.1rem; }
        .sr-card { border-radius: var(--radius); }
        .sr-card-body { padding: 0.75rem 1rem 0.5rem; }
        .sr-card-footer { padding: 0.5rem 1rem 0.75rem; }
        .sr-card-top { flex-direction: column; gap: 0.4rem; }
        .sr-action-btn { font-size: 0.78rem; padding: 0.4rem 0.75rem; }
        .sr-dropdown-menu { min-width: 140px !important; }
        .sr-dropdown-menu .dropdown-item { font-size: 0.78rem; padding: 0.4rem 0.6rem; }
        .sr-header-left h1 { font-size: 1.2rem; }
        .sr-toast { padding: 0.65rem 0.85rem; font-size: 0.82rem; }
        .modal-header .modal-title { font-size: 0.9rem; }
        .modal-footer { flex-direction: column; }
        .modal-footer .btn { width: 100%; margin: 0 !important; }
    }

    /* ── Fix card z-index stacking ── */
    .sr-card.sr-dropdown-active { z-index: 10; position: relative; }

    /* ── Button Loading ── */
    .sr-btn-loading {
        position: relative;
        pointer-events: none;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .sr-btn-loading .sr-btn-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: currentColor;
        border-radius: 50%;
        animation: srSpin 0.6s linear infinite;
    }
    .sr-btn-loading .sr-btn-text {
        opacity: 0.9;
    }

    /* ── Toast Notifications ── */
    .sr-toast-container {
        position: fixed;
        top: 1.5rem;
        right: 1.5rem;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        pointer-events: none;
    }
    .sr-toast {
        pointer-events: auto;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-radius: var(--radius);
        background: white;
        box-shadow: var(--shadow-lg);
        border-left: 4px solid var(--gray-400);
        min-width: 320px;
        max-width: 420px;
        transform: translateX(120%);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: var(--font);
    }
    .sr-toast.show {
        transform: translateX(0);
        opacity: 1;
    }
    .sr-toast-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
    }
    .sr-toast-icon .material-icons-outlined { font-size: 1.15rem; }
    .sr-toast-icon.success { background: var(--success-bg); color: var(--success); }
    .sr-toast-icon.error { background: var(--danger-bg); color: var(--danger); }
    .sr-toast-icon.warning { background: var(--warning-bg); color: var(--warning); }
    .sr-toast-icon.info { background: var(--primary-bg); color: var(--primary); }
    .sr-toast-body { flex: 1; }
    .sr-toast-body p {
        margin: 0;
        font-size: 0.88rem;
        font-weight: 500;
        color: var(--gray-700);
        line-height: 1.4;
    }
    .sr-toast-close {
        background: none;
        border: none;
        color: var(--gray-400);
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .sr-toast-close:hover { color: var(--gray-600); background: var(--gray-100); }
    .sr-toast-close .material-icons-outlined { font-size: 1.1rem; }
    .sr-toast.success { border-left-color: var(--success); }
    .sr-toast.error { border-left-color: var(--danger); }
    .sr-toast.warning { border-left-color: var(--warning); }
    .sr-toast.info { border-left-color: var(--primary); }
</style>

<div class="sr-container">
    <!-- Toast Container -->
    <div class="sr-toast-container" id="srToastContainer"></div>

    <!-- Header -->
    <div class="sr-header">
        <div class="sr-header-left">
            <h1>Service Requests</h1>
            <p>Manage and track all ICT service requests</p>
        </div>
        <div class="sr-header-right">
            <button class="sr-refresh-btn" onclick="location.reload()">
                <span class="material-icons-outlined">refresh</span>
                Refresh
            </button>
        </div>
    </div>

    <?php
    $idSRF = $_SESSION['idSRF'];
    $idSRF101 = 101;
    $sql = "SELECT * FROM srf WHERE tracking = ? OR tracking = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idSRF, $idSRF101);
    $stmt->execute();
    $result = $stmt->get_result();

    $currentTime = time();
    $formattedTime = date('H:i', $currentTime);
    $legal_disclaimer = "By clicking &#39;Affix Signature,&#39; you hereby affirm your explicit intention to authenticate and approve this electronic document and the transaction it embodies. This action serves as your legal consent and is equivalent to a physical handwritten signature.";

    // Collect stats
    $totalCount = 0;
    $pendingCount = 0;
    $progressCount = 0;
    $disapprovedCount = 0;
    $allRows = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $allRows[] = $row;
            $totalCount++;
            if ($row['level'] == "101") $pendingCount++;
            elseif ($row['status'] == "Disapproved") $disapprovedCount++;
            else $progressCount++;
        }
    }
    $stmt->close();
    ?>

    <!-- Stats -->
    <div class="sr-stats">
        <div class="sr-stat-card">
            <div class="sr-stat-icon primary">
                <span class="material-icons-outlined">assignment</span>
            </div>
            <div class="sr-stat-info">
                <h3><?php echo $totalCount; ?></h3>
                <p>Total Requests</p>
            </div>
        </div>
        <div class="sr-stat-card">
            <div class="sr-stat-icon warning">
                <span class="material-icons-outlined">pending_actions</span>
            </div>
            <div class="sr-stat-info">
                <h3><?php echo $pendingCount; ?></h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="sr-stat-card">
            <div class="sr-stat-icon success">
                <span class="material-icons-outlined">engineering</span>
            </div>
            <div class="sr-stat-info">
                <h3><?php echo $progressCount; ?></h3>
                <p>In Progress</p>
            </div>
        </div>
        <div class="sr-stat-card">
            <div class="sr-stat-icon danger">
                <span class="material-icons-outlined">cancel</span>
            </div>
            <div class="sr-stat-info">
                <h3><?php echo $disapprovedCount; ?></h3>
                <p>Disapproved</p>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="sr-toolbar">
        <div class="sr-search-wrap">
            <span class="material-icons-outlined">search</span>
            <input type="text" id="srSearch" placeholder="Search by ticket, name, request type..." autocomplete="off">
        </div>
        <div class="sr-filter-tabs" id="srFilterTabs">
            <button class="sr-filter-tab active" data-filter="all">All <span class="sr-count-badge"><?php echo $totalCount; ?></span></button>
            <button class="sr-filter-tab" data-filter="pending">Pending <span class="sr-count-badge"><?php echo $pendingCount; ?></span></button>
            <button class="sr-filter-tab" data-filter="progress">In Progress <span class="sr-count-badge"><?php echo $progressCount; ?></span></button>
            <button class="sr-filter-tab" data-filter="disapproved">Disapproved <span class="sr-count-badge"><?php echo $disapprovedCount; ?></span></button>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="sr-grid" id="srGrid">
        <?php if (empty($allRows)): ?>
            <div class="sr-empty">
                <div class="sr-empty-icon">
                    <span class="material-icons-outlined" style="font-size:4rem;">inbox</span>
                </div>
                <h3>No service requests found</h3>
                <p>There are currently no requests matching your criteria.</p>
            </div>
        <?php else: ?>
            <?php foreach ($allRows as $index => $row):
                $srfId = $row['id'];
                $email = $row['email'];
                $name = $row['name'];
                $ticketNumber = $row['ticketNumber'];
                $requestType = $row['requestType'];
                $otherSpecify = $row['otherSpecify'];
                $status = $row['status'];
                $equipment_id = $row['equipment_id'];
                $documents = $row['documents'];
                $description = $row['description'];

                // Status class
                if ($status == "Assigned RICTU staff") {
                    $status_class = 'success';
                    $status_icon = 'check_circle';
                    $filter_group = 'progress';
                } elseif ($row['level'] == "101") {
                    $status_class = 'warning';
                    $status_icon = 'pending_actions';
                    $filter_group = 'pending';
                } elseif ($row['level'] == "2") {
                    $status_class = 'primary';
                    $status_icon = 'engineering';
                    $filter_group = 'progress';
                } elseif ($status == "Disapproved") {
                    $status_class = 'danger';
                    $status_icon = 'cancel';
                    $filter_group = 'disapproved';
                } else {
                    $status_class = 'default';
                    $status_icon = 'radio_button_unchecked';
                    $filter_group = 'progress';
                }

                // Accent color
                $accent_map = ['primary' => 'primary', 'success' => 'success', 'warning' => 'warning', 'danger' => 'danger'];
                $accent = $accent_map[$status_class] ?? 'default';
            ?>
            <div class="sr-card" data-filter="<?php echo $filter_group; ?>" style="animation-delay: <?php echo $index * 0.05; ?>s">
                <div class="sr-card-accent <?php echo $accent; ?>"></div>
                <div class="sr-card-body">
                    <div class="sr-card-top">
                        <span class="sr-card-ticket">#<?php echo htmlspecialchars($ticketNumber); ?></span>
                        <span class="sr-status <?php echo $status_class; ?>">
                            <span class="material-icons-outlined"><?php echo $status_icon; ?></span>
                            <?php echo htmlspecialchars($status); ?>
                        </span>
                    </div>
                    <h4 class="sr-card-title"><?php echo htmlspecialchars($requestType); ?></h4>
                    <div class="sr-card-details">
                        <div class="sr-detail-row">
                            <span class="material-icons-outlined">person</span>
                            <span><?php echo htmlspecialchars($name); ?></span>
                        </div>
                        <div class="sr-detail-row">
                            <span class="material-icons-outlined">business</span>
                            <span><?php echo htmlspecialchars($row['office'] . ' - ' . $row['divSecUnit']); ?></span>
                        </div>
                        <div class="sr-detail-row">
                            <span class="material-icons-outlined">calendar_today</span>
                            <span><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></span>
                        </div>
                    </div>
                    <p class="sr-card-desc"><?php echo htmlspecialchars(substr($description, 0, 150)); ?><?php echo strlen($description) > 150 ? '...' : ''; ?></p>
                </div>
                <div class="sr-card-footer">
                    <div class="dropdown">
                        <?php
                        if ($row['status'] == "Assigned RICTU staff") {
                            echo "
                            <button class='sr-action-btn primary dropdown-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                Actions <span class='material-icons-outlined' style='font-size:1rem;'>expand_more</span>
                            </button>
                            <ul class='dropdown-menu dropdown-menu-end sr-dropdown-menu'>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#read_assign2{$srfId}'><span class='material-icons-outlined'>visibility</span> View Details</a></li>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#readnotificationchat{$srfId}'><span class='material-icons-outlined'>chat</span> Chat</a></li>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#viewfile{$srfId}'><span class='material-icons-outlined'>description</span> Documents</a></li>
                                <li><hr class='dropdown-divider'></li>
                                <li><a class='dropdown-item text-danger' href='#' data-bs-toggle='modal' data-bs-target='#disapproved{$srfId}'><span class='material-icons-outlined'>thumb_down</span> Disapprove</a></li>
                            </ul>";
                        } elseif ($row['level'] == "101") {
                            echo "
                            <button class='sr-action-btn primary dropdown-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                Actions <span class='material-icons-outlined' style='font-size:1rem;'>expand_more</span>
                            </button>
                            <ul class='dropdown-menu dropdown-menu-end sr-dropdown-menu'>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#assign{$srfId}'><span class='material-icons-outlined'>assignment_turned_in</span> Assign / Action</a></li>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#options{$srfId}'><span class='material-icons-outlined'>tune</span> Options</a></li>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#read_assign{$srfId}'><span class='material-icons-outlined'>visibility</span> View Details</a></li>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#viewfile{$srfId}'><span class='material-icons-outlined'>description</span> Documents</a></li>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#readnotificationchat{$srfId}'><span class='material-icons-outlined'>chat</span> Chat</a></li>
                            </ul>";
                        } elseif ($row['level'] == "2") {
                            echo "
                            <button class='sr-action-btn warning dropdown-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                Actions <span class='material-icons-outlined' style='font-size:1rem;'>expand_more</span>
                            </button>
                            <ul class='dropdown-menu dropdown-menu-end sr-dropdown-menu'>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#receive_read{$srfId}'><span class='material-icons-outlined'>visibility</span> View &amp; Receive</a></li>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#viewfile{$srfId}'><span class='material-icons-outlined'>description</span> Documents</a></li>
                                <li><hr class='dropdown-divider'></li>
                                <li><a class='dropdown-item text-danger' href='#' data-bs-toggle='modal' data-bs-target='#disapproved{$srfId}'><span class='material-icons-outlined'>thumb_down</span> Disapprove</a></li>
                            </ul>";
                        } else {
                            echo "
                            <button class='sr-action-btn dropdown-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                Actions <span class='material-icons-outlined' style='font-size:1rem;'>expand_more</span>
                            </button>
                            <ul class='dropdown-menu dropdown-menu-end sr-dropdown-menu'>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#printModal{$srfId}'><span class='material-icons-outlined'>visibility</span> View Details</a></li>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#viewfile{$srfId}'><span class='material-icons-outlined'>description</span> Documents</a></li>
                                <li><a class='dropdown-item' href='#' data-bs-toggle='modal' data-bs-target='#editdetails_1{$srfId}'><span class='material-icons-outlined'>edit</span> Edit</a></li>
                                <li><hr class='dropdown-divider'></li>
                                <li><a class='dropdown-item text-danger' href='#' data-bs-toggle='modal' data-bs-target='#disapproved{$srfId}'><span class='material-icons-outlined'>thumb_down</span> Disapprove</a></li>
                            </ul>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <?php
            // ── MODALS ──

            // 1. EDIT DESCRIPTION MODAL
            echo "<div class='modal fade' id='editdetails_1{$srfId}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>Edit Description</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><form action='update_description.php' method='POST'><div class='modal-body'><input type='hidden' name='srf_id' value='{$srfId}'><div class='mb-3'><label for='description{$srfId}' class='form-label'>Description</label><textarea class='form-control' id='description{$srfId}' name='description' rows='4' required>{$description}</textarea></div></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' class='btn btn-primary'>Update</button></div></div></form></div></div>";

            // 2. VIEW DOCUMENTS MODAL
            echo "<div class='modal fade' id='viewfile{$srfId}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>Manage Documents</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body'>";
            if (!empty($documents)) {
                $docArray = explode(',', $documents);
                echo '<div class="mb-3"><label class="form-label">Existing Documents</label><ul class="list-group">';
                foreach ($docArray as $doc) {
                    echo '<li class="list-group-item d-flex justify-content-between align-items-center"><a href="attached_documents/' . htmlspecialchars(trim($doc)) . '" target="_blank">' . htmlspecialchars(trim($doc)) . '</a><span class="material-icons-outlined text-primary">visibility</span></li>';
                }
                echo '</ul></div><hr>';
            } else { echo '<p class="text-center text-muted">No documents have been uploaded.</p><hr>'; }
            echo "<form id='uploadForm{$srfId}'><div class='mb-3'><label for='documentName{$srfId}' class='form-label'>New Document Name</label><input type='text' class='form-control' id='documentName{$srfId}' name='documentName' placeholder='e.g., Diagnostic Report' required></div><div class='mb-3'><label for='documentFile{$srfId}' class='form-label'>Upload File</label><input type='file' class='form-control' id='documentFile{$srfId}' name='documentFile' required></div><input type='hidden' name='srfId' value='{$srfId}'></form></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='button' class='btn btn-primary' onclick='submitUploadForm({$srfId})'>Upload New</button></div></div></div></div>";

            // 3. CHAT MODAL
            echo "<div class='modal fade' id='readnotificationchat{$srfId}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>Chat for #{$ticketNumber}</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body'><div id='chatContainer{$srfId}' style='max-height:300px;overflow-y:auto;border:1px solid var(--gray-200);padding:10px;margin-bottom:15px;border-radius:8px;background:var(--gray-50);'><p class='text-center text-muted'>Loading messages...</p></div><form id='messageForm{$srfId}'><div class='mb-3'><textarea class='form-control' name='message' rows='3' placeholder='Type your message...' required></textarea></div><input type='hidden' name='srfId' value='{$srfId}'><button type='submit' class='btn btn-primary w-100'>Send</button></form></div></div></div></div>";

            echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                var chatModal = document.getElementById('readnotificationchat{$srfId}');
                if (!chatModal) return;
                var chatContainer = document.getElementById('chatContainer{$srfId}');
                var messageForm = document.getElementById('messageForm{$srfId}');
                if (!messageForm) return;

                function fetchMessages() {
                    fetch('getMessages.php?srfId={$srfId}').then(function(r){return r.json();}).then(function(data){
                        chatContainer.innerHTML = '';
                        if (data.length === 0) {
                            chatContainer.innerHTML = '<p class=\"text-center text-muted\">No messages yet. Start the conversation!</p>';
                        } else {
                            data.forEach(function(msg){
                                chatContainer.innerHTML += '<div class=\"message mb-2\"><strong>' + msg.sender + ':</strong> ' + msg.message + '<br><small class=\"text-muted\">' + new Date(msg.created_at).toLocaleString() + '</small></div><hr class=\"my-1\">';
                            });
                        }
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    })['catch'](function(err){console.error('Error fetching messages:', err);});
                }

                chatModal.addEventListener('shown.bs.modal', fetchMessages);

                messageForm.addEventListener('submit', function(e){
                    e.preventDefault();
                    var fd = new FormData(this);
                    fetch('sendMessage.php',{method:'POST',body:fd}).then(function(){ messageForm.reset(); fetchMessages(); })['catch'](function(err){console.error('Error sending message:',err);});
                });
            });
            </script>";

            // 4. IFRAME MODALS
            $iframeModalTitles = [
                'read_assign2' => 'View Document', 'read_assign' => 'View Document',
                'receive_read' => 'View &amp; Receive Document', 'printModal' => 'View Document Details'
            ];
            $iframeModalFooters = [
                'read_assign2' => "<span class='d-inline-block' tabindex='0' data-bs-toggle='tooltip' title='{$legal_disclaimer}'><button type='button' data-bs-toggle='modal' data-bs-target='#receive_staff{$srfId}' class='btn btn-success'>Receive</button></span>",
                'read_assign' => "<button type='button' data-bs-toggle='modal' data-bs-target='#assign{$srfId}' class='btn btn-primary'>Assign</button>",
                'receive_read' => "<span class='d-inline-block' tabindex='0' data-bs-toggle='tooltip' title='{$legal_disclaimer}'><button type='button' data-bs-toggle='modal' data-bs-target='#approve{$srfId}' class='btn btn-success'>Receive</button></span><button type='button' class='btn btn-info text-white ms-1' data-bs-toggle='modal' data-bs-target='#options{$srfId}'>View Equipment</button>",
                'printModal' => "<span class='d-inline-block' tabindex='0' data-bs-toggle='tooltip' title='{$legal_disclaimer}'><button type='button' data-bs-toggle='modal' data-bs-target='#approve{$srfId}' class='btn btn-success'>Approve</button></span><button type='button' class='btn btn-info text-white ms-1' data-bs-toggle='modal' data-bs-target='#options{$srfId}'>Options</button>"
            ];
            foreach($iframeModalTitles as $id => $title) {
                $footer = $iframeModalFooters[$id];
                echo "<div class='modal fade' id='{$id}{$srfId}' tabindex='-1'><div class='modal-dialog modal-xl'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>{$title}</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body p-0'><iframe src='printform.php?id={$srfId}' style='width:100%;height:75vh;border:none;'></iframe></div><div class='modal-footer'>{$footer}<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button></div></div></div></div>";
            }

            // 5. CONFIRMATION MODALS
            echo "<div class='modal fade' id='receive_staff{$srfId}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>Confirm Reception</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body'><p>Are you sure you want to mark this request as received?</p><div class='alert alert-info mt-3 mb-0' style='font-size:0.85rem;text-align:justify;'><strong>Notice:</strong> {$legal_disclaimer}</div></div><div class='modal-footer'><form action='receive_action.php' method='post' class='d-flex align-items-center w-100 justify-content-end'><input type='hidden' name='srfId' value='{$srfId}'><button type='button' class='btn btn-secondary me-2' data-bs-dismiss='modal'>Cancel</button><span class='d-inline-block' tabindex='0' data-bs-toggle='tooltip' title='{$legal_disclaimer}'><button type='submit' class='btn btn-success'>Affix Signature</button></span></form></div></div></div></div>";

            echo "<div class='modal fade' id='disapproved{$srfId}' tabindex='-1'><div class='modal-dialog'><form method='POST' action='disapproved.php'><div class='modal-content'><div class='modal-header bg-danger text-white'><h5 class='modal-title'>Disapprove Request</h5><button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button></div><div class='modal-body'><p>Are you sure you want to disapprove request #{$ticketNumber}?</p><input type='hidden' name='disapproved' value='{$srfId}'><input type='hidden' name='level' value='{$row['level']}'><input type='hidden' name='name' value='{$name}'><div class='form-group'><label for='remarks_disapprove_{$srfId}'>Remarks (Required)</label><textarea class='form-control' id='remarks_disapprove_{$srfId}' name='remarks' rows='3' required></textarea></div></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' class='btn btn-danger'>Confirm Disapproval</button></div></div></form></div></div>";

            echo "<div class='modal fade' id='approve{$srfId}' tabindex='-1'><div class='modal-dialog'><form method='GET' action='approve.php'><div class='modal-content'><div class='modal-header bg-success text-white'><h5 class='modal-title'>Confirm Action</h5><button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button></div><div class='modal-body'><p>Do you really want to approve request #{$ticketNumber}?</p><div class='alert alert-info mt-3 mb-0' style='font-size:0.85rem;text-align:justify;'><strong>Notice:</strong> {$legal_disclaimer}</div><input type='hidden' name='approve' value='{$srfId}'><input type='hidden' name='level' value='{$row['level']}'><input type='hidden' name='name' value='{$name}'><input type='hidden' name='description' value='{$description}'><input type='hidden' name='requestType' value='{$requestType}'><input type='hidden' name='equipment_id' value='{$equipment_id}'></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><span class='d-inline-block ms-2' tabindex='0' data-bs-toggle='tooltip' title='{$legal_disclaimer}'><button type='submit' class='btn btn-success'>Affix Signature</button></span></div></div></form></div></div>";

            // 6. OPTIONS MODAL
            echo "<div class='modal fade' id='options{$srfId}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><div class='modal-header bg-info text-white'><h5 class='modal-title'>Options &amp; Updates</h5><button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button></div><div class='modal-body'><form method='POST' action='options.php' enctype='multipart/form-data'><input type='hidden' name='srfId' value='{$srfId}' /><div class='d-grid gap-2 mb-3'><a href='mainmenu.php?dir=search_inventory&id={$srfId}' class='btn btn-success'><span class='material-icons-outlined' style='vertical-align:middle;font-size:1.2em;'>inventory</span> View Inventory</a><button type='button' class='btn btn-primary open-equipment' data-id='{$equipment_id}'><span class='material-icons-outlined' style='vertical-align:middle;font-size:1.2em;'>devices</span> View Equipment Details</button></div><div class='mb-3'><label>Changes Made</label><div class='form-check'><input class='form-check-input' type='checkbox' name='changes[]' value='SSD Changed' id='ssd_{$srfId}'><label class='form-check-label' for='ssd_{$srfId}'>SSD Changed</label></div><div class='form-check'><input class='form-check-input' type='checkbox' name='changes[]' value='Power Chord Changed' id='pwr_{$srfId}'><label class='form-check-label' for='pwr_{$srfId}'>Power Chord Changed</label></div><div class='form-check'><input class='form-check-input' type='checkbox' name='changes[]' value='Battery Changed' id='batt_{$srfId}'><label class='form-check-label' for='batt_{$srfId}'>Battery Changed</label></div><div class='form-check'><input class='form-check-input' type='checkbox' name='changes[]' value='Screen Changed' id='scr_{$srfId}'><label class='form-check-label' for='scr_{$srfId}'>Screen Changed</label></div></div><div class='mb-3'><label for='remarks_options_{$srfId}'>Remarks (Required)</label><textarea class='form-control' id='remarks_options_{$srfId}' name='remarks' rows='3' required></textarea></div><div class='mb-3'><label for='fileToUpload_{$srfId}'>Upload Supporting Document</label><input type='file' name='fileToUpload' class='form-control' id='fileToUpload_{$srfId}'></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' name='action' value='submit' class='btn btn-info text-white'>Submit Updates</button></div></div></form></div></div></div>";

            // 7. ASSIGN ACTION MODAL
            $jsRequestType = addslashes($requestType);
            $jsDescription = preg_replace("/[\r\n]+/", " ", $description);
            $jsDescription = addslashes($jsDescription);
            $isZoomRequest = (strcasecmp($requestType, 'Zoom') === 0) ? '1' : '0';
            $rawZoomTitle = trim($row['zoom_title'] ?? '');
            if ($rawZoomTitle === '') {
                $rawZoomTitle = calendarExtractZoomField($description, 'Meeting Title');
            }
            if ($rawZoomTitle === '') {
                $rawZoomTitle = $ticketNumber;
            }
            $rawZoomScheduleDateTime = calendarNormalizeZoomDateTime($row['zoom_schedule_datetime'] ?? '');
            if ($rawZoomScheduleDateTime === '') {
                $rawZoomScheduleDateTime = calendarNormalizeZoomDateTime(calendarExtractZoomField($description, 'Date & Time'));
            }
            $zoomTitleValue = htmlspecialchars($rawZoomTitle, ENT_QUOTES, 'UTF-8');
            $zoomScheduleValue = htmlspecialchars($rawZoomScheduleDateTime, ENT_QUOTES, 'UTF-8');
            $officeValue = htmlspecialchars($row['office'], ENT_QUOTES, 'UTF-8');
            $divSecUnitValue = htmlspecialchars($row['divSecUnit'], ENT_QUOTES, 'UTF-8');
            $divisionDisplay = htmlspecialchars(trim($row['office'] . ' - ' . $row['divSecUnit']), ENT_QUOTES, 'UTF-8');

            echo "<div class='modal fade' id='assign{$srfId}' tabindex='-1'><div class='modal-dialog'><form method='GET' action='assign.php' data-is-zoom='{$isZoomRequest}'><div class='modal-content'><div class='modal-header'><h5 class='modal-title'>Assign Action</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body'><input type='hidden' name='assign' value='{$srfId}'/><div class='row g-3'><div class='col-md-6'><label>Date</label><input type='date' class='form-control' name='action_date' id='action_date_{$srfId}' onchange='checkAssignForm({$srfId})' required></div><div class='col-md-6'><label>Time</label><input type='time' class='form-control' name='action_time' value='{$formattedTime}' required></div></div><div class='mt-3'><label class='form-label d-flex justify-content-between align-items-center'><span>Action Taken</span><button type='button' id='ai-btn-{$srfId}' class='btn btn-outline-primary ai-suggestion-btn' onclick='getAiSuggestion({$srfId}, \"{$jsRequestType}\", \"{$jsDescription}\")'><span class='material-icons-outlined' style='font-size:1em;vertical-align:text-bottom;'>auto_awesome</span> Suggest</button></label><textarea class='form-control' id='action_taken_{$srfId}' name='action_taken' rows='3' oninput='checkAssignForm({$srfId})' required></textarea></div><div class='mt-3'><label>Assign To</label><select name='personelid' class='form-select' id='personelid_{$srfId}' onchange='updateNameInTextField(this, {$srfId}); checkAssignForm({$srfId});' required><option disabled selected value=''>Select Personnel...</option>";
            $sql2 = "SELECT DISTINCT personelid, name FROM srfactionstaff WHERE Office = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("s", $_SESSION['OfficeSRF']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            if ($result2->num_rows > 0) { while ($officeRow = $result2->fetch_assoc()) { echo "<option value='" . htmlspecialchars($officeRow['personelid']) . "'>" . strtoupper(htmlspecialchars($officeRow['name'])) . "</option>"; } }
            echo "</select></div><div class='form-check mt-3'><input class='form-check-input' type='checkbox' name='mark_as_done' id='mark_as_done_{$srfId}' value='1' onchange='checkAssignForm({$srfId})'><label class='form-check-label fw-semibold' for='mark_as_done_{$srfId}'>Mark as Done</label></div><div class='border rounded-3 p-3 mt-3 bg-light mark-done-fields' id='markDoneFields_{$srfId}' style='display:none;'><label class='form-label d-block'>Completion Result</label><div class='form-check form-check-inline'><input class='form-check-input' type='radio' name='completion_result' id='completion_resolved_{$srfId}' value='Resolved' onchange='checkAssignForm({$srfId})'><label class='form-check-label' for='completion_resolved_{$srfId}'>Resolved</label></div><div class='form-check form-check-inline'><input class='form-check-input' type='radio' name='completion_result' id='completion_unserviceable_{$srfId}' value='Unserviceable' onchange='checkAssignForm({$srfId})'><label class='form-check-label' for='completion_unserviceable_{$srfId}'>Unserviceable</label></div><div class='form-check form-check-inline'><input class='form-check-input' type='radio' name='completion_result' id='completion_parts_{$srfId}' value='Needs Parts Replacement' onchange='checkAssignForm({$srfId})'><label class='form-check-label' for='completion_parts_{$srfId}'>Needs Parts Replacement</label></div><div class='mt-3 return-reason-fields' id='returnReasonFields_{$srfId}' style='display:none;'><label class='form-label'>Return Reason</label><textarea class='form-control' name='return_reason' id='return_reason_{$srfId}' rows='3' placeholder='Reason / remarks for return approval' oninput='checkAssignForm({$srfId})'></textarea><div class='form-text'>Unserviceable equipment will be submitted to the existing return approval queue.</div></div></div><input type='hidden' name='assignedperson_1' id='assignedperson_1_{$srfId}'><input type='hidden' name='email' value='{$email}'/><input type='hidden' name='name' value='{$name}'/><input type='hidden' name='ticketNumber' value='{$ticketNumber}'/><input type='hidden' name='requestType' value='{$requestType}'/><input type='hidden' name='otherSpecify' value='{$otherSpecify}'/><input type='hidden' name='equipment_id' value='{$equipment_id}'/><input type='hidden' name='zoom_title' value='{$zoomTitleValue}'/><input type='hidden' name='zoom_schedule_datetime' value='{$zoomScheduleValue}'/><input type='hidden' name='office' value='{$officeValue}'/><input type='hidden' name='divSecUnit' value='{$divSecUnitValue}'/><div class='border rounded-3 p-3 mt-3 bg-light zoom-completion-fields' id='zoomFields_{$srfId}' style='display:none;'><div class='alert alert-warning py-2 mb-3' style='font-size:0.85rem;'><strong>Zoom calendar save:</strong> Meeting ID and password will be saved to Calendar Scheduler with the requested division.</div><div class='mb-2'><label class='form-label'>Meeting ID</label><input type='text' class='form-control' name='zoom_meeting_id' id='zoom_meeting_id_{$srfId}' placeholder='Enter meeting ID' oninput='checkAssignForm({$srfId})'></div><div class='mb-2'><label class='form-label'>Password</label><input type='text' class='form-control' name='zoom_password' id='zoom_password_{$srfId}' placeholder='Enter password' oninput='checkAssignForm({$srfId})'></div><div class='mb-2'><label class='form-label'>Meeting Link (Optional)</label><input type='url' class='form-control' name='zoom_link' id='zoom_link_{$srfId}' placeholder='https://...'></div><div class='small text-muted'>Title: {$zoomTitleValue}<br>Zoom Schedule: {$zoomScheduleValue}<br>Requested Division: {$divisionDisplay}</div></div><div class='alert alert-info mt-3 mb-0' style='font-size:0.85rem;text-align:justify;'><strong>Notice:</strong> {$legal_disclaimer}</div></div><div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><span class='d-inline-block ms-2' tabindex='0' data-bs-toggle='tooltip' title='{$legal_disclaimer}'><button type='submit' id='submitBtn_{$srfId}' class='btn btn-primary' disabled>Affix Signature</button></span></div></div></form></div></div>";

            $stmt2->close();
            endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Bootstrap Tooltips ──
    var ttTriggers = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    ttTriggers.map(function(el) { return new bootstrap.Tooltip(el); });

    // ── Z-index stacking for dropdowns ──
    document.querySelectorAll('.sr-card .dropdown').forEach(function(dd) {
        var card = dd.closest('.sr-card');
        if (!card) return;
        dd.addEventListener('show.bs.dropdown', function() { card.classList.add('sr-dropdown-active'); });
        dd.addEventListener('hide.bs.dropdown', function() { card.classList.remove('sr-dropdown-active'); });
    });

    // ── View Equipment button delegation ──
    document.body.addEventListener('click', function(e) {
        var btn = e.target.closest('.open-equipment');
        if (btn) {
            var eqId = btn.getAttribute('data-id');
            if (eqId) window.location.href = 'mainmenu.php?dir=equipment_page&equipment_id=' + eqId;
        }
    });

    // ── Search & Filter ──
    var searchInput = document.getElementById('srSearch');
    var filterTabs = document.querySelectorAll('.sr-filter-tab');
    var cards = document.querySelectorAll('.sr-card');

    function applyFilters() {
        var query = (searchInput ? searchInput.value.toLowerCase() : '');
        var activeFilter = document.querySelector('.sr-filter-tab.active');
        var filterVal = activeFilter ? activeFilter.getAttribute('data-filter') : 'all';

        cards.forEach(function(card) {
            var cardFilter = card.getAttribute('data-filter') || '';
            var matchesFilter = (filterVal === 'all' || cardFilter === filterVal);

            var matchesSearch = true;
            if (query) {
                var text = card.textContent.toLowerCase();
                matchesSearch = text.indexOf(query) !== -1;
            }

            card.classList.toggle('hidden', !(matchesFilter && matchesSearch));
        });
    }

    if (searchInput) {
        var debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(applyFilters, 200);
        });
    }

    filterTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            filterTabs.forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');
            applyFilters();
        });
    });

    // ── Toast from URL parameters ──
    var urlParams = new URLSearchParams(window.location.search);
    var toastMsg = urlParams.get('toast_msg');
    var toastType = urlParams.get('toast_type') || 'success';
    if (toastMsg) {
        setTimeout(function() { showToast(decodeURIComponent(toastMsg), toastType); }, 300);
        // Clean URL without reload
        if (window.history.replaceState) {
            var url = new URL(window.location);
            url.searchParams.delete('toast_msg');
            url.searchParams.delete('toast_type');
            window.history.replaceState({}, document.title, url.toString());
        }
    }

    // ── Button Loading Indicator ──
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('button[type="submit"]');
        if (!btn || btn.disabled || btn.classList.contains('sr-btn-loading')) return;
        var form = btn.closest('form');
        if (!form) return;
        btn.classList.add('sr-btn-loading');
        var origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="sr-btn-spinner"></span><span class="sr-btn-text">Processing...</span>';
        btn.disabled = true;
        if (form) form.submit();
    });
});

// ── Toast Notification ──
function showToast(message, type) {
    type = type || 'info';
    var container = document.getElementById('srToastContainer');
    if (!container) return;

    var icons = { success: 'check_circle', error: 'cancel', warning: 'warning', info: 'info' };
    var icon = icons[type] || 'info';

    var toast = document.createElement('div');
    toast.className = 'sr-toast ' + type;
    toast.innerHTML =
        '<div class="sr-toast-icon ' + type + '"><span class="material-icons-outlined">' + icon + '</span></div>' +
        '<div class="sr-toast-body"><p>' + message + '</p></div>' +
        '<button class="sr-toast-close" onclick="this.parentElement.remove();"><span class="material-icons-outlined">close</span></button>';

    container.appendChild(toast);
    requestAnimationFrame(function() { toast.classList.add('show'); });

    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() { if (toast.parentElement) toast.remove(); }, 400);
    }, 4000);
}

// ── File upload ──
function submitUploadForm(srfId) {
    var form = document.getElementById('uploadForm' + srfId);
    if (!form.documentName.value || !form.documentFile.files[0]) {
        showToast('Please provide both a document name and a file.', 'warning');
        return;
    }
    var btn = document.querySelector('#viewfile' + srfId + ' .btn-primary');
    if (btn) {
        btn.classList.add('sr-btn-loading');
        btn.disabled = true;
        btn.innerHTML = '<span class="sr-btn-spinner"></span><span class="sr-btn-text">Uploading...</span>';
    }
    var fd = new FormData(form);
    fetch('upload.php', { method: 'POST', body: fd })
        .then(function(r) { return r.text(); })
        .then(function(data) {
            try {
                var json = JSON.parse(data);
                showToast(json.message || 'Upload successful!', json.status || 'success');
            } catch(e) {
                showToast(data || 'Upload successful!', 'success');
            }
            setTimeout(function() { location.reload(); }, 1500);
        })
        .catch(function(err) {
            console.error(err);
            showToast('Upload failed. Please try again.', 'error');
        });
}

// ── AI Suggestion ──
function updateNameInTextField(sel, id) {
    document.getElementById('assignedperson_1_' + id).value = sel.options[sel.selectedIndex].text;
}

function checkAssignForm(id) {
    var dateVal = document.getElementById('action_date_' + id);
    var actionVal = document.getElementById('action_taken_' + id);
    var personelVal = document.getElementById('personelid_' + id);
    var btn = document.getElementById('submitBtn_' + id);
    if (!dateVal || !actionVal || !personelVal || !btn) return;
    var form = btn.closest('form');
    var isZoom = form && form.getAttribute('data-is-zoom') === '1';
    var markAsDone = document.getElementById('mark_as_done_' + id);
    var isMarkDone = markAsDone && markAsDone.checked;
    var markDonePanel = document.getElementById('markDoneFields_' + id);
    var completionResult = document.querySelector('#markDoneFields_' + id + ' input[name="completion_result"]:checked');
    var completionInputs = document.querySelectorAll('#markDoneFields_' + id + ' input[name="completion_result"]');
    var returnReasonPanel = document.getElementById('returnReasonFields_' + id);
    var returnReason = document.getElementById('return_reason_' + id);
    var zoomPanel = document.getElementById('zoomFields_' + id);
    var meetingId = document.getElementById('zoom_meeting_id_' + id);
    var password = document.getElementById('zoom_password_' + id);
    var showZoomFields = isZoom && isMarkDone;
    var showReturnReason = isMarkDone && completionResult && completionResult.value === 'Unserviceable';

    if (markDonePanel) markDonePanel.style.display = isMarkDone ? 'block' : 'none';
    personelVal.required = !isMarkDone;
    personelVal.disabled = isMarkDone;
    if (isMarkDone) {
        personelVal.value = '';
        var assignedPerson = document.getElementById('assignedperson_1_' + id);
        if (assignedPerson) assignedPerson.value = 'MARK AS DONE';
    }
    completionInputs.forEach(function(input) { input.required = isMarkDone; });
    if (returnReasonPanel) returnReasonPanel.style.display = showReturnReason ? 'block' : 'none';
    if (returnReason) returnReason.required = showReturnReason;
    if (zoomPanel) zoomPanel.style.display = showZoomFields ? 'block' : 'none';
    if (meetingId) meetingId.required = showZoomFields;
    if (password) password.required = showZoomFields;

    var dateOk = dateVal.value.trim() !== '';
    var actionOk = actionVal.value.trim() !== '';
    var personelOk = isMarkDone || personelVal.value !== '';
    var completionOk = !isMarkDone || !!completionResult;
    var returnOk = !showReturnReason || (returnReason && returnReason.value.trim() !== '');
    var zoomOk = !showZoomFields || ((meetingId && meetingId.value.trim() !== '') && (password && password.value.trim() !== ''));
    btn.disabled = !(dateOk && actionOk && personelOk && completionOk && returnOk && zoomOk);
}

async function getAiSuggestion(srfId, requestType, description) {
    var btn = document.getElementById('ai-btn-' + srfId);
    var ta = document.getElementById('action_taken_' + srfId);
    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Thinking...';
    ta.placeholder = 'Generating AI suggestion...';
    try {
        var r = await fetch('ai_suggestion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'requestType=' + encodeURIComponent(requestType) + '&description=' + encodeURIComponent(description)
        });
        if (!r.ok) throw new Error('Server error');
        ta.value = (await r.text()).trim();
    } catch (e) {
        console.error(e);
        ta.value = 'Sorry, could not get a suggestion at this time.';
    }
    btn.disabled = false;
    btn.innerHTML = orig;
    ta.placeholder = '';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
