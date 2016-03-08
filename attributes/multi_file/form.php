<?php
defined('C5_EXECUTE') or die('Access Denied.');
$uid = 'dropzone' . uniqid();
?>
<div class="multi-file" id="dropzone-container-<?= $uid ?>">
    <div id="<?= $uid ?>" class="dropzone">
        <div class="file-list">
            <?php foreach ($files as $file) { ?>
                <div class="file" id="file_<?= $file->getFileID() ?>" data-id="<?= $file->getFileID() ?>">
                    <?= $file->getListingThumbnailImage() ?>
                    <div class="file-name">
                        <?= $file->getFileName() ?>
                    </div>
                    <div class="remove-file">
                        <i class="fa fa-times"></i>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div id="preview-<?=$uid?>" class="dropzone-previews"></div>

        <div class="<?= count($files) >= $typeValues['maximumFiles'] ? ' hidden' : '' ?>"></div>
    </div>
    <input type="hidden" name="<?= $this->field('value') ?>" value="<?= uniqid() ?>" id="session-key-<?= $uid ?>">
    <input type="hidden" name="linkType" value="<?= $typeValues['linkType'] ?>" id="linkType<?= $uid ?>">
    <input type="hidden" name="<?= $this->field('fsID') ?>" value="<?= $fsID ?>">
    <input type="hidden" name="<?= $this->field('sortOrder') ?>" id="file-sort-<?= $uid ?>" value="">
    <input type="hidden" name="<?= $this->field('removeFiles') ?>" id="remove-files-<?= $uid ?>" value="">

    <div class="clearfix"></div>

    <script type="text/javascript">
        $(document).ready(function() {
            Dropzone.autoDiscover = false;
            $("#<?=$uid?>").dropzone(
                {
                    init: function () {
                        <?=$uid?> = this;
                    },
                    uploadMultiple: true,
                    previewsContainer: '#preview-<?=$uid?>',
                    url: '<?=View::url('/attribute_multi_file/upload/')?>/' + $("#session-key-<?= $uid ?>").val(),
                    acceptedFiles: <?=json_encode($typeValues['fileTypes'])?>,
                    maxFiles: <?=$typeValues['maximumFiles'] - count($files) ?>
                }
            );

            $("#dropzone-container-<?=$uid?>").on("click", ".remove-file", function() {
                var $file = $(this).parent(".file");
                $("#remove-files-<?= $uid ?>").val($("#remove-files-<?= $uid ?>").val() + $file.data("id") + ",");
                $file.remove();
                <?=$uid?>.options.maxFiles = <?=$typeValues['maximumFiles']?> - $("#dropzone-container-<?=$uid?>").find(".file").length;
            });

            $fileContainer = $("#<?=$uid?>").parent().find(".file-list");
            $fileContainer.sortable();
            $fileContainer.on("sortupdate", function () {
                var data = $(this).sortable('serialize');
                $("#file-sort-<?=$uid?>").val(data);
            });
        });
    </script>
</div>
