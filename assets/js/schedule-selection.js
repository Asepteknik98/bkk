'use strict';
(() => {
    const form=document.querySelector('.schedule-multi-picker');
    if(!form)return;
    const key=form.dataset.storageKey, boxes=Array.from(form.querySelectorAll('input[name="placement_ids[]"]'));
    const counter=document.getElementById('schedule-selected-count');
    let selected=new Set();
    try{
        if(form.dataset.reset==='1')sessionStorage.removeItem(key);
        const stored=JSON.parse(sessionStorage.getItem(key)||'[]');
        if(Array.isArray(stored))selected=new Set(stored.filter(id=>typeof id==='string'&&/^[1-9][0-9]{0,17}$/.test(id)).slice(0,50));
    }catch{}
    const save=()=>{
        try{sessionStorage.setItem(key,JSON.stringify([...selected]));}catch{}
        counter.textContent=`${selected.size} siswa dipilih`;
        boxes.forEach(box=>{box.checked=selected.has(box.value);});
    };
    form.addEventListener('change',event=>{
        const box=event.target;if(!boxes.includes(box))return;
        if(box.checked){if(selected.size>=50&&!selected.has(box.value)){box.checked=false;counter.textContent='Maksimal 50 penempatan. Kurangi pilihan terlebih dahulu.';return;}selected.add(box.value);}else selected.delete(box.value);
        save();
    });
    form.querySelectorAll('[data-schedule-check]').forEach(button=>button.addEventListener('click',()=>{
        if(button.dataset.scheduleCheck==='none')selected.clear();
        else boxes.forEach(box=>{if(selected.size<50)selected.add(box.value);});
        save();
    }));
    form.addEventListener('submit',event=>{
        if(!selected.size){event.preventDefault();counter.textContent='Centang minimal satu siswa.';return;}
        form.querySelectorAll('[data-other-page-selection]').forEach(input=>input.remove());
        const visible=new Set(boxes.map(box=>box.value));
        selected.forEach(id=>{if(!visible.has(id)){const input=document.createElement('input');input.type='hidden';input.name='placement_ids[]';input.value=id;input.dataset.otherPageSelection='1';form.append(input);}});
    });
    save();
})();
