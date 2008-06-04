<?php
/**
 * Internationalisation file for extension TorBlock.
 *
 * @addtogroup Extensions
 */

$messages = array();

/** English
 * @author Andrew Garrett
 */
$messages['en'] = array(
	'torblock-desc'    => 'Allows tor exit nodes to be blocked from editing a wiki',
	'torblock-blocked' => 'Your IP address, <tt>$1</tt>, has been automatically identified as a tor exit node.
Editing through tor is blocked to prevent abuse.',
	'right-torunblocked' => 'Bypass automatic blocks of tor exit nodes',
);

/** German (Deutsch)
 * @author Raimond Spekking
 */
$messages['de'] = array(
	'torblock-desc'    => 'Ermöglicht die Schreibsperre im Wiki für Tor-Ausgangsknoten',
	'torblock-blocked' => 'Deine IP-Adresse <tt>$1</tt> wurde automatisch als Tor-Ausgangsknoten identifiziert. Die Seitenbearbeitung in Verbindung mit dem Tor-Netzwerk ist unerwünscht, da die Missbrauchsgefahr sehr hoch ist.'
);

/** Finnish (Suomi)
 * @author Crt
 */
$messages['fi'] = array(
	'torblock-blocked'   => 'IP-osoitteesi <tt>$1</tt> on automaattisesti tunnistettu Tor-verkon exit nodeksi. Muokkaaminen Tor-verkon kautta on estetty väärinkäytösten estämiseksi.',
	'right-torunblocked' => 'Ohittaa automaattiset Tor exit node -estot',
);

/** French (Français)
 * @author Grondin
 */
$messages['fr'] = array(
	'torblock-desc'      => 'Permet aux nœuds de sortie Tor d’être bloqués en écriture sur un wiki',
	'torblock-blocked'   => 'Votre adresse ip, <tt>$1</tt>, a été identifiée automatiquement comme un nœud de sortie Tor.
L’édition par ce moyen est bloquée pour éviter des abus.',
	'right-torunblocked' => 'Passer au travers des blocages des nœuds de sortie Tor.',
);

/** Hebrew (עברית)
 * @author Rotem Liss
 */
$messages['he'] = array(
	'torblock-desc'      => 'אפשרות לחסימת נקודות יציאה של רשת TOR מעריכה בוויקי',
	'torblock-blocked'   => 'כתובת ה־IP שלכם, <tt>$1</tt>, זוהתה אוטומטית כנקודת יציאה של רשת TOR. עריכה דרך TOR חסומה כדי למנוע ניצול לרעה.',
	'right-torunblocked' => 'עקיפת חסימות אוטומטיות של נקודות יציאה ברשת TOR',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'torblock-desc'      => 'Maakt bewerken onmogelijk voor tor exitnodes',
	'torblock-blocked'   => 'Uw IP-adres, <tt>$1</tt>, is herkend als tor exitnode. Bewerken via tor is niet toegestaan om misbruik te voorkomen.',
	'right-torunblocked' => 'Automatische blokkades van tor exitnodes omzeilen',
);

/** Polish (Polski)
 * @author Beau
 */
$messages['pl'] = array(
	'torblock-desc'      => 'Blokuje możliwość edycji dla użytkowników anonimowych łączących się przez serwery wyjściowe sieci Tor',
	'torblock-blocked'   => 'Twój adres IP <tt>$1</tt> został automatycznie zidentyfikowany jako serwer wyjściowy sieci Tor. Możliwość edycji z wykorzystaniem tej sieci jest zablokowana w celu zapobiegania nadużyciom.',
	'right-torunblocked' => 'Obejście automatycznych blokad zakładanych na serwery wyjściowe sieci Tor',
);

/** Russian (Русский)
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'torblock-desc'      => 'Позволяет блокировать выходные узлы сети Tor',
	'torblock-blocked'   => 'Ваш IP-адрес, <tt>$1</tt>, был автоматически определён как выходной узел сети Tor.
Редактирование посредством Tor запрещено во избежание злоупотреблений.',
	'right-torunblocked' => 'обход автоматической блокировки узлов сети Tor',
);

