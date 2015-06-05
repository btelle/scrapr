<div class="container router default-off" id="route-follows">
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2">
            <h1>Your Follows</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2">
            <table class="table table-striped">
                <caption>
                    Your Follows <span><a href="#follows-editor" data-follow-id="">New Follow</a></span>
                </caption>
                <thead>
                    <tr>
                        <th>Flickr ID</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal" id="follows-editor">
    <div class="modal-dialog">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit a follow</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label" for="follows-snid">Flickr ID</label>
                        <input type="text" id="follows-snid" class="form-control" value="" placeholder="######@###">
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label" for="follows-name">Name</label>
                        <input type="text" id="follows-name" class="form-control" value="" placeholder="Your name for this profile">
                    </div>
                    <input type="hidden" id="follows-id" value="">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>