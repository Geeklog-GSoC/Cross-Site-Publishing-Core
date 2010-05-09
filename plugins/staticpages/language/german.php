<?php

###############################################################################
# german.php
#
# This is the German language file for the Geeklog Static Pages plugin
#
# Copyright (C) 2001 Tony Bibbs
# tony AT tonybibbs DOT com
#
# German translation by Dirk Haun <dirk AT haun-online DOT de>
# and Markus Wollschl�ger
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
###############################################################################

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_STATIC = array(
    'newpage' => 'Neue Seite',
    'adminhome' => 'Schaltzentrale',
    'staticpages' => 'Statische Seiten',
    'staticpageeditor' => 'Editor f�r Statische Seiten',
    'writtenby' => 'Autor',
    'date' => 'Letzte �nderung',
    'title' => 'Titel',
    'page_title' => 'Seitentitel',
    'content' => 'Inhalt',
    'hits' => 'Angezeigt',
    'staticpagelist' => 'Liste der Statischen Seiten',
    'url' => 'URL',
    'edit' => '�ndern',
    'lastupdated' => 'Letzte �nderung',
    'pageformat' => 'Seitenformat',
    'leftrightblocks' => 'Bl�cke links &amp; rechts',
    'blankpage' => 'Leere Seite',
    'noblocks' => 'Keine Bl�cke',
    'leftblocks' => 'Bl�cke links',
    'addtomenu' => 'Ins Men� aufnehmen',
    'label' => 'Label',
    'nopages' => 'Es sind keine statischen Seiten vorhanden.',
    'save' => 'Speichern',
    'preview' => 'Vorschau',
    'delete' => 'L�schen',
    'cancel' => 'Abbruch',
    'access_denied' => 'Zugriff verweigert',
    'access_denied_msg' => 'Unerlaubter Zugriff, auf eine der Admin-Seiten f�r Statische Seiten. Hinweis: Alle derartigen Versuche werden protokolliert',
    'all_html_allowed' => 'Alle HTML-Tags sind erlaubt',
    'results' => 'Gefundene Statische Seiten',
    'author' => 'Autor',
    'no_title_or_content' => 'Bitte mindestens die Felder <b>Titel</b> und <b>Inhalt</b> ausf�llen.',
    'no_such_page_anon' => 'Bitte einloggen.',
    'no_page_access_msg' => "Dies k�nnte passiert sein, weil Du nicht eingeloggt bist, oder kein Mitglied bist von  {$_CONF['site_name']}. Bitte <a href=\"{$_CONF['site_url']}/users.php?mode=new\"> Mitglied werden</a> bei {$_CONF['site_name']}, um vollen Zugriff zu erhalten.",
    'php_msg' => 'PHP: ',
    'php_warn' => 'Hinweis: Wenn diese Option aktiviert ist, wird in der Seite enthaltener PHP-Code ausgef�hrt. <em>Bitte mit Bedacht verwenden!</em>',
    'exit_msg' => 'Hinweistext: ',
    'exit_info' => 'Art des Hinweistextes, wenn kein Zugriff auf die Seite erlaubt ist: Aktiviert = "Anmeldung erforderlich", nicht aktiviert = "Zugriff verweigert".',
    'deny_msg' => 'Zugriff auf diese Seite ist nicht m�glich. Die Seite wurde entweder umbenannt oder gel�scht oder Du hast nicht die n�tigen Zugriffsrechte.',
    'stats_headline' => 'Top Ten der Statischen Seiten',
    'stats_page_title' => 'Titel',
    'stats_hits' => 'Angezeigt',
    'stats_no_hits' => 'Es gibt keine Statischen Seiten oder sie wurden von niemandem gelesen.',
    'id' => 'ID',
    'duplicate_id' => 'Diese ID wird bereits f�r eine andere Statische Seite benutzt. Bitte andere ID w�hlen.',
    'instructions' => 'Um eine Statische Seite zu �ndern oder zu l�schen, auf das �ndern-Symbol klicken. Um eine Statische Seite anzusehen, auf deren Titel klicken. Auf Neu anlegen (s.o.) klicken, um einen neue Statische Seite anzulegen. Auf das Kopie-Symbol klicken, um eine Kopie einer vorhandenen Seite zu erhalten.',
    'centerblock' => 'Centerblock: ',
    'centerblock_msg' => 'Wenn angekreuzt wird diese Seite als Block auf der Index-Seite angezeigt.',
    'topic' => 'Kategorie: ',
    'position' => 'Position: ',
    'all_topics' => 'Alle',
    'no_topic' => 'Nur auf der Startseite',
    'position_top' => 'Seitenanfang',
    'position_feat' => 'Nach Hauptartikel',
    'position_bottom' => 'Seitenende',
    'position_entire' => 'Ganze Seite',
    'head_centerblock' => 'Centerblock',
    'centerblock_no' => 'Nein',
    'centerblock_top' => 'oben',
    'centerblock_feat' => 'Hauptartikel',
    'centerblock_bottom' => 'unten',
    'centerblock_entire' => 'Ganze Seite',
    'inblock_msg' => 'Im Block: ',
    'inblock_info' => 'Block-Templates f�r diese Seite verwenden.',
    'title_edit' => 'Seite �ndern',
    'title_copy' => 'Seite kopieren',
    'title_display' => 'Seite anzeigen',
    'select_php_none' => 'PHP nicht ausf�hren',
    'select_php_return' => 'PHP ausf�hren (mit return)',
    'select_php_free' => 'PHP ausf�hren',
    'php_not_activated' => "Das Verwenden von PHP in statischen Seiten ist nicht aktiviert. Hinweise zur Aktivierung finden sich in der <a href=\"{$_CONF['site_url']}/docs/english/staticpages.html#php\">Dokumentation</a>.",
    'printable_format' => 'Druckf�hige Version',
    'copy' => 'Kopieren',
    'limit_results' => 'Ergebnisse einschr�nken',
    'search' => 'Suchen',
    'submit' => 'Absenden',
    'no_new_pages' => 'Keine neuen Seiten',
    'pages' => 'SEITEN',
    'comments' => 'Kommentare',
    'draft' => 'Entwurf',
    'draft_yes' => 'Ja',
    'draft_no' => 'Nein'
);

