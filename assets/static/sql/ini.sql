/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table datatree.data
DROP TABLE IF EXISTS `data`;
CREATE TABLE IF NOT EXISTS `data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `type` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Dumping data for table datatree.data: ~6 rows (approximately)
/*!40000 ALTER TABLE `data` DISABLE KEYS */;
INSERT INTO `data` (`id`, `name`, `type`) VALUES
	(0, 'Root', 'root'),
	(1, 'First group', 'group'),
	(2, 'First child', 'child'),
	(3, 'Second child', 'child'),
	(4, 'Second group', 'group'),
	(5, 'Third child', 'child'),
	(6, 'Fourth child', 'child');
/*!40000 ALTER TABLE `data` ENABLE KEYS */;


-- Dumping structure for table datatree.data_closure
DROP TABLE IF EXISTS `data_closure`;
CREATE TABLE IF NOT EXISTS `data_closure` (
  `ancestor` int(10) unsigned NOT NULL,
  `descendant` int(10) unsigned NOT NULL,
  `depth` smallint(5) unsigned NOT NULL,
  KEY `k__ancestor` (`ancestor`),
  KEY `k__descendant` (`descendant`),
  CONSTRAINT `fk__data_closure__data__ancestor` FOREIGN KEY (`ancestor`) REFERENCES `data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk__data_closure__data__descendant` FOREIGN KEY (`descendant`) REFERENCES `data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table datatree.data_closure: ~11 rows (approximately)
/*!40000 ALTER TABLE `data_closure` DISABLE KEYS */;
INSERT INTO `data_closure` (`ancestor`, `descendant`, `depth`) VALUES
	(1, 1, 0),
	(2, 2, 0),
	(3, 3, 0),
	(4, 4, 0),
	(5, 5, 0),
	(6, 6, 0),
	(0, 1, 1),
	(0, 4, 1),
	(1, 2, 1),
	(1, 3, 1),
	(4, 5, 1),
	(4, 6, 1),
	(0, 2, 2),
	(0, 3, 2),
	(0, 5, 2),
	(0, 6, 2);
/*!40000 ALTER TABLE `data_closure` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
