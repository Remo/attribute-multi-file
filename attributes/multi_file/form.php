<div class="multi-file">
    <?php
    $uid = 'dropzone' . uniqid();
    ?>
    <div class="file-list">
        <?php foreach ($files as $file) { ?>
            <div class="file" id="file_<?= $file->getFileID() ?>">
                <?= $file->getListingThumbnailImage() ?>
                <div class="file-name">
                    <?= $file->getFileName() ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="dropzone">
        <div id="preview" class="dropzone-previews"></div>
    </div>

    <div id="<?= $uid ?>" class="dropzone"></div>
    <input type="hidden" name="<?= $this->field('value') ?>" id="session-key-<?= $uid ?>">
    <input type="hidden" name="<?= $this->field('fsID') ?>" value="<?= $fsID ?>">
    <input type="hidden" name="<?= $this->field('sortOrder') ?>" id="file-sort-<?= $uid ?>" value="">

    <script type="text/javascript">
        $("#<?=$uid?>").dropzone(
            {
                uploadMultiple: true,
                previewsContainer: '#preview',
                url: '<?=View::url('/attribute_multi_file/upload/')?>',
                acceptedFiles: <?=json_encode($typeValues['fileTypes'])?>,
                success: function (file, response) {
                    $("#session-key-<?=$uid?>").val(response);
                }
            }
        );

        $fileContainer = $("#<?=$uid?>").parent().find(".file-list");
        $fileContainer.sortable();
        $fileContainer.on("sortupdate", function () {
            var data = $(this).sortable('serialize');
            $("#file-sort-<?=$uid?>").val(data);
        });
    </script>
</div>