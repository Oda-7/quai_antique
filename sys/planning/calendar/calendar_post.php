<?php
include '../../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../../vendor/phpmailer/phpmailer/src/SMTP.php';

// faire une verification grace a la part de la journée et la date, si la verification du nombre et plus grande que le nombre de reservation donnée
if (isset($_POST['number_reservation']) && isset($_POST['date']) && isset($_POST['part'])) {
   // récupération de l'email dans la session et envoie d'email
   if (isset($_POST['email']) && $_POST['hours']) {

      $mailReservation = new PHPMailer(true);
      try {
         $secret_m = 'byzloaokvucfvloe';
         $username_m = 'arnaud.michant@gmail.com';
         $username = 'Arnaud Michant';
         $subject = "Confirmation de reservation pour " . $_POST['date'] . " à " . $_POST['hours'];
         $mailBody = "Bonjour, votre réservation a bien été enregistré nous somme heureux de vous acceuillir le " . $_POST['date'] . " à " . $_POST['hours'] . '.
         Merci de vous munir de la réservation sur votre téléphone.';

         //Server settings   
         $mailReservation->SMTPDebug = 0; // debogage: 1 = Erreurs et messages, 2 = messages seulement
         $mailReservation->isSMTP();                                            //Send using SMTP
         $mailReservation->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
         $mailReservation->SMTPAuth   = true;                                   //Enable SMTP authentication
         $mailReservation->Username   = $username_m;                             //SMTP username
         $mailReservation->Password   = $secret_m;                               //SMTP password
         $mailReservation->SMTPSecure = 'TLS';      // STARTTLS pour outlook      //Enable implicit TLS encryption
         $mailReservation->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

         $mailReservation->smtpConnect();
         //Recipients
         $mailReservation->setFrom($username_m, $username);
         $mailReservation->addAddress(trim($_POST['email']));     //Add a recipient

         //Content 
         $mailReservation->isHTML(true);    //Set email format to HTML
         $mailReservation->Subject = $subject;
         $mailReservation->Body = $mailBody;


         $mailReservation->send();
         $_SESSION['flash']['success'] = "Un email de confirmation vous a été envoyer";
      } catch (Exception $e) {
         echo "Le mail n'a pas été envoyer !";
         $errorMail = $mailReservation->ErrorInfo; //Envoyer le mail au propriétaire ou l'enregistrer dans un fichier de log 
         $_SESSION['flash']['danger'] = "Le mail n'a pas été envoyer, avertir le chef Arnaud Michant";
      }
   }

   $reqSelectCalendarNumberReservation = $pdo->prepare('SELECT number_reservation FROM calendar WHERE date_calendar = ? AND part_day = ?');
   $reqSelectCalendarNumberReservation->execute([$_POST['date'], $_POST['part']]);
   $selectNumberReservation = $reqSelectCalendarNumberReservation->fetch();
   $numberUpdate = $selectNumberReservation->number_reservation - $_POST['number_reservation'];

   $reqUpdateCalendar = $pdo->prepare('UPDATE calendar SET number_reservation = ? WHERE date_calendar = ? AND part_day = ?');
   $reqUpdateCalendar->execute([$numberUpdate, $_POST['date'], $_POST['part']]);

   $reqSelectCalendarNumberReservation->execute([$_POST['date'], $_POST['part']]);
   $selectLastNumber = $reqSelectCalendarNumberReservation->fetch();

   echo $selectLastNumber->number_reservation;
}
