<?php

use Yandex\DiskManager;

require_once('vendor/autoload.php');
require_once('Yandex/DiskManager.php');
require_once('token.php');

$dirPath = $_GET['dir'] ?? 'disk:/';
$manager = new DiskManager(OAUTH_TOKEN, urldecode($dirPath));

if (!empty($_POST['folder_name'])) {
    $folderName = $_POST['folder_name'];
    $manager->createFolder($folderName);
}

if (!empty($_FILES['file']['name'])) {
    $file = $_FILES['file'];
    $manager->uploadFile($file);
}
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
        <section class="col-menu">
            <form action="" method="post">
                <div class="form-container">
                    <input type="text" name="folder_name" placeholder="Название папки" class="form-input">
                    <button type="submit" class="form-button">Создать</button>
                </div>
            </form>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-container">
                    <input type="file" name="file" class="form-input">
                    <button type="submit" class="form-button">Загрузить</button>
                </div>
            </form>
        </section>
        <section id="resources">
        <?php
        echo $manager->displayItems();
        ?>
        </section>
    </div>
</body>
</html>
