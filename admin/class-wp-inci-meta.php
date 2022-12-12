<?php

/**
 * WP_Inci_Meta
 *
 * @category Plugin
 * @package  Wpinci
 * @author   chyta
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL 3
 */
if (!class_exists('WP_Inci_Meta', false)) {
    /**
     * Class for Manage Meta Box (back-end)
     *
     * @category Plugin
     * @package  Wpinci
     * @author   chyta
     * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL 3
     */
    class WP_Inci_Meta extends WP_Inci
    {

        /**
         * A static reference to track the single instance of this class.
         */
        private static $_instance;

        /**
         * Constructor.
         *
         * @since 1.0
         */
        public function __construct()
        {
            (WP_Inci::getInstance())->__construct();
            $this->init();
            $this->url = plugins_url("", __FILE__);
        }

        /**
         * Standard init.
         *
         * @return void
         */
        public function init()
        {
            /**
             * Include and setup custom meta boxes and fields.
             */
            add_action('cmb2_admin_init', array($this, 'registerSourceUrl'));
            add_action('cmb2_admin_init', array($this, 'registerIngredientsRepeater'));
            add_action('cmb2_admin_init', array($this, 'registerSafetySelect'));
            add_action('cmb2_admin_init', array($this, 'registerPageSettings'));
            add_action('cmb2_admin_init', array($this, 'registerBrandMetabox'));
            add_action('admin_init', array($this, 'removeMenuPage'));
            add_filter('parent_file', array($this, 'selectOtherMenu'));
            add_action('admin_head', array($this, 'removeGutenbergTips'));
            add_action('enqueue_block_editor_assets', array($this, 'disableEditorFullscreen'));
        }

        /**
         * Method used to provide a single instance of this class.
         *
         * @return WP_Inci_Meta|null
         */
        public static function getInstanceMeta()
        {

            if (null === self::$_instance) {
                self::$_instance = new WP_Inci_Meta();
            }

            return self::$_instance;
        }

        /**
         * Create new custom meta 'source_url' for Source taxonomy.
         *
         * @return void
         */
        public function registerSourceUrl()
        {

            $cmb_term = new_cmb2_box(
                array(
                    'id'               => 'source_url_add',
                    'title'            => __('Url', 'wp-inci'),
                    'object_types'     => array('term'),
                    'taxonomies'       => array('source'),
                    'new_term_section' => true,
                )
            );

            $cmb_term->add_field(
                array(
                    'name'         => __('Url', 'wp-inci'),
                    'id'           => 'source_url',
                    'type'         => 'text_url',
                    'protocols'    => array('http', 'https'),
                    'show_in_rest' => WP_REST_Server::ALLMETHODS,
                )
            );
        }

        /**
         * Create new custom meta 'ingredients' and 'may_contain' for Product post type.
         *
         * @return void
         */
        public function registerIngredientsRepeater()
        {

            $ingredients = new_cmb2_box(
                array(
                    'id'           => 'ingredients_search_ajax',
                    'title'        => __('Ingredients', 'wp-inci'),
                    'object_types' => array('products'),
                    'context'      => 'normal',
                    'priority'     => 'high',
                    'show_names'   => false,
                )
            );


            $ingredients->add_field(
                array(
                    'name'         => __('Ingredients', 'wp-inci'),
                    'id'           => 'ingredients',
                    'type'         => 'search_ajax',
                    'desc'         => __('Start typing ingredient name, then select one from the list. No results found?', 'wp-inci'),
                    'sortable'     => true,
                    'limit'        => 10,
                    'query_args'   => array(
                        'post_type'      => 'ingredients',
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'order'          => 'ASC',
                        'orderby'        => 'title',
                    ),
                    'show_in_rest' => WP_REST_Server::ALLMETHODS,
                )
            );

            $may_contain = new_cmb2_box(
                array(
                    'id'           => 'may_contain_search_ajax',
                    'title'        => __('May Contain', 'wp-inci'),
                    'object_types' => array('products'),
                    'context'      => 'normal',
                    'priority'     => 'default',
                    'show_names'   => false,
                )
            );

            $may_contain->add_field(
                array(
                    'name'         => __('May Contain', 'wp-inci'),
                    'id'           => 'may_contain',
                    'type'         => 'search_ajax',
                    'desc'         => __('Start typing ingredient name, then select one from the list. No results found?', 'wp-inci'),
                    'sortable'     => true,
                    'limit'        => 10,
                    'query_args'   => array(
                        'post_type'      => 'ingredients',
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'order'          => 'ASC',
                        'orderby'        => 'title',
                    ),
                    'show_in_rest' => WP_REST_Server::ALLMETHODS,
                )
            );
        }

        /**
         * Create the Safety select.
         *
         * @return void
         */
        public function registerSafetySelect()
        {

            $safety = new_cmb2_box(
                array(
                    'id'           => 'inci',
                    'title'        => __('INCI', 'wp-inci'),
                    'object_types' => array('ingredients'), // Post type
                    'context'      => 'side',
                    'priority'     => 'default',
                    'show_names'   => true,
                )
            );

            /**
             * Create pseudonimus field.
             */
            $safety->add_field(array(
                'name' => __('Дополнительные названия', 'wp-inci'),
                'id'   => 'psev',
                'type' => 'text_small',
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
            ));

            $safety->add_field(array(
                'name'             => 'Ключевая роль',
                'id'               => 'role',
                'type'             => 'multicheck',
                'show_option_none' => true,
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
                //'before_field'     => array( $this, 'before_safety' ),
                'options'          => array(
                    '1' => __('<img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Brightening-1cd9c2f5dcdd9edb1d023dc46c290121b78bc0db1f584eeba19c848d94d3756a.png" class="img-effects"><b>Осветление</b>', 'wp-inci'),
                    '2' => __('<img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Promotes%20Wound%20Healing-e7a95d5590e806b332559c604d2a08f9a0b23aa88046873f0839a7495d7789f0.png" class="img-effects"><b>Заживление</b>', 'wp-inci'),
                    '3' => __('<img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Anti-Aging-cd7044b572861dda33a4a0864e40999dacb6d34b942c19552b97209ce49ecf89.png" class="img-effects"><b>Anti-Age</b>', 'wp-inci'),
                    '4' => __('<img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/Acne-Fighting-c4a8124526ac47a08077940f801808fdd24dea70946dfc7fa302db9a08bb23a5.png" class="img-effects"><b>От акне</b>', 'wp-inci'),
                    '5' => __('<img class="img-label" src="https://www.skincarisma.com/assets/ingredients/icons/UV%20Protection-ce76a212f25a8942783f172ce9e87b3d4f3fa9abb72aa063650e904761211e39.png" class="img-effects"><b>Защита от солнца</b>', 'wp-inci'),
                ),
            ));

            $safety->add_field(
                array(
                    'name'             => '',
                    'id'               => 'safety',
                    'type'             => 'select',
                    'show_option_none' => true,
                    'before_field'     => array($this, 'beforeSafety'),
                    'options'          => array(
                        '1' => __('Double green', 'wp-inci'),
                        '2' => __('Green', 'wp-inci'),
                        '3' => __('Yellow', 'wp-inci'),
                        '4' => __('Red', 'wp-inci'),
                        '5' => __('Double red', 'wp-inci'),
                    ),
                    'show_in_rest'     => WP_REST_Server::ALLMETHODS,
                )
            );

            $safety->add_field(
                array(
                    'id'   => 'cosing_id',
                    'type' => 'hidden',
                )
            );

            $safety->add_field(
                array(
                    'id'   => 'last_update',
                    'type' => 'hidden',
                )
            );

            /**
             * Create the dry skin field.
             */
            $safety->add_field(array(
                'name' => __('Совместимость с сухой кожей', 'wp-inci'),
                'id'   => 'dry_skin',
                'type' => 'radio_inline',
                'options' => array(
                    'yes' => __('Рекомендуется для сухой кожи', 'wp-inci'),
                    'no'   => __('Не рекомендуется для сухой кожи', 'wp-inci'),
                ),
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
            ));

            /**
             * Create the oil skin field.
             */
            $safety->add_field(array(
                'name' => __('Совместимость с жирной кожей', 'wp-inci'),
                'id'   => 'oil_skin',
                'type' => 'radio_inline',
                'options' => array(
                    'yes' => __('Рекомендуется для жирной кожи / склонной к акне', 'wp-inci'),
                    'no'   => __('Не рекомендуется для жирной кожи / склонной к акне', 'wp-inci'),
                ),
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
            ));

            /**
             * Create the irritation skin field.
             */
            $safety->add_field(array(
                'name' => __('Совместимость с чувствительной кожей', 'wp-inci'),
                'id'   => 'irritation_skin',
                'type' => 'radio_inline',
                'options' => array(
                    'yes' => __('Рекомендуется для чувствительной кожи', 'wp-inci'),
                    'no'   => __('Не рекомендуется для чувствительной кожи', 'wp-inci'),
                ),
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
            ));

            /**
             * Create the Paraben field.
             */
            $safety->add_field(array(
                'name' => __('Это парабен?', 'wp-inci'),
                'id'   => 'it_is_paraben',
                'type' => 'radio_inline',
                'options' => array(
                    '1' => __('Да', 'wp-inci'),
                    '2' => __('Нет', 'wp-inci'),
                ),
                'default' => '2',
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
            ));

            /**
             * Create the Silicone field.
             */
            $safety->add_field(array(
                'name' => __('Это силикон?', 'wp-inci'),
                'id'   => 'it_is_silicone',
                'type' => 'radio_inline',
                'options' => array(
                    '1' => __('Да', 'wp-inci'),
                    '2' => __('Нет', 'wp-inci'),
                ),
                'default' => '2',
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
            ));

            /**
             * Create the Sulfate field.
             */
            $safety->add_field(array(
                'name' => __('Это сульфат?', 'wp-inci'),
                'id'   => 'it_is_sulfate',
                'type' => 'radio_inline',
                'options' => array(
                    '1' => __('Да', 'wp-inci'),
                    '2' => __('Нет', 'wp-inci'),
                ),
                'default' => '2',
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
            ));

            /**
             * Create the Alcohol field.
             */
            $safety->add_field(array(
                'name' => __('Это спирт?', 'wp-inci'),
                'id'   => 'it_is_alcohol',
                'type' => 'radio_inline',
                'options' => array(
                    '1' => __('Да', 'wp-inci'),
                    '2' => __('Нет', 'wp-inci'),
                ),
                'default' => '2',
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
            ));

            /**
             * Create the Comedohenity field.
             */
            $safety->add_field(array(
                'name' => __('Комедогенность', 'wp-inci'),
                'id'   => 'comedohenity',
                'type'    => 'text_small',
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
            ));

            /**
             * Create the Irritancy field.
             */
            $safety->add_field(array(
                'name' => __('Раздражительность', 'wp-inci'),
                'id'   => 'irritancy',
                'type'    => 'text_small',
                'show_in_rest' => WP_REST_Server::ALLMETHODS,
            ));

            /**
             * Create the CAS Number field.
             */
            $safety->add_field(
                array(
                    'name'         => __('CAS #', 'wp-inci'),
                    'id'           => 'cas_number',
                    'type'         => 'text_small',
                    'show_in_rest' => WP_REST_Server::ALLMETHODS,
                )
            );

            /**
             * Create the EC Number field.
             */
            $safety->add_field(
                array(
                    'name'         => __('EC #', 'wp-inci'),
                    'id'           => 'ec_number',
                    'type'         => 'text_small',
                    'show_in_rest' => WP_REST_Server::ALLMETHODS,
                )
            );

            /**
             * Create the Restriction field.
             */
            $safety->add_field(
                array(
                    'name'         => __('Restrictions', 'wp-inci'),
                    'id'           => 'restriction',
                    'type'         => 'text_small',
                    'show_in_rest' => WP_REST_Server::ALLMETHODS,
                )
            );
        }

        /**
         * Returns the safety custom meta with HTML before the Safety select.
         *
         * @param array  $field_args The fields args
         * @param object $field      The field object
         *
         * @return void
         */
        public function beforeSafety($field_args, $field)
        {
            echo (WP_Inci::getInstance())->getSafetyHtml($field->object_id);
        }

        /**
         * Check filesystem credentials.
         *
         * @param string $url     The url
         * @param string $method  The method
         * @param string $context The context
         * @param array  $fields  The fields
         *
         * @return bool
         */
        public function connect($url, $method, $context, $fields = null)
        {

            if (false === ($credentials = request_filesystem_credentials($url, $method, false, $context, $fields))) {
                return false;
            }

            if (!WP_Filesystem($credentials)) {
                request_filesystem_credentials($url, $method, true, $context);

                return false;
            }

            return true;
        }

        /**
         * Sets CSS for default style reading the content of the CSS.
         *
         * @return string
         */
        public function defaultStyle()
        {
            global $wp_filesystem;

            $url = wp_nonce_url("options-general.php?page=settings");

            if ($this->connect($url, "", WP_PLUGIN_DIR . "/wp-inci/public/css")) {
                $dir  = $wp_filesystem->find_folder(WP_PLUGIN_DIR . "/wp-inci/public/css");
                $file = trailingslashit($dir) . "wp-inci.css";

                if ($wp_filesystem->exists($file)) {
                    $text = $wp_filesystem->get_contents($file);
                    if (!$text) {
                        return "";
                    }

                    return $text;
                }

                return "File doesn't exist";
            }

            return "Cannot initialize filesystem";
        }

        /**
         * Returns the button to copy the WP INCI style.
         *
         * @return void
         */
        public function copyButton()
        {
            echo "<script>var wi_style=`" . $this->defaultStyle() . "`;";
            echo "var wi_msg='" . __('Style copied to clipboard.', 'wp-inci') . "';</script>";
            echo '<button id="copy_style" type="button" class="button copy">' . __('Copy style', 'wp-inci') . '</button><span id="msg"></span>';
        }

        /**
         * Create WP INCI Settings page.
         *
         * @return void
         */
        public function registerPageSettings()
        {

            $args = array(
                'id'           => 'wi_settings',
                'title'        => __('WP INCI', 'wp-inci'),
                'object_types' => array('options-page'),
                'option_key'   => 'wi_settings',
                'tab_group'    => 'wi_settings',
                'tab_title'    => __('Settings', 'wp-inci'),
                'parent_slug'  => 'options-general.php',
                'message_cb'   => array($this, 'optionsPageMessageCallback'),
            );

            $main_options = new_cmb2_box($args);

            $desc = __(
                'You can disable the WP INCI style and add your own to your theme.<br/>'
                    . 'Just copy the standard WP INCI style above into your style.css and customize it.',
                'wp-inci'
            );

            /**
             * Create style settings.
             *
             * @return void
             */
            $main_options->add_field(
                array(
                    'name'         => __('WP INCI Default Style', 'wp-inci'),
                    'desc'         => '',
                    'id'           => 'textarea_style',
                    'type'         => 'textarea_code',
                    'default_cb'   => array($this, 'defaultStyle'),
                    'save_field'   => false,
                    'attributes'   => array(
                        'readonly'        => 'readonly',
                        'disabled'        => 'disabled',
                        'data-codeeditor' => json_encode(
                            array(
                                'codemirror' => array(
                                    'mode'     => 'css',
                                    'readOnly' => 'nocursor',
                                ),
                            )
                        ),
                    ),
                    'after_field'  => array($this, 'copyButton'),
                    'show_in_rest' => WP_REST_Server::ALLMETHODS,
                )
            );

            $main_options->add_field(
                array(
                    'name'           => __('Disable WP INCI style', 'wp-inci'),
                    'id'             => 'wi_disable_style',
                    'desc'           => $desc,
                    'type'           => 'switch',
                    'default'        => 'off',
                    'active_value'   => 'on',
                    'inactive_value' => 'off',
                    'show_in_rest'   => WP_REST_Server::ALLMETHODS,
                )
            );

            /**
             * Create disclaimer settings.
             *
             * @return void
             */
            $args = array(
                'id'           => 'wi_disclaimer',
                'title'        => __('WP INCI', 'wp-inci'),
                'object_types' => array('options-page'),
                'option_key'   => 'wi_disclaimer',
                'parent_slug'  => 'options-general.php',
                'tab_group'    => 'wi_settings',
                'tab_title'    => __('Disclaimer', 'wp-inci'),
                'message_cb'   => array($this, 'optionsPageMessageCallback'),
            );

            $secondary_options = new_cmb2_box($args);

            $secondary_options->add_field(
                array(
                    'name'         => __('Disclaimer', 'wp-inci'),
                    'desc'         => __('Add a disclaimer after WP INCI table of ingredients.', 'wp-inci'),
                    'id'           => 'textarea_disclaimer',
                    'type'         => 'textarea_code',
                    'default_cb'   => array($this, 'getDefaultDisclaimer'),
                    'show_in_rest' => WP_REST_Server::ALLMETHODS,
                )
            );
        }

        /**
         * Remove the disclaimer menu.
         *
         * @return void
         */
        public function removeMenuPage()
        {
            remove_submenu_page('options-general.php', 'wi_disclaimer');
        }

        /**
         * Highlight the setting menu.
         *
         * @param string $parent_file The parent file.
         *
         * @return string
         */
        public function selectOtherMenu($parent_file)
        {
            global $plugin_page;

            if ('wi_disclaimer' === $plugin_page) {
                $plugin_page = 'wi_settings';
            }

            return $parent_file;
        }


        /**
         * Modify the updated message.
         *
         * @param string $cmb  The cmb
         * @param array  $args The args
         *
         * @return void
         */
        public function optionsPageMessageCallback($cmb, $args)
        {
            if (!empty($args['should_notify'])) {

                if (('updated' == $args['type']) || ('notice-warning' == $args['type'])) {
                    $args['message'] = __('Settings saved.', 'wp-inci');
                }

                add_settings_error($args['setting'], $args['code'], $args['message'], 'success');
            }
        }

        /**
         * Custom metabox for brand taxonomy.
         *
         * @return void
         */
        public function registerBrandMetabox()
        {

            $brand = new_cmb2_box(
                array(
                    'id'           => 'brand_box',
                    'title'        => __('Brand', 'wp-inci'),
                    'object_types' => array('products'),
                    'context'      => 'side',
                    'priority'     => 'default',
                    'show_names'   => false,
                )
            );

            $brand->add_field(
                array(
                    'name'         => __('Brand', 'wp-inci'),
                    'desc'         => '',
                    'id'           => 'taxonomy_brand',
                    'taxonomy'     => 'brand',
                    'type'         => 'taxonomy_select',
                    'after_field'  => '<br/><a style="" target="_blank" href="' . esc_url(admin_url('edit-tags.php?taxonomy=brand')) . '" class="button brand">' . __('Add new brand', 'wp-inci') . '</a>',
                    'show_in_rest' => WP_REST_Server::ALLMETHODS,
                )
            );
        }

        /**
         * Hides the very annoying Welcome Tips popup for Gutenberg.
         *
         * @return void
         */
        public function removeGutenbergTips()
        {
            global $pagenow;

            if ('post.php' == $pagenow && isset($_GET['post'])) {

                $post_type = get_post_type($_GET['post']);

                if (('ingredients' == $post_type) || ('products' == $post_type)) {
?>
                    <style>
                        .components-modal__frame.components-guide {
                            display: none !important;
                        }

                        .components-modal__screen-overlay {
                            display: none !important;
                        }
                    </style>
<?php
                }
            }
        }

        /**
         * Disable the very annoying fullscreen mode for Gutenberg.
         *
         * @return void
         */
        public function disableEditorFullscreen()
        {
            global $pagenow;

            if ('post.php' == $pagenow && isset($_GET['post'])) {

                $post_type = get_post_type($_GET['post']);
                if (('ingredients' == $post_type) || ('products' == $post_type)) {

                    $script = "window.onload = function() { const isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ); if ( isFullscreenMode ) { wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' ); } }";

                    wp_add_inline_script('wp-blocks', $script);
                }
            }
        }
    }

    add_action('plugins_loaded', array('WP_Inci_Meta', 'getInstanceMeta'));
}
