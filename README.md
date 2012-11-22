PROZER 2.0
=============

project-communication-software
license - still unclear - will be defined soon.


Installation
-------

* Download and unzip redaxo5 / https://github.com/redaxo/redaxo
* Install redaxo5
* Change in redaxo5 in src/core/lib/fragment.php in line 64, einsetzen: 
** if(substr($filename,-3) != "tpl") $filename .= ".tpl";

* Download prozer via redaxo 5 installer.
