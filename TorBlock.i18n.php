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

/** Arabic (العربية) */
$messages['ar'] = array(
	'torblock-desc'      => 'يسمح بمنع عقد خروج التور من تعديل ويكي',
	'torblock-blocked'   => 'عنوان الأيبي الخاص بك، <tt>$1</tt>، تم التعرف عليه تلقائيا كعقدة خروج تور.
التعديل من خلال التور ممنوع لمنع التخريب.',
	'right-torunblocked' => 'تفادي عمليات المنع التلقائية لعقد خروج التور',
);

/** Czech (Česky) */
$messages['cs'] = array(
	'torblock-desc'      => 'Umožňuje blokovat editace pocházející z výstupních uzlů sítě Tor',
	'torblock-blocked'   => 'Vaše IP adresa (<tt>$1</tt>) byla automaticky rozpoznána jako výstupní uzel sítě Tor.
Editace prostřednictvím sítě Tor je kvůli prevenci zneužití zablokována.',
	'right-torunblocked' => 'Obcházení automatického blokování výstupních uzlů sítě Tor',
);

/** German (Deutsch)
 * @author Raimond Spekking
 */
$messages['de'] = array(
	'torblock-desc'      => 'Ermöglicht die Schreibsperre im Wiki für Tor-Ausgangsknoten',
	'torblock-blocked'   => 'Deine IP-Adresse <tt>$1</tt> wurde automatisch als Tor-Ausgangsknoten identifiziert. Die Seitenbearbeitung in Verbindung mit dem Tor-Netzwerk ist unerwünscht, da die Missbrauchsgefahr sehr hoch ist.',
	'right-torunblocked' => 'Umgehung der automatischen Sperre von Tor-Ausgangsknoten',
);

