-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: billiard_app
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,'default','created','App\\Models\\Admin','created',1,NULL,NULL,'{\"attributes\": {\"name\": \"Superadmin\", \"email\": \"jiyuhonpostudio@gmail.com\", \"phone\": null, \"address\": null, \"tickets\": 0, \"admin_id\": \"superadmin\", \"manager_name\": null, \"last_login_at\": null, \"notification_type\": null, \"subscription_until\": null}}',NULL,'2026-02-13 06:45:11','2026-02-13 06:45:11'),(2,'default','created','App\\Models\\Admin','created',2,NULL,NULL,'{\"attributes\": {\"name\": \"テスト店舗\", \"email\": \"w4y0m70@gmail.com\", \"phone\": null, \"address\": null, \"tickets\": 0, \"admin_id\": \"000\", \"manager_name\": null, \"last_login_at\": null, \"notification_type\": null, \"subscription_until\": null}}',NULL,'2026-02-13 06:45:11','2026-02-13 06:45:11'),(3,'default','created','App\\Models\\User','created',1,NULL,NULL,'{\"attributes\": {\"city\": null, \"class\": null, \"email\": \"stade_roland_garros19761@ezweb.ne.jp\", \"phone\": null, \"gender\": null, \"line_id\": null, \"birthday\": null, \"zip_code\": null, \"last_name\": \"テスト\", \"first_name\": \"ユーザー\", \"prefecture\": null, \"account_name\": null, \"address_line\": null, \"last_name_kana\": \"テスト\", \"first_name_kana\": \"ユーザー\", \"notification_type\": null}}',NULL,'2026-02-13 06:45:11','2026-02-13 06:45:11'),(4,'default','updated','App\\Models\\Admin','updated',2,NULL,NULL,'{\"old\": {\"last_login_at\": null}, \"attributes\": {\"last_login_at\": \"2026-02-13T06:46:17.000000Z\"}}',NULL,'2026-02-13 06:46:17','2026-02-13 06:46:17'),(5,'default','created','App\\Models\\Ticket','created',1,'App\\Models\\Admin',2,'{\"attributes\": {\"plan_id\": 1, \"used_at\": null, \"admin_id\": 2, \"event_id\": null, \"expired_at\": \"2026-04-14T14:59:59.000000Z\", \"is_expiry_notified\": 0}}',NULL,'2026-02-13 06:46:28','2026-02-13 06:46:28'),(6,'default','created','App\\Models\\Ticket','created',2,'App\\Models\\Admin',2,'{\"attributes\": {\"plan_id\": 1, \"used_at\": null, \"admin_id\": 2, \"event_id\": null, \"expired_at\": \"2026-04-14T14:59:59.000000Z\", \"is_expiry_notified\": 0}}',NULL,'2026-02-13 06:46:28','2026-02-13 06:46:28'),(7,'default','created','App\\Models\\Ticket','created',3,'App\\Models\\Admin',2,'{\"attributes\": {\"plan_id\": 1, \"used_at\": null, \"admin_id\": 2, \"event_id\": null, \"expired_at\": \"2026-04-14T14:59:59.000000Z\", \"is_expiry_notified\": 0}}',NULL,'2026-02-13 06:46:28','2026-02-13 06:46:28'),(8,'default','created','App\\Models\\Ticket','created',4,'App\\Models\\Admin',2,'{\"attributes\": {\"plan_id\": 1, \"used_at\": null, \"admin_id\": 2, \"event_id\": null, \"expired_at\": \"2026-04-14T14:59:59.000000Z\", \"is_expiry_notified\": 0}}',NULL,'2026-02-13 06:46:28','2026-02-13 06:46:28'),(9,'default','created','App\\Models\\Ticket','created',5,'App\\Models\\Admin',2,'{\"attributes\": {\"plan_id\": 1, \"used_at\": null, \"admin_id\": 2, \"event_id\": null, \"expired_at\": \"2026-04-14T14:59:59.000000Z\", \"is_expiry_notified\": 0}}',NULL,'2026-02-13 06:46:28','2026-02-13 06:46:28'),(10,'default','created','App\\Models\\Event','created',1,'App\\Models\\Admin',2,'{\"attributes\": {\"title\": \"BC\", \"admin_id\": 2, \"ticket_id\": 1, \"event_date\": \"2026-02-22T06:46:00.000000Z\", \"description\": \"【種目】ナインボール（セットマッチ）\\r\\n【試合形式】予選：ダブルイリミネーション／決勝（ベスト８）：シングルイリミネーション\\r\\n【ルール】ランダムラック／勝者ブレイク／スリーポイントルール採用／プッシュアウトあり／ダブルヒットなし\\r\\n【ショットクロック】採用：◯分・時間切れ＞1ショット40秒・エクステンション（1ラック1回40秒）\\r\\n【ハンデ】P=6／A=5／B=4／C=3\\r\\n【参加費】◯円\\r\\n【賞典】◯円分の商品券\\r\\n【注意事項】時間厳守（遅れる場合は事前に店舗までご連絡お願いいたします）\\r\\n【お店より】和気あいあいと楽しく行うトーナメントです。奮ってご参加ください！\\r\\nエントリー入力画面から所属店舗の入力をお願いいたします\", \"published_at\": \"2026-02-12T03:00:00.000000Z\", \"allow_waitlist\": 1, \"entry_deadline\": \"2026-02-21T06:46:00.000000Z\", \"max_participants\": 4, \"instruction_label\": null}}',NULL,'2026-02-13 06:46:57','2026-02-13 06:46:57'),(11,'default','updated','App\\Models\\Ticket','updated',1,'App\\Models\\Admin',2,'{\"old\": {\"used_at\": null, \"event_id\": null}, \"attributes\": {\"used_at\": \"2026-02-13T06:46:57.000000Z\", \"event_id\": 1}}',NULL,'2026-02-13 06:46:57','2026-02-13 06:46:57'),(12,'default','created','App\\Models\\UserEntry','created',1,'App\\Models\\Admin',2,'{\"attributes\": {\"class\": \"B\", \"gender\": \"男性\", \"status\": \"entry\", \"user_id\": null, \"event_id\": 1, \"last_name\": \"山田\", \"first_name\": \"太郎\", \"waitlist_until\": null}}',NULL,'2026-02-13 06:47:27','2026-02-13 06:47:27');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'superadmin','Superadmin',NULL,NULL,'jiyuhonpostudio@gmail.com','$2y$12$cBwn3lMxygymLGYxIbfxaODuNmac01eGCjSPEL/XSdOB7OGNU5t1e',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,'super_admin',NULL,'2026-02-13 06:45:11','2026-02-13 06:45:11',NULL),(2,'000','テスト店舗',NULL,NULL,'w4y0m70@gmail.com','$2y$12$2jx2LCGS/enHsuDj.oQzouqvEczTNqpmG.K3hvJMgbh.sTSVHqTgq','5550023','大阪府','大阪市','',NULL,NULL,0,'2026-02-13 15:46:17',NULL,'admin',NULL,'2026-02-13 06:45:11','2026-02-13 06:46:17',NULL);
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `campaign_code_admin`
--

