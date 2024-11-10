<?php

use Trolleyfun\Yandex\FolderManager;

require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/token.php');

session_start();

$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

$arFolder = [];
$arFolder['path']= $_GET['path'] ?? 'disk:/';
$folder = new FolderManager(OAUTH_TOKEN, urldecode($arFolder['path']));
$arFolder['newFolderName'] = '';
$arFolder['isRoot'] = $folder->isRoot();
$arFolder['name'] = $folder->getName();
$arFolder['itemsListHtml'] = $folder->displayItems();

if (isset($_POST['create_folder_button'])) {
    $arFolder['newFolderName'] = $_POST['folder_name'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $folder->createFolder($arFolder['newFolderName']);
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

if (isset($_POST['update_folder_button'])) {
    $arFolder['name'] = $_POST['folder_name'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $folder->changeFolderName($arFolder['name']);
    }
    header('Location: index.php?path=' . urlencode($folder->getPath()));
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
                    <input type="text" name="folder_name" placeholder="Название папки" class="form-input"
                        value="<?=htmlspecialchars($arFolder['newFolderName'])?>">
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

        <!-- Список файлов и папок -->
        <section id="resources">
        <?php if ($arFolder['isRoot']): ?>
            <h1>Папка: <?=htmlspecialchars($arFolder['name'])?></h1>
        <?php else: ?>
            <form id="update-folder" action="" method="post">
                <div class="form-container">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <label for="folder_name">Папка:</label>
                    <input type="text" name="folder_name" id="folder_name"
                        value="<?=htmlspecialchars($arFolder['name'])?>" class="form-input">
                    <button type="submit" name="update_folder_button" class="form-button">Переименовать</button>
                </div>
            </form>
        <?php endif; ?>

            <form id="delete-items" action="" method="post">
                <div class="resource-container">
                    <?=$arFolder['itemsListHtml']?>
                </div>
                <div class="form-container">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <button id="select-all" class="form-button">Выбрать все</button>
                    <button id="unselect-all" class="form-button">Снять выделение</button>
                    <button type="submit" name="delete_item_button"
                        class="form-button">Удалить выбранные</button>
                </div>
            </form>
        </section>
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
