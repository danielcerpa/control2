<?php
// includes/footer.php — Cierre de main, scripts JS, cierre body/html
?>
</main><!-- /main -->
</div><!-- /row -->
</div><!-- /container-fluid -->
<!-- jQuery 1.12 LOCAL (compatible IE8+) -->
<script src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
<!-- Bootstrap 4 JS LOCAL -->
<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
<?php if (isset($extra_js)) echo $extra_js; ?>
</body>

</html>