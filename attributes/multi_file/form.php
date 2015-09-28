<?php
$uid = 'dropzone' . uniqid();
?>
<?php foreach ($files as $file) { ?>
<div><?=$file->getListingThumbnailImage()?></div>
<?php } ?>

<div id="<?= $uid ?>" class="dropzone"></div>
<input type="hidden" name="<?= $this->field('value') ?>" id="session-key-<?= $uid ?>">
<input type="hidden" name="<?= $this->field('fsID') ?>" value="<?= $fsID ?>">

<script type="text/javascript">
    $("#<?=$uid?>").dropzone(
        {
            uploadMultiple: true,
            url: '<?=View::url('/attribute_multi_file/upload/')?>',
            acceptedFiles: <?=json_encode($typeValues['fileTypes'])?>,
            success: function (file, response) {
                $("#session-key-<?=$uid?>").val(response);
            }
        }
    );
</script>
