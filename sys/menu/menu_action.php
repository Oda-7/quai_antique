<?php
include './sys/food/db_categorie.php';

if (isset($_POST["validate_add_menu"])) {
   $reqVerifyTitleMenu = $pdo->prepare("SELECT * FROM menu WHERE menu_title = ?");
   $reqVerifyTitleMenu->execute([$_POST['menu_title']]);
   $verifyTitleMenu = $reqVerifyTitleMenu->fetch();
   if (!$verifyTitleMenu) {
      if (empty($_POST['menu_title']) || empty($_POST['menu_price'])) {
         $errors['form_menu'] = "Un champ du formulaire du menu n'est pas rempli (titre, prix)";
      } else {
         $reqInsertMenu = $pdo->prepare('INSERT INTO menu SET menu_title = ?, menu_price = ?, menu_description = ?');
         $reqInsertHaveMenu = $pdo->prepare('INSERT INTO have_menu SET menu_id = ?, dishes_id = ?, menu_categorie = ?');

         if (
            $_POST['menu_entrées'] != 'null'
            && $_POST['menu_plats'] != 'null'
            && $_POST['menu_fromages'] != 'null'
            && $_POST['menu_desserts'] != 'null'
         ) {
            echo 'insertion entrée plat dessert et fromage';
            $reqInsertMenu->execute([$_POST['menu_title'], $_POST['menu_price'], $_POST['menu_description']]);
            $idMenu = $pdo->lastInsertId();

            foreach ($categorieList as $post => $categorie) {
               if (preg_match("/\s/", $categorie->categorie_name)) {
                  $categorieMenu =  $categorie->categorie_name;
                  $categorieMenu = str_replace(' ', '_', $categorieMenu);
               } else {
                  $categorieMenu = $categorie->categorie_name;
               }

               if ($_POST['menu_' . strtolower($categorieMenu)] != 'null') {
                  $reqInsertHaveMenu->execute([$idMenu, $_POST['menu_' . strtolower($categorieMenu)], $_POST["menu_categorie_id"][$post]]);
               }
            }
         } elseif (
            $_POST['menu_plats'] != 'null'
            && $_POST['menu_fromages'] != 'null'
            && $_POST['menu_desserts'] != 'null'
         ) {
            echo 'insertion plat dessert et fromage';
            $reqInsertMenu->execute([$_POST['menu_title'], $_POST['menu_price'], $_POST['menu_description']]);
            $idMenu = $pdo->lastInsertId();

            foreach ($categorieList as $post => $categorie) {
               if (preg_match("/\s/", $categorie->categorie_name)) {
                  $categorieMenu =  $categorie->categorie_name;
                  $categorieMenu = str_replace(' ', '_', $categorieMenu);
               } else {
                  $categorieMenu = $categorie->categorie_name;
               }

               if ($_POST['menu_' . strtolower($categorieMenu)] != 'null') {
                  $reqInsertHaveMenu->execute([$idMenu, $_POST['menu_' . strtolower($categorieMenu)], $_POST["menu_categorie_id"][$post]]);
               }
            }
         } elseif ($_POST['menu_entrées'] != 'null' && $_POST['menu_plats'] != 'null') {
            echo 'insertion entrée plat';
            $reqInsertMenu->execute([$_POST['menu_title'], $_POST['menu_price'], $_POST['menu_description']]);
            $idMenu = $pdo->lastInsertId();

            foreach ($categorieList as $post => $categorie) {
               if (preg_match("/\s/", $categorie->categorie_name)) {
                  $categorieMenu =  $categorie->categorie_name;
                  $categorieMenu = str_replace(' ', '_', $categorieMenu);
               } else {
                  $categorieMenu = $categorie->categorie_name;
               }

               if ($_POST['menu_' . strtolower($categorieMenu)] != 'null') {
                  $reqInsertHaveMenu->execute([$idMenu, $_POST['menu_' . strtolower($categorieMenu)], $_POST["menu_categorie_id"][$post]]);
               }
            }
         } else {
            $errors['dishes_no_select'] = "Vous n'avez pas choisis les plats nécessaire pour un menu<br>
            (Entrée - Plat) <br>
            (Plat - Dessert - Fromage) <br>
            (Entrée - Plat - Dessert - Fromage)
            ";
         }
         // header('location: panel.php');
         $urlLogin = "panel.php";
         echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
      }
   } else {
      $errors['menu'] = "Le menu existe déja";
   }
}

// delete Menu
$reqSelectMenuDelete = $pdo->prepare('SELECT * FROM menu WHERE menu_id = ?');
if (isset($_POST['delete_menu'])) {
   echo "Voulez vous supprimer les menu :
   <form method='post' class='d-flex flex-wrap flex-column align-items-center py-2 gap-2'>";
   foreach ($_POST["checkbox_delete_menu"] as $post => $postIdMenuDelete) {
      echo '<input name="id_menu_delete[]" type="hidden" value="' . $postIdMenuDelete . '"> ';
      $reqSelectMenuDelete->execute([$postIdMenuDelete]);
      $selectMenuDelete = $reqSelectMenuDelete->fetch();
      echo $selectMenuDelete->menu_title . "<br>";
   }
   echo '<input class="btn" style="background-color: #242423 ;color: #e8eddf;" name="validate_delete_menu" type="submit" value="valider">
   </form>';
}

