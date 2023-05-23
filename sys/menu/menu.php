<?php

$reqMenuList = $pdo->prepare('SELECT * FROM menu ORDER BY menu_title');
$reqMenuList->execute();
$menuList = $reqMenuList->fetchAll();
include './sys/dishes/db_dishes.php';
include './sys/food/db_categorie.php';
?>


<form method="post" class="col-lg-7 border border-2 p-2 p-sm-3 border-dark rounded-3 d-flex flex-wrap flex-column flex-md-row align-items-center align-items-md-start align-items-lg-start align-self-start justify-content-center  gap-2">
   <div class="d-flex flex-wrap justify-content-center gap-2 mx-3 mx-md-4">
      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="button_add_menu" value="Ajouter un Menu">
      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="button_add_menu_day" value="Ajouter un Menu/Suggestion Du jour">
      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="delete_menu" value="Supprimer">
   </div>

   <?php
   if (!isset($menuList)) {
      $menuSortByMenuDayAndSugesstion = array();

      foreach ($menuList as $m => $menu) {
         if (strtolower($menu->menu_title) == "suggestion du jour") {
            array_unshift($menuSortByMenuDayAndSugesstion, $menu);
         } elseif (strtolower($menu->menu_title) == "menu du jour") {
            array_unshift($menuSortByMenuDayAndSugesstion, $menu);
         } else {
            array_push($menuSortByMenuDayAndSugesstion, $menu);
         }
      }

      foreach ($menuSortByMenuDayAndSugesstion as $menu) {
         $reqSelectHaveMenu = $pdo->prepare('SELECT * FROM have_menu WHERE menu_id = ? ORDER BY  menu_categorie ASC');
         $reqSelectHaveMenu->execute([$menu->menu_id]);
         $SelectHaveMenu = $reqSelectHaveMenu->fetchAll();
         // nom menu

         echo '<div class="d-flex flex-column align-items-center p-2">
         <h4>' . $menu->menu_title . '</h4>' . $menu->menu_price . ' ' . $menu->menu_description . '
         <div class="">
         <a type="button" href="./panel.php?menu=' . $menu->menu_id . '"><img src="./svg/sticky.svg" alt="Modifier"></a>
         <input type="checkbox" name="checkbox_delete_menu[]" value="' . $menu->menu_id . '">
         </div>';
         foreach ($SelectHaveMenu as $haveMenu) {
            // Plat menu
            $reqSelectDishesMenu = $pdo->prepare('SELECT * FROM dishes WHERE dishes_id = ?');
            $reqSelectDishesMenu->execute([$haveMenu->dishes_id]);
            $selectDishesMenu = $reqSelectDishesMenu->fetch();
            // Sous categorie du plat
            $reqSelectSubCategorieMenu = $pdo->prepare('SELECT * FROM sub_categorie WHERE sub_categorie_id = ? ');
            $reqSelectSubCategorieMenu->execute([$selectDishesMenu->sub_categorie_id]);
            $selectSubCategorieMenu = $reqSelectSubCategorieMenu->fetch();
            // categorie du plat
            $reqSelectNameCategorie = $pdo->prepare('SELECT * FROM categorie WHERE categorie_id = ?');
            $reqSelectNameCategorie->execute([$selectSubCategorieMenu->categorie_id]);
            $selectNameCategorie = $reqSelectNameCategorie->fetch();
            // aliment du plat
            $ReqselectFoodDishes = $pdo->prepare('SELECT * FROM have_food WHERE dishes_id =?');
            $ReqselectFoodDishes->execute([$haveMenu->dishes_id]);
            $selectFoodDishes = $ReqselectFoodDishes->fetchAll();
            // id de l'allergies, id de l'aliment
            $reqSelectAllergicFood = $pdo->prepare('SELECT * FROM food_allergic WHERE food_id = ?');


            echo '<table><thead><th>' . $selectNameCategorie->categorie_name . '</th></thead>';
            echo '<tr><td>' .  $selectDishesMenu->dishes_name . '</td></tr>
            <tr><td>' . $selectDishesMenu->dishes_description . '</td></tr>';

            /// terminer les allergènes correctement
            // var_dump($selectFoodDishes);
            $ListAllergic = array();
            foreach ($selectFoodDishes as $foodDishes) {
               $reqSelectAllergicFood->execute([$foodDishes->food_id]);
               $selectAllergicFood = $reqSelectAllergicFood->fetch();


               if ($selectAllergicFood) {
                  // allergic de l'aliment s'il en a une
                  $reqSelectAllergic = $pdo->prepare('SELECT * FROM allergic WHERE allergic_id = ?');
                  $reqSelectAllergic->execute([$selectAllergicFood->allergic_id]);
                  $selectAllergic = $reqSelectAllergic->fetch();

                  if (!in_array($selectAllergic, $ListAllergic)) {
                     array_push($ListAllergic, $selectAllergic);
                  }
               }
            }
            echo '<tr><td>
            <small style="">Allergènes :</small>
            <a role="button" data-bs-html="true" class="btn " id="allergic" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-content="
            ';

            foreach ($ListAllergic as $allergic) {
               echo ' - ' . $allergic->allergic_food . '<br>';
            }
            echo '"><img src="./svg/circle-info_dark.svg"></a></tr></td>';
            echo '</table>';
            echo '<br>';
         }
         echo '</div>';
      }
   } else {
      echo "Aucun Menu";
   }
   ?>
