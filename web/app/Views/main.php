<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>
<div class="row main">
    <div class="col-12">
        <div class="card card-block radius-0 p-0">
            <div class="pl-3 pr-3 mt-4">
                <h1 class="display-4"><i class="fa fa-archive" aria-hidden="true"></i> Packages</h1>
                <p class="lead mb-0">
                    <i class="fa fa-info-circle" aria-hidden="true"></i> Include the packages you need.
                </p>
                <hr class="my-3">
                <ul class="nav nav-pills mb-3">
                    <?php foreach ($this->packages as $items) { ?>
                        <li class="nav-item">
                            <a class="nav-link <?php print($this->packages->key() < 1 ? 'active' : ''); ?>"
                               data-toggle="tab"
                               href="#<?php print(strtolower($items->type)); ?>" role="tab">
                                <i class="fa fa-<?php echo(strtolower($items->type)); ?>"></i>
                                <?php print($items->type); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <!-- Tab panes -->
            <div class="tab-content">
                <?php foreach ($this->packages as $items) { ?>
                    <div class="tab-pane fade <?php print($this->packages->key() < 1 ? 'show active' : ''); ?>"
                         id="<?php print(strtolower($items->type)); ?>" role="tabpanel">

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-inverse">
                                <tr>
                                    <th class="text-left">Name</th>
                                    <th width="60%" class="text-left">Description</th>
                                    <th width="50" class="text-right">Version</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $this->total = 0; ?>
                                <?php foreach ($items as $package) { ?>
                                    <tr>
                                        <td class="text-left">
                                            <label class="custom-control custom-checkbox">
                                                <input type="checkbox" name="pkg[<?php print($package->getId()); ?>]"
                                                       class="custom-control-input"
                                                       data-toggle="pkg-select" <?php print($package->isEnabled()
                                                    ? 'checked' : ''); ?> />
                                                <span class="custom-control-indicator"></span>
                                                <?php print($package->getName()); ?>
                                            </label>
                                        </td>
                                        <td class="text-left text-muted">
                                            <?php print($package->getShortDescription()); ?>
                                        </td>
                                        <td class="text-right">
                                            v<?php print($package->getVersion()); ?>
                                        </td>
                                        <td class="text-right">
                                            <?php if ($package->hasAuth()) { ?>
                                                <button class="btn btn-sm btn-primary" data-toggle="modal"
                                                        data-target="#pkg-auth"
                                                        data-id="<?php print($package->getId()); ?>"
                                                        data-href="/packages/settings"
                                                        data-loading-text='<i class="fa fa-key fa-circle-o-notch fa-spin fa-fw" aria-hidden="true"></i>'>
                                                    <i class="fa fa-key" aria-hidden="true"></i>
                                                </button>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php $this->total = ($package->isEnabled() ? $this->total + 1 : $this->total); ?>
                                <?php } ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="3"></td>
                                    <th class="text-right">
                                        Enabled: <span data-element="total"><?php print($this->total); ?></span>
                                    </th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php $this->view('modals/pkg-auth'); ?>
<?php $this->endSection('content'); ?>
