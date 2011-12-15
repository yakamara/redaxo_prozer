<?php

/*
  Drop Down 
    - Bspl.: Projektauswahl
    
  class_ul ............. = CSS Klasse fuer ul
  class_selected ....... = CSS Klasse fuer Auswahl
  selected ............. = Auswahl
  entries .............. = enthaelt die Auswahlmoeglichkeiten
  entries[][url] ....... = Url fuer <a>, wenn leer javascript:void(0)
  entries[][attributes]  = Attribute fuer <a>, Bspl. "rel" zum zwischenspeichern von Values, "onclick" ... 
  entries[][class] ..... = CSS Klasse fuer <a>
  entries[][name] ...... = Name -> schwarz
  entries[][info] ...... = Info -> wird grau (Datum)
  entries[][title] ..... = Titel -> wird blau
  entries[][checkbox] .. = Checkbox -> <a> wir ein <label>
  extra ................ = Formularelemente wie hidden, um Auswahl zu speichern oder JS einzufuegen oder ...
*/

?>


      <ul class="sl1<?php echo ($this->class_ul != '') ? ' '.$this->class_ul : ''; ?>">
        <li class="selected<?php echo ($this->class_selected != '') ? ' '.$this->class_selected : ''; ?>"><span class="selected"><?php echo $this->selected; ?></span>
        
        <?php
          // Flayout nur anzeigen, wenn Entries vorhanden (XForm disabled wird nichts uebergeben)
          if (count($this->entries) > 0)
          {
        ?>
          <div class="flyout">
            <div class="content">
              <ul class="entries">
              <?php
                $i = 0;
                $c = count($this->entries);
                foreach ($this->entries as $entry)
                {
                  $i++;
                  $li_class = 'entry';
                  $li_class .= ($i == 1) ? ' first' : '';
                  $li_class .= ($i == $c) ? ' last' : '';
                  
                  $li_class = ($li_class != '') ? ' class="'.trim($li_class).'"' : '';
                  
                  // url uebergabe in attributes speichern
                  if (isset($entry['url']) && $entry['url'] != '')
                  {
                    $entry['attributes']['href'] = $entry['url'];
                  }
                  else
                  {
                    $entry['attributes']['href'] = 'javascript:void(0)';
                  }
                  
                  // class uebergabe in attributes speichern
                  if (isset($entry['class']) && $entry['class'] != '')
                  {
                    if (isset($entry['attributes']['class']))
                      $entry['class'] = $entry['attributes']['class'].' '.$entry['class'];
                       
                    $entry['attributes']['class'] = $entry['class'];
                  }
                  
                  
                  $tag = 'a';
                  $entry_checkbox = '';
                  if (isset($entry['checkbox']) && $entry['checkbox'] != '')
                  {
                    $tag = 'label';
                    $entry_checkbox = '<span class="xform-checkbox">'.$entry['checkbox'].'</span>';
                    
                    if (isset($entry['attributes']['href']))
                      unset($entry['attributes']['href']);
                  }
                  
                  
                                    
                  $entry_attributes = '';
                  if (isset($entry['attributes']) && count($entry['attributes']) > 0)
                  {
                    foreach ($entry['attributes'] as $attr => $value)
                      $entry_attributes .= ' '.$attr.'="'.$value.'"';
                  }
                  
                  $entry_name = (isset($entry['name']) && $entry['name'] != '') ? '<span class="name">'.$entry['name'].'</span>' : '';
                  $entry_info = (isset($entry['info']) && $entry['info'] != '') ? '<span class="info">'.$entry['info'].'</span>' : '';
                  $entry_title = (isset($entry['title']) && $entry['title'] != '') ? '<span class="title">'.$entry['title'].'</span>' : '';
                  
                  
                  echo '<li'.$li_class.'><'.$tag.$entry_attributes.'>'.$entry_checkbox.$entry_name.$entry_info.$entry_title.'</'.$tag.'></li>';
                }
                
              ?>
              </ul>
            </div>
          </div>
          <?php
          }
          ?>
          <?php echo ($this->extra != '') ? $this->extra : ''; ?>
        </li>
      </ul>