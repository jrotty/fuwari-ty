<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need("functions.php"); ?>
<?php
// 路由 type 区分格式: feed_xml_atom => Atom 1.0，否则 RSS 2.0
$isAtom = ($this->parameter->type === 'feed_xml_atom');
$siteUrl = rtrim(\Typecho\Common::url('/', $this->options->index), '/');
$feedUrl = $siteUrl . ($isAtom ? '/atom.xml' : '/rss.xml');

// Typecho 的 options->description 魔法属性访问会向输出流泄漏一段站点描述，
// 用缓冲区包住整个生成过程，确保最终只输出干净的 XML。
ob_start();

$feed = new \Typecho\Feed(
    \Typecho\Common::VERSION,
    $isAtom ? \Typecho\Feed::ATOM1 : \Typecho\Feed::RSS2,
    'UTF-8',
    $this->options->lang ?: 'en'
);
$feed->setTitle($this->options->title);
$feed->setSubTitle($this->options->description());
$feed->setFeedUrl($feedUrl);
$feed->setBaseUrl($siteUrl);

$posts = $this->widget('Widget_Contents_Post_Recent', 'pageSize=20');
while ($posts->next()) {
    // 排除密码保护 / 隐藏文章（草稿已被 Recent 在 SQL 层过滤）
    if (!$posts->allow('feed')) {
        continue;
    }
    // Atom 的 Feed 类在 category 上没有做属性转义，term/scheme 需自行转义才不至于让包含 & 等的分类名破坏 XML
    $esc = $isAtom ? fn($v) => htmlspecialchars($v, ENT_QUOTES | ENT_XML1, 'UTF-8') : fn($v) => $v;
    $cats = [];
    foreach ((array)$posts->categories as $c) {
        $cats[] = ['name' => $esc($c['name']), 'permalink' => $esc($c['permalink'])];
    }
    $tags = [];
    foreach ((array)$posts->tags as $t) {
        $tags[] = ['name' => $esc($t['name']), 'permalink' => $esc($t['permalink'])];
    }
    $feed->addItem([
        'title'    => $posts->title,
        'content'  => $posts->content,
        'excerpt'  => $posts->excerpt,
        'date'     => (int)$posts->modified,
        'link'     => $posts->permalink,
        'author'   => (object)[
            'screenName' => $posts->author->screenName,
            'url'        => $posts->author->url,
        ],
        'category' => array_merge($cats, $tags),
        'comments' => (string)$posts->commentsNum,
    ]);
}

$output = (string)$feed;
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . ($isAtom ? 'application/atom+xml' : 'application/rss+xml') . '; charset=UTF-8');
echo $output;
