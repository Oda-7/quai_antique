<?php
$namePage = "Quai Antique";

include './templates/header.php';

if (isset($_SESSION['flash'])) :
    foreach ($_SESSION['flash'] as $type => $message) : ?>
        <div class="alert alert-<?= $type; ?>">
            <?= $message; ?>
        </div>
<?php endforeach;
    unset($_SESSION['flash']);
endif; ?>

<?php if (!empty($errors)) : ?>
    <div class="alert alert-danger">
        <p>Les champs du formulaire sont pas rempli correctement</p>
        <ul>
            <?php foreach ($errors as $error) : ?>
                <li><?= $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif;

?>


<article class="d-flex flex-column position-relative container-fluid mt-5 py-5">
    <div class="d-flex justify-content-around mt-3">
        <h1>Bienvenue au restaurant le Quai Antique</h1>
    </div>

    <div class="d-flex justify-content-center">
        <p class="">Le restaurant le Quai Antique vous propose un voyage culinaire avec des produits locaux de Savoie.<br>
            Le Chef Arnaud Michant vous donne rendez-vous pour vivre une expérience culinaire inégalable.
        </p>
    </div>


    <div class="d-flex flex-wrap pt-4 gap-4  justify-content-center" id="galleryPicture">
        <?php
        $reqSelectImage = $pdo->prepare('SELECT * FROM images');
        $reqSelectImage->execute();
        $selectImage = $reqSelectImage->fetchAll();

        if ($selectImage) :
            // s'il y a des images dans la base de donnée on les affiche
            foreach ($selectImage as $i => $image) {
                // si je récupère bien les images alors je vais boucler dessus pour avoir les mettre dans la page d'acceuil
                $nameImage = strtolower(strrchr($image->images_path, '/'));

                if ($i % 2 == 1) {
                    if ($i == 3 && $i % 3 == 0) {
                        echo '<div class=" col-lg-5 ">
                        <img class="h-100 w-100" src="' . $image->images_path . '" alt="' . $nameImage . '" title="' . $nameImage . '" id="firstImage">
                        </div>';
                        echo '</div>';
                    } else {
                        echo '<div class="col-lg-6">
                            <img class="h-100 w-100" src="' . $image->images_path . '" alt="' . $nameImage . '" title="' . $nameImage . '" id="firstImage">
                        </div>';
                        echo '</div>';
                    }
                } else {
                    if ($i == 2 && $i % 2 == 0) {
                        echo '<div id="div_image" style="width: 70%;" class="row gap-2 justify-content-center">';
                        echo '<div class="col-lg-6">
                            <img class="h-100 w-100" src="' . $image->images_path . '" alt="' . $nameImage . '" title="' . $nameImage . '" id="firstImage">
                        </div>';
                    } else {
                        echo '<div id="div_image" style="width: 70%;" class="row gap-2 justify-content-center">';
                        echo '<div class=" col-lg-5">
                            <img class="h-100 w-100" src="' . $image->images_path . '" alt="' . $nameImage . '" title="' . $nameImage . '" id="firstImage">
                        </div>';
                    }
                }
                // echo $i + 1 . ' ' . $image->images_path;
            }
        ?>
        <?php else : ?>
            <div class=" row gap-4 justify-content-center">
                <div class="col-lg-5">
                    <img class="h-100 w-100" src="./images/pate.jpg" alt="pates" title="pates" id="firstImage">
                </div>
                <div class="col-lg-6">
                    <img class="h-100 w-100" src="./images/viande.jpg" alt="viande" title="viande" id="secondImage">
                </div>
            </div>
            <div class=" row gap-4 justify-content-center">
                <div class="col-lg-6">
                    <img class="h-100 w-100" src="./images/pate.jpg" alt="pates" title="pate" id="thirdImage">
                </div>
                <div class="col-lg-5">
                    <img class="h-100 w-100" src="./images/viande.jpg" alt="viande" title="viande" id="fourthPicture">
                </div>
            </div>
            <div class=" row gap-4 justify-content-center">
                <div class="col-lg-5">
                    <img class="h-100 w-100" src="./images/viande.jpg" alt="viande" title="viande" id="fourthPicture">
                </div>
                <div class="col-lg-6">
                    <img class="h-100 w-100" src="./images/pate.jpg" alt="pates" title="pate" id="thirdImage">
                </div>
            </div>
    </div>
<?php endif; ?>

</article>

<?php include './templates/footer.php'; ?>