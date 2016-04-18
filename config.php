<?php
$result = explode( "wp-content" , __FILE__ );
require($result[0] . "wp-load.php" );
echo '<?xml version="1.0" encoding="'. get_option('blog_charset').'"?'.'>'; 
$folder = get_post(clearbase_get_value('id', 0, $_REQUEST)); 
$query = clearbase_query_attachments('image', $folder);
global $post;

echo '<juiceboxgallery>';
while ( $query->have_posts() ) : $query->the_post();
      $large = wp_get_attachment_image_src( $post->ID, 'large');
      $thumbnail = wp_get_attachment_image_src( $post->ID, 'thumbnail');
      echo '<image 
          imageURL="' . $large[0] .'" 
          thumbURL="' . $thumbnail[0] .'"
          linkURL="'  . the_permalink() . '"
          linkTarget="_blank">' .
         (apply_filters('juicebox_show_title', true) ? "<title><![CDATA[{$post->post_title}]]></title>" : '').
         (apply_filters('juicebox_show_caption', true) ? "<caption><![CDATA[{$post->post_excerpt}]]></caption>" : '').
     '</image>';
endwhile; 

echo '</juiceboxgallery>';
die();