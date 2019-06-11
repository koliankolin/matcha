<?php

require "Classes/QueryBuilder.php";

$db = new PDO("mysql:host=db;dbname=myDb", "root", "test");
$queryBuilder = new QueryBuilder(["db" => $db]);

//$queryBuilder->createTable("users", [
//    "id" => "INT NOT NULL AUTO_INCREMENT",
//    "login" => "VARCHAR(255) NOT NULL",
//    "email" => "VARCHAR(255) NOT NULL",
//    "password" => "VARCHAR(255) NOT NULL",
//    "admin" => "BOOLEAN NOT NULL DEFAULT false",
//    "hash" => "VARCHAR(255) NOT NULL",
//    "activated" => "BOOLEAN NOT NULL DEFAULT false"
//]);

//$queryBuilder->createTable("photos", [
//    "id" => "INT NOT NULL AUTO_INCREMENT",
//    "user_id" => "INT NOT NULL",
//    "photo" => "VARCHAR(255) NOT NULL",
//], "user_id", "users");

$queryBuilder->createTable("users_info", [
    "id" => "INT NOT NULL AUTO_INCREMENT",
    "user_id" => "INT NOT NULL",
    "sex" => "INT",
    "first_name" => "VARCHAR(255)",
    "surname" => "VARCHAR(255)",
    "sex_pref_id" => "INT",
    "biography" => "TEXT",
], "user_id", "users");

//$queryBuilder->createTable("comments", [
//    "id" => "INT NOT NULL AUTO_INCREMENT",
//    "photo_id" => "INT NOT NULL",
//    "comment" => "TEXT NOT NULL"
//], "photo_id", "photos");
//
//$queryBuilder->createTable("likes", [
//    "id" => "INT NOT NULL AUTO_INCREMENT",
//    "photo_id" => "INT NOT NULL",
//    "user_id" => "INT NOT NULL",
//    "login_who_likes" => "VARCHAR(255) NOT NULL"
//], "photo_id", "photos");
//
//$queryBuilder->createTable("sex_prefs", [
//    "id" => "INT NOT NULL AUTO_INCREMENT",
//    "name" => "VARCHAR(255)",
//]);
//
//$queryBuilder->createTable("tags", [
//    "id" => "INT NOT NULL AUTO_INCREMENT",
//    "name" => "VARCHAR(255)",
//]);
//
//$queryBuilder->createTable("tags_users", [
//    "id" => "INT NOT NULL AUTO_INCREMENT",
//    "tag_id" => "INT NOT NULL",
//    "user_id" => "INT NOT NULL",
//], "tag_id", "tags");