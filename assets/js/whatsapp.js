(() => {
    const group = document.querySelector('#wa-group');
    const jobField = document.querySelector('#wa-job-field');
    const job = document.querySelector('#wa-job');
    const updateGroup = () => {
        const alumni = group?.value === 'tracer';
        const applicants = group?.value === 'pelamar';
        const yearField=document.querySelector('#wa-year-field');
        const year=document.querySelector('#wa-year');
        if(yearField) yearField.hidden=applicants;
        if(year){year.disabled=applicants;if(applicants)year.value='';}
        if (jobField) jobField.hidden = alumni;
        if (job) { job.disabled = alumni; if (alumni) job.value = ''; }
    };
    group?.addEventListener('change', updateGroup);
    updateGroup();
    const form = document.querySelector('.wa-compose');
    if (!form) return;
    const choices = [...form.querySelectorAll('input[name="selected[]"]')];
    const count = document.querySelector('#wa-selected-count');
    const basketCount = document.querySelector('#wa-basket-count');
    const submit = document.querySelector('#wa-continue');
    const status = document.querySelector('#wa-selection-status');
    let queue = Promise.resolve();
    let canLeave = false;
    const updateRows = () => choices.forEach(input => input.closest('tr').classList.toggle('wa-row-selected', input.checked));
    const save = (action = 'update') => {
        // Capture before queuing so rapid checkbox changes are saved in order.
        const data = new URLSearchParams(new FormData(form));
        data.set('action', action); data.set('format', 'json');
        status.textContent = 'Menyimpan pilihan...';
        const run = async () => {
            const response = await fetch(form.dataset.selectionUrl, { method: 'POST', body: data, credentials: 'same-origin' });
            if (!response.ok) throw new Error('Pilihan belum tersimpan. Coba kembali sebelum berpindah halaman.');
            const result = await response.json();
            const phones = new Set(result.phones.map(String));
            if (action !== 'update') choices.forEach(input => { input.checked = !input.disabled && phones.has(input.dataset.phone); });
            count.textContent = basketCount.textContent = String(result.count);
            submit.disabled = result.count === 0;
            status.textContent = `${result.count} orang terpilih. Pilihan tersimpan, termasuk dari halaman dan pencarian lain.`;
            updateRows();
        };
        queue = queue.catch(() => {}).then(run);
        queue.catch(error => { status.textContent = error.message; });
        return queue;
    };
    choices.forEach(input => input.addEventListener('change', () => { updateRows(); save(); }));
    form.querySelectorAll('[data-selection-action]').forEach(button => button.addEventListener('click', event => {
        event.preventDefault(); save(button.dataset.selectionAction);
    }));
    form.addEventListener('submit', async event => {
        if (canLeave) return;
        event.preventDefault();
        try { await queue; await save(); canLeave = true; form.requestSubmit(submit); } catch {}
    });
    const filters = document.querySelector('.wa-filters');
    filters?.addEventListener('submit', async event => {
        if (canLeave) return;
        event.preventDefault();
        try { await queue; await save(); canLeave = true; filters.requestSubmit(); } catch {}
    });
    document.querySelectorAll('.wa-page .pagination a, .wa-filters .filter-reset').forEach(link => link.addEventListener('click', async event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        try { await queue; await save(); location.assign(link.href); } catch {}
    }));
    const message = document.querySelector('#wa-message');
    const link = document.querySelector('#wa-link');
    const preview = document.querySelector('#wa-preview');
    const updatePreview = () => {
        if (preview) preview.textContent = (message?.value || '').replaceAll('{nama}', preview.dataset.name || '') + (link?.value.trim() ? '\n\n' + link.value.trim() : '');
    };
    message?.addEventListener('input', updatePreview);
    link?.addEventListener('input', updatePreview);
    updateRows(); updatePreview();
})();
