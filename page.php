<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need("functions.php"); ?>
<?php 
$this->PAGE_TYPE = 'page';
$_wc = calcWordCount($this->content);
$_rt = calcReadingTime($_wc);
ob_start();
?>
<meta name="description" content="<?php echo $this->title; ?> - <?php echo $this->options->siteTitle; ?>">
<?php $this->PAGE_META = ob_get_clean(); ?>
<?php ob_start(); ?>
<div class="relative mb-4 flex min-h-32 w-full overflow-hidden rounded-[var(--radius-large)]">
  <div class="card-base relative z-10 w-full px-9 py-6">
    <?php if ($this->fields->cover): ?>
    <style>
      .cover-wrap-fuwari-page {
        overflow: hidden;
        margin: -1.5rem -1.5rem .5rem -1.5rem;
      }
      @media (min-width: 768px) {
        .cover-wrap-fuwari-page {
          margin: -2.25rem -2.25rem .5rem -2.25rem;
        }
      }
    </style>
    <div class="onload-animation cover-wrap-fuwari-page">
      <img src="<?php echo $this->fields->cover; ?>" alt="<?php echo $this->title; ?>"
           style="display: block; width: 100%; height: 300px; object-fit: cover;"
           loading="lazy">
    </div>
    <?php endif; ?>
    <h1 class="text-3xl font-bold mb-6 text-black/90 dark:text-white/90"><?php echo $this->title; ?></h1>
    <div class="onload-animation mb-3 flex flex-row gap-5 text-black/30 transition dark:text-white/30">
      <div class="flex flex-row items-center">
        <div class="mr-2 flex h-6 w-6 items-center justify-center rounded-md bg-black/5 text-black/50 transition dark:bg-white/10 dark:text-white/50">
          <span class="icon-[material-symbols--notes-rounded]"></span>
        </div>
        <div class="text-sm"><?php echo $_wc; ?> 字</div>
      </div>
      <div class="flex flex-row items-center">
        <div class="mr-2 flex h-6 w-6 items-center justify-center rounded-md bg-black/5 text-black/50 transition dark:bg-white/10 dark:text-white/50">
          <span class="icon-[material-symbols--schedule-outline-rounded]"></span>
        </div>
        <div class="text-sm"><?php echo $_rt; ?> 分钟</div>
      </div>
    </div>
    <div class="custom-md prose prose-base mt-2 !max-w-none dark:prose-invert">
      <?php echo $this->content; ?>
    </div>
    <?php if ($this->options->postLicenseEnable): ?>
    <div class="license-container onload-animation relative mt-6 overflow-hidden rounded-xl bg-[var(--license-block-bg)] px-6 py-5 transition">
      <div class="font-bold text-black/75 transition dark:text-white/75"><?php echo $this->title; ?></div>
      <a href="<?php echo $this->permalink; ?>" class="link text-[var(--primary)]" x-text="window.location.href"></a>
      <div class="mt-2 flex gap-6">
        <div>
          <div class="text-sm text-black/30 transition dark:text-white/30">作者</div>
          <div class="line-clamp-2 text-black/75 transition dark:text-white/75"><?php echo $this->author->name; ?></div>
        </div>
        <div>
          <div class="text-sm text-black/30 transition dark:text-white/30">发布于</div>
          <div class="line-clamp-2 text-black/75 transition dark:text-white/75"><?php echo $this->date('Y-m-d'); ?></div>
        </div>
        <div>
          <div class="text-sm text-black/30 transition dark:text-white/30">License</div>
          <a href="<?php echo $this->options->postLicenseUrl; ?>" target="_blank" class="link line-clamp-2 text-[var(--primary)]"><?php echo $this->options->postLicenseName; ?></a>
        </div>
      </div>
      <span class="icon-[fa6-brands--creative-commons] pointer-events-none absolute right-6 top-1/2 -translate-y-1/2 text-[15rem] text-black/5 transition dark:text-white/5"></span>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php $this->CONTENT = ob_get_clean(); ?>
<?php $this->need("modules/layout.php"); ?>
