<?php
$namePage = 'Connexion';

include './templates/header.php';

if (isset($_SESSION['auth'])) {
   $urlLogin = "/";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
   // header('Location: /');
   exit();
}
// time() + 365*24*3600 pour une validation de cookie d'un an


$errors = array();

if (!empty($_POST['email']) && empty($_POST['password']) || empty($_POST['email']) && !empty($_POST['password'])) {
   $errors['lost'] = "Un champ n'est pas rempli !";
}

if (!empty($_POST['email']) && !empty($_POST['password'])) {
   require_once './sys/db.php';

   try {
      $req = $pdo->prepare('SELECT * FROM users WHERE user_email = ? ');
      $req->execute([$_POST['email']]);
      $user = $req->fetch();

      if ($user->user_confirmed_at == 'NULL') {
         $errors['confirm'] = "L'utilisateur n'a pas confirmer l'email";
      } else {
         if (password_verify($_POST['password'], $user->user_password)) {
            $reqProfil = $pdo->prepare('SELECT * FROM profil WHERE profil_id = ? ');
            $reqProfil->execute([$user->user_id]);
            $userProfil = $reqProfil->fetch();
            $_SESSION['auth'] = $user;
            $_SESSION['flash']['success'] = "Vous êtes maintenant connecté " . $userProfil->profil_firstname;

            if ($_POST['remember']) {
               $remember_token = rand();
               $req = $pdo->prepare('UPDATE users SET user_remember_token = ? WHERE user_id = ?');
               $req->execute([$remember_token, $user->user_id]);
               setcookie('remember', $user->user_id . '//' . $remember_token . sha1($user->user_id . 'ratonlaveurs'), time() + 60 * 60 * 24 * 7, '/');
            }
            // header('Location: panel_user.php;Refresh: 0;');
            $urlLogin = 'panel_user.php';
            echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
            exit();
         } else {
            $errors['Email'] = "Email ou mot de passe incorrect !";
         }
      }
   } catch (PDOException $e) {
      $errors['user'] = "L'utilisateur n'existe pas !";
   }
}

// var_dump(headers_sent());
?>

<?php if (!empty($errors)) : ?>
   <div class="alert alert-danger">
      <ul>
         <?php foreach ($errors as $error) : ?>
            <li><?= $error; ?></li>
         <?php endforeach; ?>
      </ul>
   </div>
<?php endif; ?>

<div class="mx-4">

   <div class="d-flex flex-column align-items-center mt-5">
      <h1 class="mt-2">Connection:</h1>
      <form method="POST" class="d-flex flex-column">
         <label>Email:</label>
         <input class="form-control" type="text" name="email">
         <label for="">Mot de passe:</label>
         <input class="form-control" type="password" name="password">
         <label class="py-2">
            <input class="form-check-input" type="checkbox" name="remember" value="1" />Remember me
         </label>
         <div class="d-flex flex-column align-items-center justify-content-center gap-2 mt-2">
            <a href="/">
               <button class="btn" style="color: #e8eddf; background-color: #242423 ;">Annuler</button>
            </a>
            <input class="btn" style="color: #e8eddf; background-color: #242423 ;" type="submit" value="Connection">
         </div>
      </form>
   </div>
</div>

<?php
include './templates/footer.php';
?>