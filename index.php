<?php

use Trolleyfun\Yandex\FolderManager;

require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/token.php');

session_start();

$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

$dirPath = $_GET['dir'] ?? 'disk:/';
$folder = new FolderManager(OAUTH_TOKEN, urldecode($dirPath));

if (!empty($_POST['folder_name'])) {
    $folderName = $_POST['folder_name'];
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $folder->createFolder($folderName);
    } else {
        header('Location: ' . $_SERVER['REQUEST_URI']);
    }
}

if (!empty($_FILES['file']['name'])) {
    $file = $_FILES['file'];
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $folder->uploadFile($file);
    } else {
        header('Location: ' . $_SERVER['REQUEST_URI']);
    }
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
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <input type="text" name="folder_name" placeholder="Название папки" class="form-input">
                    <button type="submit" class="form-button">Создать</button>
                </div>
            </form>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-container">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <input type="file" name="file" class="form-input">
                    <button type="submit" class="form-button">Загрузить</button>
                </div>
            </form>
        </section>
        <section id="resources">
        <?php
        echo $folder->displayItems();
        ?>
        </section>
    </div>
</body>
</html>
