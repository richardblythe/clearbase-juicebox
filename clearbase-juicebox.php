<?php
/*
    Plugin Name: Juicebox Gallery
    Description: A Clearbase controller for Juicebox galleries
    Version: 1.0.0
    Author: Richard Blythe
    Author URI: http://unity3software.com/richardblythe
    GitHub Plugin URI: https://github.com/richardblythe/clearbase-juicebox
 */

function Clearbase_Juicebox_Load() {
    class Clearbase_Juicebox extends Clearbase_View_Controller {
        
        public function ID() {
            return 'cb-juicebox';
        }

        public function Title() {
            return __('Juicebox', 'cb-juicebox');
        }

        public function FolderSettings($folder = null) {
            $folder = clearbase_load_folder($folder);
            if (is_wp_error($folder))
                return null;

            $allow_folders = false;
            if (0 == $folder->post_parent) {
                $allow_folders = 'yes' == clearbase_get_value('postmeta.clearbase_juicebox.multifolders', 'yes', $folder);
            }
            return array(
                //Specifies if child folders are shown
                'allow_folders'  => $allow_folders,

                'allow_media' => !$allow_folders,

                'media_filter' => 'image'
            );
        }

        public function Enqueue() {
            $this->register_script('cb-juicebox', plugins_url('/jbcore/juicebox.js', __FILE__), array('jquery'));
            $this->register_style('cb-juicebox-folders', plugins_url('/folders.css', __FILE__));
        }
        
        public function Render($data = null) {
            $this->enqueue_registered();
            $folder = clearbase_load_folder($data);
            if (is_wp_error($folder)) {
                echo '<p class="error">' . __('Juicebox: You must specify a valid clearbase folder', 'cb-juicebox') . '</p>';
                return false;
            }
            $multi = clearbase_get_value('postmeta.clearbase_juicebox.multifolders', 'yes', $folder);
            $query = clearbase_query_subfolders($folder);
			$multi_one_child = 'yes' == $multi && 1 == $query->found_posts;
            if (0 == $folder->post_parent && !$multi_one_child) {
                
                //$settings = clearbase_get_value('postmeta.clearbase_juicebox', null, $folder);

                $expanded = wp_is_mobile() ? '#expanded' : '';
                ?>
                <ul class="juicebox-folders">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <?php $permalink = get_the_permalink() . $expanded; ?>
                    <li>
                        <div class="juicebox-pile">
                            <div class="juicebox-pile-inner">
                                <a href="<?php echo $permalink ?>">
                                    <?php $image_src = clearbase_default_folder_image_src( get_the_ID(), 'medium' ); ?>
                                    <div class="centered">
                                        <img src="<?php echo $image_src[0] ?>"> 
                                    </div>                                                               
                                    <h3>
                                        <?php the_title(); ?>                                    
                                    </h3>
                                </a>
                            </div>
                        </div>
                    </li>
                    </li>
                    </li>

                <?php endwhile; ?>
                </ul><!-- end ul.slides -->
                <?php
            } else {
                if ($multi_one_child)
                    $folder = clearbase_load_folder($query->posts[0]);
                
                $config_url = plugins_url('config.php?id='.$folder->ID , __FILE__);
                $settings = clearbase_get_value('postmeta.clearbase_juicebox', null, $folder);


                echo "<div id=\"juicebox-{$folder->ID}\" style=\"display:inline-block;\"></div>        
                <script>
                jQuery( document ).ready(function() {
                    var jb = new juicebox({
                        baseUrl : '{$base_url}',
                        configURL:  '{$config_url}',
                        containerId : 'juicebox-{$folder->ID}',
                        galleryWidth: '" . esc_js( clearbase_get_value('width', '100%', $settings) ) . "',
                        galleryHeight: '" . esc_js( clearbase_get_value('height', '80%', $settings) ) . "',
                        backgroundColor: '" . esc_js( clearbase_get_value('background_color', '#222222', $settings) ) . "',
                        captionBackColor: 'rgba(0,0,0,.7)',
                        buttonBarHAlign: 'LEFT',
                        showOpenButton: false,
                        showAutoPlayButton: true,
                        showImageOverlay: 'ALWAYS',
                        autoPlayOnLoad: true,
                        displayTime: 10,
                        showNavButtons: true,
                        enableLooping: true,
                        screenMode: 'AUTO'
                    });
                });
                </script>";
            }
                
        }

        public function EditorFields() {
            return array( 
                array(
                    'id'        => 'clearbase_juicebox',
                    'type'      => 'sectionstart'
                ),
                array(
                    'id'        => 'postmeta.clearbase_juicebox.multifolders', 
                    'title'     => __( "Multiple Folders", 'clearbase_juicebox' ),
                    'desc'      => __( "Allows photos to be stored in multiple folders", 'clearbase_juicebox' ),
                    'type'      => 'checkbox',
                    'default'   => 'yes'
                ),
                array(
                    'id'        => 'postmeta.clearbase_juicebox.width', 
                    'title'     => __( "Width", 'clearbase_juicebox' ),
                    'desc'      => __( "Specifies the width of the juicebox gallery", 'clearbase_juicebox' ),
                    'type'      => 'text',
                    'default'   => '100%'
                ),
                array(
                    'id'        => 'postmeta.clearbase_juicebox.height', 
                    'title'     => __( "Height", 'clearbase_juicebox' ),
                    'desc'      => __( "Specifies the height of the juicebox gallery", 'clearbase_juicebox' ),
                    'type'      => 'text',
                    'default'   => '100%'
                ),
                array(
                    'id'        => 'postmeta.clearbase_juicebox.background_color', 
                    'title'     => __( "Background Color", 'clearbase_juicebox' ),
                    'desc'      => __( "Specifies the background color of the juicebox gallery", 'clearbase_juicebox' ),
                    'type'      => 'text'
                ),
                array(
                    'id'        => 'clearbase_juicebox',
                    'type'      => 'sectionend'
                )
            );
        }   
    }

    new Clearbase_Juicebox();
}
add_action('clearbase_loaded', 'Clearbase_Juicebox_Load');