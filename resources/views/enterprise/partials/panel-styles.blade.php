@once
@push('styles')
<style>
    .enterprise-panel { padding: 0; margin: 0; max-width: none; }
    .enterprise-mailbox { display: flex; min-height: calc(100vh - 56px); background: #fff; }
    .mailbox-sidebar {
        position: fixed;
        left: 0;
        top: 56px;
        bottom: 0;
        width: var(--sidebar-width, 250px);
        flex-shrink: 0;
        background: white;
        border-right: 1px solid var(--border-color, #e2e8f0);
        box-shadow: 2px 0 8px rgba(0,0,0,0.06);
        z-index: 100;
        overflow-y: auto;
        padding: 0;
    }
    .mailbox-sidebar-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-color, #e2e8f0);
        font-weight: 600;
        font-size: 1rem;
        color: var(--dark-text, #1e293b);
    }
    .mailbox-sidebar .nav-link {
        color: #475569;
        font-weight: 500;
        padding: 0.6rem 1.25rem;
        border-radius: 0;
        border-left: 3px solid transparent;
    }
    .mailbox-sidebar .nav-link:hover { background: #f1f5f9; color: #1e293b; }
    .mailbox-sidebar .nav-link.active {
        background: rgba(99, 102, 241, 0.08);
        color: #6366f1;
        border-left-color: #6366f1;
    }
    .mailbox-sidebar .nav-link i { width: 20px; margin-right: 0.5rem; }
    .mailbox-main {
        flex: 1;
        margin-left: var(--sidebar-width, 250px);
        display: flex;
        flex-direction: column;
        min-width: 0;
        min-height: calc(100vh - 56px);
    }
    .mailbox-toolbar {
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .mailbox-list {
        flex: 1;
        overflow-y: auto;
        border-bottom: 1px solid #e2e8f0;
    }
    .enterprise-mailbox .mailbox-list.padded { padding: 1rem 1.25rem; border-bottom: none; }
    .mail-item {
        display: flex;
        align-items: center;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.15s;
    }
    .mail-item:hover { background: #f8fafc; }
    .order-item {
        align-items: center;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.15s;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .order-item:hover { background: #f8fafc; }
    .order-item .order-num { font-weight: 600; color: #1e293b; }
    .order-item .order-template { color: #475569; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .order-item .order-total { font-weight: 600; color: #059669; }
    .order-item .order-status { font-size: 0.75rem; }
    .order-item .order-date { font-size: 0.8rem; color: #94a3b8; }
    .order-item .order-actions { display: flex; gap: 0.35rem; margin-left: auto; }
    .mail-reader { padding: 1.5rem 1.25rem; background: #fff; overflow-y: auto; }
    .mailbox-empty {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 0.95rem;
        padding: 2rem;
    }
    .addr-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 0.75rem;
        transition: box-shadow 0.2s;
    }
    .addr-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .addr-card .addr-name { font-weight: 600; color: #1e293b; margin-bottom: 0.25rem; }
    .addr-card .addr-meta { font-size: 0.875rem; color: #64748b; }
    .addr-card .addr-actions { margin-top: 0.5rem; }
    .sched-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 0.75rem;
    }
    .sched-card .sched-meta { font-size: 0.875rem; color: #64748b; }
    .enterprise-stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.15rem;
        height: 100%;
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .enterprise-stat-card:hover {
        border-color: rgba(99, 102, 241, 0.35);
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.08);
    }
    .enterprise-stat-card .stat-value { font-size: 1.5rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
    .enterprise-stat-card .stat-label { font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem; }
    .enterprise-stat-card .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
</style>
@endpush
@endonce
