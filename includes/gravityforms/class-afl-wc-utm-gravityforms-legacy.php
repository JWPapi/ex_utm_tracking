<?php defined( 'ABSPATH' ) || exit;

/**
 *
 */
class AFL_WC_UTM_GRAVITYFORMS_LEGACY
{

  public static function action_gform_save_field_value($value, $entry, $field, $form){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS_ADDON::c_action_gform_save_field_value', '2.4.0');

    try {

      //check form setting
      if (!self::is_form_enabled($form)) :
        return $value;
      endif;

      if (empty($field->allowsPrepopulate)) :
        return $value;
      endif;

      if (strpos($field->inputName, 'afl_wc_utm:', 0) !== 0) :
        return $value;
      endif;

      $name_split = explode(':', $field->inputName, 2);
      $meta_name = isset($name_split[1]) ? $name_split[1] : null;

      if ($meta_name === null) {
        return $value;
      }

      $user_id = rgar($entry, 'created_by');
      $meta = AFL_WC_UTM_SERVICE::get_user_synced_session($user_id);

      //@afl
      if (isset($meta[$meta_name]) && isset($meta[$meta_name]['value'])) :

        if (isset($meta[$meta_name]['type']) && $meta[$meta_name]['type'] === 'timestamp' && !empty($meta[$meta_name]['value'])) :
          $value = AFL_WC_UTM_UTIL::local_date_format($meta[$meta_name]['value'], 'Y-m-d H:i:s');
        else:
          $value = $meta[$meta_name]['value'];
        endif;

      endif;

    } catch (\Exception $e) {

    }

    return $value;
  }

  public static function save_form_fields_merge_tag_value($attribution, $entry, $form){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS::save_form_fields_merge_tag_value', '2.4.6');

    if (!isset($form['fields'])) :
      return false;
    endif;

    foreach ($form['fields'] as $field_key => $field) :

      $default_value = rgar($field, 'defaultValue');

      if (!empty($default_value) && AFL_WC_UTM_UTIL::has_merge_tag($default_value)) :

        $merge_value = AFL_WC_UTM_UTIL::get_merge_tag_value($default_value, $session['user_synced_session']);
        gform_update_meta($entry['id'], rgar($field, 'id'), sanitize_text_field($merge_value));

      elseif ((rgar($field, 'allowsPrepopulate'))) :

        $input_name = rgar($field, 'inputName');

        if (AFL_WC_UTM_UTIL::has_merge_tag($input_name)) :

          $merge_value = AFL_WC_UTM_UTIL::get_merge_tag_value($input_name, $session['user_synced_session']);
          gform_update_meta($entry['id'], rgar($field, 'id'), sanitize_text_field($merge_value));

        endif;

      endif;

    endforeach;

    return true;

  }

  public static function get_form_fields_merge_tag_value($entry_id, $form){

    _deprecated_function('AFL_WC_UTM_GRAVITYFORMS::get_form_fields_merge_tag_value', '2.4.6');

    $output = array();

    if (!isset($form['fields'])) :
      return $output;
    endif;

    foreach ($form['fields'] as $field_key => $field) :

      $field_id = rgar($field, 'id');
      $default_value = rgar($field, 'defaultValue');

      if (!empty($default_value) && AFL_WC_UTM_UTIL::has_merge_tag($default_value)) :

        $meta_value = gform_get_meta($entry_id, $field_id);

        if ($meta_value !== false) :
          $output[$field_id] = $meta_value;
        endif;

      elseif ((rgar($field, 'allowsPrepopulate'))) :

        $input_name = rgar($field, 'inputName');

        if (AFL_WC_UTM_UTIL::has_merge_tag($input_name)) :

          $meta_value = gform_get_meta($entry_id, $field_id);

          if ($meta_value !== false) :
            $output[$field_id] = $meta_value;
          endif;

        endif;

      endif;

    endforeach;

    return $output;
  }

}
