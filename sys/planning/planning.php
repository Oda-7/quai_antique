<?php
include './sys/planning/db_planning.php';

if ($dayTime) {
   $addPlanning = 'd-none';
}
?>
<section class="d-flex flex-column align-items-center flex-wrap justify-content-center gap-2 pt-3">
   <div class="d-flex gap-3">
      <div>
         <?php
         foreach ($dayTime as $dayTable) {
            echo '<p><b>' . $dayTable->planning_name . '</b></p>';
         } ?>
      </div>
      <div class="d-flex flex-column flex-wrap">
         <?php
         foreach ($dayTime as $dayTable) {
            echo '<p>';
            if ($dayTable->planning_close == 'fermé') {
               echo $dayTable->planning_close;
            } else {
               if (!$dayTable->planning_hours_close && !$dayTable->planning_second_hours_open) {
                  echo substr($dayTable->planning_hours_open, 0, -3) . ' - ' . substr($dayTable->planning_second_hours_close, 0, -3);
               } else {
                  if ($dayTable->planning_hours_open) {
                     echo substr($dayTable->planning_hours_open, 0, -3) . ' - ' . substr($dayTable->planning_hours_close, 0, -3);
                  }
                  if ($dayTable->planning_second_hours_open) {
                     echo ' / ' . substr($dayTable->planning_second_hours_open, 0, -3) . ' - ' . substr($dayTable->planning_second_hours_close, 0, -3);
                  }
               }
            }
            echo '</p>';
         }
         ?>
      </div>
   </div>

   <form class="d-flex flex-wrap flex-column gap-3 align-items-center" action="" method="post">
      <input type="submit" class="btn" style="background-color: #333533 ;color: #e8eddf;" name="submit_modify_planning" value="Modifier le planning">
      <input style="background-color: #333533 ;color: #e8eddf;" type="submit" style="background-color: #333533 ;color: #e8eddf;" name="submit_add_planning" value="Ajouter un planning" id="add_planning" class="<?= $addPlanning ?> btn">

      <div class="d-flex flex-row flex-wrap gap-2 justify-content-center ">
         <?php
         if (isset($_POST['submit_add_planning']) || isset($_POST['submit_modify_planning'])) {
            foreach ($dayTime as $dayform => $d) {
               $dayOpen = explode(':', $d->planning_hours_open);
               $dayClose = explode(':', $d->planning_hours_close);
               $daySecondOpen = explode(':', $d->planning_second_hours_open);
               $daySecondClose = explode(':', $d->planning_second_hours_close);

               echo '
            <li class="d-flex flex-column p-3 align-items-center rounded-3" style="background-color: #333533 ;color: #e8eddf;">
            <label>' . ucfirst($d->planning_name) . '</label>';
               echo '<div class="d-flex flex-column align-items-center gap-2 p-2">
               <label>1ere ouverture :</label>';
               echo '<div class="d-flex flex-row align-items-center gap-1">
               <select class="form-select" name="hours_open_' . $d->planning_name . '">';
               hoursAm($dayOpen[0]);
               echo '</select>
            <select class="form-select" name="minute_open_' . $d->planning_name . '">';
               minute($dayOpen[1]);
               echo '</select> -
            <select class="form-select" name="hours_close_' . $d->planning_name . '">';
               hoursAm($dayClose[0]);
               echo '</select>
            <select class="form-select" name="minute_close_' . $d->planning_name . '">';
               minute($dayClose[1]);
               echo '</select></div>
            <label>2nde ouverture :</label>
            <div class="d-flex flex-row align-items-center gap-1">
            <select class="form-select" name="second_hours_open_' . $d->planning_name . '">';
               hoursPm($daySecondOpen[0]);
               echo '</select>
            <select class="form-select" name="second_minute_open_' . $d->planning_name . '">';
               minute($daySecondOpen[1]);
               echo '</select> - 
            <select class="form-select" name="second_hours_close_' . $d->planning_name . '">';
               hoursPm($daySecondClose[0]);
               echo '</select>
            <select class="form-select" name="second_minute_close_' . $d->planning_name . '">';
               minute($daySecondClose[1]);
               echo '</select></div>
               <label>Fermeture :</label>
            <input type="checkbox" name="planning_close_' . $d->planning_name . '[]" value="' . $d->planning_id . '">
            </div>
         </li>';
            }
            echo '</div><input class="btn" style="background-color: #333533 ;color: #e8eddf;" type="submit" name="submit_validate" value="Valider">';
         }
         ?>



   </form>
</section>
<br>