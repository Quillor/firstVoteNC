<?php

use Roots\Sage\Assets;
use Roots\Sage\Nav;

?>
<header id="header" class="banner">
  <div class="container">
    <div class="navbar-header navbar-default">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo Assets\asset_path('images/logo.png'); ?>" srcset="<?php echo Assets\asset_path('images/logo@2x.png'); ?> 2x" alt="First Vote NC" /></a>
    </div>

    <nav class="navbar collapse navbar-collapse" data-topbar role="navigation" id="navbar-collapse-1">
      <div class="navbar-right">
        <?php
        if (has_nav_menu('primary_navigation')) {
          wp_nav_menu(['theme_location' => 'primary_navigation', 'container' => false, 'menu_class' => 'nav navbar-nav', 'depth' => 2, 'walker' => new Nav\NavWalker()]);
        }

        if (is_user_logged_in()) {
          $blog = get_active_blog_for_user( get_current_user_id() );
          ?>
          <ul class="nav navbar-nav">
            <li class="btn menu-item"><a href="<?php echo $blog->siteurl; ?>?manage">Teacher Dashboard &rarr;</a></li>
          </ul>
          <?php
        }
        ?>
      </div>
    </nav>
  </div>
</header>
