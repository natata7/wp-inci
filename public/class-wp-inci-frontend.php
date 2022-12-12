<?php
/**
 * Wp_Inci_Frontend
 *
 * @category Plugin
 * @package  Wpinci
 * @author   chyta
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL 3
 */

if (! class_exists('Wp_Inci_Frontend', false) ) {
    /**
     * Frontend Class
     *
     * @category Plugin
     * @package  Wpinci
     * @author   chyta
     * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL 3
     */
    class Wp_Inci_Frontend extends WP_Inci
    {

        /**
         * A static reference to track the single instance of this class.
         */
        private static $_instance;

        /**
         * Constructor.
         */
        public function __construct()
        {
            ( WP_Inci::getInstance() )->__construct();
            $this->init();
        }

        /**
         * Standard init
         *
         * @return void
         */
        public function init()
        {

            /**
             * Load the plugin text domain for frontend translation.
             */
            load_plugin_textdomain(
                'wp-inci', false,
                dirname(plugin_basename($this->plugin_file)) . '/languages/' 
            );

            /**
             * Add CSS into queue, add content filter for ingredients table and product shortcode.
             */
            add_action(
                'wp_enqueue_scripts',
                array( $this, 'wiEnqueueStyle' )
            );
            add_filter(
                'the_content', array( $this, 'wiContentIngredients' ),
                10, 1 
            );
            add_action('init', array( $this, 'wiAddProductShortcode' ));
        }

        /**
         * Method used to provide a single instance of this class.
         *
         * @return Wp_Inci_Frontend|null
         */
        public static function getInstanceFrontend(): ?Wp_Inci_Frontend
        {

            if (null === self::$_instance ) {
                self::$_instance = new Wp_Inci_Frontend();
            }

            return self::$_instance;
        }

 /**
         * Load the plugin text domain for translation.
         *
         * @return void
         */
        public function wiEnqueueStyle(): void
        {

            $disable_style = cmb2_get_option(
                'wi_settings',
                'wi_disable_style' 
            );

            wp_enqueue_style(
                'wp-inci',
                esc_url(plugins_url('css/wp-inci.min.css', __FILE__)) 
            );

            if ($disable_style == 'on' ) {
                wp_dequeue_style('wp-inci');
            }


        }
        
        /**
         * Gets the HTML for a single ingredient.
         *
         * @param int    $ingredient Ingredient ID
         * @param string $safety     Show safety
         *
         * @return false|string
         */
        public function getIngredient(
            int $ingredient,
            string $safety = 'true'
        ) {
            $output = false;
            $post   = get_post($ingredient);

			if (null !== $post ) {
                $functions      = '';
                $functions_list = get_the_terms($post->ID, 'functions');
                if ($functions_list && ! is_wp_error($functions_list) ) {
                    $functions = ' (' . implode(
                        ' / ',
                        wp_list_pluck($functions_list, 'name') 
                    ) . ')';
                }
				
				
				$role      = get_post_meta ( $post->ID, 'role', true );
				switch ($role){
				case ('1') :
				    $role_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Brightening-1cd9c2f5dcdd9edb1d023dc46c290121b78bc0db1f584eeba19c848d94d3756a.png" class="img-effects"><b>Освітлення</b></span>';
				break;
				case ('2') : 
				    $role_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Promotes%20Wound%20Healing-e7a95d5590e806b332559c604d2a08f9a0b23aa88046873f0839a7495d7789f0.png" class="img-effects"><b>Заживлення</b></span>';
				break;
				case ('3') : 
				    $role_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Anti-Aging-cd7044b572861dda33a4a0864e40999dacb6d34b942c19552b97209ce49ecf89.png" class="img-effects"><b>Антивікове</b></span>';
				    break;
				case ('4') : 
				    $role_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Acne-Fighting-c4a8124526ac47a08077940f801808fdd24dea70946dfc7fa302db9a08bb23a5.png" class="img-effects"><b>Від акне</b></span>';
				    break;
				case ('5') : 
				    $role_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/UV%20Protection-ce76a212f25a8942783f172ce9e87b3d4f3fa9abb72aa063650e904761211e39.png" class="img-effects"><b>Захист від сонця</b></span>';
				    break;
				}
				
				$skin_dry      = get_post_meta ( $post->ID, 'dry_skin', true );
				if ($skin_dry == 'yes') {
				    $skin_dry_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Good%20for%20Dry%20Skin-d2dfef08fa92d1495df55c3a365f6575defb82902ec61a676174db23cd21ec24.png">Рекомендовано для сухой кожи</span>';
				}else if ($skin_dry == 'no') {
				    $skin_dry_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Bad%20for%20Dry%20Skin-5c4f0f54faaa4703fc34c9538fc06db6b010aaf540640af3318dba340848ac4a.png">Не рекомендовано для сухої шкіри</span>';
				}
				
				$skin_oil      = get_post_meta ( $post->ID, 'oil_skin', true );
				if ($skin_oil == 'yes') {
				    $skin_oil_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Good%20for%20Oily%20Skin-5cca69bb625f0c5b867a587dc7bce6ea3cbf84a0a2eb09e5fbfbcb678b146ef6.png">Рекомендовано для жирної шкіри / схильної до акне</span>';
				}else if ($skin_oil == 'no') {
				    $skin_oil_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Bad%20for%20Oily%20Skin-daa833ccc00dc766805ecb5bf27ab86ec32ef740872d2e6bce6c3ed5ba3329f1.png">Не рекомендовано для жирної шкіри / схильної до акне</span>';
				}
				
				$skin_irritation      = get_post_meta ( $post->ID, 'irritation_skin', true );
				if ($skin_irritation == 'yes') {
				    $skin_irritation_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Good%20for%20Sensitive%20Skin-5ecac4dd1520ccf1437588c0a3f6d2c77d241053ef973fddaa051e2ce466c328.png">Рекомендовано для чутливої шкіри</span>';
				}else if ($skin_irritation == 'no') {
				    $skin_irritation_value = '<span class="badge"><img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Bad%20for%20Sensitive%20Skin-eeecb376f90cb387581dcd88ff76db89c02efe7bcbefd35404a947bc4c979960.png">Не рекомендовано для чутливої шкіри</span>';
				}
				$comedohenity      = get_post_meta ( $post->ID, 'comedohenity', true );
				$irritancy      = get_post_meta ( $post->ID, 'irritancy', true );

				
				$output = '<tr><td>';
                $output .= ( WP_Inci::getInstance() )->getSafetyHtml($post->ID);
                $output .= '</td><td>';
                
				//$output .= $post->post_title;
				$output .= '<a title="' . $post->post_title . '" href="' . get_permalink( $post->ID) . '">';
				$output .= $post->post_title;
				$output   .= '</a><small class="functions">';
				$output .= $functions;
				$output .= '</small></td><td>';
				$output .= $role_value;
				$output .= '';
				$output .= $skin_dry_value . $skin_oil_value . $skin_irritation_value;
				$output .= '</td><td>';
				$output .= $comedohenity;
				$output .= '</td><td>';
				$output .= $irritancy;
				$output .= '</td></tr>';
			}

			return $output;
		}

		/**
         * Gets the HTML for all ingredients.
         *
         * @param int    $post_id Post ID
         * @param string $safety  Show safety
         *
         * @return string
         */
        public function getIngredientsTable(
            int $post_id,
            string $safety = 'true'
        ): string {
            $output      = '';
            $ingredients = get_post_meta($post_id, 'ingredients', true);
            if (! empty($ingredients) ) {
                $output .= '<table class="wp-inci"><tbody>';
				$output .= '<tr><td>';
                $output .= __('Рейтинг безпеки', 'wp-inci');
                $output .= '</td><td>';
                $output .= __('Інгредієнти', 'wp-inci');
                $output .= '</td><td></td><td>';
                $output .= __('Комедогенність', 'wp-inci');
                $output .= '</td><td>';
                $output .= __('Подразнюючість', 'wp-inci');
                $output .= '</td></tr>';
                
				foreach ( $ingredients as $ingredient ) {
                    $output .= $this->getIngredient($ingredient, $safety);
                }

				$output .= '</tbody></table>';
			}

			$may_contain = get_post_meta($post_id, 'may_contain', true);
            if (! empty($may_contain) ) {
                $output .= '<h4>' . __('MAY CONTAIN', 'wp-inci') . '</h4>';
                $output .= '
				<table class="wp-inci">
						<tbody>';
                foreach ( $may_contain as $may ) {
                    $output .= $this->getIngredient($may, $safety);
                }

                $output .= '</tbody>
					</table>';
            }

			$output .= '<div class="disclaimer">' . cmb2_get_option(
                'wi_disclaimer',
                'textarea_disclaimer',
                $this->getDefaultDisclaimer()
            ) . '</div>';

            return $output;
        }
		
		/**
		 * Gets the analiz for all ingredients.
		 *
		 * @param $post_id
		 *
		 * @return string
		 */
		 
		 public function get_analiz_sulfate ( $ingredient ) {
			$output = false;
			$post   = get_post( $ingredient );
			
			if ( null !== $post ) {
				$it_is_sulfate = get_post_meta ( $post->ID, 'it_is_sulfate', true );
				return $it_is_sulfate;
			}	

		}
		
		public function get_analiz_alcohol ( $ingredient ) {
			$output = false;
			$post   = get_post( $ingredient );
			
			if ( null !== $post ) {
				$it_is_alcohol = get_post_meta ( $post->ID, 'it_is_alcohol', true );
				return $it_is_alcohol;
			}
			
		}
		
		public function get_analiz_paraben ( $ingredient ) {
			$output = false;
			$post   = get_post( $ingredient );
			
			if ( null !== $post ) {
				$it_is_paraben = get_post_meta ( $post->ID, 'it_is_paraben', true );
				return $it_is_paraben;
			}
			
		}
		
		public function get_analiz_silicone ( $ingredient ) {
			$output = false;
			$post   = get_post( $ingredient );
			
			if ( null !== $post ) {
				$it_is_silicone = get_post_meta ( $post->ID, 'it_is_silicone', true );
				return $it_is_silicone;
			}
			
		}
		 
		 
		public function get_analiz_table( $post_id ): string {
			$output      = '';
			
			$ingredients = get_post_meta( $post_id, 'ingredients', true );
			if ( ! empty( $ingredients ) ) {
                $it_is_alcohol_value = 2;
                $it_is_alcohol_output;
                $it_is_sulfate_value = 2;
                $it_is_sulfate_output;
                $it_is_paraben_value = 2;
                $it_is_paraben_output;
                $it_is_silicone_value = 2;
                $it_is_silicone_output;
				$output .= '
				<div class="row">';
				
				
				foreach ( $ingredients as $ingredient ) {
				    
				    $it_is_paraben_value = $this->get_analiz_paraben( $ingredient );
				    
				    if ($it_is_paraben_value == '1') {
				        break;
			        }
				    
				};
				
				switch ($it_is_paraben_value) {
				    case '1' :
				        $it_is_paraben_output = '<span class="false">';
                        $it_is_paraben_output .= __('Містить парабени', 'wp-inci');
                        $it_is_paraben_output .= '</span>';
				        break;
				    case '2' :
                        $it_is_paraben_output = '<span class="true">';
                        $it_is_paraben_output .= __('Без парабенів', 'wp-inci');
                        $it_is_paraben_output .= '</span>';
				        break;
				}
				
				
				foreach ( $ingredients as $ingredient ) {
				    
				    $it_is_silicone_value = $this->get_analiz_silicone( $ingredient );
				    
				    if ($it_is_silicone_value == '1') {
				        break;
			        }
				    
				};
				
				switch ($it_is_silicone_value) {
				    case '1' :
                        $it_is_silicone_output = '<span class="false">';
                        $it_is_silicone_output .= __('Містить силікони', 'wp-inci');
                        $it_is_silicone_output .= '</span>';
				        break;
				    case '2' :
				        $it_is_silicone_output = '<span class="true">';
                        $it_is_silicone_output .= __('Без силіконів', 'wp-inci');
                        $it_is_silicone_output .= '</span>';
				        break;
				}
				
				foreach ( $ingredients as $ingredient ) {
				    
				    $it_is_alcohol_value = $this->get_analiz_alcohol( $ingredient );
				    
				    if ($it_is_alcohol_value == '1') {
				        break;
			        }
				    
				};
				
				switch ($it_is_alcohol_value) {
				    case '1' :
				        $it_is_alcohol_output = '<span class="false">';
                        $it_is_alcohol_output .= __('Містить алкоголь', 'wp-inci');
                        $it_is_alcohol_output .= '</span>';
				        break;
				    case '2' :
				        $it_is_alcohol_output = '<span class="true">';
                        $it_is_alcohol_output .= __('Без алкоголю', 'wp-inci');
                        $it_is_alcohol_output .= '</span>';
				        break;
				}
				
				foreach ( $ingredients as $ingredient ) {
				    
				    $it_is_sulfate_value = $this->get_analiz_sulfate( $ingredient );
				    
				    if ($it_is_sulfate_value == '1') {
				        break;
			        }
				    
				};
				
				switch ($it_is_sulfate_value) {
				    case '1' :
				        $it_is_sulfate_output = '<span class="false">';
                        $it_is_sulfate_output .= __('Містить сульфати', 'wp-inci');
                        $it_is_sulfate_output .= '</span>';
				        break;
				    case '2' :
				        $it_is_sulfate_output = '<span class="true">';
                        $it_is_sulfate_output .= __('Без сульфатів', 'wp-inci');
                        $it_is_sulfate_output .= '</span>';
				        break;
				}
				
				$output .= '<div class="col-md-3 my-2">';
				$output .= $it_is_paraben_output;
				$output .= '</div><div class="col-md-3 my-2">';
				$output .= $it_is_silicone_output;
				$output .= '</div><div class="col-md-3 my-2">';
				$output .= $it_is_sulfate_output;
				$output .= '</div><div class="col-md-3 my-2">';
				$output .= $it_is_alcohol_output;
				$output .= '</div>';

				$output .= '</div>';
			}
			return $output;
		}

		/**
         * Show the ingredients table into product content.
         *
         * @param string $content Post content
         *
         * @return string $content
         */
        public function wiContentIngredients( string $content ): string
        {
			global $post;
			$output = '';

			if ( is_singular() && is_main_query() ) {
				if ( $post->post_type == 'products' ) {
					$output = '<div class="wp-inci">' . $this->get_analiz_table( $post->ID ) . '</div>';
					$output .= '<div class="wp-inci">' . $this->getIngredientsTable( $post->ID ) . '</div>';
				}

				return $content . $output;
			}

			return $content;
		}

		/**
         * Add the product shortcode.
         *
         * @return void
         */
        public function wiAddProductShortcode(): void
        {
            if (! shortcode_exists('wp_inci_product') ) {
                add_shortcode(
                    'wp_inci_product',
                    array( $this, 'wiProductShortcode' )
                );
            }
        }

        /**
         * Set up the shortcode to show the product.
         *
         * @param array  $atts      Shortcode attributes
         * @param string $content   Post content
         * @param string $shortcode Shortcode name
         *
         * @return string
         */
        public function wiProductShortcode(
            array $atts,
            string $content,
            string $shortcode
        ): string {

            // Example: [wp_inci_product id="33591" title="My custom title" link="true" list="false" safety="false"]
            // Basic use: [wp_inci_product id="33591"]

            // Normalize attribute keys, lowercase.
            $atts = array_change_key_case($atts);

            // Sets shortcode attributes with defaults.
            $atts = shortcode_atts(
                array(
                'id'     => 0,
                'title'  => '',
                'link'   => 'false',
                'list'   => 'true',
                'safety' => 'true',
                ),
                $atts,
                $shortcode
            );

            $output = '';

            if (0 !== $atts['id'] ) {

                $output .= '<div class="wp-inci">';

                $start = '<h3>';
                $end   = '</h3>';
                $title = esc_html(get_the_title($atts['id']));

                if ('' !== $atts['title'] ) {
                    $title = esc_html($atts['title']);
                }

                if ('true' === $atts['link'] ) {
                    $start = '<h3><a title="' . $title . '" href="' . get_permalink($atts['id']) . '">';
                    $end   = '</a></h3>';
                }

				$output .= $start . $title . $end;

                if ('true' === $atts['list'] ) {
                    $output .= $this->getIngredientsTable(
                        $atts['id'],
                        $atts['safety'] 
                    );
                }

                if ('' !== $content ) {
                    // Secure output by executing the_content filter hook on $content.
                    $output .= apply_filters('the_content', $content);

                    // Run shortcode parser recursively.
                    $output .= do_shortcode($content);
                }

                $output .= '</div>';
            }

            // Remove paragraphs around shortcode before output.
            return shortcode_unautop($output);
        }

    }

    add_action(
        'plugins_loaded',
        array( 'Wp_Inci_Frontend', 'getInstanceFrontend' )
    );
}
