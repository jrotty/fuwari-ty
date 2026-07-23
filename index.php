<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $PAGE_TYPE = 'home'; ?>
<?php ob_start(); ?>
<meta name="description" content="<?php echo $this->options->siteDescription; ?>">
<?php $PAGE_META = ob_get_clean(); ?>
<?php ob_start(); ?>
<div class="mb-4 flex flex-col rounded-[var(--radius-large)] bg-[var(--card-bg)] py-1 transition md:gap-4 md:bg-transparent md:py-0">
    <?php if ($this->have()): $postIdx = 0; ?>
    <?php while ($this->next()): $postIdx++; ?>
    <?php $this->need("modules/post-card.php"); ?>
    <?php endwhile; ?>
    <?php else: ?>
    <div class="card-base p-12 text-center">
        <p class="text-black/60 dark:text-white/60">暂无文章</p>
    </div>
    <?php endif; ?>
</div>
<?php $this->need("modules/pagination.php"); ?>
<?php $CONTENT = ob_get_clean(); ?>
<?php $this->need("modules/layout.php"); ?>
