<?php $pageName = 'Mot de passe oublié';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './vendor/phpmailer/phpmailer/src/Exception.php';
require './vendor/phpmailer/phpmailer/src/PHPMailer.php';
require './vendor/phpmailer/phpmailer/src/SMTP.php';

if (!empty($_POST) && !empty($_POST['email_forget'])) {
   require_once './sys/db.php';

   $req = $pdo->prepare('SELECT * FROM users WHERE user_email = ? AND user_confirmed_at IS NOT NULL');
   $req->execute([$_POST['email_forget']]);
   $user = $req->fetch();

   if ($user) {
      session_start();
      $reset_token = rand();
      $pdo->prepare('UPDATE users SET user_reset_token = ?, user_reset_at = NOW() WHERE user_id = ?')->execute([$reset_token, $user->id]);
      $_SESSION['flash']['success'] = "Les instructions de rappel du mot de passe vous ont été envoyées par courrier électronique.";

      $mailForget = new PHPMailer(true);

      try {
         $secret_m = 'byzloaokvucfvloe';
         $username_m = 'arnaud.michant@gmail.com';
         $username = 'Arnaud Michant';
         $subject = "Réinitialiser votre mot de passe";
         $mailBody = "Merci de cliquer sur le lien ci-dessous pour confirmer votre compte <br>localhost:8000/sys/confirm.php?id=$userId&token=$token";

         //Server settings   
         $mailForget->SMTPDebug = 0; // debogage: 1 = Erreurs et messages, 2 = messages seulement
         $mailForget->isSMTP();                                            //Send using SMTP
         $mailForget->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
         $mailForget->SMTPAuth   = true;                                   //Enable SMTP authentication
         $mailForget->Username   = $username_m;                             //SMTP username
         $mailForget->Password   = $secret_m;                               //SMTP password
         $mailForget->SMTPSecure = 'TLS';      // STARTTLS pour outlook      //Enable implicit TLS encryption
         $mailForget->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

         $mailForget->smtpConnect();
         //Recipients
         $mailForget->setFrom($username_m, $username);
         $mailForget->addAddress(trim($_POST['email_forget']));     //Add a recipient

         //Content 
         $mailForget->isHTML(true);    //Set email format to HTML
         $mailForget->Subject = $subject;
         $mailForget->Body = $mailBody;


         $mailForget->send();
         $_SESSION['flash']['success'] = "Un email de confirmation vous a été envoyer";
      } catch (Exception $e) {
         echo "Le mail n'a pas été envoyer !";
         $errorMail = $mailForget->ErrorInfo; //Envoyer le mail au propriétaire ou l'enregistrer dans un fichier de log 
         $_SESSION['flash']['danger'] = "Le mail n'a pas été envoyer, avertir le chef Arnaud Michant";
      }

      //mail($_POST['email'], "", "Reset your password", "In order to renationalize your password please click on this link\n\nhttp://localhost/App/public/src/reset.php?id={$user->id}&token=$reset_token");
      header('Location: login.php');
      exit();
   } else {
      $_SESSION['flash']['danger'] = "Aucun compte ne correspond à cet email";
   }
}

require './templates/header.php'; ?>

<div class="w-50 p-5 my-5 m-auto bg-light">
   <h1>Forget password</h1>
   <form method="POST">
      <div class="form-group mb-3">
         <label for="">Email</label>
         <input type="email" name="email_forget" class="form-control" />
      </div>

      <button type="submit" class="btn btn-primary">Envoyer le mail</button>
   </form>
</div>

<?php require_once './templates/footer.php'; ?>