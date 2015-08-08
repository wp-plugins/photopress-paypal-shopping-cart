<?php

/*
Plugin Name: PhotoPress - Paypal Shopping Cart
Plugin URI: Permalink: http://www.photopressdev.com
Description: Dynamicaly adds shopping cart functionality to Image Attachments, Posts, or Pages. Utilizes the WordPress Simple Paypal Shopping Cart Plugin. 
Author: Peter Adams
Version: 1.7
Author URI: http://www.photopressdev.com 
*/


class papt_spsc {
	
	static $admin_notices = array();
	static $enabled;
	
	static function init() {
		
		papt_spsc::registerTaxonomies();
		
		add_action('admin_notices', array( 'papt_spsc', 'admin_notices' ) );
		
		// test for dependant WordPress Simple Shopping Cart Plugin
		
		
		if ( ! papt_spsc::checkforWSPSC() ) {
			self::addAdminNotice(
				sprintf(
					__('PhotoPress Simple Paypal Shopping Cart relies on the <a href="%s">Wordpress Simple Paypal Shopping Cart</a>, please install this plugin.', 'papt_spsc'), 'http://wordpress.org/extend/plugins/wordpress-simple-paypal-shopping-cart/'
				),
			'error');
		}
	}
	
	static function checkforWSPSC() {
		
		// test for dependant WordPress Simple Shopping Cart Plugin
		self::$enabled = function_exists('shopping_cart_show');
		return self::$enabled;
	}
	
	static function registerDependantActions() {
		
		if ( papt_spsc::checkforWSPSC() ) {
		
			add_action( 'wp_head', array('papt_spsc', 'outputFormHandlerJs' ), 99 );
			add_shortcode( 'papt_spsc', array('papt_spsc', 'singleProductShortcodeHandler' ) );
			add_action( 'widgets_init', array('papt_spsc', 'widgetsInit' ) );
		}
	}
	
	static function widgetsInit() {
		
		register_widget( 'papt_displaySingleProductBuyButtonWidget' );
	}
	
	/**
	 * Append a message of a certain type to the admin notices.
	 *
	 * @param string $msg 
	 * @param string $type 
	 * @return void
	 */
	static function addAdminNotice( $msg, $type = 'updated' ) {
	
		self::$admin_notices[] = array(
			'type' => $type == 'error' ? $type : 'updated', // If it's not an error, set it to updated
			'msg' => $msg
		);
	}
	
	
	/**
	 * Displays admin notices 
	 *
	 * @return void
	 */
	static function admin_notices() {
		
		if ( is_array( self::$admin_notices ) ) {
		
			foreach ( self::$admin_notices as $notice ) {
				extract( $notice );
				?>
				<div class="<?php echo esc_attr($type); ?>">
					<p><?php echo $msg; ?></p>
				</div><!-- /<?php echo esc_html($type); ?> -->
				<?php
			}
		}
	}
		
	/**
	 * Purchase Form Handler
	 *
	 * This Javascript managed variation selection in purchase widget.
	 *
	 */
	static function outputFormHandlerJs() {
		
		echo '
		<script type="text/javascript">
			<!--
			//

		var photopressShoppingCart = {
		
			updateFields : function( selected ) {
				
				jQuery("#photopress-spsc-product_name").val( selected.attr("product_name") + " (" + selected.attr("variation_name") + ")" );
				jQuery("#photopress-spsc-price").val( selected.attr("price") );
				jQuery("#photopress-spsc-shipping").val( selected.attr("shipping") );	
				jQuery("#photopress-spsc-hash_one").val( selected.attr("hash_one") );

			}
		};
		
		
		// event handlers
		jQuery( function() {
		
			jQuery(".wp-cart-button-form > select").change(function(){
				
				var selected = jQuery(this).find("option:selected");
				photopressShoppingCart.updateFields( selected );			
			});
			
			jQuery(".wp-cart-button-form").submit(function(){
				
				var selected = jQuery(".wp-cart-button-form > select").find("option:selected");
				photopressShoppingCart.updateFields( selected );
			});
		});
		//-->
		</script>';
	}
	
