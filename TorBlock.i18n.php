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
	'torblock-desc'      => 'Ermöglicht die Schreibsperre im Wiki für Tor-Ausgangsknoten',
	'torblock-blocked'   => 'Deine IP-Adresse <tt>$1</tt> wurde automatisch als Tor-Ausgangsknoten identifiziert. Die Seitenbearbeitung in Verbindung mit dem Tor-Netzwerk ist unerwünscht, da die Missbrauchsgefahr sehr hoch ist.',
	'right-torunblocked' => 'Umgehung der automatischen Sperre von Tor-Ausgangsknoten',
);

/** Persian (فارسی)
 * @author Huji
 */
$messages['fa'] = array(
	'torblock-desc'      => 'قطع دسترسی خروجی‌های TOR از ویرایش در یک ویکی را ممکن می‌کند',
	'torblock-blocked'   => 'نشانی اینترنتی شما، <tt>$1</tt>، به طور خودکار به عنوان یک خروجی TOR شناسایی شده‌است. ویرایش از طریق این نشانی برای جلوگیری از سوء استفاده ممکن نیست.',
	'right-torunblocked' => 'گذر از قطع دسترسی خودکار خروجی‌های TOR',
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

/** Cantonese
 * @author Shinjiman
 */
$messages['yue'] = array(
	'torblock-desc'      => '容許tor出口點封鎖響一個wiki嘅編輯',
	'torblock-blocked'   => '你嘅IP地址，<tt>$1</tt>已經被自動認定為一個tor嘅出口點。
經tor嘅編輯已經封鎖以防止濫用。',
	'right-torunblocked' => '繞過tor出口點嘅自動封鎖',
);

/** Simplified Chinese
 * @author Shinjiman
 */
$messages['zh-hant'] = array(
	'torblock-desc'      => '容许tor出口点封锁在一个wiki中的编辑',
	'torblock-blocked'   => '您的IP地址，<tt>$1</tt>已经被自动认定为一个tor的出口点。
经tor的编辑已经封锁以防止滥用。',
	'right-torunblocked' => '绕过tor出口点的自动封锁',
);

/** Traditional Chinese
 * @author Shinjiman
 */
$messages['zh-hant'] = array(
	'torblock-desc'      => '容許tor出口點封鎖在一個wiki中的編輯',
	'torblock-blocked'   => '您的IP地址，<tt>$1</tt>已經被自動認定為一個tor的出口點。
經tor的編輯已經封鎖以防止濫用。',
	'right-torunblocked' => '繞過tor出口點的自動封鎖',
);
