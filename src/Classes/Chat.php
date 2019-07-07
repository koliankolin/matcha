<?php

namespace Classes;

class Chat extends MainClass
{
    public function saveMessage($message, $userIdFrom, $userIdTo)
    {
        if (!$this->qb->insertDataIntoTable("messages", [
            "user_id_from" => $userIdFrom,
            "user_id_to" => $userIdTo,
            "message" => $message,
            "created_at" => date("Y-m-d H:i:s")
        ])) {
            throw new \Exception("Message didn't add");
        }
        return true;
    }
}
