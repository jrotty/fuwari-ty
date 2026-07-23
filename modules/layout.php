<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need("functions.php"); ?>
<?php
$this->PAGE_TYPE = $this->PAGE_TYPE ?? '';
$this->PAGE_META = $this->PAGE_META ?? '';
$this->CONTENT = $this->CONTENT ?? '';
?>
<!DOCTYPE html>
<html lang="zh" class="bg-[var(--page-bg)] text-[14px] transition md:text-[16px]" data-overlayscrollbars-initialize>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->options->siteTitle; ?><?php if ($this->options->description()): ?> - <?php echo $this->options->description(); ?><?php endif; ?></title>
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/dist/main.css'); ?>" />
    <script>
      (function () {
        const DEFAULT_THEME = "<?php echo $this->options->colorScheme; ?>" || "auto";
        const LIGHT_MODE = "light";
        const DARK_MODE = "dark";
        const AUTO_MODE = "auto";
        const BANNER_HEIGHT = 35;
        const BANNER_HEIGHT_EXTEND = 30;
        const BANNER_HEIGHT_HOME = BANNER_HEIGHT + BANNER_HEIGHT_EXTEND;
        const PAGE_WIDTH = 75;
        const configHue = <?php echo (int)$this->options->themeColorHue; ?> || 250;
        const banner_position = "<?php echo $this->options->bannerPosition; ?>" || "center";
        const bannerOffsetByPosition = {
          top: `${BANNER_HEIGHT_EXTEND}vh`,
          center: `${BANNER_HEIGHT_EXTEND / 2}vh`,
          bottom: "0",
        };
        const bannerOffset = bannerOffsetByPosition[banner_position || "center"];

        const theme = localStorage.getItem("color-scheme-fuwari") || DEFAULT_THEME;
        switch (theme) {
          case LIGHT_MODE:
            document.documentElement.classList.remove("dark");
            break;
          case DARK_MODE:
            document.documentElement.classList.add("dark");
            break;
          case AUTO_MODE:
            if (window.matchMedia("(prefers-color-scheme: dark)").matches) {
              document.documentElement.classList.add("dark");
            } else {
              document.documentElement.classList.remove("dark");
            }
        }

        const storedHue = localStorage.getItem("hue");
        const hue = (storedHue && parseInt(storedHue) === configHue) ? storedHue : String(configHue);
        if (!storedHue || parseInt(storedHue) !== configHue) {
          localStorage.setItem("hue", String(configHue));
        }
        document.documentElement.style.setProperty("--hue", hue);

        let offset = Math.floor(window.innerHeight * (BANNER_HEIGHT_EXTEND / 100));
        offset = offset - (offset % 4);
        document.documentElement.style.setProperty("--banner-height-extend", `${offset}px`);
        document.documentElement.style.setProperty("--page-width", `${PAGE_WIDTH}rem`);
        document.documentElement.style.setProperty("--configHue", `${configHue}`);
        document.documentElement.style.setProperty("--banner-height", `${BANNER_HEIGHT}vh`);
        document.documentElement.style.setProperty("--banner-height-home", `${BANNER_HEIGHT_HOME}vh`);
        document.documentElement.style.setProperty("--bannerOffset", bannerOffset);
      })();
    </script>

    <?php echo $this->PAGE_META; ?>
</head>

<body class="min-h-screen transition<?php if ($this->PAGE_TYPE === 'home'): ?> is-home<?php endif; ?><?php if ($this->options->bannerEnable): ?> enable-banner<?php endif; ?>" data-page-type="<?php echo $this->PAGE_TYPE; ?>" data-overlayscrollbars-initialize>
    <?php $this->need("modules/config-carrier.php"); ?>
    <?php $this->need("modules/top-row.php"); ?>
    
    <?php if ($this->options->bannerEnable): ?>
    <?php $this->need("modules/banner-wrapper.php"); ?>
    <?php endif; ?>
    
    <div id="content-area-wrapper" class="pointer-events-none absolute z-30 w-full" style="top: calc(35vh - 3.5rem)">
      <div class="pointer-events-auto relative mx-auto max-w-[var(--page-width)]">
        <div id="main-grid" class="left-0 right-0 mx-auto grid w-full grid-cols-[17.5rem_auto] grid-rows-[auto_1fr_auto] gap-4 px-0 transition duration-700 md:px-4 lg:grid-rows-[auto]">
          <?php if ($this->options->bannerCreditEnable): ?>
          <?php $this->need("modules/widgets/banner-credit.php"); ?>
          <?php endif; ?>
          <?php $this->need("modules/sideBar.php"); ?>

          <main id="swup-container" class="transition-swup-fade col-span-2 overflow-hidden lg:col-span-1">
            <div id="content-wrapper" class="onload-animation">
              <?php echo $this->CONTENT; ?>

              <div class="footer onload-animation col-span-2 hidden lg:block">
                <?php $this->need("modules/footer.php"); ?>
              </div>
            </div>
          </main>
          <div class="footer onload-animation col-span-2 block lg:hidden">
            <?php $this->need("modules/footer.php"); ?>
          </div>
        </div>
        <?php $this->need("modules/widgets/back-to-top.php"); ?>
      </div>

      <?php if ($this->options->tocEnable): ?>
      <div class="absolute z-0 hidden w-full 2xl:block">
        <div class="relative mx-auto max-w-[var(--page-width)]">
          <div id="toc-wrapper" class="absolute -right-[var(--toc-width)] top-0 hidden w-[var(--toc-width)] items-center transition lg:block<?php if ($this->options->bannerEnable): ?> toc-hide<?php endif; ?>">
            <div id="toc-inner-wrapper" class="hide-scrollbar fixed top-14 h-[calc(100vh_-_20rem)] w-[var(--toc-width)] overflow-x-hidden overflow-y-scroll">
              <div id="toc" class="transition-swup-fade h-full w-full">
                <div class="h-8 w-full"></div>
                <div class="toc"></div>
                <div class="h-8 w-full"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div id="toc"></div>
      <?php endif; ?>
    </div>

    <div id="page-height-extend" class="hidden h-[300vh]"></div>
    <script type="module" src="<?php $this->options->themeUrl('assets/dist/main.js'); ?>"></script>
</body>
</html>