	/**
	 * DEPRICATED
	 *
	 */
	static function showSingleProductBuyButton($product_name = '', $base_price = 0, $shipping = 0, $variations = array() ) {
		
		if ( ! $product_name ) {
			
			$product_name = get_the_title($post->ID);
		}
		
		if ( $variations ) {
	 		
		 	$variations_string = papt_spsc::createVariationsString( $variations );
		 	
		 	$product_string = sprintf( "[wp_cart:%s:price:%s:shipping:%s%s:end]", $product_name, $base_price, $shipping, $variations_string );
		 	//echo print_wp_cart_button_new('[wp_cart:Demo Product 1:price:15:shipping:2:var1[Size|Small:200|Medium:300|Large:400]:var2[Color|Red|Green]:end]');
		 } else {
			 
			$product_string = sprintf( "[wp_cart:%s:price:%s:shipping:%s:end]", $product_name, $base_price, $shipping );
		 	 
		 }
	 
	 	echo print_wp_cart_button_new($product_string);
	}
	
	/**
	 * Generates The purchase options form
	 *
	 * @param $product_name	string	the name of the product to be purchased
	 * @param $variations	array	an array of product variations
	 */
	static function generateForm ($product_name, $variations = array(), $shipping = 0, $echo = false ) {
	
		$addToCartLabel = get_option('addToCartButtonName');   
	    
	    if (!$addToCartLabel || ($addToCartLabel == '') ) {
	    	$addToCartLabel = __("Add to Cart", "WSPSC");
		}
		
		$form = '';
			
		$form .= '<div class="wp_cart_button_wrapper">';
		//$form .= '<form method="post" class="wp-cart-button-form" action="" style="display:inline" onsubmit="return ReadForm(this, true);">';
		$form .= '<form method="post" class="wp-cart-button-form" action="" style="display:inline" onsubmit="return;">';
		$form .= 'Print Sizes/Types :';
		//$form .= '<select name="variation1" onchange="ReadForm (this.form, false);">';
		$form .= '<select name="variation1" onchange="">';
		// private key used to do price validation
		$p_key = get_option( 'wspsc_private_key_one' );

		if ( empty( $p_key ) ) {
		
            $p_key = uniqid();
            update_option( 'wspsc_private_key_one', $p_key );
        }
        
        $hash_one = '';
		
		foreach ( $variations as $category => $variation ) {
			
			$options = '';
			
			foreach ($variation as $v) {
				
				$options .= sprintf(
				
					'<option value="%s" product_name= "%s" variation_name="%s" price="%s" shipping="%s" hash_one="%s">%s</option>', 
					$v['name']. ': '.$v['price'], 
					$product_name,
					$v['name'],
					$v['price'], 
					$v['shipping'],
					md5( $p_key. '|' . $v['price'] ),
					$v['name']. ': '.$v['price']
				);
			} 
			
			
			$form .= sprintf('<optgroup label="%s">%s</optgroup>', $category, $options);
		}
			
		$form .= '</select>';
		$form .= '<br />';
		$form .= sprintf('<input type="submit" value="%s" />', $addToCartLabel);
		$form .= sprintf('<input id="photopress-spsc-product_name" type="hidden" name="wspsc_product" value="%s" />', $product_name );
		$form .= '<input id="photopress-spsc-price" type="hidden" name="price" value="0" />';
		$form .= sprintf('<input type="hidden" name="product_tmp" value="%s" />', $product_name );
		$form .= sprintf('<input id="photopress-spsc-product_shipping" type="hidden" name="shipping" value="%s" />', $shipping ); 
		// this hidden field is used for price validation and needs to be 
		// set by javascript based on the variation selected
        $form .= sprintf('<input id="photopress-spsc-hash_one" type="hidden" name="hash_one" value="%s" />', $hash_one );
		$form .= sprintf('<input type="hidden" name="cartLink" value="%s" />', cart_current_page_url() );
		$form .= '<input type="hidden" name="addcart" value="1" />';
		$form .= '</form>';
		$form .= '</div>';
		
		if ( $echo ) {
			echo $form;
		} else {
			return $form;
		}
	}
	