$PLG_staticpages_MESSAGE15 = 'Der Kommentar wurde gespeichert, muss aber noch von einem Moderator freigegeben werden.';
$PLG_staticpages_MESSAGE19 = 'Seite wurde gespeichert.';
$PLG_staticpages_MESSAGE20 = 'Seite wurde gel�scht';
$PLG_staticpages_MESSAGE21 = 'Diese Seite existiert noch nicht. Um eine Seite zu erstellen bitte das Formular unten ausf�llen. Durch ein Versehen hier? Einfach den Abbrechen-Knopf dr�cken.';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Statische Seiten',
    'title' => 'Konfiguration Statische Seiten'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'PHP erlauben?',
    'sort_by' => 'Centerblocks sortieren nach',
    'sort_menu_by' => 'Men�eintr�ge sortieren nach',
    'sort_list_by' => 'Adminliste sortieren nach',
    'delete_pages' => 'Seiten mit Benutzer l�schen?',
    'in_block' => 'Block-Template verwenden?',
    'show_hits' => 'Anzeigez�hler einblenden?',
    'show_date' => 'Datum anzeigen?',
    'filter_html' => 'HTML filtern?',
    'censor' => 'Inhalt zensieren?',
    'default_permissions' => 'Grundeinstellungen Statische Seiten',
    'aftersave' => 'Nach dem Speichern der Seiten',
    'atom_max_items' => 'Max. Seiten in Webservices Newsfeed',
    'meta_tags' => 'Meta-Tags verwenden',
    'comment_code' => 'Kommentar Grundeinstellung',
    'draft_flag' => 'Als Grundeinstellung auf Entwurf',
    'newstaticpagesinterval' => 'Zeitabstand f�r neue Seiten',
    'hidenewstaticpages' => 'Neue Seiten ausblenden',
    'title_trim_length' => 'Titel abschneiden nach',
    'includecenterblocks' => 'Centerblock-Seiten ber�cksichtigen',
    'includephp' => 'Seiten mit PHP ber�cksichtigen',
    'includesearch' => 'Statische Seiten durchsuchen',
    'includesearchcenterblocks' => 'Centerblock-Seiten ber�cksichtigen',
    'includesearchphp' => 'Seiten mit PHP ber�cksichtigen'
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Statische Seiten Haupteinstellungen',
    'fs_whatsnew' => 'Was-ist-neu-Block',
    'fs_search' => 'Suchergebnisse',
    'fs_permissions' => 'Grundeinstellungen Rechte'
);

// Note: entries 0, 1, 9, 12, 17 are the same as in $LANG_configselects['Core']
$LANG_configselects['staticpages'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => true, 'Nein' => false),
    2 => array('Datum' => 'date', 'Seiten-ID' => 'id', 'Titel' => 'title'),
    3 => array('Datum' => 'date', 'Seiten-ID' => 'id', 'Titel' => 'title', 'Men�punkt' => 'label'),
    4 => array('Datum' => 'date', 'Seiten-ID' => 'id', 'Titel' => 'title', 'Author' => 'author'),
    5 => array('Verbergen' => 'hide', 'Anzeigen - Modifiziertes Datum benutzen' => 'modified', 'Anzeigen - Datum der Erstellung benutzen' => 'created'),
    9 => array('Zur Seite weiterleiten' => 'item', 'Liste anzeigen' => 'list', 'Startseite' => 'home', 'Schaltzentrale' => 'admin'),
    12 => array('Kein Zugang' => 0, 'Nur lesen' => 2, 'Lesen-Schreiben' => 3),
    17 => array('Kommentare eingeschaltet' => 0, 'Kommentare ausgeschaltet' => -1)
);

?>
