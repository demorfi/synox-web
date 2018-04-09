<div id="nav-top" class="col-12 hidden-md-up">
    <nav class="navbar navbar-inverse pl-0 pr-0">
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
        <ul class="nav nav-pills nav-fill mt-3 mb-3">
            <li class="nav-item <?php echo $this->hasRoute('main.default') ? ' active ' : ''; ?>">
                <a class="nav-link" href="/"><i class="fa fa-home"></i> Home</a>
            </li>
            <li class="nav-item <?php echo $this->hasRoute('downloads') ? ' active ' : ''; ?>">
                <a class="nav-link" href="/downloads"><i class="fa fa-downloads"></i> Downloads</a>
            </li>
            <li class="nav-item <?php echo $this->hasRoute('lyrics') ? ' active ' : ''; ?>">
                <a class="nav-link" href="/lyrics"><i class="fa fa-lyrics"></i> Lyrics</a>
            </li>
            <li class="nav-item <?php echo $this->hasRoute('journal') ? ' active ' : ''; ?>">
                <a class="nav-link" href="/journal"><i class="fa fa-history"></i> Journal</a>
            </li>
        </ul>
    </div>
</div>
<div id="nav-left" class="col-md-2 hidden-md-down">
    <nav class="col-md-2 p-0">
        <div class="brand">SynoX</div>
        <ul class="nav flex-column mt-3 mb-3">
            <li class="nav-item <?php echo $this->hasRoute('main.default') ? ' active ' : ''; ?>">
                <a class="nav-link" href="/"><i class="fa fa-home"></i> Home</a>
            </li>
            <li class="nav-item <?php echo $this->hasRoute('downloads') ? ' active ' : ''; ?>">
                <a class="nav-link" href="/downloads"><i class="fa fa-downloads"></i> Downloads</a>
            </li>
            <li class="nav-item <?php echo $this->hasRoute('lyrics') ? ' active ' : ''; ?>">
                <a class="nav-link" href="/lyrics"><i class="fa fa-lyrics"></i> Lyrics</a>
            </li>
            <?php if (config('app')->get('journal')) { ?>
            <li class="nav-item <?php echo $this->hasRoute('journal') ? ' active ' : ''; ?>">
                <a class="nav-link" href="/journal"><i class="fa fa-history"></i> Journal</a>
            </li>
            <?php } ?>
        </ul>
        <nav class="footer">
            <p class="small">
                <a href="https://github.com/demorfi/synox" target="_blank">
                    <i class="fa fa-code"></i> with <i class="fa fa-heart"></i> in Siberia.
                </a>
            </p>
        </nav>
    </nav>
</div>
