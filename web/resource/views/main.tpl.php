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
                            <a class="nav-link <?php echo $this->packages->key() < 1 ? 'active' : ''; ?>"
                               data-toggle="tab"
                               href="#<?php echo strtolower($items->type->name); ?>" role="tab">
                                <i class="fa fa-<?php echo strtolower($items->type->name); ?>"></i>
                                <?php echo $items->type->name; ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <!-- Tab panes -->
            <div class="tab-content">
                <?php foreach ($this->packages as $items) { ?>
                    <div class="tab-pane fade <?php echo $this->packages->key() < 1 ? 'show active' : ''; ?>"
                         id="<?php echo strtolower($items->type->name); ?>" role="tabpanel">

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-dark">
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
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="pkg[<?php echo $package->getId(); ?>]"
                                                       id="pkg-<?php echo $package->getId(); ?>"
                                                       class="custom-control-input"
                                                       data-type="<?php echo strtolower($items->type->name); ?>"
                                                       data-toggle="pkg-select" <?php echo $package->isEnabled()
                                                    ? 'checked' : ''; ?> />
                                                <label class="custom-control-label"
                                                       for="pkg-<?php echo $package->getId(); ?>">
                                                    <?php echo $package->getName(); ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-left text-muted">
                                            <?php echo $package->getShortDescription(); ?>
                                        </td>
                                        <td class="text-right">
                                            v<?php echo $package->getVersion(); ?>
                                        </td>
                                        <td class="text-right">
                                            <?php if ($package->hasAuth()) { ?>
                                                <button class="btn btn-sm btn-primary" data-toggle="modal"
                                                        data-target="#pkg-auth"
                                                        data-id="<?php echo $package->getId(); ?>"
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
                                        Enabled: <span data-element="total"
                                                       data-type="<?php echo strtolower($items->type->name); ?>">
                                            <?php echo $this->total; ?>
                                        </span>
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
