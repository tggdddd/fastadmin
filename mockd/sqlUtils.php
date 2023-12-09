<?php
$connect;
function initDB(string $host = "127.0.0.1", string $user = "root", string $pass = "root", string $db = "company", $port = "3306")
{
    global $connect;
    $connect = mysqli_connect($host, $user, $pass, $db, $port);
    return $connect;
}

function getConnect()
{
    global $connect;
    if (!isset($connect)) {
        initDB();
    }
    return $connect;
}

function query_one(string $sql, string $class)
{
    $result = mysqli_query(getConnect(), $sql, MYSQLI_ASSOC);
    if (!$result) {
        if ($class) {
            new $class();
        }
    }
    return $result;
}

function query_all(string $sql)
{
    $result = mysqli_query(getConnect(), $sql);
    if (!$result) {
        return false;
    }
    return mysqli_fetch_all($result);
}

?>