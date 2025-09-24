<?php

// RevisionAISummary 钩子实现

require_once __DIR__ . '/AISummaryUtil.php';

class AISummaryHooks {

    /**
     * 优化后的获取修订版本ID方法
     * - 合并重复逻辑，提升代码可读性
     * @param mixed $data 数据对象或数组
     * @return int|string 修订版本ID或'unknown'
     */
    private static function getRevisionId($data) {
        $possibleAttributes = ['data-mw-revid', 'rc_this_oldid', 'rc_rev_id', 'rev_id', 'revId', 'revision_id'];

        foreach ($possibleAttributes as $attribute) {
            if (is_array($data) && array_key_exists($attribute, $data)) {
                return $data[$attribute];
            }
            if (is_object($data)) {
                if (method_exists($data, 'getAttribute') && $value = $data->getAttribute($attribute)) {
                    return $value;
                }
                if (isset($data->$attribute)) {
                    return $data->$attribute;
                }
            }
        }

        if (is_object($data) && method_exists($data, 'getId')) {
            return $data->getId();
        }

        return 'unknown';
    }

    /**
     * 生成AI摘要HTML
     *
     * @param string $aiText AI摘要文本
     * @return string HTML字符串
     */
    private static function generateAISummaryHTML($aiText) {
        return \MediaWiki\Html\Html::rawElement(
            'span',
            [ 'class' => 'mw-ai-summary' ],
            htmlspecialchars((string)$aiText)
        );
    }

    /**
     * @param \Throwable $e 异常对象
     * @param string $function 函数名
     */
    private static function logError(\Throwable $e, $function) {
        $timestamp = date('Y-m-d H:i:s');
        error_log(
            "[RevisionAISummary][$timestamp] Exception in $function: " . $e->getMessage() .
            " in " . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString()
        );
    }

    /**
     * 历史页面行尾 PageHistoryLineEnding
     */
    public static function onPageHistoryLineEnding($historyAction, &$row, &$s, &$classes, &$attribs) {
        try {
            $revid = self::getRevisionId($attribs);
            $aiText = AISummaryUtil::makeAISummaryText($revid);

            // 判断是否存在文本
            if ($aiText) {
                $s .= self::generateAISummaryHTML($aiText);
            }
        } catch (\Throwable $e) {
            self::logError($e, 'onPageHistoryLineEnding');
        }
    }

    /**
     * 最近更改页面（堆叠模式 展开的行） Special:RecentChanges EnhancedChangesListModifyLineData
     */
    public static function onEnhancedChangesListModifyLineData($changesList, &$data, $block, $rc, &$classes, &$attribs) {
        try {
            $revid = self::getRevisionId($rc);
            $aiText = AISummaryUtil::makeAISummaryText($revid);

            // 判断是否存在文本
            if ($aiText) {
                $data['ai_summary'] = self::generateAISummaryHTML($aiText);
            }
        } catch (\Throwable $e) {
            self::logError($e, 'onEnhancedChangesListModifyLineData');
        }
    }

    /**
     * 最近更改页面（堆叠模式 未堆叠行） Special:RecentChanges EnhancedChangesListModifyBlockLineData
     */
    public static function onEnhancedChangesListModifyBlockLineData($changesList, &$data, $rc) {
        try {

            // 未堆叠行的头部 修订ID ，毕竟都未堆叠了，只有会有一个
            $revid = $rc->getAttribute('rc_this_oldid');
            $aiText = AISummaryUtil::makeAISummaryText($revid);

            // 判断是否存在文本
            if ($aiText) {
                $data['ai_summary'] = self::generateAISummaryHTML($aiText);
            }
        } catch (\Throwable $e) {
            self::logError($e, 'onEnhancedChangesListModifyBlockLineData');
        }
    }
    /**
     * 最近更改页面 Special:RecentChanges OldChangesListRecentChangesLine
     */
    public static function onOldChangesListRecentChangesLine($changeslist, &$s, $rc, &$classes, &$attribs) {
        try {
            $revid = self::getRevisionId($rc);
            $aiText = AISummaryUtil::makeAISummaryText($revid);

            // 判断是否存在文本
            if ($aiText) {
                $html = self::generateAISummaryHTML($aiText);
                $s = rtrim($s);
                if (substr($s, -5) === '</li>') {
                    $s = substr($s, 0, -5) . $html . '</li>';
                } else {
                    $s .= $html;
                }
            }
        } catch (\Throwable $e) {
            self::logError($e, 'onOldChangesListRecentChangesLine');
        }
    }

    /**
     * 用户贡献页面 ContributionsLineEnding
     */
    public static function onContributionsLineEnding($contribs, &$s, $row, &$classes, &$attribs) {
        try {
            $revid = self::getRevisionId($row);

            $aiText = AISummaryUtil::makeAISummaryText($revid);

            // 判断是否存在文本
            if ($aiText) {
                $html = self::generateAISummaryHTML($aiText);
                $s = rtrim($s);
                if (substr($s, -5) === '</li>') {
                    $s = substr($s, 0, -5) . $html . '</li>';
                } else {
                    $s .= $html;
                }
            }
        } catch (\Throwable $e) {
            // 记录错误日志
            self::logError($e, 'onContributionsLineEnding');
        }
    }
}