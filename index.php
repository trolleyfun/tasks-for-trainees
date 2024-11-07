<?php

use Arhitector\Yandex\Disk;




require_once('vendor/autoload.php');
require_once('token.php');

$disk = new Disk(OAUTH_TOKEN);

$resource = $disk->getResource('/Test/Test1/Test2');


$collection = $resource->get('items');

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walle</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav>
        <a href="index.php">Главная</a>
    </nav>
    <div class="container">
        <section id="resources">
            <a href="index.php?dir=Папка1" class="resource-item">
                <input type="checkbox">
                <img src="images/folder.svg" alt="">
                <h1>Папка1</h1>
            </a>
            <a href="index.php?dir=Папка2" class="resource-item">
                <input type="checkbox">
                <img src="images/folder.svg" alt="">
                <h1>Папка2</h1>
            </a>
        </section>
    </div>
</body>
</html>

