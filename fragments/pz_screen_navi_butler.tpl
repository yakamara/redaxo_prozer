
      <ul class="navi-butler clearfix">
        <?php
        $i = 0;
        $c = count($this->entries);
        foreach ($this->entries as $entry)
        {
          $i++;
          $li_class = 'lev1';
          $li_class .= ($i == 1) ? ' first' : '';
          $li_class .= ($i == $c) ? ' last' : '';
                  
          $li_class = ($li_class != '') ? ' class="'.trim($li_class).'"' : '';
          
          $entry_attributes = '';
          if (isset($entry['attributes']) && count($entry['attributes']) > 0)
          {
            foreach ($entry['attributes'] as $attr => $value)
              $entry_attributes .= ' '.$attr.'="'.$value.'"';
          }
      		
          $entry_url = (isset($entry['url']) && $entry['url'] != '') ? $entry['url'] : 'javascript:void(0)';
          $entry_name = (isset($entry['name']) && $entry['name'] != '') ? $entry['name'] : '';

          echo '<li'.$li_class.'><a href="'.$entry_url.'"'.$entry_attributes.'>'.$entry_name.'</a></li>';
      	}
      	?>
      </ul>