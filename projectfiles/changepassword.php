<?php
session_start();
require 'core/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<!--
	Copyright 2014-2015 Zachary Hebert, Patrick Gillespie
	This file is part of Methodocracy.org.

    Methodocracy.org is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Methodocracy.org is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with Methodocracy.org.  If not, see <http://www.gnu.org/licenses/>.
	
    Methodocracy TM is a trademark of Methodocracy.org (C)2014-2015, and all rights to that TM are reserved. Any modified versions are required to be marked as changed, so that their problems will not be attributed erroneously to authors of previous versions. And the name Methodocracy TM should be clearly labeled as the source of your work as long as any part of this work remains intact in part or in whole.
-->
<?php
$user = new User();

if(!$user->isLoggedIn()) {
	Redirect::to('index.php');
}

if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'password_current' => array(
				'required' => true,
				'min' => 6),
			'password_new' => array(
				'required' => true,
				'min' => 6),
			'password_new_again' => array(
				'required' => true,
				'min' => 6,
				'matches' => 'password_new')
		));

		if($validation->passed()) {
			if(Hash::make(Input::get('password_current'), $user->data()->salt) !== $user->data()->password) {
				echo 'Your current password is wrong.';
			} else {
				try {
					
					$salt = Hash::salt(32);	

					$user->update(array(
						'password' => Hash::make(Input::get('password_new'), $salt),
						'salt' => $salt
					));
					
					Session::flash('home', 'Your password has been changed!');
					Redirect::to('index.php');

				} catch(Exception $e) {
					die($e->getMessage());
				}
			}
		} else {
			foreach($validate->errors() as $error) {
				echo $error, '<br>';
			}
		}
	}
}
?>
<body>
<article>
<form action="" method="post">
	<div class="field">
		<label for="password_current">Current password:</label>
		<input type="password" name="password_current" id="password_current">
	</div>
	
	<div class="field">
	<label for="password_new">New password:</label>
		<input type="password" name="password_new" id="password_new">
	</div>

	<div class="field">
		<label for="password_new_again">New password again:</label>
		<input type="password" name="password_new_again" id="password_new_again">
	</div>

	<input type="submit" value="Change">
	<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
</form>
</article>
</body>
</html>