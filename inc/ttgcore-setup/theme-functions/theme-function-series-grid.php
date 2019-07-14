<?php
/**
 * @package WordPress
 * @subpackage  QantumThemes Series plugin
 * @subpackage wpcast
 * @version 1.0.0
 * Theme function for custom parts:
 * Series grid standard cards
 *
 * Example:
 * [qt-series-grid columns="1|2|3" hide_empty="0|1" include="all|''|1,2,3" exclude="false|1,2,3" exclude="''|1,2,3" parent="1|0" child_of="false|1,2,3"]
*/


if(!function_exists( 'wpcast_template_series_grid' )){
	function wpcast_template_series_grid( $atts = array() ){

		ob_start();

		extract( shortcode_atts( array(
			'amount'				=> '3',
			'columns'				=> '3',
			'hide_empty' 			=> 0, //can be 1, '1' too
			'include' 				=> 'all', //empty string(''), false, 0 don't work, and return empty array
			'exclude' 				=> false, //empty string(''), false, 0 don't work, and return empty array
			'exclude_tree' 			=> '', //empty string(''), false, 0 don't work, and return empty array
			'parent' 				=> '1', // if "1", will show only first level categories
			'child_of' 				=> false,
			'el_id'					=> uniqid( 'qt-series-grid-'.get_the_ID() ), 
			'grid_id'				=> false, // required for compatibility with WPBakery Page Builder
		), $atts ) );


		if($grid_id == false){
			$grid_id = $el_id;
		}


		// Hide empty categories
		if( $hide_empty == 0 ){
			$args['hide_empty'] = 0;
		} else {
			$args['hide_empty'] = 1;
		}

		// include only these
		if( $include !== 'all' && $include !== '' ){
			$args['include'] = array_map( 'trim', explode(',', $include) );
		}

		// esclude only these
		if( $exclude ){
			$args['exclude'] =  array_map( 'trim', explode(',', $exclude) );
		}

		// esclude all the cats under these IDs
		if( $exclude_tree ){
			$args['exclude_tree'] = array_map( 'trim', explode(',', $exclude_tree) );
		}

		// show only first level
		if( $parent == '1' ){
			$args['parent'] = 0;
		}
		
		// sub cats of a parent id
		if( $child_of ){
			$args['child_of'] = intval( $child_of );
			unset( $args['parent'] );
		}



		if( !function_exists( 'qt_series_custom_series_name' ) ){
			esc_html_e( 'Please activate the Series Plugin to use this functionality' , 'wpcast' );
			$output = ob_get_clean();
			return $output;
		} 

		$seriesname = qt_series_custom_series_name();
		$series = get_terms( 
			$seriesname , 
			$args
		);


		switch ($columns){
			case '1':
				$classes = 'wpcast-s12 wpcast-m12 wpcast-l12';
				break;

			case '2':
				$classes = 'wpcast-s12 wpcast-m6 wpcast-l6';
				break;

			case '3':
			default:
				$classes = 'wpcast-s12 wpcast-m6 wpcast-l4';

		}


		$amount = intval($amount);
		$index = 0;

		// Output start
		?>
		<div id="<?php echo esc_attr( $grid_id ); ?>" class="wpcast-container wpcast-template-series-grid">
			<?php 
			if( !$series || count($series) == 0 ){
				?>

				<div class="wpcast-section">
					<h5 class="wpcast-center"><?php esc_html_e( "There are no series yet", 'wpcast' ); ?></h5>
				</div>

				<?php
			} else {
				?>

				<div class="wpcast-row">
					<?php 
					// List of series taxonomy terms
					foreach($series as $serie){

						if($index < $amount){

							/**
							 * 
							 * Used to pass variables to template files (actually safest way)
							 * https://developer.wordpress.org/reference/functions/set_query_var/#comment-2285
							 * 
							 */
							set_query_var( 'wpcast_var_serie',  $serie  ); 
							?>
								<div class="wpcast-col <?php echo esc_attr( $classes ); ?>">
									<?php  
									get_template_part ('template-parts/serie/serie-card');
									?>
								</div>
							<?php  
							remove_query_arg( 'wpcast_var_serie' );
							$index ++;

						}

						
					}
					?>
				</div>

			<?php 
			}
			?>
		</div>
		<?php
		// Output end
		
		$output = ob_get_clean();
		
		return $output;

	}
}


