<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/* ---- 评论系统纯函数库：UA 解析 / IP 归属地 / 推荐评分 / 评论项渲染 ---- */

/* user/options 是 widget 的 protected 属性，独立函数里 $archive->user 走 __get 拿不到，改用单例池 */
if (!function_exists('fuwari_user')) {
    function fuwari_user() { return \Typecho\Widget::widget('Widget_User'); }
}
if (!function_exists('fuwari_options')) {
    function fuwari_options() { return \Typecho\Widget::widget('Widget_Options'); }
}
/* SQL 字符串转义（Db 无公开 API，走适配器） */
if (!function_exists('fuwari_quote')) {
    function fuwari_quote($db, $str) { return $db->getAdapter()->quoteValue((string)$str); }
}

if (!function_exists('comment_ensure_tables')) {
    function comment_ensure_tables($db)
    {
        static $done = false;
        if ($done) return true;
        $p = $db->getPrefix();
        try {
            foreach ([
                'comment_votes' => "CREATE TABLE IF NOT EXISTS {$p}comment_votes (
                    coid INT NOT NULL, voter_hash VARCHAR(64) NOT NULL,
                    vote_type SMALLINT NOT NULL DEFAULT 0, created INT NOT NULL DEFAULT 0,
                    PRIMARY KEY (coid, voter_hash))",
                'comment_geo' => "CREATE TABLE IF NOT EXISTS {$p}comment_geo (
                    coid INT NOT NULL PRIMARY KEY, region VARCHAR(64) NOT NULL DEFAULT '',
                    avatar VARCHAR(255) NOT NULL DEFAULT '')",
            ] as $name => $sql) {
                $r = $db->fetchRow($db->query("SELECT to_regclass('" . $p . $name . "')"));
                if (!$r || $r['to_regclass'] === null) {
                    $db->query($sql);
                }
            }
            $done = true;
            return true;
        } catch (\Throwable $e) {
            error_log('comment_ensure_tables: ' . $e->getMessage());
            return false;
        }
    }
}

/* 推荐排序：F = log10(Z) + y*TS/45000，Z=|赞-踩|，y=赞>踩?1:赞<踩?-1:0 */
if (!function_exists('comment_score')) {
    function comment_score($likes, $dislikes, $created)
    {
        $z = abs((int)$likes - (int)$dislikes);
        $y = $likes > $dislikes ? 1 : ($likes < $dislikes ? -1 : 0);
        return ($z <= 0 ? 0.0 : log10($z)) + $y * (int)$created / 45000.0;
    }
}

/* 匿名投票身份：cookie 随机串（60 天）；登录用户 u{uid} */
if (!function_exists('comment_voter_id')) {
    function comment_voter_id($user)
    {
        if ($user->hasLogin()) return 'u' . $user->uid;
        $id = \Typecho\Cookie::get('fuwari_guest_id', '');
        if ($id === '' || !preg_match('/^[0-9a-f]{32}$/', $id)) {
            $id = bin2hex(random_bytes(16));
            \Typecho\Cookie::set('fuwari_guest_id', $id, 60 * 86400);
        }
        return $id;
    }
}
/* 取请求对象（独立函数里拿不到 widget 的 protected $request） */
if (!function_exists('fuwari_request')) {
    function fuwari_request() { return \Typecho\Request::getInstance(); }
}

/* IP 归属地查询（ip-api.com），失败返回空串 */
if (!function_exists('comment_lookup_region')) {
    function comment_lookup_region($ip)
    {
        try {
            $ctx = stream_context_create(['http' => [
                'timeout' => 3,
                'header'  => "User-Agent: fuwari-ty\r\n",
            ]]);
            $resp = @file_get_contents('http://ip-api.com/json/' . rawurlencode($ip) . '?lang=zh-CN&fields=status,regionName,city', false, $ctx);
            $json = json_decode((string)$resp, true);
            if (is_array($json) && ($json['status'] ?? '') === 'success') {
                $region = trim(($json['regionName'] ?? '') . ' ' . ($json['city'] ?? ''));
                return mb_substr($region, 0, 64);
            }
        } catch (\Throwable $e) {
            error_log('comment_lookup_region: ' . $e->getMessage());
        }
        return '';
    }
}

