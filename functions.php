<?php

/* ---- 注册 /archives/ 路由 ---- */
// Typecho 默认没有 /archives/ 路由（archive 路由映射到 /blog/），
// 使用 Helper::addRoute 写入路由表（Typecho 标准 API），持久化到数据库。
try {
    if (!\Typecho\Router::get('archives_list')) {
        \Utils\Helper::addRoute('archives_list', '/archives/', 'Widget_Archive', 'render', 'index');
    }
} catch (\Throwable $e) {
    // ignore
}

/* ---- /archives/ 渲染: 指定模板文件 ---- */
if (!function_exists('themeInit')) {
    function themeInit($archive)
    {
        if ('archives_list' === $archive->parameter->type) {
            $archive->setThemeFile('archives.php');
        }

        /* ---- AJAX 搜索: /?s=keyword&ajax=1 返回 JSON ---- */
        if ($archive->is('search') && $archive->request->get('ajax') === '1') {
            header('Content-Type: application/json; charset=utf-8');
            $results = [];
            while ($archive->next()) {
                $desc = $archive->fields->excerpt ?? '';
                if (!$desc) {
                    $plain = strip_tags($archive->text);
                    $desc = mb_strlen($plain) > 200 ? mb_substr($plain, 0, 200) . '…' : $plain;
                }
                $results[] = [
                    'title'       => $archive->title,
                    'permalink'   => $archive->permalink,
                    'description' => $desc,
                ];
            }
            echo json_encode(['hits' => $results, 'keyword' => $archive->request->get('s', '')], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

/* ---- 国际化: .properties 翻译函数 ---- */
if (!function_exists('__t')) {
    function __t(string $key, ...$args): string {
        static $strings = [];
        static $loaded = false;
        if (!$loaded) {
            $loaded = true;
            $lang = 'default';
            if (class_exists('Typecho\\Widget')) {
                try {
                    $opt = \Typecho\Widget::widget('Widget_Options');
                    if (!empty($opt->lang)) $lang = $opt->lang;
                } catch (\Exception $e) {}
            }
            $file = __DIR__ . '/i18n/' . $lang . '.properties';
            if (!file_exists($file)) {
                $file = __DIR__ . '/i18n/default.properties';
            }
            if (file_exists($file)) {
                $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#') continue;
                    $pos = strpos($line, '=');
                    if ($pos === false) continue;
                    $strings[trim(substr($line, 0, $pos))] = trim(substr($line, $pos + 1));
                }
            }
        }
        $text = $strings[$key] ?? $key;
        return empty($args) ? $text : vsprintf($text, $args);
    }
}

/* ---- 工具函数 ---- */
if (!function_exists('calcWordCount')) {
    function calcWordCount($content) {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        $chineseChars = preg_match_all('/[\x{4e00}-\x{9fff}]/u', $text, $matches);
        $englishText = preg_replace('/[\x{4e00}-\x{9fff}]/u', '', $text);
        $englishWords = count(preg_split('/\s+/', trim($englishText))) - 1;
        if ($englishWords < 0) $englishWords = 0;
        return $chineseChars + $englishWords;
    }
}
if (!function_exists('calcReadingTime')) {
    function calcReadingTime($wordCount, $wpm = 200) {
        return max(1, (int)ceil($wordCount / $wpm));
    }
}
if (!function_exists('getTagsString')) {
    function getTagsString($tags) {
        if (empty($tags) || !is_array($tags)) return '';
        $names = array();
        foreach ($tags as $t) {
            if (is_array($t) && isset($t['name'])) $names[] = $t['name'];
            elseif (is_object($t) && isset($t->name)) $names[] = $t->name;
        }
        return implode(',', $names);
    }
}

/* ---- 后台主题配置面板 ---- */
if (!function_exists('themeConfig')) {
    function themeConfig($form) {
        $Text = 'Typecho\Widget\Helper\Form\Element\Text';
        $Textarea = 'Typecho\Widget\Helper\Form\Element\Textarea';
        $Select = 'Typecho\Widget\Helper\Form\Element\Select';
        $Checkbox = 'Typecho\Widget\Helper\Form\Element\Checkbox';
        $Layout = 'Typecho\Widget\Helper\Layout';

        $form->addItem(new $Layout('h3', ['style' => 'margin:1.5em 0 0.5em;color:var(--primary)'])
            ->html(__t('settings.group.general')));

        $form->addInput(new $Select('lang', [
            ''       => '简体中文',
            'en'     => 'English',
            'zh_TW'  => '繁體中文',
            'es'     => 'Español',
        ], '', __t('settings.siteLanguage'), __t('settings.siteLanguage.desc')));

        $form->addInput(new $Text('faviconSrc', null, 'assets/images/favicon-light-192.png',
            __t('settings.faviconSrc'), __t('settings.faviconSrc.desc')));

        $form->addInput(new $Text('themeColorHue', null, '250',
            __t('settings.themeColorHue'), __t('settings.themeColorHue.desc')));
        $form->addInput(new $Checkbox('themeColorFixed', ['1' => __t('settings.themeColorFixed.label')], [],
            __t('settings.themeColorFixed'), __t('settings.themeColorFixed.desc')));

        $form->addInput(new $Checkbox('bannerEnable', ['1' => __t('settings.bannerEnable.label')], [],
            __t('settings.bannerEnable'), __t('settings.bannerEnable.desc')));
        $form->addInput(new $Text('bannerSrc', null, 'assets/images/demo-banner.png',
            __t('settings.bannerSrc'), __t('settings.bannerSrc.desc')));
        $form->addInput(new $Select('bannerPosition', [
            'center' => __t('settings.bannerPosition.center'),
            'top'    => __t('settings.bannerPosition.top'),
            'bottom' => __t('settings.bannerPosition.bottom'),
        ], 'center', __t('settings.bannerPosition'), __t('settings.bannerPosition.desc')));
        $form->addInput(new $Checkbox('bannerCreditEnable', ['1' => __t('settings.bannerCreditEnable.label')], [],
            __t('settings.bannerCreditEnable'), __t('settings.bannerCreditEnable.desc')));
        $form->addInput(new $Text('bannerCreditText', null, '',
            __t('settings.bannerCreditText'), __t('settings.bannerCreditText.desc')));
        $form->addInput(new $Text('bannerCreditUrl', null, '',
            __t('settings.bannerCreditUrl'), __t('settings.bannerCreditUrl.desc')));
        $form->addInput(new $Checkbox('tocEnable', ['1' => __t('settings.tocEnable.label')], ['1'],
            __t('settings.tocEnable'), __t('settings.tocEnable.desc')));

        $form->addItem(new $Layout('h3', ['style' => 'margin:1.5em 0 0.5em;color:var(--primary)'])
            ->html(__t('settings.group.style')));

        $form->addInput(new $Select('colorScheme', [
            'auto'  => __t('settings.colorScheme.auto'),
            'dark'  => __t('settings.colorScheme.dark'),
            'light' => __t('settings.colorScheme.light'),
        ], 'auto', __t('settings.colorScheme'), __t('settings.colorScheme.desc')));
        $form->addInput(new $Checkbox('enableChangeColorScheme', ['1' => __t('settings.enableChangeColorScheme.label')], ['1'],
            __t('settings.enableChangeColorScheme'), __t('settings.enableChangeColorScheme.desc')));

        $form->addItem(new $Layout('h3', ['style' => 'margin:1.5em 0 0.5em;color:var(--primary)'])
            ->html(__t('settings.group.sidebar')));

        $form->addInput(new $Checkbox('widgetCategories', ['1' => __t('settings.widgetCategories.label')], ['1'],
            __t('settings.widgetCategories'), __t('settings.widgetCategories.desc')));
        $form->addInput(new $Checkbox('widgetTags', ['1' => __t('settings.widgetTags.label')], ['1'],
            __t('settings.widgetTags'), __t('settings.widgetTags.desc')));
        $form->addInput(new $Checkbox('widgetCustomHtml', ['1' => __t('settings.widgetCustomHtml.label')], [],
            __t('settings.widgetCustomHtml'), __t('settings.widgetCustomHtml.desc')));
        $form->addInput(new $Textarea('widgetCustomContent', null, '',
            __t('settings.widgetCustomContent'), __t('settings.widgetCustomContent.desc')));

        $form->addItem(new $Layout('h3', ['style' => 'margin:1.5em 0 0.5em;color:var(--primary)'])
            ->html(__t('settings.group.profile')));

        $form->addInput(new $Text('authorName', null, 'Lorem Ipsum',
            __t('settings.authorName'), __t('settings.authorName.desc')));
        $form->addInput(new $Textarea('authorBio', null, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            __t('settings.authorBio'), __t('settings.authorBio.desc')));
        $form->addInput(new $Text('authorAvatar', null, 'assets/images/demo-avatar.png',
            __t('settings.authorAvatar'), __t('settings.authorAvatar.desc')));
        $form->addInput(new $Text('authorUrl', null, '/',
            __t('settings.authorUrl'), __t('settings.authorUrl.desc')));
        $form->addInput(new $Textarea('socialLinks', null, '',
            __t('settings.socialLinks'), __t('settings.socialLinks.desc')));

        $form->addItem(new $Layout('h3', ['style' => 'margin:1.5em 0 0.5em;color:var(--primary)'])
            ->html(__t('settings.group.post')));

        $form->addInput(new $Checkbox('postLicenseEnable', ['1' => __t('settings.postLicenseEnable.label')], ['1'],
            __t('settings.postLicenseEnable'), __t('settings.postLicenseEnable.desc')));
        $form->addInput(new $Text('postLicenseName', null, 'CC BY-NC-SA 4.0',
            __t('settings.postLicenseName'), __t('settings.postLicenseName.desc')));
        $form->addInput(new $Text('postLicenseUrl', null, 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
            __t('settings.postLicenseUrl'), __t('settings.postLicenseUrl.desc')));
        $form->addInput(new $Select('contentSize', [
            'prose-base' => __t('settings.contentSize.default'),
            'prose-sm'   => __t('settings.contentSize.small'),
            'prose-lg'   => __t('settings.contentSize.large'),
        ], 'prose-base', __t('settings.contentSize'), __t('settings.contentSize.desc')));
        $form->addInput(new $Select('contentTheme', [
            ''       => __t('settings.contentTheme.default'),
            'boring' => __t('settings.contentTheme.boring'),
        ], '', __t('settings.contentTheme'), __t('settings.contentTheme.desc')));

        $form->addItem(new $Layout('h3', ['style' => 'margin:1.5em 0 0.5em;color:var(--primary)'])
            ->html(__t('settings.group.icp')));

        $form->addInput(new $Text('icpText', null, '',
            __t('settings.icpText'), __t('settings.icpText.desc')));
        $form->addInput(new $Text('icpLink', null, '',
            __t('settings.icpLink'), __t('settings.icpLink.desc')));
        $form->addInput(new $Text('gonganText', null, '',
            __t('settings.gonganText'), __t('settings.gonganText.desc')));
        $form->addInput(new $Text('gonganLink', null, '',
            __t('settings.gonganLink'), __t('settings.gonganLink.desc')));
    }
}
