<?php

$error = '';

// bisher installierte Version:
$version = $this->getVersion();

if(version_compare($version, '2.0.0', '='))
{
  // ...


}

if(version_compare($version, '2.0.1', '<'))
{
  // ...


}

if(version_compare($version, '2.0.2', '<'))
{
  // ...
  
  
}



if($error)
  $this->setProperty('updatemsg', $error);
else
  $this->setProperty('update', true);