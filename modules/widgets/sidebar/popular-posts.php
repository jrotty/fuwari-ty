<?php $_popularPosts = $this->widget('Widget_Contents_Post_Recent', 'pageSize=5'); ?>
<div class="card-base p-3">
  <div class="mb-2 flex items-center justify-between">
    <span class="text-lg font-bold text-[var(--primary)]">热门文章</span>
  </div>
  <div class="flex flex-col gap-2">
    <?php $_popularIdx = 0; while ($_popularPosts->next()): ?>
        <a href="<?php echo $_popularPosts->permalink; ?>" class="group flex items-center gap-3 rounded-lg p-2 transition hover:bg-[var(--btn-plain-bg-hover)] active:bg-[var(--btn-plain-bg-active)]">
          <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[var(--primary)] text-xs font-bold text-white">
            <?php echo $_popularIdx + 1; ?>
          </span>
          <span class="line-clamp-2 text-sm font-medium text-black/75 transition group-hover:text-[var(--primary)] dark:text-white/75">
            <?php echo $_popularPosts->title; ?>
          </span>
        </a>
    <?php $_popularIdx++; endwhile; ?>
  </div>
</div>