	/**
	 * DEPRECATED
	 *
	 */
	static function createVariationsString( $variation_sets ) {
		
		$vstr = '';
		
		foreach ( $variation_sets as $set_number => $set ) {
		
			if ( ! isset( $set['label'] ) ) {
				
				$set['label'] = 'Select a variation';
			}
			
			if ( isset( $set['options'] ) ) {
				
				$options_str = '';
				
				foreach ( $set['options'] as $option => $price ) {
					
					$options_str .= sprintf( '|%s:%s', $option, $price );	
				}
				
			} else {
				
				continue;
			}
			
			$vstr .= sprintf(':var%s[%s%s]', $set_number+1, $set['label'], $options_str );
		}
		
		return $vstr;
	}
	
	/**
	 * Construct Purchase Options for a Single Product
	 *
	 * @param $atts	array	an array of param passed in by the widget instance.
	 * @return mixed
	 */
	static function singleProductShortcodeHandler( $atts ) {
		
		global $post;
		
		extract( shortcode_atts( array(
	      'product_name' 	=> '',
	      'base_price' 		=> 0,
	      'shipping'		=> 0,
	      'variations'		=> array(),
	      'variations_label'	=>	'Variations',
	      'taxonomy'		=>	'photos_purchase_variations'
	    ), $atts ) );
	    
	    $vars = array();
	    
	     
	    // get variations from a taxonomy
	    if ( $taxonomy ) {
		   	
			// check for purchase options associated with image
			$terms = get_the_terms( $post->ID, $taxonomy );
			
			if ( ! $terms ) {
				
				$options = get_option('photopress_spsc_option_name');
				
				// Fetch global variations unless told not to.
				if ( ! isset( $options['explicit_mode'] ) ) {
					// get all purchase options
					
					$terms = get_terms( $taxonomy, array(
						
						'hierarchical'  => true,
						'hide_empty'	=> false // even terms not yet assigned
						
					));
					
				}
			}
			
		    if ( $terms ) {
			    
			    // cache of parent terms
			    $parent_groups = array();
			    
				$tax_variations = '';			    
			    
			    // loop through terms and organize by parent
			    foreach ( $terms as $term ) {
			    	
			    	// ttest to se if the term is a valid variation
			    	if ( strpos($term->name, ':') ) {
						
						$args = array();
							
				    	if ( $term->parent != 0 && ! isset( $parent_groups[ $term->parent ] ) ) {
					    	
					    	$parent_groups[ $term->parent ] = get_term( $term->parent, $taxonomy );
				    	}
				    	
					    // if there is a parent add a 'group' attr to the option array
					    // if 0 is specified on the object then there is no parent
					    if ( $term->parent != 0 ) {
						    $parent_obj = $parent_groups[ $term->parent ];
							$args['group'] = $parent_obj->name;
						
						// check for valid purchase options that have no parent    
					    } elseif ( $term->parent === 0 && strpos($term->name, ':') ) {
					    
							$args['group'] = 'nogroup';
					    }
					    
					    // if the purchase option is valid then explode it for parts
						$parts = explode( ":", $term->name );
						
						// set variation name
						if ( isset( $parts[0] ) ) {
				    		$args['name'] = trim( $parts[0] );
				    	} else {
				    		$args['price'] = "";
				    	}
						
						// set price
				    	if ( isset( $parts[1] ) ) {
				    		$args['price'] = trim( $parts[1] );
				    	} else {
				    		$args['price'] = "";
				    	}
				    	
				    	//set shipping
				    	if ( isset( $parts[1] ) ) {
				    		$args['shipping'] = trim( $parts[2] );
				    	} else {
				    		$args['shipping'] = "";
				    	}	
						
						$tax_variations[ $args['group'] ][ $term->term_id ] = $args;
						
				    } 
				}
					
				$variations = $tax_variations;
				
				return papt_spsc::generateForm( $product_name, $variations, $shipping);
			
			} else {
		    // backwards compatability for old widget option
		    // look for global variations passed in from from widget
		    
			    if ( $variations ) {
					//split on | for variation
			        $variations = explode( "|", $variations);
			        
					// parse variations for prices
				    if ( $variations ) {
					    
					    $va = array();
					    
				        // split on : for price
				    	foreach ( $variations as $variation ) {
					    	
					    	$variation = explode( ":", $variation );
					    	
					    	if ( isset( $variation[1] ) ) {
					    		$vars[ trim( $variation[0] ) ] = trim( $variation[1] );
					    	} else {
					    		// use base price if no variation price exists
						    	$vars[ trim( $variation[0] ) ] = "";
					    	}
				    	}
				    
				    }
				    
				    $va[] = array('label' => $variations_label, 'options' => $vars);
				    
				    return papt_spsc::showSingleProductBuyButton( $product_name, $base_price, $shipping, $va );			        
			    }
			}
		}
	}
	
