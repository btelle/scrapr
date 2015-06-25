var scrapr = {
    api_key: null,
    messages: [],
    
    start: function() {
        if(!window.localStorage) {
            scrapr.show_error('scrapr requires a modern browser with localStorage support. bye!');
            return false;
        }
        
        $(window).bind('hashchange', function(e) {
            scrapr.route();
        });
        
        $('form[role="login"]').submit(function() {
            scrapr.submit_login();
            return false;
        });
        
        $.ajax({
            url: 'ajax.php?mode=num_users',
            success: function(data) {
                if(data.num_users <= 1) {
                    location.hash = '#first-run';
                }
            }
        });
        
        scrapr.confirm_api_key();
    }, 
    
    logged_in: function() {
        $('#nav-settings').show();
    },
    
    logged_out: function() {
        $('#nav-login').show();
        scrapr.show_view('route-default');
    },
    
    route: function(hash) {
        if(typeof(hash) == 'undefined') {
            hash = location.hash;
        }
        
        console.log('Routing to '+hash);
        
        $('div.col-lg-12.messages').html('');
        
        for(mes in scrapr.messages) {
            $('div.col-lg-12.messages').append('<div class="alert alert-dismissible alert-'+scrapr.messages[mes].type+'"><button type="button" class="close" data-dismiss="alert">&times;</button>'+scrapr.messages[mes].message+'</div>');
        }
        
        scrapr.messages = [];
        
        if(hash == '') {
            return;
        }
        
        $('ul.navbar-nav li').each(function() {
            $(this).removeClass('active');
        });
        
        if(hash === '#logout') {
            scrapr.api_key = null;
            window.localStorage.removeItem('api_key');
            
            location.hash = '';
            location.reload();
        } else if(hash === '#follow') {
            $('a[href="#follow"]').parent('li').addClass('active');
            $('#route-photos div.filler').html('');
            $.ajax({
                url: 'ajax.php', 
                data: {api_key: scrapr.api_key, mode: 'follow_photos'},
                success: function(data) {
                    for(i in data.photos) {
                        scrapr.show_photo_row(data.photos[i], 'follow');
                    }
                    
                    $('div.filler').append('<div class="row load-more"><div class="col-lg-8 text-center"><button class="btn btn-success">Load More</button></div></div>');
                    
                    $('.load-more button').click(function() {
                        scrapr.load_more_photos('follow');
                    });
                    
                    $('a[href="#save"]').click(function() {
                        return scrapr.save_image($(this).parents('div.row').attr('data-photo-id'));
                    });
                                        
                    scrapr.show_view('route-photos');
                }
            });
        } else if(hash === '#search') {
            $('a[href="#search"]').parent('li').addClass('active');
            $('#route-photos div.filler').html('');
            $.ajax({
                url: 'ajax.php', 
                data: {api_key: scrapr.api_key, mode: 'search_photos'},
                success: function(data) {
                    for(i in data.photos) {
                        scrapr.show_photo_row(data.photos[i], 'search');
                    }
                    
                    $('div.filler').append('<div class="row load-more"><div class="col-lg-8 text-center"><button class="btn btn-success">Load More</button></div></div>');
                    
                    $('.load-more button').click(function() {
                        scrapr.load_more_photos('search');
                    });
                    
                    $('a[href="#delete-by-profile"]').click(function() {
                        scrapr.delete_by_profile($(this).attr('data-profile-snid'), '#search');
                        return false;
                    });
                    
                    $('a[href="#ignore-profile"]').click(function() {
                        scrapr.ignore_profile($(this).attr('data-profile-snid'), '#search');
                        return false;
                    });
                    
                    $('a[href="#save"]').click(function() {
                        return scrapr.save_image($(this).parents('div.row').attr('data-photo-id'));
                    });
                    
                    scrapr.show_view('route-photos');
                }
            });
        } else if(hash === '#saved') {
            $('a[href="#saved"]').parent('li').addClass('active');
            $('#route-photos div.filler').html('');
            $.ajax({
                url: 'ajax.php', 
                data: {api_key: scrapr.api_key, mode: 'saved_photos'},
                success: function(data) {
                    for(i in data.photos) {
                        scrapr.show_photo_row(data.photos[i], 'saved');
                    }
                    
                    $('div.filler').append('<div class="row load-more"><div class="col-lg-8 text-center"><button class="btn btn-success">Load More</button></div></div>');
                    
                    $('.load-more button').click(function() {
                        scrapr.load_more_photos('follow');
                    });
                    
                    $('a[href="#download"]').click(function() {
                        return scrapr.download_image($(this).parents('div.row').attr('data-photo-id'));
                    }); 
                    
                    $('a[href="#delete"]').click(function() {
                        return scrapr.delete_image($(this).parents('div.row').attr('data-photo-id'));
                    }); 
                    
                    scrapr.show_view('route-photos');
                }
            });
        } else if(hash === '#follows') {
            $('ul.nav a.dropdown-toggle').parent('li').addClass('active');
            $('#follows-editor form').unbind('submit');
            
            $.ajax({
                url: 'ajax.php?mode=all_profiles&api_key='+scrapr.api_key,
                success: function(data) {
                    $('#route-follows table tbody').html('');
                    
                    for(i in data.profiles) {
                        str = '<tr><td>'+data.profiles[i].snid+'</td><td>'+data.profiles[i].name+'</td><td><a href="#follows-editor" data-follow-id="'+data.profiles[i].id+'">Edit</a> | <a href="#follows-delete" data-follow-id="'+data.profiles[i].id+'">Delete</a></td></tr>';
                        
                        $('#route-follows table tbody').append(str);
                    }
                    
                    $('a[href="#follows-editor"]').unbind('click').click(function() {
                        if($(this).attr('data-follow-id')) {
                            $.ajax({
                                url: 'ajax.php',
                                data: {mode: 'profile', api_key: scrapr.api_key, id: $(this).attr('data-follow-id')},
                                type: 'get',
                                success: function(data) {
                                    $('#follows-snid').val(data.profile.snid);
                                    $('#follows-name').val(data.profile.name);
                                    $('#follows-id').val(data.profile.id);
                                    
                                    $('#follows-editor').modal('show');
                                },
                                error: function() {
                                    $('#follows-editor').modal('show');
                                }
                            });
                        } else {
                            $('#follows-editor').modal('show');
                        }
                        return false;
                    });
                    
                    $('a[href="#follows-delete"]').unbind('click').click(function() {
                        $.ajax({
                            url: 'ajax.php?mode=delete_profile',
                            data: {api_key: scrapr.api_key, id: $(this).attr('data-follow-id')},
                            type: 'post',
                            success: function(data) {
                                scrapr.message('Deleted follow', 'success');
                                scrapr.route('#follows');
                            }
                        });
                        return false;
                    });
                    
                    $('#follows-editor form').submit(function() {
                        data = {
                            api_key: scrapr.api_key, 
                            id: $('#follows-id').val(),
                            snid: $('#follows-snid').val(),
                            name: $('#follows-name').val()
                        };
                        
                        $.ajax({
                            url: 'ajax.php?mode=profile',
                            data: data, 
                            type: 'post',
                            success: function(data) {
                                scrapr.message('Saved follow', 'success');
                                $('#follows-editor form input').val('');
                                $('#follows-editor').modal('hide');
                                scrapr.route('#follows');
                            }
                        });
                    });
                    
                    scrapr.show_view('route-follows');
                }
            });
        } else if(hash === '#queries') {
            $('ul.nav a.dropdown-toggle').parent('li').addClass('active');
            $('#query-editor form').unbind('submit');
            
            $.ajax({
                url: 'ajax.php',
                data: {api_key: scrapr.api_key, mode: 'all_queries'},
                success: function(data) {
                    $('#route-queries table tbody').html('');
                    
                    for(i in data.queries) {
                        str = '<tr><td>'+data.queries[i].search+'</td><td><a href="#query-editor" data-query-id="'+data.queries[i].id+'">Edit</a> | <a href="#query-delete" data-query-id="'+data.queries[i].id+'">Delete</a></td></tr>';
                        
                        $('#route-queries table tbody').append(str);
                    }
                    
                    $('a[href="#query-editor"]').unbind('click').click(function() {
                        if($(this).attr('data-query-id')) {
                            $.ajax({
                                url: 'ajax.php',
                                data: {mode: 'query', api_key: scrapr.api_key, id: $(this).attr('data-query-id')},
                                type: 'get',
                                success: function(data) {
                                    $('#queries-search').val(data.query.search);
                                    $('#queries-id').val(data.query.id);
                                    
                                    $('#query-editor').modal('show');
                                },
                                error: function() {
                                    $('#query-editor').modal('show');
                                }
                            });
                        } else {
                            $('#query-editor').modal('show');
                        }
                        return false;
                    });
                    
                    $('a[href="#query-delete"]').unbind('click').click(function() {
                        $.ajax({
                            url: 'ajax.php?mode=delete_query',
                            data: {api_key: scrapr.api_key, id: $(this).attr('data-query-id')},
                            type: 'post',
                            success: function(data) {
                                scrapr.message('Deleted query', 'success');
                                scrapr.route('#queries');
                            }
                        });
                        return false;
                    });
                    
                    $('#query-editor form').submit(function() {
                        data = {
                            api_key: scrapr.api_key, 
                            id: $('#queries-id').val(),
                            search: $('#queries-search').val()
                        };
                        
                        $.ajax({
                            url: 'ajax.php?mode=query',
                            data: data, 
                            type: 'post',
                            success: function(data) {
                                scrapr.message('Saved query', 'success');
                                $('#query-editor form input').val('');
                                $('#query-editor').modal('hide');
                                scrapr.route('#queries');
                            }
                        });
                    });
                    scrapr.show_view('route-queries');
                }
            });
        } else if(hash === '#filters') {
            $('ul.nav a.dropdown-toggle').parent('li').addClass('active');
            $('#filter-editor form').unbind('submit');
            
            $.ajax({
                url: 'ajax.php',
                data: {api_key: scrapr.api_key, mode: 'all_filters'},
                success: function(data) {
                    $('#route-filters table tbody').html('');
                    
                    for(i in data.filters) {
                        str = '<tr><td>'+data.filters[i].field+' '+data.filters[i].operator+' '+data.filters[i].value+'</td><td><a href="#filter-editor" data-filter-id="'+data.filters[i].id+'">Edit</a> | <a href="#filter-delete" data-filter-id="'+data.filters[i].id+'">Delete</a></td></tr>';
                        
                        $('#route-filters table tbody').append(str);
                    }
                    
                    $('a[href="#filter-editor"]').unbind('click').click(function() {
                        if($(this).attr('data-filter-id')) {
                            $.ajax({
                                url: 'ajax.php',
                                data: {mode: 'filter', api_key: scrapr.api_key, id: $(this).attr('data-filter-id')},
                                type: 'get',
                                success: function(data) {
                                    for(i in data.operators) {
                                        $('#filters-operator').append('<option value="'+data.operators[i]+'">'+data.operators[i]+'</option>');
                                    }
                                    
                                    for(i in data.actions) {
                                        $('#filters-action').append('<option value="'+data.actions[i]+'">'+data.actions[i]+'</option>');
                                    }
                                    
                                    $('#filters-field').val(data.filter.field);
                                    $('#filters-operator').val(data.filter.operator);
                                    $('#filters-value').val(data.filter.value);
                                    $('#filters-action').val(data.filter.action);
                                    $('#filters-priority').val(data.filter.priority);
                                    $('#filters-id').val(data.filter.id);
                                    
                                    $('#filter-editor').modal('show');
                                },
                                error: function() {
                                    $('#filter-editor').modal('show');
                                }
                            });
                        } else {
                            $('#filter-editor').modal('show');
                        }
                        return false;
                    });
                    
                    $('a[href="#filter-delete"]').unbind('click').click(function() {
                        $.ajax({
                            url: 'ajax.php?mode=delete_filter',
                            data: {api_key: scrapr.api_key, id: $(this).attr('data-filter-id')},
                            type: 'post',
                            success: function(data) {
                                scrapr.message('Deleted filter', 'success');
                                scrapr.route('#filters');
                            }
                        });
                        return false;
                    });
                    
                    $('#filter-editor form').submit(function() {
                        data = {
                            api_key: scrapr.api_key, 
                            id: $('#filters-id').val(),
                            field: $('#filters-field').val(),
                            operator: $('#filters-operator').val(),
                            value: $('#filters-value').val(),
                            action: $('#filters-action').val(),
                            priority: $('#filters-priority').val()
                        };
                        
                        $.ajax({
                            url: 'ajax.php?mode=filter',
                            data: data, 
                            type: 'post',
                            success: function(data) {
                                scrapr.message('Saved filter', 'success');
                                $('#filter-editor form input').val('');
                                $('#filter-editor').modal('hide');
                                scrapr.route('#filters');
                            }
                        });
                    });
                    scrapr.show_view('route-filters');
                }
            });
        } else if(hash === '#settings') {
            $('ul.nav a.dropdown-toggle').parent('li').addClass('active');
            
            $.ajax({
                url: 'ajax.php', 
                data: {api_key: scrapr.api_key, mode: 'settings'},
                success: function(data) {
                    for(i in data.settings) {
                        console.log(data.settings[i]['key']+': '+data.settings[i]['value']);
                        $('#setting-'+data.settings[i]['key']).val(data.settings[i]['value']);
                    }
                    
                    $('#route-settings form').submit(function() {
                        data = {api_key: scrapr.api_key, mode: 'settings'};
                        $('#route-settings form input').each(function(){
                            data[$(this).attr('id').replace('setting-', '')] = $(this).val();
                        });
                        console.log(data);
                        $.ajax({
                            url: 'ajax.php',
                            data: data,
                            type: 'post',
                            success: function(data) {
                                console.log(data);
                                scrapr.message('Updated settings', 'success');
                                scrapr.route('#settings');
                            }
                        });
                    });
                    scrapr.show_view('route-settings');
                }
            });
        } else if(hash == '#logs') {
            $('ul.nav a.dropdown-toggle').parent('li').addClass('active');
            $('#route-logs table tbody').html('');
            $.ajax({
                url: 'ajax.php',
                data: {
                    api_key: scrapr.api_key, 
                    mode: 'logs', 
                    timestamp: $('#logs-timestamp').val(),
                    message: $('#logs-message').val(),
                    log_level: $('#logs-log-level').val()
                },
                success: function(data) {
                    for(i in data.logs) {
                        scrapr.add_log_table_row(data.logs[i]);
                    }
                    
                    if(data.logs.length > 0) {
                        $('#route-logs table tbody').append('<tr><td colspan="3"><button class="btn btn-success load-more">Load More</button></td></tr>');
                    }
                    
                    $('button.load-more').unbind('click').click(function() {
                        scrapr.logs_load_more();
                    });
                    
                    $('#route-logs form').unbind('submit').submit(function() {
                        scrapr.route();
                        return false;
                    });
                    
                    scrapr.show_view('route-logs');
                }
            });
        } else if(hash === '#first-run') {
            $('#first-run').modal('show');
            
            $('#first-run form').submit(function() {
                if($('#first-password').val() != $('#first-confirm').val()) {
                    scrapr.show_error('Mis-matching passwords');
                } else {
                    data = {
                        username: $('#first-username').val(),
                        password: $('#first-password').val(),
                        mode: 'first_run'
                    };
                    
                    $.ajax({
                        url: 'ajax.php',
                        data: data,
                        type: 'post',
                        success: function() {
                            scrapr.messages.push({message:'User created successfully, please log in', type:'success'});
                            location.hash = '';
                            
                            $('#first-run').modal('hide');
                            $('#username').focus();
                        },
                        error: function(data) {
                            scrapr.show_error(data.error.message);
                        }
                    })
                }
            })
        }
    },
    
    show_view: function(id) {
        $('div.router').each(function() {
            $(this).hide();
        });
        
        $('#'+id).show();
    },
    
    message: function(mes, type, insert) {
        scrapr.messages.push({message: mes, type: type});
        
        if(type == 'danger') {
            level = 'error';
        } else if(type == 'warning') {
            level = 'warning';
        } else {
            level = 'info';
        }
        
        $.ajax({
            url: 'ajax.php', 
            data: {mode: 'logs', api_key: scrapr.api_key, log_level: level, message: mes},
            type: 'post',
            success: function(data) {
                if(data.message != 'OK' && data.error) {
                    scrapr.show_error(data.error.message);
                }
            }
        });
    },
    
    add_log_table_row: function(row) {
        $('#route-logs table tbody').append('<tr data-log-id="'+row.id+'" class="log-'+row.log_level+'"><td>'+row.timestamp+'</td><td>'+row.log_level+'</td><td>'+row.message+'</td></tr>');
    },
    
    logs_load_more: function() {
        $('#route-logs table tbody tr:last-child').remove();
        
        $.ajax({
            url: 'ajax.php',
            data: {
                api_key: scrapr.api_key, 
                mode: 'logs', 
                timestamp: $('#logs-timestamp').val(),
                message: $('#logs-message').val(),
                log_level: $('#logs-log-level').val(),
                last_id: $('#route-logs table tbody tr:last-child').attr('data-log-id')
            },
            success: function(data) {
                for(i in data.logs) {
                    scrapr.add_log_table_row(data.logs[i]);
                }
                
                if(data.logs.length > 0) {
                    $('#route-logs table tbody').append('<tr><td colspan="3"><button class="btn btn-success load-more">Load More</button></td></tr>');
                }
                    
                $('button.load-more').unbind('click').click(function() {
                    scrapr.logs_load_more();
                });
            }
        });
    },
    
    show_photo_row: function(photo, photo_type) {
        photo_url = photo.original;
        if(photo_url == '') {
            photo_url = photo.large;
        }
        
        if(photo_type == 'follow') {
            controls = '<li><a href="https://www.flickr.com/photos/'+photo.owner+'" target="_blank">View '+photo.name+' on Flickr</a> <sup><i class="glyphicon glyphicon-share"></i></sup></li><li><a href="#save">Save for later</a></li>';
        } else if(photo_type == 'search') {
            controls = '<li><a href="https://www.flickr.com/photos/'+photo.owner+'" target="_blank">View this profile</a></li><li><a href="#save">Save for later</a></li>';
        } else if(photo_type == 'saved') {
            controls = '<li><a href="#download" download>Download</a></li><li><a href="#delete">Delete</a></li>';
        }
        $('#route-photos div.filler').append('<div class="row" data-photo-id="'+photo.id+'"><div class="col-lg-8"><a href="'+photo_url+'" target="_blank"><img src="'+photo.large+'" alt="Photo '+photo.id+'" class="img-responsive"></a></div><div class="col-lg-4"><ul>'+controls+'</ul></div></div>');
    },
    
    load_more_photos: function(type) {
        $('div.filler div.load-more').remove();
        
        $.ajax({
            url: 'ajax.php', 
            data: {
                api_key: scrapr.api_key, 
                mode: type+'_photos', 
                start_id: $('div.filler div.row:first-child').attr('data-photo-id'), 
                last_id: $('div.filler div.row:last-child').attr('data-photo-id')
            },
            success: function(data) {
                for(i in data.photos) {
                    scrapr.show_photo_row(data.photos[i], type);
                }
                
                $('div.filler').append('<div class="row load-more"><div class="col-lg-8 text-center"><button class="btn btn-success">Load More</button></div></div>');
                
                $('div.filler button').unbind('click').click(function() {
                    scrapr.load_more_photos(type);
                });
                
                $('a[href="#save"]').unbind('click').click(function() {
                    return scrapr.save_image($(this).parents('div.row').attr('data-photo-id'));
                });
                
                $('a[href="#download"]').unbind('click').click(function() {
                    return scrapr.download_image($(this).parents('div.row').attr('data-photo-id'));
                }); 

                $('a[href="#delete"]').unbind('click').click(function() {
                    return scrapr.delete_image($(this).parents('div.row').attr('data-photo-id'));
                }); 
            }
        });
    },
    
    delete_by_profile: function(snid, route) {
        $.ajax({
            url: 'ajax.php?mode=delete_by_profile', 
            data: {snid: snid, api_key: scrapr.api_key},
            type: 'post', 
            success: function(data) {
                if(typeof(route) != 'undefined') {
                    scrapr.route(route);
                }
            }
        });
    },
    
    ignore_profile: function(snid, route) {
        $.ajax({
            url: 'ajax.php?mode=ignore_profile', 
            data: {snid: snid, api_key: scrapr.api_key},
            type: 'post', 
            success: function(data) {
                if(typeof(route) != 'undefined') {
                    scrapr.route(route);
                }
            }
        });
    },
    
    save_image: function(photo_id) {
        console.log('Saving image #'+photo_id);
        $.ajax({
            url: 'ajax.php?mode=save',
            data: {id: photo_id, api_key: scrapr.api_key},
            type: 'post',
            success: function(data) {
                $('div.row[data-photo-id="'+photo_id+'"] a[href="#save"]').parent('li').append(' <i class="glyphicon glyphicon-ok text-success"></i>');
            }
        });
        return false;
    },
    
    download_image: function(photo_id) {
        $.ajax({
            url: 'ajax.php',
            data: {mode: 'dl', id: photo_id, api_key: scrapr.api_key},
            success: function(data) {
                location.assign(data.url);
            }
        });
        return false;
    },
    
    delete_image: function(photo_id) {
        console.log('Deleting image #'+photo_id);
        $.ajax({
            url: 'ajax.php?mode=delete',
            data: {id: photo_id, api_key: scrapr.api_key},
            type: 'post',
            success: function(data) {
                $('div.row[data-photo-id="'+photo_id+'"] a[href="#delete"]').append(' <i class="glyphicon glyphicon-ok"></i>');
            }
        });
        return false;
    },
    
    confirm_api_key: function() {
        if(!window.localStorage['api_key']) {
            scrapr.logged_out();
            return false;
        }
        
        $.ajax({
            url: 'ajax.php',
            data: {api_key: window.localStorage['api_key'], mode: 'confirm_key'},
            success: function(data) {
                if(data.message == 'OK') {
                    scrapr.logged_in();
                    scrapr.api_key = window.localStorage['api_key'];
                    
                    if(data.new_follow_count > 0) {
                        $('ul.nav a[href="#follow"] span').html(data.new_follow_count);
                    }
                    
                    if(data.new_search_count > 0) {
                        $('ul.nav a[href="#search"] span').html(data.new_search_count);
                    }
                    
                    if(data.new_saved_count > 0) {
                        $('ul.nav a[href="#saved"] span').html(data.new_saved_count);
                    }

                    if(location.hash == '') {
                        location.hash = '#follow';
                    } else {
                        scrapr.route();
                    }
                } else {
                    scrapr.logged_out();
                }
            }, 
            error: function() {
                scrapr.logged_out();
            }
        });
    },
    
    submit_login: function() {
        $('form[role="login"] div').each(function() {
            $(this).removeClass('has-error');
        });
        
        $.ajax({
            url: 'ajax.php?mode=login',
            data: {username: $('#username').val(), password: $('#password').val()},
            type: 'post',
            success: function(data) {
                if(data.api_key) {
                    window.localStorage['api_key'] = data.api_key;
					scrapr.api_key = data.api_key;
                    
                    if(data.new_follow_count > 0) {
                        $('ul.nav a[href="#follow"] span').html(data.new_follow_count);
                    }
                    
                    if(data.new_search_count > 0) {
                        $('ul.nav a[href="#search"] span').html(data.new_search_count);
                    }
                    
                    if(data.new_saved_count > 0) {
                        $('ul.nav a[href="#saved"] span').html(data.new_saved_count);
                    }
                    
                    $('#nav-login').hide();
                    scrapr.logged_in();
                    
                    if(location.hash == '') {
                        location.hash = '#follow';
                    } else {
                        scrapr.route();
                    }
                    
                } else {
                    scrapr.show_error(data.error.message);
                }
            },
            error: function(data) {
                data = data.responseJSON;
                if(data.error) {
                    if(data.error.field == 'username') {
                        $('#username').parent('div').addClass('has-error');
                    } else if(data.error.field == 'password') {
                        $('#password').parent('div').addClass('has-error');
                    }
                    
                    scrapr.show_error(data.error.message);
                }
            }
        });
    },
    
    show_error: function(msg) {
        alert(msg);
    }
};

$(document).ready(function() {
    scrapr.start();
});