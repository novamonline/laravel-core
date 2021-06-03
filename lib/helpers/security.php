<?php

if(!function_exists('permissions')){
  function permissions()
  {
    return app(PermissionsHandler::class);

  }
}

if(!function_exists('validation')){
  function validation()
  {

  }
}
