<?php

session_start();

if (empty($_SESSION['oauth_token'])) {
    header('Location: login.php');
}

$arErrors = ['Запрашиваемый ресурс не найден.'];

include('includes/header.php');
include('includes/errors.php');
include('includes/footer.php');
