<?php

/**
 * WP_Inci_Fields
 *
 * @category Plugin
 * @package  Wpinci
 * @author   natata7
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL 3
 */
if (!class_exists('WP_Inci_Fields', false)) {
    /**
     * Class for custom CMB2 fields
     *
     * @category Plugin
     * @package  Wpinci
     * @author   natata7
     * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL 3
     */
    class WP_Inci_Fields extends WP_Inci {

        /**
         * A static reference to track the single instance of this class.
         */
        private static $_instance;

        /**
         * Constructor.
         */
        public function __construct() {
            (WP_Inci::getInstance())->__construct();
            $this->init();
            $this->url = plugins_url("", __FILE__);
        }

        /**
         * Standard init.
         *
         * @return void
         */
        public function init() {
            /**
             * Add hooks and queue.
             */
            add_action(
                'cmb2_render_switch',
                array($this, 'renderSwitch'),
                10,
                5
            );
            add_action('admin_footer', array($this, 'adminFooter'));
            add_action(
                'cmb2_render_search_ajax',
                array($this, 'renderSearchAjax'),
                10,
                5
            );
            add_action(
                'cmb2_sanitize_search_ajax',
                array($this, 'sanitizeSearchAjax'),
                10,
                4
            );
            add_action(
                'admin_enqueue_scripts',
                array($this, 'setupAdminScripts')
            );
            add_action(
                'wp_ajax_cmb2_search_ajax_get_results',
                array(
                    $this,
                    'cmb2SearchAjaxGetResults'
                )
            );
            add_action(
                'wp_ajax_cmb2_multiple_search_ajax_get_results',
                array(
                    $this,
                    'cmb2MultipleSearchAjaxGetResults'
                )
            );
        }

        /**
         * Method used to provide a single instance of this class.
         *
         * @return WP_Inci_Fields|null
         */
        public static function getInstanceFields() {

            if (null === self::$_instance) {
                self::$_instance = new WP_Inci_Fields();
            }

            return self::$_instance;
        }

        /**
         * Title filter.
         * 
         * @param string $where    Query where
         * @param object $wp_query WP query
         *
         * @return string
         */
        public function setTitleFilter($where, $wp_query) {

            global $wpdb;
            if ($search_term = $wp_query->get('title_filter')) :
                $search_term           = $wpdb->esc_like($search_term);
                $search_term           = ' \'' . $search_term . '%\'';
                $title_filter_relation = (strtoupper($wp_query->get('title_filter_relation')) === 'OR' ? 'OR' : 'AND');
                $where                 .= ' ' . $title_filter_relation . ' ' . $wpdb->posts . '.post_title LIKE ' . $search_term;
            endif;

            return $where;
        }

        /**
         * Render switch button field.
         *
         * @param object $field             The field object
         * @param string $escaped_value     The escaped value
         * @param int    $object_id         Object ID
         * @param string $object_type       Object type
         * @param object $field_type_object The type object
         *
         * @return void
         */
        public function renderSwitch(
            $field,
            $escaped_value,
            $object_id,
            $object_type,
            $field_type_object
        ) {
            $field_name   = $field->_name();
            $active_value = 'on';
            if (!empty($field->args('active_value')) && null !== $field->args('active_value')) {
                $active_value = $field->args('active_value');
            }

            $inactive_value = 'off';
            if (!empty($field->args('inactive_value')) && null !== $field->args('inactive_value')) {
                $inactive_value = $field->args('inactive_value');
            }

            $args = array(
                'type'  => 'checkbox',
                'id'    => $field_name,
                'name'  => $field_name,
                'desc'  => '',
                'value' => $active_value,
            );

            if ($escaped_value == $active_value) {
                $args['checked'] = 'checked="checked"';
            } else {
                $args['checked'] = '';
            }

            echo '<label class="cmb2-switch">
				    <input type="checkbox" name="' . esc_attr($args['name']) . '" id="' . esc_attr($args['id']) . '" value="' . esc_attr($active_value) . '" data-inactive-value="' . esc_attr($inactive_value) . '" ' . $args['checked'] . ' />
				    <span class="cmb2-slider round"></span>
			      </label>';

            $field_type_object->_desc(true, true);
        }

        /**
         *  Adds styles for checked and focus based on WP color scheme.
         *
         * @return void
         */
        public function adminFooter() {
            global $_wp_admin_css_colors;
            if (!empty($_wp_admin_css_colors[get_user_option('admin_color')])) {
                $scheme_colors = $_wp_admin_css_colors[get_user_option('admin_color')]->colors;
            }
            $toggle_color = !empty($scheme_colors) ? end($scheme_colors) : '#2196F3';
?>
            <style>
                input:checked+.cmb2-slider {
                    background-color: <?php echo $toggle_color ?>;
                }

                input:focus+.cmb2-slider {
                    box-shadow: 0 0 1px<?php echo $toggle_color ?>;
                }
            </style>
<?php
        }

        /**
         * Render Search Ajax field.
         *
         * @param object $field       The field object
         * @param string $value       Field value
         * @param int    $object_id   Object ID
         * @param string $object_type Object type
         * @param object $field_type  The type object
         *
         * @return void
         */
        public function renderSearchAjax(
            $field,
            $value,
            $object_id,
            $object_type,
            $field_type
        ) {
            $field_name = $field->_name();

            echo '<div class="container-search-left">';
            echo '<h2 class="wi_single">' . __('Search', 'wp-inci') . '</h2>';

            $field_value = '';

            echo $field_type->input(
                array(
                    'type'           => 'text',
                    'name'           => $field_name,
                    'id'             => $field_name,
                    'class'          => 'cmb2-search-ajax',
                    'value'          => $field_value,
                    'desc'           => false,
                    'data-limit'     => $field->args('limit') ?: '1',
                    'data-sortable'  => $field->args('sortable') ?: '0',
                    'data-object'    => $field->args('object_type') ?: 'post',
                    'data-queryargs' => $field->args('query_args') ? htmlspecialchars(
                        json_encode($field->args('query_args')),
                        ENT_QUOTES,
                        'UTF-8'
                    ) : ''
                )
            );

            echo '<img src="' . admin_url('images/spinner.gif') . '" class="cmb2-search-ajax-spinner" />';

            $field_type->_desc(true, true);

            echo '<a target="_blank" href="' . esc_url(admin_url('post-new.php?post_type=ingredients')) . '" class="button desc">' . __(
                'Add new ingredient',
                'wp-inci'
            ) . '</a>';

            echo '<h2 class="wi_multiple">' . __(
                'Multiple search',
                'wp-inci'
            ) . '</h2>';

            echo $field_type->textarea(
                array(
                    'type'  => 'textarea',
                    'name'  => __('Multiple search', 'wp-inci'),
                    'id'    => $field_name . '_textarea_search',
                    'class' => 'cmb2-multiple-search-ajax',
                    'value' => false,
                    'desc'  => '<p class="cmb2-metabox-description">' . __(
                        'Enter multiple ingredients separated by a comma, then click on "Search Ingredients".',
                        'wp-inci'
                    ) . '</p>',
                )
            );

            echo '<button id="' . $field_name . '_button_search" type="button" class="button desc">' . __(
                'Search Ingredients',
                'wp-inci'
            ) . '</button>';

            echo '<img src="' . admin_url('images/spinner.gif') . '" class="cmb2-multiple-search-ajax-spinner" />';

            echo '</div>';

            echo '<div class="container-search-right">';

            if (1 !== $field->args('limit')) {
                echo '<ol class="cmb2-search-ajax-results" id="' . $field_name . '_results">';
                if (isset($value) && !empty($value)) {
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                    foreach ($value as $val) {
                        $handle = ($field->args('sortable')) ? '<span class="handle"></span>' : '';
                        if ($field->args('object_type') === 'user') {
                            $guid  = get_edit_user_link($val);
                            $user  = get_userdata($val);
                            $title = $user->display_name;
                        } else {
                            $guid  = get_edit_post_link($val);
                            $title = get_the_title($val);
                        }

                        $safety = (WP_Inci::getInstance())->getSafetyHtml($val);
                        $title  = '<div class="wi_wrapper">' . $safety . '<div class="wi_value">' . $title . '</div></div>';

                        echo '<li><input type="hidden" name="' . $field_name . '_results[]" value="' . $val . '">' . $handle . '<a href="' . $guid . '" target="_blank" class="edit-link">' . $title . '</a><a class="remover"><span class="dashicons dashicons-no"></span><span class="dashicons dashicons-dismiss"></span></a></li>';
                    }
                }
                echo '</ol>';
            }

            echo '</div>';
        }

        /**
         * Sanitize the Ajax search.
         *
         * @param string $override_value The override value
         * @param string $value          The field value
         * @param int    $object_id      The object ID
         * @param array  $field_args     The field args
         *
         * @return bool|mixed
         */
        public function sanitizeSearchAjax(
            $override_value,
            $value,
            $object_id,
            $field_args
        ) {
            $fid = $field_args['id'];
            if (!empty($field_args['render_row_cb'][0]->data_to_save[$fid . '_results'])) {
                $value = $field_args['render_row_cb'][0]->data_to_save[$fid . '_results'];
            } elseif (!defined('DOING_AJAX')) {
                $value = false;
            }

            return $value;
        }

        /**
         * Enqueue scripts and styles.
         *
         * @return void
         */
        public function setupAdminScripts() {
            wp_register_script(
                'jquery-autocomplete',
                $this->url . '/admin/js/jquery.autocomplete.min.js',
                array('jquery'),
                $this->version
            );
            wp_register_script(
                'search-ajax',
                $this->url . '/admin/js/search-ajax.min.js',
                array(
                    'jquery',
                    'jquery-autocomplete',
                    'jquery-ui-sortable'
                ),
                $this->version
            );
            wp_localize_script(
                'search-ajax',
                'wi',
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce'   => wp_create_nonce('cmb2_search_ajax_get_results'),
                    'notice'  => __('No results found.', 'wp-inci'),
                )
            );
            wp_enqueue_script('search-ajax');

            wp_register_script(
                'multiple-search-ajax',
                $this->url . '/admin/js/multiple-search-ajax.min.js',
                array('jquery',),
                $this->version
            );
            wp_localize_script(
                'multiple-search-ajax',
                'wi_mu',
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce'   => wp_create_nonce('cmb2_multiple_search_ajax_get_results')
                )
            );
            wp_enqueue_script('multiple-search-ajax');
        }

        /**
         * Ajax request: get results for single search.
         *
         * @return void
         */
        public function cmb2SearchAjaxGetResults() {
            $nonce = $_POST['wicheck'];
            if (!wp_verify_nonce($nonce, 'cmb2_search_ajax_get_results')) {
                die(json_encode(
                    array(
                        'error' => __('Error : Unauthorized action', 'wp-inci')
                    )
                ));
            }

            $args = json_decode(
                stripslashes(htmlspecialchars_decode($_POST['query_args'])),
                true
            );
            add_filter(
                'posts_where',
                array($this, 'setTitleFilter'),
                10,
                2
            );
            $args['title_filter'] = $_POST['query'];
            $args['title_filter_relation'] = 'OR';
            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => 'psev',
                    'value' =>  $_POST['query'],
                    'compare' => 'LIKE'
                ]
            ];
            $data = array();

            $results = new WP_Query($args);
            if ($results->have_posts()) :
                while ($results->have_posts()) : $results->the_post();
                    // Define filter "cmb2_search_ajax_result" to allow customize ajax results.
                    $data[] = apply_filters(
                        'cmb2_search_ajax_result',
                        array(
                            'value'  => get_the_title(),
                            'data'   => get_the_ID(),
                            'guid'   => get_edit_post_link(),
                            'safety' => (WP_Inci::getInstance())->getSafetyHtml(get_the_ID())
                        )
                    );
                endwhile;
            endif;

            wp_reset_postdata();
            remove_filter('posts_where', array($this, 'setTitleFilter'));
            die(json_encode($data));
        }

        /**
         * Ajax request: get results for multiple search.
         *
         * @return void
         */
        public function cmb2MultipleSearchAjaxGetResults() {
            global $wpdb;

            $nonce = $_POST['wimucheck'];
            if (!wp_verify_nonce(
                $nonce,
                'cmb2_multiple_search_ajax_get_results'
            )) {
                die(json_encode(
                    array(
                        'error' => __('Error: Unauthorized action', 'wp-inci')
                    )
                ));
            }

            $string = '';
            $data   = [];
            $i      = 0;

            $results  = explode(',', trim($_POST['text']));
            $field_id = $_POST['field_id'];

            foreach ($results as $result) {
                $i++;
                $name = strtoupper(trim($result));

                $ingredient_id = $wpdb->get_col(
                    "SELECT ID from $wpdb->posts 
                WHERE ( post_title = '" . $name . "' OR post_title LIKE '" . $name . "%' OR post_content LIKE '%" . $name . "%')
				AND post_type = 'ingredients' 
				AND post_status = 'publish' "
                );

                if ($ingredient_id) {
                    $data['row'][] = $this->setResults(
                        $ingredient_id[0],
                        $field_id
                    );
                } else {
                    $string .= $i . ". " . $name . ": " . __(
                        'Not found',
                        'wp-inci'
                    ) . " \n";
                }

                $data['string'] = $string;
            }

            die(json_encode($data));
        }

        /**
         * Returns a list item from the post ID.
         *
         * @param int $post_id  The post ID
         * @param int $field_id The field ID
         *
         * @return string
         */
        public function setResults($post_id, $field_id): string {
            $post = get_post($post_id);

            $guid   = get_edit_post_link($post->ID);
            $safety = (WP_Inci::getInstance())->getSafetyHtml($post->ID);
            $title  = '<div class="wi_wrapper">' . $safety . '<div class="wi_value">' . $post->post_title . '</div></div>';

            return '<li><span class="handle"></span><input type="hidden" name="' . $field_id . '_results[]" value="' . $post->ID . '"><a href="' . $guid . '" target="_blank" class="edit-link">' . $title . '</a><a class="remover"><span class="dashicons dashicons-no"></span><span class="dashicons dashicons-dismiss"></span></a></li>';
        }
    }

    add_action(
        'plugins_loaded',
        array('WP_Inci_Fields', 'getInstanceFields')
    );
}
