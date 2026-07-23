<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<div id="comments" class="card-base relative z-10 w-full px-6 pb-4 pt-6 md:px-9">
  <?php $this->comments()->to($_comments); ?>

  <?php if ($_comments->have()): ?>
    <div class="mb-4 flex items-center gap-2">
      <span class="icon-[material-symbols--chat-outline] text-[1.5rem] text-[var(--primary)]"></span>
      <span class="text-lg font-bold"><?php $_comments->commentsNum('评论', '1 条评论', '%d 条评论'); ?></span>
    </div>

    <?php $_comments->listComments(); ?>

    <?php $_comments->pageNav('&laquo;', '&raquo;'); ?>
  <?php endif; ?>

  <?php if ($this->allow('comment')): ?>
    <div id="<?php $this->respondId(); ?>" class="respond mt-6">
      <div class="cancel-comment-reply mb-2">
        <button class="btn-plain h-8 rounded-lg px-3 text-sm"><?php $_comments->cancelReply(); ?></button>
      </div>

      <div class="mb-3 flex items-center gap-2">
        <span class="icon-[material-symbols--edit-outline] text-[1.5rem] text-[var(--primary)]"></span>
        <span class="text-lg font-bold">添加新评论</span>
      </div>

      <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form" role="form" class="flex flex-col gap-3">
        <div>
          <label class="mb-1 block text-sm font-bold text-neutral-600 dark:text-neutral-400">内容</label>
          <textarea rows="4" name="text" id="textarea" class="w-full rounded-lg border border-black/10 bg-transparent px-3 py-2 text-sm transition focus:border-[var(--primary)] focus:outline-none dark:border-white/10" required><?php $this->remember('text'); ?></textarea>
        </div>

        <?php if (!$this->user->hasLogin()): ?>
        <div class="flex flex-wrap gap-3">
          <div class="flex-1">
            <label for="author" class="mb-1 block text-sm font-bold text-neutral-600 dark:text-neutral-400">称呼</label>
            <input type="text" name="author" id="author" class="w-full rounded-lg border border-black/10 bg-transparent px-3 py-2 text-sm transition focus:border-[var(--primary)] focus:outline-none dark:border-white/10" value="<?php $this->remember('author'); ?>" required />
          </div>
          <div class="flex-1">
            <label for="mail" class="mb-1 block text-sm font-bold text-neutral-600 dark:text-neutral-400">Email</label>
            <input type="email" name="mail" id="mail" class="w-full rounded-lg border border-black/10 bg-transparent px-3 py-2 text-sm transition focus:border-[var(--primary)] focus:outline-none dark:border-white/10" value="<?php $this->remember('mail'); ?>" />
          </div>
          <div class="flex-1">
            <label for="url" class="mb-1 block text-sm font-bold text-neutral-600 dark:text-neutral-400">网站</label>
            <input type="url" name="url" id="url" class="w-full rounded-lg border border-black/10 bg-transparent px-3 py-2 text-sm transition focus:border-[var(--primary)] focus:outline-none dark:border-white/10" placeholder="http://" value="<?php $this->remember('url'); ?>" />
          </div>
        </div>
        <?php endif; ?>

        <div>
          <button type="submit" class="btn-regular h-9 rounded-lg px-5 font-bold">提交评论</button>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="flex items-center justify-center py-8 text-neutral-500">评论已关闭</div>
  <?php endif; ?>
</div>
