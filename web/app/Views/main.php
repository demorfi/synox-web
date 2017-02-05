<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>
    <div class="row main">
        <div class="col-12">
            <div class="jumbotron jumbotron-fluid pb-0 mb-0 pl-3 pr-3">
                <div class="row">
                    <div class="col-8">
                        <h1 class="display-4"><i class="fa fa-archive" aria-hidden="true"></i> Packages</h1>
                        <p class="lead mb-0">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> Include the packages you need.
                        </p>
                    </div>
                    <div class="col-4">
                        <blockquote class="blockquote blockquote-reverse">
                            <p class="mb-0 small">Your API Key</p>
                            <p class="small"><kbd><?php print(config('system')->get('api-key')) ?></kbd></p>
                        </blockquote>
                    </div>
                </div>
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

                <!-- Tab panes -->
                <div class="tab-content">
                    <?php foreach ($this->packages as $items) { ?>
                        <div class="tab-pane fade <?php print($this->packages->key() < 1 ? 'show active' : ''); ?>"
                             id="<?php print(strtolower($items->type)); ?>" role="tabpanel">
                            <fieldset class="form-group">
                                <?php foreach ($items as $package) { ?>
                                    <div class="form-check clearfix">
                                        <div class="pull-left">
                                            <label class="custom-control custom-checkbox">
                                                <input type="checkbox" name="pkg[<?php print($package->getId()); ?>]"
                                                       class="custom-control-input"
                                                       data-toggle="pkg-select" <?php print($package->isEnabled() ? 'checked' : ''); ?> />
                                                <span class="custom-control-indicator"></span>
                                                <span class="custom-control-description"
                                                      data-content="<?php print($package->getShortDescription()); ?>"
                                                      data-toggle="popover" data-trigger="hover" data-delay="800">
                                                    <?php print($package->getName()); ?>
                                                </span>
                                            </label>
                                        </div>
                                        <?php if ($package->hasAuth()) { ?>
                                            <div class="pull-left">
                                                <i class="fa btn-link fa-key pointer" aria-hidden="true"
                                                   data-toggle="modal" data-target="#pkg-auth"
                                                   data-id="<?php print($package->getId()); ?>"
                                                   data-href="/packages/settings"
                                                   data-loading-class="fa-circle-o-notch fa-spin fa-fw"
                                                   data-loading-class-save="btn-link fa-key"
                                                   data-loading-text="false"></i>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </fieldset>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php $this->view('modals/pkg-auth'); ?>
<?php $this->endSection('content'); ?>
