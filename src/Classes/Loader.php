<?php

namespace Classes;

use Slim\Http\UploadedFile;
use Exception;

class Loader extends MainClass
{
    private function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        if (!in_array($extension, ["png", "jpg", "jpeg", "gif"])) {
            return false;
        }
        try {
            $basename = bin2hex(random_bytes(8));
        } catch (Exception $e) {
            return "error";
        }
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    public function loadPhoto(Array $photos, $dirName)
    {
        if (count($photos) > 3 || count($this->qb->filterDataByCol("photos", "user_id",
                $_SESSION["logged"]["user_id"])) > 3) {
            throw new Exception("More then 5 photos");
        }
        foreach ($photos as $photo) {
            if ($photo->getError() === UPLOAD_ERR_OK) {
                $fileName = $this->moveUploadedFile($dirName, $photo);
                if ($fileName) {
                    $this->qb->insertDataIntoTable("photos", [
                        "user_id" => $_SESSION["logged"]["user_id"],
                        "photo" =>
                            "/data/" . $_SESSION["logged"]["login"] . DIRECTORY_SEPARATOR . $fileName
                    ]);
                }
            } else {
                throw new Exception("Upload error");
            }
        }
        return true;
    }

    public function loadAvatar($avatar, $dirName)
    {
        $fileName = $this->moveUploadedFile($dirName, $avatar);
        if ($fileName) {
            $this->qb->insertDataIntoTable("avatars", [
                "user_id" => $_SESSION["logged"]["user_id"],
                "avatar" =>
                    "/data/" . $_SESSION["logged"]["login"] . DIRECTORY_SEPARATOR . $fileName
            ]);
        } else {
            throw new Exception("Upload Error");
        }

        return true;

    }
}
