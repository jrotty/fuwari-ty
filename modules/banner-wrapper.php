<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$_bannerSrc = $this->options->bannerSrc;
if ($_bannerSrc && !preg_match('#^https?://#i', $_bannerSrc) && $_bannerSrc[0] !== '/') {
    $_bannerSrc = $this->options->themeUrl($_bannerSrc);
}
$_bannerMode = (string)($this->BANNER_MODE ?? 'banner');
if ($_bannerMode === 'fullscreen'):
?>
<div id="banner-wrapper" class="fixed inset-0 z-[40] overflow-hidden pointer-events-none">
    <img src="<?php echo htmlspecialchars($_bannerSrc, ENT_QUOTES); ?>" alt="Fullscreen wallpaper" style="object-position: <?php echo $this->options->bannerPosition; ?> center;" class="h-full w-full object-cover" decoding="async" fetchpriority="high">
    <div class="absolute inset-0 bg-black/30 dark:bg-black/50"></div>
</div>
<?php else: ?>
<div id="banner-wrapper" class="absolute z-10 w-full overflow-hidden transition duration-700" style="top: -30vh">
    <div id="banner" class="relative h-full overflow-hidden object-cover transition duration-700">
      <div class="pointer-events-none absolute inset-0 bg-opacity-50 transition dark:bg-black/10"></div>
      <img src="<?php echo $_bannerSrc; ?>" alt="Banner image of the blog" style="object-position: <?php echo $this->options->bannerPosition; ?>" width="1344" height="896" loading="lazy" decoding="async" class="h-full w-full object-cover">
    </div>
</div>
<?php endif; ?>
