<?php
if ($_SERVER['SERVER_NAME'] == "localhost") {
   // Information a modifier pour une utilisation local
   $host_name = '127.0.0.1';
   $database = 'quaiantique';
   $user_name = 'admin';
   $password = 'quaiantique';
} else {
   $host_name = 'db5013125203.hosting-data.io';
   $database = 'dbs11016128';
   $user_name = 'dbu1243084';
   $password = 'quai.AntiqueArnaud3';
}
