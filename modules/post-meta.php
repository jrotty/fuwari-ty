<?php $_isArticle = ($this->PAGE_TYPE === 'post'); ?>
<div class="mb-4 flex flex-wrap items-center gap-4 gap-x-4 gap-y-2 text-neutral-500 dark:text-neutral-400">
    <?php if ($_isArticle): ?>
    <!-- article: publish date -->
    <div class="flex items-center">
      <div class="meta-icon">
        <span class="icon-[material-symbols--calendar-today-outline-rounded] text-xl"></span>
      </div>
      <span class="text-50 text-sm font-medium">
        <?php echo $this->date('Y-m-d'); ?>
      </span>
    </div>

    <!-- article: category -->
    <?php if (!empty($this->categories[0]['name'])): ?>
    <div class="flex items-center">
      <div class="meta-icon">
        <span class="icon-[material-symbols--book-2-outline-rounded] text-xl"></span>
      </div>
      <a href="<?php echo $this->categories[0]['permalink']; ?>" class="link-lg transition text-50 text-sm font-medium hover:text-[var(--primary)] dark:hover:text-[var(--primary)] whitespace-nowrap">
        <?php echo $this->categories[0]['name']; ?>
      </a>
    </div>
    <?php endif; ?>

    <!-- article: tags -->
    <?php $_tagObjs = is_array($this->tags) ? $this->tags : []; ?>
    <?php if (count($_tagObjs) > 0): ?>
    <div class="flex items-center">
      <div class="meta-icon">
        <span class="icon-[material-symbols--tag-rounded] text-xl"></span>
      </div>
      <?php $i = 0; foreach ($_tagObjs as $_to): ?>
        <?php if ($i > 0): ?><span class="mx-1.5 text-sm text-[var(--meta-divider)]">/</span><?php endif; ?>
        <a href="<?php echo $_to['permalink']; ?>" class="link-lg transition text-50 text-sm font-medium hover:text-[var(--primary)] dark:hover:text-[var(--primary)] whitespace-nowrap"><?php echo trim($_to['name']); ?></a>
      <?php $i++; endforeach; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- card: date -->
    <div class="flex items-center">
      <div class="meta-icon">
        <span class="icon-[material-symbols--calendar-today-outline-rounded] text-xl"></span>
      </div>
      <span class="text-50 text-sm font-medium">
        <?php echo $this->date('Y-m-d'); ?>
      </span>
    </div>

    <!-- card: category -->
    <?php if (!empty($this->categories[0]['name'])): ?>
    <div class="flex items-center">
      <div class="meta-icon">
        <span class="icon-[material-symbols--book-2-outline-rounded] text-xl"></span>
      </div>
      <a href="<?php echo $this->categories[0]['permalink']; ?>" class="link-lg transition text-50 text-sm font-medium hover:text-[var(--primary)] dark:hover:text-[var(--primary)] whitespace-nowrap">
        <?php echo $this->categories[0]['name']; ?>
      </a>
    </div>
    <?php endif; ?>

    <!-- card: word count -->
    <div class="flex items-center">
      <div class="meta-icon">
        <span class="icon-[material-symbols--article-outline-rounded] text-xl"></span>
      </div>
      <span class="text-50 text-sm font-medium">
        <?php if ($this->PAGE_WC): ?><?php echo $this->PAGE_WC; ?> 字<?php else: ?>0 字<?php endif; ?>
      </span>
    </div>

    <!-- card: views -->
    <div class="flex items-center">
      <div class="meta-icon">
        <span class="icon-[material-symbols--visibility-outline-rounded] text-xl"></span>
      </div>
      <span class="text-50 text-sm font-medium">
        <?php echo getPostViews($this); ?>
      </span>
    </div>
    <?php endif; ?>
  </div>
