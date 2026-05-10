<style>
    .erp-card {
        border: 1px solid #e5eaf2;
        background: rgba(255, 255, 255, 0.96);
        border-radius: 1.1rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }

    .erp-soft {
        border: 1px solid #e5eaf2;
        background: #f8fafc;
        border-radius: 1rem;
    }

    .erp-btn {
        display: inline-flex;
        min-height: 2.75rem;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        border-radius: 0.85rem;
        padding: 0 1rem;
        font-weight: 900;
        transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease;
        white-space: nowrap;
    }

    .erp-btn:hover {
        transform: translateY(-1px);
    }

    .erp-btn-dark {
        background: #0f172a;
        color: white;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.16);
    }

    .erp-btn-blue {
        background: #2563eb;
        color: white;
        box-shadow: 0 14px 28px rgba(37, 99, 235, 0.18);
    }

    .erp-btn-green {
        background: #16a34a;
        color: white;
        box-shadow: 0 14px 28px rgba(22, 163, 74, 0.18);
    }

    .erp-btn-orange {
        background: linear-gradient(135deg, #f97316, #dc2626);
        color: white;
        box-shadow: 0 14px 28px rgba(249, 115, 22, 0.2);
    }

    .erp-btn-outline {
        border: 1px solid #dbe3ef;
        background: white;
        color: #334155;
    }

    .erp-input {
        min-height: 3.1rem;
        width: 100%;
        border-radius: 0.85rem;
        border: 1px solid #dbe3ef;
        background: #f8fafc;
        padding: 0 1rem;
        font-weight: 700;
        outline: none;
    }

    .erp-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        background: white;
    }

    .erp-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .erp-table th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 950;
        letter-spacing: 0.16em;
        padding: 0.85rem 1rem;
        text-transform: uppercase;
    }

    .erp-table td {
        border-top: 1px solid #edf2f7;
        color: #1e293b;
        font-size: 0.92rem;
        font-weight: 650;
        padding: 0.85rem 1rem;
        vertical-align: top;
    }

    .erp-empty {
        border: 2px dashed #dce6f2;
        border-radius: 1.1rem;
        color: #94a3b8;
        display: grid;
        min-height: 14rem;
        place-items: center;
        text-align: center;
    }

    .erp-modal {
        position: fixed;
        inset: 0;
        z-index: 120;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .erp-modal.is-open {
        display: flex;
    }

    .erp-modal::before {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.38);
        backdrop-filter: blur(8px);
    }

    .erp-modal-panel {
        position: relative;
        max-height: min(86vh, 760px);
        overflow: auto;
        width: min(100%, 760px);
        border: 1px solid #e5eaf2;
        border-radius: 1.1rem;
        background: white;
        box-shadow: 0 30px 90px rgba(15, 23, 42, 0.25);
    }

    .dark .erp-card {
        border-color: #1e293b;
        background: rgba(15, 23, 42, 0.92);
        box-shadow: 0 14px 34px rgba(0, 0, 0, 0.25);
    }

    .dark .erp-soft,
    .dark .erp-input,
    .dark .erp-btn-outline {
        border-color: #1e293b;
        background: #0f172a;
        color: #e2e8f0;
    }

    .dark .erp-input:focus {
        background: #111827;
    }

    .dark .erp-table th {
        background: #111827;
        color: #94a3b8;
    }

    .dark .erp-table td {
        border-top-color: #1e293b;
        color: #e2e8f0;
    }

    .dark .erp-modal-panel {
        border-color: #1e293b;
        background: #0f172a;
    }

    .dark main .text-slate-950,
    .dark main .text-slate-900,
    .dark main .text-slate-800,
    .dark main .text-slate-700 {
        color: #f1f5f9;
    }

    .dark main .text-slate-600,
    .dark main .text-slate-500,
    .dark main .text-slate-400 {
        color: #94a3b8;
    }

    @media print {
        aside,
        header,
        #ai-chat-root,
        .no-print,
        .erp-actions {
            display: none !important;
        }

        body {
            overflow: visible !important;
            background: white !important;
        }

        main {
            overflow: visible !important;
            padding: 0 !important;
            background: white !important;
        }

        .erp-card {
            box-shadow: none !important;
        }
    }
</style>
