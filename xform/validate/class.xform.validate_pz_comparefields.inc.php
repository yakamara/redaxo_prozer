<?php

class rex_xform_validate_pz_comparefields extends rex_xform_validate_abstract
{

  function enterObject()
  {
    if($this->params["send"]=="1")
    {
      $field_1 = $this->getElement(2);
      $field_2 = $this->getElement(3);

      foreach($this->obj as $o)
      {
        if ($o->getName() == $field_1)
        {
          $id_1 = $o->getId();
          $value_1 = $o->getValue();
        }
        if ($o->getName() == $field_2)
        {
          $id_2 = $o->getId();
          $value_2 = $o->getValue();
        }
      }

      $error = FALSE;

      if(!isset($value_1) || !isset($value_2))
        $error = TRUE;

      if(!$error)
      {
        switch($this->getElement(4)) {
          case(">"):
            if($value_1 > $value_2)
              $error = TRUE;
            break;
          case("<"):
            if($value_1 < $value_2)
              $error = TRUE;
            break;
          case(">="):
            if($value_1 >= $value_2)
              $error = TRUE;
            break;
          case("<="):
            if($value_1 <= $value_2)
              $error = TRUE;
            break;
          case("!="):
            if($value_1 != $value_2)
              $error = TRUE;
            break;
          default:
            if($value_1 == $value_2)
              $error = TRUE;
        }

      }

      if ($error)
      {
        $this->params["warning"][$id_1] = $this->params["error_class"];
        $this->params["warning"][$id_2] = $this->params["error_class"];
        $this->params["warning_messages"][$id_1] = $this->getElement(5);
      }
    }
  }

  function getDescription()
  {
    return "pz_comparefields -> prüft ob = | != | < | > | <= | >=, beispiel: validate|compare|label1|label2|type<=|warning_message ";
  }

  function getDefinitions()
  {
    return array(
      'type' => 'validate',
      'name' => 'compare_value',
      'values' => array(
        array( 'type' => 'select_name', 'label' => 'Name der 1. Felder' ),
        array( 'type' => 'select_name', 'label' => 'Name der 1. Felder'),
        array( 'type' => 'text',	'label' => '= / != / < / > / <= / >='),
        array( 'type' => 'text', 	'label' => 'Fehlermeldung'),
      ),
      'description' => '2 Felder werden miteinander vergleichen',
    );

  }
}