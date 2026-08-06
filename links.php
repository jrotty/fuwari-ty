<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php 
$this->PAGE_TYPE = 'links';
ob_start();
?>
<meta name="description" content="友情链接 - <?php echo $this->options->title; ?>">
<?php $this->PAGE_META = ob_get_clean(); ?>
<?php ob_start(); ?>
<div class="card-base px-8 py-6">
  <div class="text-center py-12">
    <p class="text-black/60 dark:text-white/60">友情链接功能即将上线</p>
  </div>
</div>
<?php $this->CONTENT = ob_get_clean(); ?>
<?php $this->need("modules/layout.php"); ?>
