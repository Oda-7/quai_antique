   <?php
   $namePage = "Menu";

   include './templates/header.php';

   $reqMenuList = $pdo->prepare('SELECT * FROM menu ORDER BY menu_title');
   $reqMenuList->execute();
   $menuList = $reqMenuList->fetchAll();

   include './sys/dishes/db_dishes.php';
   include './sys/food/db_categorie.php';
   ?>

   <div class="d-flex justify-content-center mt-5 py-4">
      <h1>Voici nos Menus</h1>
   </div>

   <div class="d-flex align-items-center align-items-sm-start justify-content-evenly  py-3 flex-wrap gap-5">

      <?php
      if (!isset($MenuList)) {
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
            $reqSelectHaveMenu = $pdo->prepare('SELECT * FROM have_menu WHERE menu_id = ? ORDER BY menu_categorie ASC');
            $reqSelectHaveMenu->execute([$menu->menu_id]);
            $SelectHaveMenu = $reqSelectHaveMenu->fetchAll();
            // nom menu
            echo '<div class="d-flex flex-wrap flex-column px-3 py-3 border border-3 border-dark align-items-center" style="background-color: #333533 ;color: #e8eddf;border-radius:0% 5%;">
            
            <h1 class="mx-3 pb-2  mb-4 border-bottom">' . $menu->menu_title . '</h1> 
            <p>Prix : ' . $menu->menu_price . '</p>
             ' . $menu->menu_description . '';

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


               echo '<table>
            <thead>
               <th>' . $selectNameCategorie->categorie_name . '</th>
            </thead>';
               echo '<tr>
               <td>' . $selectDishesMenu->dishes_name . '</td>
               </tr>';
               echo '<tr>';

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
               echo '<td>
               <small style="">Allergènes :</small>
               <a role="button" data-bs-html="true" class="btn " id="allergic" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-content="';
               foreach ($ListAllergic as $a => $allergic) {
                  echo ' - ' . $allergic->allergic_food . '<br>';
               }
               echo '"><img src="./svg/circle-info.svg">
               </a>
               </td>
               </tr>
               </table>';
               echo '<br>';
            }
            echo '</div>';
         }
      } else {
         echo "Aucun Menu";
      }
      ?>
   </div>


   <?php include './templates/footer.php'; ?>