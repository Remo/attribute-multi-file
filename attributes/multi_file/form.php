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
    <input type="hidden" name="<?= $this->field('fsID') ?>" value="<?= $fsID ?>">
    <input type="hidden" name="<?= $this->field('sortOrder') ?>" id="file-sort-<?= $uid ?>" value="">
    <input type="hidden" name="<?= $this->field('removeFiles') ?>" id="remove-files-<?= $uid ?>" value="">

    <div class="clearfix"></div>

    <script type="text/javascript">
        $(document).ready(function() {
            var maxImageWidth = 0,
                maxImageHeight = 0;
            <?php if(isset($typeValues['maximumWidth']) && $typeValues['maximumWidth'] > 0){ ?>
                maxImageWidth = <?=$typeValues['maximumWidth']; ?>;
            <?php }
            if(isset($typeValues['maximumHeight']) && $typeValues['maximumHeight'] > 0){
            ?>
                maxImageHeight = <?=$typeValues['maximumHeight']; ?>;
            <?php } ?>
            Dropzone.autoDiscover = false;
            $("#<?=$uid?>").dropzone(
                {
                    init: function () {
                        <?=$uid?> = this;
                    },
                    accept: function(file, done) {
                        var ValidImageTypes = ["image/gif", "image/jpeg", "image/png", "image/jpg"],
                            fileType = file.type;
                        if ($.inArray(fileType, ValidImageTypes) > 0) {
                            var pixels = 0;
                            var reader = new FileReader();
                            reader.onload = (function (file) {
                                var fileType = file.type,
                                    fileWidth = file.width,
                                    fileHeight = file.height;

                                var image = new Image();
                                image.src = file.target.result;
                                image.onload = function () {
                                    if ((maxImageWidth > 0 && this.width > maxImageWidth) || (maxImageHeight > 0 && this.height > maxImageHeight)) {
                                            done("Image dimension should be " + maxImageWidth + " X " + maxImageHeight + ".");
                                    } else {
                                        done();
                                    }
                                };
                            });
                            reader.readAsDataURL(file);
                        }else{
                            done();
                        }
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
