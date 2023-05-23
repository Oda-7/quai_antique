<?php
require '../db.php';

if (isset($_POST['number_reservation_default'])) {
   $reqUpdateReservation = $pdo->prepare('UPDATE FROM profil SET profil_default_reservation = ? WHERE profil_id = ?');
   $reqUpdateReservation->execute([$_POST['number_reservation_default'], $_POST['id_profil']]);

   $reqSelectNumberReservation = $pdo->prepare('SELECT * FROM profil WHERE profil_id = ?');
   $reqSelectNumberReservation->execute([$_POST['id_profil']]);
   $selectNumberReservation = $reqSelectNumberReservation->fetch();
   echo $selectNumberReservation->number_p_reservation;
}
