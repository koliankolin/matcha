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
}