</form>


<?php
// Menu
if (isset($_POST['button_add_menu'])) :
   if (isset($_GET)) {
      unset($_GET['menu']);
   }
?>

   <form method="post" class="gap-1 p-1 border rounded-3 border-2 border-dark col-lg-4 align-items-center justify-content-center justify-content-lg-start d-flex flex-wrap flex-column align-items-center justify-content-lg-start">
      <h4 class="text-center my-2">Ajouter un menu</h4>
      <input class="form-control" type="text" name="menu_title" placeholder="Titre du menu">
      <input class="form-control" type="number" name="menu_price" placeholder="Prix du menu">
      <textarea class="form-control" type="text" name="menu_description" rows="3" placeholder="Description du menu"></textarea>

      <?php
      // usort($categorieList, "sortDessert");
      foreach ($categorieList as $categorie) {
         echo '<input name="menu_categorie_id[]" type="hidden" value="' . $categorie->categorie_id . '">
         <select class="form-select" name="menu_' . strtolower($categorie->categorie_name) . '">
            <option value="null" selected>' . $categorie->categorie_name . '</option>';
         foreach ($listSubCategorie as $subCategorie) {
            if ($categorie->categorie_id == $subCategorie->categorie_id) {
               echo '<optgroup label="' . $subCategorie->sub_categorie_name . '">';

               $reqVerifyDishesSubCategorieId = $pdo->prepare('SELECT * FROM dishes WHERE sub_categorie_id = ?');
               $reqVerifyDishesSubCategorieId->execute([$subCategorie->sub_categorie_id]);
               $verifyDishesSubCategorieId = $reqVerifyDishesSubCategorieId->fetch();

               if (!$verifyDishesSubCategorieId) {
                  echo '<option value="null">Il ni y a pas de plats ou de vins pour la catégorie ' . $subCategorie->sub_categorie_name . '</option>';
               } else {
                  foreach ($dishesList as $dishes) {
                     if ($dishes->sub_categorie_id == $subCategorie->sub_categorie_id) {
                        echo '<option value="' . $dishes->dishes_id . '">' . $dishes->dishes_name . '</option> ';
                     }
                  }
               }
               echo '</optgroup>';
            }
         }
         echo '</select>';
      }
      ?>

      <!-- -->
      <input type="submit" name="validate_add_menu">
   </form>
<?php
endif;
?>

<?php
//Menu du jour
if (isset($_POST["button_add_menu_day"])) :
   if (isset($_GET)) {
      unset($_GET['menu']);
   }