/* UA 解析：返回 [OS, Browser]，如 ['Windows', 'Chrome 126'] */
if (!function_exists('comment_parse_agent')) {
    function comment_parse_agent($agent)
    {
        $a = (string)$agent;
        if (strpos($a, 'Windows') !== false) $os = 'Windows';
        elseif (strpos($a, 'Android') !== false) $os = 'Android';
        elseif (strpos($a, 'iPhone') !== false || strpos($a, 'iPad') !== false) $os = 'iOS';
        elseif (strpos($a, 'Mac OS X') !== false) $os = 'macOS';
        elseif (strpos($a, 'Linux') !== false) $os = 'Linux';
        else $os = '';

        if (preg_match('/Edg\/([\d.]+)/', $a, $m)) $browser = 'Edge ' . $m[1];
        elseif (preg_match('/OPR\/([\d.]+)/', $a, $m)) $browser = 'Opera ' . $m[1];
        elseif (preg_match('/Vivaldi\/([\d.]+)/', $a, $m)) $browser = 'Vivaldi ' . $m[1];
        elseif (preg_match('/Firefox\/([\d.]+)/', $a, $m)) $browser = 'Firefox ' . $m[1];
        elseif (preg_match('/Chrome\/([\d.]+)/', $a, $m)) $browser = 'Chrome ' . $m[1];
        elseif (preg_match('/Safari\/([\d.]+)/', $a, $m)) $browser = 'Safari ' . $m[1];
        else $browser = '';

        return [$os, $browser];
    }
}

/* 头像规则：QQ 邮箱 → QQ 头像；GitHub → GitHub 头像；否则主题色首字母 */
if (!function_exists('comment_avatar')) {
    function comment_avatar($mail, $avatar, $name = '')
    {
        if ($avatar !== '') return $avatar; // GitHub 头像
        $mail = strtolower(trim((string)$mail));
        if (preg_match('/^[0-9]+@qq\.com$/', $mail)) {
            return 'https://q1.qlogo.cn/g?b=qq&nk=' . substr($mail, 0, strpos($mail, '@')) . '&s=100';
        }
        return comment_letter_avatar($name ?: $mail);
    }
}
/* 主题色首字母头像（data URI SVG） */
if (!function_exists('comment_letter_avatar')) {
    function comment_letter_avatar($name)
    {
        $letter = mb_strtoupper(mb_substr(trim((string)$name), 0, 1)) ?: '?';
        return 'data:image/svg+xml;charset=utf-8,' . rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" fill="oklch(0.55 0.12 250)"/><text x="32" y="43" font-family="sans-serif" font-size="28" font-weight="700" fill="#fff" text-anchor="middle">' . htmlspecialchars($letter) . '</text></svg>'
        );
    }
}

/* 回复线程收集：所有评论 → 按 parent 分组的树（最多 depth 层，超出平级） */
if (!function_exists('comment_build_tree')) {
    function comment_build_tree(array $rows)
    {
        $byParent = [];
        foreach ($rows as $r) $byParent[(int)$r['parent']][] = $r;
        $tree = [];
        $flat = [];
        $walk = function ($parent, $depth) use (&$walk, &$byParent, &$tree, &$flat) {
            foreach ($byParent[$parent] ?? [] as $r) {
                $r['depth'] = $depth;
                $flat[$r['coid']] = $r;
                if ($depth < 2) $tree[$r['coid']] = $r; // 深度 0/1 为树根
            }
            foreach ($byParent[$parent] ?? [] as $r) {
                if ($depth < 2) $walk($r['coid'], $depth + 1);
            }
        };
        $walk(0, 0);
        return [$tree, $flat];
    }
}

