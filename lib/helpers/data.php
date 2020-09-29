<?php


use Popcx\Business\Models\Business;

if(!function_exists('pop')){
   function pop()
   {
      return config('pop');
   }
}

/**
 *
 */

if(!function_exists('import')){
   function import($filepath, $extra = false)
   {
      $realpath = realpath($filepath);
      if(!$realpath){
         return null;
      }

      $extension = pathinfo($realpath, PATHINFO_EXTENSION);

      switch (strtolower($extension)) {
         case 'php':
            $import = require $realpath;
            break;
         case 'json':
            $json = file_get_contents($realpath);
            $import = json_decode($json, $extra);
            break;
         case 'csv':
            $csv = file_get_contents($realpath);
            $import = str_getcsv($csv);
            break;
         default:
            $import = file_get_contents($realpath);
            break;
      }

      return $import ?? [];
   }
}


if(!function_exists('business')){
    function business()
    {
//        dump(request()->header('x-popcx-token'));
        return Business::find(6);

    }
}
