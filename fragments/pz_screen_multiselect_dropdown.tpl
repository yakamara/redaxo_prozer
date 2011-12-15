<?php

/*
  Drop Down 
    - Bspl.: Projektauswahl
    
  class_ul ............. = CSS Klasse fuer ul
  class_selected ....... = CSS Klasse fuer Auswahl
  selected ............. = Auswahl
  selected_text ........ = Text der hinter 18 "Projekte ausgewählt"
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

<ul id="<?php echo $this->layer_id; ?>" class="sl1<?php echo (isset($this->class_ul)) ? ' '.$this->class_ul : ''; ?>">
  
  <?php
    // Flayout nur anzeigen, wenn Entries vorhanden (XForm disabled wird nichts uebergeben)
    if (count($this->entries) > 0)
    {
  ?>
  
        <?php
          $i = 0;
          
          $selected_text = "";
          $content = "";
          $c = count($this->entries);
          $selected_amount = 0;

// href setzen, 
// - eigene klasse setzen/wegnehmen
// - alle aktiven auslesen 
// - 
          
          foreach ($this->entries as $entry)
          {
            $i++;
            $li_class = 'entry';
            $li_class .= ($i == 1) ? ' first' : '';
            $li_class .= ($i == $c) ? ' last' : '';
            $li_class = ($li_class != '') ? ' class="'.trim($li_class).'"' : '';
            
            $entry['attributes']['href'] = "javascript:void(0)"; // javascript:pz_multiselect_toggleValue(this,'".$this->multiselect_field."','".$entry["id"]."')";
            
            if(in_array($entry["id"],$this->selected_values))
            {
              $selected_amount++;
              $entry['class'] = "active";
              if($selected_text == "") // $selected_text .= ' / ';
              	$selected_text = $entry["title"];
            }
            
            $entry['attributes']['data-id'] = $entry["id"];
            $entry['attributes']['data-title_short'] = $entry["title_short"];
            
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
            
            $content .= '<li'.$li_class.'><'.$tag.$entry_attributes.'>'.$entry_checkbox.$entry_name.$entry_info.$entry_title.'</'.$tag.'></li>';
          }

		  if($selected_amount > 1) {
            	$selected_text = $selected_amount." ".@$this->text_selected;
          }

          
        ?>
        
  <li class="selected"><span class="selected" id="<?php echo $this->layer_id; ?>_text"><?php echo $selected_text; ?></span>
  
  <div class="flyout">
      <div class="content">
        <ul class="entries">
        <?php echo $content; ?>
        </ul>
      </div>
    </div>
    <?php
    }
    ?>
    <?php echo (isset($this->extra)  && $this->extra != '') ? $this->extra : ''; ?>
    <?php echo '<input type="hidden" id="'.$this->multiselect_field.'" value="'.implode(",",$this->selected_values).'" />'; ?>
  </li>
</ul>
      
<script>$("#<?php echo $this->layer_id; ?> .flyout ul li a").click(function() {
	
	if ($(this).is('.active')) {
		$(this).removeClass('active');
	}else {
		$(this).addClass('active');
	}
	
	var values = "";
	var selected_text = "";
	var selected_amount = 0;
	$("#<?php echo $this->layer_id; ?> .flyout ul li a.active").each(function() {
		if(values != "")
		  values += ",";
		values += $(this).attr("data-id");
		if(selected_text == "") // selected_text += " / ";
			selected_text = $(this).attr("data-title_short");
		selected_amount = selected_amount +1;
	});
	
	if(selected_amount > 1)
		selected_text = selected_amount+" <?php echo htmlspecialchars(@$this->text_selected); ?>";
	$("#<?php echo $this->layer_id; ?>_text").html(selected_text);
	
	// reload of elemnts
	$("#<?php echo $this->multiselect_field; ?>").val(values);
	
	<?php
	if(isset($this->refresh_layer))
	{
		foreach($this->refresh_layer as $layer){
			echo '
	url = $("#'.$layer.'").attr("data-url").replace("___value_ids___", values);
	pz_loadPage("'.$layer.'",url);
	';			
		}
	}
	
	?>
});</script>
      
      
      
      