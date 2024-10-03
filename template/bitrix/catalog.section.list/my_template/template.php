<?php 
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div id="barba-wrapper">
    <div class="article-list">
    <?php foreach ($arResult["SECTIONS"] as $arSection): ?>
        <a class="article-item article-list__item" href="<?=$arSection["SECTION_PAGE_URL"]?>" data-anim="anim-3">

        <?php if (is_array($arSection["PICTURE"])): ?>
            <div class="article-item__background"><img src="<?=$arSection["PICTURE"]["SRC"]?>" alt=""/></div>
        <?php endif; ?>

            <div class="article-item__wrapper">
                <div class="article-item__title"><?=$arSection["NAME"]?></div>

                <?php if ($arSection["DESCRIPTION"]): ?>
                    <div class="article-item__content"><?=$arSection["DESCRIPTION"]?></div>
                <? endif; ?>
            </div>	
            <!-- /.article-item__wrapper -->		
        </a>
        <!-- /.article-item.article-list__item -->
    <?php endforeach; ?>
    </div>
    <!-- /.article-list -->
</div>
<!-- /#barba-wrapper -->