LOCK TABLES `campaign_code_admin` WRITE;
/*!40000 ALTER TABLE `campaign_code_admin` DISABLE KEYS */;
INSERT INTO `campaign_code_admin` VALUES (1,1,2,'2026-02-13 06:46:28','2026-02-13 06:46:28');
/*!40000 ALTER TABLE `campaign_code_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `campaign_codes`
--

LOCK TABLES `campaign_codes` WRITE;
/*!40000 ALTER TABLE `campaign_codes` DISABLE KEYS */;
INSERT INTO `campaign_codes` VALUES (1,'GIFT5',1,5,100,1,NULL,60,'2026-02-13 06:45:11','2026-02-13 06:46:28');
/*!40000 ALTER TABLE `campaign_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `event_classes`
--

LOCK TABLES `event_classes` WRITE;
/*!40000 ALTER TABLE `event_classes` DISABLE KEYS */;
INSERT INTO `event_classes` VALUES (1,1,'B','2026-02-13 06:46:57','2026-02-13 06:46:57'),(2,1,'C','2026-02-13 06:46:57','2026-02-13 06:46:57');
/*!40000 ALTER TABLE `event_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'BC','【種目】ナインボール（セットマッチ）\r\n【試合形式】予選：ダブルイリミネーション／決勝（ベスト８）：シングルイリミネーション\r\n【ルール】ランダムラック／勝者ブレイク／スリーポイントルール採用／プッシュアウトあり／ダブルヒットなし\r\n【ショットクロック】採用：◯分・時間切れ＞1ショット40秒・エクステンション（1ラック1回40秒）\r\n【ハンデ】P=6／A=5／B=4／C=3\r\n【参加費】◯円\r\n【賞典】◯円分の商品券\r\n【注意事項】時間厳守（遅れる場合は事前に店舗までご連絡お願いいたします）\r\n【お店より】和気あいあいと楽しく行うトーナメントです。奮ってご参加ください！\r\nエントリー入力画面から所属店舗の入力をお願いいたします','2026-02-22 15:46:00','2026-02-21 15:46:00','2026-02-12 12:00:00',4,1,2,1,NULL,'2026-02-13 06:46:57','2026-02-13 06:46:57');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `group_event`
--

LOCK TABLES `group_event` WRITE;
/*!40000 ALTER TABLE `group_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `group_user`
--

LOCK TABLES `group_user` WRITE;
/*!40000 ALTER TABLE `group_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_10_19_000010_create_admins_table',1),(5,'2025_10_25_015917_create_events_table',1),(6,'2025_10_28_150927_create_user_entries_table',1),(7,'2025_11_04_142724_remove_entry_counts_from_events_table',1),(8,'2025_12_10_111530_create_notification_settings_table',1),(9,'2026_01_13_104941_create_event_classes_table',1),(10,'2026_01_15_151551_create_plans_table',1),(11,'2026_01_15_151637_create_tickets_table',1),(12,'2026_01_15_151722_create_campaign_codes_table',1),(13,'2026_02_03_180506_create_campaign_code_admin_table',1),(14,'2026_02_05_110751_create_activity_log_table',1),(15,'2026_02_05_110752_add_event_column_to_activity_log_table',1),(16,'2026_02_05_110753_add_batch_uuid_column_to_activity_log_table',1),(17,'2026_02_07_110334_create_groups_table',1),(18,'2026_02_07_110335_create_group_user_table',1),(19,'2026_02_07_192447_create_group_event_table',1),(20,'2026_02_13_144055_create_site_messages_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `notification_settings`
--

LOCK TABLES `notification_settings` WRITE;
/*!40000 ALTER TABLE `notification_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (1,'pocket','POCKET',20,500,NULL,'2026-02-13 06:45:11','2026-02-13 06:45:11'),(2,'rack','RACK',35,800,NULL,'2026-02-13 06:45:11','2026-02-13 06:45:11');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `site_messages`
--

LOCK TABLES `site_messages` WRITE;
/*!40000 ALTER TABLE `site_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (1,2,1,'2026-02-13 06:46:57',1,'2026-04-14 14:59:59',0,'2026-02-13 06:46:28','2026-02-13 06:46:57'),(2,2,1,NULL,NULL,'2026-04-14 14:59:59',0,'2026-02-13 06:46:28','2026-02-13 06:46:28'),(3,2,1,NULL,NULL,'2026-04-14 14:59:59',0,'2026-02-13 06:46:28','2026-02-13 06:46:28'),(4,2,1,NULL,NULL,'2026-04-14 14:59:59',0,'2026-02-13 06:46:28','2026-02-13 06:46:28'),(5,2,1,NULL,NULL,'2026-04-14 14:59:59',0,'2026-02-13 06:46:28','2026-02-13 06:46:28');
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_entries`
--

LOCK TABLES `user_entries` WRITE;
/*!40000 ALTER TABLE `user_entries` DISABLE KEYS */;
INSERT INTO `user_entries` VALUES (1,NULL,1,'山田','太郎',NULL,NULL,'男性','B',NULL,'entry',NULL,'2026-02-13 06:47:27','2026-02-13 06:47:27');
/*!40000 ALTER TABLE `user_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'テスト','ユーザー','テスト','ユーザー','stade_roland_garros19761@ezweb.ne.jp',NULL,NULL,'$2y$12$RC5gO6.0lfb6BD6cfULw/OTnMWqrLc90FNATtv27xl0S0ahwM2xTm',NULL,'2026-02-13 06:45:11','2026-02-13 06:45:11','player',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-13 16:18:59
