<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need("functions.php"); ?>
<!-- POST.PHP LOADED -->
<?php 
$this->PAGE_TYPE = 'post';
$_wc = calcWordCount($this->content);
$_rt = calcReadingTime($_wc);
$this->PAGE_WC = $_wc;
$this->PAGE_RT = $_rt;
ob_start();
?>
<meta name="description" content="<?php echo $this->title; ?> - <?php echo $this->options->siteTitle; ?>">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "<?php echo $this->title; ?>",
  "datePublished": "<?php echo $this->date('c'); ?>",
  "dateModified": "<?php echo $this->modified('c'); ?>",
  "author": {
    "@type": "Person",
    "name": "<?php echo $this->author->name; ?>"
  },
  "description": "<?php echo $this->title; ?>"
}
</script>
<?php $this->PAGE_META = ob_get_clean(); ?>
<?php ob_start(); ?>
<div class="relative mb-4 flex w-full overflow-hidden rounded-[var(--radius-large)]">
  <div
    id="post-container"
    class="card-base relative z-10 w-full px-6 pb-4 pt-6 md:px-9"
  >
    <?php
    $_postCover = $this->fields->cover;
    if ($_postCover && !preg_match('#^https?://#i', $_postCover) && $_postCover[0] !== '/') {
        $_postCover = $this->options->themeUrl($_postCover);
    }
    ?>
    <?php if ($_postCover): ?>
    <style>
      .cover-wrap-fuwari {
        overflow: hidden;
        margin: -1.5rem -1.5rem .5rem -1.5rem;
      }
      @media (min-width: 768px) {
        .cover-wrap-fuwari {
          margin: -2.25rem -2.25rem .5rem -2.25rem;
        }
      }
    </style>
    <div class="onload-animation cover-wrap-fuwari">
      <img src="<?php echo $_postCover; ?>" alt="<?php echo $this->title; ?>"
           style="display: block; width: 100%; height: 300px; object-fit: cover;"
           loading="lazy">
    </div>
    <?php endif; ?>
    <!-- word count and reading time -->
    <div class="onload-animation mb-3 flex flex-row gap-5 text-black/30 transition dark:text-white/30">
      <div class="flex flex-row items-center">
        <div
          class="mr-2 flex h-6 w-6 items-center justify-center rounded-md bg-black/5 text-black/50 transition dark:bg-white/10 dark:text-white/50"
        >
          <span class="icon-[material-symbols--notes-rounded]"></span>
        </div>
        <div class="text-sm"><?php echo $_wc; ?> 字</div>
      </div>
      <div class="flex flex-row items-center">
        <div
          class="mr-2 flex h-6 w-6 items-center justify-center rounded-md bg-black/5 text-black/50 transition dark:bg-white/10 dark:text-white/50"
        >
          <span class="icon-[material-symbols--schedule-outline-rounded]"></span>
        </div>
        <div class="text-sm"><?php echo $_rt; ?> 分钟</div>
      </div>
    </div>
    <!-- title -->
    <div class="onload-animation relative">
      <div
        data-pagefind-body
        data-pagefind-weight="10"
        data-pagefind-meta="title"
        class="mb-3 block w-full text-3xl font-bold text-black/90 transition before:absolute before:left-[-1.125rem] before:top-[0.75rem] before:h-5 before:rounded-md before:bg-[var(--primary)] dark:text-white/90 md:text-[2.25rem]/[2.75rem] md:before:w-1"
      >
        <?php echo $this->title; ?>
      </div>
    </div>
    <!-- metadata -->
    <div class="onload-animation">
      <?php $this->need("modules/post-meta.php"); ?>
    </div>
    <div
      class="custom-md markdown-content onload-animation prose prose-base mb-6 !max-w-none dark:!prose-invert"
      id="content"
    ><?php echo $this->content; ?></div>
    <?php if ($this->options->postLicenseEnable): ?>
    <div
      class="license-container onload-animation relative mb-6 overflow-hidden rounded-xl bg-[var(--license-block-bg)] px-6 py-5 transition"
    >
      <div class="font-bold text-black/75 transition dark:text-white/75"><?php echo $this->title; ?></div>
      <a href="<?php echo $this->permalink; ?>" class="link text-[var(--primary)]" x-text="window.location.href"></a>
      <div class="mt-2 flex gap-6">
        <div>
          <div class="text-sm text-black/30 transition dark:text-white/30">作者</div>
          <div class="line-clamp-2 text-black/75 transition dark:text-white/75"><?php echo $this->author->name; ?></div>
        </div>
        <div>
          <div class="text-sm text-black/30 transition dark:text-white/30">发布于</div>
          <div class="line-clamp-2 text-black/75 transition dark:text-white/75">
            <?php echo $this->date('Y-m-d'); ?>
          </div>
        </div>
        <div>
          <div class="text-sm text-black/30 transition dark:text-white/30">License</div>
          <a
            href="<?php echo $this->options->postLicenseUrl; ?>"
            target="_blank"
            class="link line-clamp-2 text-[var(--primary)]"
          ><?php echo $this->options->postLicenseName; ?></a>
        </div>
      </div>
      <span
        class="icon-[fa6-brands--creative-commons] pointer-events-none absolute right-6 top-1/2 -translate-y-1/2 text-[15rem] text-black/5 transition dark:text-white/5"
      ></span>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php $this->CONTENT = ob_get_clean(); ?>
<?php $this->need("modules/layout.php"); ?>
