<?php
declare(strict_types=1);

function pkl_points_ready(): bool
{
    static $ready;
    return $ready ??= (bool)panel_query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pkl_location_points'")->fetchColumn();
}

function pkl_point_input(array $data): ?array
{
    $lat = input_text($data, 'latitude'); $lng = input_text($data, 'longitude');
    if ($lat === '' && $lng === '') { return null; }
    foreach ([$lat, $lng] as $value) {
        if (!preg_match('/^-?[0-9]{1,3}(?:\.[0-9]{1,18})?$/D', $value)) { throw new InvalidArgumentException('Isi lintang dan bujur sebagai angka desimal yang valid.'); }
    }
    if (abs((float)$lat) > 90 || abs((float)$lng) > 180) { throw new InvalidArgumentException('Lintang harus -90 sampai 90 dan bujur -180 sampai 180.'); }
    return ['latitude'=>number_format((float)$lat,6,'.',''), 'longitude'=>number_format((float)$lng,6,'.','')];
}

function pkl_store_point(int $id, ?array $point): void
{
    if (!pkl_points_ready()) {
        if ($point) { throw new InvalidArgumentException('Impor database/updates/026_pkl_location_points.sql sebelum menyimpan koordinat.'); }
        return;
    }
    if (!$point) { panel_query('DELETE FROM pkl_location_points WHERE placement_id=:id', ['id'=>$id]); return; }
    panel_query('INSERT INTO pkl_location_points(placement_id,latitude,longitude) VALUES(:id,:latitude,:longitude) ON DUPLICATE KEY UPDATE latitude=VALUES(latitude),longitude=VALUES(longitude)', ['id'=>$id]+$point);
}

/** Only public business-place text goes upstream, never student metadata. */
function pkl_map_lookup(string $mode, array $input): array
{
    $config = app_config('maps') ?? [];
    $endpoint = rtrim((string)($config['geocoder_url'] ?? 'https://nominatim.openstreetmap.org'), '/');
    if (!str_starts_with($endpoint, 'https://') || filter_var($endpoint,FILTER_VALIDATE_URL) === false) { throw new RuntimeException('Konfigurasi layanan peta tidak valid.'); }
    if ($mode === 'reverse') {
        $point = pkl_point_input($input);
        if (!$point) { throw new InvalidArgumentException('Pilih titik pada peta terlebih dahulu.'); }
        $args = ['lat'=>$point['latitude'],'lon'=>$point['longitude'],'format'=>'jsonv2','zoom'=>18];
    } else {
        $query = input_text($input,'q');
        if (!valid_text($query,190) || mb_strlen($query)<3) { throw new InvalidArgumentException('Isi nama tempat/kota, 3-190 karakter.'); }
        $args = ['q'=>$query,'format'=>'jsonv2','limit'=>5,'countrycodes'=>'id','addressdetails'=>1];
    }
    $directory = APP_ROOT.'/storage/map-cache';
    if (!is_dir($directory) && !mkdir($directory,0700,true) && !is_dir($directory)) { throw new RuntimeException('Cache peta belum tersedia.'); }
    $key = hash('sha256',$endpoint.$mode.json_encode($args));
    $path = $directory.'/'.$key.'.json';
    $lock = fopen($directory.'/requests.lock','c+');
    if (!$lock || !flock($lock, LOCK_EX|LOCK_NB)) { if($lock){fclose($lock);} throw new InvalidArgumentException('Pencarian lain sedang berjalan. Tunggu beberapa detik.'); }
    try {
        if (is_file($path) && filemtime($path)>time()-604800) { return json_decode((string)file_get_contents($path),true,512,JSON_THROW_ON_ERROR); }
        rewind($lock); $last = (float)stream_get_contents($lock);
        if (microtime(true)-$last<1.1) { throw new InvalidArgumentException('Tunggu sebentar sebelum mencari lokasi lagi.'); }
        ftruncate($lock,0); rewind($lock); fwrite($lock,(string)microtime(true)); fflush($lock);
        if (!function_exists('curl_init')) { throw new RuntimeException('Ekstensi cURL diperlukan untuk pencarian peta.'); }
        $curl = curl_init($endpoint.'/'.$mode.'?'.http_build_query($args));
        $body = '';
        curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>false,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>4,CURLOPT_TIMEOUT=>8,CURLOPT_USERAGENT=>'BKK-Jaya-Buana/1.0 (PKL place picker)',CURLOPT_HTTPHEADER=>['Accept: application/json','Accept-Language: id'],CURLOPT_WRITEFUNCTION=>static function($handle,string $chunk)use(&$body):int{if(strlen($body)+strlen($chunk)>524288){return 0;}$body.=$chunk;return strlen($chunk);}]);
        $ok=curl_exec($curl);$code=(int)curl_getinfo($curl,CURLINFO_HTTP_CODE);curl_close($curl);
        if($ok===false||$code!==200){throw new RuntimeException('Pencarian peta sementara tidak tersedia.');}
        $raw=json_decode($body,true,512,JSON_THROW_ON_ERROR);if(!is_array($raw)){throw new RuntimeException('Respons peta tidak valid.');}$results=[];
        foreach($mode==='reverse'?[$raw]:$raw as $row){
            if(!is_array($row)){continue;}
            try{$point=pkl_point_input(['latitude'=>(string)($row['lat']??''),'longitude'=>(string)($row['lon']??'')]);}catch(InvalidArgumentException){continue;}
            if(!$point){continue;}
            $results[]=$point+['name'=>mb_substr((string)($row['name']??''),0,190),'address'=>mb_substr((string)($row['display_name']??''),0,5000)];
            if(count($results)>=5){break;}
        }
        file_put_contents($path,json_encode($results,JSON_THROW_ON_ERROR),LOCK_EX);
        // Bound the cache without retaining old query files indefinitely.
        foreach(glob($directory.'/*.json')?:[] as $cached){if(filemtime($cached)<time()-604800){unlink($cached);}}
        return $results;
    } finally { flock($lock,LOCK_UN);fclose($lock); }
}
