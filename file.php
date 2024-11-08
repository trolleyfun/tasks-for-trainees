<?php

use Trolleyfun\Yandex\FileManager;

require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/token.php');

session_start();

$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

$filePath = $_GET['path'] ?? 'disk:/';
$file = new FileManager(OAUTH_TOKEN, urldecode($filePath));

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
        <form id="update-file" action="" method="post">
            <!-- Просмотр файла -->
            <section id="file">
                <h1>Файл: <?=$file->getName()?></h1>

            </section>

            <!-- Нижнее меню -->
            <section id="bottom-menu" class="col-menu">
                <div class="form-container">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <button type="submit" name="update_file_button"
                        class="form-button">Сохранить</button>
                    <a href="index.php?path=<?=htmlspecialchars(urlencode($file->getParentPath()))?>"
                        class="form-button">Назад</a>
                </div>
            </section>
        </form>
    </div>
</body>
</html>