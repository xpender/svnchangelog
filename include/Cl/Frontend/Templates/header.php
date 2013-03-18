<!DOCTYPE html>
<html lang="en">
<head>
    <title>svnchangelog</title>    
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="utf-8" />
    
    <!-- Le styles -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
    }
    </style>
    <link href="/css/bootstrap-responsive.min.css" rel="stylesheet">
</head>
<body>

<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="brand" href="#">svnchangelog</a>
            <div class="nav-collapse collapse">
            <ul class="nav">
                <li<?=($this->get('sCurrentAction') == 'index' ? ' class="active"' : '');?>><a href="/">Home</a></li>
                <li class="divider-vertical"></li>
                <?php if ($this->get('sCurrentProject')) { ?>
                <li<?=($this->get('sCurrentAction') == 'changelog' ? ' class="active"' : '');?>><a href="/?action=changelog">Changelog</a></li>
                <?php } else { ?>
                <li><a href="#"><strong>Select project &gt; &gt;</strong></a></li>
                <?php } ?>
            </ul>
            <ul class="nav pull-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Project<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                    <li>
                        <?php
                        $aProjects = $this->get('aProjects');

                        foreach ($aProjects as $sProject) {
                        ?>
                        <a href="/?action=changelog&project=<?=$sProject?>"><?=$sProject?></a>
                        <?php
                        }
                        ?>
                    </li>
                    </ul>
                </li>
            </ul>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row-fluid">