/* 渲染单条评论 li（含子线程） */
if (!function_exists('comment_render_item')) {
    function comment_render_item(array $c, $options, $user, $me)
    {
        $out = '<li class="comma-item" data-coid="' . (int)$c['coid'] . '" id="comment-' . (int)$c['coid'] . '">';
        $out .= '<div class="comma-avatar">';
        $out .= '<img src="' . htmlspecialchars(comment_avatar($c['mail'] ?? '', $c['avatar'] ?? '', $c['author'] ?? '')) . '" alt="" loading="lazy" width="30" height="30">';
        $out .= '</div>';
        $out .= '<div class="comma-body">';

        $author = htmlspecialchars($c['author'] ?? '');
        if (!empty($c['url'])) {
            $author = '<a href="' . htmlspecialchars($c['url']) . '" rel="external nofollow" target="_blank">' . $author . '</a>';
        }
        $out .= '<div class="comma-head">';
        $out .= '<span class="comma-author">' . $author . '</span>';
        if ($c['authorId'] > 0 && $c['authorId'] == $c['ownerId']) {
            $out .= '<span class="comma-badge">' . __t('comments.author') . '</span>';
        }
        $out .= '<div class="comma-meta">';
        $out .= '<span>' . date('Y-m-d H:i', (int)$c['created']) . '</span>';
        if (!empty($c['region'])) {
            $out .= '<span class="dot"></span><span>' . htmlspecialchars($c['region']) . '</span>';
        }
        list($os, $browser) = comment_parse_agent($c['agent'] ?? '');
        if ($os !== '') $out .= '<span class="dot"></span><span>' . htmlspecialchars($os) . '</span>';
        if ($browser !== '') $out .= '<span class="dot"></span><span>' . htmlspecialchars($browser) . '</span>';
        $out .= '</div>';
        $out .= '</div>';

        $html = $options->commentsMarkdown
            ? \Widget\Base\Comments::alloc()->markdown($c['text'])
            : \Widget\Base\Comments::alloc()->autoP($c['text']);
        $out .= '<div class="comma-text custom-md prose prose-base !max-w-none dark:!prose-invert">' . $html . '</div>';

        $out .= '<div class="comma-actions">';
        $likes = (int)($c['likes'] ?? 0);
        $my = $c['my'] ?? '';
        // 点赞/踩：Material 风格实心图标（踩图标仍显示，但计数隐藏）
        $out .= '<button type="button" class="comma-action js-vote' . ($my === 'like' ? ' voted' : '') . '" data-coid="' . (int)$c['coid'] . '" data-vote="like" aria-label="' . __t('comments.like') . '" title="' . __t('comments.like') . '">'
            . '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>'
            . '<span class="comma-count">' . $likes . '</span></button>';
        $out .= '<button type="button" class="comma-action js-vote' . ($my === 'dislike' ? ' voted' : '') . '" data-coid="' . (int)$c['coid'] . '" data-vote="dislike" aria-label="' . __t('comments.dislike') . '" title="' . __t('comments.dislike') . '">'
            . '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 14V2"/><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z"/></svg></button>';
        if ($c['depth'] < 2) {
            $out .= '<button type="button" class="comma-action js-reply" data-coid="' . (int)$c['coid'] . '" data-author="' . htmlspecialchars($c['author'] ?? '') . '">' . __t('comments.reply') . '</button>';
        }
        if (!empty($c['deletable'])) {
            $out .= '<button type="button" class="comma-action danger js-delete" data-coid="' . (int)$c['coid'] . '">' . __t('comments.delete') . '</button>';
        }
        $out .= '</div>';

        if (!empty($c['children'])) {
            $out .= '<ul class="comma-children">' . $c['children'] . '</ul>';
        }
        $out .= '</div></li>';
        return $out;
    }
}

