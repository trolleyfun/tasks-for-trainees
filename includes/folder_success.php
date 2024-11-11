<!-- Информационное сообщение -->
<section id="success">
    <p class="info success">Название папки успешно изменено.</p>
    <div class="form-container">
        <a href="index.php" class="form-button">Главная</a>
        <a href="index.php?path=<?=htmlspecialchars(urlencode($arFolder['path']))?>"
            class="form-button">Назад</a>
    </div>
</section>
