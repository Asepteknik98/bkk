'use strict';
(() => {
    const editor = document.querySelector('[data-industry-editor]');
    if (!editor) return;
    const selectedList = editor.querySelector('[data-selected-students]');
    const selectedRows = () => [...selectedList.querySelectorAll('.industry-choice-row')];
    const selectedIds = () => new Set(selectedRows().map(row => row.querySelector('input').value));
    const updateCounts = () => {
        const counts = {
            speakers: editor.querySelectorAll('input[name="speakers[]"]:checked').length,
            participants: selectedRows().length
        };
        Object.entries(counts).forEach(([name, count]) => {
            editor.querySelectorAll(`[data-choice-count="${name}"]`).forEach(label => { label.textContent = `${count} dipilih`; });
        });
        const empty = selectedList.querySelector('[data-selected-empty]');
        if (empty) empty.remove();
        if (!counts.participants) {
            const message = document.createElement('p');
            message.className = 'industry-choice-empty';
            message.dataset.selectedEmpty = '';
            message.textContent = 'Belum ada siswa dipilih. Centang nama pada hasil pencarian.';
            selectedList.append(message);
        }
    };
    const syncResults = () => {
        const ids = selectedIds();
        editor.querySelectorAll('[data-student-list] input[type="checkbox"]').forEach(input => {
            // Only the selected panel submits participant IDs; each ID is posted once.
            input.removeAttribute('name');
            input.disabled = false;
            input.checked = ids.has(input.value);
        });
    };
    editor.addEventListener('change', event => {
        const input = event.target;
        if (input.matches('[data-student-list] input[type="checkbox"]')) {
            const existing = selectedRows().find(row => row.querySelector('input').value === input.value);
            if (input.checked && !existing) {
                const row = input.closest('.industry-choice-row').cloneNode(true);
                const storedInput = row.querySelector('input');
                storedInput.name = 'participants[]';
                storedInput.disabled = false;
                storedInput.checked = true;
                selectedList.append(row);
            } else if (!input.checked && existing) existing.remove();
        } else if (input.matches('[data-selected-students] input[type="checkbox"]') && !input.checked) {
            input.closest('.industry-choice-row').remove();
        }
        syncResults();
        updateCounts();
    });
    const speakerSearch = editor.querySelector('[data-speaker-search]');
    speakerSearch.addEventListener('input', () => {
        const query = speakerSearch.value.trim().toLocaleLowerCase('id');
        const choices = [...editor.querySelectorAll('[data-speaker-list] .industry-choice-row')];
        choices.forEach(row => { row.hidden = !row.textContent.toLocaleLowerCase('id').includes(query); });
        editor.querySelector('[data-speaker-empty]').hidden = choices.length === 0 || choices.some(row => !row.hidden);
    });
    // Replace only a bounded result page, keeping selections and unsaved class fields.
    const searchForm = document.getElementById('industry-student-search');
    let busy = false;
    const loadResults = async url => {
        if (busy) return;
        busy = true;
        const button = editor.querySelector('button[form="industry-student-search"]');
        const status = editor.querySelector('[data-student-search-status]');
        button.disabled = true;
        editor.querySelector('[data-student-results]').setAttribute('aria-busy', 'true');
        status.textContent = 'Memuat hasil pencarian...';
        try {
            const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'text/html' } });
            if (!response.ok || response.redirected) throw new Error('search');
            const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
            const results = documentResult.querySelector('[data-student-results]');
            if (!results) throw new Error('search');
            editor.querySelector('[data-student-results]').replaceWith(results);
            syncResults();
            updateCounts();
            status.textContent = 'Pilihan dan isian kelas tetap tersedia. Klik Simpan kelas setelah selesai memilih siswa.';
        } catch {
            status.textContent = 'Hasil belum dapat dimuat. Periksa koneksi atau sesi login, lalu coba lagi. Pilihan dan isian Anda tetap tersedia.';
        } finally {
            busy = false;
            button.disabled = false;
            editor.querySelector('[data-student-results]').removeAttribute('aria-busy');
        }
    };
    searchForm.addEventListener('submit', event => {
        event.preventDefault();
        const url = new URL(location.href);
        url.searchParams.set('q_siswa', new FormData(searchForm).get('q_siswa') || '');
        url.searchParams.set('page_siswa', '1');
        loadResults(url);
    });
    editor.addEventListener('click', event => {
        const link = event.target.closest('a[data-student-page]');
        if (!link) return;
        event.preventDefault();
        loadResults(new URL(link.href));
    });
    syncResults();
    updateCounts();
})();
