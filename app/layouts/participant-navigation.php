<?php
if(!defined('APP_ROOT')){http_response_code(403);exit;}
$participantTabs=[
    'list'=>['panel/peserta/','identity','Daftar siswa'],
    'form'=>['panel/peserta/form.php','add','Tambah siswa'],
    'import'=>['panel/peserta/impor.php','upload','Impor Excel'],
];
if(($participantPage??'')==='form'&&!empty($id)){
    $participantTabs['form']=['panel/peserta/form.php?id='.(int)$id,'edit','Edit siswa'];
}
?>
<nav class="participant-page-tabs" aria-label="Pengelolaan data siswa">
<?php foreach($participantTabs as $tab=>[$path,$icon,$label]): ?>
<a data-feature="<?= e($tab) ?>" href="<?= e(base_url($path)) ?>" <?= ($participantPage??'')===$tab?'aria-current="page"':'' ?>><?= panel_icon($icon) ?> <?= e($label) ?></a>
<?php endforeach; ?>
</nav>
