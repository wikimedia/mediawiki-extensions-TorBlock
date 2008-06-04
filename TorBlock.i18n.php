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
	'torblock-desc'    => 'Maakt bewerken onmogelijk voor tor exitnodes',
	'torblock-blocked' => 'Uw IP-adres, <tt>$1</tt>, is herkend als tor exitnode. Bewerken via tor is niet toegestaan om misbruik te voorkomen.',
);

