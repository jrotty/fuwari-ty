<div id="banner-wrapper" class="absolute z-10 w-full overflow-hidden transition duration-700" style="top: -30vh">
    <div id="banner" class="relative h-full overflow-hidden object-cover transition duration-700">
      <div class="pointer-events-none absolute inset-0 bg-opacity-50 transition dark:bg-black/10"></div>
      <img src="<?php echo $this->options->bannerSrc; ?>" alt="Banner image of the blog" style="object-position: <?php echo $this->options->bannerPosition; ?>" width="1344" height="896" loading="lazy" decoding="async" class="h-full w-full object-cover">
    </div>
</div>
