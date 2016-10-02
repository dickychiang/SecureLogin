/**
 *	Part of a framework for a simple user authentication.
 */

User = {
	/**
	 * generates a random hex string
	 */
	randomString: function(len)
	{
		var hex = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f'];
		var string = "";
		while(len-->0)
		{
			string += hex[parseInt(16*Math.random())];
		}
		return string;
	},

	processLogin: function()
	{
		var valid = true;
		var form = document.getElementById('log_in');
		var username = form["username"].value;
		var password = form["password"].value;

		if(password.trim() == "" || username.trim() == "")
		{
			document.getElementById('error').innerHTML = "You need to fill in a username or password";
			return false;
		}
		else
		{
			form["sha1"].value = Sha1.hash(form["password"].value);
			form["password"].value = this.randomString(16);
			form.submit();
			return true;
		}
	},

	processRegistration: function()
	{
		var valid = true;
		var form = document.getElementById('registration');
		var username = form["username"].value;
		var password = form["password"].value;

		if(password.trim() == "" || username.trim() == "")
		{
			document.getElementById('error').innerHTML = "You need to fill in a username or password";
			return false;
		}

		if(password.length < 8)
		{
			document.getElementById('error').innerHTML = "Your password is easy too guess, please try to put it longer";
			return false;
		}

		form["sha1"].value = Sha1.hash(form["password"].value);
		form["password"].value = this.randomString(16);
		form.submit();
		return true;
	},

	processLogout: function()
	{
		var form = document.getElementById('log_out');

		form.submit();
	},

	processResetpassword: function()
	{
		var form = document.getElementById('resetpassword');
		var password = form["password"].value

		if(password.trim() == "")
		{
			document.getElementById('error').innerHTML = "You need to fill in a username or password";
			return false;
		}
		else
		{
			form["sha1"].value = Sha1.hash(form["password"].value);
			form["password"].value = this.randomString(16);
			form.submit();
			return true;
		}
	},

	prev : "",

	processDoAdmin: function()
	{
	//	console.log(document.getElementById(this.id).value);

		var targetElement = event.target || event.srcElement;
		//console.log(targetElement);
		//console.log(targetElement['id']);
		var num = targetElement['id'];
		var user_name = targetElement['value'];
		//console.log(this.prev);

		if(this.prev != num)
		{
			this.prev = num;
			var form = document.createElement('form');
			form.id = "admin_update_user";
			form.method = "post";
			form.action = "admin.php";

			var op = document.createElement("input");
			op.type = "hidden";
			op.name = "op";
			op.value = "admin_update";

			var sha1 = document.createElement("input");
			sha1.type = "hidden";
			sha1.name = "sha1";
			sha1.value = "";

			var user = document.createElement("input");
			user.type = "hidden";
			user.name = "username";
			user.value = user_name;

			var label = document.createElement("label");
			label.innerHTML = "New password :";

			var password_input = document.createElement('input');
			password_input.id = "password";
			password_input.name = "password";
			password_input.type = "password";
			password_input.value = "";

			var role_label = document.createElement("label");
			role_label.innerHTML = "Set permision as :";

			var role_user = document.createElement('input');
			role_user.id = "role";
			role_user.name = "role";
			role_user.type = "radio";
			role_user.value = "user";

			var role_label_user = document.createElement("label");
			role_label_user.innerHTML =  " User";

			var role_admin = document.createElement('input');
			role_admin.id = "role";
			role_admin.name = "role";
			role_admin.type = "radio";
			role_admin.value = "admin";

			var role_label_admin = document.createElement("label");
			role_label_admin.innerHTML =  " Admin";

			var update = document.createElement('input');
			update.id = "admin_update";
			update.type = "button";
			update.value = "Update";
			update.onclick = function() {
				var form = document.getElementById('admin_update_user');

				if(form["password"].value != "")
				{
					form["sha1"].value = Sha1.hash(form["password"].value);
					form["password"].value = User.randomString(16);
					form.submit();
				}
				else
				{
					form["password"].value = "";
					form.submit();
				}
			}

			form.appendChild(op);
			form.appendChild(sha1);
			form.appendChild(user);
			form.appendChild(label);
			form.appendChild(password_input);
			form.appendChild(role_label);
			form.appendChild(role_label_user);
			form.appendChild(role_user);
			form.appendChild(role_label_admin);
			form.appendChild(role_admin);
			form.appendChild(update);

			document.getElementsByClassName("output")[targetElement['id']].appendChild(form);

		}
		else
		{
			document.getElementsByClassName("output")[targetElement['id']].innerHTML = "";
			this.prev = "";
		}

	},
};
