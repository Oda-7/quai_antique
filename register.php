<?php
$namePage = "S'inscrire";

include './templates/header.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './vendor/phpmailer/phpmailer/src/Exception.php';
require './vendor/phpmailer/phpmailer/src/PHPMailer.php';
require './vendor/phpmailer/phpmailer/src/SMTP.php';

if (!empty($_POST)) {
   $errors = array();
   require './sys/db.php';

   if (empty($_POST['civilite'])) {
      $errors['civilite'] = "Il faut choisir votre civilité";
   }

   if (empty($_POST['firstname']) || !preg_match('/^[a-zA-Zéèâàô]+$/', $_POST['firstname'])) {
      $errors['firstname'] = "Votre prénom n'est pas valide";
   }

   if (empty($_POST['lastname']) || !preg_match('/^[a-zA-Zéèâàô]+$/', $_POST['lastname'])) {
      $errors['lastname'] = "Votre nom n'est pas valide";
   }

   if (empty($_POST['age']) || !preg_match('/^[0-9]{2}/', $_POST['age'])) {
      $errors['age'] = "L'age n'est pas valide";
   }

   if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = "Votre email n'est pas valide";
   } else {

      $req = $pdo->prepare('SELECT user_id FROM users WHERE user_email = ?');
      $req->execute([$_POST['email']]);
      $user = $req->fetch();
      if ($user) {
         $errors['email'] = "Cette adresse email est déja utilisé";
      }
   }

   if (!preg_match('/^[a-zA-Z]{6,}[0-9]{1,}/', $_POST['password'])) {
      $errors['password'] = "Le mot de passe ne comporte pas au minimum 1 caractères numérique";

      if (empty($_POST['password']) || $_POST['password'] != $_POST['password_confirm']) {
         $errors['password'] = "Les mots de passe ne correspondent pas";
      }
   }

   if (empty($errors)) {
      $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
      $token = rand();
      $req = $pdo->prepare("INSERT INTO users SET user_email = ?, user_password = ?, user_confirmation_token = ?,user_register_date = NOW(), user_role_id = 1");
      $req->execute([$_POST['email'], $password, $token]);
      $userId = $pdo->lastInsertId();

      $reqProfil = $pdo->prepare("UPDATE profil SET profil_lastname = ?, profil_firstname = ?, profil_age = ?, profil_civility = ? WHERE profil_id = ?");
      $reqProfil->execute([$_POST['lastname'], $_POST['firstname'], $_POST['age'], $_POST['civilite'], $userId]);

      $mail = new PHPMailer(true);


      $protocol = explode('/', $_SERVER['SERVER_PROTOCOL']);
      if (isset($protocol) && $protocol[0] == 'HTTP') {
         $url = "http";
      } else {
         $url = "https";
      }

      $url .= "://";

      $url .= $_SERVER['HTTP_HOST'];
      // echo $url;

      try {

         $secret_m = 'byzloaokvucfvloe';
         $username_m = 'arnaud.michant@gmail.com';
         $username = 'Arnaud Michant';
         $subject = "Confirmation de votre compte " . $_POST['firstname'] . " " . $_POST['lastname'];
         $mailBody = "Merci de cliquer sur le lien ci-dessous pour confirmer votre compte <br>$url/sys/confirm.php?id=$userId&token=$token";

         //Server settings   
         $mail->SMTPDebug = 2; // debogage: 1 = Erreurs et messages, 2 = messages seulement
         $mail->isSMTP();                                            //Send using SMTP
         $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
         $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
         $mail->Username   = $username_m;                             //SMTP username
         $mail->Password   = $secret_m;                               //SMTP password
         $mail->SMTPSecure = 'TLS';      // STARTTLS pour outlook      //Enable implicit TLS encryption
         $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

         $mail->smtpConnect();
         //Recipients
         $mail->setFrom($username_m, $username);
         $mail->addAddress(trim($_POST['email']));     //Add a recipient

         //Content 
         $mail->isHTML(true);    //Set email format to HTML
         $mail->Subject = $subject;
         $mail->Body = $mailBody;


         $mail->send();
         $_SESSION['flash']['success'] = "Un email de confirmation vous a été envoyer";
      } catch (Exception $e) {
         echo "Le mail n'a pas été envoyer !";
         $errorMail = $mail->ErrorInfo; //Envoyer le mail au propriétaire ou l'enregistrer dans un fichier de log 
         $_SESSION['flash']['danger'] = "Le mail n'a pas été envoyer, avertir le chef Arnaud Michant";
      }
      echo 'la';
      $urlLogin = 'index.php';
      echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
   }
}


?>
<div class="mx-4 mt-5 pt-2">
   <?php if (isset($_SESSION['flash'])) :
      foreach ($_SESSION['flash'] as $type => $message) : ?>
         <div class="alert alert-<?= $type; ?>">
            <?= $message; ?>
         </div>
   <?php endforeach;
      unset($_SESSION['flash']);
   endif; ?>

   <?php if (!empty($errors)) : ?>
      <div class="alert alert-danger">
         <p>Les champs du formulaire sont pas rempli correctement</p>
         <ul>
            <?php foreach ($errors as $error) : ?>
               <li><?= $error; ?></li>
            <?php endforeach; ?>
         </ul>
      </div>
   <?php endif; ?>

   <div class="d-flex flex-column align-items-center gap-2">
      <h1>S'inscrire</h1>
      <form action="" method="post" class="d-flex flex-column gap-2">
         <div>
            <input class="form-check-input" type="radio" id="Mr" name="civilite" value="Mr">
            <label for="Mr" class="inline-label">Monsieur</label>
         </div>
         <div>
            <input class="form-check-input" type="radio" id="Mme" name="civilite" value="Mme">
            <label for="Mme" class="inline-label">Madame</label>
         </div>
         <input class="form-control" type="text" name="firstname" id="surname" placeholder="Prénom">
         <input class="form-control" type="text" name="lastname" id="name" placeholder="Nom">
         <label class="text-center" for="age">Âge:</label>
         <input class="form-control" type="select" name="age" id="age" placeholder="20">
         <input class="form-control" type="email" name="email" id="email" placeholder="Adresse e-mail">
         <label class="text-center">Le mot de passe doit contenir<br>6 lettres et 1 chiffres</label>
         <input class="form-control" type="password" name="password" placeholder="Mot de passe">
         <input class="form-control" type="password" name="password_confirm" placeholder="Confirmer votre mot de passe">
         <div class="d-flex flex-column align-items-center justify-content-center gap-2">
            <a class="text-decoration-none text-dark" href="/">
               <button class="btn" style="color: #e8eddf; background-color: #242423 ;">Annuler</button>
            </a>

            <button class="btn " style="background-color: #242423 ;color: #e8eddf;" type="submit">S'inscrire</button>
         </div>
      </form>
   </div>

</div>

<?php
include './templates/footer.php';
?>