?>
   <form method="post" id="form_menu_day" class="border rounded-3 gap-1 border-2 border-dark col-lg-4 p-3 d-flex flex-wrap flex-column align-items-center">
      <h4 class="text-center">Ajouter un menu du jour</h4>
      <select class="form-select" name="menu_day_title" placeholder="Titre du menu">
         <option value="Menu du jour">Menu du jour</option>
         <option value="Suggestion du jour">Suggestion du jour</option>
      </select>
      <input class="form-control" type="number" name="menu_day_price" placeholder="Prix du menu">
      <textarea class="form-control" type="text" name="menu_day_description" rows="3" placeholder="Description du menu"></textarea>
      <br>

      <?php
      foreach ($categorieList as $categorie) :
         if ($categorie->categorie_name != "Vins") : ?>
            <input class="form-control" type="text" id="menu_day_dishes" name="menu_day_<?= strtolower($categorie->categorie_name); ?>" placeholder="<?= strtolower($categorie->categorie_name); ?>">
            <textarea class="form-control" type="text" id="menu_day_food" name="menu_day_food_<?= strtolower($categorie->categorie_name); ?>" rows="3" placeholder="aliment <?= strtolower($categorie->categorie_name); ?>"></textarea>
         <?php else : ?>
            <select class="form-select" name="menu_day_vins">
               <?php
               echo '<option value="null">' . $categorie->categorie_name . '</option>';
               foreach ($listSubCategorie as $subCategorie) {
                  if ($subCategorie->categorie_id == $categorie->categorie_id) {
                     $reqVerifyDishesSubCategorieId = $pdo->prepare('SELECT * FROM dishes WHERE sub_categorie_id = ?');
                     $reqVerifyDishesSubCategorieId->execute([$subCategorie->sub_categorie_id]);
                     $verifyDishesSubCategorieId = $reqVerifyDishesSubCategorieId->fetch();
                     if (!$verifyDishesSubCategorieId) {
                        echo '<option value="null">Il ni y a pas de plats ou de vins pour la catégorie ' . $subCategorie->sub_categorie_name . '</option>';
                     } else {
                        foreach ($dishesList as $dishes) {
                           if ($dishes->sub_categorie_id == $subCategorie->sub_categorie_id) {
                              echo '<option value="' . $dishes->dishes_id . '">' . $dishes->dishes_name . '</option> ';
                           }
                        }
                     }
                  }
               }
               ?>
            </select>
         <?php endif;
         if ($categorie->categorie_name != "Vins") {
            echo '<select class="form-select" name="menu_day_subcategorie_' . strtolower($categorie->categorie_name) . '">';
            foreach ($listSubCategorie as $subCategorieMenuDay) {
               if ($categorie->categorie_id == $subCategorieMenuDay->categorie_id) {
                  echo '<option value="' . $subCategorieMenuDay->sub_categorie_id . '">' . $subCategorieMenuDay->sub_categorie_name . '</option>';
               }
            }
            echo '</select>';
         }
         // 26/04 testé demain l'ajout etc du formulaire menu day

         ?>

         <br>
      <?php endforeach; ?>
      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="validate_menu_day" value="Valider un menu du jour">
   </form>
<?php endif; ?>


