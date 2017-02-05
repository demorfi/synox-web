<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>
    <div class="card card-block radius-0 p-0">
        <div class="card-title mb-0">
            <form method="POST" action="/downloads/search" role="form"
                  class="form-inline float-right p-3" data-url-results="/downloads/results">
                <div class="form-group input-group">
                    <span class="input-group-addon">Name</span>
                    <input id="name" name="name" class="form-control" placeholder="ex. Silent Hill 2" required />
                    <span class="input-group-btn">
                        <button type="submit" name="search" value="1" class="btn btn-info">Search</button>
                        <button type="button" name="break" class="btn btn-danger" hidden>Break</button>
                    </span>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table id="download-items" class="table table-hover mb-0">
                <thead class="thead-inverse">
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
                        <div><a href="#" data-element="page" target="_blank"><span data-element="title"></span></a>
                        </div>
                        <div class="blockquote-footer">
                            <span data-element="category"></span> in <cite data-element="package"></cite>
                        </div>
                        <button type="button" class="btn btn-secondary"
                                data-element="fetch" data-url="/downloads/fetch"
                                data-loading-text="wait...">Fetch
                        </button>
                        <button type="button" class="btn btn-secondary"
                                data-element="download" data-url="/downloads/download" hidden>Download
                        </button>
                        <input type="hidden" data-element="id" />
                        <div class="last-action" hidden></div>
                    </td>
                    <td class="text-right" data-element="size"></td>
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
                             data-reset="Perform a new search"
                             data-empty="The search query is not successfull">Perform search
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
