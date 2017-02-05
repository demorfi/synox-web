<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>
    <div class="card card-block radius-0 p-0">
        <div class="card-title mb-0">
            <form method="POST" action="/lyrics/search" role="form"
                  class="form-inline float-right p-3" data-url-results="/lyrics/results">
                <div class="form-group input-group">
                    <span class="input-group-addon">Name</span>
                    <input id="name" name="name" class="form-control" placeholder="ex. Rammstein - Ohne Dich" required>
                    <span class="input-group-btn">
                        <button type="submit" name="search" value="1" class="btn btn-info">Search</button>
                        <button type="button" name="break" class="btn btn-danger" hidden>Break</button>
                    </span>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table id="lyric-items" class="table table-hover mb-0">
                <thead class="thead-inverse">
                <tr>
                    <th width="65%" class="text-left">Title</th>
                    <th width="35%" class="text-left">Artist</th>
                </tr>
                </thead>
                <tbody>
                <tr hidden>
                    <td class="text-left relative hover-show-btn">
                        <div><a href="#" data-element="page" target="_blank"><span data-element="title"></span></a>
                        </div>
                        <button type="button" class="btn btn-secondary"
                                data-element="fetch" data-url="/lyrics/fetch"
                                data-loading-text="wait...">Fetch
                        </button>
                        <button type="button" class="btn btn-secondary"
                                data-element="show" hidden>Show
                        </button>
                        <input type="hidden" data-element="id" />
                    </td>
                    <td class="text-left" data-element="artist"></td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <td>
                        <div data-toggle="loading"
                             data-load="<i class='fa fa-circle-o-notch fa-spin fa-fw'></i>"
                             data-reset="Perform a new search"
                             data-empty="The search query is not successfull">Perform search
                        </div>
                    </td>
                    <th class="text-right">
                        Total: <span data-element="total">0</span>
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php $this->view('modals/pkg-lyric'); ?>
<?php $this->endSection('content'); ?>
