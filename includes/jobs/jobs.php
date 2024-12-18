<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


/*
    =============================================
    Register 'jobs' Custom Post Type
    =============================================
*/
function AV_register_jobs_post_type_func() {

	$labels = array(
		'name' 				=> _x('Jobs', 'plural'),
		'singular_name' 	=> _x('Job', 'singular'),
		'menu_name' 		=> _x('Jobs', 'admin menu'),
		'name_admin_bar' 	=> _x('Jobs', 'admin bar'),
		'add_new' 			=> __('Hinzufügen', 'add new'),
		'add_new_item' 		=> __('Job hinzufügen'),
		'new_item' 			=> __('New Job'),
		'edit_item' 		=> __('Job bearbeiten'),
		'view_item' 		=> __('Jobs ansehen'),
		'all_items' 		=> __('Alle Jobs'),
		'search_items' 		=> __('Job suchen'),
		'not_found' 		=> __('Keine Jobs gefunden.'),
	);

	$args = array(
		'labels' 			=> $labels,
		'public' 			=> true,
		'query_var' 		=> true,
		//'rewrite' 			=> array('slug' => 'jobs'),
		'capability_type'   => 'post',
		'has_archive' 		=> false,
		'hierarchical' 		=> false,
		'menu_position'     => 5,
		'menu_icon'   		=> 'dashicons-businessman',
		'supports' 			=> array('title','editor','thumbnail','excerpt','page-attributes'),
		'show_in_rest'      => true
	);

	register_post_type('jobs', $args);

    register_taxonomy( 'jobs_cat', 'jobs', array(
	        'label'        => __( 'Kategorien' ),
	        'public'       => true,
	        'hierarchical' => true,
    	) 
	);
}
add_action('init', 'AV_register_jobs_post_type_func');


/*
    =============================================
    Enqueue jobs scripts and styles
    =============================================
*/
function AV_enqueue_scripts_jobs_func() {    
    wp_register_style('jobs', get_stylesheet_directory_uri() . '/includes/jobs/css/jobs.css');
    wp_enqueue_style('jobs');
}
add_action('wp_enqueue_scripts', 'AV_enqueue_scripts_jobs_func');


/*
    =============================================
    Jobs slider shortcode
    =============================================
*/
function AV_jobs_shortcode_func($atts) {

    ob_start();

    wp_enqueue_script( 'avia-module-isotope', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/portfolio/isotope.min.js', array( 'avia-shortcodes' ), Avia_Builder()->get_theme_version() , true );

    $a = shortcode_atts( array(
		'layout' => 'grid',
		'filter' => '',
		'cat' => '',
	), $atts );

	$layout = $a['layout'];
	$filter = $a['filter'];
	$cat = $a['cat'];

    $args = array(
	    'post_type' => 'jobs',
	    'posts_per_page' => -1,
	);

	if (!empty($cat)) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'jobs_cat',
				'field' => 'slug',
				'terms' => explode(',', $cat),
			),
		);
	}

	$jobs_query = new WP_Query( $args ); ?>
	 
	<?php if ( $jobs_query->have_posts() ) : ?>

		<div class="__jobs">
	 	
		 	<?php if ( $filter == 'true'  ) : ?>
			 	<?php 
		        $terms = get_terms( array( 
		            'taxonomy' => 'jobs_cat',
		            'hide_empty' => true
		        ) ); 
		        ?>
			 	<div class="__jobs--filter">
		            <div class="button-group filter-button-group">
		              <a href="#" class="active" data-filter="*">Alle</a>
		                <?php
		                if ( $terms && ! is_wp_error( $terms ) ){
		                    foreach ( $terms as $term ) {
		                        echo '<a href="#" data-filter=".' . $term->slug . '">' . $term->name . '</a>';
		                    }                 
		                }
		                ?>
		            </div>
		        </div>
		    <?php endif; ?>

		 	<div class="__jobs--wrap" data-layout="<?php echo $layout; ?>">
		 		<div class="grid-sizer"></div>
		 		<div class="gutter-sizer"></div>
			    <?php while ( $jobs_query->have_posts() ) : $jobs_query->the_post(); ?>

			    	<?php 
		            $terms = get_the_terms( get_the_ID(), 'jobs_cat' ); 
		            $jobs_cat_class_list = '';

		            if ( $terms && ! is_wp_error( $terms ) ){
		                $jobs_cat = array();
		                foreach ( $terms as $term ) {
		                    $jobs_cat[] = $term->slug;
		                }                 
		                $jobs_cat_class_list = join( " ", $jobs_cat );
		            }
		            ?>
			    	<a href="<?php the_permalink(); ?>" class="jobs-entry noLightbox <?php if($jobs_cat_class_list) echo $jobs_cat_class_list; ?>">
			    		<?php if (has_post_thumbnail()) : ?>
		                    <figure>
		                        <?php the_post_thumbnail(); ?>
		                    </figure>
		                <?php endif; ?>
		                <div class="__content">
			                <?php the_title('<h4>', '</h4>'); ?>
			                <?php
			                $excerpt = get_the_excerpt(); 
			                if($excerpt){
								echo '<div class="__excerpt"><p>' . substr( $excerpt, 0, 120 ) . '</p></div>';
							}
			                ?>
		                </div>
			    	</a>

			    <?php endwhile; ?>
			    <?php wp_reset_postdata(); ?>

		    </div>

		    <script>
		    	document.addEventListener('DOMContentLoaded', () => {

					var iso = new Isotope( '.__jobs--wrap', {
					  	itemSelector: '.jobs-entry',
					  	masonry: {
					    	columnWidth: '.grid-sizer',
					    	gutter: '.gutter-sizer',
					    	horizontalOrder: true
					  	}
					});

	                const filterBtnGroup = document.querySelector('.__jobs--filter .filter-button-group');
		            if(filterBtnGroup){
		                filterBtnGroup.addEventListener('click', (e) => {
		                  e.preventDefault();
		                  if ( matchesSelector( e.target, 'a' ) ) {
		                    const filterValue = e.target.getAttribute('data-filter');
		                    iso.arrange({ filter: filterValue });
		                  }
		                });

		                const buttonGroups = document.querySelectorAll('.button-group');
		                buttonGroups.forEach(function(buttonGroup) {
		                  buttonGroup.addEventListener('click', (e) => {
		                    if ( matchesSelector( e.target, 'a' ) ) {
		                      const buttonGroup = e.currentTarget;
		                      const activeSort = buttonGroup.querySelector('.active');
		                      if (activeSort) {
		                        activeSort.classList.remove('active');
		                      }
		                      e.target.classList.add('active');
		                    }
		                  });
		                });
	            	}

		    	});
		    </script>

		</div>

	<?php else : ?>
	    <p><?php _e( 'Sorry, no jobs found..' ); ?></p>
	<?php endif; ?>

    <?php
    return ob_get_clean();
}
add_shortcode('jobs','AV_jobs_shortcode_func');


/*
    =============================================
    WP Jobs Redirects
    =============================================
*/
function AV_jobs_redirect_func(){
    if ( is_tax('jobs_cat') ){
        wp_redirect( home_url(), 301 );
        die;
    }
}
add_action( 'template_redirect', 'AV_jobs_redirect_func' );


/*
    =============================================
    Advanced Layout Builder -> jobs
    =============================================
*/
add_filter('avf_alb_supported_post_types', function ($array) {
    $array[] = 'jobs';
    return $array;
});