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
</fieldset>