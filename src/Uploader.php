<?php
namespace Concrete\Package\AttributeMultiFile\Src;

use Controller,
    Loader;

class Uploader extends Controller
{
    public function upload()
    {
        $fh = Loader::helper('file');
        $files = [];
        $tempKey = uniqid();
        $_SESSION['multi_file'][$tempKey] = [];
        foreach ($_FILES['file']['tmp_name'] as $key => $uploadedFile) {
            $name = $_FILES['file']['name'][$key];
            $tmpName = tempnam($fh->getTemporaryDirectory(), 'img');

            if (move_uploaded_file($uploadedFile, $tmpName)) {
                $_SESSION['multi_file'][$tempKey][] = ['fileName' => $tmpName, 'name' => $name];
            }
        }
        echo $tempKey;
    }
}