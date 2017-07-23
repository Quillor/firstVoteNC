<?php

use Roots\Sage\Assets;
use Roots\Sage\Nav;

$class = '';
?>
<header id="header" class="banner">
  <div class="container">
    <div class="navbar-header navbar-default">
      <?php if (is_user_logged_in()) : ?>
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
      <?php endif; ?>
      <a class="navbar-brand" href="<?php echo esc_url( network_site_url( '/' ) ); ?>"><img src="<?php echo Assets\asset_path('images/logo.png'); ?>" srcset="<?php echo Assets\asset_path('images/logo@2x.png'); ?> 2x" alt="First Vote NC" /></a>
    </div>

    <?php if (is_user_logged_in()) : ?>
      <nav class="navbar collapse navbar-collapse" data-topbar role="navigation" id="navbar-collapse-1">
        <div class="navbar-right">
          <ul class="nav navbar-nav">
            <?php
            $navs = array(
              array(
                'title' => 'Teacher Dashboard',
                'type' => 'home'
              ),
              array(
                'title' => 'Lesson Plans',
                'type' => 'page',
                'slug' => 'lesson-plans'
              )
            );

            foreach ($navs as $nav) {
              $class = '';
              $url = '';

              switch ($nav['type']) {
                case 'home':
                  $url = '/?manage';
                  if (is_home()) {
                    $class = 'active';
                  }
                  break;

                case 'page':
                  $url = '/' . $nav['slug'];
                  if (is_page($nav['slug'])) {
                    $class = 'active';
                  }
                  break;
              }
              ?>
              <li class="<?php echo $class; ?>">
                <a href="<?php echo home_url($url); ?>">
                  <?php echo $nav['title']; ?>
                </a>
              </li>
              <?php
            }
            ?>
            <li class="dropdown">
              <?php $current_user = wp_get_current_user(); ?>
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $current_user->user_firstname; ?> <?php echo $current_user->user_lastname; ?> <span class="caret"></span></button>
              <ul class="dropdown-menu dropdown-menu-right">
                <!-- <li><a href="#">Profile</a></li> -->
                <li><a href="<?php echo wp_logout_url('http://firstvotenc.org'); ?>">Log Out</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
    <?php else: ?>
      <nav class="navbar collapse navbar-collapse logged-out" data-topbar role="navigation" id="navbar-collapse-1">
        <div class="navbar-right">
          <ul class="nav navbar-nav">
            <li class="<?php echo $class; ?>">
              <a href="<?php echo network_site_url('/teacher-login'); ?>">Teacher Login</a>
            </li>
          </ul>
        </div>
      </nav>
    <?php endif; ?>
  </div>
</header>
