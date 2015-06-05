<div class="container router default-off" id="route-queries">
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2">
            <h1>Your Queries</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2">
            <table class="table table-striped">
                <caption>
                    Your Queries <span><a href="#query-editor" data-query-id="">New Query</a></span>
                </caption>
                <thead>
                    <tr>
                        <th>Search Text</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal" id="query-editor">
    <div class="modal-dialog">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit a search query</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label" for="queries-search">Search Text</label>
                        <input type="text" id="queries-search" class="form-control" value="">
                    </div>
                    
                    <input type="hidden" id="queries-id" value="">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>