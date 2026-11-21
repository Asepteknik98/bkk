<?php if(!isset($user,$data,$locationReady)){http_response_code(403);exit;} $maps=app_config('maps')??[]; ?>
<div class="pkl-location-picker pkl-placement-wide" data-endpoint="<?= e(base_url('panel/pkl/lokasi.php')) ?>" data-tiles="<?= e($maps['tile_url']??'https://tile.openstreetmap.org/{z}/{x}/{y}.png') ?>">
<div class="pkl-location-search"><label for="pkl-place-query"><?= panel_icon('search') ?> Cari tempat PKL (opsional)</label><div><input id="pkl-place-query" maxlength="190" placeholder="Nama perusahaan / tempat PKL dan kota"><button type="button" data-place-action="stored"><?= panel_icon('save') ?> Cari tersimpan</button><button type="button" data-place-action="search"><?= panel_icon('search') ?> Cari di peta</button></div><small>Pilih hasil pencarian untuk mengisi nama dan alamat. Jangan masukkan data pribadi siswa.</small></div>
<p id="pkl-place-status" role="status"></p><div class="pkl-place-results" aria-label="Hasil pencarian tempat"></div>
</div>
