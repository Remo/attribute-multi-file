<?php

namespace Concrete\Package\AttributeMultiFile\Attribute\MultiFile;

use Database,
    Loader,
    Core,
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
            $value = $db->GetRow('SELECT fileTypes FROM atMultiFileSettings WHERE akID = ?', [$ak->getAttributeKeyID()]);
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
        $this->set('fileTypes', preg_split('[\|]', $typeValues['fileTypes']));
        $this->set('availableFileTypes', [
            'jpg' => t('JPG'),
            'png' => t('PNG'),
            'gif' => t('GIF'),
        ]);
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
            'fileTypes' => join('|', $data['fileTypes']),
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
    protected function getFileSetID() {
        $db = Database::connection();
        $fsID = $db->GetOne('SELECT fsID FROM atMultiFile WHERE avID = ?', [$this->getAttributeValueID()]);
        return $fsID;
    }

    /**
     * Returns a list of files connected to the current attribute instance
     * @return array
     */
    protected function getFiles() {
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

        if (!is_array($files)) {
            return;
        }

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

        // Import files
        foreach ($files as $file) {
            $fi = new FileImporter();
            $fileVersion = $fi->import($file['fileName'], $file['name']);
            unlink($file['fileName']);

            if ($fileVersion instanceof Version) {
                $fileSet->addFileToSet($fileVersion);
            }
            else {
                // @TODO now what?
                switch ($fileVersion) {
                    case FileImporter::E_FILE_INVALID_EXTENSION:
                        break;
                    case FileImporter::E_FILE_INVALID:
                        break;
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

}