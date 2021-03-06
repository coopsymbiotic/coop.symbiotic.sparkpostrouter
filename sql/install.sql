--
-- Table that stores the webhooks received.
--
CREATE TABLE civicrm_sparkpost_router (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique table ID',
  `type` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT 'delivery, injection, etc.',
  `received_date` datetime DEFAULT NULL COMMENT 'When the webhook event was received',
  `relay_date` datetime DEFAULT NULL COMMENT 'Date when the message was relayed',
  `relay_status` int(2) unsigned NOT NULL DEFAULT 0 COMMENT 'Relay status: 0=pending, 1=delivery, 2=error, 3=ignored',
  `customer_id` int(10) unsigned NOT NULL COMMENT 'SparkPost customer ID',
  `subaccount_id` int(10) unsigned NOT NULL COMMENT 'SparkPost subaccount ID',
  `message_id` varchar(32) DEFAULT NULL COMMENT 'SparkPost message ID',
  `data` text DEFAULT NULL COMMENT 'Sparkpost message',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