/* 渲染整棵评论树（树根按给定顺序） */
if (!function_exists('comment_render_tree')) {
    function comment_render_tree(array $tree, array $flat, $options, $user, $me)
    {
        if (empty($tree)) {
            return '<li class="comma-empty"><span class="comma-empty-icon">'
                . '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor" aria-hidden="true"><path d="M12 3C6.48 3 2 6.58 2 11c0 2.52 1.36 4.79 3.5 6.31-.1 1.05-.56 2.44-1.5 3.69 0 0 2.13-.1 4.01-1.67.6.19 1.24.3 1.99.3 5.52 0 10-3.58 10-8S17.52 3 12 3Z"/></svg></span>'
                . '<span class="comma-empty-text">' . __t('comments.empty') . '</span></li>';
        }
        $html = '';
        foreach ($tree as $root) {
            $children = '';
            foreach ($flat as $r) {
                if ((int)$r['parent'] === (int)$root['coid']) {
                    $children .= comment_render_item($r, $options, $user, $me);
                }
            }
            $root['children'] = $children;
            $html .= comment_render_item($root, $options, $user, $me);
        }
        return $html;
    }
}

/* 当前用户的投票记录：coid => like|dislike（渲染高亮 + 端点复用） */
if (!function_exists('fuwari_comments_my_votes')) {
    function fuwari_comments_my_votes($db)
    {
        $p = $db->getPrefix();
        $me = comment_voter_id(fuwari_user());
        $rows = $db->fetchAll($db->query(
            "SELECT coid, vote_type FROM {$p}comment_votes WHERE voter_hash = " . fuwari_quote($db, $me)
        ));
        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['coid']] = (int)$r['vote_type'] > 0 ? 'like' : 'dislike';
        }
        return $map;
    }
}

