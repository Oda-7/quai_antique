<?php

include './sys/dishes/db_dishes.php';
include './sys/allergic/db_allergic.php';


// Ajout de plat et d'aliments
if (isset($_POST['validate_add_dishes'])) {
   $reqContainDishes = $pdo->prepare('SELECT * FROM dishes WHERE dishes_name = ?');
   $reqContainDishes->execute([ucfirst($_POST['dishes_name'])]);
   $containDishes = $reqContainDishes->fetch();
   if ($containDishes) {
      $errors['contain_dishes'] = "Le plat existe déja";
   } else {
      if (empty($_POST['dishes_name']) || empty($_POST['dishes_food']) || $_POST['select_categorie'] == 'null') {
         $errors['form_dishes'] = "Un des champs n'est pas remplie";
      } else {
         $formDishes = [
            ucfirst($_POST['dishes_name']),
            ucfirst($_POST['dishes_description']),
            $_POST['select_categorie']
         ];

         $arrayFood = explode(",", trim($_POST['dishes_food']));
         $newListFood = [];
         $arrayIdFood = [];


         echo '<form method="post" class="d-flex flex-wrap flex-column align-items-center py-2 gap-2">';
         foreach ($arrayFood as $i => $food) {
            $reqFood = $pdo->prepare('SELECT * FROM food WHERE food_name = ? ');
            $food = trim(ucfirst($food));
            $reqFood->execute([$food]);
            $foodContain = $reqFood->fetch();

            if ($food) {
               array_push($newListFood, $food);
            }

            if (!$foodContain) {
               $reqInsertFood = $pdo->prepare('INSERT INTO food SET food_name = ?')->execute([ucfirst($food)]);
               array_push($arrayIdFood, $pdo->lastInsertId());
               echo '<input type="hidden" name="food_id[]" value="' . $arrayIdFood[$i] . '">
               <label>' . $food . '</label>
               <select class="form-select" name="allergic_select[]">
                  <option value="null">Allergènes</option>  ';
               foreach ($allergicListBd as $allergic) {
                  echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
               }
               echo '</select>
               <input class="form-control" type="text" name="add_food_origin[]" placeholder="Traçabilités des produits">
               <select class="form-select" name="add_food_breeding[]">
                  <option value="null" selected>Condition de vie</option>
                  <option value="1">Élevage</option>
                  <option value="2">Sauvage</option>
               </select>';
               $buttonAdd = true;
            } else {
               array_push($arrayIdFood, $foodContain->food_id);
            }
         }
         if (isset($buttonAdd) && $buttonAdd) {
            echo '<input class="btn" style="background-color: #242423 ;color: #e8eddf;" class="form-control" name="add_food_allergic" type="submit" value="Valider">';
         } else {
            // header('location: panel.php', true);
            $urlLogin = "panel.php";
            echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
         }
         echo '</form>';

         $implodeFood = implode(', ', $newListFood);

         if (!$formDishes[1]) {
            $reqInsertDishes = $pdo->prepare('INSERT INTO dishes SET dishes_name = ?, dishes_description = ?, dishes_food = ?, sub_categorie_id = ?');
            $reqInsertDishes->execute([ucfirst($formDishes[0]), $formDishes[1], ucfirst($implodeFood), $formDishes[2]]);
         } else {
            $reqInsertDishes = $pdo->prepare('INSERT INTO dishes SET dishes_name = ?, dishes_food = ?, sub_categorie_id = ?');
            $reqInsertDishes->execute([ucfirst($formDishes[0]), ucfirst($implodeFood), $formDishes[2]]);
         }
         $idDishes = $pdo->lastInsertId();

         foreach ($arrayIdFood as $idFood) {
            $reqInsertHaveFood = $pdo->prepare("INSERT INTO have_food SET food_id = ?, dishes_id = ?");
            $reqInsertHaveFood->execute([$idFood, $idDishes]);
         }
      }
   }
}

