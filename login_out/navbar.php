<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <style>
		.navbar {
			display: flex;
			justify-content: flex-start; /*讓導航欄靠左*/
			background-color: #333;
			overflow: hidden;
		}

		.navbar a {
			display: block;
			color: white;
			text-align: center;
			padding: 14px 16px;
			text-decoration: none;
		}

		.navbar a:hover {
			background-color: #ddd;
			color: black;
		}

		.logout {
			margin-left: auto; /* 使用 margin-left: auto; 推動 Logout 靠右*/
		}

    </style>
</head>
<body>
    <div class="navbar">
        <!-- <a href="/web1/Index/index.php">Index</a> -->
        <a href="/web1/Fw_release_build/fw_r_main.php">Fw_release_build</a>
        <a href="/web1/Device_control/button3.php">Device_control</a>
		<!-- <a href="/web1/Automation/automation.php">Automation</a> -->
		<a href="/web1/login_out/logout.php" class="logout">Logout</a>
        <!-- more links -->
    </div>

</body>
</html>
