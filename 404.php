<?php if (!defined("__TYPECHO_ROOT_DIR__")) exit; ?>
<?php $this->PAGE_TYPE = "404"; ?>
<?php ob_start(); ?>
<meta name="description" content="404 - <?php echo $this->options->title; ?>">
<?php $this->PAGE_META = ob_get_clean(); ?>
<?php ob_start(); ?>
<div class="relative mb-4 flex w-full overflow-hidden rounded-[var(--radius-large)]">
  <div class="card-base relative z-10 w-full px-6 pb-4 pt-6 md:px-9">
    <div class="flex flex-col items-center justify-center py-20">
      <div class="text-8xl font-bold text-[var(--primary)]">404</div>
      <div class="mt-4 text-2xl font-bold text-neutral-700 dark:text-neutral-200">页面未找到</div>
      <p class="mt-2 text-neutral-500">你想查看的页面已被转移或删除了</p>
      <a href="/" class="btn-regular mt-6 h-10 rounded-lg px-6 font-bold">返回首页</a>
    </div>
  </div>
</div>
<?php $this->CONTENT = ob_get_clean(); ?>
<?php $this->need("modules/layout.php"); ?>

