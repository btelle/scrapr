<div class="container router default-off" id="route-filters">
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2">
            <h1>Your Filters</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2">
            <table class="table table-striped">
                <caption>
                    Your filters <span><a href="#filter-editor" data-filter-id="">New Filter</a></span>
                </caption>
                <thead>
                    <tr>
                        <th>Filter</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal" id="filter-editor">
    <div class="modal-dialog">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit a filter</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label" for="filters-field">Field</label>
                        <input type="text" id="filters-field" class="form-control" placeholder="field" />
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label" for="filters-operator">Operator</label>
                        <select id="filters-operator" class="form-control"></select>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label" for="filters-value">Value</label>
                        <input type="text" id="filters-value" class="form-control" placeholder="value" />
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label" for="filters-action">Filter</label>
                        <select id="filters-action" class="form-control"></select>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label" for="filters-priority">Priority (Ascending)</label>
                        <input type="text" id="filters-priority" class="form-control" placeholder="0-100" />
                    </div>
                    
                    <input type="hidden" id="filters-id" value="" />
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>