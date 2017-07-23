<section class="precinct-admin">
  <div class="container">
    <div class="row extra-bottom-margin">
      <div class="col-md-6">

        <?php
        $election = new WP_Query([
          'post_type' => 'election',
          'posts_per_page' => -1
        ]);
        ?>

        <table class="table table-condensed">
          <thead>
            <tr>
              <th scope="col" class="h3">Simulation Elections</th>
              <th scope="col">Dates</th>
            </tr>
          </thead>

          <tbody>

          <?php if ($election->have_posts()) : while ($election->have_posts()): $election->the_post();

              /**
               * When election is live
               *
               *
               *
               */

            if ( current_user_can( 'editor' ) ) { ?>

              <tr>
                <th scope="row">
                  <a href="<?php the_permalink(); ?>?edit"><?php the_title(); ?></a><br />
                  <span class="small"><a href="<?php the_permalink(); ?>?edit">Edit</a> | <a href="<?php the_permalink(); ?>?preview">Preview Ballot</a> | <a href="<?php the_permalink(); ?>?results=general">Results</a></span>
                </th>
                <td>
                  <?php echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_early_voting', true))); ?> |
                  <?php echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true))); ?>
                </td>
              </tr>

            <?php } else { ?>

              <tr>
                <th scope="row">
                  <?php the_title(); ?><br />
                  <span class="small"><a href="<?php echo add_query_arg('preview', '', the_permalink()); ?>">Preview Ballot</a></span> |
                  <span class="small"><a href="<?php echo add_query_arg('results', 'general', the_permalink()); ?>">View Results</a></span>
                </th>
                <td>
                  <?php echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_early_voting', true))); ?> -
                  <?php echo date('m/d/Y', strtotime(get_post_meta(get_the_id(), '_cmb_voting_day', true))); ?>
                </td>
              </tr>

            <?php }
          endwhile; else: ?>

            <tr>
              <td colspan="2">
                <div class="well well-sm">
                  <p><em>No simulation elections have been created for your precinct.</em></p>

                  <?php if ( current_user_can( 'editor' ) ) { ?>
                    <a class="btn btn-default" href="?add">Add Simulation Election</a>
                  <?php } ?>

                </div>
              </td>
            </tr>

          <?php endif; wp_reset_postdata(); ?>
          </tbody>
        </table>


        <?php if (is_super_admin()) { ?>
          <p class="text-center extra-padding">
            <button type="button" class="btn btn-primary btn-lg" id="count-votes" data-toggle="modal" data-target="#tally-modal" data-backdrop="static" data-keyboard="false">Count Votes!</button>
          </p>

          <div class="modal fade" id="tally-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Hang tight! We're counting votes at each precinct:</h4>
                </div>
                <div class="modal-body">
                  <div id="script-progress"></div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" id="btn-close" style="display: none;" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>

      <div class="col-md-6">

        <?php
        // Only show for school-specific precincts
        if (get_bloginfo() !== 'North Carolina') :
          $officials = get_users();
          ?>

          <table class="table table-hover table-condensed">
            <thead>
              <tr>
                <th scope="col" class="h3">Election Officials</th>
                <th scope="col">Class</th>
              </tr>
            </thead>

            <tbody>

              <?php foreach ($officials as $official) : if ($official->ID != 1) : ?>

                <tr>
                  <th scope="row">
                    <a href="mailto:<?php echo $official->user_email; ?>">
                      <?php echo $official->display_name; ?>
                    </a><br />
                    <?php if (user_can($official, 'edit_pages')) { ?>
                      <span class="small">Precinct Director</span>
                    <?php } ?>
                  </th>
                  <td><?php echo get_user_meta($official->ID, 'classes', true); ?></td>
                </tr>

              <?php endif; endforeach; ?>

            </tbody>
          </table>

        <?php endif; ?>

      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <h3>TurboVote for Teachers</h3>
        <div class="entry-content-asset" style="height: 500px;"><iframe src="https://firstvotenc.turbovote.org"></iframe></div>
        <p class="small">Powered by TurboVote: <a href="https://firstvotenc.turbovote.org">register to vote, request absentee ballots, and get election reminders</a></p>
      </div>

      <div class="col-md-6">
        <h3>Informational Webinar</h3>
        <div class="entry-content-asset"><iframe width="560" height="315" src="https://www.youtube.com/embed/_ZYJYFWe8Dg" frameborder="0" allowfullscreen></iframe></div>
        <p>This webinar provides an overview of the First Vote North Carolina project, including implementation ideas, training on customizing your school's online ballot, instruction on utilizing the exit poll data for post-election analysis, and a summary of the adaptable curricular resources.</p>
        <p><strong>Questions?</strong> We're here to help: <a href="mailto:help@firstvotenc.org">help@firstvotenc.org</a></p>
      </div>
    </div>

  </div>
</section>
