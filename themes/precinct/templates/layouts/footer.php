<?php

use Roots\Sage\Assets;

?>
<footer class="global-footer">
  <div class="container">
    <div class="text-center">
      <p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo Assets\asset_path('images/logo_small.png'); ?>" srcset="<?php echo Assets\asset_path('images/logo_small@2x.png'); ?> 2x" alt="First Vote NC" /></a></p>
      <p>Copyright &copy; <?php echo date('Y'); ?> &nbsp;|&nbsp; First Vote NC &nbsp;|&nbsp; <a href="/contact">Contact</a></p>
    </div>
  </div>
</footer>