if (isset($_POST['validate_delete_menu'])) {
   $errors['text_delete_menu'] = "Vous venez de supprimer le(s) menu(s) : ";

   $reqFetchHaveDishesMenuDay = $pdo->prepare('SELECT * FROM have_menu WHERE menu_id = ?');

   $reqDeleteHaveMenu = $pdo->prepare('DELETE FROM have_menu WHERE menu_id = ?');
   $reqSelectMenuDay = $pdo->prepare('SELECT * FROM menu WHERE menu_id = ?');

   $reqDeleteMenu = $pdo->prepare('DELETE FROM menu WHERE menu_id = ?');
   foreach ($_POST['id_menu_delete'] as $deleteMenu) {
      $reqSelectMenuDelete->execute([$deleteMenu]);
      $selectMenuDelete = $reqSelectMenuDelete->fetch();

      $reqFetchHaveDishesMenuDay->execute([$deleteMenu]);
      $reqDeleteHaveMenu->execute([$deleteMenu]);
      $reqDeleteMenu->execute([$deleteMenu]);

      if ($selectMenuDelete->menu_end_date) {
         $fetchHaveDishesMenuDay = $reqFetchHaveDishesMenuDay->fetchAll();
         $reqDeleteDishesMenuDay = $pdo->prepare('DELETE FROM dishes WHERE dishes_id = ?');
         $reqDeleteHaveFood = $pdo->prepare('DELETE FROM have_food WHERE dishes_id = ?');

         // delete
         foreach ($fetchHaveDishesMenuDay as $fetch) {
            if ($fetch->menu_categorie != 6) {
               $reqDeleteHaveFood->execute([$fetch->dishes_id]);
               $reqDeleteDishesMenuDay->execute([$fetch->dishes_id]);
            }
         }
         // header('location: panel.php');
         $urlLogin = "panel.php";
         echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
      }
      $errors['delete_menu_name'] .= $selectMenuDelete->menu_title . '<br>';
   }
}

// validation menu du jour
if (isset($_POST['validate_menu_day'])) {
   if (!empty($_POST['menu_day_price'])) {
      if (
         !empty($_POST['menu_day_plats'])
         && !empty($_POST['menu_day_food_plats'])
         && !empty($_POST['menu_day_desserts'])
         && !empty($_POST['menu_day_food_desserts'])
      ) {
         //insertion si les champs (plat et aliment) des categorie du menu du jour, ne sont pas vide
         $dateEndMenu = new \DateTimeImmutable("+2 day");

         $reqInsertMenuDay = $pdo->prepare('INSERT INTO menu SET menu_title = ?, menu_price = ?, menu_description = ?, menu_end_date = ?');
         $reqInsertMenuDay->execute([$_POST['menu_day_title'], $_POST['menu_day_price'], $_POST['menu_day_description'], $dateEndMenu->format("Y-m-d H:i:s")]);
         // récuperation id menu
         $idMenuInsert = $pdo->lastInsertId();
         $nameMenuDayDishe = array();
         $foodMenuDayDishe = array();

         foreach ($categorieList as $key => $categorie) {
            if (preg_match("/\s/", $categorie->categorie_name)) {
               $categorieMenuDay =  $categorie->categorie_name;
               $categorieMenuDay = str_replace(' ', '_', $categorieMenuDay);
            } else {
               $categorieMenuDay = $categorie->categorie_name;
            }

            if ($categorieMenuDay != "Vins") {
               if (!empty($_POST['menu_day_' . strtolower($categorieMenuDay)]) && !empty($_POST['menu_day_food_' . strtolower($categorieMenuDay)])) {
                  array_push($nameMenuDayDishe, [$categorie->categorie_id, strtolower($_POST['menu_day_' . strtolower($categorieMenuDay)])]);
                  $foodMenuDay = trim($_POST['menu_day_food_' . strtolower($categorieMenuDay)]);
                  $foodMenuDayExplode = explode(',', $foodMenuDay);
                  array_map('trim', $foodMenuDayExplode);
                  $goodMenuExplode = [];

                  foreach ($foodMenuDayExplode as $foodMenuExplode) {
                     // trie si l'aliment et vide 
                     if ($foodMenuExplode) {
                        array_push($goodMenuExplode, trim($foodMenuExplode));
                     }
                  }
                  $foodMenuDayDishe[$_POST['menu_day_subcategorie_' . strtolower($categorie->categorie_name)]] = $goodMenuExplode;
               }
            }
         }

         $countGoodInsertDishe = 0;
         echo '<form method="post" class="d-flex flex-wrap flex-column align-items-center py-2 gap-2">';
         foreach ($foodMenuDayDishe as $k => $foodDishe) {
            // insérer les plats , liaison plat et menu
            $reqVerifyDishesMenuDay = $pdo->prepare('SELECT * FROM dishes WHERE dishes_name = ?');
            $reqVerifyDishesMenuDay->execute([$nameMenuDayDishe[$countGoodInsertDishe][1]]);
            $verifyDishesMenuDay = $reqVerifyDishesMenuDay->fetch();

            $foodStringMenuDayCategorie = implode(', ', $foodDishe);
            if (!$verifyDishesMenuDay) {
               $reqInsertDishesMenuDay = $pdo->prepare('INSERT INTO dishes SET dishes_name = ?, dishes_food = ?, sub_categorie_id = ?, dishes_temp = ?');
               $reqInsertDishesMenuDay->execute([ucfirst($nameMenuDayDishe[$countGoodInsertDishe][1]), $foodStringMenuDayCategorie, $k, $dateEndMenu->format("Y-m-d H:i:s")]);
               // récupération ip dishes
               $idDishesInsert = $pdo->lastInsertId();
            } else {
               $idDishesInsert = $verifyDishesMenuDay->dishes_id;
            }

            $reqInsertHaveMenuDay = $pdo->prepare('INSERT INTO have_menu SET menu_id = ?, dishes_id = ?, menu_categorie = ?');
            $reqInsertHaveMenuDay->execute([$idMenuInsert, $idDishesInsert, $nameMenuDayDishe[$countGoodInsertDishe][0]]);

            $reqSelectCategorieMenuDay = $pdo->prepare('SELECT * FROM sub_categorie WHERE sub_categorie_id = ?');
            $reqSelectCategorieMenuDay->execute([$k]);
            $selectCategorieMenuDay = $reqSelectCategorieMenuDay->fetch();
            $reqVerifyFoodMenuDay = $pdo->prepare('SELECT * FROM food WHERE food_name = ?');

            // Ajout de formulaire par aliment s'il n'existe pas et pour relier les allergènes
            // verification d'aliment pour inséré des allergènes et les relier au plats
            foreach ($foodDishe as $food) {
               $reqVerifyFoodMenuDay->execute([ucfirst($food)]);
               $verifyFoodMenuDay = $reqVerifyFoodMenuDay->fetch();

               if (!$verifyFoodMenuDay) {
                  echo '<label>' . $food . '</label>
                  <input type="hidden" name="name_food_menu_day[]" value="' . $food . '">';
                  echo '<input  type="hidden" name="name_food_menu_id[]" value="' . $idDishesInsert . '">';
                  echo '<select class="form-select" name="allergic_select_menu_day[]">
                     <option value="null">Allergènes</option>';
                  foreach ($allergicListBd as $allergic) {
                     echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
                  }
                  echo '</select>
                  <input class="form-control" type="text" name="add_food_origin_menu_day[]" placeholder="Traçabilités des produits">
                  <select class="form-select" name="add_food_breeding_menu_day[]">
                     <option value="null" selected>Condition de vie</option>
                     <option value="1">Élevage</option>
                     <option value="2">Sauvage</option>
                  </select><br>';
               } else {
                  // insertion de la liaison des aliments existant avec le plat
                  $reqInsertHaveFoodMenuDay = $pdo->prepare('INSERT INTO have_food SET food_id = ?, dishes_id = ?');
                  $reqInsertHaveFoodMenuDay->execute([$verifyFoodMenuDay->food_id, $idDishesInsert]);
               }
            }
            $countGoodInsertDishe++;
         }

         if (!$verifyFoodMenuDay) {
            // si les aliments n'existe pas et que l'on doit ajouter des allergènes
            echo '<input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="validate_allergic_food_menu_day" value="Ajouter">';
         }
         echo '</form>';
         if (!empty($_POST['menu_day_vins'])) {
            if ($_POST['menu_day_vins'] != 'null') {
               $reqInsertVinHaveMenu = $pdo->prepare('INSERT INTO have_menu SET menu_id = ?, dishes_id = ?, menu_categorie = ?');
               $reqInsertVinHaveMenu->execute([$idMenuInsert, $_POST['menu_day_vins'], 6]);
               // insertion du vin 
            }
         }
      } else {
         $errors['errors_menu_day'] = "Il faut choisir au minimum un Plat et un Dessert";
      }
   }
}


