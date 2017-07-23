<?php

namespace Roots\Sage\Shortcodes;

// Row shortcode (to wrap columns)
function row_shortcode($atts, $content = null) {
  extract( shortcode_atts( array(
  ), $atts ) );

  $output = '<div class="row">';
  $output .= do_shortcode($content);
  $output .= '</div>';

  return $output;
}
add_shortcode('row', __NAMESPACE__ . '\\row_shortcode');

// Columns shortcode
function column_shortcode($atts, $content = null) {
  extract( shortcode_atts( array(
    'size' => 'md-6'
  ), $atts ) );

  $output = '<div class="col-' . $size . '">';
  $output .= do_shortcode($content);
  $output .= '</div>';

  return $output;
}
add_shortcode('column', __NAMESPACE__ . '\\column_shortcode');

// Colophon
function colophon_shortcode($atts, $content = null) {
  extract( shortcode_atts( array(
  ), $atts) );

  $output = '<span class="colophon"></span>';

  return $output;
}
add_shortcode('colophon', __NAMESPACE__ . '\\colophon_shortcode');
