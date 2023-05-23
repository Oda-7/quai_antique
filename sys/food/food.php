<?php
include './sys/food/db_food.php';
include './sys/allergic/db_allergic.php';

?>

<?php if ($foodList) : ?>
   <form method="post" class="">
      <div class="border border-3 border-dark rounded-3" style="background-color: white ;">
         <div class=" d-flex flex-row  p-2 gap-1" style="background-color: #242423 ;color:white;">
            <p scope="col" class="pt-2">Nom des aliments</p>
            <p scope="col" class="pt-2 pe-3">Origine</p>
            <p scope="col" class="pt-2">Condition de vie</p>
            <p scope="col" class="pt-2">Allergènes</p>
            <p scope="col" class=""></p>
            <p scope="col" class=""> </p>
         </div>
         <div class="border border-1 d-flex flex-column flex-wrap" tabindex="0" style="overflow-x: scroll; height: 500px;">
            <?php


            foreach ($foodList as $food) {
               $reqFoodAllergic = $pdo->prepare('SELECT allergic_id FROM food_allergic WHERE food_id = ?');
               $reqFoodAllergic->execute([$food->food_id]);
               $foodAllergic = $reqFoodAllergic->fetch();
               if ($foodAllergic) {
                  $reqAllergicName = $pdo->prepare('SELECT * FROM allergic WHERE allergic_id = ?');
                  $reqAllergicName->execute([$foodAllergic->allergic_id]);
                  $allergicName = $reqAllergicName->fetch();
               }

               echo '<div class="d-flex flex-row align-items-center justify-content-between border p-1 px-2">
            <p class="pt-3 px-1">' . $food->food_name . '</p>';
               if ($food->food_origin) {
                  echo '<p class="pt-3 px-1">' . $food->food_origin . '</p>';
               } else {
                  echo '<p class="pt-3 px-1"> -- </p>';
               }

               if ($food->food_breeding) {
                  if ($food->food_breeding == 1) {
                     echo '<p class="pt-3 px-1">' . ucfirst('élevage') . '</p>';
                  } else {
                     echo '<p class="pt-3 px-1">Sauvage</p>';
                  }
               } else {
                  echo '<p class="pt-3 px-1"> -- </p>';
               }

               if ($foodAllergic) {
                  echo '<p class="pt-3 px-1">' . $allergicName->allergic_name . '</p>
            <p class="pt-3 px-1"><a type="button" href="./panel.php?food=' . $food->food_id . '&allergic=' . $allergicName->allergic_id . '"><img  src="./svg/sticky.svg"></a></p>';
               } else {
                  echo '<p class="pt-3 px-1"> -- </p>
            <p class="pt-3 px-1"><a type="button" href="./panel.php?food=' . $food->food_id . '"><img src="./svg/sticky.svg"></a></p>';
               }
               echo '<p class="pt-3 px-1"><input type="checkbox" name="checkbox_delete_food[]" value="' . $food->food_id . '"></p>
         </div>';
               unset($allergicName);
            }
            ?>
         </div>

      </div>
      <div class="d-flex flex-wrap gap-2 py-2 justify-content-center">
         <input class="btn" style="background-color: #242423 ;color:white;" type="submit" name="add_food" value="Ajouter">
         <input class="btn" style="background-color: #242423 ;color:white;" type="submit" name="delete_food" value="Supprimer">
      </div>
   </form>
<?php
else :
   echo 'Aucun aliment';
endif; ?>

<?php
if (isset($_POST['add_food'])) {
   if (isset($_GET)) {
      unset($_GET['food']);
   }
   echo '<form method="post" class="d-flex gap-2 flex-column align-items-center">
      <input class="form-control" type="text" name="add_food_name" placeholder="Aliment">
      <input class="form-control" type="text" name="add_food_origin" placeholder="Traçabilités des produits">
      <select class="form-select" name="add_food_breeding">
         <option value="null" selected>Condition de vie</option>
         <option value="1">Élevage</option>
         <option value="2">Sauvage</option>
      </select>
      <select class="form-select" name="select_food_allergic">
         <option value="null">Allergènes</option>';
   foreach ($allergicListBd as $allergic) {
      echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
   }
   echo '</select>
      <input class="btn mb-2" style="background-color: #242423 ;color:white;" type="submit" name="validate_add_food">
      <input class="btn mb-2" style="background-color: #242423 ;color:white;" type="submit" id="close_post_food" value="Annuler">
   </form>';
}

if (isset($_GET['food'])) {
   $reqSelectFoodName = $pdo->prepare('SELECT * FROM food WHERE food_id = ?');
   $reqSelectFoodName->execute([$_GET['food']]);
   $modifyFoodName = $reqSelectFoodName->fetch();

   echo '<form method="post" class="d-flex gap-2 flex-column align-items-center">
      <input class="form-control" type="text" name="modify_food_name" value="' . $modifyFoodName->food_name . '" placeholder="Aliment"> 
      <input class="form-control" type="text" name="modify_food_origin" value="' . $modifyFoodName->food_origin . '" placeholder="Traçabilité des viandes">
      <select class="form-select" name="modify_food_breeding">';
   if (!$modifyFoodName->food_breeding) {
      echo '<option value="null" selected>Condition de vie</option>';
   } else {
      $foodBreading = [
         "Élevage",
         'Sauvage'
      ];
      echo '<option selected value="' . $modifyFoodName->food_breeding . '">' . $foodBreading[$modifyFoodName->food_breeding - 1] . '</option>';
   }

   echo '<option value="1">Élevage</option>
         <option value="2">Sauvage</option>
      </select>
      <select class="form-select" name="modify_food_allergic">';

   if (isset($_GET['allergic'])) {
      $reqModifyAllergicName = $pdo->prepare('SELECT * FROM allergic WHERE allergic_id = ?');
      $reqModifyAllergicName->execute([$_GET['allergic']]);
      $modifyAllergicName = $reqModifyAllergicName->fetch();

      echo '<option value="' . $modifyAllergicName->allergic_id . '" selected>' . $modifyAllergicName->allergic_name . '</option>';
      echo '<option value="null" >Aucun allergènes</option>';
      foreach ($allergicListBd as $allergic) {
         echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
      }
   } else {
      echo '<option value="null" selected>Aucun allergènes</option>';
      foreach ($allergicListBd as $allergic) {
         echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
      }
   }

   echo ' </select>
      <input class="btn mb-2" style="background-color: #242423 ;color:white;" type="submit" id="close_post_modify_food" value="Annuler">
      <input class="btn" style="background-color: #242423 ;color:white;" type="submit" name="modify_food" value="Modifier">
   </form>';
}
