<?php

/*
Plugin Name: PhotoPress - Paypal Shopping Cart
Plugin URI: Permalink: http://www.peteradamsphoto.com/?page_id=3148
Description: Dynamicaly adds shopping cart functionality to Image Attachments, Posts, or Pages. Utilizes the WordPress Simple Paypal Shopping Cart Plugin.
Author: Peter Adams
Version: 1.0
Author URI: http://www.peteradamsphoto.com 
*/


class papt_spsc {
	
	static $admin_notices = array();
	static $enabled;
	
	static function init() {
	
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
	 * This Javascript is a drop in replacment for ReadForm that adds
	 * support for price per variation using a 'variation:price' notation
	 *
	 */
	static function outputFormHandlerJs() {
		
		echo '
		<script type="text/javascript">
			<!--
			//
			var ReadForm = function( obj1, tst ) {
				
			    // Read the user form
		    var i,j,pos;
		    val_total="";val_combo="";		
		
		    for (i=0; i<obj1.length; i++) 
		    {     
		        // run entire form
		        obj = obj1.elements[i];           // a form element
		
		        if (obj.type == "select-one") 
		        {   // just selects
		            if (obj.name == "quantity" ||
		                obj.name == "amount") continue;
			        pos = obj.selectedIndex;        // which option selected
			        val = obj.options[pos].value;   // selected value
			        
			        var val_name = "", val_price = 0;
			        val_pieces = val.split(":");
			        if (val_pieces.length > 1) {
			        	val_name  = val_pieces[0];
			        	val_price = val_pieces[1].substr(1);
			        	obj1.elements["price"].value = val_price;
			        } else {
			        	val_name = val;
			        }
			        
			        //val_combo = val_combo + "(" + val + ")";
			        val_combo = val_combo + "(" + val_name + ")";
			         
		        }
		    }
			// Now summarize everything we have processed above
			val_total = obj1.product_tmp.value + val_combo;
			obj1.product.value = val_total;
			}
			//-->
		</script>';
		
	}
	
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
	
	static function singleProductShortcodeHandler( $atts ) {
		
		extract( shortcode_atts( array(
	      'product_name' 	=> '',
	      'base_price' 		=> 0,
	      'shipping'		=> 0,
	      'variations'		=> array(),
	      'variations_label'	=>	'Variations',
	      'taxonomy'		=>	''
	    ), $atts ) );
	    
	    $vars = array();
	    
	    
	    // look for variations on attr
	    if ( $variations ) {
			//split on | for variation
	        $variations = explode( "|", $variations);
	    }
	    
	    
	    // get variations from a taxonomy
	    if ( ! $variations && $taxonomy ) {
		    
		    $terms = get_the_terms( $post->ID, $taxonomy);
		    
		    if ( $terms ) {
			    
				$variations = '';			    
			    
			    foreach ( $terms as $term ) {
				    $variations[] = $term->name; 
				}
			}
	    }
	    
	    
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
			    	$vars[ trim( $variation[0] ) ] = $base_price;
		    	}
	    	}
	    
	    }
	    
	    $va[] = array('label' => $variations_label, 'options' => $vars);
	    
	    return papt_spsc::showSingleProductBuyButton( $product_name, $base_price, $shipping, $va );
	}
	
}

class papt_displaySingleProductBuyButtonWidget extends WP_Widget {
	
	/* Set up some default widget settings. */
	static $defaults = array( 
		
		'title' 			=> 'Purchase A Print', 
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
		$control_ops = array();
		parent::WP_Widget(false, $name = 'PhotoPress - Purchase Image Button', $widget_ops, $control_ops);
	}
	
	function widget( $args, $instance ) {
		
		extract( $args );
		
		/* User-selected settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		echo "<h2>$title</h2>";	
		echo "<p>".$instance['description']."</p>";		
		
		papt_spsc::singleProductShortcodeHandler( 
			array(
				'base_price'		=> 	$instance['base_price'], 
				'shipping'			=> 	$instance['shipping'],
				'variations_label'	=>	$instance['variations_label'], 
				'variations'		=>	$instance['variations'] 
			) 
		);
		
		
		/* After widget (defined by themes). */
		echo $after_widget.'<br>';
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
			<label for="<?php echo $this->get_field_id( 'variations_label' ); ?>">Variations Label (optional):</label>
			<input id="<?php echo $this->get_field_id( 'variations_label' ); ?>" name="<?php echo $this->get_field_name( 'variations_label' ); ?>" value="<?php echo $instance['variations_label']; ?>" type="text" class="widefat" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'variations' ); ?>">Variations (optional):</label>
			<input id="<?php echo $this->get_field_id( 'variations' ); ?>" name="<?php echo $this->get_field_name( 'variations' ); ?>" value="<?php echo $instance['variations']; ?>" type="text" class="widefat" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>">Variations Taxonomy (optional):</label>
			<input id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" value="<?php echo $instance['taxonomy']; ?>" style="width:;" type="text" class="widefat" />
		</p>
		
		

		<?php

	}
}


add_action( 'init', array('papt_spsc', 'init' ), 98 );
add_action( 'plugins_loaded', array('papt_spsc', 'registerDependantActions' ) );
?>