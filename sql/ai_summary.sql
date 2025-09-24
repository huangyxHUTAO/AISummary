-- 用于存储AI生成的编辑摘要的表
CREATE TABLE IF NOT EXISTS /*_*/ai_summary (
  -- 主键，自增ID
  ais_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  -- 版本ID，与revision表的rev_id关联
  ais_rev_id INT UNSIGNED NOT NULL,
  -- AI摘要文本
  ais_text MEDIUMBLOB NOT NULL,
  -- 摘要生成时间
  ais_timestamp BINARY(14) NOT NULL DEFAULT '',
  -- 添加生成此摘要的模型或服务信息（应该没啥用）
  ais_model VARBINARY(255) NOT NULL DEFAULT '',
  -- 唯一索引，确保每个版本只有一条摘要
  UNIQUE INDEX (ais_rev_id)
) /*$wgDBTableOptions*/;
