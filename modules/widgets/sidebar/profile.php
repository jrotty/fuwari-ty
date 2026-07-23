<div class="card-base p-3">
  <a aria-label="Go to About Page" href="<?php echo $this->options->authorUrl; ?>" class="group relative mx-auto mb-3 mt-1 block max-w-[12rem] overflow-hidden rounded-xl active:scale-95 lg:mx-0 lg:mt-0 lg:max-w-none">
    <div class="pointer-events-none absolute z-50 flex h-full w-full items-center justify-center transition group-hover:bg-black/30 group-active:bg-black/50">
      <span class="icon-[fa6-regular--address-card] scale-90 text-5xl text-white opacity-0 transition group-hover:scale-100 group-hover:opacity-100"></span>
    </div>
    <img src="<?php echo $this->options->authorAvatar; ?>" alt="Profile Image of the Author" class="mx-auto h-full lg:mt-0 lg:w-full">
  </a>
  <div class="px-2">
    <div class="mb-1 text-center text-xl font-bold transition dark:text-neutral-50"><?php echo $this->options->authorName; ?></div>
    <div class="mx-auto mb-2 h-1 w-5 rounded-full bg-[var(--primary)] transition"></div>
    <div class="mb-2.5 text-center text-neutral-400 transition"><?php echo $this->options->authorBio; ?></div>
  </div>
</div>
