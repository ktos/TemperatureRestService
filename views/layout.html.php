<!DOCTYPE html>
<html>
	<head>
		<title>TemperatureRestService</title>
		<meta charset="utf-8" />

		<style>						
			body
			{
				font-family: "Tahoma", "Arial", sans-serif;
				font-size: 12pt;
				
				width: 770px;
				margin: auto;				
			}
			
			footer
			{				
				margin-top: 3em;
				border-top: 1px teal solid;				
				font-size: 8pt;
				color: #aaa;
			}
			
			footer p
			{
				margin-top: 0;
				display: block;
				width: 100%;
				text-align: right;
			}
			
			section.info
			{
				font-size: 10pt;
			}
			
			a
			{
				color: teal;
				font-weight: bold;
			}
			
			header h1 a
			{
				text-decoration: none;
				font-size: 36pt;
			}			
			
			footer a
			{
				color: #aaa;
			}
			
			footer a:hover
			{
				color: teal;
			}
		</style>		
	</head>
	
	<body>
		<header>
			<h1><a href="http://github.com/ktos/TemperatureRestService/">TemperatureRestService</a></h1>			
		</header>		
		
		<section>			
			<?= content() ?>
		</section>		
		
		<footer>
			
		</footer>
	</body>
</html>