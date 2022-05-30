<?php
session_start();
$servername = "localhost";
$database = "exchangerates";	//название бд
$username = "root";
$password = "";
// Создаем соединение
$link = mysqli_connect($servername, $username, $password, $database);
// Проверяем соединение
?>