if (isset($_POST['validate_allergic_food_menu_day'])) {
   // inseré l'aliment 
   $reqInsertHaveFoodMenuDay = $pdo->prepare('INSERT INTO have_food SET food_id = ?, dishes_id = ?');

   foreach ($_POST['name_food_menu_day'] as $f => $foodName) {
      $reqVerifyFood = $pdo->prepare('SELECT * FROM food WHERE food_name = ?');
      $reqVerifyFood->execute([ucfirst($foodName)]);
      $verifyFood = $reqVerifyFood->fetch();

      if (!$verifyFood) {
         if (!empty($_POST["add_food_origin_menu_day"][$f]) && !empty($_POST["add_food_breeding_menu_day"][$f])) {
            $reqInsertFoodMenuDayAllDetails = $pdo->prepare('INSERT INTO food SET food_name = ?, food_origin = ?, food_breeding = ?');
            $reqInsertFoodMenuDayAllDetails->execute([ucfirst($foodName), $_POST['add_food_origin_menu_day'][$f], $_POST['add_food_breeding_menu_day'][$f]]);
         } elseif (!empty($_POST["add_food_origin_menu_day"][$f])) {
            $reqInsertFoodMenuDayNoBreeding = $pdo->prepare('INSERT INTO food SET food_name = ?, food_origin = ?');
            $reqInsertFoodMenuDayNoBreeding->execute([ucfirst($foodName), $_POST['add_food_origin_menu_day'][$f]]);
         } else {
            $reqInsertFoodMenuDay = $pdo->prepare('INSERT INTO food SET food_name = ?');
            $reqInsertFoodMenuDay->execute([ucfirst($foodName)]);
         }

         $idFood = $pdo->lastInsertId();
         if ($_POST['allergic_select_menu_day'][$f] != 'null') {
            $reqInsertFoodAllergic = $pdo->prepare('INSERT INTO food_allergic SET food_id = ?, allergic_id = ?');
            $reqInsertFoodAllergic->execute([$idFood, $_POST['allergic_select_menu_day'][$f]]);
         }
         $reqInsertHaveFoodMenuDay->execute([$idFood, $_POST['name_food_menu_id'][$f]]);
      }
   }
   // Testé tout les insertion ce soir et vérifier 
}

