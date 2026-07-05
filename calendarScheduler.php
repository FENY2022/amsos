<?php
require_once 'calendarSchedulerdb.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<div class="scheduler-app">
    <style>
        .scheduler-app {
            --bg: #f5f7fb;
            --panel: rgba(255, 255, 255, 0.92);
            --panel-border: rgba(96, 125, 139, 0.14);
            --text: #132238;
            --muted: #66758a;
            --primary: #2563eb;
            --primary-2: #7c3aed;
            --success: #0f9d58;
            --danger: #dc2626;
            --shadow: 0 18px 50px rgba(15, 23, 42, 0.12);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 30%),
                radial-gradient(circle at top right, rgba(124, 58, 237, 0.12), transparent 28%),
                var(--bg);
            padding: 24px;
            min-height: 100vh;
        }

        .scheduler-hero,
        .scheduler-panel,
        .scheduler-stat,
        .scheduler-modal__dialog {
            backdrop-filter: blur(16px);
        }

        .scheduler-hero {
            display: flex;
            gap: 18px;
            justify-content: space-between;
            align-items: flex-end;
            padding: 28px;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.96), rgba(124, 58, 237, 0.92));
            color: #fff;
            box-shadow: var(--shadow);
            margin-bottom: 18px;
        }

        .scheduler-hero h1 {
            margin: 6px 0 10px;
            font-size: clamp(1.6rem, 3vw, 2.65rem);
            line-height: 1.1;
            max-width: 14ch;
        }

        .scheduler-hero p {
            margin: 0;
            max-width: 68ch;
            color: rgba(255, 255, 255, 0.86);
        }

        .scheduler-hero__meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
            align-items: center;
        }

        .scheduler-pill,
        .scheduler-stat__label {
            font-size: 0.82rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .scheduler-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #fff;
            white-space: nowrap;
        }

        .scheduler-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .scheduler-btn {
            appearance: none;
            border: 0;
            border-radius: 14px;
            padding: 12px 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .scheduler-btn:hover { transform: translateY(-1px); }

        .scheduler-btn--primary {
            background: #fff;
            color: var(--primary);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
        }

        .scheduler-btn--ghost {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .scheduler-btn--soft {
            background: #eef2ff;
            color: #3730a3;
        }

        .scheduler-btn--danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .scheduler-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(320px, 0.85fr);
            gap: 18px;
            align-items: start;
        }

        .scheduler-stack {
            display: grid;
            gap: 18px;
        }

        .scheduler-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .scheduler-stat,
        .scheduler-panel {
            border-radius: 24px;
            background: var(--panel);
            border: 1px solid var(--panel-border);
            box-shadow: var(--shadow);
        }

        .scheduler-stat {
            padding: 18px;
        }

        .scheduler-stat__label {
            color: var(--muted);
            margin-bottom: 10px;
        }

        .scheduler-stat__value {
            font-size: clamp(1.7rem, 3vw, 2.4rem);
            font-weight: 800;
            line-height: 1;
        }

        .scheduler-stat__note {
            margin-top: 8px;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .scheduler-panel {
            padding: 18px;
        }

        .scheduler-panel__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .scheduler-panel__header h2,
        .scheduler-panel__header h3 {
            margin: 0;
            font-size: 1.05rem;
        }

        .scheduler-panel__header p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .scheduler-toolbar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .scheduler-toolbar .scheduler-btn {
            padding: 10px 14px;
            font-size: 0.92rem;
        }

        .scheduler-search {
            width: 100%;
            border: 1px solid rgba(100, 116, 139, 0.2);
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 0.96rem;
            outline: none;
            background: #fff;
        }

        .scheduler-search:focus {
            border-color: rgba(37, 99, 235, 0.55);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
        }

        #calendar {
            min-height: 760px;
        }

        .fc {
            --fc-border-color: rgba(100, 116, 139, 0.12);
            --fc-neutral-bg-color: #fff;
            --fc-page-bg-color: transparent;
            --fc-today-bg-color: rgba(37, 99, 235, 0.06);
            --fc-button-bg-color: #fff;
            --fc-button-border-color: rgba(100, 116, 139, 0.18);
            --fc-button-hover-bg-color: #eef2ff;
            --fc-button-hover-border-color: rgba(37, 99, 235, 0.22);
            --fc-button-active-bg-color: #dbeafe;
            --fc-button-active-border-color: rgba(37, 99, 235, 0.3);
            --fc-button-text-color: #23314d;
            font-size: 0.95rem;
        }

        .fc .fc-toolbar {
            gap: 12px;
            margin-bottom: 1.2rem;
        }

        .fc .fc-toolbar-title {
            font-size: clamp(1.2rem, 2vw, 1.7rem);
            font-weight: 800;
        }

        .fc .fc-button {
            border-radius: 12px;
            box-shadow: none !important;
            text-transform: capitalize;
            font-weight: 700;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            color: #1d4ed8;
        }

        .fc .fc-daygrid-day-number,
        .fc .fc-col-header-cell-cushion {
            color: #334155;
            text-decoration: none;
            font-weight: 600;
        }

        .fc .fc-daygrid-event,
        .fc .fc-timegrid-event {
            border-radius: 12px;
            border: 0;
            padding: 4px 8px;
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.14);
            cursor: pointer;
        }

        .fc .fc-event-main {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .fc-event--linked {
            background: linear-gradient(135deg, #2563eb, #7c3aed) !important;
        }

        .fc-event--plain {
            background: linear-gradient(135deg, #0f9d58, #14b8a6) !important;
        }

        .scheduler-upcoming {
            display: grid;
            gap: 10px;
            margin-top: 12px;
        }

        .scheduler-upcoming__item {
            display: grid;
            gap: 4px;
            padding: 14px;
            border-radius: 18px;
            border: 1px solid rgba(100, 116, 139, 0.14);
            background: #fff;
            cursor: pointer;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }

        .scheduler-upcoming__item:hover {
            transform: translateY(-1px);
            border-color: rgba(37, 99, 235, 0.28);
        }

        .scheduler-upcoming__date {
            color: var(--primary);
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .scheduler-upcoming__title {
            font-weight: 700;
        }

        .scheduler-upcoming__meta {
            color: var(--muted);
            font-size: 0.9rem;
            word-break: break-word;
        }

        .scheduler-empty {
            padding: 18px;
            border-radius: 18px;
            background: #f8fafc;
            color: var(--muted);
            border: 1px dashed rgba(100, 116, 139, 0.24);
        }

        .scheduler-modal {
            position: fixed;
            inset: 0;
            display: none;
            z-index: 2000;
        }

        .scheduler-modal[data-open="true"] {
            display: grid;
            place-items: center;
        }

        .scheduler-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.56);
        }

        .scheduler-modal__dialog {
            position: relative;
            width: min(920px, calc(100vw - 28px));
            max-height: min(90vh, 900px);
            overflow: auto;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 35px 90px rgba(15, 23, 42, 0.28);
            border: 1px solid rgba(255, 255, 255, 0.28);
            padding: 22px;
        }

        .scheduler-modal__header,
        .scheduler-modal__footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .scheduler-modal__header {
            margin-bottom: 18px;
        }

        .scheduler-modal__header h2 {
            margin: 4px 0 0;
            font-size: 1.45rem;
        }

        .scheduler-modal__close {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 0;
            background: #eef2ff;
            color: #3730a3;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .scheduler-modal__body {
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        }

        .scheduler-form,
        .scheduler-details {
            border: 1px solid rgba(100, 116, 139, 0.14);
            border-radius: 22px;
            background: #fff;
            padding: 18px;
        }

        .scheduler-form {
            display: grid;
            gap: 14px;
        }

        .scheduler-field {
            display: grid;
            gap: 8px;
        }

        .scheduler-field label {
            font-weight: 700;
            color: #223047;
        }

        .scheduler-input,
        .scheduler-textarea {
            width: 100%;
            border: 1px solid rgba(100, 116, 139, 0.18);
            border-radius: 14px;
            padding: 12px 14px;
            outline: none;
            font: inherit;
            background: #fff;
        }

        .scheduler-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .scheduler-input:focus,
        .scheduler-textarea:focus {
            border-color: rgba(37, 99, 235, 0.58);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
        }

        .scheduler-details {
            display: grid;
            gap: 14px;
            align-content: start;
        }

        .scheduler-detail {
            padding: 14px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid rgba(100, 116, 139, 0.12);
        }

        .scheduler-detail__label {
            display: block;
            color: var(--muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .scheduler-detail__value {
            font-weight: 600;
            word-break: break-word;
        }

        .scheduler-detail__value--secret {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            letter-spacing: 0.04em;
        }

        .scheduler-detail__actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .scheduler-footer-spacer {
            flex: 1;
        }

        .scheduler-status {
            min-height: 20px;
            color: var(--muted);
            font-size: 0.94rem;
        }

        .scheduler-status[data-tone="success"] { color: var(--success); }
        .scheduler-status[data-tone="danger"] { color: var(--danger); }

        .scheduler-display-toggle {
            display: inline-flex;
            gap: 8px;
            padding: 6px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .scheduler-display-toggle .scheduler-btn {
            min-width: 112px;
            padding: 10px 14px;
            background: transparent;
            color: rgba(255, 255, 255, 0.9);
            border: 1px solid transparent;
        }

        .scheduler-display-toggle .scheduler-btn.is-active {
            background: #fff;
            color: var(--primary);
        }

        .scheduler-calendar-grid {
            display: grid;
            gap: 18px;
        }

        .scheduler-calendar-grid.is-dual {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .scheduler-calendar-panel {
            display: grid;
            gap: 12px;
        }

        .scheduler-calendar-panel__label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 0 4px;
            color: var(--muted);
            font-size: 0.92rem;
            font-weight: 700;
        }

        .scheduler-calendar-panel__label strong {
            color: var(--text);
            font-size: 1rem;
        }

        .scheduler-toast-stack {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 4000;
            display: grid;
            gap: 10px;
            width: min(380px, calc(100vw - 24px));
            pointer-events: none;
        }

        .scheduler-toast {
            pointer-events: auto;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.96);
            color: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transform: translateY(-8px);
            opacity: 0;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .scheduler-toast.is-visible {
            transform: translateY(0);
            opacity: 1;
        }

        .scheduler-toast__dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            margin-top: 6px;
            flex: 0 0 auto;
            background: #60a5fa;
        }

        .scheduler-toast[data-tone="success"] .scheduler-toast__dot { background: #22c55e; }
        .scheduler-toast[data-tone="danger"] .scheduler-toast__dot { background: #ef4444; }
        .scheduler-toast[data-tone="warning"] .scheduler-toast__dot { background: #f59e0b; }

        .scheduler-toast__body {
            min-width: 0;
            flex: 1;
        }

        .scheduler-toast__title {
            font-weight: 800;
            margin-bottom: 2px;
        }

        .scheduler-toast__message {
            color: rgba(255, 255, 255, 0.84);
            font-size: 0.94rem;
            line-height: 1.35;
            word-break: break-word;
        }

        .scheduler-toast__close {
            border: 0;
            background: transparent;
            color: rgba(255, 255, 255, 0.78);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .scheduler-confirm {
            position: fixed;
            inset: 0;
            z-index: 3500;
            display: none;
            place-items: center;
        }

        .scheduler-confirm[data-open="true"] {
            display: grid;
        }

        .scheduler-confirm__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.58);
        }

        .scheduler-confirm__dialog {
            position: relative;
            width: min(460px, calc(100vw - 24px));
            border-radius: 24px;
            background: #fff;
            padding: 22px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.28);
            border: 1px solid rgba(255, 255, 255, 0.34);
        }

        .scheduler-confirm__title {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0 0 8px;
        }

        .scheduler-confirm__message {
            color: var(--muted);
            line-height: 1.5;
            margin-bottom: 18px;
        }

        .scheduler-confirm__actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 1180px) {
            .scheduler-grid,
            .scheduler-modal__body {
                grid-template-columns: 1fr;
            }

            .scheduler-calendar-grid.is-dual {
                grid-template-columns: 1fr;
            }

            .scheduler-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .scheduler-app { padding: 14px; }
            .scheduler-hero {
                padding: 20px;
                align-items: stretch;
                flex-direction: column;
            }

            .scheduler-hero__meta,
            .scheduler-actions,
            .scheduler-modal__footer,
            .scheduler-modal__header {
                align-items: stretch;
                flex-direction: column;
            }

            .scheduler-stats {
                grid-template-columns: 1fr;
            }

            .scheduler-display-toggle {
                width: 100%;
            }

            .scheduler-display-toggle .scheduler-btn {
                flex: 1;
                min-width: 0;
            }

            #calendar { min-height: 620px; }
        }
    </style>

    <section class="scheduler-hero">
        <div>
            <div class="scheduler-pill" id="schedulerMonthPill">Calendar Scheduler</div>
            <h1>Plan meetings and reminders with a cleaner, more dynamic calendar.</h1>
            <p>Click a date to create a new event, tap an item to inspect details, then edit or delete it without reloading the page.</p>
        </div>
        <div class="scheduler-hero__meta">
            <div class="scheduler-display-toggle" role="tablist" aria-label="Calendar display mode">
                <button type="button" class="scheduler-btn is-active" id="singleCalendarBtn">1 Calendar</button>
                <button type="button" class="scheduler-btn" id="dualCalendarBtn">2 Calendars</button>
            </div>
            <div class="scheduler-actions">
                <button type="button" class="scheduler-btn scheduler-btn--primary" id="newEventBtn">New Event</button>
                <button type="button" class="scheduler-btn scheduler-btn--ghost" id="jumpTodayBtn">Today</button>
            </div>
        </div>
    </section>

    <section class="scheduler-stats" aria-live="polite">
        <div class="scheduler-stat">
            <div class="scheduler-stat__label">Total Events</div>
            <div class="scheduler-stat__value" id="statTotal">0</div>
            <div class="scheduler-stat__note">All scheduled items loaded from the database.</div>
        </div>
        <div class="scheduler-stat">
            <div class="scheduler-stat__label">Today</div>
            <div class="scheduler-stat__value" id="statToday">0</div>
            <div class="scheduler-stat__note">Events happening on the current date.</div>
        </div>
        <div class="scheduler-stat">
            <div class="scheduler-stat__label">Next 7 Days</div>
            <div class="scheduler-stat__value" id="statWeek">0</div>
            <div class="scheduler-stat__note">Upcoming items in the next seven days.</div>
        </div>
        <div class="scheduler-stat">
            <div class="scheduler-stat__label">With Link</div>
            <div class="scheduler-stat__value" id="statLink">0</div>
            <div class="scheduler-stat__note">Events with a Zoom or meeting URL.</div>
        </div>
    </section>

    <section class="scheduler-grid" style="margin-top:18px;">
        <div class="scheduler-panel">
            <div class="scheduler-panel__header">
                <div>
                    <h2>Calendar</h2>
                    <p id="calendarHeaderMonth">Browse by month, week, or list view.</p>
                </div>
                <div class="scheduler-toolbar">
                    <button type="button" class="scheduler-btn scheduler-btn--soft" id="prevBtn">Prev</button>
                    <button type="button" class="scheduler-btn scheduler-btn--soft" id="nextBtn">Next</button>
                    <button type="button" class="scheduler-btn scheduler-btn--soft" id="monthBtn">Month</button>
                    <button type="button" class="scheduler-btn scheduler-btn--soft" id="weekBtn">Week</button>
                    <button type="button" class="scheduler-btn scheduler-btn--soft" id="listBtn">List</button>
                </div>
            </div>
            <div class="scheduler-calendar-grid" id="calendarGrid">
                <div class="scheduler-calendar-panel">
                    <div class="scheduler-calendar-panel__label">
                        <strong id="primaryCalendarLabel">Current Month</strong>
                        <span id="primaryCalendarHint">Main view</span>
                    </div>
                    <div id="calendarPrimary"></div>
                </div>

                <div class="scheduler-calendar-panel" id="secondaryCalendarPanel" hidden>
                    <div class="scheduler-calendar-panel__label">
                        <strong id="secondaryCalendarLabel">Next Month</strong>
                        <span id="secondaryCalendarHint">Secondary view</span>
                    </div>
                    <div id="calendarSecondary"></div>
                </div>
            </div>
        </div>

        <aside class="scheduler-stack">
            <div class="scheduler-panel">
                <div class="scheduler-panel__header">
                    <div>
                        <h3>Upcoming Events</h3>
                        <p>Search the list and jump straight to an item.</p>
                    </div>
                </div>
                <input type="search" id="eventSearch" class="scheduler-search" placeholder="Search title, date, email, or link">
                <div class="scheduler-upcoming" id="upcomingList"></div>
            </div>

            <div class="scheduler-panel">
                <div class="scheduler-panel__header">
                    <div>
                        <h3>Quick Tips</h3>
                        <p>Small actions that make scheduling faster.</p>
                    </div>
                </div>
                <div class="scheduler-empty">
                    Use the date picker or click a day cell to prefill the form instantly. The list updates live after add, edit, or delete.
                </div>
            </div>
        </aside>
    </section>

    <div class="scheduler-toast-stack" id="schedulerToastStack" aria-live="polite" aria-atomic="true"></div>

    <div class="scheduler-confirm" id="schedulerConfirm" aria-hidden="true" data-open="false">
        <div class="scheduler-confirm__backdrop" data-close-confirm></div>
        <div class="scheduler-confirm__dialog" role="dialog" aria-modal="true" aria-labelledby="schedulerConfirmTitle">
            <h3 class="scheduler-confirm__title" id="schedulerConfirmTitle">Delete Event</h3>
            <div class="scheduler-confirm__message" id="schedulerConfirmMessage">Are you sure you want to delete this event?</div>
            <div class="scheduler-confirm__actions">
                <button type="button" class="scheduler-btn scheduler-btn--soft" data-close-confirm>Cancel</button>
                <button type="button" class="scheduler-btn scheduler-btn--danger" id="schedulerConfirmOk">Delete</button>
            </div>
        </div>
    </div>

    <div class="scheduler-modal" id="eventModal" aria-hidden="true" data-open="false">
        <div class="scheduler-modal__backdrop" data-close-modal></div>
        <div class="scheduler-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="eventModalTitle">
            <div class="scheduler-modal__header">
                <div>
                    <div class="scheduler-pill" style="background:#eef2ff;color:#3730a3;border-color:rgba(37,99,235,0.12);">Event Manager</div>
                    <h2 id="eventModalTitle">Add Event</h2>
                </div>
                <button type="button" class="scheduler-modal__close" data-close-modal aria-label="Close">&times;</button>
            </div>

            <div class="scheduler-modal__body">
                <form id="eventForm" class="scheduler-form">
                    <input type="hidden" id="eventId" name="id">
                    <div class="scheduler-field">
                        <label for="event_date">Date</label>
                        <input type="date" id="event_date" name="event_date" class="scheduler-input" required>
                    </div>
                    <div class="scheduler-field">
                        <label for="remarks">Remarks</label>
                        <textarea id="remarks" name="remarks" class="scheduler-textarea" required placeholder="Describe the meeting, reminder, or activity"></textarea>
                    </div>
                    <div class="scheduler-field">
                        <label for="zoom_link">Zoom / Meeting Link</label>
                        <input type="url" id="zoom_link" name="zoom_link" class="scheduler-input" placeholder="https://...">
                    </div>
                    <div class="scheduler-field">
                        <label for="password">Password</label>
                        <input type="text" id="password" name="password" class="scheduler-input" required>
                    </div>
                    <div class="scheduler-field">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="scheduler-input" required>
                    </div>
                    <div class="scheduler-status" id="formStatus"></div>
                </form>

                <div class="scheduler-details" id="eventDetails">
                    <div class="scheduler-detail">
                        <span class="scheduler-detail__label">Date</span>
                        <div class="scheduler-detail__value" id="detailDate"></div>
                    </div>
                    <div class="scheduler-detail">
                        <span class="scheduler-detail__label">Remarks</span>
                        <div class="scheduler-detail__value" id="detailRemarks"></div>
                    </div>
                    <div class="scheduler-detail">
                        <span class="scheduler-detail__label">Meeting Link</span>
                        <div class="scheduler-detail__value" id="detailLink"></div>
                        <div class="scheduler-detail__actions">
                            <button type="button" class="scheduler-btn scheduler-btn--soft" id="copyLinkBtn">Copy Link</button>
                            <button type="button" class="scheduler-btn scheduler-btn--soft" id="openLinkBtn">Open Link</button>
                        </div>
                    </div>
                    <div class="scheduler-detail">
                        <span class="scheduler-detail__label">Meeting ID</span>
                        <div class="scheduler-detail__value" id="detailMeetingId"></div>
                    </div>
                    <div class="scheduler-detail">
                        <span class="scheduler-detail__label">Password</span>
                        <div class="scheduler-detail__value scheduler-detail__value--secret" id="detailPassword"></div>
                        <div class="scheduler-detail__actions">
                            <button type="button" class="scheduler-btn scheduler-btn--soft" id="togglePasswordBtn">Show Password</button>
                            <button type="button" class="scheduler-btn scheduler-btn--soft" id="copyPasswordBtn">Copy Password</button>
                        </div>
                    </div>
                    <div class="scheduler-detail">
                        <span class="scheduler-detail__label">Requested Division</span>
                        <div class="scheduler-detail__value" id="detailDivision"></div>
                    </div>
                    <div class="scheduler-detail">
                        <span class="scheduler-detail__label">Email</span>
                        <div class="scheduler-detail__value" id="detailEmail"></div>
                    </div>
                </div>
            </div>

            <div class="scheduler-modal__footer" style="margin-top:18px;">
                <div class="scheduler-actions">
                    <button type="button" class="scheduler-btn scheduler-btn--danger" id="deleteEventBtn">Delete</button>
                    <button type="button" class="scheduler-btn scheduler-btn--soft" id="editEventBtn">Edit</button>
                </div>
                <div class="scheduler-footer-spacer"></div>
                <div class="scheduler-actions">
                    <button type="button" class="scheduler-btn scheduler-btn--soft" id="cancelEventBtn">Cancel</button>
                    <button type="submit" form="eventForm" class="scheduler-btn scheduler-btn--primary" id="saveEventBtn">Save Event</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var primaryCalendarEl = document.getElementById('calendarPrimary');
        var secondaryCalendarEl = document.getElementById('calendarSecondary');
        var secondaryCalendarPanel = document.getElementById('secondaryCalendarPanel');
        var calendarGrid = document.getElementById('calendarGrid');
        var calendarHeaderMonth = document.getElementById('calendarHeaderMonth');
        var primaryCalendarLabel = document.getElementById('primaryCalendarLabel');
        var secondaryCalendarLabel = document.getElementById('secondaryCalendarLabel');
        var primaryCalendarHint = document.getElementById('primaryCalendarHint');
        var secondaryCalendarHint = document.getElementById('secondaryCalendarHint');
        var monthPill = document.getElementById('schedulerMonthPill');
        var singleCalendarBtn = document.getElementById('singleCalendarBtn');
        var dualCalendarBtn = document.getElementById('dualCalendarBtn');
        var modal = document.getElementById('eventModal');
        var modalTitle = document.getElementById('eventModalTitle');
        var eventForm = document.getElementById('eventForm');
        var eventDetails = document.getElementById('eventDetails');
        var saveBtn = document.getElementById('saveEventBtn');
        var editBtn = document.getElementById('editEventBtn');
        var deleteBtn = document.getElementById('deleteEventBtn');
        var cancelBtn = document.getElementById('cancelEventBtn');
        var togglePasswordBtn = document.getElementById('togglePasswordBtn');
        var copyLinkBtn = document.getElementById('copyLinkBtn');
        var openLinkBtn = document.getElementById('openLinkBtn');
        var copyPasswordBtn = document.getElementById('copyPasswordBtn');
        var detailMeetingId = document.getElementById('detailMeetingId');
        var detailDivision = document.getElementById('detailDivision');
        var searchInput = document.getElementById('eventSearch');
        var upcomingList = document.getElementById('upcomingList');
        var statTotal = document.getElementById('statTotal');
        var statToday = document.getElementById('statToday');
        var statWeek = document.getElementById('statWeek');
        var statLink = document.getElementById('statLink');
        var newEventBtn = document.getElementById('newEventBtn');
        var jumpTodayBtn = document.getElementById('jumpTodayBtn');
        var prevBtn = document.getElementById('prevBtn');
        var nextBtn = document.getElementById('nextBtn');
        var monthBtn = document.getElementById('monthBtn');
        var weekBtn = document.getElementById('weekBtn');
        var listBtn = document.getElementById('listBtn');
        var toastStack = document.getElementById('schedulerToastStack');
        var confirmModal = document.getElementById('schedulerConfirm');
        var confirmTitle = document.getElementById('schedulerConfirmTitle');
        var confirmMessage = document.getElementById('schedulerConfirmMessage');
        var confirmOk = document.getElementById('schedulerConfirmOk');
        var state = {
            displayMode: 'single',
            activeView: 'dayGridMonth',
            baseDate: new Date(),
            events: [],
            filter: '',
            mode: 'create',
            event: null,
            passwordVisible: false
        };
        var calendars = {
            primary: null,
            secondary: null
        };
        var confirmResolve = null;

        var dateFormatter = new Intl.DateTimeFormat('en-US', {
            weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
        });
        var monthFormatter = new Intl.DateTimeFormat('en-US', {
            month: 'long', year: 'numeric'
        });
        var shortDateFormatter = new Intl.DateTimeFormat('en-US', {
            month: 'short', day: 'numeric', year: 'numeric'
        });

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"]+/g, function (match) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[match] || match;
            });
        }

        function formatDateInput(value) {
            return value ? String(value).slice(0, 10) : '';
        }

        function formatLocalDate(value) {
            if (!value) return null;
            if (value instanceof Date) {
                return new Date(value.getFullYear(), value.getMonth(), value.getDate());
            }
            return new Date(String(value) + 'T00:00:00');
        }

        function localDateString(value) {
            var date = value instanceof Date ? value : new Date(value);
            return [
                date.getFullYear(),
                String(date.getMonth() + 1).padStart(2, '0'),
                String(date.getDate()).padStart(2, '0')
            ].join('-');
        }

        function addMonths(dateValue, months) {
            var date = new Date(dateValue.getFullYear(), dateValue.getMonth() + months, 1);
            return date;
        }

        function monthLabel(dateValue) {
            return monthFormatter.format(dateValue);
        }

        function setBodyLock(open) {
            document.body.classList.toggle('scheduler-modal-open', open);
        }

        function showToast(message, tone, title) {
            var toast = document.createElement('div');
            toast.className = 'scheduler-toast';
            toast.dataset.tone = tone || 'info';
            toast.innerHTML = '<span class="scheduler-toast__dot" aria-hidden="true"></span>' +
                '<div class="scheduler-toast__body"><div class="scheduler-toast__title">' + escapeHtml(title || (tone === 'success' ? 'Success' : tone === 'danger' ? 'Error' : 'Notice')) + '</div>' +
                '<div class="scheduler-toast__message">' + escapeHtml(message) + '</div></div>' +
                '<button type="button" class="scheduler-toast__close" aria-label="Close toast">&times;</button>';

            var closeToast = function () {
                toast.classList.remove('is-visible');
                window.setTimeout(function () {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 180);
            };

            toast.querySelector('.scheduler-toast__close').addEventListener('click', closeToast);
            toastStack.appendChild(toast);
            window.setTimeout(function () { toast.classList.add('is-visible'); }, 10);
            window.setTimeout(closeToast, 3600);
        }

        function openConfirm(options) {
            confirmTitle.textContent = options.title || 'Confirm Action';
            confirmMessage.textContent = options.message || 'Are you sure?';
            confirmOk.textContent = options.confirmText || 'Confirm';
            confirmOk.className = 'scheduler-btn scheduler-btn--danger';
            confirmModal.dataset.open = 'true';
            confirmModal.setAttribute('aria-hidden', 'false');
            setBodyLock(true);

            return new Promise(function (resolve) {
                confirmResolve = resolve;
            });
        }

        function closeConfirm(result) {
            confirmModal.dataset.open = 'false';
            confirmModal.setAttribute('aria-hidden', 'true');
            setBodyLock(modal.dataset.open === 'true');
            if (confirmResolve) {
                confirmResolve(result);
                confirmResolve = null;
            }
        }

        function openModal() {
            modal.dataset.open = 'true';
            modal.setAttribute('aria-hidden', 'false');
            setBodyLock(true);
        }

        function closeModal() {
            modal.dataset.open = 'false';
            modal.setAttribute('aria-hidden', 'true');
            setBodyLock(confirmModal.dataset.open === 'true');
        }

        function getFormData() {
            return {
                id: document.getElementById('eventId').value.trim(),
                event_date: document.getElementById('event_date').value.trim(),
                remarks: document.getElementById('remarks').value.trim(),
                zoom_link: document.getElementById('zoom_link').value.trim(),
                password: document.getElementById('password').value.trim(),
                email: document.getElementById('email').value.trim()
            };
        }

        function fillForm(eventData) {
            document.getElementById('eventId').value = eventData && eventData.id ? eventData.id : '';
            document.getElementById('event_date').value = eventData && eventData.start ? formatDateInput(eventData.start) : '';
            document.getElementById('remarks').value = eventData && eventData.remarks ? eventData.remarks : '';
            document.getElementById('zoom_link').value = eventData && eventData.zoom_link ? eventData.zoom_link : '';
            document.getElementById('password').value = eventData && eventData.password ? eventData.password : '';
            document.getElementById('email').value = eventData && eventData.email ? eventData.email : '';
        }

        function renderDetails(eventData) {
            var link = eventData && eventData.zoom_link ? eventData.zoom_link : 'No meeting link saved';
            var email = eventData && eventData.email ? eventData.email : 'No email saved';
            var password = eventData && eventData.password ? eventData.password : 'No password saved';

            document.getElementById('detailDate').textContent = eventData && eventData.start ? dateFormatter.format(formatLocalDate(eventData.start)) : '';
            document.getElementById('detailRemarks').textContent = eventData && eventData.remarks ? eventData.remarks : '';
            document.getElementById('detailLink').innerHTML = eventData && eventData.zoom_link
                ? '<a href="' + escapeHtml(eventData.zoom_link) + '" target="_blank" rel="noopener">' + escapeHtml(eventData.zoom_link) + '</a>'
                : '<span style="color:#66758a;">' + escapeHtml(link) + '</span>';
            detailMeetingId.textContent = eventData && eventData.meeting_id ? eventData.meeting_id : 'No meeting ID saved';
            document.getElementById('detailPassword').textContent = eventData && eventData.password ? '••••••••' : password;
            document.getElementById('detailPassword').dataset.value = eventData && eventData.password ? eventData.password : '';
            detailDivision.textContent = eventData && (eventData.office || eventData.divSecUnit)
                ? [eventData.office || '', eventData.divSecUnit || ''].filter(Boolean).join(' - ')
                : 'No division saved';
            document.getElementById('detailEmail').innerHTML = eventData && eventData.email
                ? '<a href="mailto:' + escapeHtml(eventData.email) + '">' + escapeHtml(eventData.email) + '</a>'
                : '<span style="color:#66758a;">' + escapeHtml(email) + '</span>';

            copyLinkBtn.disabled = !eventData || !eventData.zoom_link;
            openLinkBtn.disabled = !eventData || !eventData.zoom_link;
            copyPasswordBtn.disabled = !eventData || !eventData.password;
            togglePasswordBtn.disabled = !eventData || !eventData.password;
            togglePasswordBtn.textContent = 'Show Password';
            state.passwordVisible = false;
        }

        function renderModal() {
            var isView = state.mode === 'view';
            var isCreate = state.mode === 'create';
            var isEdit = state.mode === 'edit';

            eventDetails.style.display = isView ? 'grid' : 'none';
            eventForm.style.display = isView ? 'none' : 'grid';
            editBtn.style.display = isView ? 'inline-flex' : 'none';
            deleteBtn.style.display = isView ? 'inline-flex' : 'none';
            saveBtn.style.display = (isCreate || isEdit) ? 'inline-flex' : 'none';
            cancelBtn.textContent = isView ? 'Close' : 'Cancel';
            modalTitle.textContent = isCreate ? 'Add Event' : (isEdit ? 'Edit Event' : 'Event Details');
            saveBtn.textContent = isCreate ? 'Save Event' : 'Save Changes';
        }

        function openCreateModal(dateValue) {
            state.mode = 'create';
            state.event = null;
            fillForm({ start: dateValue || localDateString(new Date()) });
            renderModal();
            openModal();
        }

        function openViewModal(eventData) {
            state.mode = 'view';
            state.event = eventData;
            renderDetails(eventData);
            fillForm(eventData);
            renderModal();
            openModal();
        }

        function openEditModal() {
            if (!state.event) return;
            state.mode = 'edit';
            fillForm(state.event);
            renderModal();
            openModal();
        }

        function closeOrReturn() {
            if (state.mode === 'edit' && state.event) {
                state.mode = 'view';
                renderModal();
                openModal();
                renderDetails(state.event);
                return;
            }
            closeModal();
        }

        function copyToClipboard(value) {
            if (!value) return Promise.resolve(false);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(value).then(function () { return true; }).catch(function () { return fallbackCopy(value); });
            }
            return Promise.resolve(fallbackCopy(value));
        }

        function fallbackCopy(value) {
            var textarea = document.createElement('textarea');
            textarea.value = value;
            textarea.setAttribute('readonly', 'true');
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            var success = false;
            try {
                success = document.execCommand('copy');
            } catch (error) {
                success = false;
            }
            document.body.removeChild(textarea);
            return success;
        }

        function mapEvent(item) {
            return {
                id: item.id,
                title: item.title,
                start: item.start,
                remarks: item.title,
                zoom_link: item.extendedProps && item.extendedProps.zoom_link ? item.extendedProps.zoom_link : '',
                meeting_id: item.extendedProps && item.extendedProps.meeting_id ? item.extendedProps.meeting_id : '',
                password: item.extendedProps && item.extendedProps.password ? item.extendedProps.password : '',
                email: item.extendedProps && item.extendedProps.email ? item.extendedProps.email : '',
                office: item.extendedProps && item.extendedProps.office ? item.extendedProps.office : '',
                divSecUnit: item.extendedProps && item.extendedProps.divSecUnit ? item.extendedProps.divSecUnit : '',
                allDay: true
            };
        }

        function filterEvents(events) {
            var term = (state.filter || '').trim().toLowerCase();
            if (!term) return events.slice();

            return events.filter(function (item) {
                var haystack = [item.title, item.start, item.email, item.zoom_link, item.remarks]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();
                return haystack.indexOf(term) !== -1;
            });
        }

        function renderUpcoming(events) {
            var filtered = filterEvents(events)
                .slice()
                .sort(function (a, b) { return new Date(a.start) - new Date(b.start); })
                .slice(0, 8);

            if (!filtered.length) {
                upcomingList.innerHTML = '<div class="scheduler-empty">No events match this search.</div>';
                return;
            }

            upcomingList.innerHTML = filtered.map(function (item) {
                var linkBadge = item.zoom_link ? '<span style="color:#2563eb;font-weight:700;">Link ready</span>' : '<span style="color:#64748b;">No link</span>';
                return '<div class="scheduler-upcoming__item" data-event-id="' + escapeHtml(item.id) + '">' +
                    '<div class="scheduler-upcoming__date">' + escapeHtml(shortDateFormatter.format(formatLocalDate(item.start))) + '</div>' +
                    '<div class="scheduler-upcoming__title">' + escapeHtml(item.title || 'Untitled event') + '</div>' +
                    '<div class="scheduler-upcoming__meta">' + escapeHtml([item.office || '', item.divSecUnit || ''].filter(Boolean).join(' - ') || 'No division saved') + '</div>' +
                    '<div class="scheduler-upcoming__meta">' + escapeHtml(item.email || '') + '</div>' +
                    '<div class="scheduler-upcoming__meta">' + linkBadge + '</div>' +
                '</div>';
            }).join('');

            Array.prototype.forEach.call(upcomingList.querySelectorAll('[data-event-id]'), function (node) {
                node.addEventListener('click', function () {
                    var eventId = node.getAttribute('data-event-id');
                    var eventObj = state.events.find(function (item) { return String(item.id) === String(eventId); });
                    if (eventObj) {
                        goToDate(eventObj.start);
                        openViewModal(eventObj);
                    }
                });
            });
        }

        function updateStats(events) {
            var now = new Date();
            var today = localDateString(now);
            var weekEnd = new Date(now);
            weekEnd.setDate(weekEnd.getDate() + 7);
            var weekEndISO = localDateString(weekEnd);

            statTotal.textContent = String(events.length);
            statToday.textContent = String(events.filter(function (item) { return item.start === today; }).length);
            statWeek.textContent = String(events.filter(function (item) { return item.start >= today && item.start <= weekEndISO; }).length);
            statLink.textContent = String(events.filter(function (item) { return item.zoom_link; }).length);
        }

        function updateMonthLabels() {
            var primaryLabel = monthLabel(state.baseDate);
            var secondaryLabel = monthLabel(addMonths(state.baseDate, 1));
            monthPill.textContent = state.displayMode === 'dual' ? primaryLabel + ' + ' + secondaryLabel : primaryLabel;
            calendarHeaderMonth.textContent = state.displayMode === 'dual'
                ? 'Showing ' + primaryLabel + ' and ' + secondaryLabel
                : 'Showing ' + primaryLabel;
            primaryCalendarLabel.textContent = primaryLabel;
            primaryCalendarHint.textContent = 'Main view';
            secondaryCalendarLabel.textContent = secondaryLabel;
            secondaryCalendarHint.textContent = 'Next month';
        }

        function buildCalendarOptions(type) {
            return {
                initialView: state.activeView,
                initialDate: type === 'primary' ? state.baseDate : addMonths(state.baseDate, 1),
                height: 'auto',
                nowIndicator: true,
                selectable: true,
                dayMaxEvents: true,
                expandRows: true,
                headerToolbar: false,
                events: function (fetchInfo, successCallback) {
                    successCallback(state.events.slice());
                },
                eventClassNames: function (arg) {
                    return [arg.event.extendedProps.zoom_link ? 'fc-event--linked' : 'fc-event--plain'];
                },
                eventContent: function (arg) {
                    var container = document.createElement('div');
                    var title = document.createElement('div');
                    title.textContent = arg.event.title;
                    title.style.fontWeight = '700';
                    title.style.lineHeight = '1.1';

                    var meta = document.createElement('div');
                    meta.textContent = arg.event.extendedProps.zoom_link ? 'Meeting link ready' : 'Reminder';
                    meta.style.fontSize = '0.78rem';
                    meta.style.opacity = '0.9';

                    container.appendChild(title);
                    container.appendChild(meta);
                    return { domNodes: [container] };
                },
                eventClick: function (info) {
                    openViewModal({
                        id: info.event.id,
                        title: info.event.title,
                        start: info.event.startStr,
                        remarks: info.event.title,
                        zoom_link: info.event.extendedProps.zoom_link || '',
                        meeting_id: info.event.extendedProps.meeting_id || '',
                        password: info.event.extendedProps.password || '',
                        email: info.event.extendedProps.email || '',
                        office: info.event.extendedProps.office || '',
                        divSecUnit: info.event.extendedProps.divSecUnit || ''
                    });
                },
                dateClick: function (info) {
                    openCreateModal(info.dateStr);
                },
                datesSet: function (arg) {
                    state.activeView = arg.view.type;
                    updateMonthLabels();
                }
            };
        }

        function renderCalendars() {
            if (calendars.primary) {
                calendars.primary.destroy();
                calendars.primary = null;
            }

            if (calendars.secondary) {
                calendars.secondary.destroy();
                calendars.secondary = null;
            }

            calendars.primary = new FullCalendar.Calendar(primaryCalendarEl, buildCalendarOptions('primary'));
            calendars.primary.render();

            if (state.displayMode === 'dual') {
                secondaryCalendarPanel.hidden = false;
                calendarGrid.classList.add('is-dual');
                calendars.secondary = new FullCalendar.Calendar(secondaryCalendarEl, buildCalendarOptions('secondary'));
                calendars.secondary.render();
            } else {
                secondaryCalendarPanel.hidden = true;
                calendarGrid.classList.remove('is-dual');
            }

            updateMonthLabels();
        }

        function applyDisplayMode(mode) {
            state.displayMode = mode;
            singleCalendarBtn.classList.toggle('is-active', mode === 'single');
            dualCalendarBtn.classList.toggle('is-active', mode === 'dual');
            renderCalendars();
            syncCalendarDates();
            syncCalendarView();
            syncCalendarEvents();
            showToast(mode === 'dual' ? 'Two calendars are now visible.' : 'Single calendar view is active.', 'info', 'Display Mode');
        }

        function syncCalendarView() {
            if (calendars.primary) calendars.primary.changeView(state.activeView);
            if (calendars.secondary) calendars.secondary.changeView(state.activeView);
        }

        function syncCalendarDates() {
            if (calendars.primary) calendars.primary.gotoDate(state.baseDate);
            if (calendars.secondary) calendars.secondary.gotoDate(addMonths(state.baseDate, 1));
            updateMonthLabels();
        }

        function syncCalendarEvents() {
            if (calendars.primary) calendars.primary.refetchEvents();
            if (calendars.secondary) calendars.secondary.refetchEvents();
        }

        function goToDate(value) {
            state.baseDate = formatLocalDate(value) || new Date();
            syncCalendarDates();
        }

        function changeMonth(delta) {
            state.baseDate = addMonths(state.baseDate, delta);
            syncCalendarDates();
        }

        function changeView(viewName) {
            state.activeView = viewName;
            syncCalendarView();
            updateMonthLabels();
        }

        async function loadEvents() {
            try {
                var response = await fetch('fetch_events.php', { headers: { 'Accept': 'application/json' } });
                var data = await response.json();

                if (!response.ok) {
                    throw new Error((data && data.error) || 'Unable to load events.');
                }

                state.events = data.map(mapEvent);
                updateStats(state.events);
                renderUpcoming(state.events);
                syncCalendarEvents();
            } catch (error) {
                showToast(error.message || 'Unable to load events.', 'danger', 'Calendar');
            }
        }

        async function submitForm() {
            var payload = getFormData();

            if (!payload.event_date || !payload.remarks || !payload.password || !payload.email) {
                showToast('Please complete all required fields.', 'warning', 'Missing Data');
                return;
            }

            showToast('Saving event...', 'info', 'Calendar');

            var response = await fetch(payload.id ? 'update_event.php' : 'add_event.php', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(eventForm)
            });

            var result = await response.json();

            if (!response.ok || !result.success) {
                showToast(result.message || 'Unable to save the event.', 'danger', 'Calendar');
                return;
            }

            showToast(result.message || 'Event saved successfully.', 'success', 'Calendar');
            await loadEvents();
            state.mode = 'view';
            closeModal();
        }

        async function deleteCurrentEvent() {
            if (!state.event || !state.event.id) return;

            var confirmed = await openConfirm({
                title: 'Delete Event',
                message: 'This will permanently remove the selected event.',
                confirmText: 'Delete'
            });

            if (!confirmed) return;

            var response = await fetch('delete_event.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id: state.event.id })
            });

            var result = await response.json();

            if (!response.ok || !result.success) {
                showToast(result.message || 'Unable to delete the event.', 'danger', 'Calendar');
                return;
            }

            showToast(result.message || 'Event deleted successfully.', 'success', 'Calendar');
            state.event = null;
            closeModal();
            await loadEvents();
        }

        singleCalendarBtn.addEventListener('click', function () {
            applyDisplayMode('single');
        });

        dualCalendarBtn.addEventListener('click', function () {
            applyDisplayMode('dual');
        });

        newEventBtn.addEventListener('click', function () {
            openCreateModal(localDateString(new Date()));
        });

        jumpTodayBtn.addEventListener('click', function () {
            goToDate(new Date());
        });

        prevBtn.addEventListener('click', function () { changeMonth(-1); });
        nextBtn.addEventListener('click', function () { changeMonth(1); });
        monthBtn.addEventListener('click', function () { changeView('dayGridMonth'); });
        weekBtn.addEventListener('click', function () { changeView('timeGridWeek'); });
        listBtn.addEventListener('click', function () { changeView('listMonth'); });

        searchInput.addEventListener('input', function () {
            state.filter = searchInput.value;
            renderUpcoming(state.events);
        });

        modal.addEventListener('click', function (event) {
            if (event.target && event.target.hasAttribute('data-close-modal')) {
                closeOrReturn();
            }
        });

        confirmModal.addEventListener('click', function (event) {
            if (event.target && event.target.hasAttribute('data-close-confirm')) {
                closeConfirm(false);
            }
        });

        confirmOk.addEventListener('click', function () {
            closeConfirm(true);
        });

        eventForm.addEventListener('submit', function (event) {
            event.preventDefault();
            submitForm().catch(function (error) {
                console.error(error);
                showToast('Unexpected error while saving the event.', 'danger', 'Calendar');
            });
        });

        editBtn.addEventListener('click', openEditModal);

        deleteBtn.addEventListener('click', function () {
            deleteCurrentEvent().catch(function (error) {
                console.error(error);
                showToast('Unexpected error while deleting the event.', 'danger', 'Calendar');
            });
        });

        cancelBtn.addEventListener('click', closeOrReturn);

        togglePasswordBtn.addEventListener('click', function () {
            var detailPassword = document.getElementById('detailPassword');
            var value = detailPassword.dataset.value || '';
            if (!value) return;

            state.passwordVisible = !state.passwordVisible;
            detailPassword.textContent = state.passwordVisible ? value : '••••••••';
            togglePasswordBtn.textContent = state.passwordVisible ? 'Hide Password' : 'Show Password';
        });

        copyLinkBtn.addEventListener('click', function () {
            var value = state.event && state.event.zoom_link ? state.event.zoom_link : '';
            if (!value) {
                showToast('No link is saved for this event.', 'warning', 'Copy Link');
                return;
            }
            copyToClipboard(value).then(function (success) {
                showToast(success ? 'Link copied to clipboard.' : 'Unable to copy the link.', success ? 'success' : 'warning', 'Copy Link');
            });
        });

        openLinkBtn.addEventListener('click', function () {
            if (state.event && state.event.zoom_link) {
                window.open(state.event.zoom_link, '_blank', 'noopener');
            }
        });

        copyPasswordBtn.addEventListener('click', function () {
            var value = state.event && state.event.password ? state.event.password : '';
            if (!value) {
                showToast('No password is saved for this event.', 'warning', 'Copy Password');
                return;
            }
            copyToClipboard(value).then(function (success) {
                showToast(success ? 'Password copied to clipboard.' : 'Unable to copy the password.', success ? 'success' : 'warning', 'Copy Password');
            });
        });

        function syncCalendarViewAndDates() {
            syncCalendarView();
            syncCalendarDates();
        }

        state.displayMode = 'single';
        renderCalendars();
        updateStats(state.events);
        updateMonthLabels();
        loadEvents();
        renderModal();
        syncCalendarViewAndDates();

        window.addEventListener('resize', function () {
            if (calendars.primary) calendars.primary.updateSize();
            if (calendars.secondary) calendars.secondary.updateSize();
        });
    });
    </script>
</div>
