<ul class="nav <?php echo $this->self['subclass'] ?> mt-3 mb-3">
    <li class="nav-item <?php echo $this->hasRoute('main.default') ? ' active ' : ''; ?>">
        <a class="nav-link" href="/"><i class="fa fa-home"></i> Home</a>
    </li>
    <li class="nav-item <?php echo $this->hasRoute('download') ? ' active ' : ''; ?>">
        <a class="nav-link" href="/download"><i class="fa fa-download"></i> Download</a>
    </li>
    <li class="nav-item <?php echo $this->hasRoute('lyrics') ? ' active ' : ''; ?>">
        <a class="nav-link" href="/lyrics"><i class="fa fa-lyrics"></i> Lyrics</a>
    </li>
    <li class="nav-item <?php echo $this->hasRoute('journal') ? ' active ' : ''; ?>">
        <a class="nav-link" href="/journal"><i class="fa fa-history"></i> Journal</a>
    </li>
</ul>