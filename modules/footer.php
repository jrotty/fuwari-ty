<?php
$_icp = $this->options->icpText;
$_icpLink = $this->options->icpLink;
$_gongan = $this->options->gonganText;
$_gonganLink = $this->options->gonganLink;
?>
<div class="flex w-full flex-col items-center justify-center">
  <div class="my-10 w-2/3 border-t border-dashed border-black/10 transition dark:border-white/15"></div>
  <div class="mb-12 flex flex-col items-center justify-center rounded-2xl border-dashed border-[oklch(85%_0.01_var(--hue))] px-6 transition dark:border-white/15">
    <div class="text-50 text-center text-sm transition !delay-0">
      &copy;
      <span id="copyright-year"><?php echo date('Y'); ?></span>
      <?php echo $this->options->siteTitle; ?>. All Rights Reserved. /
      <a class="link font-medium text-[var(--primary)] transition" target="_blank" href="/rss.xml">RSS</a> /
      <a class="link font-medium text-[var(--primary)]" target="_blank" href="/sitemap.xml">Sitemap</a><br />
      Powered by
      <a class="link font-medium text-[var(--primary)]" target="_blank" href="https://typecho.org">Typecho</a> &
      <a class="link font-medium text-[var(--primary)]" target="_blank" href="https://github.com/jiewenhuang/halo-theme-fuwari">Theme-Fuwari</a>
      <br />
      <div>
        <?php if ($_icp): ?>
        <a class="hover:underline" target="_blank" href="<?php echo $_icpLink; ?>"><?php echo $_icp; ?></a>
        <?php endif; ?>
        <?php if ($_gongan): ?>
        <p class="flex items-center justify-center gap-1">
          <img src="<?php echo $this->options->themeUrl; ?>assets/images/gongan_beian.png" class="size-4" alt="gongan_beian">
          <a href="<?php echo $_gonganLink; ?>" class="hover:underline" target="_blank"><?php echo $_gongan; ?></a>
        </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
