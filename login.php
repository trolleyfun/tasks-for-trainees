<?php

/* Идентификатор и пароль приложения */
define('CLIENT_ID', '');
define('CLIENT_SECRET', '');

session_start();

if (!empty($_SESSION['oauth_token'])) {
    header('Location: index.php');
}

if (!empty($_GET['logout'])) {
    unset($_SESSION['oauth_token']);
    header('Location: login.php');
}

/* Получение OAuth-токена для сервисов Яндекс */
if (!empty($_GET['code']))
  {
    $query = array(
      'grant_type' => 'authorization_code',
      'code' => $_GET['code'],
      'client_id' => CLIENT_ID,
      'client_secret' => CLIENT_SECRET
    );
    $query = http_build_query($query);

    $header = "Content-type: application/x-www-form-urlencoded";

    $opts = array('http' =>
      array(
      'method'  => 'POST',
      'header'  => $header,
      'content' => $query
      )
    );
    $context = stream_context_create($opts);
    $result = file_get_contents('https://oauth.yandex.ru/token', false, $context);
    $result = json_decode($result);

    if ($result) {
        $_SESSION['oauth_token'] = $result->access_token;
    }
    header('Location: index.php');
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
    <div class="container">
        <section id="login" class="login-page">
            <div class="login-form">
                <h1>Walle &bull; Disk Manager</h1>
                <div class="login-container">
                    <img src="images/logo.svg" alt="" class="logo-img">
                    <a href="https://oauth.yandex.ru/authorize?response_type=code&client_id=<?=CLIENT_ID?>"
                        class="form-button">Вход</a>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
