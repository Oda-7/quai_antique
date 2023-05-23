<?php

function reconnect_from_cookie()
{

   if (session_status() == PHP_SESSION_NONE) {
      session_start();
   }

   if (isset($_COOKIE['remember']) && !isset($_SESSION['auth'])) {
      require_once './sys/db.php';
      if (!isset($pdo)) {
         global $pdo;
      }

      $remember_token = $_COOKIE['remember'];
      $parts = explode('//', $remember_token);
      $user_id = $parts[0];
      $req = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
      $req->execute([$user_id]);
      $user = $req->fetch();

      if ($user) {
         $expected = $user_id . '//' . $user->user_remember_token . sha1($user_id . 'ratonlaveurs');

         if ($expected == $remember_token) {

            $_SESSION['auth'] = $user;
            setcookie('remember', $remember_token, time() + 60 * 60 * 24 * 7);
         }
      } else {
         setcookie('remember', NULL, -1);
      }
   }
}

function sortDessert($dessert, $next)
{
   if (str_contains($dessert->categorie_name, 'Vi') || str_contains($dessert->categorie_name, 'Des')) {
      return 0;
   }
   return ($dessert->categorie_name < $next->categorie_name) ? -1 : 1;
}
