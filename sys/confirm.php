<?php

$user_id = $_GET['id'];
$token = $_GET['token'];

require './db.php';

$req = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$req->execute($user_id);
$user = $req->fetch();

if ($user && $user->user_confirmation_token == $token) {
   $pdo->prepare('UPDATE users SET user_confirmation_token = NULL, user_confirmed_at = NOW() WHERE user_id = ?')->execute([$user_id]);
   $_SESSION['flash']['success'] = "Votre compte est bien validé";
   $_SESSION['auth'] = $user;
   // header('Location: /login.php');
   $urlLogin = "./login.php";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
} else {
   $_SESSION['flash']['danger'] = "Le token n'est pas valide";
   // header('Location: /');
   $urlLogin = "/";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
}