/** Esperanto (Esperanto) */
$messages['eo'] = array(
	'torblock-desc'      => 'Permesas por elignodoj esti forbaritaj kontraŭ redaktado de vikio',
	'torblock-blocked'   => "Via IP-adreso, <tt>$1</tt> estis aŭtomate identigita kiel elignodo ''tor''.
Redaktado per ''tor'' estas forbarita por preventi misuzo.",
	'right-torunblocked' => "Preterpasi aŭtomatajn blokojn de elignodoj ''tor''.",
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
	'torblock-blocked'   => 'IP-osoitteesi <tt>$1</tt> on tunnistettu Tor-verkon poistumispisteeksi. Muokkaaminen Tor-verkon kautta on estetty väärinkäytösten välttämiseksi.',
	'right-torunblocked' => 'Ohittaa automaattiset Tor-poistumispisteiden estot',
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

/** Indonesian (Bahasa Indonesia) */
$messages['id'] = array(
	'torblock-desc'      => 'Memblokir titik alamat Tor untuk menyunting wiki',
	'torblock-blocked'   => 'Alamat IP Anda, <tt>$1</tt> telah diidentifikasi secara otomatis sebagai sebuah titik alamat Tor.
Penyuntingan melalui Tor diblokir untuk mencegah penyalahgunaan.',
	'right-torunblocked' => 'Mengabaikan pemblokiran otomatis terhadap alamat Tor',
);

/** Ripoarisch (Ripoarisch) */
$messages['ksh'] = array(
	'torblock-desc'      => 'Kann et Ändere am Wiki ongerbenge för Metmaacher, di övver <tt>tor</tt> Ußjäng kumme',
	'torblock-blocked'   => 'Ding IP-Adress (<tt>$1</tt>) eß als_enne <tt>tor</tt> Ußjäng äkannt woode.
Änderunge aam Wiki dom_mer övver <tt>tor</tt> nit zolohße,
esu määt och Keiner Dreßß domet.',
	'right-torunblocked' => 'Et Ändere am Wiki övver <tt>tor</tt> Ußjäng zolohße',
);

/** Marathi (मराठी) */
$messages['mr'] = array(
	'torblock-desc'      => 'टॉर एक्झीट नोड्सना विकि संपादनापासून ब्लॉक करण्याची परवानगी देते',
	'torblock-blocked'   => 'तुमचा आयपी अंकपत्ता, <tt>$1</tt>, आपोआप टॉर एक्झीट नोड म्हणून ओळखला गेलेला आहे.
गैरवापर टाळण्यासाठी टॉर मधून संपादन करण्यावर बंदी घालण्यात आलेली आहे.',
	'right-torunblocked' => 'टॉर एक्झीट नोड्सच्या आपोआप आलेल्या प्रतिबंधांकडे दुर्लक्ष करा',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'torblock-desc'      => 'Maakt bewerken onmogelijk voor tor exitnodes',
	'torblock-blocked'   => 'Uw IP-adres, <tt>$1</tt>, is herkend als tor exitnode. Bewerken via tor is niet toegestaan om misbruik te voorkomen.',
	'right-torunblocked' => 'Automatische blokkades van tor exitnodes omzeilen',
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬) */
$messages['no'] = array(
	'torblock-desc'      => 'Gjør det mulig å blokkere Tors utgangsnoder fra å redigere en wiki',
	'torblock-blocked'   => 'Din IP-adresse, <tt>$1</tt>, har blitt automatisk identifisert som en utgangsnode fra TOR.
Redigering via TOR er blokkert for å forhindre misbruk.',
	'right-torunblocked' => 'Kan redigere fra automatisk blokkerte TOR-noder',
);

/** Occitan (Occitan) */
$messages['oc'] = array(
	'torblock-desc'      => 'Permet als noses de sortida Tor d’èsser blocats en escritura sus un wiki',
	'torblock-blocked'   => "Vòstra adreça ip, <tt>$1</tt>, es estada identificada automaticament coma un nos de sortida Tor.
L’edicion per aqueste mejan es blocada per evitar d'abuses.",
	'right-torunblocked' => 'Passar al travèrs dels blocatges dels noses de sortida Tor.',
);

/** Polish (Polski)
 * @author Beau
 */
$messages['pl'] = array(
	'torblock-desc'      => 'Blokuje możliwość edycji dla użytkowników anonimowych łączących się przez serwery wyjściowe sieci Tor',
	'torblock-blocked'   => 'Twój adres IP <tt>$1</tt> został automatycznie zidentyfikowany jako serwer wyjściowy sieci Tor.
Możliwość edycji z wykorzystaniem tej sieci jest zablokowana w celu zapobiegania nadużyciom.',
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

/** Slovak (Slovenčina) */
$messages['sk'] = array(
	'torblock-desc'      => 'Umožňuje bolovať úpravy pochádzajúce z výstupných uzlov siete TOR.',
	'torblock-blocked'   => 'Vaša IP adresa, <tt>$1</tt>, bola automaticky identifikovaná ako výstupný uzol siete TOR.
Aby sa zabránilo zneužitiu, úpravy zo siete TOR sú blokované.',
	'right-torunblocked' => 'Obísť automatické blokovanie výstupných uzlov siete TOR',
);

/** Swedish (Svenska) */
$messages['sv'] = array(
	'torblock-desc'      => 'Gör det möjligt att blockera Tors utgångsnoder från att redigera en wiki',
	'torblock-blocked'   => 'Din IP-adress, <tt>$1</tt>, har automatiskt identifierats som en utgångsnod från Tor.
Redigering via Tor är blockerad för att förhindra missbruk.',
	'right-torunblocked' => 'Får redigera från automatiskt blockerade Tor-noder',
);

/** Vèneto (Vèneto) */
$messages['vec'] = array(
	'torblock-desc'      => 'Permeti de inpedirghe la modifica de la wiki ai nodi de uscita Tor',
	'torblock-blocked'   => 'El to indirisso IP, <tt>$1</tt>, el xe stà identificà automaticamente come un nodo de uscita Tor.
Le modifiche tramite Tor le xe blocà par evitar abusi.',
	'right-torunblocked' => 'Scavalca i blochi automatici dei nodi de uscita Tor',
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
$messages['zh-hans'] = array(
	'torblock-desc'      => '容许tor出口点封锁在一个wiki中的编辑',
	'torblock-blocked'   => '您的IP地址，<tt>$1</tt>已经被自动认定为一个tor的出口点。
经tor的编辑已经封锁以防止滥用。',
	'right-torunblocked' => '绕过tor出口点的自动封锁',
);

/** Traditional Chinese (‪中文(繁體)‬)
 * @author Shinjiman
 */
$messages['zh-hant'] = array(
	'torblock-desc'      => '封鎖使用tor位址發出的匿名編輯',
	'torblock-blocked'   => '您的IP位址<tt>$1</tt>已被系統自動認定為tor的節點，為防止破壞，經由tor發出的匿名編輯已經被封鎖。',
	'right-torunblocked' => '自動繞過tor的節點',
);