if (isset($_POST["validate_modify_menu"])) {
   // modify menu
   if (!empty($_POST['menu_modify_title']) && !empty($_POST['menu_modify_price'])) {
      $reqUpdateMenuModify = $pdo->prepare('UPDATE menu SET menu_title = ?, menu_price = ?, menu_description = ? WHERE menu_id = ?');
      $reqUpdateMenuModify->execute([$_POST['menu_modify_title'], $_POST['menu_modify_price'], $_POST['menu_modify_description'], $_GET['menu']]);
   } else {
      $errors['menu_modify'] = "Vous devez forcément donner un nom au menu et un prix";
      exit();
   }

   if (
      $_POST['select_menu_modify_entrées'] != 'null' && $_POST['select_menu_modify_plats'] != 'null'
      || $_POST['select_menu_modify_plats'] != 'null' && $_POST['select_menu_modify_desserts'] != 'null'
   ) {
      // si l'entrée et le plat sont différents de null ou que le plat et le dessert sont different de null
      foreach ($categorieList as $categorie) {
         if (preg_match("/\s/", $categorie->categorie_name)) {
            $categorieModifyMenu =  $categorie->categorie_name;
            $categorieModifyMenu = strtolower(str_replace(' ', '_', $categorieModifyMenu));
         } else {
            $categorieModifyMenu = strtolower($categorie->categorie_name);
         }

         $reqVerifyDishesHaveMenu = $pdo->prepare('SELECT * FROM have_menu WHERE menu_id = ? AND menu_categorie = ?');
         $reqVerifyDishesHaveMenu->execute([$_GET['menu'], $_POST['menu_modify_' . $categorieModifyMenu . '_id']]);
         $verifyDishesHaveMenu = $reqVerifyDishesHaveMenu->fetch();

         if ($_POST['select_menu_modify_' . $categorieModifyMenu] != 'null') {
            // Update have menu
            if (!$verifyDishesHaveMenu) {
               $reqInsertModifyHaveMenu = $pdo->prepare('INSERT INTO have_menu SET menu_id = ?, dishes_id = ?, menu_categorie = ?');
               $reqInsertModifyHaveMenu->execute([$_GET['menu'], $_POST['select_menu_modify_' . $categorieModifyMenu], $_POST['menu_modify_' . $categorieModifyMenu . '_id']]);
            } else {
               $reqModifyHaveMenu = $pdo->prepare('UPDATE have_menu SET dishes_id = ? WHERE menu_id = ? AND menu_categorie = ?');
               $reqModifyHaveMenu->execute([$_POST['select_menu_modify_' . $categorieModifyMenu], $_GET['menu'], $_POST['menu_modify_' . $categorieModifyMenu . '_id']]);
            }
         } elseif ($_POST['select_menu_modify_' . $categorieModifyMenu] == 'null' && $verifyDishesHaveMenu) {
            $reqDeleteModifyHaveMenu = $pdo->prepare('DELETE FROM have_menu WHERE menu_id = ? AND menu_categorie = ?');
            $reqDeleteModifyHaveMenu->execute([$_GET['menu'], $_POST['menu_modify_' . $categorieModifyMenu . '_id']]);
         }
      }
      if (isset($_POST['validate_modify_menu'])) {
         // header('location: panel.php', true);
         $urlLogin = "panel.php";
         echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
      }
   } else {
      $errors['modify_dishes'] = "Il faut au minimum :<br>
         (Entrée - Plats) <br>
         (Plat - Dessert)";
   }
}

