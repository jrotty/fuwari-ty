<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need("functions.php"); ?>
<?php
$this->PAGE_TYPE = 'feed-info';
$isAtom = ($this->parameter->type === 'feed_info_atom');
$prefix = $isAtom ? 'atom' : 'rss';
$feedUrl = rtrim(\Typecho\Common::url('/', $this->options->index), '/')
    . ($isAtom ? '/atom.xml' : '/rss.xml');
$t = function ($k) { return __t($k); };
ob_start(); ?>
<meta name="description" content="<?php echo __t('feed.' . $prefix . 'Description'); ?>">
<?php $this->PAGE_META = ob_get_clean(); ?>
<?php ob_start(); ?>
<div class="onload-animation">
  <!-- 头部卡片 -->
  <div class="card-base rounded-[var(--radius-large)] p-8 mb-6">
    <div class="text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-[var(--primary)] rounded-2xl mb-4">
        <span class="icon-[material-symbols--<?php echo $isAtom ? 'notes-rounded' : 'rss-feed-rounded'; ?>] text-white text-3xl"></span>
      </div>
      <h1 class="text-3xl font-bold text-[var(--primary)] mb-3"><?php echo $t('feed.' . $prefix); ?></h1>
      <p class="text-75 max-w-2xl mx-auto"><?php echo $t('feed.' . $prefix . 'Subtitle'); ?></p>
    </div>
  </div>

  <!-- RSS Link 卡片 -->
  <div class="card-base rounded-[var(--radius-large)] p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
      <div class="flex items-center">
        <div class="w-12 h-12 bg-[var(--primary)] rounded-xl flex items-center justify-center mr-4">
          <span class="icon-[material-symbols--link-rounded] text-white text-xl"></span>
        </div>
        <div>
          <h3 class="font-semibold text-90 mb-1"><?php echo $t('feed.' . $prefix . 'Link'); ?></h3>
          <p class="text-sm text-75"><?php echo $t('feed.' . $prefix . 'CopyToReader'); ?></p>
        </div>
      </div>
      <div class="flex flex-col sm:flex-row gap-3 sm:flex-1 lg:flex-none lg:justify-end">
        <code class="flex-1 min-w-0 bg-[var(--card-bg)] px-3 py-2 rounded-lg text-sm font-mono text-75 border border-[var(--line-divider)] break-all"><?php echo $feedUrl; ?></code>
        <button id="copy-<?php echo $prefix; ?>-btn"
                class="px-4 py-2 bg-[var(--primary)] text-white rounded-lg hover:opacity-80 transition-all duration-200 font-medium text-sm whitespace-nowrap"
                data-url="<?php echo $feedUrl; ?>">
          <?php echo $t('feed.' . $prefix . 'CopyLink'); ?>
        </button>
      </div>
    </div>
  </div>

  <!-- 最新文章卡片 -->
  <div class="card-base rounded-[var(--radius-large)] p-6 mb-6">
    <h2 class="text-xl font-bold text-90 mb-4 flex items-center">
      <span class="icon-[material-symbols--article-outline-rounded] mr-2 text-[var(--primary)]"></span>
      <?php echo $t('feed.' . $prefix . 'LatestPosts'); ?>
    </h2>
    <div class="space-y-4">
      <?php $posts = $this->widget('Widget_Contents_Post_Recent', 'pageSize=6'); ?>
      <?php while ($posts->next()):
        $_desc = trim(preg_replace('/<[^>]+>/', '', $posts->excerpt));
      ?>
      <article class="bg-[var(--card-bg)] rounded-xl p-4 border border-[var(--line-divider)] hover:border-[var(--primary)] transition-all duration-300">
        <h3 class="text-lg font-semibold text-90 mb-2 transition-colors">
          <a href="<?php echo $posts->permalink; ?>" class="hover:text-[var(--primary)] hover:underline"><?php echo $posts->title; ?></a>
        </h3>
        <?php if ($_desc !== ''): ?>
        <p class="text-75 mb-3 line-clamp-2"><?php echo $_desc; ?></p>
        <?php endif; ?>
        <div class="flex items-center gap-4 text-sm text-60">
          <time datetime="<?php echo $posts->date('c'); ?>" class="text-75"><?php echo $posts->date('Ymd'); ?></time>
        </div>
      </article>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- 什么是 RSS/Atom 卡片 -->
  <div class="card-base rounded-[var(--radius-large)] p-6">
    <h2 class="text-xl font-bold text-90 mb-4 flex items-center">
      <span class="icon-[material-symbols--help-outline-rounded] mr-2 text-[var(--primary)]"></span>
      <?php echo $t('feed.' . $prefix . 'WhatIs' . ($isAtom ? 'Atom' : 'RSS')); ?>
    </h2>
    <div class="text-75 space-y-3">
      <p><?php echo $t('feed.' . $prefix . 'WhatIs' . ($isAtom ? 'Atom' : 'RSS') . 'Description'); ?></p>
      <ul class="list-disc list-inside space-y-1 ml-4">
        <li><?php echo $t('feed.' . $prefix . 'Benefit1'); ?></li>
        <li><?php echo $t('feed.' . $prefix . 'Benefit2'); ?></li>
        <li><?php echo $t('feed.' . $prefix . 'Benefit3'); ?></li>
        <li><?php echo $t('feed.' . $prefix . 'Benefit4'); ?></li>
      </ul>
      <p class="text-sm"><?php echo $t('feed.' . $prefix . 'HowToUse'); ?></p>
    </div>
  </div>
</div>

<script>
  (function () {
    var btn = document.getElementById('copy-<?php echo $prefix; ?>-btn');
    if (btn) {
      btn.addEventListener('click', async function () {
        var url = this.getAttribute('data-url');
        if (!url) return;
        try {
          await navigator.clipboard.writeText(url);
          var originalText = this.textContent || 'Copy';
          this.textContent = url.includes('rss')
            ? '<?php echo $t('feed.rssCopied'); ?>'
            : '<?php echo $t('feed.atomCopied'); ?>';
          this.style.backgroundColor = '#10b981';
          setTimeout(function () { btn.textContent = originalText; btn.style.backgroundColor = ''; }, 2000);
        } catch (err) {
          console.error('复制失败:', err);
          var originalText = this.textContent || 'Copy';
          this.textContent = '<?php echo $t('feed.' . $prefix . 'CopyFailed'); ?>';
          setTimeout(function () { btn.textContent = originalText; }, 2000);
        }
      });
    }
  })();
</script>
<?php $this->CONTENT = ob_get_clean(); ?>
<?php $this->need("modules/layout.php"); ?>