	/**
	 * Register Purchase Options Custom Taxonomy
	 *
	 */
	static function registerTaxonomies() {
		
		$labels = array(
		'name'                       => _x( 'Photo Purchase Variations', 'taxonomy general name' ),
		'singular_name'              => _x( 'Photo Purchase Variation', 'taxonomy singular name' ),
		'search_items'               => __( 'Search Photo Purchase Variations' ),
		'popular_items'              => __( 'Popular Purchase Variations' ),
		'all_items'                  => __( 'All Photo Purchase Variations' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Purchase Variation' ),
		'update_item'                => __( 'Update Purchase Variation' ),
		'add_new_item'               => __( 'Add New Purchase Variation' ),
		'new_item_name'              => __( 'New Purchase Variation Name' ),
		'separate_items_with_commas' => __( 'Separate Purchase Variation with commas' ),
		'add_or_remove_items'        => __( 'Add or remove Purchase Variations' ),
		'choose_from_most_used'      => __( 'Choose from the most used Purchase Variations' ),
		'not_found'                  => __( 'No Photo Purchase Variations found.' ),
		'menu_name'                  => __( 'Photo Purchase Variations' ),
	);

		
		
		
		register_taxonomy( 'photos_purchase_variations', 'attachment', array(
							'hierarchical'	 			=> true, 
							'labels' 					=> $labels, 
							'query_var' 				=> 'photos_purchase_variations', 
							'rewrite' 					=> false,
							'update_count_callback'		=> '_update_generic_term_count',
							'show_admin_column' 		=> true,
							'public'					=> true,
							'sort'						=> true )
		);
	}
	
}

class papt_displaySingleProductBuyButtonWidget extends WP_Widget {
	
	/* Set up some default widget settings. */
	static $defaults = array( 
		
		'title' 			=> 'Purchase a Print', 
		'description' 		=> 'Purchase a print of this image using the buy button below', 
		'base_price' 		=> 0, 
		'shipping' 			=> 0,
		'variations_label' 	=> 'Print Sizes/Types',
		'variations'		=> '',
		'taxonomy'			=> '' 
	);

	
	function papt_displaySingleProductBuyButtonWidget() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'papt_displaySingleProductBuyButtonWidget', 'description' => "Display's the Simple Paypal shopping cart buy button for a single image. Can only be used on single image (attachment) pages." );

