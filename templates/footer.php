</div>

<footer class="mt-4 py-4" style="background-color:#242423; z-index: 2;">
   <div class="container my-4 d-flex flex-wrap justify-content-between" style="color: #e8eddf;">
      <div>
         <h1 style="font-family: Charlotte;">Le Quai Antique</h1>
         <h2>Restaurant gastronomique</h2>
         <p>Chambery 73000<br>
            Lorem Ipsum<br>
            Tel: <a class="text-decoration-none " style="color: #e8eddf;" href="tel:06"> 06.00.00.00.00</a><br>
            Mail du Chef: <a class="text-decoration-none" style="color: #e8eddf;" href="mailto:arnaud.michant@gmail.com"> arnaud.michant@gmail.com</a>
         </p>
      </div>

      <?php include './sys/planning/db_planning.php';

      if ($dayTime) : ?>
         <div>
            <table>
               <thead>
                  <tr>
                     <?php
                     foreach ($dayTime as $dayTable) {
                        echo '<th>' . $dayTable->planning_name . '</th>';
                     } ?>
                  </tr>
               </thead>
               <tbody>
                  <tr>
                     <?php
                     foreach ($dayTime as $dayTable) {
                        echo '<td>';
                        if ($dayTable->planning_close == 'fermÃ©') {
                           echo $dayTable->planning_close;
                        } else {
                           if (!$dayTable->planning_hours_close && !$dayTable->planning_second_hours_open) {
                              echo substr($dayTable->planning_hours_open, 0, -3) . ' - ' . substr($dayTable->planning_second_hours_close, 0, -3);
                           } else {
                              if ($dayTable->planning_hours_open) {
                                 echo substr($dayTable->planning_hours_open, 0, -3) . ' - ' . substr($dayTable->planning_hours_close, 0, -3);
                              }
                              if ($dayTable->planning_second_hours_open) {
                                 echo '<br>' . substr($dayTable->planning_second_hours_open, 0, -3) . ' - ' . substr($dayTable->planning_second_hours_close, 0, -3);
                              }
                           }
                        }
                        echo '</td>';
                     }
                     ?>
                  </tr>
               </tbody>
            </table>
         </div>
      <?php else : ?>
         <p>Le planning n'à pas était configuré</p>
      <?php endif; ?>

   </div>
</footer>
</body>



<script src="./node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
<script>
   var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
   var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl)
   })
</script>
<!-- <script src="./node_modules/bootstrap/js/dist/popover.js"></script>