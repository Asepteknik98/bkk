'use strict';
(() => {
    const picker = document.querySelector('.pkl-location-picker');
    if (!picker) return;
    const form = picker.closest('form'), query = document.getElementById('pkl-place-query');
    const status = document.getElementById('pkl-place-status'), results = picker.querySelector('.pkl-place-results');
    const lat = document.getElementById('latitude'), lng = document.getElementById('longitude');
    const name = document.getElementById('tempat_pkl'), address = document.getElementById('alamat_lengkap');
    const details = picker.querySelector('details'), mapsLink = document.getElementById('pkl-google-maps');
    let map, marker, busy = false;
    const coordinates = () => {
        if (!lat.value.trim() || !lng.value.trim()) return null;
        const a=Number(lat.value), b=Number(lng.value);
        return Number.isFinite(a)&&Number.isFinite(b)&&Math.abs(a)<=90&&Math.abs(b)<=180 ? [a,b] : null;
    };
    const link = () => {
        const point=coordinates();
        mapsLink.href='https://www.google.com/maps/search/?api=1&query='+encodeURIComponent(point?point.join(','):(address.value||name.value));
    };
    const pin = (point, zoom = false) => {
        if (!map) return;
        if (!marker) {
            marker=L.marker(point,{draggable:!lat.readOnly,autoPan:false}).addTo(map);
            marker.on('dragend',()=>setPoint(marker.getLatLng().lat,marker.getLatLng().lng));
        } else marker.setLatLng(point);
        if (zoom) map.setView(point,16);
    };
    const setPoint = (a,b) => {
        if(lat.readOnly){ status.textContent='Impor pembaruan SQL 026 untuk menyimpan titik koordinat.';return; }
        lat.value=Number(a).toFixed(6);lng.value=Number(b).toFixed(6);pin([Number(a),Number(b)]);link();
    };
    const initMap = () => {
        if (map) { setTimeout(()=>map.invalidateSize(),0);return; }
        if (!window.L) { status.textContent='Peta tidak dapat dimuat. Koordinat dan alamat tetap dapat diisi manual.';return; }
        map=L.map('pkl-place-map',{scrollWheelZoom:false}).setView(coordinates()||[-2,118],coordinates()?16:4);
        L.tileLayer(picker.dataset.tiles,{maxZoom:19,minZoom:3,keepBuffer:0,updateWhenIdle:true,attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'}).addTo(map);
        if(coordinates())pin(coordinates());
        map.on('click',event=>setPoint(event.latlng.lat,event.latlng.lng));
    };
    details.addEventListener('toggle',()=>{if(details.open)initMap();});
    [lat,lng].forEach(input=>input.addEventListener('change',()=>{const point=coordinates();if(point)pin(point,true);link();}));
    [name,address].forEach(input=>input.addEventListener('input',link));
    const choose = (place, mode) => {
        if(mode!=='reverse'||!name.value.trim())name.value=(place.name||place.address.split(',')[0]).slice(0,190);
        address.value=place.address;
        if(place.latitude!==null&&place.longitude!==null&&!lat.readOnly){setPoint(place.latitude,place.longitude);details.open=true;initMap();pin(coordinates(),true);}
        else {lat.value='';lng.value='';if(marker&&map){map.removeLayer(marker);marker=null;}link();}
        status.textContent='Lokasi dipilih. Periksa nama, alamat, dan titik sebelum menyimpan penempatan.';
    };
    const lookup = async mode => {
        if(busy)return;
        if(mode==='reverse'&&!coordinates()){status.textContent='Pilih titik atau isi lintang/bujur terlebih dahulu.';return;}
        if(mode==='search'&&query.value.trim().length<3){status.textContent='Isi nama tempat dan kota minimal 3 karakter.';return;}
        busy=true;status.textContent='Mencari lokasi...';results.replaceChildren();
        const buttons=picker.querySelectorAll('button[data-place-action]');buttons.forEach(button=>button.disabled=true);
        try{
            const body=new URLSearchParams({mode,q:query.value.trim(),csrf_token:form.querySelector('[name="csrf_token"]').value});
            if(mode==='reverse'){body.set('latitude',lat.value);body.set('longitude',lng.value);}
            const response=await fetch(picker.dataset.endpoint,{method:'POST',body,credentials:'same-origin',headers:{Accept:'application/json'}});
            const data=await response.json();if(!response.ok)throw Error(data.error||'Pencarian gagal.');
            status.textContent=data.results.length?`${data.results.length} lokasi ditemukan. Pilih lokasi yang benar.`:'Lokasi tidak ditemukan. Coba nama/kota lain atau isi alamat manual.';
            data.results.forEach(place=>{
                const button=document.createElement('button');button.type='button';button.className='pkl-place-result';
                const title=document.createElement('strong');title.textContent=place.name||'Gunakan alamat ini';
                const text=document.createElement('span');text.textContent=place.address;
                button.append(title,text);button.addEventListener('click',()=>choose(place,mode));results.append(button);
            });
        }catch(error){status.textContent=error instanceof SyntaxError?'Sesi atau layanan peta tidak tersedia. Muat ulang halaman; alamat tetap bisa diisi manual.':error.message;}
        finally{busy=false;buttons.forEach(button=>button.disabled=false);}
    };
    picker.querySelectorAll('[data-place-action]').forEach(button=>button.addEventListener('click',()=>{
        if(button.dataset.placeAction==='clear'){lat.value='';lng.value='';if(marker&&map){map.removeLayer(marker);marker=null;}link();status.textContent='Titik dihapus; nama/alamat tetap.';}
        else lookup(button.dataset.placeAction);
    }));
    query.addEventListener('keydown',event=>{if(event.key==='Enter'){event.preventDefault();lookup('stored');}});
    link();
})();
