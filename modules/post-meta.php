<?php $_tagText = getTagsString($this->tags); $_tagsArr = array_filter(array_map('trim', explode(',', $_tagText))); ?>
<div class="mb-4 flex flex-wrap items-center gap-4 gap-x-4 gap-y-2 text-neutral-500 dark:text-neutral-400">
    <!-- publish date -->
    <div class="flex items-center">
      <div class="meta-icon">
        <span class="icon-[material-symbols--calendar-today-outline-rounded] text-xl"></span>
      </div>
      <span class="text-50 text-sm font-medium">
        <?php echo $this->date('Y-m-d'); ?>
      </span>
    </div>

    <!-- word count -->
    <div class="flex items-center">
      <div class="meta-icon">
        <span class="icon-[material-symbols--article-outline-rounded] text-xl"></span>
      </div>
      <span class="text-50 text-sm font-medium">
        <?php if ($_wc): ?><?php echo $_wc; ?> 字<?php else: ?>0 字<?php endif; ?>
      </span>
    </div>

    <!-- read time -->
    <div class="flex items-center">
      <div class="meta-icon">
        <span class="icon-[material-symbols--schedule-outline-rounded] text-xl"></span>
      </div>
      <span class="text-50 text-sm font-medium">
        <?php if ($_rt): ?><?php echo $_rt; ?> 分钟<?php else: ?>0 分钟<?php endif; ?>
      </span>
    </div>

    <!-- tags -->
    <?php if (count($_tagsArr) > 0): ?>
    <div class="flex items-center gap-1.5">
      <div class="meta-icon">
        <span class="icon-[material-symbols--label-outline-rounded] text-xl"></span>
      </div>
      <?php foreach ($_tagsArr as $_t): ?>
        <span class="rounded-md bg-black/5 px-2 py-0.5 text-xs font-medium dark:bg-white/10"><?php echo trim($_t); ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
