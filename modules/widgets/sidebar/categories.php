<?php $this->widget('Widget_Metas_Category_Rows')->to($_catWidget); ?>
<?php $_catCount = 0; while ($_catWidget->next()): $_catCount++; endwhile; ?>
<div data-id="categories" x-data="{ isCollapsed: true }" x-init="isCollapsed = <?php echo $_catCount >= 5 ? 'true' : 'false'; ?>" class="card-base onload-animation pb-4" style="animation-delay: 150ms; --collapsedHeight: 7.5rem">
    <div class="relative mb-2 ml-8 mt-4 text-lg font-bold text-neutral-900 transition before:absolute before:left-[-16px] before:top-[5.5px] before:h-4 before:w-1 before:rounded-md before:bg-[var(--primary)] dark:text-neutral-100" style="--collapsedHeight: 7.5rem">
        分类
    </div>
    <div id="categories" class="collapse-wrapper overflow-hidden px-4" :class="isCollapsed ? 'collapsed' : ''" style="--collapsedHeight: 7.5rem">
        <?php $this->widget('Widget_Metas_Category_Rows')->to($_catWidget); ?>
        <?php while ($_catWidget->next()): ?>
        <a href="<?php echo $_catWidget->permalink; ?>" class="btn-plain scale-animation flex w-full items-center justify-between rounded-lg px-3 py-1.5 font-bold active:scale-95">
            <div class="flex items-center">
                <span class="icon-[material-symbols--folder-open-outline] mr-2 text-[1.25rem]"></span>
                <?php echo $_catWidget->name; ?>
            </div>
            <span class="text-sm text-black/30 dark:text-white/30"><?php echo $_catWidget->count; ?></span>
        </a>
        <?php endwhile; ?>
    </div>
    <?php if ($_catCount >= 5): ?>
    <div class="expand-btn -mb-2 px-4">
        <button class="btn-plain h-9 w-full rounded-lg" @click="isCollapsed = ! isCollapsed">
            <div class="flex -translate-x-2 items-center justify-center gap-2 text-[var(--primary)]">
                <span class="icon-[material-symbols--more-horiz] text-[1.75rem]"></span>
                更多
            </div>
        </button>
    </div>
    <?php endif; ?>
</div>