if (isset($_POST['validate_modify_menu_day'])) {
   // modify menu day
   $reqSelectMenuDetails = $pdo->prepare('SELECT * FROM menu WHERE menu_id = ?');
   $reqSelectMenuDetails->execute([$_GET['menu']]);
   $selectMenuDetails = $reqSelectMenuDetails->fetch();

   if (!empty($_POST['menu_day_modify_price'])) {
      $reqUpdateMenuDayModify = $pdo->prepare('UPDATE menu SET menu_title = ?, menu_price = ? WHERE menu_id = ?');
      $reqUpdateMenuDayModify->execute([$_POST['menu_day_modify_title'], $_POST['menu_day_modify_price'], $_GET['menu']]);
   } elseif (!empty($_POST['menu_day_modify_price']) && !empty($_POST['menu_day_modify_description'])) {
      $reqUpdateMenuDayModify = $pdo->prepare('UPDATE menu SET menu_title = ?, menu_price = ?, menu_description = ? WHERE menu_id = ?');
      $reqUpdateMenuDayModify->execute([$_POST['menu_day_modify_title'], $_POST['menu_day_modify_price'], $_POST['menu_day_modify_description'], $_GET['menu']]);
   }

   if (
      !empty($_POST['menu_day_modify_entrées']) && !empty($_POST['menu_day_modify_plats'])
      || !empty($_POST['menu_day_modify_plats']) && !empty($_POST['menu_day_modify_desserts'])
   ) {
      $arrayModifyInsertDishes = array();
      $arrayModifyInsertFoodDishes = array();
      $arrayModifyUpdateDishes = array();
      $goodArrayModifyUpdateFood = array();
      $reqSelectDishesDetails = $pdo->prepare('SELECT * FROM dishes WHERE dishes_id = ?');
      $lastDishes = array();

      foreach ($categorieList as $categorie) {

         if (preg_match("/\s/", $categorie->categorie_name)) {
            $categorieModifyMenuDay =  $categorie->categorie_name;
            $categorieModifyMenuDay = strtolower(str_replace(' ', '_', $categorieModifyMenuDay));
         } else {
            $categorieModifyMenuDay = strtolower($categorie->categorie_name);
         }

         // verify have menu day
         $reqVerifyDishesHaveMenuDay = $pdo->prepare('SELECT * FROM have_menu WHERE menu_id = ? AND menu_categorie = ?');
         $reqVerifyDishesHaveMenuDay->execute([$_GET['menu'], $_POST['menu_day_modify_' . $categorieModifyMenuDay . '_id']]);
         $verifyDishesHaveMenuDay = $reqVerifyDishesHaveMenuDay->fetch();

         $reqUpdateDishesMenuDay = $pdo->prepare('UPDATE dishes SET dishes_name = ?, dishes_food = ?, sub_categorie_id = ? WHERE dishes_id = ?');

         if ($categorieModifyMenuDay != 'vins') {
            // si la catégorie est différente de "vins"
            if (!empty($_POST['menu_day_modify_' . $categorieModifyMenuDay]) && !empty($_POST['menu_day_modify_food_' . $categorieModifyMenuDay]) && !$verifyDishesHaveMenuDay) {
               // si les champs des plats et des aliments des plats ne sont pas vide
               // et que le menu n'a pas de plats pour cette categorie
               // alors on va vouloir inseré dans have menu

               array_push($arrayModifyInsertDishes, [$categorie->categorie_id, $_POST['menu_day_modify_' . $categorieModifyMenuDay]]);
               $modifyFoodMenuDay = trim($_POST['menu_day_modify_food_' . $categorieModifyMenuDay]);
               $modifyFoodMenuDayExplode = explode(',', $modifyFoodMenuDay);
               array_map('trim', $modifyFoodMenuDayExplode);
               $goodModifyMenuExplode = [];

               foreach ($modifyFoodMenuDayExplode as $foodMenuExplode) {
                  // trie si l'aliment est vide 

                  if ($foodMenuExplode) {
                     array_push($goodModifyMenuExplode, trim($foodMenuExplode));
                  }
               }

               array_push($arrayModifyInsertFoodDishes, [$_POST['menu_day_subcategorie_' . $categorieModifyMenuDay], $goodModifyMenuExplode]);
            } elseif (!empty($_POST['menu_day_modify_' . $categorieModifyMenuDay]) && !empty($_POST['menu_day_modify_food_' . $categorieModifyMenuDay]) && $verifyDishesHaveMenuDay) {
               // si les champs, noms du plats et les aliments du plats ne sont pas vide et  
               // qu'il y a un plat pour ce menu et pour cette catégorie du menu , alors on le modifie

               array_push($arrayModifyUpdateDishes, [$categorie->categorie_id, $_POST['menu_day_modify_' . $categorieModifyMenuDay]]);
               $arrayTrim = trim($_POST['menu_day_modify_food_' . $categorieModifyMenuDay]);
               $arrayTempUpdateFood = explode(',', $arrayTrim);
               $arrayTempUpdateFood = array_map('trim', $arrayTempUpdateFood);
               $goodArrayUpdateFood = array();

               foreach ($arrayTempUpdateFood as $foodUpdate) {
                  // trie si l'aliment est vide 
                  if ($foodUpdate) {
                     array_push($goodArrayUpdateFood, trim($foodUpdate));
                  }
               }
               $reqSelectDishesDetails->execute([$verifyDishesHaveMenuDay->dishes_id]);
               array_push($lastDishes, $reqSelectDishesDetails->fetch());
               // faire une vérification pour modifier les aliment s'il y en a en plus ou en moins
               array_push($goodArrayModifyUpdateFood, [$_POST['menu_day_subcategorie_' . $categorieModifyMenuDay], $goodArrayUpdateFood]);
               $goodInsertFoodDishes = implode(', ', $goodArrayUpdateFood);

               $reqUpdateDishesMenuDay->execute([ucfirst($_POST['menu_day_modify_' . $categorieModifyMenuDay]), $goodInsertFoodDishes, $_POST['menu_day_subcategorie_' . $categorieModifyMenuDay], $verifyDishesHaveMenuDay->dishes_id]);
            } elseif (empty($_POST['menu_day_modify_' . $categorieModifyMenuDay]) && empty($_POST['menu_day_modify_food_' . $categorieModifyMenuDay]) && $verifyDishesHaveMenuDay) {
               // suppression des plats, si les champs sont vide, que le plat existe 
               // et de la liaison des plats si le plat existant a une date 
               // pour cette categorie de menu du jour

               $reqSelectDishesEndDate = $pdo->prepare('SELECT * FROM dishes WHERE dishes_id = ?');
               $reqSelectDishesEndDate->execute([$verifyDishesHaveMenuDay->dishes_id]);
               $selectDishesEndDate = $reqSelectDishesEndDate->fetch();

               $reqDeleteModifyDishesMenuDay = $pdo->prepare('DELETE FROM dishes WHERE dishes_id = ?');
               $reqDeleteModifyHaveMenuDay = $pdo->prepare('DELETE FROM have_menu WHERE menu_id = ? AND menu_categorie = ?');
               $reqDeleteModifyHaveMenuDay->execute([$_GET['menu'], $categorie->categorie_id]);
               if ($selectDishesEndDate->dishes_temp) {
                  // si le plat et un plat temporaire , suppression 
                  $reqModifyDeleteHaveFood = $pdo->prepare('DELETE FROM have_food WHERE dishes_id = ?');
                  $reqModifyDeleteHaveFood->execute([$selectDishesEndDate->dishes_id]);
                  $reqDeleteModifyDishesMenuDay->execute([$selectDishesEndDate->dishes_id]);
               }
            }
         } else {
            if ($_POST['menu_day_modify_' . $categorieModifyMenuDay] != 'null' && !$verifyDishesHaveMenuDay) {
               // si le nom du vins n'est pas null et qu'il n'est pas lié au menu , insertion have menu
               $reqModifyInsertHaveMenuVins = $pdo->prepare('INSERT INTO have_menu SET menu_id = ?, dishes_id = ?, menu_categorie = ?');
               $reqModifyInsertHaveMenuVins->execute([$_GET['menu'], $_POST['menu_day_modify_' . $categorieModifyMenuDay], $categorie->categorie_id]);
            } elseif ($_POST['menu_day_modify_' . $categorieModifyMenuDay] != 'null' && $verifyDishesHaveMenuDay) {
               // si le nom du vins n'est pas null et qu'il est lié au menu, modification have menu
               $reqModifyUpdateHaveMenuVins = $pdo->prepare('UPDATE have_menu SET dishes_id = ? WHERE menu_id = ? AND menu_categorie = ?');
               $reqModifyUpdateHaveMenuVins->execute([$_POST['menu_day_modify_' . $categorieModifyMenuDay], $_GET['menu'], $categorie->categorie_id]);
            } elseif ($_POST['menu_day_modify_' . $categorieModifyMenuDay] == 'null' && $verifyDishesHaveMenuDay) {
               // suppression des vins si null et que le menu a un plat pour cette categorie
               $reqDeleteModifyHaveMenuVins = $pdo->prepare('DELETE FROM have_menu WHERE menu_id = ? AND menu_categorie = ? ');
               $reqDeleteModifyHaveMenuVins->execute([$_GET['menu'], $categorie->categorie_id]);
            }
         }
      }

      $reqInsertModifyHavefood = $pdo->prepare('INSERT INTO have_food SET food_id = ?, dishes_id = ?');
      $reqVerifyDishesModifyMenuDay = $pdo->prepare('SELECT * FROM dishes WHERE dishes_name = ?');
      $reqVerifyModifyFood = $pdo->prepare('SELECT * FROM food WHERE food_name = ?');
      $reqSelectModifyHaveFood = $pdo->prepare('SELECT * FROM have_food WHERE food_id = ? AND dishes_id = ?');
      $reqDeleteHaveFood = $pdo->prepare('DELETE FROM have_food WHERE food_id = ? AND dishes_id = ?');
      $reqUpdateDishesFood = $pdo->prepare('UPDATE dishes SET dishes_food = ? WHERE dishes_id = ?');
      $newFood = false;

      echo '<form method="post" d-flex flex-wrap flex-column align-items-center py-2 gap-2>';
      $arrayNewFoodDishes = array();
      foreach ($arrayModifyUpdateDishes as $d => $dishesUpdate) {

         // si le plat existe on verifie ses aliments et ceux rentré dans le formulaire
         $reqVerifyDishesModifyMenuDay->execute([ucfirst($dishesUpdate[1])]);
         $verifyDishesModifyMenuDay = $reqVerifyDishesModifyMenuDay->fetch();

         $listFoodVerifyDishes = explode(',', trim($lastDishes[$d]->dishes_food));
         $listFoodVerifyDishes = array_map('trim', $listFoodVerifyDishes);

         if (count($goodArrayModifyUpdateFood[$d][1]) > count($listFoodVerifyDishes)) {
            foreach ($goodArrayModifyUpdateFood[$d][1] as $f => $foodComparate) {
               if (strtolower($listFoodVerifyDishes[$f]) != strtolower($foodComparate)) {
                  // si l'aliment est different que celui enregistre alors on modifie le have food si l'aliment existe
                  // et on modifie la liste du plat
                  array_push($arrayNewFoodDishes, $foodComparate);
                  $reqVerifyModifyFood->execute([ucfirst($foodComparate)]);
                  $verifyModifyFood = $reqVerifyModifyFood->fetch();

                  if ($verifyModifyFood) {
                     $reqInsertModifyHavefood->execute([$verifyModifyFood->food_id, $verifyDishesModifyMenuDay->dishes_id]);
                  } elseif (!$verifyModifyFood) {
                     echo '<label>' . $foodComparate . '</label>
                  <input  type="hidden" name="name_food_modify_menu_day[]" value="' . $foodComparate . '">';
                     echo '<input type="hidden" name="dishes_id_modify[]" value="' . $verifyDishesModifyMenuDay->dishes_id . '">';
                     echo '<select class="form-select" name="allergic_select_modify_menu_day[]">
                     <option value="null">Allergènes</option>';
                     foreach ($allergicListBd as $allergic) {
                        echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
                     }
                     echo '</select>
                  <input class="form-control" type="text" name="add_food_origin_modify_menu_day[]" placeholder="Traçabilités des produits">
                  <select class="form-select" name="add_food_breeding_modify_menu_day[]">
                     <option value="null" selected>Condition de vie</option>
                     <option value="1">Élevage</option>
                     <option value="2">Sauvage</option>
                  </select>';
                     $newFood = true;
                  }
               }
            }
         } else {
            // sinon on enlève le have food sur le plats
            foreach ($listFoodVerifyDishes as $l => $foodComparateDelete) {
               $reqSelectModifyFoodDelete = $pdo->prepare('SELECT * FROM food WHERE food_name = ?');
               $reqSelectModifyFoodDelete->execute([ucfirst($foodComparateDelete)]);
               $selectModifyFoodDelete = $reqSelectModifyFoodDelete->fetch();

               if (empty($goodArrayModifyUpdateFood[$d][1][$l])) {
                  $reqDeleteHaveFood->execute([$selectModifyFoodDelete->food_id, $verifyDishesModifyMenuDay->dishes_id]);
               } elseif ($foodComparateDelete != $goodArrayModifyUpdateFood[$d][1][$l]) {
                  array_push($arrayNewFoodDishes, $goodArrayModifyUpdateFood[$d][1][$l]);
                  $reqSelectModifyFoodDelete->execute([$goodArrayModifyUpdateFood[$d][1][$l]]);
                  $selectFoodUpdate = $reqSelectModifyFoodDelete->fetch();
                  $reqSelectModifyHaveFood->execute([$selectFoodUpdate->food_id, $verifyDishesModifyMenuDay->dishes_id]);
                  $selectModifyHaveFood = $reqSelectModifyHaveFood->fetch();

                  $reqDeleteHaveFood->execute([$selectModifyFoodDelete->food_id, $verifyDishesModifyMenuDay->dishes_id]);
                  if ($selectFoodUpdate && !$selectModifyHaveFood) {
                     $reqInsertModifyHavefood->execute([$selectFoodUpdate->food_id, $verifyDishesModifyMenuDay->dishes_id]);
                  } elseif (!$selectFoodUpdate) {
                     echo '<label>' . $goodArrayModifyUpdateFood[$d][1][$l] . '</label>
                  <input type="hidden" name="name_food_modify_menu_day[]" value="' . $goodArrayModifyUpdateFood[$d][1][$l] . '">';
                     echo '<input type="hidden" name="dishes_id_modify[]" value="' . $verifyDishesModifyMenuDay->dishes_id . '">';
                     echo '<select class="form-select" name="allergic_select_modify_menu_day[]">
                     <option value="null">Allergènes</option>';
                     foreach ($allergicListBd as $allergic) {
                        echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
                     }
                     echo '</select>
                  <input class="form-control" type="text" name="add_food_origin_modify_menu_day[]" placeholder="Traçabilités des produits">
                  <select class="form-select" name="add_food_breeding_modify_menu_day[]">
                     <option value="null" selected>Condition de vie</option>
                     <option value="1">Élevage</option>
                     <option value="2">Sauvage</option>
                  </select>';
                     $newFood = true;
                  }
                  ///

               }
            }
         }
      }

      foreach ($arrayModifyInsertDishes as $m => $modify) {
         // si le plat n'existe pas insertion du plat
         $reqVerifyDishesModifyMenuDay->execute([ucfirst($arrayModifyInsertDishes[$m][1])]);
         $verifyDishesModifyMenuDay = $reqVerifyDishesModifyMenuDay->fetch();

         $modifyFoodImplode = implode(', ', $arrayModifyInsertFoodDishes[$m][1]);

         if (!$verifyDishesModifyMenuDay) {
            $reqInsertDishesModifyMenuDay = $pdo->prepare('INSERT INTO dishes SET dishes_name = ?, dishes_food = ?, sub_categorie_id = ?, dishes_temp = ?');
            $reqInsertDishesModifyMenuDay->execute([ucfirst($modify[1]), $modifyFoodImplode, $arrayModifyInsertFoodDishes[$m][0], $selectMenuDetails->menu_end_date]);
            $InsertDishesIdModifyMenuDay = $pdo->lastInsertId();
         } else {
            $InsertDishesIdModifyMenuDay = $verifyDishesModifyMenuDay->dishes_id;
         }


         if ($modifyFoodImplode > $verifyDishesModifyMenuDay->dishes_food) {
            // si la liste des aliments formulaire est plus grande que la liste d'aliments du plat existant
            foreach ($arrayModifyInsertFoodDishes[$m][1] as $food) {
               $reqVerifyModifyFood->execute([ucfirst($food)]);
               $verifyModifyFood = $reqVerifyModifyFood->fetch();

               array_push($arrayNewFoodDishes, $food);
               $reqSelectModifyHaveFood->execute([$verifyModifyFood->food_id, $verifyDishesModifyMenuDay->dishes_id]);
               $selectModifyHaveFood = $reqSelectModifyHaveFood->fetch();
               // var_dump($selectModifyHaveFood);

               if (!$verifyModifyFood) {
                  // trie des aliments s'il existe pour leur rajouter des allergènes sur l'insertion du plat
                  echo '<label>' . $food . '</label>
                  <input type="hidden" name="name_food_modify_menu_day[]" value="' . $food . '">';
                  echo '<input type="hidden" name="dishes_id_modify[]" value="' . $InsertDishesIdModifyMenuDay . '">';
                  echo '<select class="form-select" name="allergic_select_modify_menu_day[]">
                     <option value="null">Allergènes</option>';
                  foreach ($allergicListBd as $allergic) {
                     echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
                  }
                  echo '</select>
                  <input class="form-control" type="text" name="add_food_origin_modify_menu_day[]" placeholder="Traçabilités des produits">
                  <select class="form-select" name="add_food_breeding_modify_menu_day[]">
                     <option value="null" selected>Condition de vie</option>
                     <option value="1">Élevage</option>
                     <option value="2">Sauvage</option>
                  </select><br>';
                  $newFood = true;
               } elseif ($verifyModifyFood && !$selectModifyHaveFood) {
                  // update le plat s'il existe et que les aliments sont differents de ceux du formulaire
                  $reqUpdateDishesMenuDay->execute([ucfirst($modify[1]), $modifyFoodImplode, $arrayModifyInsertFoodDishes[$m][0], $InsertDishesIdModifyMenuDay]);
                  $reqInsertModifyHavefood->execute([$verifyModifyFood->food_id, $InsertDishesIdModifyMenuDay]);
               }
            }
         } else {
            $arrayFoodVerify = explode(',', trim($verifyDishesModifyMenuDay->dishes_food));
            foreach ($arrayFoodVerify as $f => $food) {
               $reqVerifyModifyFood->execute([trim(ucfirst($food))]);
               $verifyModifyFood = $reqVerifyModifyFood->fetch();

               if (empty(trim($arrayModifyInsertFoodDishes[$m][1][$f]))) {
                  // si l'aliment du formulaire est vide on supprime la liaison de l'aliment      
                  $reqDeleteHaveFood->execute([$verifyModifyFood->food_id, $verifyDishesModifyMenuDay->dishes_id]);
               } elseif ($food != trim(ucfirst($arrayModifyInsertFoodDishes[$m][1][$f]))) {
                  // si l'aliment est différent de la liste on le supprime
                  // et on le remplace par le nouveau
                  // récuperé l'id de l'aliment pour le supprimé 
                  array_push($arrayNewFoodDishes, $arrayModifyInsertFoodDishes[$m][1][$f]);
                  $reqDeleteHaveFood->execute([$verifyModifyFood->food_id, $verifyDishesModifyMenuDay->dishes_id]);

                  $reqVerifyModifyFood->execute([trim(ucfirst($arrayModifyInsertFoodDishes[$m][1][$f]))]);
                  $selectNewFood = $reqVerifyModifyFood->fetch();
                  $reqSelectModifyHaveFood->execute([$selectNewFood->food_id, $verifyDishesModifyMenuDay->dishes_id]);
                  $selectModifyHaveFood = $reqSelectModifyHaveFood->fetch();
                  ///
                  if ($selectNewFood && !$selectModifyHaveFood) {
                     // si l'aliment existe qu'il n'est pas lié au plat
                     $reqInsertModifyHavefood->execute([$selectNewFood->food_id, $verifyDishesModifyMenuDay->dishes_id]);
                  } elseif (!$selectNewFood) {
                     echo '<label>' . $arrayModifyInsertFoodDishes[$m][1][$f] . '</label>
                  <input type="hidden" name="name_food_modify_menu_day[]" value="' . $arrayModifyInsertFoodDishes[$m][1][$f] . '">';
                     echo '<input type="hidden" name="dishes_id_modify[]" value="' . $InsertDishesIdModifyMenuDay . '">';
                     echo '<select class="form-select" name="allergic_select_modify_menu_day[]">
                     <option value="null">Allergènes</option>';
                     foreach ($allergicListBd as $allergic) {
                        echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
                     }
                     echo '</select>
                  <input class="form-control" type="text" name="add_food_origin_modify_menu_day[]" placeholder="Traçabilités des produits">
                  <select class="form-select" name="add_food_breeding_modify_menu_day[]">
                     <option value="null" selected>Condition de vie</option>
                     <option value="1">Élevage</option>
                     <option value="2">Sauvage</option>
                  </select><br>';
                     $newFood = true;
                  }
               }
            }
         }
         // insertion have menu
         $reqInsertHaveMenuModify = $pdo->prepare('INSERT INTO have_menu SET menu_id = ?, dishes_id = ?, menu_categorie = ?');
         $reqInsertHaveMenuModify->execute([$selectMenuDetails->menu_id, $InsertDishesIdModifyMenuDay, $modify[0]]);
      }
   }
   if (!empty($arrayNewFoodDishes)) {
      // modification de la liste du plats
      $implodeNewFood = implode(', ', $arrayNewFoodDishes);
      $reqUpdateDishesFood->execute([$implodeNewFood, $_GET['menu']]);
   }

   // faire une condition s'il y a un nouveau aliment , 
   // s'il n'y en pas reload la page
   if ($newFood == true) {
      echo '<input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="modify_food_dishes_menu_day" value="Ajouter">';
   } else {
      // header('location: panel.php', false);
      $urlLogin = "panel.php";
      echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
   }
   echo '</form>';
   $urlLogin = "panel.php";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
}

