<?php

require "Classes/QueryBuilder.php";

use Classes\QueryBuilder;

$db = new PDO("mysql:host=db;dbname=myDb", "root", "test");
$queryBuilder = new QueryBuilder(["db" => $db]);

if (
$queryBuilder->createTable("users", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "login" => "VARCHAR(255) NOT NULL",
    "email" => "VARCHAR(255) NOT NULL",
    "password" => "VARCHAR(255) NOT NULL",
    "admin" => "BOOLEAN NOT NULL DEFAULT false",
    "hash" => "VARCHAR(255) NOT NULL",
    "activated" => "BOOLEAN NOT NULL DEFAULT false"
])

    &&

$queryBuilder->createTable("photos", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "user_id" => "INT NOT NULL",
    "photo" => "VARCHAR(255) NOT NULL",
], "user_id", "users")

    &&

$queryBuilder->createTable("avatars", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "user_id" => "INT NOT NULL",
    "avatar" => "VARCHAR(255) NOT NULL",
], "user_id", "users")

    &&

$queryBuilder->createTable("users_info", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "user_id" => "INT NOT NULL",
    "sex" => "INT",
    "first_name" => "VARCHAR(255)",
    "surname" => "VARCHAR(255)",
    "sex_pref_id" => "INT",
    "biography" => "TEXT",
], "user_id", "users")

    &&

$queryBuilder->createTable("comments", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "photo_id" => "INT NOT NULL",
    "comment" => "TEXT NOT NULL"
], "photo_id", "photos")

    &&

$queryBuilder->createTable("likes", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "user_id_from" => "INT NOT NULL",
    "user_id_to" => "INT NOT NULL",
    "created_at" => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"
], "user_id_from", "users")

    &&

$queryBuilder->createTable("views", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "user_id_from" => "INT NOT NULL",
    "user_id_to" => "INT NOT NULL",
    "created_at" => "TIMESTAMP NOT NULL"
], "user_id_from", "users")

    &&

$queryBuilder->createTable("sex_prefs", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "name" => "VARCHAR(255)",
])

    &&

$queryBuilder->insertDataIntoTable("sex_prefs", ["female"], false, true)

    &&

    $queryBuilder->insertDataIntoTable("sex_prefs", ["male"], false, true)

    &&


    $queryBuilder->insertDataIntoTable("sex_prefs", ["everyone"], false, true)

    &&

$queryBuilder->createTable("tags", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "name" => "VARCHAR(255)",
])

    &&

$queryBuilder->createTable("tags_users", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "tag_id" => "INT NOT NULL",
    "user_id" => "INT NOT NULL",
], "tag_id", "tags"))
    echo "Tables were uploaded";
else
    echo "Tables were NOT uploaded";