<?php $this->widget('Widget_Contents_Page_List')->to($_navPages); ?>
<div id="top-row" class="pointer-events-none relative z-50 mx-auto max-w-[var(--page-width)] px-0 transition-all duration-700 md:px-4">
  <div id="navbar-wrapper" class="pointer-events-auto sticky top-0 transition-all">
    <div id="navbar" class="onload-animation z-50">
      <div class="absolute -top-8 left-0 right-0 h-8 bg-[var(--card-bg)] transition"></div>
      <div class="card-base mx-auto flex h-[4.5rem] max-w-[var(--page-width)] items-center justify-between !overflow-visible !rounded-t-none px-4">
        <a href="/" class="btn-plain scale-animation h-[3.25rem] rounded-lg px-5 font-bold active:scale-95">
          <div class="text-md flex flex-row items-center text-[var(--primary)]">
            <span class="icon-[tabler--smart-home] mb-1 mr-2 text-[1.75rem]"></span> <?php echo $this->options->siteTitle; ?>
          </div>
        </a>
        <div class="hidden md:flex flex-1 justify-center">
          <?php while ($_navPages->next()): ?>
            <a href="<?php echo $_navPages->permalink; ?>" class="btn-plain scale-animation h-11 rounded-lg px-5 font-bold active:scale-95">
              <div class="flex items-center">
                <?php echo $_navPages->title; ?>
              </div>
            </a>
          <?php endwhile; ?>
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
          <?php $this->widget('Widget_Contents_Page_List')->to($_navPages); ?>
          <?php while ($_navPages->next()): ?>
            <a href="<?php echo $_navPages->permalink; ?>" class="group flex items-center justify-between gap-8 rounded-lg py-2 pl-3 pr-1 transition hover:bg-[var(--btn-plain-bg-hover)] active:bg-[var(--btn-plain-bg-active)]">
              <div class="font-bold text-black/75 transition group-hover:text-[var(--primary)] group-active:text-[var(--primary)] dark:text-white/75">
                <?php echo $_navPages->title; ?>
              </div>
              <span class="icon-[material-symbols--chevron-right-rounded] ml-1 -translate-y-[1px] text-[1.3rem] text-black/[0.2] transition dark:text-white/[0.2]"></span>
            </a>
          <?php endwhile; ?>
        </div>
        <div id="display-setting" class="float-panel float-panel-closed absolute right-4 w-80 px-4 py-4 transition-all"></div>
      </div>
    </div>
  </div>
</div>
