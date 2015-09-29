<?php

namespace Concrete\Package\AttributeMultiFile;

use Concrete\Core\Backup\ContentImporter,
    Concrete\Core\Asset\AssetList,
    Route,
    Package;

class Controller extends Package
{
    protected $pkgHandle = 'attribute_multi_file';
    protected $appVersionRequired = '5.7.5';
    protected $pkgVersion = '0.9.1';

    public function getPackageName()
    {
        return t('Multi File Upload attribute');
    }

    public function getPackageDescription()
    {
        return t('Installs a multi file upload attribute');
    }

    protected function installXmlContent()
    {
        $pkg = Package::getByHandle($this->pkgHandle);

        $ci = new ContentImporter();
        $ci->importContentFile($pkg->getPackagePath() . '/install.xml');
    }

    public function install()
    {
        parent::install();

        $this->installXmlContent();
    }

    public function upgrade()
    {
        parent::upgrade();

        $this->installXmlContent();
    }

    public function on_start()
    {
        $al = AssetList::getInstance();
        $al->register(
            'javascript', 'dropzone', 'js/dropzone.js', ['position' => \Concrete\Core\Asset\Asset::ASSET_POSITION_FOOTER], $this->pkgHandle
        );
        $al->register(
            'css', 'dropzone', 'css/dropzone.css', ['position' => \Concrete\Core\Asset\Asset::ASSET_POSITION_FOOTER], $this->pkgHandle
        );
        $al->register(
            'css', 'multifile', 'css/multifile.css', ['position' => \Concrete\Core\Asset\Asset::ASSET_POSITION_FOOTER], $this->pkgHandle
        );
        $al->registerGroup('dropzone', [
            ['javascript', 'dropzone'],
            ['css', 'dropzone'],
            ['css', 'multifile'],
        ]);

        Route::register('/attribute_multi_file/upload', '\Concrete\Package\AttributeMultiFile\Src\Uploader::upload');
    }
}