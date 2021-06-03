<?php

namespace Core\Data\Security;

class PermissionsHandler
{
  public function __construct()
  {

  }

  public function allow($entity)
  {
    dump($entity);
    return $this;

  }

  public function toDo($action)
  {
    return true;
  }

}
