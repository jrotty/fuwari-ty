<?php
$_socialIconMap = [
    'github.com'    => 'mdi--github',
    'twitter.com'   => 'streamline-logos--x-twitter-logo-block',
    'x.com'         => 'streamline-logos--x-twitter-logo-block',
    'weibo.com'     => 'simple-icons--sinaweibo',
    'zhihu.com'     => 'ant-design--zhihu-circle-filled',
    'bilibili.com'  => 'streamline-logos--bilibili-logo-block',
    'douban.com'    => 'streamline-logos--douban-logo-block',
    'tiktok.com'    => 'streamline-logos--tiktok-logo-block',
    'douyin.com'    => 'streamline-logos--tiktok-logo-block',
    'telegram.org'  => 'ic--baseline-telegram',
    't.me'          => 'ic--baseline-telegram',
    'facebook.com'  => 'ic--baseline-facebook',
    'instagram.com' => 'ant-design--instagram-filled',
    'linkedin.com'  => 'entypo-social--linkedin-with-circle',
    'youtube.com'   => 'entypo-social--youtube-with-circle',
    'steamcommunity.com' => 'ri--steam-fill',
    'gitlab.com'    => 'fa6-brands--square-gitlab',
    'slack.com'     => 'ant-design--slack-circle-filled',
    'discord.com'   => 'ic--baseline-discord',
    'discord.gg'    => 'ic--baseline-discord',
    'qq.com'        => 'fa6-brands--qq',
];

function _fuwariSocialIcon($url) {
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) return 'tabler--external-link';
    $host = strtolower(ltrim($host, 'www.'));
    global $_socialIconMap;
    return $_socialIconMap[$host] ?? 'tabler--external-link';
}
?>
<div class="card-base p-3">
  <a aria-label="Go to About Page" href="<?php echo $this->options->authorUrl; ?>" class="group relative mx-auto mb-3 mt-1 block max-w-[12rem] overflow-hidden rounded-xl active:scale-95 lg:mx-0 lg:mt-0 lg:max-w-none">
    <div class="pointer-events-none absolute z-50 flex h-full w-full items-center justify-center transition group-hover:bg-black/30 group-active:bg-black/50">
      <span class="icon-[fa6-regular--address-card] scale-90 text-5xl text-white opacity-0 transition group-hover:scale-100 group-hover:opacity-100"></span>
    </div>
    <?php $_avatar = $this->options->authorAvatar; ?>
    <img src="<?php echo $_avatar && $_avatar[0] !== '/' && !str_starts_with($_avatar, 'http') ? $this->options->themeUrl($_avatar, $this->options->theme) : $_avatar; ?>" alt="Profile Image of the Author" class="mx-auto h-full lg:mt-0 lg:w-full">
  </a>
  <div class="px-2">
    <div class="mb-1 text-center text-xl font-bold transition dark:text-neutral-50"><?php echo $this->options->authorName; ?></div>
    <div class="mx-auto mb-2 h-1 w-5 rounded-full bg-[var(--primary)] transition"></div>
    <div class="mb-2.5 text-center text-neutral-400 transition"><?php echo $this->options->authorBio; ?></div>
    <?php if ($this->options->socialLinks): ?>
    <?php $_links = array_filter(explode("\n", str_replace("\r", '', $this->options->socialLinks))); ?>
    <?php if ($_links): ?>
    <div class="mt-3 flex flex-wrap justify-center gap-2">
      <?php foreach ($_links as $_url): ?>
      <?php $_url = trim($_url); if (!$_url) continue; ?>
      <a href="<?php echo $_url; ?>" target="_blank" rel="noopener"
         class="flex h-8 w-8 items-center justify-center rounded-full text-neutral-400 transition hover:bg-[var(--primary)] hover:text-white active:scale-90"
         aria-label="Social link">
        <span class="icon-[<?php echo _fuwariSocialIcon($_url); ?>] text-lg"></span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
