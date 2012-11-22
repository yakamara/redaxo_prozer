<?php

if(!rex::isBackend())
{
  rex_extension::register('FE_OUTPUT',
    function($params)
    {
      $output = $params['subject'];
      $output .= pz::controller();
      rex_response::sendArticle($output, 'frontend');

    }
  );
}
