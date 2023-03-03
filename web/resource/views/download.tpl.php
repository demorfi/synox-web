<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>
<div class="card card-block radius-0 p-0">
    <div class="card-title mb-0">
        <form method="POST" action="/download/search" role="form" class="pt-3 pl-3 pr-3"
              data-url-results="/download/results">
            <div class="form-row justify-content-end">
                <div class="col-sm-7 pb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="query">Filter</label>
                        </div>
                        <label class="sr-only" for="package">Package</label>
                        <select id="package" name="package" class="custom-select">
                            <option value="" selected>Any Packages</option>
                            <?php foreach ($this->packages as $package) { ?>
                                <option value="<?php echo $package->getId(); ?>">
                                    <?php echo $package->getName(); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <?php foreach ($this->filters as $filter) { ?>
                            <label class="sr-only" for="filter-<?php echo $filter::getId(); ?>">
                                <?php echo $filter::getTypeName(); ?>
                            </label>
                            <select id="filter-<?php echo $filter::getId(); ?>"
                                    name="filters[<?php echo $filter::getId(); ?>]"
                                    class="custom-select">
                                <option selected>Any <?php echo $filter::getTypeName(); ?></option>
                                <?php foreach ($filter::cases() as $category) { ?>
                                    <option value="<?php echo $category->name; ?>"><?php echo $category->value; ?></option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-sm-5 pb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="query">Query</label>
                        </div>
                        <input id="query" name="query" class="form-control" placeholder="ex. Silent Hill 2" required
                               size="3"/>
                        <div class="input-group-append">
                            <button type="submit" name="search" value="1" class="btn btn-info">Search</button>
                            <button type="button" name="break" class="btn btn-danger" hidden>Break</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table id="download-items" class="table table-hover mb-0">
            <thead class="thead-dark">
            <tr>
                <th width="60%" class="text-left">Title</th>
                <th class="text-right">Size</th>
                <th class="text-center">Date</th>
                <th class="text-right">Seeds</th>
                <th class="text-right">Peers</th>
            </tr>
            </thead>
            <tbody>
            <tr hidden>
                <td class="text-left relative hover-show-btn">
                    <div>
                        <a href="//" data-element="pageUrl" target="_blank"><span data-element="title"></span></a>
                    </div>
                    <div class="blockquote-footer">
                        <span data-element="category"></span> in <cite data-element="package"></cite>
                    </div>
                    <button type="button" class="btn btn-secondary"
                            data-element="fetchUrl" data-url="/download/fetch"
                            data-loading-text="wait...">Fetch
                    </button>
                    <button type="button" class="btn btn-secondary"
                            data-element="download" data-url="/download/download" hidden>Download
                    </button>
                    <input type="hidden" data-element="id"/>
                    <div class="last-action" hidden></div>
                </td>
                <td class="text-right" data-element="weight"></td>
                <td class="text-center" data-element="date"></td>
                <td class="text-right" data-element="seeds"></td>
                <td class="text-right" data-element="peers"></td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="3">
                    <div data-toggle="loading"
                         data-load="<i class='fa fa-circle-o-notch fa-spin fa-fw'></i>"
                         data-reset="Perform new search"
                         data-empty="The search query is not successful">Perform search
                    </div>
                </td>
                <th colspan="3" class="text-right">
                    Total: <span data-element="total">0</span>
                </th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php $this->endSection('content'); ?>
