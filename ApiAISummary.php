<?php

use MediaWiki\MediaWikiServices;

class ApiAISummary extends ApiBase {
    public function execute() {
        try {
            $params = $this->extractRequestParams();
            $token = $params['token'] ?? '';
            global $wgAISummaryToken;
            
            if ($token !== $wgAISummaryToken) {
                $this->dieWithError('Invalid token', 'badtoken');
            }
            
            $revid = intval($params['revid'] ?? 0);
            $content = trim($params['content'] ?? '');
            $timestamp = trim($params['timestamp'] ?? '');
            $model = trim($params['model'] ?? '');

            if (!$revid || $content === '' || $timestamp === '') {
                $this->dieWithError('Missing required fields: revid, content, or timestamp', 'missingfields');
            }

            // 验证timestamp格式 (YYYYMMDDHHMMSS)
            if (!preg_match('/^\d{14}$/', $timestamp)) {
                $this->dieWithError('Invalid timestamp format. Use YYYYMMDDHHMMSS', 'invalidtimestamp');
            }

            $dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(DB_PRIMARY);
            $dbw->upsert(
                'ai_summary',
                [
                    'ais_rev_id' => $revid,
                    'ais_text' => $content,
                    'ais_timestamp' => $timestamp,
                    'ais_model' => $model
                ],
                [ 'ais_rev_id' ],
                [ 
                    'ais_text' => $content, 
                    'ais_timestamp' => $timestamp, 
                    'ais_model' => $model 
                ]
            );

            $affectedRows = $dbw->affectedRows();
            $actionDescription = $affectedRows > 0 ? 'updated' : 'inserted';

            $this->getResult()->addValue(null, $this->getModuleName(), [
                'result' => 'success',
                'message' => "Record with revid $revid has been $actionDescription.",
                'revid' => $revid,
                'affected_rows' => $affectedRows
            ]);
        } catch (\Throwable $e) {
            error_log('[AISummaryAPI] Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
            $this->dieWithError('Internal error: ' . $e->getMessage(), 'internalerror');
        }
    }

    public function getAllowedParams() {
        return [
            'token' => [ ApiBase::PARAM_TYPE => 'string', ApiBase::PARAM_REQUIRED => true ],
            'revid' => [ ApiBase::PARAM_TYPE => 'integer', ApiBase::PARAM_REQUIRED => true ],
            'content' => [ ApiBase::PARAM_TYPE => 'string', ApiBase::PARAM_REQUIRED => true ],
            'timestamp' => [ ApiBase::PARAM_TYPE => 'string', ApiBase::PARAM_REQUIRED => true ],
            'model' => [ ApiBase::PARAM_TYPE => 'string', ApiBase::PARAM_REQUIRED => false ],
        ];
    }
}