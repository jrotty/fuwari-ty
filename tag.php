<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php 
$PAGE_TYPE = 'tag';
ob_start();
?>
<meta name="description" content="标签: <?php $this->archiveTitle('tag', '', ''); ?> - <?php echo $this->options->siteTitle; ?>">
<?php $PAGE_META = ob_get_clean(); ?>
<?php ob_start(); ?>
<div class="card-base mb-5 px-8 py-6">
  <div class="my-2 px-2 text-2xl font-bold text-[var(--primary)]">#<?php $this->archiveTitle('tag', '', ''); ?></div>
  <div>
    <div class="flex h-[3.75rem] w-full flex-row items-center">
      <div class="text-75 w-[15%] text-right text-lg font-bold transition md:w-[10%]">时间轴</div>
      <div class="w-[15%] md:w-[10%]">
        <div class="outline-3 z-50 mx-auto h-3 w-3 rounded-full bg-none outline -outline-offset-[2px] outline-[var(--primary)]"></div>
      </div>
      <div class="text-50 w-[70%] text-left transition md:w-[80%]">
        共 <?php echo $this->getTotal(); ?> 篇文章
      </div>
    </div>

    <?php if ($this->have()): ?>
    <?php while ($this->next()): ?>
    <a href="<?php echo $this->permalink; ?>" aria-label="<?php echo $this->title; ?>" class="btn-plain group !block h-10 w-full rounded-lg hover:text-[initial]">
      <div class="flex h-full flex-row items-center justify-start">
        <!-- date -->
        <div class="text-50 w-[15%] text-right text-sm transition md:w-[10%]">
          <?php echo $this->date('m-d'); ?>
        </div>

        <!-- dot and line -->
        <div class="dash-line relative flex h-full w-[15%] items-center md:w-[10%]">
          <div class="z-50 mx-auto h-1 w-1 rounded bg-[oklch(0.5_0.05_var(--hue))] outline outline-4 outline-[var(--card-bg)] transition-all group-hover:h-5 group-hover:bg-[var(--primary)] group-hover:outline-[var(--btn-plain-bg-hover)] group-active:outline-[var(--btn-plain-bg-active)]"></div>
        </div>

        <!-- post title -->
        <div class="text-75 w-[70%] overflow-hidden overflow-ellipsis whitespace-nowrap pr-8 text-left font-bold transition-all group-hover:translate-x-1 group-hover:text-[var(--primary)] md:w-[65%] md:max-w-[65%]">
          <?php echo $this->title; ?>
        </div>

        <!-- tag list -->
        <div class="text-30 hidden overflow-hidden overflow-ellipsis whitespace-nowrap text-left text-sm transition md:block md:w-[15%]">
          <?php echo '#' . str_replace(',', ' #', getTagsString($this->tags)); ?>
        </div>
      </div>
    </a>
    <?php endwhile; ?>
    <?php endif; ?>
  </div>
</div>
<?php $this->need("modules/pagination.php"); ?>
<?php $CONTENT = ob_get_clean(); ?>
<?php $this->need("modules/layout.php"); ?>
