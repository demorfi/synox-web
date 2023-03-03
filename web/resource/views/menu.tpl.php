<div id="nav-top" class="col-12 d-md-none">
    <nav class="navbar navbar-dark pl-0 pr-0">
        <div class="row">
            <div class="col-12">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-top"
                        aria-controls="navbar-top" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-brand ml-3">SynoX</div>
            </div>
        </div>
    </nav>
    <div class="collapse" id="navbar-top">
        <?php $this->view('parts/_navigation', ['subclass' => 'nav-pills nav-fill']); ?>
    </div>
</div>
<div id="nav-left" class="col-md-2 d-none d-md-block">
    <nav class="col-md-2 p-0">
        <div class="brand">SynoX</div>
        <?php $this->view('parts/_navigation', ['subclass' => 'flex-column']); ?>
        <nav class="footer">
            <p class="small">
                <a href="https://github.com/demorfi/synox" target="_blank">
                    <i class="fa fa-code"></i> with <i class="fa fa-heart"></i> in Siberia.
                </a>
            </p>
        </nav>
    </nav>
</div>
