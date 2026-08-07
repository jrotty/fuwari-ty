<?php $this->widget('Widget_Metas_Category_Rows')->to($_catWidget); ?>
<?php $_catCount = 0; while ($_catWidget->next()): $_catCount++; endwhile; ?>
<div data-id="categories" x-data="{ isCollapsed: true }" x-init="isCollapsed = <?php echo $_catCount >= 5 ? 'true' : 'false'; ?>" class="card-base onload-animation pb-4" style="animation-delay: 150ms; --collapsedHeight: 7.5rem">
    <div class="relative mb-2 ml-8 mt-4 text-lg font-bold text-neutral-900 transition before:absolute before:left-[-16px] before:top-[5.5px] before:h-4 before:w-1 before:rounded-md before:bg-[var(--primary)] dark:text-neutral-100" style="--collapsedHeight: 7.5rem">
        分类
    </div>
    <div id="categories" class="collapse-wrapper overflow-hidden px-4" :class="isCollapsed ? 'collapsed' : ''" style="--collapsedHeight: 7.5rem">
        <?php $this->widget('Widget_Metas_Category_Rows')->to($_catWidget); ?>
        <?php while ($_catWidget->next()): ?>
        <a href="<?php echo $_catWidget->permalink; ?>" class="flex w-full items-center justify-between rounded-lg px-3 py-1.5 font-medium text-neutral-700 transition hover:bg-[var(--btn-plain-bg-hover)] hover:pl-3 hover:text-[var(--primary)] active:bg-[var(--btn-plain-bg-active)] dark:text-neutral-300 dark:hover:text-[var(--primary)]">
            <span class="overflow-hidden text-ellipsis whitespace-nowrap"><?php echo $_catWidget->name; ?></span>
            <span class="ml-4 flex h-7 min-w-[2rem] items-center justify-center rounded-lg bg-[var(--btn-regular-bg)] px-2 text-sm font-bold text-[var(--btn-content)] transition dark:bg-[var(--primary)] dark:text-white"><?php echo $_catWidget->count; ?></span>
        </a>
        <?php endwhile; ?>
    </div>
    <?php if ($_catCount >= 5): ?>
    <div class="expand-btn -mb-2 px-4">
        <button class="btn-plain h-9 w-full rounded-lg" @click="isCollapsed = ! isCollapsed">
            <div class="flex -translate-x-2 items-center justify-center gap-2 text-[var(--primary)]">
                <span class="icon-[material-symbols--more-horiz] text-[1.75rem]"></span>
                <span x-text="isCollapsed ? '更多' : '收起'"></span>
            </div>
        </button>
    </div>
    <?php endif; ?>
</div>
