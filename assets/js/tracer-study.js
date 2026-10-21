'use strict';
const statusSelect = document.querySelector('#status_kegiatan');
if (statusSelect) {
    const updateDetails = () => {
        document.querySelectorAll('[data-tracer-status]').forEach((group) => {
            const active = group.dataset.tracerStatus === statusSelect.value;
            group.hidden = !active;
            group.disabled = !active;
            group.querySelectorAll('[data-status-required]').forEach((input) => { input.required = active; });
        });
    };
    statusSelect.addEventListener('change', updateDetails);
    updateDetails();
}
