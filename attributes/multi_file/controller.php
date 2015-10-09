<?php

namespace Concrete\Package\AttributeMultiFile\Attribute\MultiFile;

use Concrete\Core\File\File,
    Database,
    View,
    Concrete\Core\File\Version,
    \Concrete\Core\Attribute\Controller as AttributeTypeController,
    FileImporter,
    FileSet;

class Controller extends AttributeTypeController
{
    public $helpers = ['form'];

    /**
     * Returns the configuration values
     * @return array
     */
    public function getTypeValues()
    {
        $db = Database::connection();
        $ak = $this->getAttributeKey();
        $value = [];
        if (is_object($ak)) {
            $value = $db->GetRow('SELECT fileTypes, maximumFiles FROM atMultiFileSettings WHERE akID = ?', [$ak->getAttributeKeyID()]);
        }
        return $value;
    }

    public function getValue()
    {
        return $this->getFiles();
    }

    /**
     * Shows the attribute configuration form
     */
    public function type_form()
    {
        $typeValues = $this->getTypeValues();
        $this->set('fileTypes', preg_split('[,]', $typeValues['fileTypes']));
        $this->set('maximumFiles', isset($typeValues['maximumFiles']) ? $typeValues['maximumFiles'] : 100);
        $this->set('availableFileTypes', $this->getFileTypes());
    }

    protected function getFileTypes()
    {
        $mimeTypes = \Concrete\Core\File\Service\Mime::$mime_types_and_extensions;
        $fileTypes = [];

        foreach ($mimeTypes as $extension => $mimeType) {
            $fileTypes[".{$extension}"] = t($extension);
        }

        return $fileTypes;
    }

    /**
     * Saves the attribute configuration
     * @param array $data
     */
    public function saveKey($data)
    {
        $ak = $this->getAttributeKey();
        $db = Database::connection();

        $db->Replace('atMultiFileSettings', [
            'akID' => $ak->getAttributeKeyID(),
            'fileTypes' => is_array($data['fileTypes']) ? join(',', $data['fileTypes']) : '',
            'maximumFiles' =>  $data['maximumFiles'],
        ], ['akID'], true);
    }

    /**
     * Shows the value, the HTML text in the form
     */
    public function form()
    {
        $view = View::getInstance();
        $view->requireAsset('dropzone');

        $this->set('typeValues', $this->getTypeValues());
        $this->set('fsID', $this->getFileSetID());
        $this->set('files', $this->getFiles());
    }

    /**
     * Returns the file set id connected to this attribute instance. Will be null in case the attribute hasn't been
     * saved yet.
     * @return int
     */
    protected function getFileSetID()
    {
        $db = Database::connection();
        $fsID = $db->GetOne('SELECT fsID FROM atMultiFile WHERE avID = ?', [$this->getAttributeValueID()]);
        return $fsID;
    }

    /**
     * Returns a list of files connected to the current attribute instance
     * @return array
     */
    protected function getFiles()
    {
        $fsID = $this->getFileSetID();
        $files = FileSet::getFilesBySetID($fsID);
        return $files ?: [];
    }

    /**
     * Called when we're searching using an attribute.
     * @param $list
     */
    public function searchForm($list)
    {
    }

    /**
     * Called when we're saving the attribute from the frontend.
     * @param $data
     */
    public function saveForm($data)
    {
        $sessionKey = $data['value'];
        $files = $_SESSION['multi_file'][$sessionKey];

        $db = Database::connection();

        // create or get file set
        $fileSetName = sprintf('Multi File %s', date('Y-m-d'));
        $fileSet = FileSet::getByID($data['fsID']);
        if (!$fileSet->getFileSetID()) {
            $fileSet = FileSet::add($fileSetName);
        }

        $db->Replace(
            'atMultiFile',
            array(
                'avID' => $this->getAttributeValueID(),
                'fsID' => $fileSet->getFileSetID(),
            ),
            'avID',
            true
        );

        // Update sort order of files
        if (isset($data['sortOrder']) && !empty($data['sortOrder'])) {
            $sortOrder = $data['sortOrder'];
            parse_str($sortOrder, $sortOrderArray);

            $fileSet->updateFileSetDisplayOrder($sortOrderArray['file']);
        }

        // Remove files
        if (isset($data['removeFiles']) && !empty($data['removeFiles'])) {
            $removeFiles = preg_split('[,]', $data['removeFiles'], -1, PREG_SPLIT_NO_EMPTY);
            foreach ($removeFiles as $fID) {
                $file = File::getByID($fID);
                $file->delete();
            }
        }

        // Import files
        if (is_array($files)) {
            foreach ($files as $file) {
                $fi = new FileImporter();
                $fileVersion = $fi->import($file['fileName'], $file['name']);
                unlink($file['fileName']);

                if ($fileVersion instanceof Version) {
                    $fileSet->addFileToSet($fileVersion);
                } else {
                    // @TODO now what?
                    switch ($fileVersion) {
                        case FileImporter::E_FILE_INVALID_EXTENSION:
                            break;
                        case FileImporter::E_FILE_INVALID:
                            break;
                    }
                }
            }
        }

        // Clear session
        unset($_SESSION['multi_file'][$sessionKey]);
    }

    /**
     * Called when the attribute is edited in the composer.
     */
    public function composer()
    {
        $this->form();
    }

    public function deleteKey()
    {
        $db = Database::connection();
        $arr = $this->attributeKey->getAttributeValueIDList();
        foreach ($arr as $id) {
            $db->Execute('DELETE FROM atMultiFile WHERE avID = ?', [$id]);
        }
        $db->Execute('delete from atSelectOptions where akID = ?', array($this->attributeKey->getAttributeKeyID()));
    }

    public function deleteValue()
    {
        $db = Database::connection();
        $db->Execute('DELETE FROM atMultiFile WHERE avID = ?', [$this->getAttributeValueID()]);
    }

}