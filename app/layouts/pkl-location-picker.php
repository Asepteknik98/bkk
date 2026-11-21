<?php if(!isset($user,$data,$locationReady)){http_response_code(403);exit;} $maps=app_config('maps')??[]; ?>
<div class="pkl-location-picker pkl-placement-wide" data-endpoint="<?= e(base_url('panel/pkl/lokasi.php')) ?>" data-tiles="<?= e($maps['tile_url']??'https://tile.openstreetmap.org/{z}/{x}/{y}.png') ?>">
<div class="pkl-location-search"><label for="pkl-place-query"><?= panel_icon('search') ?> Cari lokasi (opsional)</label><div><input id="pkl-place-query" maxlength="190" placeholder="Nama perusahaan / tempat PKL dan kota"><button type="button" data-place-action="stored"><?= panel_icon('save') ?> Tersimpan</button><button type="button" data-place-action="search"><?= panel_icon('search') ?> Cari peta</button></div><small>Gunakan nama tempat/kota, bukan data pribadi siswa.</small></div>
<p id="pkl-place-status" role="status"></p><div class="pkl-place-results" aria-label="Hasil pencarian tempat"></div>
<details class="pkl-location-map"><summary><?= panel_icon('location') ?> Peta &amp; koordinat</summary>
<small>Klik atau geser pin. Periksa alamat sebelum menyimpan.</small>
<div id="pkl-place-map" aria-label="Peta lokasi tempat PKL"></div>
<div class="pkl-coordinate-fields"><label for="latitude">Lintang<input id="latitude" name="latitude" inputmode="decimal" value="<?= e($data['latitude']??'') ?>" placeholder="-6.123456" <?= !$locationReady?'readonly':'' ?>></label><label for="longitude">Bujur<input id="longitude" name="longitude" inputmode="decimal" value="<?= e($data['longitude']??'') ?>" placeholder="106.123456" <?= !$locationReady?'readonly':'' ?>></label></div>
<div class="pkl-location-actions"><button type="button" data-place-action="reverse"><?= panel_icon('location') ?> Ambil alamat</button><button type="button" data-place-action="clear"><?= panel_icon('close') ?> Hapus titik</button><a id="pkl-google-maps" href="https://www.google.com/maps" target="_blank" rel="noopener noreferrer"><?= panel_icon('link') ?> Google Maps</a></div>
</details>
<?php if(!$locationReady): ?><p class="notice">Untuk menyimpan koordinat, impor <strong>database/updates/026_pkl_location_points.sql</strong>. Pilihan nama/alamat tersimpan tetap bisa digunakan.</p><?php endif; ?>
<small><a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">© OpenStreetMap contributors</a>. Nama/alamat tetap dapat diedit.</small>
</div>
