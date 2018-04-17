<?php

// Routes

/* @var $slim Slim\App */
$slim->group('/advancedcontentfilter/api', function () {
	/* @var $this Slim\App */
	$this->group('/rules', function () {
		/* @var $this Slim\App */
		$this->get('', 'advancedcontentfilter_get_rules');
		$this->post('', 'advancedcontentfilter_post_rules');

		$this->get('/{id}', 'advancedcontentfilter_get_rules_id');
		$this->put('/{id}', 'advancedcontentfilter_put_rules_id');
		$this->delete('/{id}', 'advancedcontentfilter_delete_rules_id');
	});

	$this->group('/variables', function () {
		/* @var $this Slim\App */
		$this->get('/{guid}', 'advancedcontentfilter_get_variables_guid');
	});
});
