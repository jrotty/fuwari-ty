<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need("functions.php"); ?>
<?php
$this->PAGE_TYPE = 'archives';
ob_start();
?>
<meta name="description" content="归档 - <?php echo $this->options->siteTitle; ?>">
<?php $this->PAGE_META = ob_get_clean(); ?>
<?php ob_start(); ?>
<div class="card-base px-8 py-6">
  <?php
  $archives = $this->widget('Widget_Contents_Post_Recent', 'pageSize=10000');
  $postsByYear = array();
  while ($archives->next()):
    $year = date('Y', strtotime($archives->date));
    if (!isset($postsByYear[$year])) $postsByYear[$year] = array();
    $postsByYear[$year][] = array(
      'permalink' => $archives->permalink,
      'title' => $archives->title,
      'date' => $archives->date,
      'tags' => getTagsString($archives->tags),
    );
  endwhile;
  krsort($postsByYear);
  ?>
  <?php if (count($postsByYear) > 0): ?>
  <?php foreach ($postsByYear as $year => $posts): ?>
  <div>
    <div class="flex h-[3.75rem] w-full flex-row items-center">
      <div class="text-75 w-[15%] text-right text-2xl font-bold transition md:w-[10%]">
        <?php echo $year; ?>
      </div>
      <div class="w-[15%] md:w-[10%]">
        <div class="outline-3 z-50 mx-auto h-3 w-3 rounded-full bg-none outline -outline-offset-[2px] outline-[var(--primary)]"></div>
      </div>
      <div class="text-50 w-[70%] text-left transition md:w-[80%]">
        <?php echo count($posts); ?> 篇文章
      </div>
    </div>

    <?php foreach ($posts as $post): ?>
    <a href="<?php echo $post['permalink']; ?>" aria-label="<?php echo $post['title']; ?>" class="btn-plain group !block h-10 w-full rounded-lg hover:text-[initial]">
      <div class="flex h-full flex-row items-center justify-start">
        <!-- date -->
        <div class="text-50 w-[15%] text-right text-sm transition md:w-[10%]">
          <?php echo date('m-d', strtotime($post['date'])); ?>
        </div>

        <!-- dot and line -->
        <div class="dash-line relative flex h-full w-[15%] items-center md:w-[10%]">
          <div class="z-50 mx-auto h-1 w-1 rounded bg-[oklch(0.5_0.05_var(--hue))] outline outline-4 outline-[var(--card-bg)] transition-all group-hover:h-5 group-hover:bg-[var(--primary)] group-hover:outline-[var(--btn-plain-bg-hover)] group-active:outline-[var(--btn-plain-bg-active)]"></div>
        </div>

        <!-- post title -->
        <div class="text-75 w-[70%] overflow-hidden overflow-ellipsis whitespace-nowrap pr-8 text-left font-bold transition-all group-hover:translate-x-1 group-hover:text-[var(--primary)] md:w-[65%] md:max-w-[65%]">
          <?php echo $post['title']; ?>
        </div>

        <!-- tag list -->
        <div class="text-30 hidden overflow-hidden overflow-ellipsis whitespace-nowrap text-left text-sm transition md:block md:w-[15%]">
          <?php echo '#' . str_replace(',', ' #', $post['tags']); ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
  <?php else: ?>
  <div class="text-center py-12">
    <p class="text-black/60 dark:text-white/60">暂无归档文章</p>
  </div>
  <?php endif; ?>
</div>
<?php $this->CONTENT = ob_get_clean(); ?>
<?php $this->need("modules/layout.php"); ?>
