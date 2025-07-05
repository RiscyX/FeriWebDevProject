/* -------------------------------------------------------
   recipe – minimal dump (structures + units & ingredients)
   ------------------------------------------------------- */

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;

/* ---------- PARENT TÁBLÁK ----------------------------- */

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
                              `id`   INT(11)     NOT NULL AUTO_INCREMENT,
                              `name` VARCHAR(100) NOT NULL COMMENT 'Name of the recipe category (e.g., Breakfast, Dessert)',
                              PRIMARY KEY (`id`),
                              UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
                         `id`          INT(11)     NOT NULL AUTO_INCREMENT,
                         `name`        VARCHAR(50) NOT NULL COMMENT 'Unit name',
                         `abbreviation`VARCHAR(10) NOT NULL COMMENT 'Short form',
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `name` (`name`),
                         UNIQUE KEY `abbreviation` (`abbreviation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* alapegységek */
INSERT INTO `units` (`id`,`name`,`abbreviation`) VALUES
                                                     (1,'Kilogramm','kg'),
                                                     (2,'Darab','db'),
                                                     (3,'Gramm','g'),
                                                     (4,'Liter','l'),
                                                     (5,'Milliliter','ml'),
                                                     (6,'Csomag','cs');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
                         `id`              INT(11)      NOT NULL AUTO_INCREMENT,
                         `username`        VARCHAR(100) NOT NULL,
                         `email`           VARCHAR(255) NOT NULL,
                         `password_hash`   VARCHAR(255) NOT NULL,
                         `is_banned`       TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '0=active,1=banned',
                         `role`            TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '0=user,1=admin',
                         `created_at`      DATETIME     DEFAULT CURRENT_TIMESTAMP,
                         `email_verified_at` DATETIME   DEFAULT NULL,
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `username` (`username`),
                         UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* ---------- INGREDIENTS (nagy lista) ------------------ */

DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE `ingredients` (
                               `id`      INT(11)      NOT NULL AUTO_INCREMENT,
                               `name`    VARCHAR(100) NOT NULL COMMENT 'Name of the ingredient',
                               `unit_id` INT(11)      NOT NULL COMMENT 'FK → units.id',
                               PRIMARY KEY (`id`),
                               UNIQUE KEY `name` (`name`),
                               KEY `unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ingredients` (`name`,`unit_id`) VALUES
                                                 ('darált marhahús',3),
                                                 ('darált sertéshús',3),
                                                 ('darált bárányhús',3),
                                                 ('darált csirkehús',3),
                                                 ('darált pulykahús',3),
                                                 ('darált kacsahús',3),
                                                 ('darált libahús',3),
                                                 ('darált borjúhús',4),
                                                 ('darált szarvashús',3),
                                                 ('vaddisznóhús',3),
                                                 ('nyúlhús',3),
                                                 ('lóhús',3),
                                                 ('kecskehús',3),
                                                 ('strucchús',3),
                                                 ('kenguruhús',3),
                                                 ('sonka',3),
                                                 ('szalonna',3),
                                                 ('kolbász',3),
                                                 ('szalámi',3),
                                                 ('cheddar',3),
                                                 ('parmezán',3),
                                                 ('mozzarella',3),
                                                 ('feta',3),
                                                 ('camembert',3),
                                                 ('brie',3),
                                                 ('gouda',3),
                                                 ('rokfort',3),
                                                 ('edami',3),
                                                 ('ementáli',3),
                                                 ('trappista',3),
                                                 ('gruyère',3),
                                                 ('mascarpone',3),
                                                 ('ricotta',3),
                                                 ('pecorino',3),
                                                 ('gorgonzola',3),
                                                 ('halloumi',3),
                                                 ('pálpusztai',3),
                                                 ('maasdam',3),
                                                 ('manchego',3),
                                                 ('kecskesajt',3),
                                                 ('limburger',3),
                                                 ('sárgarépa',3),
                                                 ('burgonya',3),
                                                 ('paradicsom',3),
                                                 ('uborka',4),
                                                 ('paprika',3),
                                                 ('hagyma',3),
                                                 ('fokhagyma',3),
                                                 ('padlizsán',3),
                                                 ('cukkini',3),
                                                 ('brokkoli',3),
                                                 ('karfiol',3),
                                                 ('spenót',3),
                                                 ('káposzta',3),
                                                 ('kelbimbó',3),
                                                 ('avokádó',3),
                                                 ('cékla',3),
                                                 ('retek',3),
                                                 ('édesburgonya',3),
                                                 ('gomba',3),
                                                 ('zöldborsó',4),
                                                 ('spárga',3),
                                                 ('articsóka',3),
                                                 ('karalábé',3),
                                                 ('paszternák',3),
                                                 ('okra',3),
                                                 ('spagetti',3),
                                                 ('makaróni',3),
                                                 ('penne',3),
                                                 ('fusilli',3),
                                                 ('lasagne',3),
                                                 ('ravioli',3),
                                                 ('tortellini',3),
                                                 ('tagliatelle',3),
                                                 ('linguine',3),
                                                 ('farfalle',3),
                                                 ('orzo',3),
                                                 ('udon tészta',3),
                                                 ('soba tészta',3),
                                                 ('ramen tészta',3),
                                                 ('üvegtészta',3),
                                                 ('rizstészta',3),
                                                 ('gnocchi',3),
                                                 ('cannelloni',3),
                                                 ('fettuccine',3),
                                                 ('pappardelle',3),
                                                 ('vörösbor',4),
                                                 ('fehérbor',4),
                                                 ('sör',4),
                                                 ('whisky',4),
                                                 ('konyak',3),
                                                 ('rum',4),
                                                 ('vodka',4),
                                                 ('vermut',4),
                                                 ('sherry',3),
                                                 ('portói bor',4),
                                                 ('Marsala bor',4),
                                                 ('Madeira bor',4),
                                                 ('szaké',3),
                                                 ('tequila',4),
                                                 ('pezsgő',4),
                                                 ('pálinka',4),
                                                 ('narancslikőr',4),
                                                 ('amaretto',3),
                                                 ('alma',3),
                                                 ('banán',3),
                                                 ('narancs',3),
                                                 ('citrom',3),
                                                 ('lime',3),
                                                 ('eper',3),
                                                 ('málna',3),
                                                 ('áfonya',3),
                                                 ('szőlő',3),
                                                 ('őszibarack',3),
                                                 ('sárgabarack',3),
                                                 ('szilva',3),
                                                 ('cseresznye',3),
                                                 ('meggy',3),
                                                 ('körte',3),
                                                 ('mangó',3),
                                                 ('ananász',3),
                                                 ('kókusz',3),
                                                 ('görögdinnye',3),
                                                 ('sárgadinnye',3),
                                                 ('kivi',3),
                                                 ('papaja',3),
                                                 ('gránátalma',3),
                                                 ('licsi',3),
                                                 ('füge',3),
                                                 ('grapefruit',3),
                                                 ('maracuja',3),
                                                 ('lazac',3),
                                                 ('tonhal',3),
                                                 ('tőkehal',3),
                                                 ('szardínia',3),
                                                 ('makréla',3),
                                                 ('pisztráng',3),
                                                 ('ponty',3),
                                                 ('harcsa',3),
                                                 ('süllő',3),
                                                 ('csuka',3),
                                                 ('hering',3),
                                                 ('szardella',3),
                                                 ('tilápia',3),
                                                 ('pangasius',3),
                                                 ('hekk',3),
                                                 ('angolna',3),
                                                 ('busa',3),
                                                 ('keszeg',3),
                                                 ('sügér',3),
                                                 ('kardhal',3),
                                                 ('garnélarák',3),
                                                 ('királyrák',3),
                                                 ('homár',3),
                                                 ('osztriga',3),
                                                 ('kagyló',3),
                                                 ('fésűkagyló',3),
                                                 ('tintahal',3),
                                                 ('polip',3),
                                                 ('tarisznyarák',3),
                                                 ('kaviár',3),
                                                 ('kalmár',3),
                                                 ('tengeri sün',3),
                                                 ('languszta',3),
                                                 ('folyami rák',3),
                                                 ('basmati rizs',3),
                                                 ('jázmin rizs',3),
                                                 ('arborio rizs',4),
                                                 ('carnaroli rizs',3),
                                                 ('vadrizs',3),
                                                 ('barna rizs',3),
                                                 ('fekete rizs',3),
                                                 ('ragacsos rizs',3),
                                                 ('sushi rizs',3),
                                                 ('bomba rizs',3),
                                                 ('vörös rizs',3),
                                                 ('előfőzött rizs',3),
                                                 ('tej',3),
                                                 ('vaj',3),
                                                 ('tejszín',3),
                                                 ('tejföl',3),
                                                 ('joghurt',3),
                                                 ('kefir',3),
                                                 ('író',3),
                                                 ('túró',3),
                                                 ('krémsajt',3),
                                                 ('sűrített tej',3),
                                                 ('ghí',3),
                                                 ('tejpor',3),
                                                 ('tejsavó',3),
                                                 ('búza',3),
                                                 ('árpa',3),
                                                 ('zab',3),
                                                 ('rozs',3),
                                                 ('köles',3),
                                                 ('kukorica',3),
                                                 ('hajdina',3),
                                                 ('quinoa',3),
                                                 ('bulgur',3),
                                                 ('kuszkusz',3),
                                                 ('amaránt',3),
                                                 ('tönkölybúza',3),
                                                 ('cirok',3),
                                                 ('árpagyöngy',3),
                                                 ('búzadara',3),
                                                 ('marha fej',3),
                                                 ('marha pofa',3),
                                                 ('marha nyak (tarja)',3),
                                                 ('marha hasaalja',3),
                                                 ('marha szegy',3),
                                                 ('marha lapocka',3),
                                                 ('marha oldalas',3),
                                                 ('marha láb',3),
                                                 ('marha lábszár',3),
                                                 ('marha farok',3),
                                                 ('marha fartő',3),
                                                 ('marha fehérpecsenye',3),
                                                 ('marha feketepecsenye',3),
                                                 ('marha felsál',3),
                                                 ('marha dió',3),
                                                 ('marha rostélyos',3),
                                                 ('marha hátszín',3),
                                                 ('marha bélszín',3),
                                                 ('marha máj',3),
                                                 ('marha vese',3),
                                                 ('marha szív',3),
                                                 ('marha tüdő',3),
                                                 ('pacal',3),
                                                 ('marha nyelv',3),
                                                 ('marha velőscsont',3),
                                                 ('marha agyvelő',3),
                                                 ('marha lép',4),
                                                 ('bikahere',3),
                                                 ('borjúmirigy (bríz)',4),
                                                 ('sertés fej',3),
                                                 ('sertés orr',3),
                                                 ('sertés fül',3),
                                                 ('sertés pofa',3),
                                                 ('sertés nyelv',3),
                                                 ('sertés nyak',3),
                                                 ('sertés tarja',3),
                                                 ('sertés lapocka',3),
                                                 ('sertés hosszú karaj',3),
                                                 ('sertés rövid karaj',3),
                                                 ('sertés szűzpecsenye',3),
                                                 ('sertés oldalas',3),
                                                 ('sertés dagadó',3),
                                                 ('sertés comb',3),
                                                 ('sertés felsál',3),
                                                 ('sertés dió',3),
                                                 ('sertés frikandó',3),
                                                 ('sertés rózsa',3),
                                                 ('sertés csülök',3),
                                                 ('sertés láb',3),
                                                 ('sertés farok',3),
                                                 ('sertés szalonna',3),
                                                 ('sertés máj',3),
                                                 ('sertés vese',3),
                                                 ('sertés szív',3),
                                                 ('sertés tüdő',3),
                                                 ('sertés agyvelő',3),
                                                 ('sertés vér',3),
                                                 ('sertés belek',3),
                                                 ('sertés gyomor',3),
                                                 ('sertés lép',4),
                                                 ('csirkemell',3),
                                                 ('csirke felsőcomb',3),
                                                 ('csirke alsócomb',3),
                                                 ('csirkeszárny',3),
                                                 ('csirkenyak',3),
                                                 ('csirkefarhát',3),
                                                 ('csirkemáj',3),
                                                 ('csirkeszív',3),
                                                 ('csirkezúza',3),
                                                 ('csirkeláb',3),
                                                 ('kakashere',3),
                                                 ('kakastaréj',3),
                                                 ('kacsamell',3),
                                                 ('kacsacomb',3),
                                                 ('kacsaszárny',3),
                                                 ('kacsanyak',3),
                                                 ('kacsamáj',3),
                                                 ('kacsaszív',3),
                                                 ('kacsazúza',3),
                                                 ('kacsaháj',3),
                                                 ('libamell',3),
                                                 ('libacomb',3),
                                                 ('libaszárny',3),
                                                 ('libanyak',3),
                                                 ('libamáj',3),
                                                 ('libaszív',3),
                                                 ('libazúza',3),
                                                 ('libaháj',3),
                                                 ('pulykamell',3),
                                                 ('pulykacomb',3),
                                                 ('pulykaszárny',3),
                                                 ('pulykanyak',3),
                                                 ('pulykamáj',3),
                                                 ('pulykaszív',3),
                                                 ('pulykazúza',3),
                                                 ('bárány fej',3),
                                                 ('bárány nyak',3),
                                                 ('bárány lapocka',3),
                                                 ('bárány borda',4),
                                                 ('bárány gerinc',3),
                                                 ('bárány comb',3),
                                                 ('bárány lábszár',3),
                                                 ('bárány máj',3),
                                                 ('bárány vese',3),
                                                 ('bárány szív',3),
                                                 ('bárány tüdő',3),
                                                 ('bárány nyelv',3),
                                                 ('bárány agyvelő',3),
                                                 ('bárány here',3),
                                                 ('bárány lép',4),
                                                 ('nyúl lapocka',3),
                                                 ('nyúl comb',3),
                                                 ('nyúl gerinc',3),
                                                 ('nyúl máj',3),
                                                 ('nyúl szív',3),
                                                 ('nyúl vese',3),
                                                 ('lazacikra',3),
                                                 ('pisztrángikra',3),
                                                 ('pontyikra',3),
                                                 ('tokhalikra',3),
                                                 ('Csiperke gomba',3),
                                                 ('Laskagomba',3),
                                                 ('Őzlábgomba',3),
                                                 ('Rókagomba',3),
                                                 ('Ízletes vargánya',3),
                                                 ('Fenyőalja vargánya',3),
                                                 ('Kék tönkű galambgomba',3),
                                                 ('Mezei szegfűgomba',3),
                                                 ('Sárga rókagomba',3),
                                                 ('Szarvasgomba',3),
                                                 ('Kucsmagomba',3),
                                                 ('Gyapjas tintagomba',3),
                                                 ('Shiitake gomba',3),
                                                 ('Portobello gomba',3),
                                                 ('Enoki gomba',3),
                                                 ('Shimeji gomba',3),
                                                 ('Maitake gomba',3),
                                                 ('Trombitagomba',3),
                                                 ('Japán laskagomba',3),
                                                 ('Vajaspöfeteg',3),
                                                 ('Csirketojás',2),
                                                 ('Fürjtojás',2),
                                                 ('Kacsatojás',2),
                                                 ('Libatojás',2),
                                                 ('Pulykatojás',2),
                                                 ('Strucctojás',2),
                                                 ('Emutojás',2),
                                                 ('Gyöngytyúktojás',2),
                                                 ('Fácántojás',2),
                                                 ('Coca-Cola',4),
                                                 ('Pepsi',4),
                                                 ('Fanta',4),
                                                 ('Sprite',4),
                                                 ('Tonic víz',4),
                                                 ('Gyömbérsör',4),
                                                 ('Club Soda',4),
                                                 ('Dr Pepper',4),
                                                 ('7 Up',3),
                                                 ('Mountain Dew',4),
                                                 ('Root Beer',4),
                                                 ('Appletiser',3),
                                                 ('Schweppes Narancs',4),
                                                 ('Schweppes Citrom',4),
                                                 ('Kóla ital',3),
                                                 ('Narancs üdítő',4),
                                                 ('Citrom üdítő',4),
                                                 ('Almás üdítő',4),
                                                 ('Cola Zero',4),
                                                 ('Pepsi Max',4);

/* FK az ingredients → units-re */
ALTER TABLE `ingredients`
    ADD CONSTRAINT `ingredients_ibfk_1`
        FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

/* ---------- GYERMEK-/KAPCSOLÓ TÁBLÁK ------------------ */

DROP TABLE IF EXISTS `recipes`;
CREATE TABLE `recipes` (
                           `id`          INT(11)      NOT NULL AUTO_INCREMENT,
                           `user_id`     INT(11)      NOT NULL COMMENT 'Creator',
                           `title`       VARCHAR(255) NOT NULL,
                           `description` TEXT         DEFAULT NULL,
                           `instructions`TEXT         DEFAULT NULL,
                           `prep_time`   INT(11)      DEFAULT NULL,
                           `cook_time`   INT(11)      DEFAULT NULL,
                           `servings`    INT(11)      DEFAULT NULL,
                           `category_id` INT(11)      DEFAULT NULL,
                           `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
                           PRIMARY KEY (`id`),
                           KEY `user_id` (`user_id`),
                           KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `email_verifications`;
CREATE TABLE `email_verifications` (
                                       `user_id`   INT(11)  NOT NULL,
                                       `token`     VARCHAR(255) NOT NULL,
                                       `created_at`DATETIME DEFAULT CURRENT_TIMESTAMP,
                                       PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
                                   `user_id`   INT(11)  NOT NULL,
                                   `token`     VARCHAR(255) NOT NULL,
                                   `created_at`DATETIME DEFAULT CURRENT_TIMESTAMP,
                                   PRIMARY KEY (`user_id`),
                                   KEY `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
                             `user_id`  INT(11) NOT NULL,
                             `recipe_id`INT(11) NOT NULL,
                             `created_at`DATETIME DEFAULT CURRENT_TIMESTAMP,
                             PRIMARY KEY (`user_id`,`recipe_id`),
                             KEY `recipe_id` (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `fridge_items`;
CREATE TABLE `fridge_items` (
                                `id`           INT(11)      NOT NULL AUTO_INCREMENT,
                                `user_id`      INT(11)      NOT NULL,
                                `ingredient_id`INT(11)      NOT NULL,
                                `quantity`     DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount in user fridge',
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
                         `id`         INT(11) NOT NULL AUTO_INCREMENT,
                         `user_id`    INT(11) NOT NULL,
                         `name`       VARCHAR(10) NOT NULL,
                         `recipe_id`  INT(11) NOT NULL,
                         `day_of_week`ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
                         `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                         PRIMARY KEY (`id`),
                         KEY `user_id` (`user_id`),
                         KEY `recipe_id` (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `recipe_ingredients`;
CREATE TABLE `recipe_ingredients` (
                                      `recipe_id`    INT(11)      NOT NULL,
                                      `ingredient_id`INT(11)      NOT NULL,
                                      `quantity`     DECIMAL(10,2) DEFAULT NULL,
                                      PRIMARY KEY (`recipe_id`,`ingredient_id`),
                                      KEY `ingredient_id` (`ingredient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* ---------- KÜLSŐ KULCSOK ---------------------------- */

ALTER TABLE `email_verifications`
    ADD CONSTRAINT `email_verifications_ibfk_1`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `password_resets`
    ADD CONSTRAINT `fk_password_resets_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `recipes`
    ADD CONSTRAINT `recipes_ibfk_1`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `recipes_ibfk_2`
      FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

ALTER TABLE `favorites`
    ADD CONSTRAINT `favorites_ibfk_1`
        FOREIGN KEY (`user_id`)   REFERENCES `users`   (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2`
      FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

ALTER TABLE `recipe_ingredients`
    ADD CONSTRAINT `recipe_ingredients_ibfk_1`
        FOREIGN KEY (`recipe_id`)    REFERENCES `recipes`    (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredients_ibfk_2`
      FOREIGN KEY (`ingredient_id`)REFERENCES `ingredients`(`id`) ON DELETE CASCADE;

ALTER TABLE `menus`
    ADD CONSTRAINT `menus_ibfk_1`
        FOREIGN KEY (`user_id`)   REFERENCES `users`   (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menus_ibfk_2`
      FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

/* ----------------------------------------------------- */

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
