<?php

namespace Classes;
use Exception;

class Liker extends MainClass
{
    public function addLike($fromUserId, $toUserId, $date)
    {
        $like = $this->qb->filterDataByCond("likes", [
            "user_id_to" => $toUserId,
            "user_id_from" => $fromUserId,
        ])[0];

        if (!empty($like)) {
            $this->qb->deleteRowByCond("likes", [
                "user_id_to" => $toUserId,
                "user_id_from" => $fromUserId,
            ]);
            return true;
        }

        if (!$this->qb->insertDataIntoTable("likes", [
            "user_id_from" => $fromUserId,
            "user_id_to" => $toUserId,
            "created_at" => $date
        ])) {
            throw new Exception("Like was not added");
        } else
            return true;
    }
}
