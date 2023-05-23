<div id="errors_select"></div>


<form method="post" class="d-flex flex-column justify-content-center align-items-center " enctype="multipart/form-data">
   <div class="d-flex flex-column align-items-center flex-wrap">
      <label class="btn p-2" style="background-color: #333533 ;color: #e8eddf;" for="image_uploads">Choisissez des image (PNG, JPG)</label>
      <input class="" type="file" id="image_uploads" name="image_uploads[]" accept=".jpg, .jpeg, .png" multiple />
   </div>
   <br>
   <input class="btn mb-2" style="background-color: #333533 ;color: #e8eddf;" type="submit" value="Enregistrer">
   <div id="preview" class="d-inline-flex flex-wrap justify-content-center align-items-center gap-3">
      <p>Aucun fichier selectionné</p>
   </div>
</form>


<?php
// galerie d'image
// $filesTest = '<pre>' . print_r($_FILES, true) . '</pre>';
// echo $filesTest;
// $arrayImageHome = array();

if (!is_dir('./images/image_base')) {
   mkdir('./images/image_base', 0777);
}

$openFiles = opendir('./images/image_base');
$uploadDir = './images/image_base';
?>
<form method="post" id="formImage" class="d-flex flex-column-reverse align-items-center  my-3 gap-3">
   <?php
   // formulaire d'image
   if (readdir($openFiles)) {

      echo '<div class="d-flex flex-wrap justify-content-center gap-3">';
      while ($files_current = readdir($openFiles)) {
         $extension = strtolower(strrchr($files_current, '.'));
         if ($extension == '.jpg' || $extension == '.jpeg' || $extension == '.png') {
            $pathImageRead = $uploadDir . '/' . $files_current;

            if (file_exists($pathImageRead)) {
               $haveImage = true;
               echo '<div class="d-flex flex-column align-items-center">
                  <input  type="checkbox" name="path_image[]" id="path_image" value="' . $pathImageRead . '">
                  <img  style="width:20rem;" src="' . $pathImageRead . '" title="' . $files_current . '">
               </div>';
            }
         }
      }
      echo '</div>';
   }

   // gestion d'images
   ?>
   <div id="add_input"></div>

   <?php
   if (isset($haveImage)) {
      echo "<div class='d-flex flex-column align-items-center gap-3'>
      <label class='text-center'><b>Cliquer au dessus de l'image pour donner un ordre d'image sur la page d'acceuil (entre 1 et 6) </b></label>";
      echo '<input type="submit" class="btn" style="background-color: #333533 ;color: #e8eddf;" name="delete_img" value="Supprimer un image">';
      echo '<label>Ordonner vos images pour la page d\'acceuil :</label>
      <input type="submit" class="btn overflow-auto" style="background-color: #333533 ;color: #e8eddf;" id="select_position" name="select_position" value="Ordonner">
      </div>';
   } ?>
</form>


<?php
// verification d'ajout d'image
if (isset($_FILES['image_uploads'])) {
   foreach ($_FILES['image_uploads']['tmp_name'] as $f => $file) {
      if (isset($_FILES['image_uploads']['tmp_name'][$f])) {

         $tmp_name = $_FILES['image_uploads']['tmp_name'][$f];
         $name = basename($_FILES["image_uploads"]["name"][$f]);

         $moveUpload = move_uploaded_file($tmp_name, "$uploadDir/$name");
         if ($moveUpload) {
            echo '<p>La photo ' . $name . ' a bien été envoyée.</p>';
         }
      }
   }
}

// delete image
if (isset($_POST['delete_img'])) {
   if (!empty($_POST['path_image'])) {
      foreach ($_POST['path_image'] as $pathImageDelete) {
         // unlink($pathImageDelete);
      }
   } else {
      $errors['no_delete_img'] = "Vous n'avez pas sélectionné d'image(s) à supprimer";
   }
}

// requete qui récupère le nombre d'image
$reqCountTableImage = $pdo->prepare('SELECT COUNT(images_id) AS number_images FROM images');
$reqCountTableImage->execute();
$countTableImage = $reqCountTableImage->fetch();

// order image home
$numberCookie = 0;

if (isset($_POST['select_position'])) {
   if (count($_POST['image_path_order']) == 2 || count($_POST['image_path_order']) == 4  || count($_POST['image_path_order']) == 6) {


      // si le nombre d'images de la bd est different du nombre d'images ajouter a insérer alors on supprime les autres 
      // et on modifie celle existante
      $reqInsertImage = $pdo->prepare('INSERT INTO images SET images_id = ?, images_path = ?');
      $reqUpdateImage = $pdo->prepare('UPDATE images SET images_path = ? WHERE images_id = ?');

      if ($countTableImage->number_images > count($_POST['image_path_order'])) {
         $reqDeleteImageSuperiorAtCountTable = $pdo->prepare('DELETE FROM images WHERE images_id > ?');
         $reqDeleteImageSuperiorAtCountTable->execute([count($_POST['image_path_order'])]);
         // modify image_path
         foreach ($_POST['image_path_order'] as $o => $orderImage) {
            $reqUpdateImage->execute([$orderImage, $o + 1]);
         }
      } else {

         // insertion en base de donnée
         foreach ($_POST['image_path_order'] as $o => $orderImage) {
            if ($o + 1 > $countTableImage->number_images) {
               $reqInsertImage->execute([$o + 1, $orderImage]);
            } else {
               $reqUpdateImage->execute([$orderImage, $o + 1]);
            }
         }
      }
   } elseif (count($_POST['image_path_order']) < 6) {
      $errors['bad_number'] = "Vous ne pouvez selection que 6 images";
   }
}
?>


<script src="./sys/images_manager/image_manager.js">
   // script download DL Show
</script>

<script>
   // script Order
   const form = document.getElementById('formImage')
   const buttonCheckbox = document.querySelectorAll('#path_image')
   let countSelect = 1

   const addInput = document.getElementById('add_input')

   const errorsSelect = document.getElementById('errors_select')
   // let pErrors = document.createElement('p')
   const buttonOrder = document.getElementById('select_position')

   for (let checkbox of buttonCheckbox) {
      let input = document.createElement('input')
      checkbox.onchange = () => {
         // arrayTest = []

         if (checkbox.checked) {
            // si l'element est supprimé on récupère son ordre
            pathImage = checkbox.value
            input.type = "hidden"
            input.name = "image_path_order[]"
            input.value = pathImage

            addInput.append(input)

            if (checkbox.checked && countSelect < addInput.childElementCount) {
               // si le count select et plus petit que le nombre d'element du tableau
               countSelect = addInput.childElementCount
            } else {
               if (countSelect == addInput.childElementCount) {
                  countSelect = addInput.childElementCount
                  countSelect += 1
               } else {
                  countSelect++
               }
            }

         } else {
            // voir le test sinon inseré 12/05
            regex = /[0-9*]/gm
            intInput = input.name.match(regex)
            countSelect--
            input.remove()
         }

         if (countSelect == 7) {
            countSelect = 1
         }

         buttonOrder.onclick = (event) => {
            if (addInput.childElementCount == 6 || addInput.childElementCount == 4 || addInput.childElementCount == 2) {
               // pErrors.remove()
            } else {
               event.preventDefault()
               alert("Choisissez un nombre paire (2-4-6) d'images")
            }
         }

      }
   }
</script>