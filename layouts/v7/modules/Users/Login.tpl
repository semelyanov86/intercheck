{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Users/views/Login.php *}

{strip}
	<style>
		@import url(https://fonts.googleapis.com/css?family=Roboto:300);

.login-page {
  width: 360px;
  padding: 8% 0 0;
  margin: auto;
}
.form {
  position: relative;
  z-index: 1;
  background: #FFFFFF;
  max-width: 360px;
  margin: 0 auto 100px;
  padding: 45px;
  text-align: center;
  box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);
}
.form input {
  font-family: "Roboto", sans-serif;
  outline: 0;
  background: #f2f2f2;
  width: 100%;
  border: 0;
  margin: 0 0 15px;
  padding: 15px;
  box-sizing: border-box;
  font-size: 14px;
}
.form button {
  font-family: "Roboto", sans-serif;
  text-transform: uppercase;
  outline: 0;
  background: #4CAF50;
  width: 100%;
  border: 0;
  padding: 15px;
  color: #FFFFFF;
  font-size: 14px;
  -webkit-transition: all 0.3 ease;
  transition: all 0.3 ease;
  cursor: pointer;
}
.form button:hover,.form button:active,.form button:focus {
  background: #43A047;
}
.form .message {
  margin: 15px 0 0;
  color: #b3b3b3;
  font-size: 12px;
}
.form .message a {
  color: #4CAF50;
  text-decoration: none;
}
.form .forgotPassword-form {
  display: none;
}
.container {
  position: relative;
  z-index: 1;
  max-width: 300px;
  margin: 0 auto;
}
.container:before, .container:after {
  content: "";
  display: block;
  clear: both;
}
.container .info {
  margin: 50px auto;
  text-align: center;
}
.container .info h1 {
  margin: 0 0 15px;
  padding: 0;
  font-size: 36px;
  font-weight: 300;
  color: #1a1a1a;
}
.container .info span {
  color: #4d4d4d;
  font-size: 12px;
}
.container .info span a {
  color: #000000;
  text-decoration: none;
}
.container .info span .fa {
  color: #EF3B3A;
}
body {
  background: #76b852; /* fallback for old browsers */
  background: -webkit-linear-gradient(right, #76b852, #8DC26F);
  background: -moz-linear-gradient(right, #76b852, #8DC26F);
  background: -o-linear-gradient(right, #76b852, #8DC26F);
  background: linear-gradient(to left, #76b852, #8DC26F);
  font-family: "Roboto", sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;      
}
.user-logo {
	margin: 0 auto;
	padding-bottom: 20px;
		}
	</style>


<body>
<span class="app-nav"></span>
<div class="login-page">
  <div class="form">
  <img class="img-responsive user-logo" src="layouts/v7/resources/Images/vtiger.png">
  
  <div>
    <span class="{if !$ERROR}hide{/if} failureMessage" id="validationMessage">{$MESSAGE}</span>
    <span class="{if !$MAIL_STATUS}hide{/if} successMessage">{$MESSAGE}</span>
</div>
    <form class="forgotPassword-form" action="forgotPassword.php" method="POST">
    <label for="fusername">Username</label>
      <input id="fusername" type="text" name="username" placeholder="Username">
    <label for="email">Email</label>
    <input id="email" type="email" name="emailId" placeholder="Email">
    
    <button type="submit">Submit</button>
      <p class="message">Please enter details and submit <a href="#">Back to login page</a></p>
    </form>

    <form class="login-form" method="POST" action="index.php">
    <input type="hidden" name="module" value="Users" />
    <input type="hidden" name="action" value="Login" />
    <label for="username">Username</label>
    <input id="username" type="text" name="username" placeholder="Username">
    
    <label for="password">Password</label>
    <input id="password" type="password" name="password" placeholder="Password">

      <button type="submit">Sign in - login</button>
      <p class="message"><a href="#">forgot password?</a></p>
    </form>
  </div>
</div>

 
{literal}	
<script type="text/javascript">
	$('.message a').click(function(){
	$('form').animate({height: "toggle", opacity: "toggle"}, "slow");
});
</script>
{/literal}		

		
	{/strip}