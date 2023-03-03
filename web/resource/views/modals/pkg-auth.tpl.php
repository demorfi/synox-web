<div class="modal fade" id="pkg-auth" tabindex="-1" role="dialog" aria-labelledby="pkg-auth-label"
     aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <form method="POST" action="/packages/settings">
                <div class="modal-body">
                    <input type="hidden" name="pkg" />
                    <div class="form-group">
                        <label for="username" class="form-control-label sr-only">Username:</label>
                        <input type="text" id="username" name="data[username]" class="form-control"
                               placeholder="Username" autocomplete="off" required />
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-control-label sr-only">Password:</label>
                        <input type="password" id="password" name="data[password]" class="form-control"
                               placeholder="Password" autocomplete="off" required />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-save" data-loading-text="Wait...">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>