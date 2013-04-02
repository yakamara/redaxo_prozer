<?php

if(!rex::isBackend())
{
  rex_extension::register('FE_OUTPUT',
    function(rex_extension_point $ep)
    {
      $output = $ep->getSubject();
      $output .= pz::controller();
      rex_response::sendPage($output);
    }
  );
}
