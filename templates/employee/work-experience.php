<div class="hrm-update-notification"></div>
<?php
if ( isset( $_REQUEST['employee_id'] ) && $_REQUEST['employee_id'] ) {
    $employer_id = intval( $_REQUEST['employee_id'] );
} else {
    $employer_id = get_current_user_id();
}
?>

<div id="hrm-employee-work-experience"></div>
<?php

$results = hrm_Settings::getInstance()->conditional_query_val( 'hrm_work_experience', '*', array( 'emp_number' => $employer_id ) );
$total = $results['total_row'];

foreach ( $results as $key => $value) {
    if ( $results['total_row'] == 0 || $key === 'total_row' ) {
      continue;
    }

    $del_checkbox = '<input class="hrm-single-checked" name="hrm_check['.$value->id.']" value="" type="checkbox">';
    $delete_text  = '<a href="#" class="hrm-delete" data-id='.$value->id.'>'.__( 'Delete', 'hrm' ).'</a>';
    $td_attr[][0] = 'class="hrm-table-checkbox"';

    $name_id = '<div class="hrm-title-wrap"><a href="#" class="hrm-editable hrm-title" data-table_option="hrm_work_experience" data-id='.$value->id.'>'.$value->eexp_company.'</a>
    <div class="hrm-title-action"><a href="#" class="hrm-editable hrm-edit" data-table_option="hrm_work_experience" data-id='.$value->id.'>'.__( 'Edit', 'hrm' ).'</a>'
    .$delete_text. '</div></div>';

    $body[] = array(
        $del_checkbox,
        $name_id,
        $value->eexp_jobtit,
        hrm_get_date2mysql( $value->eexp_from_date ),
        hrm_get_date2mysql( $value->eexp_to_date ),
        $value->eexp_comments,
    );
}

$table = array();
$table['head']       = array( '<input class="hrm-all-checked" type="checkbox">', __( 'Company', 'hrm'), __( 'Job Title', 'hrm'), __( 'From', 'hrm'), __( 'To', 'hrm'), __( 'Comment', 'hrm') );
$table['body']       = isset( $body ) ? $body : array();
$table['td_attr']    = isset( $td_attr ) ? $td_attr : array();
$table['table_attr'] = array( 'class' => 'widefat' );
$table['table']      = 'hrm_work_experience';
$table['action']     = 'hrm_delete';
$table['tab']        = $tab;
$table['subtab']     = $subtab;
$table['page']       = $page;

echo hrm_Settings::getInstance()->table( $table );
$url = hrm_Settings::getInstance()->get_current_page_url( $page, $tab, $subtab ) . '&employee_id='. $employer_id;
$file_path = urlencode(__FILE__);
global $hrm_is_admin;
?>
<script type="text/javascript">
    jQuery(function($) {
        hrm_dataAttr = {
           add_form_generator_action : 'add_form',
           add_form_apppend_wrap : 'hrm-employee-work-experience',
           redirect : '<?php echo $url; ?>',
           class_name : 'hrm_Employee',
           function_name : 'work_experience',
           employee_id: "<?php echo $employer_id; ?>",
           page: '<?php echo $page; ?>',
           tab: '<?php echo $tab; ?>',
           subtab: '<?php echo $subtab; ?>',
           req_frm: '<?php echo $file_path; ?>',
           is_admin : '<?php echo $hrm_is_admin; ?>'
        };
    });
</script>