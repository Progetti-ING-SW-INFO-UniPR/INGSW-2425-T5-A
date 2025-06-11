<?php
function isValidUsername($username) : bool {
	return preg_match("/^[A-Za-z0-9]+$/", $username) == 1;
	// return true;
}

function isValidPassword($pwd) : bool {
	return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $pwd) == 1;
	// return true;
}

function isValidEmail($email) : bool {
	return preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$/", $email) == 1;
	// return true;
	//Aa12345!
}
?>