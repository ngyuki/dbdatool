
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

ALTER TABLE `t2`
  ;

ALTER TABLE `t2` ADD CONSTRAINT `fk` FOREIGN KEY (`id`) references `t1` (`id`) on update NO ACTION on delete NO ACTION;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
