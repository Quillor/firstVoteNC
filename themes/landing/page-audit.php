
<p class="text-center extra-padding">
  <label style="display:initial;"> Select Election to be count.<br/>
	<select id="election-option" style="height: 45px;" onchange="mainInfo(this.value)";>
	<option>Select</option>
	<?php
					$q = new WP_Query([
					  'posts_per_page' => -1,
					  'post_type' => 'election'
					]);
					if($q->have_posts()){
						while($q->have_posts()){
							$q->the_post(); ?>
								<option value="<?php echo get_the_title(); ?>"><?php echo get_the_title(); ?></option>
												
							<?php
						}
					}
					 wp_reset_query();

	?>
		</select>	
	</label>
  <button type="button" class="btn btn-primary btn-lg" id="count-votes" data-toggle="modal" data-target="#tally-modal" data-backdrop="static" data-keyboard="false">ReCount Votes!</button>
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

	<img id="loading-image" src="../wp-content/themes/landing/img/ajax-loader.gif" style="display:none;text-align:center;margin: 0 auto;"/>
	
<div id="election-result">
</div>

<script>
	jQuery(document).ready(function(){
		  jQuery('#count-votes').prop('disabled', true);
	});
	
	function mainInfo(id) {
		jQuery.ajax({
			type: "POST",
			url: "../wp-content/themes/landing/count-election.php",
			data: {
              election: id
            },
			beforeSend: function() {
		      jQuery("#election-result").empty();
              jQuery("#loading-image").show();
               jQuery('#count-votes').prop('disabled', true);
			  //console.log(id);
            },
			success: function(result) {
				jQuery("#election-result").html(result);
				jQuery("#loading-image").hide();
				jQuery('#count-votes').prop('disabled', false);
			}
		});
	};
</script>
<style>
	#loading-image{
		text-align: center;
		margin: 0 auto;
		position: fixed;
		left: 0px;
		right: 0px;
		z-index: 99999999999;
		height: 25px;
	}
</style>