/* ---- 端点：list ---- */
if (!function_exists('fuwari_comments_list')) {
    function fuwari_comments_list($archive)
    {
        $db = \Typecho\Db::get();
        $p = $db->getPrefix();
        $req = fuwari_request();
        $options = fuwari_options();
        $user = fuwari_user();
        $cid = (int)$req->get('cid', 0);
        $sort = $req->get('sort', 'recommend');
        if (!in_array($sort, ['recommend', 'latest', 'oldest'], true)) $sort = 'recommend';
        $page = max(1, (int)$req->get('page', 1));
        $perPage = max(1, (int)($options->commentPerPage ?? 8));
        $isAdmin = $user->pass('editor', true);
        $me = comment_voter_id($user);
        $q = fuwari_quote($db, $me);
        $isMyUid = $user->hasLogin() ? (int)$user->uid : 0;

        $rows = $db->fetchAll($db->query(
            "SELECT c.coid, c.cid, c.author, c.mail, c.url, c.ip, c.\"authorId\", c.\"ownerId\", c.agent, c.text, c.created, c.parent, " .
            "g.region, g.avatar, " .
            "COALESCE(l.n, 0) AS likes, COALESCE(d.n, 0) AS dislikes, " .
            "CASE WHEN mv.vote_type > 0 THEN 'like' WHEN mv.vote_type < 0 THEN 'dislike' ELSE '' END AS my, " .
            "(CASE WHEN " . $isMyUid . " > 0 THEN c.\"authorId\" = " . $isMyUid . " ELSE FALSE END) AS mine " .
            "FROM {$p}comments c " .
            "LEFT JOIN {$p}comment_geo g ON g.coid = c.coid " .
            "LEFT JOIN (SELECT coid, COUNT(*) AS n FROM {$p}comment_votes WHERE vote_type > 0 GROUP BY coid) l ON l.coid = c.coid " .
            "LEFT JOIN (SELECT coid, COUNT(*) AS n FROM {$p}comment_votes WHERE vote_type < 0 GROUP BY coid) d ON d.coid = c.coid " .
            "LEFT JOIN {$p}comment_votes mv ON mv.coid = c.coid AND mv.voter_hash = {$q} " .
            "WHERE c.cid = " . $cid . " AND c.status = 'approved'"
        ));

        foreach ($rows as &$r) {
            $r['deletable'] = $isAdmin || ((int)$r['authorId'] > 0 && (int)$r['authorId'] === $isMyUid);
            $r['my'] = $r['my'] ?: '';
        }
        unset($r);

        $total = count($rows);
        if ($sort === 'latest') usort($rows, fn($a, $b) => $b['created'] <=> $a['created']);
        elseif ($sort === 'oldest') usort($rows, fn($a, $b) => $a['created'] <=> $b['created']);
        else usort($rows, fn($a, $b) =>
            comment_score($b['likes'], $b['dislikes'], $b['created']) <=> comment_score($a['likes'], $a['dislikes'], $a['created']));

        list($tree, $flat) = comment_build_tree($rows);
        $html = comment_render_tree($tree, $flat, $options, $user, $me);
        $hasMore = $total > $page * $perPage;

        echo json_encode([
            'ok' => true,
            'html' => $html,
            'total' => $total,
            'hasMore' => $hasMore,
            'sort' => $sort,
            'page' => $page,
            'login' => $user->hasLogin(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/* ---- 端点：submit ---- */
if (!function_exists('fuwari_comments_submit')) {
    function fuwari_comments_submit($archive)
    {
        $options = fuwari_options();
        $db = \Typecho\Db::get();
        $p = $db->getPrefix();
        $req = fuwari_request();
        $cid = (int)$req->get('cid', 0);
        $user = fuwari_user();

        if (!$archive->allow('comment')) {
            echo json_encode(['ok' => false, 'msg' => __t('comments.closed')], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($options->commentsAntiSpam) {
            $expected = md5($options->secret . '&comments');
            if ($req->get('_') !== $expected) {
                echo json_encode(['ok' => false, 'msg' => __t('comments.err.token')], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        $text = trim((string)$req->get('text', ''));
        if ($text === '') {
            echo json_encode(['ok' => false, 'msg' => __t('comments.err.textRequired')], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (strlen($text) === 0 && mb_strlen($text) > 0) {
            // 全角空格等 trim 后为空的极端情况
            echo json_encode(['ok' => false, 'msg' => 'empty after trim'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (mb_strlen($text) > 3000) {
            echo json_encode(['ok' => false, 'msg' => __t('comments.err.tooLong')], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $parent = (int)$req->get('parent', 0);
        if ($parent > 0) {
            $pRow = $db->fetchRow($db->select('cid')->from('table.comments')->where('coid = ?', $parent)->limit(1));
            if (!$pRow || (int)$pRow['cid'] !== $cid) {
                echo json_encode(['ok' => false, 'msg' => __t('comments.err.parent')], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        $ip = $req->getIp();
        $time = $options->time;

        if (!$user->pass('editor', true) && $options->commentsPostIntervalEnable) {
            $latest = $db->fetchRow($db->select('created')->from('table.comments')
                ->where('cid = ? AND ip = ?', $cid, $ip)->order('created', \Typecho\Db::SORT_DESC)->limit(1));
            if ($latest && $time - $latest['created'] > 0 && $time - $latest['created'] < $options->commentsPostInterval) {
                echo json_encode(['ok' => false, 'msg' => __t('comments.err.frequent')], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        $dup = $db->fetchRow($db->select('coid')->from('table.comments')
            ->where('cid = ? AND ip = ? AND text = ? AND created > ?', $cid, $ip, $text, $time - 60)->limit(1));
        if ($dup) {
            echo json_encode(['ok' => false, 'msg' => __t('comments.err.duplicate')], JSON_UNESCAPED_UNICODE);
            exit;
        }
        error_log('DEBUG dup passed, text=[' . $text . '] hex=' . bin2hex($text));

        if ($user->hasLogin()) {
            $comment = [
                'author' => $user->screenName, 'mail' => $user->mail, 'url' => $user->url,
                'authorId' => $user->uid,
            ];
        } else {
            $author = trim((string)$req->get('author', ''));
            $mail = trim((string)$req->get('mail', ''));
            $url = trim((string)$req->get('url', ''));
            if ($author === '' || $mail === '' || !filter_var($mail, FILTER_VALIDATE_EMAIL) || mb_strlen($author) > 150 || mb_strlen($mail) > 150) {
                echo json_encode(['ok' => false, 'msg' => __t('comments.err.identity')], JSON_UNESCAPED_UNICODE);
                exit;
            }
            if ($url !== '') {
                if (mb_strlen($url) > 255) {
                    echo json_encode(['ok' => false, 'msg' => __t('comments.err.url')], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if (!preg_match('#^[a-z]+://#i', $url)) $url = 'http://' . $url;
            }
            $comment = ['author' => $author, 'mail' => $mail, 'url' => $url, 'authorId' => 0];
            \Typecho\Cookie::set('__typecho_remember_author', $author, 30 * 86400);
            \Typecho\Cookie::set('__typecho_remember_mail', $mail, 30 * 86400);
            \Typecho\Cookie::set('__typecho_remember_url', $url, 30 * 86400);
        }

        $status = ($options->commentsRequireModeration && !$user->pass('editor', true)) ? 'waiting' : 'approved';
        if ($status === 'approved' && !$options->commentsRequireModeration && $options->commentsWhitelist && !$user->hasLogin()) {
            $ok = $db->fetchRow($db->select('coid')->from('table.comments')
                ->where('author = ? AND mail = ? AND status = ?', $comment['author'], $comment['mail'], 'approved')->limit(1));
            if (!$ok) $status = 'waiting';
        }

        $rows = [
            'cid' => $cid, 'created' => $time, 'author' => $comment['author'],
            'authorId' => $comment['authorId'], 'ownerId' => (int)$archive->author->uid,
            'mail' => $comment['mail'], 'url' => $comment['url'],
            'ip' => $ip, 'agent' => $req->getAgent(), 'text' => $text,
            'type' => 'comment', 'status' => $status, 'parent' => $parent,
        ];

        $region = comment_lookup_region($ip);        try {
            $coid = $db->query($db->insert('table.comments')->rows($rows));
        } catch (\Throwable $e) {
            error_log('comment submit insert: ' . $e->getMessage() . ' | ' . json_encode($rows, JSON_UNESCAPED_UNICODE));
            echo json_encode(['ok' => false, 'msg' => __t('comments.err.generic')], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $region = comment_lookup_region($ip);
        try {
            $db->query($db->insert($p . 'comment_geo')->rows(['coid' => $coid, 'region' => $region, 'avatar' => '']));
        } catch (\Throwable $e) {
            error_log('comment_geo insert: ' . $e->getMessage());
        }

        $num = $db->fetchObject($db->select(['COUNT(coid)' => 'num'])->from('table.comments')
            ->where('status = ? AND cid = ?', 'approved', $cid))->num;
        $db->query($db->update('table.contents')->rows(['commentsNum' => $num])->where('cid = ?', $cid));

        echo json_encode(['ok' => true, 'coid' => $coid, 'total' => (int)$num, 'waiting' => $status === 'waiting'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/* ---- 端点：vote ---- */
if (!function_exists('fuwari_comments_vote')) {
    function fuwari_comments_vote($archive)
    {
        $db = \Typecho\Db::get();
        $p = $db->getPrefix();
        $req = fuwari_request();
        $user = fuwari_user();
        $options = fuwari_options();
        $cid = (int)$req->get('cid', 0);
        $coid = (int)$req->get('coid', 0);
        $vote = $req->get('vote', '');
        if (!in_array($vote, ['like', 'dislike'], true)) {
            echo json_encode(['ok' => false, 'msg' => 'bad vote'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $c = $db->fetchRow($db->select('cid')->from('table.comments')->where('coid = ? AND status = ?', $coid, 'approved')->limit(1));
        if (!$c || (int)$c['cid'] !== $cid) {
            echo json_encode(['ok' => false, 'msg' => 'not found'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $me = comment_voter_id($user);
        $type = $vote === 'like' ? 1 : -1;
        $db->query("INSERT INTO {$p}comment_votes (coid, voter_hash, vote_type, created) VALUES (" .
            (int)$coid . ", " . fuwari_quote($db, $me) . ", " . $type . ", " . (int)$options->time . ") " .
            "ON CONFLICT (coid, voter_hash) DO UPDATE SET vote_type = EXCLUDED.vote_type, created = EXCLUDED.created");

        $agg = $db->fetchAll($db->query("SELECT vote_type, COUNT(*) AS n FROM {$p}comment_votes WHERE coid = " . (int)$coid . " GROUP BY vote_type"));
        $likes = $dislikes = 0;
        foreach ($agg as $a) {
            if ((int)$a['vote_type'] > 0) $likes = (int)$a['n'];
            else $dislikes = (int)$a['n'];
        }
        echo json_encode(['ok' => true, 'likes' => $likes, 'dislikes' => $dislikes, 'my' => $vote], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/* ---- 端点：delete ---- */
if (!function_exists('fuwari_comments_delete')) {
    function fuwari_comments_delete($archive)
    {
        $db = \Typecho\Db::get();
        $p = $db->getPrefix();
        $req = fuwari_request();
        $user = fuwari_user();
        $cid = (int)$req->get('cid', 0);
        $coid = (int)$req->get('coid', 0);
        $c = $db->fetchRow($db->select('cid', 'authorId')->from('table.comments')->where('coid = ?', $coid)->limit(1));
        if (!$c || (int)$c['cid'] !== $cid) {
            echo json_encode(['ok' => false, 'msg' => 'not found'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $isOwner = $user->pass('editor', true) || ((int)$c['authorId'] > 0 && (int)$c['authorId'] === (int)$user->uid);
        if (!$isOwner) {
            echo json_encode(['ok' => false, 'msg' => 'forbidden'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $db->query($db->update('table.comments')->rows(['status' => 'spam'])->where('coid = ?', $coid));
        $db->query("DELETE FROM {$p}comment_votes WHERE coid = " . (int)$coid);
        $db->query("DELETE FROM {$p}comment_geo WHERE coid = " . (int)$coid);

        $num = $db->fetchObject($db->select(['COUNT(coid)' => 'num'])->from('table.comments')
            ->where('status = ? AND cid = ?', 'approved', $cid))->num;
        $db->query($db->update('table.contents')->rows(['commentsNum' => $num])->where('cid = ?', $cid));
        echo json_encode(['ok' => true, 'total' => (int)$num], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/* ---- 端点统一入口（functions.php themeInit 调用） ---- */
if (!function_exists('fuwari_comments_dispatch')) {
    function fuwari_comments_dispatch($archive)
    {
        $req = fuwari_request();

        if ($req->get('comma') !== '1') return;

        header('Content-Type: application/json; charset=utf-8');
        if (!comment_ensure_tables(\Typecho\Db::get())) {
            echo json_encode(['ok' => false, 'msg' => 'db error'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $action = (string)$req->get('action', '');
        $cid = (int)$req->get('cid', 0);
        $select = $archive->select()
            ->where('table.contents.type IN (?, ?)', 'post', 'page')
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.cid = ?', $cid);
        $archive->query($select);
        if (!$archive->have()) {
            echo json_encode(['ok' => false, 'msg' => 'not found'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        switch ($action) {
            case 'list':    fuwari_comments_list($archive);   break;
            case 'submit':  fuwari_comments_submit($archive); break;
            case 'vote':    fuwari_comments_vote($archive);   break;
            case 'delete':  fuwari_comments_delete($archive); break;
            default:
                echo json_encode(['ok' => false, 'msg' => 'bad action'], JSON_UNESCAPED_UNICODE);
                exit;
        }
    }
}
/* ---- OAuth 回调：换 token → 取用户 → 登录/注册 → 跳回 ---- */
if (!function_exists('fuwari_comments_oauth')) {
    function fuwari_comments_oauth($archive)
    {
        $options = fuwari_options();
        $req = fuwari_request();
        $clientId = trim((string)($options->githubClientId ?? ''));
        $clientSecret = trim((string)($options->githubClientSecret ?? ''));
        $home = \Typecho\Common::url('/', $options->siteUrl);
        $returnTo = $home;
        if ($clientId === '' || $clientSecret === '') {
            header('Location: ' . $home);
            exit;
        }
        $code = (string)$req->get('code', '');
        $state = (string)$req->get('state', '');
        $parts = explode('|', $state, 2);
        $stateOk = isset($parts[0]) && hash_equals(md5($options->secret . '&' . ($parts[1] ?? '')), $parts[0]);
        if ($stateOk && !empty($parts[1])) $returnTo = $parts[1];
        if (!$stateOk || $code === '' || $req->get('error') !== null) {
            header('Location: ' . $returnTo);
            exit;
        }

        $redirect = fuwari_gh_redirect($options);

        $ctx = stream_context_create(['http' => [
            'method' => 'POST', 'ignore_errors' => true, 'timeout' => 10,
            'header' => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json\r\nUser-Agent: fuwari-ty\r\n",
            'content' => http_build_query([
                'client_id' => $clientId, 'client_secret' => $clientSecret,
                'code' => $code, 'redirect_uri' => $redirect,
            ]),
        ]]);
        $resp = @file_get_contents('https://github.com/login/oauth/access_token', false, $ctx);
        $token = json_decode((string)$resp, true)['access_token'] ?? '';
        if ($token === '') {
            error_log('gh_oauth token failed');
            header('Location: ' . $returnTo);
            exit;
        }

        $ctx = stream_context_create(['http' => [
            'ignore_errors' => true, 'timeout' => 10,
            'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\nUser-Agent: fuwari-ty\r\n",
        ]]);
        $resp = @file_get_contents('https://api.github.com/user', false, $ctx);
        $gh = json_decode((string)$resp, true);
        if (!is_array($gh) || empty($gh['id'])) {
            error_log('gh_oauth user failed');
            header('Location: ' . $returnTo);
            exit;
        }

        $db = \Typecho\Db::get();
        $mail = 'github+' . $gh['id'] . '@noreply.invalid';
        $user = $db->fetchRow($db->select()->from('table.users')->where('mail = ?', $mail)->limit(1));
        if (!$user) {
            $hasher = new \Utils\PasswordHash(8, true);
            $userRow = [
                'name'       => 'github-' . $gh['id'],
                'password'   => $hasher->hashPassword(bin2hex(random_bytes(16))),
                'mail'       => $mail,
                'url'        => $gh['html_url'] ?? '',
                'screenName' => mb_substr($gh['login'] ?? 'GitHub User', 0, 32),
                'created'    => $options->time,
                'activated'  => $options->time,
                'logged'     => $options->time,
                'group'      => 'subscriber',
            ];
            $uid = $db->query($db->insert('table.users')->rows($userRow));
        } else {
            $uid = (int)$user['uid'];
        }
        $authCode = bin2hex(random_bytes(16));
        $db->query($db->update('table.users')->rows(['authCode' => $authCode, 'logged' => $options->time])
            ->where('uid = ?', $uid));
        \Typecho\Cookie::set('__typecho_uid', $uid, 30 * 86400);
        \Typecho\Cookie::set('__typecho_authCode', \Typecho\Common::hash($authCode), 30 * 86400);

        header('Location: ' . $returnTo);
        exit;
    }
}

/* 回调地址：路由注册后固定为 /oauth/github/（可被后台配置覆盖） */
if (!function_exists('fuwari_gh_redirect')) {
    function fuwari_gh_redirect($options)
    {
        $redirect = trim((string)($options->githubRedirectUrl ?? ''));
        if ($redirect !== '') return $redirect;
        return \Typecho\Common::url('/oauth/github/', $options->siteUrl);
    }
}

if (!function_exists('comment_github_button')) {
    function comment_github_button($options, $returnTo)
    {
        $clientId = trim((string)($options->githubClientId ?? ''));
        if ($clientId === '' || trim((string)($options->githubClientSecret ?? '')) === '') return '';
        $icon = '<svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8Z"/></svg>';
        $redirect = fuwari_gh_redirect($options);
        $state = md5($options->secret . '&' . $returnTo);
        $url = 'https://github.com/login/oauth/authorize?client_id=' . rawurlencode($clientId)
            . '&redirect_uri=' . rawurlencode($redirect)
            . '&scope=read:user&state=' . rawurlencode($state . '|' . $returnTo);
        return '<a class="comma-gh" href="' . htmlspecialchars($url) . '">'
            . $icon . '<span>' . __t('comments.githubLogin') . '</span></a>';
    }
}
