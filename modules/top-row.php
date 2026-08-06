<?php
// Gather nav pages and detect archives page
$_navList = [];
$_archivesUrl = '';
$this->widget('Widget_Contents_Page_List')->to($_navPages);
while ($_navPages->next()):
    $_navList[] = ['permalink' => $_navPages->permalink, 'title' => $_navPages->title];
    if (($_navPages->template ?? '') === 'archives.php') $_archivesUrl = $_navPages->permalink;
endwhile;
if (!$_archivesUrl) $_archivesUrl = $this->options->siteUrl . 'archives/';
?>
<div id="top-row" class="pointer-events-none relative z-50 mx-auto max-w-[var(--page-width)] px-0 transition-all duration-700 md:px-4">
  <div id="navbar-wrapper" class="pointer-events-auto sticky top-0 transition-all">
    <div id="navbar" class="onload-animation z-50">
      <div class="absolute -top-8 left-0 right-0 h-8 bg-[var(--card-bg)] transition"></div>
      <div class="card-base relative mx-auto flex h-[4.5rem] max-w-[var(--page-width)] items-center justify-between !overflow-visible !rounded-t-none px-4">
        <a href="/" class="btn-plain scale-animation h-[3.25rem] rounded-lg px-5 font-bold active:scale-95">
          <div class="text-md flex flex-row items-center text-[var(--primary)]">
            <span class="icon-[tabler--smart-home] mb-1 mr-2 text-[1.75rem]"></span> <?php echo $this->options->title; ?>
          </div>
        </a>
        <div class="absolute left-1/2 hidden -translate-x-1/2 md:flex">
          <a href="/" class="btn-plain scale-animation h-11 rounded-lg px-5 font-bold active:scale-95">
            <div class="flex items-center"><?php echo __t('nav.home'); ?></div>
          </a>
          <a href="<?php echo $_archivesUrl; ?>" class="btn-plain scale-animation h-11 rounded-lg px-5 font-bold active:scale-95">
            <div class="flex items-center"><?php echo __t('nav.archives'); ?></div>
          </a>
          <?php foreach ($_navList as $_p): ?>
            <a href="<?php echo $_p['permalink']; ?>" class="btn-plain scale-animation h-11 rounded-lg px-5 font-bold active:scale-95">
              <div class="flex items-center"><?php echo $_p['title']; ?></div>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="flex items-center">
          <div id="search"></div>
          <?php if (!$this->options->themeColorFixed): ?>
          <button aria-label="Display Settings" class="btn-plain scale-animation h-11 w-11 rounded-lg active:scale-90" id="display-settings-switch">
            <span class="icon-[material-symbols--palette-outline] text-[1.25rem]"></span>
          </button>
          <?php endif; ?>
          <?php if ($this->options->enableChangeColorScheme): ?>
          <button aria-label="Light/Dark Mode" class="btn-plain scale-animation relative h-11 w-11 rounded-lg active:scale-90" id="scheme-switch">
            <span class="icon-[material-symbols--dark-mode-outline] text-[1.25rem]"></span>
          </button>
          <?php endif; ?>
          <button aria-label="Menu" name="Nav Menu" class="btn-plain scale-animation h-11 w-11 rounded-lg active:scale-90 md:!hidden" id="nav-menu-switch">
            <span class="icon-[material-symbols--menu-rounded] text-[1.25rem]"></span>
          </button>
        </div>
        <div id="nav-menu-panel" class="float-panel float-panel-closed absolute right-4 px-2 py-2">
          <a href="/" class="group flex items-center justify-between gap-8 rounded-lg py-2 pl-3 pr-1 transition hover:bg-[var(--btn-plain-bg-hover)] active:bg-[var(--btn-plain-bg-active)]">
            <div class="font-bold text-black/75 transition group-hover:text-[var(--primary)] group-active:text-[var(--primary)] dark:text-white/75">
              <?php echo __t('nav.home'); ?>
            </div>
            <span class="icon-[material-symbols--chevron-right-rounded] ml-1 -translate-y-[1px] text-[1.3rem] text-black/[0.2] transition dark:text-white/[0.2]"></span>
          </a>
          <a href="<?php echo $_archivesUrl; ?>" class="group flex items-center justify-between gap-8 rounded-lg py-2 pl-3 pr-1 transition hover:bg-[var(--btn-plain-bg-hover)] active:bg-[var(--btn-plain-bg-active)]">
            <div class="font-bold text-black/75 transition group-hover:text-[var(--primary)] group-active:text-[var(--primary)] dark:text-white/75">
              <?php echo __t('nav.archives'); ?>
            </div>
            <span class="icon-[material-symbols--chevron-right-rounded] ml-1 -translate-y-[1px] text-[1.3rem] text-black/[0.2] transition dark:text-white/[0.2]"></span>
          </a>
          <?php foreach ($_navList as $_p): ?>
            <a href="<?php echo $_p['permalink']; ?>" class="group flex items-center justify-between gap-8 rounded-lg py-2 pl-3 pr-1 transition hover:bg-[var(--btn-plain-bg-hover)] active:bg-[var(--btn-plain-bg-active)]">
              <div class="font-bold text-black/75 transition group-hover:text-[var(--primary)] group-active:text-[var(--primary)] dark:text-white/75">
                <?php echo $_p['title']; ?>
              </div>
              <span class="icon-[material-symbols--chevron-right-rounded] ml-1 -translate-y-[1px] text-[1.3rem] text-black/[0.2] transition dark:text-white/[0.2]"></span>
            </a>
          <?php endforeach; ?>
        </div>
        <div id="display-setting" class="float-panel float-panel-closed absolute right-4 w-80 px-4 py-4 transition-all"></div>
      </div>
    </div>
  </div>
</div>
