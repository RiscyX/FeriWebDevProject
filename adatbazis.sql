-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 06, 2025 at 09:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `recipe`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Name of the recipe category (e.g., Breakfast, Dessert)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(4, 'Desszert'),
(7, 'Egyéb'),
(1, 'Előétel'),
(3, 'Főétel'),
(2, 'Leves'),
(6, 'Reggeli'),
(5, 'Saláta'),
(11, 'Vegan'),
(10, 'Vegetarianus');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`user_id`, `token`, `created_at`) VALUES
(25, '07ca98449fc350ece3f6053d90c3a8b99d92f49f494dca8c4bd77687c4964f94', '2025-07-05 12:32:59');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fridge_items`
--

CREATE TABLE `fridge_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount of ingredient in user''s fridge'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fridge_items`
--

INSERT INTO `fridge_items` (`id`, `user_id`, `ingredient_id`, `quantity`) VALUES
(35, 22, 731, 26.00),
(36, 22, 499, 300.00),
(37, 22, 608, 500.00),
(38, 22, 435, 300.00),
(39, 24, 653, 600.00),
(40, 24, 551, 400.00),
(42, 24, 435, 200.00),
(43, 24, 432, 200.00);

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Name of the ingredient',
  `unit_id` int(11) NOT NULL COMMENT 'Measurement unit reference'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `name`, `unit_id`) VALUES
