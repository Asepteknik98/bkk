document.querySelectorAll('[data-vertical-list]').forEach(viewport => {
    const controls = document.querySelector(`[data-scroll-target="${viewport.id}"]`) || (viewport.id === 'alumni-list' ? document.querySelector('.alumni-scroll-controls') : null);
    if (!viewport || !controls) return;
    const up = controls.querySelector('[data-scroll="-1"]');
    const down = controls.querySelector('[data-scroll="1"]');
    const update = () => {
        controls.hidden = viewport.scrollHeight <= viewport.clientHeight + 2;
        up.disabled = viewport.scrollTop <= 2;
        down.disabled = viewport.scrollTop + viewport.clientHeight >= viewport.scrollHeight - 2;
    };
    controls.querySelectorAll('button').forEach(button => button.addEventListener('click', () => {
        viewport.scrollBy({ top: Number(button.dataset.scroll) * viewport.clientHeight * .8,
            behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
    }));
    viewport.addEventListener('scroll', update, { passive: true });
    new ResizeObserver(update).observe(viewport);
    update();
});