<?php
// modify menu
if (isset($_GET['menu'])) : ?>

   <?php
   $reqSelectMenuModify = $pdo->prepare('SELECT * FROM menu WHERE menu_id = ?');
   $reqSelectMenuModify->execute([$_GET['menu']]);
   $selectModifyMenu = $reqSelectMenuModify->fetch();
   $reqSelectDishesIdHaveMenu = $pdo->prepare('SELECT * FROM have_menu WHERE menu_id = ? AND menu_categorie = ?');
   $reqSelectModifyDishesMenu = $pdo->prepare('SELECT * FROM dishes WHERE dishes_id = ?');

   // modify menu day
   if ($selectModifyMenu->menu_end_date) {
      echo '<form method="post" id="modify_menu_day" class="border rounded-3 gap-1 border-2 border-dark col-lg-4 p-3 d-flex flex-wrap flex-column align-items-center">';
      echo '<h4>Modification de menu</h4>
      <select class="form-select" name="menu_day_modify_title" placeholder="Titre du menu">
         <option value="' . $selectModifyMenu->menu_title . '"selected>' . $selectModifyMenu->menu_title . '</option>
         <option value="Menu du jour">Menu du jour</option>
         <option value="Suggestion du jour">Suggestion du jour</option>
      </select>
      <input class="form-control" type="number" name="menu_day_modify_price" placeholder="Prix du menu" value="' . $selectModifyMenu->menu_price . '">
      <textarea class="form-control" type="text" name="menu_day_modify_description" rows="3" placeholder="Description du menu">' . $selectModifyMenu->menu_description . '</textarea>';

      // plat menu day
      foreach ($categorieList as $c => $categorie) {
         $reqSelectDishesIdHaveMenu->execute([$_GET['menu'], $categorie->categorie_id]);
         $selectDishesIdHaveMenu = $reqSelectDishesIdHaveMenu->fetch();
         echo '<input class="form-control" name="menu_day_modify_' . strtolower($categorie->categorie_name) . '_id" type="hidden" value="' . $categorie->categorie_id . '">';
         if ($categorie->categorie_name != "Vins") {
            if ($selectDishesIdHaveMenu) {
               $reqSelectModifyDishesMenu->execute([$selectDishesIdHaveMenu->dishes_id]);
               $selectDishesModifyMenu = $reqSelectModifyDishesMenu->fetch();
               echo '<input class="form-control" type="text" id="menu_day_modify_dishes" name="menu_day_modify_' . strtolower($categorie->categorie_name) . '" placeholder="' . strtolower($categorie->categorie_name) . '" value="' . $selectDishesModifyMenu->dishes_name . '">';
               echo '<textarea class="form-control" id="menu_day_modify_food" name="menu_day_modify_food_' . strtolower($categorie->categorie_name) . '" rows="3"  placeholder="aliment ' . strtolower($categorie->categorie_name) . '">' . $selectDishesModifyMenu->dishes_food . '</textarea>';
            } else {

               echo '<input class="form-control" type="text" id="menu_day_modify_dishes" name="menu_day_modify_' . strtolower($categorie->categorie_name) . '" placeholder="' . strtolower($categorie->categorie_name) . '">';
               echo '<textarea class="form-control" id="menu_day_modify_food" name="menu_day_modify_food_' . strtolower($categorie->categorie_name) . '" rows="3"  placeholder="aliment ' . strtolower($categorie->categorie_name) . '"></textarea>';
            }
         } else {
            echo '<select class="form-select" name="menu_day_modify_vins">
               <option value="null">' . $categorie->categorie_name . '</option>';
            if ($selectDishesIdHaveMenu) {
               // vérifier si le plat apparait bien une fois selectionné
               $reqSelectModifyDishesMenu->execute([$selectDishesIdHaveMenu->dishes_id]);
               $selectDishesModifyMenu = $reqSelectModifyDishesMenu->fetch();
               echo '<option value="' . $selectDishesModifyMenu->dishes_id . '" selected>' . $selectDishesModifyMenu->dishes_name . '</option>';
            }

            foreach ($listSubCategorie as $subCategorie) {
               if ($subCategorie->categorie_id == $categorie->categorie_id) {
                  $reqVerifyDishesSubCategorieId = $pdo->prepare('SELECT * FROM dishes WHERE sub_categorie_id = ?');
                  $reqVerifyDishesSubCategorieId->execute([$subCategorie->sub_categorie_id]);
                  $verifyDishesSubCategorieId = $reqVerifyDishesSubCategorieId->fetch();

                  echo '<optgroup label="' . $subCategorie->sub_categorie_name . '">';
                  if (!$verifyDishesSubCategorieId) {
                     echo '<option value="null">Il ni y a pas de plats ou de vins pour la catégorie ' . $subCategorie->sub_categorie_name . '</option>';
                  } else {

                     foreach ($dishesList as $dishes) {
                        if ($dishes->sub_categorie_id == $subCategorie->sub_categorie_id) {
                           echo '<option value="' . $dishes->dishes_id . '">' . $dishes->dishes_name . '</option> ';
                        }
                     }
                  }
                  echo '</optgroup>';
               }
            }
            echo '</select>';
         }

         if ($categorie->categorie_name != "Vins") {
            echo '<select class="form-select" name="menu_day_subcategorie_' . strtolower($categorie->categorie_name) . '">';
            foreach ($listSubCategorie as $subCategorieMenuDay) {
               if ($categorie->categorie_id == $subCategorieMenuDay->categorie_id) {
                  echo '<option value="' . $subCategorieMenuDay->sub_categorie_id . '">' . $subCategorieMenuDay->sub_categorie_name . '</option>';
               }
            }
            echo '</select>';
         }
         echo '<br>';
      }
      echo '<input class="btn " style="background-color: #242423 ;color: #e8eddf;" type="submit" name="validate_modify_menu_day" value="Modifier">';
   } else {
      // modify menu
      echo '<form method="post" class="border rounded-3 gap-1 border-2 border-dark col-lg-4 p-3 d-flex flex-wrap flex-column align-items-center">
      <h4>Modification de menu</h4>
         <input class="form-control" type="text" name="menu_modify_title" placeholder="Titre du menu" value="' . $selectModifyMenu->menu_title . '">
         <input class="form-control"type="number" name="menu_modify_price" placeholder="Prix du menu" value="' . $selectModifyMenu->menu_price . '">
         <textarea class="form-control" type="text" name="menu_modify_description" rows="3" placeholder="Description du menu">' . $selectModifyMenu->menu_description . '</textarea>';

      foreach ($categorieList as $c => $categorie) {
         $reqSelectDishesIdHaveMenu->execute([$_GET['menu'], $categorie->categorie_id]);
         $selectDishesIdHaveMenu = $reqSelectDishesIdHaveMenu->fetch();
         echo '<input class="form-control" name="menu_modify_' . strtolower($categorie->categorie_name) . '_id" type="hidden" value="' . $categorie->categorie_id . '">
            <select class="form-select" name="select_menu_modify_' . strtolower($categorie->categorie_name) . '">';

         if ($selectDishesIdHaveMenu) {
            $reqSelectModifyDishesMenu->execute([$selectDishesIdHaveMenu->dishes_id]);
            $selectDishesModifyMenu = $reqSelectModifyDishesMenu->fetch();
            echo '<option value="' . $selectDishesModifyMenu->dishes_id . '" selected>' . $selectDishesModifyMenu->dishes_name . '</option>';
            echo '<option value="null">' . $categorie->categorie_name . '</option>';
         } else {
            echo '<option value="null" selected>' . $categorie->categorie_name . '</option>';
         }

         foreach ($listSubCategorie as $subCategorie) {
            if ($categorie->categorie_id == $subCategorie->categorie_id) {
               echo '<optgroup label="' . $subCategorie->sub_categorie_name . '">';
               $reqVerifyModifyDishesSubCategorieId = $pdo->prepare('SELECT * FROM dishes WHERE sub_categorie_id = ?');
               $reqVerifyModifyDishesSubCategorieId->execute([$subCategorie->sub_categorie_id]);
               $verifyModifyDishesSubCategorieId = $reqVerifyModifyDishesSubCategorieId->fetch();

               if (!$verifyModifyDishesSubCategorieId) {
                  echo '<option value="null">Il ni y a pas de plats ou de vins pour la catégorie ' . $subCategorie->sub_categorie_name . '</option>';
               } else {
                  foreach ($dishesList as $dishes) {
                     if ($dishes->sub_categorie_id == $subCategorie->sub_categorie_id) {
                        echo '<option value="' . $dishes->dishes_id . '">' . $dishes->dishes_name . '</option> ';
                     }
                  }
               }
               echo '</optgroup>';
            }
         }
         echo '</select>';
      }
      echo '<input class="btn my-2" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="validate_modify_menu" value="Modifier">';
   }
   ?>
   </form>
