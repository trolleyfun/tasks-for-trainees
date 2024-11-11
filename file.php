<?php

use Arhitector\Yandex\Client\Exception\NotFoundException;
use Arhitector\Yandex\Client\Exception\UnauthorizedException;
use Arhitector\Yandex\Disk\Exception\AlreadyExistsException;
use Trolleyfun\Yandex\Exception\FileCreationFailureException;
use Trolleyfun\Yandex\Exception\ResourceTypeNotValidException;
use Trolleyfun\Yandex\FileManager;

require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/token.php');

session_start();

$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

$arFile = [];
$arErrors = [];
$displayMainSection = true;
$arFile['path'] = $_GET['path'] ?? 'disk:/';
$arFile['success'] = $_GET['success'] ?? '';

try {
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
            header('Location: file.php?success=ok&path=' . urlencode($file->getPath()));
        } else {
            $arErrors[] = 'Неверный CSRF-токен';
            $displayMainSection = true;
        }
    }
} catch (UnauthorizedException | FileCreationFailureException $e) {
    $arErrors[] = $e->getMessage();
    $displayMainSection = false;
} catch (AlreadyExistsException | UnexpectedValueException $e) {
    $arErrors[] = $e->getMessage();
    $displayMainSection = true;
} catch (NotFoundException | ResourceTypeNotValidException $e) {
    header('Location: error.php');
}

include(__DIR__.'/includes/header.php');
include(__DIR__.'/includes/errors.php');
if ($arFile['success']) {
    include(__DIR__.'/includes/file_success.php');
} elseif ($displayMainSection) {
    include(__DIR__.'/includes/file_main_section.php');
}
include(__DIR__.'/includes/footer.php');
