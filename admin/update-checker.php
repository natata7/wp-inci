<?php

defined('ABSPATH') || exit;


if (!class_exists('WP_Inci_Update_Checker')) {

    class WP_Inci_Update_Checker {

        private $file;

        private $plugin;

        private $basename;

        private $active;

        private $username;

        private $repository;

        private $authorize_token;

        private $github_response;

        public function __construct($file) {

            $this->file = $file;
            $this->username = 'natata7';
            $this->repository = 'wp-inci';

            add_action('admin_init', array($this, 'set_plugin_properties'));

            add_filter('pre_set_site_transient_update_plugins', array($this, 'modify_transient'), 10, 1);
            add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
            add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);

            // Add Authorization Token to download_package
            add_filter(
                'upgrader_pre_download',
                function () {
                    add_filter('http_request_args', [$this, 'download_package'], 15, 2);
                    return false; // upgrader_pre_download filter default return value.
                }
            );

            return $this;
        }

        public function set_plugin_properties() {
            $this->plugin    = get_plugin_data($this->file);
            $this->basename = plugin_basename($this->file);
            $this->active    = is_plugin_active($this->basename);
        }

        public function authorize($token) {
            $this->authorize_token = $token;
        }

        private function get_repository_info() {
            if (is_null($this->github_response)) { // Do we have a response?
                $args = array();
                $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases', $this->username, $this->repository); // Build URI

                $args = array();

                if ($this->authorize_token) { // Is there an access token?
                    $args['headers']['Authorization'] = "bearer {$this->authorize_token}"; // Set the headers
                }

                $response = json_decode(wp_remote_retrieve_body(wp_remote_get($request_uri, $args)), true); // Get JSON and parse it

                if (is_array($response)) { // If it is an array
                    $response = current($response); // Get the first item
                }

                $this->github_response = $response; // Set it to our property
            }
        }


        public function modify_transient($transient) {

            if (property_exists($transient, 'checked')) {

                if ($checked = $transient->checked) {

                    $this->get_repository_info();

                    $out_of_date = version_compare($this->github_response['tag_name'], $checked[$this->basename], 'gt');

                    if ($out_of_date) {

                        $new_files = $this->github_response['zipball_url'];

                        $slug = current(explode('/', $this->basename));

                        $plugin = array( 
                            'url' => $this->plugin["PluginURI"],
                            'slug' => $slug,
                            'package' => $new_files,
                            'new_version' => $this->github_response['tag_name']
                        );

                        $transient->response[$this->basename] = (object) $plugin; 
                    }
                }
            }

            return $transient; 
        }

        public function plugin_popup($result, $action, $args) {

            if (!empty($args->slug)) { 

                if ($args->slug == current(explode('/', $this->basename))) { 

                    $this->get_repository_info();

                    $plugin = array(
                        'name'              => $this->plugin["Name"],
                        'slug'              => $this->basename,
                        'requires'          => '6.0',
                        'tested'            => '6.2.2',
                        'rating'            => '100.0',
                        'num_ratings'       => '10823',
                        'downloaded'        => '14249',
                        'added'             => '2023-05-23',
                        'version'           => $this->github_response['tag_name'],
                        'author'            => $this->plugin["AuthorName"],
                        'author_profile'    => $this->plugin["AuthorURI"],
                        'last_updated'      => $this->github_response['published_at'],
                        'homepage'          => $this->plugin["PluginURI"],
                        'short_description' => $this->plugin["Description"],
                        'sections'          => array(
                            'Description'   => $this->plugin["Description"],
                            'Updates'       => $this->github_response['body'],
                        ),
                        'download_link'     => $this->github_response['zipball_url']
                    );

                    return (object) $plugin; 
                }
            }
            return $result; 
        }

        public function download_package($args, $url) {

            if (null !== $args['filename']) {
                if ($this->authorize_token) {
                    $args = array_merge($args, array("headers" => array("Authorization" => "token {$this->authorize_token}")));
                }
            }

            remove_filter('http_request_args', [$this, 'download_package']);

            return $args;
        }

        public function after_install($response, $hook_extra, $result) {
            global $wp_filesystem; 

            $install_directory = plugin_dir_path($this->file); 
            $wp_filesystem->move($result['destination'], $install_directory); 
            $result['destination'] = $install_directory; 

            if ($this->active) { 
                activate_plugin($this->basename); 
            }

            return $result;
        }
    }

 
}
