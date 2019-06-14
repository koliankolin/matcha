<?php

namespace Classes;

class Deleter extends MainClass
{
    public function deletePhoto($photoId)
    {
        $path = $this->qb->filterDataByCol("photos", "id", $photoId)[0]["photo"];
        if ($this->qb->deleteRowByCond("photos", [
            "id" => $photoId
        ])) {
            return unlink("/var/www/html/" . $path);
        }
        return false;
    }

    public function deleteAvatar($avatarId)
    {
        $path = $this->qb->filterDataByCol("avatars", "id", $avatarId)[0]["avatar"];
        if ($this->qb->deleteRowByCond("avatars", [
            "id" => $avatarId
        ])) {
            return unlink("/var/www/html/" . $path);
        }
        return false;
    }
}
