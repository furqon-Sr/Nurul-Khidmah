<?php> temp_route_debug.php  
require 'vendor/autoload.php'; 
 = require 'bootstrap/app.php'; 
 = - 
- 
 = app('router')- 
if () { echo - . ' ' . implode(',', -; } else { echo 'logout route missing'; }
