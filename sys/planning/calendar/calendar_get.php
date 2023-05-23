<?php
include '../../db.php';

if (isset($_POST['date']) && isset($_POST['part'])) {
   $reqSelectCalendarNumberReservation = $pdo->prepare('SELECT number_reservation FROM calendar WHERE date_calendar = ? AND part_day = ?');
   $reqSelectCalendarNumberReservation->execute([$_POST['date'], $_POST['part']]);
   $selectNumberReservation = $reqSelectCalendarNumberReservation->fetch();
   echo $selectNumberReservation->number_reservation;
}
