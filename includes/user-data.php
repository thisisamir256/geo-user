<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

if (!isset($_GET['user_id']) || ! is_numeric($_GET['user_id'])) {
    exit(__('User identity must be integer'));
  }
$user_info = get_userdata($_GET['user_id']);
if(! $user_info) {
    exit(__('Not found user'));
}
$user_login = $user_info->user_login;
$csv_files_posts =new WP_Query(
    [
    'post_type'  => 'cl-csv',
    'post_status'=> 'publish',
    ]
    );
    ?>
<style>
    .field-container {
        clear: both;
        padding-left: 20px;
        text-align: center;

    }

    .field-container div:nth-child(2n+1) {
        float: right;

    }

    .field-container div:nth-child(2n) {
        float: left;
    }
    .field-container div {
        width: 50%;        
        border-bottom:1px solid #a8a8b0f7;

    }
</style>
<h1>
    <?php echo $user_login; ?>
</h1>
<p><a href="<?php echo get_edit_user_link($user_info->ID); ?>" target="_blank">اطلاعات کاربر</a></p>
<?php if ($csv_files_posts->have_posts()): ?>
<?php while ($csv_files_posts->have_posts()): ?>
<div style="margin-top:20px;">
    <?php $csv_files_posts->the_post(); ?>
    <h2>
        <?php the_title(); ?>
    </h2>
    <div>
        <?php the_content();?>
    </div>
    <?php $csv_file_id = get_post_meta(get_the_ID(),'_csv_file',true); ?>
    <?php if($csv_file_id): ?>
    <?php            
            $csv_url = wp_get_attachment_url($csv_file_id); 
            $csv_file = file_get_contents($csv_url);
            $first_deliminer_postion = strpos($csv_file,"\n");
            $fields_name_str = substr($csv_file,0,$first_deliminer_postion);
            $csv_fields_name = explode(',',$fields_name_str);
            $csv_arry = str_getcsv($csv_file,"\n");            
            $user_postion = strpos($csv_file,$user_login);
            $csv_file= substr($csv_file,$user_postion);
            $first_deliminer_postion = strpos($csv_file,"\n");
            $csv_file = substr($csv_file,0,$first_deliminer_postion);
            $csv_arry = explode(',',$csv_file);            
            if ($user_postion) { ?>
                <div class="field-container">
                <?php for ($i=1; $i < count($csv_arry); $i++) { ?>
                    <div>
                        <p style="padding:0 0.5rem;">
                            <span><strong><?php echo $csv_fields_name[$i] ?>:</strong></span><span><?php echo $csv_arry[$i];?></span>
                        </p>
                    </div>        
                <?php } ?>
                </div>
                
            <?php }else{
                echo "<h>" . __("can't find user") . "</h>";
            }
            ?>           
    
    <div class="clear"></div>
    <?php endif ?>
</div>
<?php endwhile; ?>
<?php else:?>
<h1><?php _e('Data has not be')?></h1>
<?php endif;?>