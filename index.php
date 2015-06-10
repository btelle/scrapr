<?php require_once('loader.php'); ?>
<!doctype html>
<html>
<head>
    <title>scrapr</title>
    
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; minimum-scale=1.0; user-scalable=1;" />
    
    <link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.4/cosmo/bootstrap.min.css" rel="stylesheet">
    <link href="static/css/scrapr.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
              <span class="navbar-brand">scrapr</span>
            </div>

            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="#follow">People you follow <span class="badge"></span></a></li>
                    <li><a href="#search">Search results <span class="badge"></span></a></li>
                    <li><a href="#saved">Saved <span class="badge"></span></a></li>
                    <li class="dropdown" id="nav-settings">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Settings <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#follows">Follows</a></li>
                            <li><a href="#queries">Search queries</a></li>
                            <li><a href="#filters">Filters</a></li>
                            <li class="divider"></li>
                            <li><a href="#logs">View system logs</a></li>
                            <li><a href="#logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
                <form class="navbar-form navbar-right" role="login" id="nav-login">
                    <div class="form-group">
                        <input class="form-control" placeholder="Username" type="text" id="username">
                    </div>
                    <div class="form-group">
                        <input class="form-control" placeholder="Password" type="password" id="password">
                    </div>
                    <button type="submit" class="btn btn-default">Log in</button>
                </form>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="row">
            <div class="col-lg-12 messages">
            
            </div>
        </div>
    </div>
    
    <div class="container router default-off" id="route-default">
        <div class="row">
            <div class="col-lg-6 col-lg-offset-3">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <h3 class="panel-title">Please log in</h3>
                    </div>
                    <div class="panel-body">
                        You must log in to use this application
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <? require_once('templates/photos.php'); ?>
    <? require_once('templates/follows.php'); ?>
    <? require_once('templates/queries.php'); ?>
    <? require_once('templates/filters.php'); ?>
    <? require_once('templates/logs.php'); ?>
    <? require_once('templates/first_run.php'); ?>
    
    <script src="https://code.jquery.com/jquery-2.1.4.min.js" type="text/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="static/js/scrapr.js" type="text/javascript"></script>
</body>
</html>