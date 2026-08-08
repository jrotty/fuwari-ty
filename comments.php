<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;

$this->need("modules/comment-util.php");
if (!function_exists('fuwari_comments_render')) {
    function fuwari_comments_render($archive)
    {
        if (!comment_ensure_tables(\Typecho\Db::get())) return;
        $user = fuwari_user();
        $options = fuwari_options();
        $myVotes = fuwari_comments_my_votes(\Typecho\Db::get());
        $sort = fuwari_request()->get('commentsort', 'recommend');
        if (!in_array($sort, ['recommend', 'latest', 'oldest'], true)) $sort = 'recommend';
        $ghBtn = comment_github_button($options, $archive->permalink);
        ?>
<div class="fuwari-comments card-base relative z-10 w-full px-6 pb-6 pt-6 md:px-9 mt-4" data-cid="<?php echo $archive->cid; ?>" data-login="<?php echo $user->hasLogin() ? '1' : '0'; ?>">
  <div class="comma-header">
    <div class="comma-title">
      <span><?php echo __t('comments.title'); ?></span>
      <span class="comma-title-count" data-comma-total><?php echo (int)$archive->commentsNum; ?></span>
    </div>
    <div class="comma-header-right">
      <button type="button" class="comma-sort <?php echo $sort === 'recommend' ? 'comma-sort-on' : ''; ?>" data-sort="recommend"><?php echo __t('comments.sort.recommend'); ?></button>
      <button type="button" class="comma-sort <?php echo $sort === 'latest' ? 'comma-sort-on' : ''; ?>" data-sort="latest"><?php echo __t('comments.sort.latest'); ?></button>
      <button type="button" class="comma-sort <?php echo $sort === 'oldest' ? 'comma-sort-on' : ''; ?>" data-sort="oldest"><?php echo __t('comments.sort.oldest'); ?></button>
      <button type="button" class="comma-refresh js-comma-refresh" title="<?php echo __t('comments.refresh'); ?>" aria-label="<?php echo __t('comments.refresh'); ?>">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M12 5V2L7 6l5 4V7c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8Zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 8.74C4.46 9.97 4 11.43 4 13c0 4.42 3.58 8 8 8v3l5-4-5-4v3Z"/></svg>
      </button>
    </div>
  </div>

  <div class="comma-form-wrap">
    <div class="comma-replying" hidden></div>
    <?php $archive->need("modules/comment-form.php"); ?>
  </div>

  <ul class="comma-list"></ul>
  <button type="button" class="comma-more js-comma-more" hidden><?php echo __t('comments.loadMore'); ?></button>
</div>
<script type="application/json" id="comma-json"><?php
        echo json_encode([
            'cid' => $archive->cid, 'sort' => $sort, 'login' => $user->hasLogin(),
            'myVotes' => $myVotes,
            'token' => md5($options->secret . '&comments'),
        ], JSON_UNESCAPED_UNICODE);
        ?></script>
<?php
    }
}
fuwari_comments_render($this);
