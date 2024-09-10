<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

	require_once('./stripephp/init.php');

	$stripe = new \Stripe\StripeClient('pk_test_TYooMQauvdEDq54NiTphI7jx');
	if( isset($_POST['source']) ){
		$charge = $stripe->charges->create([
			'amount' => 2000,
			'currency' => 'usd',
			'source' => 'tok_visa',
			'description' => 'My First Test Charge (created for API docs at https://www.stripe.com/docs/api)',
		]);
		echo $charge;
	}
	echo 'false';
	array_key_exists(key, array)
?>