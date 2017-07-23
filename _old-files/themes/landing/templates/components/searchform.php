<form class="form-inline search" role="search" method="get" action="<?php echo esc_url(home_url( '/' )); ?>">
  <div class="form-group">
    <label class="hidden" for="s">Search</label>
    <input class="form-control input-sm" value="<?php echo get_search_query(); ?>" type="search" placeholder="Search..." name="s" id="s" />
  </div>
  <button type="submit" class="btn" class="postfix">Go</button>
</form>
