<?php
if ($_SERVER['REMOTE_ADDR'] !== '::1')
{
    header('HTTP/1.1 403 Forbidden');
    echo '<h1>HTTP/1.1 403 - Access Denied</h1>';
    echo '<p>You must be on localhost to access this page. Refusing to serve request.</p>';
    exit;
}

// Run the app
try {

// Load the same autoloader as the project
$app = require_once sprintf('%s/vendor/autoload.php', BASE_PATH);

// Project configuration class
$project = new class
{
    public string $path;
    public string $name;
	public string $version;

    public function __construct()
    {
        $this->path = BASE_PATH;
        $this->name = ucwords(str_replace('-', ' ', basename(BASE_PATH)));
		$this->version = $this->getVersion();
    }

	protected function getVersion(): string
	{
		return json_decode(file_get_contents($this->path . '/composer.json'))->require->{'hyde/framework'};
	}
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="description" content="" />
<meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors" />
<!-- Created with the Bootstrap Dashboard Template https://getbootstrap.com/docs/5.1/examples/dashboard/ -->
<title>Dashboard Template · Bootstrap v5.1</title>
<!-- Bootstrap core CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous" />
<!-- Dashboard CSS -->
<style>
body {
	font-size: .875rem;
}
.feather {
	width: 16px;
	height: 16px;
	vertical-align: text-bottom;
}
/*
* Sidebar
*/
.sidebar {
	position: fixed;
	top: 0;
	bottom: 0;
	left: 0;
	z-index: 100;
	/* Behind the navbar */
	padding: 48px 0 0;
	/* Height of navbar */
	box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}
@media (max-width: 767.98px) {
	.sidebar {
		top: 5rem;
	}
}
.sidebar-sticky {
	position: relative;
	top: 0;
	height: calc(100vh - 48px);
	padding-top: .5rem;
	overflow-x: hidden;
	overflow-y: auto;
	/* Scrollable contents if viewport is shorter than content. */
}
.sidebar .nav-link {
	font-weight: 500;
	color: #333;
}
.sidebar .nav-link .feather {
	margin-right: 4px;
	color: #727272;
}
.sidebar .nav-link.active {
	color: #2470dc;
}
.sidebar .nav-link:hover .feather,
.sidebar .nav-link.active .feather {
	color: inherit;
}
.sidebar-heading {
	font-size: .75rem;
	text-transform: uppercase;
}
/*
* Navbar
*/
.navbar-brand {
	padding-top: .75rem;
	padding-bottom: .75rem;
	font-size: 1rem;
	background-color: rgba(0, 0, 0, .25);
	box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
}
.navbar .navbar-toggler {
	top: .25rem;
	right: 1rem;
}
.navbar .form-control {
	padding: .75rem 1rem;
	border-width: 0;
	border-radius: 0;
}
.form-control-dark {
	color: #fff;
	background-color: rgba(255, 255, 255, .1);
	border-color: rgba(255, 255, 255, .1);
}
.form-control-dark:focus {
	border-color: transparent;
	box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
}
</style>
<meta name="theme-color" content="#7952b3" />
<style>
.bd-placeholder-img {
	font-size: 1.125rem;
	text-anchor: middle;
	-webkit-user-select: none;
	-moz-user-select: none;
	user-select: none;
}
@media (min-width: 768px) {
	.bd-placeholder-img-lg {
		font-size: 3.5rem;
	}
}
</style>
</head>
<body>
   <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
      <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">
      <?=e($project->name) ?>
      </a>
      <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
      </button>
      <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search" />
      <div class="navbar-nav">
         <div class="nav-item text-nowrap">
            <a class="nav-link px-3" href="#">Sign out</a>
         </div>
      </div>
   </header>
   <div class="container-fluid">
      <div class="row">
         <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
               <ul class="nav flex-column">
                  <li class="nav-item">
                     <a class="nav-link active" aria-current="page" href="#">
                     <span data-feather="home"></span>
                     Dashboard
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" href="#">
                     <span data-feather="file"></span>
                     Orders
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" href="#">
                     <span data-feather="shopping-cart"></span>
                     Products
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" href="#">
                     <span data-feather="users"></span>
                     Customers
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" href="#">
                     <span data-feather="bar-chart-2"></span>
                     Reports
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" href="#">
                     <span data-feather="layers"></span>
                     Integrations
                     </a>
                  </li>
               </ul>
               <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                  <span>Saved reports</span>
                  <a class="link-secondary" href="#" aria-label="Add a new report">
                  <span data-feather="plus-circle"></span>
                  </a>
               </h6>
               <ul class="nav flex-column mb-2">
                  <li class="nav-item">
                     <a class="nav-link" href="#">
                     <span data-feather="file-text"></span>
                     Current month
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" href="#">
                     <span data-feather="file-text"></span>
                     Last quarter
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" href="#">
                     <span data-feather="file-text"></span>
                     Social engagement
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" href="#">
                     <span data-feather="file-text"></span>
                     Year-end sale
                     </a>
                  </li>
               </ul>
            </div>
         </nav>
         <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
               <h1 class="h2">Hyde Dashboard</h1>
            </div>
			
            <h2>
				Project Information
			</h2>
            <div class="table-responsive">
               <table class="table table-striped table-sm">
                  <thead>
                     <tr>
						<th scope="col">Project Name</th>
						<th scope="col">Project Path</th>
						<th scope="col">Hyde Version</th>
                     </tr>
                  </thead>
                  <tbody>
					<td><?= $project->name ?></td>
					<td><?= $project->path ?></td>
					<td><?= $project->version ?></td>
                  </tbody>
               </table>
            </div>
         </main>
      </div>
   </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
<!-- Dashboard scripts -->
<script>
/* feather:false */

(function() {
	'use strict'
	
	feather.replace({
		'aria-hidden': 'true'
	})
})()
</script>
</body>
</html>

<?php

} catch (\Throwable $th) {
	echo '<h1>Error</h1>';
	echo '<p>An error occurred while processing your request.</p>';
	echo '<pre><code>'.$th->getMessage().'</code></pre>';
	echo '<p>Extra information:</p>';
	echo '<pre><code>'.$th->getTraceAsString().print_r($th, true).'</code></pre>';
	exit($th->getCode());
}
