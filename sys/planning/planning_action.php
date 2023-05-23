<?php
include './sys/planning/db_planning.php';

//Ajout du planning en db

if (isset($_POST['submit_add_planning'])) {
   $weekInsertOrNot = $pdo->prepare('SELECT COUNT(planning_name)
      AS number_week
      FROM planning');
   $weekInsertOrNot->execute();
   $insert = $weekInsertOrNot->fetch();

   if ($insert->number_week <= 1) {
      $weekInsert = $pdo->prepare("INSERT INTO planning (planning_name, planning_close) VALUES ('Lundi', 'fermé'), ('Mardi', 'fermé'), ('Mercredi', 'fermé'), ('Jeudi', 'fermé'), ('Vendredi', 'fermé'), ('Samedi', 'fermé'), ('Dimanche', 'fermé')");
      $weekInsert->execute();
   }
   $urlLogin = "panel.php";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
}

//boucle d'insertion de resultat du formulaire de planning
if (isset($_POST['submit_validate'])) {
   $errors = array();
   foreach ($dayTime as $day) {

      $secondHoursFormatOpen = $_POST['second_hours_open_' . $day->planning_name] . ':' . $_POST['second_minute_open_' . $day->planning_name];
      $secondHoursFormatClose = $_POST['second_hours_close_' . $day->planning_name] . ':' . $_POST['second_minute_close_' . $day->planning_name];
      $hoursFormatOpen = $_POST['hours_open_' . $day->planning_name] . ':' . $_POST['minute_open_' . $day->planning_name];
      $hoursFormatClose = $_POST['hours_close_' . $day->planning_name] . ':' . $_POST['minute_close_' . $day->planning_name];

      if (!ctype_digit($_POST['hours_open_' . $day->planning_name])) {
         $hoursFormatOpen = NULL;
         $hoursFormatClose = NULL;
      } elseif (!ctype_digit($_POST['second_hours_open_' . $day->planning_name]) && !ctype_digit($_POST['second_hours_close_' . $day->planning_name])) {
         $secondHoursFormatOpen = NULL;
         $secondHoursFormatClose = NULL;
      }

      if (!empty($_POST['planning_close_' . $day->planning_name])) {
         $closeInsert = $pdo->prepare('UPDATE planning SET planning_hours_open = NULL, planning_hours_close = NULL, planning_second_hours_open = NULL, planning_second_hours_close = NULL, planning_close = "fermé" WHERE planning_name = ?');
         $closeInsert->execute([$day->planning_name]);
      } elseif (
         ctype_digit($_POST['hours_open_' . $day->planning_name])
         && !ctype_digit($_POST['hours_close_' . $day->planning_name])
         && !ctype_digit($_POST['second_hours_open_' . $day->planning_name])
         && ctype_digit($_POST['second_hours_close_' . $day->planning_name])
      ) {
         $hoursDaytimeOpen = $hoursFormatOpen;
         $hoursDaytimeClose = $secondHoursFormatClose;
         $daytime = $day->planning_name;

         array_push($errors, "Vous allez ouvrir toute la journée " . $day->planning_name);

         $hoursOpenDaytimeinsert = $pdo->prepare('UPDATE planning SET planning_hours_open = ?, planning_second_hours_close = ?, planning_close = NULL WHERE planning_name = ?');
         $hoursOpenDaytimeinsert->execute([$hoursDaytimeOpen, $hoursDaytimeClose, $daytime]);
      } elseif (
         ctype_digit($_POST['hours_open_' . $day->planning_name]) && ctype_digit($_POST['minute_open_' . $day->planning_name]) && ctype_digit($_POST['hours_close_' . $day->planning_name]) && ctype_digit($_POST['minute_close_' . $day->planning_name])
         || ctype_digit($_POST['second_hours_open_' . $day->planning_name]) && ctype_digit($_POST['second_minute_open_' . $day->planning_name]) && ctype_digit($_POST['second_hours_close_' . $day->planning_name]) && ctype_digit($_POST['second_minute_close_' . $day->planning_name])
      ) {
         if (
            ctype_digit($_POST['hours_open_' . $day->planning_name])
            && ctype_digit($_POST['hours_close_' . $day->planning_name])
            && !ctype_digit($_POST['second_hours_open_' . $day->planning_name])
            && ctype_digit($_POST['second_hours_close_' . $day->planning_name])
         ) {
            $errors['no_select_2nde'] = "Vous n'avez pas choisit de créneau pour la 2nde partie " . $day->planning_name;
         } elseif (
            ctype_digit($_POST['hours_open_' . $day->planning_name])
            && !ctype_digit($_POST['hours_close_' . $day->planning_name])
            && ctype_digit($_POST['second_hours_open_' . $day->planning_name])
            && ctype_digit($_POST['second_hours_close_' . $day->planning_name])
         ) {
            $errors['no_select_1ere'] = "Vous n'avez pas choisit de créneau pour la 1ere partie " . $day->planning_name;
         } elseif ((int) $_POST['hours_open_' . $day->planning_name] > $_POST['hours_close_' . $day->planning_name]) {
            $errors['first_party'] = "Le crénaux choisit pour la 1ère partie de journée n'est pas bon " . $day->planning_name;
         } elseif ((int) $_POST['second_hours_open_' . $day->planning_name] > $_POST['second_hours_close_' . $day->planning_name]) {
            $errors['second_party'] = "Le crénaux choisit pour la 2nde partie de journée n'est pas bon " . $day->planning_name;
         } elseif ((int) $_POST['hours_open_' . $day->planning_name] + 2 > $_POST['hours_close_' . $day->planning_name]) {
            // première ouverture courte
            array_push($errors, 'Vous ouvrez le restaurant pour moins de 2 heures pour la première ouverture ? ' . $day->planning_name);
            $hoursOpeninsertContrainte = $pdo->prepare('UPDATE planning SET planning_hours_open = ?, planning_hours_close = ?, planning_second_hours_open = ?, planning_second_hours_close = ?, planning_close = NULL WHERE planning_name = ?');
            $hoursOpeninsertContrainte->execute([$hoursFormatOpen, $hoursFormatClose, $secondHoursFormatOpen, $secondHoursFormatClose, $day->planning_name]);
         } elseif ((int) $_POST['second_hours_open_' . $day->planning_name] + 2 > $_POST['second_hours_close_' . $day->planning_name]) {
            // seconde ouverture courte
            array_push($errors, 'Vous ouvrez le restaurant pour moins de 2 heures pour la seconde ouverture ? ' . $day->planning_name);
            $hoursOpeninsertContrainte = $pdo->prepare('UPDATE planning SET planning_hours_open = ?, planning_hours_close = ?, planning_second_hours_open = ?, planning_second_hours_close = ?, planning_close = NULL WHERE planning_name = ?');
            $hoursOpeninsertContrainte->execute([$hoursFormatOpen, $hoursFormatClose, $secondHoursFormatOpen, $secondHoursFormatClose, $day->planning_name]);
         } else {
            $hoursOpeninsert = $pdo->prepare('UPDATE planning SET planning_hours_open = ?, planning_hours_close = ?, planning_second_hours_open = ?, planning_second_hours_close = ?, planning_close = NULL WHERE planning_name = ?');
            $hoursOpeninsert->execute([$hoursFormatOpen, $hoursFormatClose, $secondHoursFormatOpen, $secondHoursFormatClose, $day->planning_name]);
         }
      }
   }
   $urlLogin = "panel.php";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
}

function hoursAm($h)
{
   if (!empty($h)) {
      echo '<option value="' . $h . '" selected>' . $h . '</option>';
   } else {
      echo '<option value="NULL" selected>--</option>';
   }

   for ($hours = 8; $hours < 16; $hours++) {
      echo '<option value="' . $hours . '">' . $hours . '</option>';
   }
   echo '<option value="NULL">--</option>';
}

function hoursPm($h)
{
   if (!empty($h)) {
      echo '<option value="' . $h . '" selected>' . $h . '</option>';
   } else {
      echo '<option value="NULL" selected>--</option>';
   }



   for ($hours = 15; $hours < 24; $hours++) {
      echo '<option value="' . $hours . '">' . $hours . '</option>';
   }
   echo '<option value="NULL">--</option>';
}
function minute($m)
{
   $minute = [00, 15, 30, 45];
   if ($m != NULL) {
      echo '<option value="' . $m . '"selected>' . $m . '</option>';
   } else {
      echo '<option value="0" selected>0</option>';
   }
   foreach ($minute as $min) {
      echo '<option value="' . $min . '">' . $min . '</option>';
   }
   echo '<option>--</option>';
}
