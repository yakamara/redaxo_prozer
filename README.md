PROZER 3.0 AddOn für REDAXO 4 (www.redaxo.org)
=============

project-communication-software
license - still unclear - will be defined soon.


#### Installation
-------
* Download and install redaxo4 / http://www.redaxo.org
* Log In REDAXO Backend www.domain.de/redaxo
* SetUp Domain in System-AddOn: www.domain.de
* Install and activate PHPMailer-AddOn
* Go To AddON "Installer" and load and install newest version of XForm and Prozer
* Prozer will be now available in the frontend. Login with admin/admin


Last Changes
-------

### Version 3.0 // 09. Mai 2015

#### Neu

* E-Mail-Setup: SMTP um eigenes Login und Passwort ergänzt
* Infobox im Projekt auf der Projektübersichtsseite eingebaut
* Navigation Title Attribute hinzugefügt
* E-Mail Übersicht: Infotexte verlängert auf 150 Zeichen
* Adressen können nun auch nach ID (Neuester/Ältester) Eintrag sortiert werden.
* E-Mail Entwürfe werden nun nach Erstellungsdatum sortiert
* CalDav: Apple Default Alarm gesetzt. Unnötige Benachrichtigungen entfallen

#### Info

* SabreDav Update auf 2.1.1

#### Bugs

* Labels wurde nicht richtig erstellt.
* E-Mails in einem archivierten Projekt, kann nun auch von Projekt-User gelesen werden
* Kalender Add Form bug behoben, es wurden nicht geprüft ob das Projekt "nur" Kalender (Keine Jobs) erlaubt.
* Das mehrfach löschen, von Anhängen, war nicht möglich.
* Anhänge wurden nach dem Löschen, noch mit gesendet, Doppelte Anhaenge die folge.
* Verschieben eines Ganztages Termins ist nun auch möglich. 
* Tagestermin verschieben eingebaur
* XForm Datetime Template hinzugefügt - Visuelle Anpassung
* Autocompleter. Suchstring ist nun sichtbar
* Textergänzungen und Korrekturen
* Kalender: Addslash Problem gelöst. Texte sehen nun nicht mehr so aus: test \"\> ..
* Projektrechte können nun nachträglich verändert werden
* Darstellung des MOnatsnames ist nun richtig (keine doppelte utf8 kodierung mehr)
*


### Version 3.0 beta4 // 28. November 2014

#### Neu

* Adresse. Sozial Profile um google+ und xing ergänzt
* E-Mail Attachementnamen werden nun besser erkannt
* Logfile um Anzeige von Fehlern ergänzt. 
* Logfile - fehlerhafter E-mail Versand wird festgehalten und mit der Fehlermeldung versehen
* Wiki in Projekten ergänzt. MarkDown mit Tasklisten und geschützten Wikiseiten
* E-Mail-Suche: Auch CC wird nun durchsucht.
* Minimale responsive Anpassungen / Vorbereitungen

#### Bugs

* Emails wurden z.T. mit falschem Zeichensatz ausgegeben
* Downloaddateigröße war falsch und führte z.T. zu Problemen beim Download


### Version 3.0 beta3 // 22. August 2014

#### Bugs

* Teilprojekte reagieren im Kalender nun richtig
* Bei der Installation wird nun nach der PHP Version geprüft (>=5.4)


