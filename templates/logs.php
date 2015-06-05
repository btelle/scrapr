<div class="container router default-off" id="route-logs">
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2">
            <h1>System Logs</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2">
            <div class="panel panel-default" id="logs-filter">
                <div class="panel-heading" id="logs-filter-heading"><a data-toggle="collapse" data-parent="#logs-filter" href="#logs-filter-form" aria-expanded="true" aria-controls="logs-filter-form">Filter Logs</a></div>
                <div id="logs-filter-form" class="panel-collapse collapse" role="tabpanel" aria-labelledby="logs-filter-heading">
                    <div class="panel-body">
                        <form class="form-horizontal">
                            <div class="form-group">
                                <label for="logs-timestamp" class="col-lg-2 control-label">Timestamp</label>
                                <div class="col-lg-10">
                                    <input class="form-control" id="logs-timestamp" placeholder="Timestamp" type="text">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="logs-message" class="col-lg-2 control-label">Message</label>
                                <div class="col-lg-10">
                                    <input class="form-control" id="logs-message" placeholder="Message" type="text">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="logs-log-level" class="col-lg-2 control-label">Log Level</label>
                                <div class="col-lg-10">
                                    <select class="form-control" id="logs-log-level">
                                        <option></option>
                                        <option value="info">Info</option>
                                        <option value="warning">Warning</option>
                                        <option value="error">Error</option>
                                    </select>
                                </div>
                            </div>
                            <div class="text-right">
                                <button class="btn btn-primary" type="submit">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Log Level</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                
                </tbody>
            </table>
        </div>
    </div>
</div>