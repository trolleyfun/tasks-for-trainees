<?php

use Trolleyfun\Yandex\FolderManager;

require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/token.php');

session_start();

$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

$dirPath = $_GET['path'] ?? 'disk:/';
$folder = new FolderManager(OAUTH_TOKEN, urldecode($dirPath));

if (isset($_POST['create_folder_button'])) {
    $folderName = $_POST['folder_name'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $folder->createFolder($folderName);
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
}

if (isset($_POST['upload_file_button'])) {
    $file = $_FILES['file'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $folder->uploadFile($file);
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
}

if (isset($_POST['delete_item_button'])) {
    $arItemPath = $_POST['item_path'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'], $csrfToken) && is_array($arItemPath)) {
        foreach ($arItemPath as $path) {
            $folder->deleteResource($path);
        }
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walle &bull; Disk Manager</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="favicon.ico">
</head>
<body>
    <nav>
        <a href="index.php">Главная</a>
    </nav>
    <div class="container">
        <!-- Верхнее меню -->
        <section id="top-menu" class="col-menu">
            <form id="create-folder" action="" method="post">
                <div class="form-container">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <input type="text" name="folder_name" placeholder="Название папки" class="form-input">
                    <button type="submit" name="create_folder_button" class="form-button">Создать</button>
                </div>
            </form>
            <form id="upload-file" action="" method="post" enctype="multipart/form-data">
                <div class="form-container">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <input type="file" name="file" class="form-input">
                    <button type="submit" name="upload_file_button" class="form-button">Загрузить</button>
                </div>
            </form>
        </section>

        <form id="delete-items" action="" method="post">
            <!-- Список файлов и папок -->
            <section id="resources">
                <h1>Папка: <?=$folder->getName()?></h1>
                <div class="resource-container">
                <?php
                echo $folder->displayItems();
                ?>
                </div>
            </section>

            <!-- Нижнее меню -->
            <section id="bottom-menu" class="col-menu">
                <div class="form-container">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <button id="select-all" class="form-button">Выбрать все</button>
                    <button id="unselect-all" class="form-button">Снять выделение</button>
                    <button type="submit" name="delete_item_button"
                        class="form-button">Удалить выбранные</button>
                </div>
            </section>
        </form>
    </div>

    <!-- JQuery -->
    <script
        src="https://code.jquery.com/jquery-3.7.1.min.js"
		integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
		crossorigin="anonymous"></script>

    <!-- Пользовательские скрипты -->
    <script src="js/scripts.js"></script>
</body>
</html>