if (isset($_POST['add_food_allergic'])) {
   foreach ($_POST['food_id'] as $i => $idAddFood) {
      $postOrigin = ucfirst($_POST["add_food_origin"][$i]);
      if (!empty($_POST["add_food_origin"][$i]) && $_POST["add_food_breeding"][$i] != 'null') {
         $reqUpdateFood = $pdo->prepare('UPDATE food SET food_origin = ?, food_breeding = ? WHERE food_id = ?');
         $reqUpdateFood->execute([$postOrigin, $_POST["add_food_breeding"][$i], $idAddFood]);
      } elseif (!empty($_POST["add_food_origin"][$i])) {
         $reqUpdateFood = $pdo->prepare('UPDATE food SET food_origin = ? WHERE food_id = ?');
         $reqUpdateFood->execute([$postOrigin, $idAddFood]);

         //test de ce matin concluant testé a nouveau sur un autre plat
      }
   }

   $reqErrorFood = $pdo->prepare('SELECT * FROM food WHERE food_id = ?');
   foreach ($_POST['allergic_select'] as $i => $idAllergic) {
      if ($idAllergic == 'null') {
         $reqErrorFood->execute([$_POST['food_id'][$i]]);
         $errorFood = $reqErrorFood->fetch();
         $_SESSION['flash']['danger'] .= "Vous n'avez pas ajouté d'allergène pour l'aliment " . $errorFood->food_name . '<br>';
      } else {
         $reqInsertFoodAllergic = $pdo->prepare('INSERT INTO food_allergic SET food_id = ?, allergic_id = ?');
         $reqInsertFoodAllergic->execute([$_POST['food_id'][$i], $idAllergic]);
      }
   }
}

if (isset($_POST['modify_dishes'])) {
   $reqVerifyDishes = $pdo->prepare('SELECT * FROM dishes WHERE dishes_id = ?');
   $reqVerifyDishes->execute([$_GET['dishes']]);
   $verifyDishes = $reqVerifyDishes->fetch();
   if (
      $verifyDishes->dishes_name != $_POST['modify_dishes_name']
      || $verifyDishes->dishes_description != $_POST['modify_dishes_description']
      || $verifyDishes->dishes_food != $_POST['modify_dishes_food']
      || $verifyDishes->sub_categorie_id != $_POST['modify_sub_categorie_dishes']
   ) {
      $listExplodeVerifyDishesFood = explode(',', $verifyDishes->dishes_food);
      $listExplodePostDishesFood = explode(',', $_POST['modify_dishes_food']);
      $reqCheckFood = $pdo->prepare('SELECT * FROM food WHERE food_name = ?');
      $reqVerifyHaveFood = $pdo->prepare('SELECT * FROM have_food WHERE food_id = ?');

      echo '<form method="post" class="d-flex flex-wrap flex-column align-items-center py-2 gap-2">';
      foreach ($listExplodePostDishesFood as $i => $postDishesFood) {
         $postDishesFood = ucfirst(trim($postDishesFood));
         $listVerifyDishesFood = ucfirst(trim($listExplodeVerifyDishesFood[$i]));

         $reqCheckFood->execute([$postDishesFood]);
         $checkFoodDishes = $reqCheckFood->fetch();
         if ($postDishesFood != $listVerifyDishesFood) {
            // Si la liste des aliment du plat et differente de la liste du formulaire
            if (!$checkFoodDishes) {
               // insertion d'aliment s'il n'existe pas
               $reqInsertFood = $pdo->prepare('INSERT INTO food SET food_name = ?');
               $reqInsertFood->execute([ucfirst($postDishesFood)]);
               $foodId = $pdo->lastInsertId();

               echo '<input type="hidden" name="food_id_add_modify[]" value="' . $foodId . '">
                  <label>' . ucfirst(trim($postDishesFood)) . '</label>
                  <select class="form-select" name="allergic_select_modify[]">
                     <option value="null">Allergènes</option>  ';
               foreach ($allergicListBd as $allergic) {
                  echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
               }
               echo '</select>
               <input class="form-control" type="text" name="modify_add_food_origin[]" placeholder="Traçabilités des produits">
               <select class="form-select" name="modify_add_food_breeding[]">
                  <option value="null" selected>Condition de vie</option>
                  <option value="1">Élevage</option>
                  <option value="2">Sauvage</option>
               </select>';

               $reqVerifyHaveFood->execute([$foodId]);
               $verifyHaveFood = $reqVerifyHaveFood->fetch();
               if (!$verifyHaveFood) {
                  $reqInsertHaveFood = $pdo->prepare("INSERT INTO have_food SET food_id = ?, dishes_id = ?");
                  $reqInsertHaveFood->execute([$foodId, $_GET['dishes']]);
               }
            }
         }
      }
      echo '<input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="validate_add_food_allergic" value="Valider">
      </form>';

      $reqUpdateDishes = $pdo->prepare('UPDATE dishes SET dishes_name = ?, dishes_description = ?, dishes_food = ?, sub_categorie_id = ? WHERE dishes_id = ?');
      $reqUpdateDishes->execute([ucfirst($_POST['modify_dishes_name']), $_POST['modify_dishes_description'], $_POST['modify_dishes_food'], $_POST['modify_sub_categorie_dishes'], $_GET['dishes']]);

      if (count($listExplodePostDishesFood) < count($listExplodeVerifyDishesFood)) {
         foreach ($listExplodeVerifyDishesFood as $i => $explodeDishesFood) {
            $explodeDishesFood = ucfirst(trim($explodeDishesFood));
            $listExplodePostDishesFood[$i] = ucfirst(trim($listExplodePostDishesFood[$i]));
            $reqCheckFood->execute([$explodeDishesFood]);
            $checkFoodDishes = $reqCheckFood->fetch();

            $reqVerifyHaveFood->execute([$checkFoodDishes->food_id]);
            $verifyHaveFood = $reqVerifyHaveFood->fetch();

            if ($explodeDishesFood != $listExplodePostDishesFood[$i]) {
               $reqDeleteHaveFood = $pdo->prepare('DELETE FROM have_food WHERE food_id = ?');
               $reqDeleteHaveFood->execute([$verifyHaveFood->food_id]);
            }
         }
      }
   } else {
      $errors['dont_modify_dishes'] = "Vous n'avez pas modifier le plat";
   }
   $urlLogin = "panel.php";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
}

