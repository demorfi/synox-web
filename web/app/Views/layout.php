<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="description" content="" />

    <title>SynoX | <?php echo($this->title); ?></title>
    <link rel="stylesheet" href="/stylesheet/tether.min.css" />
    <link rel="stylesheet" href="/stylesheet/bootstrap.min.css" />
    <link rel="stylesheet" href="/stylesheet/font-awesome.min.css" />
    <link rel="stylesheet" href="/stylesheet/main.css" />
    <?php foreach ($this->styles() as $style) { ?>
        <link rel="stylesheet" href="/stylesheet/<?php echo($style); ?>.css" />
    <?php } ?>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php $this->view('menu'); ?>
        <div id="content" class="col-12 col-md-10">
            <div class="row">
                <div class="col-12">
                    <nav class="breadcrumb mt-3 radius-0">
                        <?php if ($this->hasRoute('main.default')) { ?>
                            <span class="breadcrumb-item active"><i class="fa fa-home"></i> Home</span>
                        <?php } else { ?>
                            <a class="breadcrumb-item" href="/"><i class="fa fa-home"></i> Home</a>
                            <span class="breadcrumb-item active"><?php echo($this->name ?: $this->title); ?></span>
                        <?php } ?>
                    </nav>
                </div>
            </div>
            <div class="alert-messages" hidden>
                <div class="col-12">
                    <div class="alert alert-dismissible radius-0" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        <span class="message"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <?php echo($this->block('content')); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="p-3 text-center hidden-md-up">
    <p class="small m-0">
        <a href="https://github.com/demorfi/synox" target="_blank">
            <i class="fa fa-code"></i> with <i class="fa fa-heart"></i> in Siberia.
        </a>
    </p>
</footer>

<script src="/javascript/jquery-3.1.1.min.js"></script>
<script src="/javascript/tether.min.js"></script>
<script src="/javascript/bootstrap.min.js"></script>
<?php foreach ($this->javascripts() as $javascript) { ?>
    <script src="/javascript/<?php echo($javascript); ?>.js"></script>
<?php } ?>
<script src="/javascript/main.js"></script>
</body>
</html>