if (isset($_POST["modify_food_dishes_menu_day"])) {
   $reqModifyInsertFoodAllergic = $pdo->prepare('INSERT INTO food_allergic SET food_id = ?, allergic_id = ?');
   $reqModifyInsertHaveFood = $pdo->prepare('INSERT INTO have_food SET dishes_id = ?, food_id = ?');

   foreach ($_POST["name_food_modify_menu_day"] as $n => $nameFood) {
      if ($_POST["allergic_select_modify_menu_day"][$n] != 'null' && !empty($_POST["add_food_origin_modify_menu_day"][$n]) && $_POST['add_food_breeding_modify_menu_day'][$n] != 'null') {
         // si tout les champs sont remplie alors on insert 
         // l'aliment et tout ses details

         $reqModifyInsertFood = $pdo->prepare('INSERT INTO food SET food_name = ?, food_origin = ?, food_breeding = ?');
         $reqModifyInsertFood->execute([ucfirst($nameFood), $_POST["add_food_origin_modify_menu_day"][$n], $_POST['add_food_breeding_modify_menu_day'][$n]]);
         $idFood = $pdo->lastInsertId();
         $reqModifyInsertHaveFood->execute([$_POST['dishes_id_modify'][$n], $idFood]);
         $reqModifyInsertFoodAllergic->execute([$idFood, $_POST["allergic_select_modify_menu_day"][$n]]);
         echo $_POST["allergic_select_modify_menu_day"][$n], $_POST["add_food_origin_modify_menu_day"][$n], $_POST['add_food_breeding_modify_menu_day'][$n];
      } elseif ($_POST["allergic_select_modify_menu_day"][$n] != 'null' && !empty($_POST["add_food_origin_modify_menu_day"][$n]) && $_POST['add_food_breeding_modify_menu_day'][$n] == 'null') {
         // si l'allergènes n'est pas null, que le champs d'origine n'est pas vide et que le champ breeding est null 
         // alors on insert l'aliment, l'allergène , l'origine mais pas l'élevage

         $reqModifyInsertFood = $pdo->prepare('INSERT INTO food SET food_name = ?, food_origin = ?');
         $reqModifyInsertFood->execute([ucfirst($nameFood), $_POST["add_food_origin_modify_menu_day"][$n]]);
         $idFood = $pdo->lastInsertId();
         $reqModifyInsertHaveFood->execute([$_POST['dishes_id_modify'][$n], $idFood]);
         $reqModifyInsertFoodAllergic->execute([$idFood, $_POST["allergic_select_modify_menu_day"][$n]]);
      } elseif ($_POST["allergic_select_modify_menu_day"][$n] != 'null' && empty($_POST["add_food_origin_modify_menu_day"][$n]) && $_POST['add_food_breeding_modify_menu_day'][$n] == 'null') {
         // si l'allergènes n'est pas null mais que le autre champs sont null 
         // alors insertion de l'aliment

         $reqModifyInsertFood = $pdo->prepare('INSERT INTO food SET food_name = ?');
         $reqModifyInsertFood->execute([ucfirst($nameFood)]);
         $idFood = $pdo->lastInsertId();
         $reqModifyInsertHaveFood->execute([$_POST['dishes_id_modify'][$n], $idFood]);
         $reqModifyInsertFoodAllergic->execute([$idFood, $_POST["allergic_select_modify_menu_day"][$n]]);
      } else {
         $reqModifyInsertFood = $pdo->prepare('INSERT INTO food SET food_name = ?');
         $reqModifyInsertFood->execute([ucfirst($nameFood)]);
         $idFood = $pdo->lastInsertId();
         $reqModifyInsertHaveFood->execute([$_POST['dishes_id_modify'][$n], $idFood]);
      }
   }
   // header('location: panel.php', false);
   $urlLogin = "panel.php";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
}
