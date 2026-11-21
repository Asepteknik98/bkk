'use strict';
const pklPicker = document.querySelector('.pkl-multi-picker');
if (pklPicker) {
    const choices = Array.from(pklPicker.querySelectorAll('input[name="siswa_ids[]"]'));
    const count = document.getElementById('pkl-selected-count');
    const update = () => { count.textContent = `${choices.filter(input => input.checked).length} siswa dipilih`; };
    pklPicker.addEventListener('change', update);
    pklPicker.querySelectorAll('[data-pkl-check]').forEach(button => {
        button.addEventListener('click', () => {
            choices.forEach(input => { input.checked = button.dataset.pklCheck === 'all'; });
            update();
        });
    });
    update();
}
