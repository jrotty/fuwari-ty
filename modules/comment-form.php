<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
require_once __DIR__ . '/comment-util.php';
$_cOptions = $this->options;
$_cUser = $this->user;
$_cLogin = $_cUser->hasLogin();
$_cGhBtn = comment_github_button($_cOptions, $this->permalink);
$_cAuthor = \Typecho\Cookie::get('__typecho_remember_author', '');
$_cMail = \Typecho\Cookie::get('__typecho_remember_mail', '');
$_cUrl = \Typecho\Cookie::get('__typecho_remember_url', '');
?>
<form class="comma-form" id="comma-form">
  <input type="hidden" name="parent" value="0">

  <?php if ($_cLogin): ?>
  <!-- 已登录（站点账号 / GitHub） -->
  <div class="comma-identity-box">
    <img class="comma-identity-avatar" src="<?php echo htmlspecialchars(comment_avatar($_cUser->mail, '', $_cUser->screenName)); ?>" alt="" width="28" height="28" onerror="this.onerror=null;this.src=this.dataset.fallback" data-fallback="<?php echo htmlspecialchars(comment_letter_avatar($_cUser->screenName)); ?>">
    <span class="comma-identity-name"><?php echo htmlspecialchars($_cUser->screenName); ?></span>
  </div>
  <?php else: ?>
  <!-- 游客：实时身份（JS 更新，直角容器） -->
  <div class="comma-identity-box" data-guest hidden>
    <img class="comma-identity-avatar" src="" alt="" width="28" height="28">
    <span class="comma-identity-name"></span>
  </div>
  <?php endif; ?>

  <textarea id="comma-text" name="text" rows="4" maxlength="3000" required
            class="comma-textarea" placeholder="<?php echo __t('comments.contentPlaceholder'); ?>"></textarea>
  <div class="comma-toolbar">
    <span class="comma-hint"><?php echo __t('comments.mdHint'); ?></span>
    <span class="comma-count">0/3000</span>
  </div>

  <?php if (!$_cLogin): ?>
  <div class="comma-fields">
    <input type="text" id="comma-author" name="author" maxlength="150" required
           class="comma-input" value="<?php echo htmlspecialchars($_cAuthor); ?>" placeholder="<?php echo __t('comments.name'); ?>">
    <input type="email" id="comma-mail" name="mail" maxlength="150" required
           class="comma-input" value="<?php echo htmlspecialchars($_cMail); ?>" placeholder="<?php echo __t('comments.mail'); ?>">
    <input type="url" id="comma-url" name="url" maxlength="255"
           class="comma-input" value="<?php echo htmlspecialchars($_cUrl); ?>" placeholder="<?php echo __t('comments.url'); ?>">
  </div>
  <?php endif; ?>

  <div class="comma-bottom">
    <?php if (!$_cLogin && $_cGhBtn !== ''): ?>
      <?php echo $_cGhBtn; ?>
    <?php endif; ?>
    <span class="comma-spacer"></span>
    <button type="submit" class="comma-submit-btn"><?php echo __t('comments.submit'); ?></button>
  </div>
</form>
