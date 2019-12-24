<?php   

require_once "QueryBuilder.php";

// Создаём объект базы данных
$qr = QueryBuilder::getInstance("users"); 
/*$users = $qr->users1
	->join("posts", "user_id")
	->join("images", "post_id", "posts.id")
	->andWhere("id", "<>", 4)
	->asc()
	->groupBy("users.name")
	->select();*/

/*$users = $qr->table("users1")
    ->where("login", "=", "fe", "")
    ->where("id", "", "13", "AND")
	->select();*/

/*$users = $qr->table("users1")
    ->join("posts", "user_id")
    ->join("images", "posts_id", "posts.id")
    ->where("id", "4")
    ->where("id", "<>", "3", "AND")
    ->where("id", "=", "2", "OR")
    ->sortAscDesc()
    ->groupBy("users.name")
	->select();*/

/*$users1 = $qr->table("users1")
    ->where("last_name", "Markoski")
    ->where("id", ">", 2, "AND")
    ->where("name", "<>", "2fer", "OR")
    ->sortAscDesc("contact_id", "DESC")
    ->limit(1)
	->delete();*/

/*$users1 = $qr->table("users1")
	->insert(array('login' => 'john', 'password' => 111));*/

$users1 = $qr->table("users1")
	->where("id", "4")
	->set(array('login' => 'john', 'password' => 111))
	->update();

//var_dump($users);
//echo "<br>";
var_dump($users1);



