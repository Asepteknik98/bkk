<?php if(!isset($user,$data,$locationReady)){http_response_code(403);exit;} ?>
<div class="pkl-location-panel pkl-placement-wide">
<details class="pkl-location-map"><summary><?= panel_icon('location') ?> Atur titik lokasi (opsional)</summary>
<small>Klik peta atau geser pin jika titik lokasi perlu disesuaikan.</small>
<div id="pkl-place-map" aria-label="Peta lokasi tempat PKL"></div>
<div class="pkl-coordinate-fields"><label for="latitude">Lintang<input id="latitude" name="latitude" inputmode="decimal" value="<?= e($data['latitude']??'') ?>" placeholder="-6.123456" <?= !$locationReady?'readonly':'' ?>></label><label for="longitude">Bujur<input id="longitude" name="longitude" inputmode="decimal" value="<?= e($data['longitude']??'') ?>" placeholder="106.123456" <?= !$locationReady?'readonly':'' ?>></label></div>
<div class="pkl-location-actions"><button type="button" data-place-action="reverse"><?= panel_icon('location') ?> Ambil alamat</button><button type="button" data-place-action="clear"><?= panel_icon('close') ?> Hapus titik</button><a id="pkl-google-maps" href="https://www.google.com/maps" target="_blank" rel="noopener noreferrer"><?= panel_icon('link') ?> Google Maps</a></div>
</details>
<?php if(!$locationReady): ?><p class="notice">Untuk menyimpan koordinat, impor <strong>database/updates/026_pkl_location_points.sql</strong>. Pilihan nama/alamat tersimpan tetap bisa digunakan.</p><?php endif; ?>
<small><a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">© OpenStreetMap contributors</a>.</small>
</div>
