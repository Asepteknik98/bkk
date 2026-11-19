'use strict';
const reportEditor = document.querySelector('[data-report-editor]');
if (reportEditor) {
    const list = reportEditor.querySelector('[data-report-items]');
    const add = reportEditor.querySelector('[data-report-add]');
    const rows = () => [...list.querySelectorAll('[data-report-item]')];
    const sync = () => {
        rows().forEach((row, index) => {
            row.querySelector('[data-report-number]').textContent = String(index + 1);
            row.querySelectorAll('[name]').forEach(field => { field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`); });
            row.querySelector('[data-report-remove]').hidden = rows().length === 1;
        });
        add.disabled = rows().length >= 50;
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
        if (button && rows().length > 1) { button.closest('[data-report-item]').remove(); sync(); add.focus(); }
    });
    sync();
}
document.querySelectorAll('[data-report-print]').forEach(button => button.addEventListener('click', () => window.print()));
