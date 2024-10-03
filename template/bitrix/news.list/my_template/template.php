<?php 
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div id="barba-wrapper">
    <div class="article-list">
    <?php foreach ($arResult["ITEMS"] as $arItem): ?>
        <?php if ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"]): ?>
            <a class="article-item article-list__item" href="<?=$arItem["DETAIL_PAGE_URL"]?>" data-anim="anim-3">
        <?php else: ?>
            <div class="article-item article-list__item" data-anim="anim-3">
        <?php endif; ?>

        <?php if (is_array($arItem["PREVIEW_PICTURE"])): ?>
            <div class="article-item__background"><img src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" alt=""/></div>
        <?php endif; ?>

            <div class="article-item__wrapper">
                <div class="article-item__title"><?=$arItem["NAME"]?></div>

                <?php if ($arItem["PREVIEW_TEXT"]): ?>
                    <div class="article-item__content"><?=$arItem["PREVIEW_TEXT"]?></div>
                <? endif; ?>
            </div>	
            <!-- /.article-item__wrapper -->		

        <?php if ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"]): ?>
            </a>
        <?php else: ?>
            </div>
        <?php endif; ?>
        <!-- /.article-item.article-list__item -->

    <?php endforeach; ?>
    </div>
    <!-- /.article-list -->
</div>
<!-- /#barba-wrapper -->