		/* Widget control settings. */
		//$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'papt-displayTax-widget' );
		$control_ops = array('width' => 400);
		parent::__construct( false, 'PhotoPress - Purchase Image Button', $widget_ops, $control_ops);
	}
	
	function widget( $args, $instance ) {
		
		global $post;
		
		extract( $args );
		
		$product_name = get_the_title( $post->ID );
		//print_r($product_name);
		
		$form = papt_spsc::singleProductShortcodeHandler( 
			array(
				'base_price'		=> 	$instance['base_price'], 
				'shipping'			=> 	$instance['shipping'],
				'variations_label'	=>	$instance['variations_label'], 
				'variations'		=>	$instance['variations'],
				'product_name'		=>	$product_name,
				'post_id'			=>	$post->ID		
			) 
		);
		
		if ( $form ) {
			
			/* User-selected settings. */
			$title = apply_filters('widget_title', $instance['title'] );
			
			/* Before widget (defined by themes). */
			echo $before_widget;
	
			echo "<h2>$title</h2>";	
			echo "<p>".$instance['description']."</p>";		
			
			$product_name = get_the_title($post->ID);
			
			echo $form;
			
			/* After widget (defined by themes). */
			echo $after_widget;

		}
		
		
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$form_fields = array_keys( self::$defaults );
		
		/* Strip tags (if needed) and update the widget settings. */
		foreach ( $form_fields as $field ) {
			
			if ($field === 'description') { 
				$instance[ $field ] = $new_instance[ $field ];

			} else {
			
				$instance[ $field ] = strip_tags( $new_instance[ $field ] );
			}
		}
				
		return $instance;
	}
	
	function form( $instance ) {
		
		$instance = wp_parse_args( (array) $instance, self::$defaults ); 
		
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'description' ); ?>">Description (optional):</label>
			<textarea id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" style="width:100%;" rows="4" ><?php echo $instance['description']; ?></textarea>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'base_price' ); ?>">Base Price:</label>
			<input id="<?php echo $this->get_field_id( 'base_price' ); ?>" name="<?php echo $this->get_field_name( 'base_price' ); ?>" value="<?php echo $instance['base_price']; ?>" type="text" class="widefat" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'shipping' ); ?>">Shipping:</label>
			<input id="<?php echo $this->get_field_id( 'shipping' ); ?>" name="<?php echo $this->get_field_name( 'shipping' ); ?>" value="<?php echo $instance['shipping']; ?>" type="text" class="widefat" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'variations_label' ); ?>">Variations Label (Optional):</label>
			<input id="<?php echo $this->get_field_id( 'variations_label' ); ?>" name="<?php echo $this->get_field_name( 'variations_label' ); ?>" value="<?php echo $instance['variations_label']; ?>" type="text" class="widefat" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'variations' ); ?>">Variations (DEPRECATED):</label>
			<input id="<?php echo $this->get_field_id( 'variations' ); ?>" name="<?php echo $this->get_field_name( 'variations' ); ?>" value="<?php echo $instance['variations']; ?>" type="text" class="widefat" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>">Variations Taxonomy (DEPRECATED):</label>
			<input id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" value="<?php echo $instance['taxonomy']; ?>" style="width:;" type="text" class="widefat" />
		</p>
		
		

		<?php

	}
}

class photopress_spsc_admin_page {
	
	private $options;
	
	public function __construct() {
		
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
		
	}
	
	/**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'PhotoPress', 
            'PP Purchase Variations', 
            'manage_options', 
            'photopress-spsc-admin-page', 
            array( $this, 'create_admin_page' )
        );
    }
    
    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'photopress_spsc_option_name' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>PhotoPress Purchase Variation Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'photopress_spsc_option_group' );   
                do_settings_sections( 'photopress-spsc-admin-page' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

	/**
     * Register and add settings
     */
    public function page_init() {  
          
        register_setting(
            'photopress_spsc_option_group', // Option group
            'photopress_spsc_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Purchase Variation Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'photopress-spsc-admin-page' // Page
        );  

        add_settings_field(
            'explicit_mode', // ID
            'Explicit Mode', // Title 
            array( $this, 'explicit_mode_callback' ), // Callback
            'photopress-spsc-admin-page', // Page
            'setting_section_id' // Section           
        );        
    }
    
    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['explicit_mode'] ) ) {
         
            $new_input['explicit_mode'] = absint( $input['explicit_mode'] );
		}
		
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info() {
    
        print '';
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function explicit_mode_callback() {
    	
    	if ( isset( $this->options['explicit_mode']) && 1 == $this->options['explicit_mode'] ) {
	    		
	    	$checked = 'checked';
    	}
    	
        printf(
            '<input type="checkbox" id="explicit_mode" name="photopress_spsc_option_name[explicit_mode]" value="1" %s /> Only shows variations explicitly set on each image.',
            $checked
        );
    }
    
    
}

if( is_admin() ) {
    $my_settings_page = new photopress_spsc_admin_page();
}

add_action( 'init', array('papt_spsc', 'init' ), 98 );
add_action( 'plugins_loaded', array('papt_spsc', 'registerDependantActions' ) );
register_sidebar(array(
  'name' => 'PhotoPress Image Page Sidebar',
  'id' => 'papt-image-sidebar',
  'description' => 'Widgets in this area will be shown on image (attachment) page templates.'
));
?>