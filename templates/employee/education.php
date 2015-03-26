<div class="hrm-update-notification"></div>
<?php
if ( hrm_current_user_role() == 'hrm_employee' ) {
    $employer_id = get_current_user_id();
} else {
    $employer_id = isset( $_REQUEST['employee_id'] ) ? trim( $_REQUEST['employee_id'] ) : '';
}
?>
<div id="hrm_personal_education"></div>

<?php

$results = hrm_Settings::getInstance()->conditional_query_val( 'hrm_personal_education', $field = '*', $compare = array( 'emp_id' => $employer_id ) );
$education_id = isset( $results['education_id'] ) ? $results['education_id'] : array();
$education_id = wp_list_pluck( $education_id, 'education_id' );

$compare = array(
  'id' => $education_id
);
$edu_labels = hrm_Settings::getInstance()->hrm_query( 'hrm_education' );

unset( $edu_labels['total_row'] );

$label = array();
foreach ( $edu_labels as $key => $edu_label ) {
  $label[$edu_label->id] = $edu_label->name;
}



foreach ( $results as $key => $value) {

    if ( $results['total_row'] == 0 || $key === 'total_row' ) {
      continue;
    }

    if ( !isset( $label[$value->education_id] ) ) {
        continue;
    }

    $body[] = array(
        '<input name="hrm_check['.$value->id.']" value="" type="checkbox">',
        '<a href="#" class="hrm-editable" data-table_option="hrm_personal_education" data-emp_id="'.$value->emp_id.'" data-id='.$value->id.'>'.$label[$value->education_id].'<a>',
        $value->institute,
        $value->major,
        hrm_get_date2mysql( $value->year ),
        $value->score,
        hrm_get_date2mysql( $value->start_date ),
        hrm_get_date2mysql( $value->end_date ),
    );

    $td_attr[] = array(
        'class="check-column"'
    );
}

$table               = array();
$table['head']       = array( '<input type="checkbox">', __( 'Level', 'hrm'), __( 'Institute', 'hrm'), __( 'Major/Specialization', 'hrm'), __( 'Year', 'hrm'), __( 'GPA/Score', 'hrm'), __( 'Start Date', 'hrm'), __( 'End Date', 'hrm') );
$table['body']       = isset( $body ) ? $body : array();
$table['td_attr']    = isset( $td_attr ) ? $td_attr : array();
$table['th_attr']    = array( 'class="check-column"' );
$table['table_attr'] = array( 'class' => 'widefat' );
$table['table']      = 'hrm_personal_education';
$table['action']     = 'hrm_delete';
$table['tab']        = $tab;
$table['subtab']     = $subtab;

echo hrm_Settings::getInstance()->table( $table );
$url = hrm_Settings::getInstance()->get_current_page_url( $page, $tab, $subtab ) . '&employee_id='. $employer_id;
$file_path = urlencode(__FILE__);
?>
<script type="text/javascript">
    jQuery(function($) {
        hrm_dataAttr = {
            add_form_generator_action : 'add_form',
            add_form_apppend_wrap : 'hrm_personal_education',
            class_name : 'hrm_Employee',
            function_name : 'education',
            redirect : '<?php echo $url; ?>',
            education: '<?php echo json_encode( $label); ?>',
            employee_id: "<?php echo $employer_id; ?>",
            page: '<?php echo $page; ?>',
            tab: '<?php echo $tab; ?>',
            subtab: '<?php echo $subtab; ?>',
            req_frm: '<?php echo $file_path; ?>',
            subtab: true
        };
    });
</script>