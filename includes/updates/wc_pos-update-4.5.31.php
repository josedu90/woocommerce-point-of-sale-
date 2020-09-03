<?php
$role = get_role( 'cashier' );
if($role)
	$role->add_cap('edit_shop_order');