<?php
use MediaWiki\MediaWikiServices;
// AI摘要工具类，专门用于生成AI摘要文本
class AISummaryUtil {
    /**
     * 生成AI摘要文本
     * @param $revid 修订ID
     * @return string 返回AI摘要文本
     */
    public static function makeAISummaryText($revid) {
        // 缓存实例
        $cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
        $cacheKey = $cache->makeKey('aisummary', 'revid', $revid);

        // 从缓存中获取数据
        $cachedText = $cache->get($cacheKey);
        if ($cachedText !== false) {
            return $cachedText;
        }

        // 数据库连接
        $db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(DB_PRIMARY);
        // 查询 ai_summary 表
        $row = $db->selectRow(
            'ai_summary',
            ['ais_text'],
            ['ais_rev_id' => $revid]
        );

        $aiText = '';
        if ($row && isset($row->ais_text)) {
            $aiText = $row->ais_text;
        }

        // 将结果存入缓存，如果空则可能还未生成，暂不写入（我感觉这个时间应该差不多了）
        if ($aiText) {
            $cache->set($cacheKey, $aiText, 3600);
        }

        return $aiText;
    }
}
