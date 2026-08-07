<div data-id="tags" class="card-base onload-animation pb-4" style="animation-delay: 200ms">
    <div class="relative mb-2 ml-8 mt-4 text-lg font-bold text-neutral-900 transition before:absolute before:left-[-16px] before:top-[5.5px] before:h-4 before:w-1 before:rounded-md before:bg-[var(--primary)] dark:text-neutral-100" style="--collapsedHeight: 7.5rem">
        标签
    </div>
    <div class="flex flex-wrap gap-2 px-4">
        <?php $this->widget('Widget_Metas_Tag_Cloud', 'limit=15,ignoreZeroCount=1')->to($_tagWidget); ?>
        <?php while ($_tagWidget->next()): ?>
        <a href="<?php echo $_tagWidget->permalink; ?>" aria-label="View all posts with the <?php echo $_tagWidget->name; ?> tag" class="btn-regular h-8 rounded-lg px-3 text-sm"><?php echo $_tagWidget->name; ?></a>
        <?php endwhile; ?>
    </div>
</div>
