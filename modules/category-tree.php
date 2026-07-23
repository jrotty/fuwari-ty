<?php $this->widget('Widget_Metas_Category_Rows')->to($_catList); ?>
<?php while ($_catList->next()): ?>
<div class="relative ml-3 before:absolute before:left-[-4px] before:top-[11px] before:h-4 before:w-0.5 before:rounded-md before:bg-[var(--primary)]">
  <a
    href="<?php echo $_catList->permalink; ?>"
    title="<?php echo $_catList->name; ?>"
    aria-label="View all posts in <?php echo $_catList->name; ?>"
  >
    <button class="h-10 w-full rounded-lg bg-none pl-2 text-neutral-700 transition-all hover:bg-[var(--btn-plain-bg-hover)] hover:pl-3 hover:text-[var(--primary)] active:bg-[var(--btn-plain-bg-active)] dark:text-neutral-300 dark:hover:text-[var(--primary)]">
      <div class="relative mr-2 flex items-center justify-between">
        <div class="overflow-hidden overflow-ellipsis whitespace-nowrap text-left"><?php echo $_catList->name; ?></div>
        <div class="ml-4 flex h-7 min-w-[2rem] items-center justify-center rounded-lg bg-[oklch(0.95_0.025_var(--hue))] px-2 text-sm font-bold text-[var(--btn-content)] transition dark:bg-[var(--primary)] dark:text-[var(--deep-text)]">
          <?php echo $_catList->count; ?>
        </div>
      </div>
    </button>
  </a>
</div>
<?php endwhile; ?>
