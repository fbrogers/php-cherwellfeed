<?php if(!isset($data) or !($data instanceof TemplateData)) die('Data not passed.');
    $data->site_template('ucf_admin');
    $data->site_title('Cherwell Dashboard');
    $data->site_subtitle('SDES Information Technology');
    $data->site_subtitle_href('../');
    $data->site_css('css/style.css', 'screen');
    $data->site_navigation([
    	'Team Incidents' => './',
    	'Resolved Team Incidents' => './?filter=resolved',
    	'My Incidents' => './?filter=my'
    ]);

    $asana_email = 'x+{0}@mail.asana.com';
    $asana_json = 'https://app.asana.com/api/1.0/projects/{0}/tasks?opt_pretty&opt_expand=.';
?>