// Set TTG Core shortcode functionality
if(function_exists('ttg_custom_shortcode')) {
	ttg_custom_shortcode("qt-series-grid","wpcast_template_series_grid");
}




/**
 *  Visual Composer integration
 */
add_action( 'vc_before_init', 'wpcast_template_series_grid_vc' );
if(!function_exists('wpcast_template_series_grid_vc')){
function wpcast_template_series_grid_vc() {
  vc_map( array(
	 "name" 		=> esc_html__( "Series grid - Large", "wpcast" ),
	 "base" 		=> "qt-series-grid",
	 "icon" 		=> get_theme_file_uri( '/inc/ttgcore-setup/theme-functions/img/qt-series-grid.png' ),
	 "description" 	=> esc_html__( "Display a grid of series with episodes", "wpcast" ),
	 "category" 	=> esc_html__( "Theme shortcodes", "wpcast"),
	 "params" 		=> array(
	 	array(
			"type" 		=> "dropdown",
			"heading" 	=> esc_html__( "Columns number", "wpcast" ),
			"param_name"=> "columns",
			'std'		=> '3',
			'value' 	=> array( 
				esc_html__("1","wpcast") 	=> "1",
				esc_html__("2","wpcast") 	=> "2",
				esc_html__("3","wpcast") 	=> "3",
			
				
				)			
			),
	 	array(
		   "type" 		=> "textfield",
		   "heading" 	=> esc_html__( "Max amount", "wpcast" ),
		   "param_name" => "amount",
		   'std'		=> '3' // not translatable, is a dynamic parameter for the query
		),
		array(
		   "type" 		=> "textfield",
		   "heading" 	=> esc_html__( "Label for posts count", "wpcast" ),
		   "param_name" => "label",
		   'std'		=> esc_html__( 'Episodes', 'wpcast' )
		),
		array(
			"type" 		=> "dropdown",
			"heading" 	=> esc_html__( "Hide empty categories", "wpcast" ),
			"param_name"=> "hide_empty",
			'std'		=> 0,
			'value' 	=> array( 
				esc_html__("No","wpcast") 	=> 0,
				esc_html__("Yes","wpcast") 	=> "1",
				
				)			
			),
		array(
			"type" 		=> "dropdown",
			"heading" 	=> esc_html__( "Display sub-categories", "wpcast" ),
			"param_name"=> "parent",
			'std'		=> '1',
			'value' 	=> array( 
				esc_html__("No","wpcast") 	=> "1",
				esc_html__("Yes","wpcast") 	=> "0",
				
				)			
			),

		array(
		   "type" 		=> "textfield",
		   "heading" 	=> esc_html__( "Include by id, comma separated", "wpcast" ),
		   "param_name" => "include",
		   'std'		=> 'all' // not translatable, is a dynamic parameter for the query
		),
		array(
		   "type" 		=> "textfield",
		   "heading" 	=> esc_html__( "Exclude by id, comma separated", "wpcast" ),
		   "param_name" => "exclude",
		   'std'		=> false // not translatable, is a dynamic parameter for the query
		),
		array(
		   "type" 		=> "textfield",
		   "heading" 	=> esc_html__( "Exclude entire tree by id, comma separated", "wpcast" ),
		   "param_name" => "exclude",
		),
		array(
		   "type" 		=> "textfield",
		   "heading" 	=> esc_html__( "Child of (ID)", "wpcast" ),
		   "param_name" => "child_of",
		),
	 )
  ) );
}}


