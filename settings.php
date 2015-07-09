<?php

$pixabay_images_gallery_languages = array('cs' => 'Čeština', 'da' => 'Dansk', 'de' => 'Deutsch', 'en' => 'English', 'es' => 'Español', 'fr' => 'Français', 'id' => 'Indonesia', 'it' => 'Italiano', 'hu' => 'Magyar', 'nl' => 'Nederlands', 'no' => 'Norsk', 'pl' => 'Polski', 'pt' => 'Português', 'ro' => 'Română', 'sk' => 'Slovenčina', 'fi' => 'Suomi', 'sv' => 'Svenska', 'tr' => 'Türkçe', 'vi' => 'Việt', 'th' => 'ไทย', 'bg' => 'Български', 'ru' => 'Русский', 'el' => 'Ελληνική', 'ja' => '日本語', 'ko' => '한국어', 'zh' => '简体中文');

add_action('admin_menu', 'pixabay_images_add_settings_menu');
function pixabay_images_add_settings_menu() {
    add_options_page(__('Pixabay Images Settings', 'pixabay_images'), __('Pixabay Images', 'pixabay_images'), 'manage_options', 'pixabay_images_settings', 'pixabay_images_settings_page');
    add_action('admin_init', 'register_pixabay_images_options');
}


function register_pixabay_images_options(){
    register_setting('pixabay_images_options', 'pixabay_images_options', 'pixabay_images_options_validate');
    add_settings_section('pixabay_images_options_section', '', '', 'pixabay_images_settings');
    add_settings_field('language-id', __('Language', 'pixabay_images'), 'pixabay_images_render_language', 'pixabay_images_settings', 'pixabay_images_options_section');
    add_settings_field('per_page-id', __('Images Per Page', 'pixabay_images'), 'pixabay_images_render_per_page', 'pixabay_images_settings', 'pixabay_images_options_section');
    add_settings_field('image_type-id', __('Image Types', 'pixabay_images'), 'pixabay_images_render_image_type', 'pixabay_images_settings', 'pixabay_images_options_section');
    add_settings_field('orientation-id', __('Orientation', 'pixabay_images'), 'pixabay_images_render_orientation', 'pixabay_images_settings', 'pixabay_images_options_section');
    add_settings_field('attribution-id', __('Attribution', 'pixabay_images'), 'pixabay_images_render_attribution', 'pixabay_images_settings', 'pixabay_images_options_section');
    add_settings_field('button-id', __('Button', 'pixabay_images'), 'pixabay_images_render_button', 'pixabay_images_settings', 'pixabay_images_options_section');
}


function pixabay_images_render_language(){
    global $pixabay_images_gallery_languages;
    $options = get_option('pixabay_images_options');
    $set_lang = substr(get_locale(), 0, 2);
    if (!$options['language']) $options['language'] = $pixabay_images_gallery_languages[$set_lang]?$set_lang:'en';
    echo '<select name="pixabay_images_options[language]">';
    foreach ($pixabay_images_gallery_languages as $k => $v) { echo '<option value="'.$k.'"'.($options['language']==$k?' selected="selected"':'').'>'.$v.'</option>'; }
    echo '</select>';
}

function pixabay_images_render_per_page(){
    $options = get_option('pixabay_images_options');
    echo '<input name="pixabay_images_options[per_page]" type="number" min="10" max="100" value="'.($options['per_page']?$options['per_page']:30).'">';
}

function pixabay_images_render_image_type(){
    $options = get_option('pixabay_images_options');
    ?>
    <label><input name="pixabay_images_options[image_type]" value="all" type="radio"<?= !$options['image_type'] | $options['image_type']=='all'?' checked="checked"':''; ?>> <?= _e('All', 'pixabay_images'); ?></label>
    <br><label><input name="pixabay_images_options[image_type]" value="photo" type="radio"<?= $options['image_type']=='photo'?' checked="checked"':''; ?>> <?= _e('Photos', 'pixabay_images'); ?></label>
    <br><label><input name="pixabay_images_options[image_type]" value="clipart" type="radio"<?= $options['image_type']=='clipart'?' checked="checked"':''; ?>> <?= _e('Cliparts', 'pixabay_images'); ?></label>
    <?php
}

function pixabay_images_render_orientation(){
    $options = get_option('pixabay_images_options');
    ?>
    <label><input name="pixabay_images_options[orientation]" value="all" type="radio"<?= !$options['orientation'] | $options['orientation']=='all'?' checked="checked"':''; ?>> <?= _e('All', 'pixabay_images'); ?></label>
    <br><label><input name="pixabay_images_options[orientation]" value="horizotal" type="radio"<?= $options['orientation']=='horizotal'?' checked="checked"':''; ?>> <?= _e('Hozizontal', 'pixabay_images'); ?></label>
    <br><label><input name="pixabay_images_options[orientation]" value="vertical" type="radio"<?= $options['orientation']=='vertical'?' checked="checked"':''; ?>> <?= _e('Vertical', 'pixabay_images'); ?></label>
    <?php
}

function pixabay_images_render_attribution(){
    $options = get_option('pixabay_images_options');
    echo '<label><input name="pixabay_images_options[attribution]" value="true" type="checkbox"'.(!$options['attribution'] | $options['attribution']=='true'?' checked="checked"':'').'> '.__('Insert image credits', 'pixabay_images').'</label>';
}

function pixabay_images_render_button(){
    $options = get_option('pixabay_images_options');
    echo '<label><input name="pixabay_images_options[button]" value="true" type="checkbox"'.(!$options['button'] | $options['button']=='true'?' checked="checked"':'').'> '.__('Show Pixabay button next to "Add Media"', 'pixabay_images').'</label>';
}


function pixabay_images_settings_page() { ?>
    <div class="wrap">
    <h2><?= _e('Pixabay Images', 'pixabay_images'); ?></h2>
    <form method="post" action="options.php">
        <?php
            settings_fields('pixabay_images_options');
            do_settings_sections('pixabay_images_settings');
            submit_button();
        ?>
    </form>
    <hr style="margin-bottom:20px">
    <p>
        Official <a href="https://pixabay.com/"><img src="https://pixabay.com/static/img/logo_640.png" style="width:120px;margin:0 5px;position:relative;top:5px"></a> plugin by <a href="http://efs.byrev.org/">Emilian Robert Vicol</a> and <a href="https://pixabay.com/service/imprint/">Simon Steinberger</a>.
        Serbian translation by <a href="http://firstsiteguide.com/">Ogi Djuraskovic</a>.
    </p>
    <p>Find us on <a href="https://www.facebook.com/pixabay">Facebook</a>, <a href="https://plus.google.com/+Pixabay">Google+</a> and <a href="https://twitter.com/pixabay">Twitter</a>.</p>
    </div>
<?php }


function pixabay_images_options_validate($input){
    global $pixabay_images_gallery_languages;
    $options = get_option('pixabay_images_options');
    if ($pixabay_images_gallery_languages[$input['language']]) $options['language'] = $input['language'];
    $per_page = intval($input['per_page']);
    if ($per_page >= 10 and $per_page <= 100) $options['per_page'] = $per_page;
    if (in_array($input['image_type'], array('all', 'photo', 'clipart'))) $options['image_type'] = $input['image_type'];
    if (in_array($input['orientation'], array('all', 'horizotal', 'vertical'))) $options['orientation'] = $input['orientation'];
    if ($input['attribution']) $options['attribution'] = 'true'; else $options['attribution'] = 'false';
    if ($input['button']) $options['button'] = 'true'; else $options['button'] = 'false';
    return $options;
}
?>
