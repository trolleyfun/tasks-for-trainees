<?php

use Trolleyfun\Yandex\FileManager;

require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/token.php');

session_start();

$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

$arFile = [];
$arFile['path'] = $_GET['path'] ?? 'disk:/';
$file = new FileManager(OAUTH_TOKEN, urldecode($arFile['path']));
$arFile['type'] = $file->getFileType();
$arFile['name'] = $file->getName();
$arFile['isText'] = $file->isText();
$arFile['content'] = $file->getTextFileContent();
$arFile['downloadLink'] = $file->getDownloadLink();
$arFile['parentPath'] = $file->getParentPath();

if (isset($_POST['update_file_button'])) {
    $arFile['name'] = $_POST['file_name'] ?? '';
    $arFile['content'] = $_POST['file_content'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $file->updateFile($arFile['name'], $arFile['content']);
    }
    header('Location: file.php?path=' . urlencode($file->getPath()));
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
        <!-- Просмотр файла -->
        <section id="file">
            <h1>Тип файла: <?=htmlspecialchars($arFile['type'])?></h1>
            <form id="update-file" action="" method="post">
            <?php if ($arFile['isText']): ?>
                <div class="form-container">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <button type="submit" name="update_file_button"
                        class="form-button">Сохранить</button>
                    <a href="<?=htmlspecialchars($arFile['downloadLink'])?>"
                        class="form-button">Скачать</a>
                    <a href="index.php?path=<?=htmlspecialchars(urlencode($arFile['parentPath']))?>"
                        class="form-button">Назад</a>
                </div>
            <?php endif; ?>
                <div class="input-container">
                    <label for="file_name">Название:</label>
                    <input type="text" name="file_name" id="file_name" class="form-input"
                        value="<?=htmlspecialchars($arFile['name'])?>">
                </div>
            <?php if ($arFile['isText']): ?>
                <div class="input-container">
                    <textarea name="file_content"
                        class="file-textarea"><?=htmlspecialchars($arFile['content'])?></textarea>
                </div>
            <?php endif; ?>
                <div class="form-container">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <button type="submit" name="update_file_button"
                        class="form-button">Сохранить</button>
                    <a href="<?=htmlspecialchars($arFile['downloadLink'])?>"
                        class="form-button">Скачать</a>
                    <a href="index.php?path=<?=htmlspecialchars(urlencode($arFile['parentPath']))?>"
                        class="form-button">Назад</a>
                </div>
            </form>
        </section>
    </div>
</body>
</html>
