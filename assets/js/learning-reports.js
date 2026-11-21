'use strict';
const reportEditor = document.querySelector('[data-report-editor]');
if (reportEditor) {
    const list = reportEditor.querySelector('[data-report-items]');
    const add = reportEditor.querySelector('[data-report-add]');
    const state = reportEditor.querySelector('[name="status"]');
    const count = reportEditor.querySelector('[data-report-count]');
    const rows = () => [...list.querySelectorAll('[data-report-item]')];
    const sync = () => {
        const items = rows();
        items.forEach((row, index) => {
            row.querySelector('[data-report-number]').textContent = String(index + 1);
            row.querySelectorAll('[name]').forEach(field => { field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`); });
            row.querySelector('[data-report-remove]').hidden = items.length === 1;
            const predicate = row.querySelector('[name$="[predikat]"]');
            predicate.required = state.value === 'final';
        });
        add.disabled = items.length >= 50;
        if (count) count.textContent = String(items.length);
    };
    add.hidden = false;
    add.addEventListener('click', () => {
        if (rows().length >= 50) return;
        const copy = rows()[0].cloneNode(true);
        copy.querySelectorAll('input,textarea,select').forEach(field => { field.value = ''; });
        list.append(copy); sync(); copy.querySelector('input').focus();
    });
    list.addEventListener('click', event => {
        const button = event.target.closest('[data-report-remove]');
        if (!button || rows().length <= 1) return;
        const row = button.closest('[data-report-item]');
        if ([...row.querySelectorAll('input,textarea,select')].some(field => field.value.trim() !== '') && !window.confirm('Hapus kompetensi ini beserta nilai dan keterangannya?')) return;
        const next = row.nextElementSibling || row.previousElementSibling;
        row.remove(); sync(); (next?.querySelector('input') || add).focus();
    });
    state.addEventListener('change', sync);
    sync();
    const signature = () => new URLSearchParams(new FormData(reportEditor)).toString();
    const initial = signature();
    let leaving = false;
    reportEditor.addEventListener('submit', () => { leaving = true; });
    document.querySelector('.report-student-search')?.addEventListener('submit', event => {
        if (signature() !== initial && !window.confirm('Pencarian ulang akan mengosongkan isian rapor yang belum disimpan. Lanjutkan?')) { event.preventDefault(); return; }
        leaving = true;
    });
    window.addEventListener('beforeunload', event => {
        if (!leaving && signature() !== initial) { event.preventDefault(); event.returnValue = ''; }
    });
}
document.querySelectorAll('[data-report-print]').forEach(button => button.addEventListener('click', () => window.print()));