// testé si on ajoute pas d'allergènes a l'aliment $errors

if (isset($_POST['validate_add_food_allergic'])) {
   foreach ($_POST['food_id_add_modify'] as $i => $idModifyFood) {
      if (!empty($_POST['modify_add_food_origin'][$i]) && $_POST['modify_add_food_breeding'][$i] != 'null') {
         $reqUpdateFoodDishes = $pdo->prepare('UPDATE food SET food_origin = ?, food_breeding = ? WHERE food_id = ?');
         $reqUpdateFoodDishes->execute([ucfirst($_POST["modify_add_food_origin"][$i]), $_POST["modify_add_food_breeding"][$i], $idModifyFood]);
      } elseif (!empty($_POST['modify_add_food_origin'][$i])) {
         $reqUpdateFoodDishes = $pdo->prepare('UPDATE food SET food_origin = ? WHERE food_id = ?');
         $reqUpdateFoodDishes->execute([ucfirst($_POST["modify_add_food_origin"][$i]), $idModifyFood]);
      }

      if ($_POST['allergic_select_modify'] != 'null') {
         $reqInsertHaveFoodModify = $pdo->prepare('INSERT INTO food_allergic SET food_id = ?, allergic_id = ?');
         $reqInsertHaveFoodModify->execute([$idModifyFood, $_POST["allergic_select_modify"][$i]]);
      } else {
         $reqErrorFood->execute([$_POST["allergic_select_modify"]]);
         $errorFood = $reqErrorFood->fetch();
         // $errors['food_add_allergic'] = "Vous n'avez pas sélectionné d'allergènes pour " . $errorFood->food_name;
      }
   }

   // header('location: panel.php');
   $urlLogin = "panel.php";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
}

if (isset($_POST['delete_dishes'])) {
   if (!empty($_POST['checkbox_delete_dishes'])) {
      foreach ($_POST['checkbox_delete_dishes'] as $deleteDishesId) {
         $reqVerifyHaveMenu = $pdo->prepare('SELECT * FROM have_menu WHERE dishes_id = ?');
         $reqVerifyHaveMenu->execute([$deleteDishesId]);
         $verifyHaveMenu = $reqVerifyHaveMenu->fetch();

         $reqSelectNameMenu = $pdo->prepare('SELECT * FROM menu WHERE menu_id = ?');
         $reqSelectNameMenu->execute([$verifyHaveMenu->menu_id]);
         $selectNameMenu = $reqSelectNameMenu->fetch();

         $reqNameDeleteDishes = $pdo->prepare('SELECT * FROM dishes WHERE dishes_id = ?');
         $reqNameDeleteDishes->execute([$deleteDishesId]);
         $nameDeleteDishes = $reqNameDeleteDishes->fetch();

         if (!$verifyHaveMenu) {
            $reqDeleteHaveFood = $pdo->prepare("DELETE FROM have_food WHERE dishes_id = ?");
            $reqDeleteHaveFood->execute([$deleteDishesId]);

            $reqDeleteDishes = $pdo->prepare('DELETE FROM dishes WHERE dishes_id = ?')->execute([$deleteDishesId]);
            $errors['delete_dishes'] .= 'Le plat "' . ucfirst($nameDeleteDishes->dishes_name) . '" est supprimé<br>';
         } else {
            $errors['no_delete_have_menu'] = 'Le plat "' . ucfirst($nameDeleteDishes->dishes_name) . '" appartient au menu "' . ucfirst($selectNameMenu->menu_title) . '"';
         }
      }
   } else {
      $errors['dishes_delete'] = "Vous n'avez pas selectionné de plat a supprimé";
   }

   if (!isset($errors['no_delete_have_menu'])) {
      // header('location: panel.php');
      $urlLogin = "panel.php";
      echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
   }
}