<?php endif; ?>

<script>
   if (formMenuDay = document.querySelector('#form_menu_day')) {
      let formMenuDay = document.querySelector('#form_menu_day')

      // annuler l'envoie du formulaire de menu du jour pour y afficher les erreurs de champs
      formMenuDay.addEventListener('submit', (event => {
         if (!formMenuDay.elements[1].value) {
            event.preventDefault()
            alert("Vous n'avez pas choisit de prix")
         }
         // verifier ce script
         for (let i = 3; i <= 16; i += 3) {
            if (formMenuDay.elements[i].value && !formMenuDay.elements[i + 1].value) {
               event.preventDefault();
               alert(`Les aliments du plat de la catégorie ${formMenuDay.elements[i].name.substring(9)} ne sont pas remplis`);
            } else if (!formMenuDay.elements[i].value && formMenuDay.elements[i + 1].value) {
               event.preventDefault();
               alert(`Le nom du plat de la categorie ${formMenuDay.elements[i].name.substring(9)} n'est pas remplit`);
            }
         }
      }))
   }

   if (formModifyMenuDay = document.querySelector('#modify_menu_day')) {
      let formModifyMenuDay = document.querySelector('#modify_menu_day')

      formModifyMenuDay.addEventListener('submit', (event => {
         if (!formModifyMenuDay.elements[1].value) {
            event.preventDefault()
            alert("Vous n'avez pas choisit de prix")
         }
         // verifier ce script
         for (let i = 4; i <= 24; i += 4) {
            if (formModifyMenuDay.elements[i].value && !formModifyMenuDay.elements[i + 1].value) {
               event.preventDefault();
               alert(`Les aliments du plat de la catégorie ${formModifyMenuDay.elements[i].name.substring(16)} ne sont pas remplis`);
            } else if (!formModifyMenuDay.elements[i].value && formModifyMenuDay.elements[i + 1].value) {
               event.preventDefault();
               alert(`Le nom du plat de la categorie ${formModifyMenuDay.elements[i].name.substring(16)} n'est pas remplit \r 
               Ou les aliments sont remplis pour une suppression du plat pour le menu !`);
            }
         }
      }))
   }
</script>