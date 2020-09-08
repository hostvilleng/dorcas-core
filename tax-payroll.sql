-- MySQL dump 10.13  Distrib 8.0.18, for macos10.14 (x86_64)
--
-- Host: 127.0.0.1    Database: dorcas_base
-- ------------------------------------------------------
-- Server version	8.0.19

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `employee_payroll_paygroup`
--

LOCK TABLES `employee_payroll_paygroup` WRITE;
/*!40000 ALTER TABLE `employee_payroll_paygroup` DISABLE KEYS */;
INSERT INTO `employee_payroll_paygroup` VALUES (38,13,80,'2020-03-09 21:21:03','2020-03-09 21:21:03'),(39,13,77,'2020-03-09 21:21:03','2020-03-09 21:21:03'),(40,13,78,'2020-03-13 12:36:29','2020-03-13 12:36:29');
/*!40000 ALTER TABLE `employee_payroll_paygroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_allowances`
--

LOCK TABLES `payroll_allowances` WRITE;
/*!40000 ALTER TABLE `payroll_allowances` DISABLE KEYS */;
INSERT INTO `payroll_allowances` VALUES (66,1309,11,'ffb5be2e-624b-11ea-bdb2-dca904910a0c','Fixed Allowance','percent_of_base',1,'2020-03-09 21:21:54','2020-03-13 13:53:38','deduction','{\"base_ratio\": \"10\", \"employer_base_ratio\": \"5\"}');
/*!40000 ALTER TABLE `payroll_allowances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_authorities`
--

LOCK TABLES `payroll_authorities` WRITE;
/*!40000 ALTER TABLE `payroll_authorities` DISABLE KEYS */;
INSERT INTO `payroll_authorities` VALUES (11,1309,'4d2f2ac8-63c3-11ea-8993-dca904910a0c','FIRS','flutterwave','{\"bank\": \"Gtbank\", \"account\": \"09866576545\"}','[]',1,'2020-03-11 18:08:25','2020-03-11 18:08:25');
/*!40000 ALTER TABLE `payroll_authorities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_paygroup`
--

LOCK TABLES `payroll_paygroup` WRITE;
/*!40000 ALTER TABLE `payroll_paygroup` DISABLE KEYS */;
INSERT INTO `payroll_paygroup` VALUES (13,1309,'d3bdeb0c-624b-11ea-8e05-dca904910a0c','Engineering',1,'2020-03-09 21:20:40','2020-03-09 21:20:40');
/*!40000 ALTER TABLE `payroll_paygroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_run_authorities`
--

LOCK TABLES `payroll_run_authorities` WRITE;
/*!40000 ALTER TABLE `payroll_run_authorities` DISABLE KEYS */;
INSERT INTO `payroll_run_authorities` VALUES (10,11,36,'20000','2020-03-13 15:28:59','2020-03-13 15:28:59',66,77),(11,11,36,'25000','2020-03-13 15:28:59','2020-03-13 15:28:59',66,78),(12,11,36,'30000','2020-03-13 15:28:59','2020-03-13 15:28:59',66,80);
/*!40000 ALTER TABLE `payroll_run_authorities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_run_employees`
--

LOCK TABLES `payroll_run_employees` WRITE;
/*!40000 ALTER TABLE `payroll_run_employees` DISABLE KEYS */;
INSERT INTO `payroll_run_employees` VALUES (331,77,36,'180000','2020-03-13 15:28:59','2020-03-13 15:28:59','[13]','{\"Allowances\": {\"deduction\": {\"Fixed Allowance\": 20000}}, \"base_salary\": 200000}'),(332,78,36,'220000','2020-03-13 15:28:59','2020-03-13 15:28:59','[13]','{\"Allowances\": {\"benefits\": {\"Fixed Addition\": 1000, \"Company Anniversary\": 2000}, \"deduction\": {\"Fixed Allowance\": 25000}}, \"base_salary\": 250000, \"Transactions\": {\"additions\": {\"Earliest Coming\": \"5000\"}, \"deductions\": {\"Late Coming\": \"5000\"}}}'),(333,79,36,'1000000','2020-03-13 15:28:59','2020-03-13 15:28:59','[]','{\"base_salary\": 1000000}'),(334,80,36,'270000','2020-03-13 15:28:59','2020-03-13 15:28:59','[13]','{\"Allowances\": {\"deduction\": {\"Fixed Allowance\": 30000}}, \"base_salary\": 300000}');
/*!40000 ALTER TABLE `payroll_run_employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_run_histories`
--

LOCK TABLES `payroll_run_histories` WRITE;
/*!40000 ALTER TABLE `payroll_run_histories` DISABLE KEYS */;
INSERT INTO `payroll_run_histories` VALUES (39,80,34,'draft',NULL,'2020-03-11 06:37:48','2020-03-11 06:38:13'),(40,77,34,'draft',NULL,'2020-03-11 06:37:48','2020-03-11 06:38:13'),(46,77,36,'processed',NULL,'2020-03-11 16:22:09','2020-03-13 15:24:27'),(49,80,36,'processed',NULL,'2020-03-13 12:13:44','2020-03-13 15:24:27'),(50,78,36,'processed',NULL,'2020-03-13 12:13:44','2020-03-13 15:24:27'),(51,79,36,'processed',NULL,'2020-03-13 12:13:44','2020-03-13 15:24:27'),(52,77,37,'draft',NULL,'2020-03-13 15:41:35','2020-03-13 15:41:35'),(53,79,37,'draft',NULL,'2020-03-13 15:41:35','2020-03-13 15:41:35');
/*!40000 ALTER TABLE `payroll_run_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_runs`
--

LOCK TABLES `payroll_runs` WRITE;
/*!40000 ALTER TABLE `payroll_runs` DISABLE KEYS */;
INSERT INTO `payroll_runs` VALUES (34,'d30ad2e8-6362-11ea-88cc-dca904910a0c','Second Run','processed','March Run','2020-03-11 06:37:48','2020-03-11 06:38:13'),(36,'74e94c2e-63b4-11ea-a891-dca904910a0c','Second Run','processed','Second Run','2020-03-11 16:22:09','2020-03-13 15:28:59'),(37,'1eff4988-6541-11ea-bb28-dca904910a0c','Fifth Run','draft','May Run','2020-03-13 15:41:35','2020-03-13 15:41:35');
/*!40000 ALTER TABLE `payroll_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_transactions`
--

LOCK TABLES `payroll_transactions` WRITE;
/*!40000 ALTER TABLE `payroll_transactions` DISABLE KEYS */;
INSERT INTO `payroll_transactions` VALUES (22,'38243b44-63bb-11ea-adc3-dca904910a0c',1309,78,NULL,'5000',1,'one_time','deduction','Late Coming',NULL,'2020-03-11 17:10:34','2020-03-13 12:33:19');
/*!40000 ALTER TABLE `payroll_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tax_authorities`
--

LOCK TABLES `tax_authorities` WRITE;
/*!40000 ALTER TABLE `tax_authorities` DISABLE KEYS */;
INSERT INTO `tax_authorities` VALUES (6,'3eaf2a46-49d0-11ea-b2bf-dca904910a0c',1308,'EIC','Paystack',1,'\"[{\'bank\':\'Gtb\'}]\"','2020-02-07 17:35:34','2020-02-07 17:35:34',''),(7,'00e8a5f2-624e-11ea-840d-dca904910a0c',1309,'SaasA','Flutterwave',1,'[{\"bank\":\"UBA\",\"account\":\"09876543\"}]','2020-03-09 21:36:15','2020-03-11 18:09:03','{\"bank\":\"Gtbank\",\"account\":\"098765456\"}');
/*!40000 ALTER TABLE `tax_authorities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tax_elements`
--

LOCK TABLES `tax_elements` WRITE;
/*!40000 ALTER TABLE `tax_elements` DISABLE KEYS */;
INSERT INTO `tax_elements` VALUES (23,7,'21afa802-63c6-11ea-a1ed-dca904910a0c','NBIEC','percentage',1,'yearly','{\"value\": \"10\", \"element_type\": \"Percentage\"}','2020-03-11 18:28:40','2020-03-11 18:28:40','[\"04361126-63c6-11ea-bb72-dca904910a0c\",\"04381dea-63c6-11ea-afbd-dca904910a0c\",\"043651e0-63c6-11ea-a9ee-dca904910a0c\"]','2020-03-28 00:00:00',NULL),(24,7,'5fad6992-6712-11ea-ad16-dca904910a0c','Test','percentage',1,'monthly','{\"value\": \"20\", \"element_type\": \"Percentage\"}','2020-03-15 23:12:00','2020-03-16 00:01:32','[\"043651e0-63c6-11ea-a9ee-dca904910a0c\",\"04368b9c-63c6-11ea-8d71-dca904910a0c\",\"04361126-63c6-11ea-bb72-dca904910a0c\",\"042e6278-63c6-11ea-b41e-dca904910a0c\"]',NULL,17);
/*!40000 ALTER TABLE `tax_elements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tax_run_authorities`
--

LOCK TABLES `tax_run_authorities` WRITE;
/*!40000 ALTER TABLE `tax_run_authorities` DISABLE KEYS */;
INSERT INTO `tax_run_authorities` VALUES (47,22,7,'0','tax run completed','2020-03-17 17:10:42','2020-03-17 17:10:42');
/*!40000 ALTER TABLE `tax_run_authorities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tax_runs`
--

LOCK TABLES `tax_runs` WRITE;
/*!40000 ALTER TABLE `tax_runs` DISABLE KEYS */;
INSERT INTO `tax_runs` VALUES (22,24,'369ce90a-63c6-11ea-8e58-dca904910a0c','February Tax Run',1,'2020-03-11 18:29:15','2020-03-11 18:29:15','processed');
/*!40000 ALTER TABLE `tax_runs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-03-17 23:02:21
