<?php defined('C5_EXECUTE') or die('Access Denied.') ?>
<fieldset>
    <legend><?= t('Supported File Types') ?></legend>

    <?php foreach ($availableFileTypes as $fileType => $fileTypeName) { ?>
        <label>
            <input <?= in_array($fileType, $fileTypes) ? 'checked' : '' ?> type="checkbox" name="fileTypes[]"
                                                                           value="<?= $fileType ?>">
            <?= $fileTypeName ?>
        </label>
    <?php } ?>

    <legend><?= t('Maximum Number of Files') ?></legend>
    <input type="text" name="maximumFiles" value="<?= $maximumFiles ?>">


    <legend><?= t('File Link Type') ?> &nbsp;&nbsp; <i class="fa fa-info-circle tooltip-primary" data-toggle="tooltip" data-placement="right" title="" data-original-title="<?php echo t("If you want to share file link for only registered user select 'Private Link' otherwise select 'Public Link' ")?>"></i></legend>
    <input type="radio" name="fileLinkType" id="privateLink" value="1" <?= $fileLinkType == 1 ? 'checked' : ''?>> <label for="privateLink">Private Link</label><br>
    <input type="radio" name="fileLinkType" id="publicLink" value="0" <?= $fileLinkType == 0 ? 'checked' : ''?>> <label for="publicLink">Public Link</label>
</fieldset>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    })
</script>