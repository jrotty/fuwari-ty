<div id="sidebar" class="onload-animation col-span-2 row-start-2 row-end-3 mb-4 w-full lg:col-span-1 lg:row-start-1 lg:row-end-2 lg:max-w-[17.5rem]">
  <div class="mb-4 flex w-full flex-col gap-4">
    <?php $this->need("modules/widgets/sidebar/profile.php"); ?>
  </div>
  <div id="sidebar-sticky" class="sticky top-4 flex w-full flex-col gap-4 transition-all duration-700">
    <?php $this->need("modules/widgets/sidebar/categories.php"); ?>
    <?php $this->need("modules/widgets/sidebar/tag.php"); ?>
    <?php $this->need("modules/widgets/sidebar/popular-posts.php"); ?>
  </div>
</div>
