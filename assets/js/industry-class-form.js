'use strict';
(() => {
    const editor = document.querySelector('[data-industry-editor]');
    if (!editor) return;
    const updateCounts = () => {
        ['speakers', 'participants'].forEach(name => {
            editor.querySelector(`[data-choice-count="${name}"]`).textContent = `${editor.querySelectorAll(`input[name="${name}[]"]:checked`).length} dipilih`;
        });
    };
    editor.addEventListener('change', updateCounts);
    const speakerSearch = editor.querySelector('[data-speaker-search]');
    speakerSearch.addEventListener('input', () => {
        const query = speakerSearch.value.trim().toLocaleLowerCase('id');
        const choices = [...editor.querySelectorAll('[data-speaker-list] .industry-choice-row')];
        choices.forEach(row => { row.hidden = !row.textContent.toLocaleLowerCase('id').includes(query); });
        editor.querySelector('[data-speaker-empty]').hidden = choices.length === 0 || choices.some(row => !row.hidden);
    });
    // Fetch the same protected GET view; do not submit or replace unsaved class fields.
    const searchForm = document.getElementById('industry-student-search');
    let busy = false;
    searchForm.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy) return;
        busy = true;
        const button = editor.querySelector('button[form="industry-student-search"]');
        const status = editor.querySelector('[data-student-search-status]');
        button.disabled = true;
        status.textContent = 'Mencari siswa...';
        try {
            const url = new URL(location.href);
            url.searchParams.set('q_siswa', new FormData(searchForm).get('q_siswa') || '');
            const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'text/html' } });
            if (!response.ok || response.redirected) throw new Error('search');
            const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
            const results = documentResult.querySelector('[data-student-list]');
            if (!results) throw new Error('search');
            const list = editor.querySelector('[data-student-list]');
            // Read selections after the response, preserving changes made during the request.
            const selected = [...list.querySelectorAll('input:checked')].map(input => input.closest('.industry-choice-row'));
            const selectedIds = new Set(selected.map(row => row.querySelector('input').value));
            const resultRows = [...results.querySelectorAll('.industry-choice-row')];
            const additions = resultRows.filter(row => !selectedIds.has(row.querySelector('input').value));
            additions.forEach(row => { row.querySelector('input').checked = false; });
            list.replaceChildren(...selected, ...additions);
            if (!list.children.length) {
                const empty = document.createElement('p');
                empty.className = 'industry-choice-empty';
                empty.textContent = 'Tidak ada siswa sesuai pencarian.';
                list.append(empty);
            }
            status.textContent = `${resultRows.length} hasil tersedia. Pilihan siswa dan isian kelas tetap dipertahankan. Klik Simpan kelas untuk menyimpan perubahan.`;
            updateCounts();
        } catch {
            status.textContent = 'Pencarian gagal. Periksa koneksi atau sesi login, lalu coba kembali. Isian dan pilihan Anda tetap tersedia.';
        } finally {
            busy = false;
            button.disabled = false;
        }
    });
    updateCounts();
})();
