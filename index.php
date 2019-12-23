<?php   

require_once "QueryBuilder.php";

// Создаём объект базы данных
$qr = QueryBuilder::getInstance(); 
/*$users = $qr->users1
	->join("posts", "user_id")
	->join("images", "post_id", "posts.id")
	->andWhere("id", "<>", 4)
	->asc()
	->groupBy("users.name")
	->select();*/

$users = $qr->users1
    ->where("login", "=", "fe")
	->select();

var_dump($users);



