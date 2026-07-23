<?php
if (!function_exists('debugThemeInit')) {
    function debugThemeInit($archive) {
        error_log('FUWARI DEBUG: themeFile = ' . $archive->themeFile . ', archiveType = ' . $archive->archiveType . ', archiveSlug = ' . $archive->archiveSlug . ', have = ' . ($archive->have() ? 'true' : 'false') . ', isSingle = ' . ($archive->is('single') ? 'true' : 'false'));
    }
}
if (function_exists('themeInit')) {
    // Already defined elsewhere
} else {
    function themeInit($archive) {
        debugThemeInit($archive);
    }
}
if (!function_exists('themeOption')) {
    function themeOption($key, $default = '') {
        $helper = 'Typecho\\Widget\\Helper\\Form\\Element\\Text';
        if (class_exists('Typecho\\Widget\\Helper\\Form\\Element\\Text')) {
            $options = \Typecho\Widget::widget('Widget_Options');
            if (isset($options->{$key}) && $options->{$key} !== '') {
                return $options->{$key};
            }
        }
        return $default;
    }
}
if (!function_exists('themeOptionBool')) {
    function themeOptionBool($key, $default = false) {
        $val = themeOption($key, $default ? '1' : '0');
        return $val === '1' || $val === true || $val === 1;
    }
}
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
