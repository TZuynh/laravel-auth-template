import './bootstrap';
import './ai-chat-widget';
import './users-management';
import './notifications';

function initThemeToggle() {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const root = document.documentElement;
            const nextTheme = root.classList.contains('dark') ? 'light' : 'dark';
            root.classList.toggle('dark', nextTheme === 'dark');
            localStorage.setItem('theme', nextTheme);
        });
    });
}

function initFlashBanner() {
    const banner = document.getElementById('flash-banner');
    const closeBtn = banner?.querySelector('[data-flash-close]');
    if (!banner || !closeBtn) return;

    const close = () => banner.remove();
    closeBtn.addEventListener('click', close);

    window.setTimeout(close, 3500);
}

function collectPrintStyles() {
    return Array.from(document.querySelectorAll('link[rel="stylesheet"], style'))
        .map((node) => node.outerHTML)
        .join('\n');
}

function printSection(selector) {
    const element = document.querySelector(selector);
    if (!element) return;

    const clone = element.cloneNode(true);
    clone.classList.remove('hidden');

    const printWindow = window.open('', '_blank', 'width=1100,height=760');
    if (!printWindow) {
        window.print();
        return;
    }

    printWindow.document.write(`<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${document.title}</title>
    ${collectPrintStyles()}
    <style>
        body { background: #ffffff !important; color: #0f172a; padding: 24px; }
        .erp-actions, .no-print { display: none !important; }
        .hidden { display: block !important; }
        table { width: 100%; }
    </style>
</head>
<body>${clone.outerHTML}</body>
</html>`);
    printWindow.document.close();

    const runPrint = () => {
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };

    if (printWindow.document.readyState === 'complete') {
        window.setTimeout(runPrint, 150);
    } else {
        printWindow.addEventListener('load', () => window.setTimeout(runPrint, 150), { once: true });
    }
}

function csvEscape(value) {
    const normalized = String(value ?? '').replace(/\s+/g, ' ').trim();
    return `"${normalized.replace(/"/g, '""')}"`;
}

function cellText(cell) {
    const controls = Array.from(cell.querySelectorAll('input, select, textarea'));
    if (controls.length === 0) {
        return cell.innerText;
    }

    const clone = cell.cloneNode(true);
    clone.querySelectorAll('input, select, textarea, button').forEach((node) => node.remove());
    const staticText = clone.innerText;
    const controlText = controls
        .map((control) => {
            if (control.tagName === 'SELECT') {
                return control.selectedOptions?.[0]?.textContent || control.value;
            }

            return control.value;
        })
        .filter(Boolean)
        .join(' ');

    return [staticText, controlText].filter(Boolean).join(' ');
}

function exportTable(selector, filename = 'export.csv') {
    const table = document.querySelector(selector);
    if (!table) return;

    const rows = Array.from(table.querySelectorAll('tr'))
        .filter((row) => !row.classList.contains('hidden'))
        .map((row) => Array.from(row.children)
            .filter((cell) => !cell.classList.contains('erp-actions'))
            .map((cell) => csvEscape(cellText(cell)))
            .join(','))
        .filter(Boolean);

    if (rows.length === 0) return;

    const blob = new Blob([`\uFEFF${rows.join('\n')}`], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

function initPrintAndExport() {
    document.addEventListener('click', (event) => {
        const printButton = event.target.closest('[data-print-section]');
        if (printButton) {
            printSection(printButton.dataset.printSection);
            return;
        }

        const exportButton = event.target.closest('[data-export-table]');
        if (exportButton) {
            exportTable(exportButton.dataset.exportTable, exportButton.dataset.filename || 'export.csv');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initFlashBanner();
    initPrintAndExport();
});
