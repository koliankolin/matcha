<?php

namespace Classes;

use PDO;

class Finder extends MainClass
{
    public function findPerson(array $cond)
    {
        if (empty($cond["sex"])) {
            $cond["sex"] = 3;
        }
        $whoLikedSql = "
            SELECT * FROM likes l
            LEFT JOIN users u ON l.user_id_from = u.id
            LEFT JOIN users_info ui ON ui.user_id = l.user_id_from
            WHERE 1=1
                AND l.user_id_to = {$_SESSION["logged"]["user_id"]}
                AND ui.sex = {$cond["sex"]}
            ";

        $whoLiked = $this->db->query($whoLikedSql)->fetchAll(PDO::FETCH_ASSOC);

        // Interests
        $sameInterests = [];
        if (!empty($cond["interests"])) {
            foreach ($cond["interests"] as $interest) {
                $interestSql = "
                SELECT * FROM tags_users tu
                LEFT JOIN users u ON tu.user_id = u.id
                LEFT JOIN users_info ui ON ui.user_id = tu.user_id
                WHERE 1=1
                    AND tu.tag_id = {$interest}
                    AND ui.sex = {$cond["sex"]}
                ";

                $sameInterests = array_merge($sameInterests, $this->db->query($interestSql)->fetchAll(PDO::FETCH_ASSOC));
            }
        }

        return array_unique(array_merge($whoLiked, $sameInterests));


    }
}
