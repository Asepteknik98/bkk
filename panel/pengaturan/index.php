<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/app/helpers/management.php';
$user=require_role('admin');
$sections=settings_sections();$sectionKey=$_GET['bagian']??'identitas';
if(!is_string($sectionKey)||!isset($sections[$sectionKey])){http_response_code(400);exit('Bagian pengaturan tidak valid.');}
$section=$sections[$sectionKey];$fields=settings_fields();$data=[];
foreach($section['fields'] as $key){$data[$key]=setting($key,$key==='school_name'?'SMK JAYA BUANA':'');}
$draft=$_SESSION['settings_form']??null;
if(is_array($draft)){
    if(isset($draft['data'])){
        if(($draft['section']??'')===$sectionKey||($draft['section']??'')==='legacy'){$data=array_replace($data,array_intersect_key($draft['data'],$data));unset($_SESSION['settings_form']);}
    }else{$data=array_replace($data,array_intersect_key($draft,$data));unset($_SESSION['settings_form']);}
}
panel_start($user,'Pengaturan - '.$section['label'],'pengaturan');
?>
<div class="settings-page settings-separated">
    <div class="page-actions"><div><p class="eyebrow">Konfigurasi portal</p><h1><?= panel_icon('pengaturan') ?> Pengaturan Website</h1></div></div>

    <div class="settings-workspace">
        <nav class="settings-category-list" aria-label="Bagian pengaturan">
            <?php foreach($sections as $key=>$item): ?><a href="<?= e(base_url('panel/pengaturan/?bagian='.$key)) ?>" <?= $sectionKey===$key?'aria-current="page"':'' ?>><?= panel_icon($item['icon']) ?><span><?= e($item['label']) ?></span><?= panel_icon('next') ?></a><?php endforeach; ?>
        </nav>
        <div class="settings-section-content">
            <form class="settings-form settings-single-form" method="post" enctype="multipart/form-data" action="<?= e(base_url('panel/pengaturan/simpan.php')) ?>">
                <?= csrf_field() ?><input type="hidden" name="bagian" value="<?= e($sectionKey) ?>">
                <section class="card settings-section <?= $sectionKey==='sosial'?'settings-social':'' ?> <?= $sectionKey==='logo'?'settings-logos':'' ?>" <?= in_array($sectionKey,['kelas','jurusan','angkatan','kelas_industri','tahun_pkl'],true)?'id="settings-student-options"':'' ?>>
                    <header class="settings-section-heading"><h2><?= panel_icon($section['icon']) ?> <?= e($section['label']) ?></h2><p><?= e($section['description']) ?></p></header>
                    <?php if($sectionKey==='logo'): ?>
                    <div class="settings-logo-grid"><div class="settings-logo-field"><?php management_image_field('bkk_logo','Logo BKK',setting('bkk_logo')); ?></div><div class="settings-logo-field"><?php management_image_field('school_logo','Logo sekolah',setting('school_logo')); ?></div></div>
                    <?php else: ?><div class="settings-fields">
                        <?php foreach($section['fields'] as $key): [$label,$max,$required]=$fields[$key];$multiline=in_array($key,['contact_address','profile_description','manager_address','privacy_retention','participant_classes','participant_departments','participant_years','participant_industries','participant_pkl_years'],true); ?>
                        <div class="settings-field <?= $multiline||$key==='copyright_owner'?'settings-wide':'' ?>">
                            <label for="<?= $key ?>"><?= str_starts_with($key,'social_')?social_icon($key):panel_icon(['site_name'=>'identity','school_name'=>'program','contact_phone'=>'phone','contact_email'=>'mail','contact_address'=>'location','profile_description'=>'message','participant_classes'=>'peserta','participant_departments'=>'program','participant_years'=>'calendar','participant_industries'=>'lowongan','participant_pkl_years'=>'calendar'][$key]??$section['icon']) ?> <?= e(str_starts_with($key,'social_')?social_networks()[$key]:$label) ?></label>
                            <?php if(str_starts_with($key,'participant_')): ?>
                            <div class="settings-choice-sheet">
                                <div class="settings-sheet-header" aria-hidden="true"><span>#</span><span>A</span></div>
                                <div class="settings-sheet-body"><div class="settings-sheet-gutter" aria-hidden="true"><div class="settings-sheet-numbers"><?php for($line=1;$line<=8;$line++): ?><span><?= $line ?></span><?php endfor; ?></div></div><textarea id="<?= $key ?>" name="<?= $key ?>" rows="6" wrap="off" maxlength="<?= $max ?>"><?= e($data[$key]) ?></textarea></div>
                            </div>
                            <?php elseif($multiline): ?><textarea id="<?= $key ?>" name="<?= $key ?>" rows="<?= $key==='profile_description'?8:3 ?>" maxlength="<?= $max ?>"><?= e($data[$key]) ?></textarea>
                            <?php else: ?><input id="<?= $key ?>" name="<?= $key ?>" type="<?= str_starts_with($key,'social_')?'url':(in_array($key,['contact_email','manager_email'],true)?'email':'text') ?>" maxlength="<?= $max ?>" value="<?= e($data[$key]) ?>" <?= str_starts_with($key,'social_')?'placeholder="https://"':'' ?> <?= $required?'required':'' ?>><?php endif; ?>
                        </div><?php endforeach; ?>
                    </div><?php endif; ?>
                    <div class="settings-save"><span>Hanya perubahan pada bagian ini yang disimpan.</span><button type="submit"><?= panel_icon('save') ?> Simpan <?= e($section['label']) ?></button></div>
                </section>
            </form>
        </div>
    </div>
</div>
<?php panel_end($user); ?>