(386, 'darált marhahús', 3),
(387, 'darált sertéshús', 3),
(388, 'darált bárányhús', 3),
(389, 'darált csirkehús', 3),
(390, 'darált pulykahús', 3),
(391, 'darált kacsahús', 3),
(392, 'darált libahús', 3),
(393, 'darált borjúhús', 4),
(394, 'darált szarvashús', 3),
(395, 'vaddisznóhús', 3),
(396, 'nyúlhús', 3),
(397, 'lóhús', 3),
(398, 'kecskehús', 3),
(399, 'strucchús', 3),
(400, 'kenguruhús', 3),
(401, 'sonka', 3),
(402, 'szalonna', 3),
(403, 'kolbász', 3),
(404, 'szalámi', 3),
(405, 'cheddar', 3),
(406, 'parmezán', 3),
(407, 'mozzarella', 3),
(408, 'feta', 3),
(409, 'camembert', 3),
(410, 'brie', 3),
(411, 'gouda', 3),
(412, 'rokfort', 3),
(413, 'edami', 3),
(414, 'ementáli', 3),
(415, 'trappista', 3),
(416, 'gruyère', 3),
(417, 'mascarpone', 3),
(418, 'ricotta', 3),
(419, 'pecorino', 3),
(420, 'gorgonzola', 3),
(421, 'halloumi', 3),
(422, 'pálpusztai', 3),
(423, 'maasdam', 3),
(424, 'manchego', 3),
(425, 'kecskesajt', 3),
(426, 'limburger', 3),
(427, 'sárgarépa', 3),
(428, 'burgonya', 3),
(429, 'paradicsom', 3),
(430, 'uborka', 4),
(431, 'paprika', 3),
(432, 'hagyma', 3),
(433, 'fokhagyma', 3),
(434, 'padlizsán', 3),
(435, 'cukkini', 3),
(436, 'brokkoli', 3),
(437, 'karfiol', 3),
(438, 'spenót', 3),
(439, 'káposzta', 3),
(440, 'kelbimbó', 3),
(441, 'avokádó', 3),
(442, 'cékla', 3),
(443, 'retek', 3),
(444, 'édesburgonya', 3),
(445, 'gomba', 3),
(446, 'zöldborsó', 4),
(447, 'spárga', 3),
(448, 'articsóka', 3),
(449, 'karalábé', 3),
(450, 'paszternák', 3),
(451, 'okra', 3),
(452, 'spagetti', 3),
(453, 'makaróni', 3),
(454, 'penne', 3),
(455, 'fusilli', 3),
(456, 'lasagne', 3),
(457, 'ravioli', 3),
(458, 'tortellini', 3),
(459, 'tagliatelle', 3),
(460, 'linguine', 3),
(461, 'farfalle', 3),
(462, 'orzo', 3),
(463, 'udon tészta', 3),
(464, 'soba tészta', 3),
(465, 'ramen tészta', 3),
(466, 'üvegtészta', 3),
(467, 'rizstészta', 3),
(468, 'gnocchi', 3),
(469, 'cannelloni', 3),
(470, 'fettuccine', 3),
(471, 'pappardelle', 3),
(472, 'vörösbor', 4),
(473, 'fehérbor', 4),
(474, 'sör', 4),
(475, 'whisky', 4),
(476, 'konyak', 3),
(477, 'rum', 4),
(478, 'vodka', 4),
(479, 'vermut', 4),
(480, 'sherry', 3),
(481, 'portói bor', 4),
(482, 'Marsala bor', 4),
(483, 'Madeira bor', 4),
(484, 'szaké', 3),
(485, 'tequila', 4),
(486, 'pezsgő', 4),
(487, 'pálinka', 4),
(488, 'narancslikőr', 4),
(489, 'amaretto', 3),
(490, 'alma', 3),
(491, 'banán', 3),
(492, 'narancs', 3),
(493, 'citrom', 3),
(494, 'lime', 3),
(495, 'eper', 3),
(496, 'málna', 3),
(497, 'áfonya', 3),
(498, 'szőlő', 3),
(499, 'őszibarack', 3),
(500, 'sárgabarack', 3),
(501, 'szilva', 3),
(502, 'cseresznye', 3),
(503, 'meggy', 3),
(504, 'körte', 3),
(505, 'mangó', 3),
(506, 'ananász', 3),
(507, 'kókusz', 3),
(508, 'görögdinnye', 3),
(509, 'sárgadinnye', 3),
(510, 'kivi', 3),
(511, 'papaja', 3),
(512, 'gránátalma', 3),
(513, 'licsi', 3),
(514, 'füge', 3),
(515, 'grapefruit', 3),
(516, 'maracuja', 3),
(517, 'lazac', 3),
(518, 'tonhal', 3),
(519, 'tőkehal', 3),
(520, 'szardínia', 3),
(521, 'makréla', 3),
(522, 'pisztráng', 3),
(523, 'ponty', 3),
(524, 'harcsa', 3),
(525, 'süllő', 3),
(526, 'csuka', 3),
(527, 'hering', 3),
(528, 'szardella', 3),
(529, 'tilápia', 3),
(530, 'pangasius', 3),
(531, 'hekk', 3),
(532, 'angolna', 3),
(533, 'busa', 3),
(534, 'keszeg', 3),
(535, 'sügér', 3),
(536, 'kardhal', 3),
(537, 'garnélarák', 3),
(538, 'királyrák', 3),
(539, 'homár', 3),
(540, 'osztriga', 3),
(541, 'kagyló', 3),
(542, 'fésűkagyló', 3),
(543, 'tintahal', 3),
(544, 'polip', 3),
(545, 'tarisznyarák', 3),
(546, 'kaviár', 3),
(547, 'kalmár', 3),
(548, 'tengeri sün', 3),
(549, 'languszta', 3),
(550, 'folyami rák', 3),
(551, 'basmati rizs', 3),
(552, 'jázmin rizs', 3),
(553, 'arborio rizs', 4),
(554, 'carnaroli rizs', 3),
(555, 'vadrizs', 3),
(556, 'barna rizs', 3),
(557, 'fekete rizs', 3),
(558, 'ragacsos rizs', 3),
(559, 'sushi rizs', 3),
(560, 'bomba rizs', 3),
(561, 'vörös rizs', 3),
(562, 'előfőzött rizs', 3),
(563, 'tej', 3),
(564, 'vaj', 3),
(565, 'tejszín', 3),
(566, 'tejföl', 3),
(567, 'joghurt', 3),
(568, 'kefir', 3),
(569, 'író', 3),
(570, 'túró', 3),
(571, 'krémsajt', 3),
(572, 'sűrített tej', 3),
(573, 'ghí', 3),
(574, 'tejpor', 3),
(575, 'tejsavó', 3),
(576, 'búza', 3),
(577, 'árpa', 3),
(578, 'zab', 3),
(579, 'rozs', 3),
(580, 'köles', 3),
(581, 'kukorica', 3),
(582, 'hajdina', 3),
(583, 'quinoa', 3),
(584, 'bulgur', 3),
(585, 'kuszkusz', 3),
(586, 'amaránt', 3),
(587, 'tönkölybúza', 3),
(588, 'cirok', 3),
(589, 'árpagyöngy', 3),
(590, 'búzadara', 3),
(591, 'marha fej', 3),
(592, 'marha pofa', 3),
(593, 'marha nyak (tarja)', 3),
(594, 'marha hasaalja', 3),
(595, 'marha szegy', 3),
(596, 'marha lapocka', 3),
(597, 'marha oldalas', 3),
(598, 'marha láb', 3),
(599, 'marha lábszár', 3),
(600, 'marha farok', 3),
(601, 'marha fartő', 3),
(602, 'marha fehérpecsenye', 3),
(603, 'marha feketepecsenye', 3),
(604, 'marha felsál', 3),
(605, 'marha dió', 3),
(606, 'marha rostélyos', 3),
(607, 'marha hátszín', 3),
(608, 'marha bélszín', 3),
(609, 'marha máj', 3),
(610, 'marha vese', 3),
(611, 'marha szív', 3),
(612, 'marha tüdő', 3),
(613, 'pacal', 3),
(614, 'marha nyelv', 3),
(615, 'marha velőscsont', 3),
(616, 'marha agyvelő', 3),
(617, 'marha lép', 4),
(618, 'bikahere', 3),
(619, 'borjúmirigy (bríz)', 4),
(620, 'sertés fej', 3),
(621, 'sertés orr', 3),
(622, 'sertés fül', 3),
(623, 'sertés pofa', 3),
(624, 'sertés nyelv', 3),
(625, 'sertés nyak', 3),
(626, 'sertés tarja', 3),
(627, 'sertés lapocka', 3),
(628, 'sertés hosszú karaj', 3),
(629, 'sertés rövid karaj', 3),
(630, 'sertés szűzpecsenye', 3),
(631, 'sertés oldalas', 3),
(632, 'sertés dagadó', 3),
(633, 'sertés comb', 3),
(634, 'sertés felsál', 3),
(635, 'sertés dió', 3),
(636, 'sertés frikandó', 3),
(637, 'sertés rózsa', 3),
(638, 'sertés csülök', 3),
(639, 'sertés láb', 3),
(640, 'sertés farok', 3),
(641, 'sertés szalonna', 3),
(642, 'sertés máj', 3),
(643, 'sertés vese', 3),
(644, 'sertés szív', 3),
(645, 'sertés tüdő', 3),
(646, 'sertés agyvelő', 3),
(647, 'sertés vér', 3),
(648, 'sertés belek', 3),
(649, 'sertés gyomor', 3),
(650, 'sertés lép', 4),
(651, 'csirkemell', 3),
(652, 'csirke felsőcomb', 3),
(653, 'csirke alsócomb', 3),
(654, 'csirkeszárny', 3),
(655, 'csirkenyak', 3),
(656, 'csirkefarhát', 3),
(657, 'csirkemáj', 3),
(658, 'csirkeszív', 3),
(659, 'csirkezúza', 3),
(660, 'csirkeláb', 3),
(661, 'kakashere', 3),
(662, 'kakastaréj', 3),
(663, 'kacsamell', 3),
(664, 'kacsacomb', 3),
(665, 'kacsaszárny', 3),
(666, 'kacsanyak', 3),
(667, 'kacsamáj', 3),
(668, 'kacsaszív', 3),
(669, 'kacsazúza', 3),
(670, 'kacsaháj', 3),
(671, 'libamell', 3),
(672, 'libacomb', 3),
(673, 'libaszárny', 3),
(674, 'libanyak', 3),
(675, 'libamáj', 3),
(676, 'libaszív', 3),
(677, 'libazúza', 3),
(678, 'libaháj', 3),
(679, 'pulykamell', 3),
(680, 'pulykacomb', 3),
(681, 'pulykaszárny', 3),
(682, 'pulykanyak', 3),
(683, 'pulykamáj', 3),
(684, 'pulykaszív', 3),
(685, 'pulykazúza', 3),
(686, 'bárány fej', 3),
(687, 'bárány nyak', 3),
(688, 'bárány lapocka', 3),
(689, 'bárány borda', 4),
(690, 'bárány gerinc', 3),
(691, 'bárány comb', 3),
(692, 'bárány lábszár', 3),
(693, 'bárány máj', 3),
(694, 'bárány vese', 3),
(695, 'bárány szív', 3),
(696, 'bárány tüdő', 3),
(697, 'bárány nyelv', 3),
(698, 'bárány agyvelő', 3),
(699, 'bárány here', 3),
(700, 'bárány lép', 4),
(701, 'nyúl lapocka', 3),
(702, 'nyúl comb', 3),
(703, 'nyúl gerinc', 3),
(704, 'nyúl máj', 3),
(705, 'nyúl szív', 3),
(706, 'nyúl vese', 3),
(707, 'lazacikra', 3),
(708, 'pisztrángikra', 3),
(709, 'pontyikra', 3),
(710, 'tokhalikra', 3),
(711, 'Csiperke gomba', 3),
(712, 'Laskagomba', 3),
(713, 'Őzlábgomba', 3),
(714, 'Rókagomba', 3),
(715, 'Ízletes vargánya', 3),
(716, 'Fenyőalja vargánya', 3),
(717, 'Kék tönkű galambgomba', 3),
(718, 'Mezei szegfűgomba', 3),
(719, 'Sárga rókagomba', 3),
(720, 'Szarvasgomba', 3),
(721, 'Kucsmagomba', 3),
(722, 'Gyapjas tintagomba', 3),
(723, 'Shiitake gomba', 3),
(724, 'Portobello gomba', 3),
(725, 'Enoki gomba', 3),
(726, 'Shimeji gomba', 3),
(727, 'Maitake gomba', 3),
(728, 'Trombitagomba', 3),
(729, 'Japán laskagomba', 3),
(730, 'Vajaspöfeteg', 3),
(731, 'Csirketojás', 2),
(732, 'Fürjtojás', 2),
(733, 'Kacsatojás', 2),
(734, 'Libatojás', 2),
(735, 'Pulykatojás', 2),
(736, 'Strucctojás', 2),
(737, 'Emutojás', 2),
(738, 'Gyöngytyúktojás', 2),
(739, 'Fácántojás', 2),
(740, 'Coca-Cola', 4),
(741, 'Pepsi', 4),
(742, 'Fanta', 4),
(743, 'Sprite', 4),
(744, 'Tonic víz', 4),
(745, 'Gyömbérsör', 4),
(746, 'Club Soda', 4),
(747, 'Dr Pepper', 4),
(748, '7 Up', 3),
(749, 'Mountain Dew', 4),
(750, 'Root Beer', 4),
(751, 'Appletiser', 3),
(752, 'Schweppes Narancs', 4),
(753, 'Schweppes Citrom', 4),
(754, 'Kóla ital', 3),
(755, 'Narancs üdítő', 4),
(756, 'Citrom üdítő', 4),
(757, 'Almás üdítő', 4),
(758, 'Cola Zero', 4),
(759, 'Pepsi Max', 4);

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(10) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL COMMENT 'Planned day for the recipe',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `user_id` int(11) NOT NULL COMMENT 'FK to users.id',
  `token` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Creator of the recipe',
  `title` varchar(255) NOT NULL COMMENT 'Recipe title',
  `description` text DEFAULT NULL COMMENT 'Detailed description of the recipe',
  `instructions` text DEFAULT NULL COMMENT 'Cooking instructions',
  `prep_time` int(11) DEFAULT NULL COMMENT 'Preparation time in minutes',
  `cook_time` int(11) DEFAULT NULL COMMENT 'Cooking time in minutes',
  `servings` int(11) DEFAULT NULL COMMENT 'Number of servings',
  `category_id` int(11) DEFAULT NULL COMMENT 'Recipe category',
  `created_at` datetime DEFAULT current_timestamp(),
  `verified_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `user_id`, `title`, `description`, `instructions`, `prep_time`, `cook_time`, `servings`, `category_id`, `created_at`, `verified_at`) VALUES
(1, 22, 'Almas pite', 'ajdkalsjdkalsjaslkdjaskljasldkasjlkd', 'asjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksa\r\n\r\nasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksaasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksaasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksaasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksaasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksa\r\nasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksaasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksaasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksa\r\nasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksaasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksa\r\nasjklasjfklasjfklasjfaklsjflaksjflkajfklasjflkasjlfkjaslkfjasklfjasklfjaslkfjaslkfjlksa', NULL, NULL, NULL, 4, '2025-07-05 15:51:21', '2025-07-05'),
(2, 22, 'Fagyi', 'asask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;l', 'asask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;lsajdlkasjdlksajlksadjsalkjdalskdasdasdasd\r\nasask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;lsajdlkasjdlksajlksadjsalkjdalskdasdasdasd\r\nasask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;lsajdlkasjdlksajlksadjsalkjdalskdasdasdasdasask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;lsajdlkasjdlksajlksadjsalkjdalskdasdasdasdasask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;lsajdlkasjdlksajlksadjsalkjdalskdasdasdasdasask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;lsajdlkasjdlksajlksadjsalkjdalskdasdasdasd\r\nasask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;lsajdlkasjdlksajlksadjsalkjdalskdasdasdasdasask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;lsajdlkasjdlksajlksadjsalkjdalskdasdasdasdasask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;lsajdlkasjdlksajlksadjsalkjdalskdasdasdasdasask;ldkas;ldsak;dlaskdasl;dksa;ldksal;dkas;ldkas;ldkas;ldksal;dkasl;dkas;ldksa;lsajdlkasjdlksajlksadjsalkjdalskdasdasdasd', 15, 180, 12, 4, '2025-07-05 20:19:22', '2025-07-05'),
(3, 22, 'sajasfaslkjfasljklfsaklfs', 'fa;slkas;lfsa;lkfals;kf;asfa;lsfas;', 'asl;a;lsjsal;kja;slkdf;laskf;laskflasfa;slkkasl;kfas;lfkl;sakfsalas;lkfas;lkfaskfl;askfl;sakflaskfl;saksa;lkfl;askf;lsakfasl;kasl;kasl;kaskflas;fl;asasl;k;las;l', 145, 156, 23, 3, '2025-07-05 21:04:10', '2025-07-05'),
(8, 22, 'Sült marha bélszín cukkini-őszibarackos raguval', 'Fűszeres marha bélszín, finom cukkini-őszibarackos raguval, könnyű és gyors vacsora.', '1. A marha bélszínt sózzuk, borsozzuk, és egy kevés olajon minden oldalról lepirítjuk.  2. A cukkinit és az őszibarackot (magját eltávolítva) kockázzuk. 3. A lepirított bélszínt kivesszük a serpenyőből, majd a cukkinit és az őszibarackot a visszamaradt zsírban dinszteljük.  4.  Sózzuk, borsozzuk, kevés pirospaprikával és  ízlés szerint más fűszerekkel ízesítjük. 5. A felvert tojást belekeverjük a zöldséghez, és pár perc alatt készre sütjük. 6. A bélszínt szeletekre vágjuk, és a cukkini-őszibarackos raguval tálaljuk.', 20, 30, 2, 3, '2025-07-06 14:08:21', '2025-07-06'),
(9, 24, 'Cukkini-csirkés rizs', 'Egyszerű, gyorsan elkészíthető egytálétel csirke alsócomb, cukkini, hagyma és basmati rizs felhasználásával.', '1. A csirkecombokat megmossuk, kisebb darabokra vágjuk. Sózzuk, borsozzuk. \n2. A hagymát apróra vágjuk, a cukkinit kockázzuk. \n3. Egy serpenyőben vagy wokban az olajon megpirítjuk a csirkét, majd hozzáadjuk a hagymát és a cukkinit.  Kevés pirospaprikát is adhatunk hozzá. \n4.  A rizst megmossuk, majd hozzáadjuk a serpenyő tartalmához.  Annyi vizet adunk hozzá, amennyi a rizs kétszeres mennyisége, majd fedő alatt, lassú tűzön puhára főzzük.  Főzés közben néha megkeverjük. \n5. Tálalás előtt ízlés szerint sózzuk, borsozzuk.', 15, 35, 4, 3, '2025-07-06 14:37:15', '2025-07-06');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients` (
  `recipe_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(10,2) DEFAULT NULL COMMENT 'Amount of the ingredient used'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipe_ingredients`
--

INSERT INTO `recipe_ingredients` (`recipe_id`, `ingredient_id`, `quantity`) VALUES
(1, 464, 400.00),
(1, 490, 300.00),
(1, 567, 100.00),
(2, 491, 150.00),
(2, 512, 400.00),
(2, 563, 300.00),
(3, 428, 1600.00),
(3, 431, 200.00),
(3, 432, 250.00),
(3, 608, 1500.00),
(8, 435, 300.00),
(8, 499, 200.00),
(8, 608, 500.00),
(8, 731, 2.00),
(9, 432, 150.00),
(9, 435, 300.00),
(9, 551, 250.00),
(9, 653, 600.00);

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Name of the unit (e.g., gram, liter, piece)',
  `abbreviation` varchar(10) NOT NULL COMMENT 'Short form of the unit (e.g., g, l, pcs)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `abbreviation`) VALUES
(1, 'Kilogramm', 'kg'),
(2, 'Darab', 'db'),
(3, 'Gramm', 'g'),
(4, 'Liter', 'l'),
(5, 'Milliliter', 'ml'),
(6, 'Csomag', 'cs');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=active, 1=banned',
  `role` int(1) NOT NULL DEFAULT 0 COMMENT '0=user, 1=admin',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'User registration date',
  `email_verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `is_banned`, `role`, `created_at`, `email_verified_at`) VALUES
(22, 'vassrichard31@gmail.com', 'vassrichard31@gmail.com', '$2y$10$/PJv4iYAScWRgWXHlPLyme/FhYxki1QyNFSFPB7CJdezlEPEduFA2', 0, 1, '2025-06-30 01:11:21', '2025-06-30 01:13:12'),
(24, 'Ricsi', 'ricsyxchannel@gmail.com', '$2y$10$G0bdwHrrdVGsU4hZ9iPwr.kRmDEeZ.DBs.LyJ/WJya7BK.hT0yVG.', 0, 0, '2025-07-03 01:47:53', '2025-07-03 01:48:09'),
(25, 'Teszt', 'clashroyalehungarian@gmail.com', '$2y$10$zWwBobj5fcnbbSmjm0MZlepNuAQ0gIGX6BWgWuIWxlj2aBtPiMf6K', 0, 0, '2025-07-05 12:32:59', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `fridge_items`
--
ALTER TABLE `fridge_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD PRIMARY KEY (`recipe_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `abbreviation` (`abbreviation`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `fridge_items`
--
ALTER TABLE `fridge_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=760;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menus_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;