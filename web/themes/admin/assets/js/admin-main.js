var AdminMain = window.AdminMain || {};

(function($){
    AdminMain.Func = {
        init: function () {
            AdminMain.Func.admin_submit_filter_records();
            AdminMain.Func.admin_submit_limit_records();
            AdminMain.Func.admin_search_keyword_form();
            AdminMain.Func.admin_order_field_data();
            AdminMain.Func.delete_item_lists_thumb_gallery();
            AdminMain.Func.show_left_menu_active();

            setTimeout(function(){
                $(".alert-success").slideUp();
            },5000);

            //Call popup errors message
            if($("#ErrorModal").data('error') == 1){
                $('#ErrorModal').modal('show');
            }
            //to translate the daterange picker, please copy the "examples/daterange-fr.js" contents here before initialization

            $('.date-picker').daterangepicker({
                'applyClass' : 'btn-sm btn-success',
                'cancelClass' : 'btn-sm btn-default',
                locale: {
                    applyLabel: 'Apply',
                    cancelLabel: 'Cancel',
                }
            }, function(start, end, label){
                /*var start_date = Date.parse(start) / 1000;
                var end_date = Date.parse(end) / 1000;
                if(start_date > 0 && end_date > 0){
                    var value = start_date + '-' + end_date;
                    var redirect_url = AdminMain.Func.generate_url_hande_filter('date_range', value);
                    window.location.href = redirect_url;
                }*/

            });

            var tag_input = $('#form_tags');
            try{
                tag_input.tag(
                    {
                        placeholder:tag_input.attr('placeholder'),
                        //enable typeahead by specifying the source array
                        //source: ace.vars['US_STATES'],//defined in ace.js >> ace.enable_search_ahead
                        /**
                         //or fetch data from database, fetch those that match "query"
                         source: function(query, process) {
						  $.ajax({url: 'remote_source.php?q='+encodeURIComponent(query)})
						  .done(function(result_items){
							process(result_items);
						  });
						}
                         */
                    }
                )

                //programmatically add a new
                var $tag_obj = $('#form_tags').data('tag');
                var list_tags = $(".list_tags").val() ? JSON.parse($(".list_tags").val()) : [];
                if(list_tags.length > 0){
                    for(var i in list_tags){
                        $tag_obj.add(list_tags[i].tag_name);
                    }
                }


            }
            catch(e) {
                //display a textarea for old IE, because it doesn't support this plugin or another one I tried!
                tag_input.after('<textarea id="'+tag_input.attr('id')+'" name="'+tag_input.attr('name')+'" rows="10">'+tag_input.val()+'</textarea>').remove();
                //$('#form_tags').autosize({append: "\n"});
            }

        },

        admin_submit_filter_records : function(){
            $(".search-filter").on('click', function(){
                /*var current_url = $("#filter_options").attr('action');
                //var pathname = $(location).attr('href');
                var params = '';
                var value;
                $.each($("#filter_options").serializeArray(), function(key, element){
                    value = element.value;
                    if($("#"+element.name).data('type') == 'date-picker'){
                        var date_range = element.value.split('-');
                        console.log(date_range);
                        var start_date = Date.parse(date_range[0]) / 1000;
                        var end_date = Date.parse(date_range[1]) / 1000;
                        if(start_date > 0 && end_date > 0){
                            value = start_date + '-' + end_date;
                        }
                    }
                    if (key == 0) {
                        //var first_param  = pathname.indexOf('?') > 0 ? '&' : '?';
                        params += '?' + element.name + '=' + value;
                    } else {
                        params += '&' + element.name + '=' + value;
                    }
                });

                var redirect_url = current_url + params;
                window.location.href = redirect_url;*/

                $("#filter_options").submit();
            });
        },

        admin_submit_limit_records : function(){
            /*$("#show_record_num").on('change', function(){
                var limit = $(this).val();
                var redirect_url = AdminMain.Func.generate_url_hande_filter('lm', limit);
                window.location.href = redirect_url;
            });*/
        },

        admin_search_keyword_form : function(){
            /*$("#search-keyword").keydown(function(e){
                var keyCode = e.which;
                if (keyCode == 13) {
                    var key = $(this).val();
                    var redirect_url = AdminMain.Func.generate_url_hande_filter('key', key);
                    window.location.href = redirect_url;
                }
            });*/
        },

        admin_order_field_data : function(){
            $(".admin_order_field").on('click',function(){
                var redirect_url = $(this).data('url');
                window.location.href = redirect_url;
            });
        },

        getURLParameter : function (name, url) {
            if (!url) url = location.href;
            name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
            var regexS = "[\\?&]"+name+"=([^&#]*)";
            var regex = new RegExp( regexS );
            var results = regex.exec( url );
            return results == null ? null : results[1];
        },

        generate_url_hande_filter : function(str_type, value){
            var pathname = $(location).attr('href');
            var parameter_url, redirect_url;
            if(pathname.indexOf(str_type + '=') == -1){
                parameter_url = pathname.indexOf('?') > 0 ? '&' + str_type + '='+value : '?' + str_type + '=' + value;
                redirect_url = pathname + parameter_url;
            } else {
                var param = AdminMain.Func.getURLParameter(str_type, pathname);
                redirect_url = pathname.replace(str_type + '='+param, str_type + '=' + value);
            }

            return redirect_url;
        },

        _plupload_files : function(url_upload_file, container, browse_button)
        {
            var uploader_file = new plupload.Uploader({
                // General settings
                runtimes : 'html5,flash,silverlight,html4',
                url : url_upload_file,

                // User can upload no more then 20 files in one go (sets multiple_queues to false)
                max_file_size : '2mb',

                chunk_size: '3mb',

                container: container,

                browse_button : browse_button,

                filters : {
                    // Specify what files to browse for
                    mime_types: [
                        {title : "Image files", extensions : "jpg,gif,png"},
                        {title : "Zip files", extensions : "zip"}
                    ]
                },

                // Rename files by clicking on their titles
                rename: true,

                // Sort files
                sortable: true,

                // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
                dragdrop: true,

                // Views to activate
                views: {
                    //list: true,
                    thumbs: true, // Show thumbs
                    active: 'thumbs'
                },

                // Flash settings
                flash_swf_url : path_url_assset + 'js/plupload/Moxie.swf',

                // Silverlight settings
                silverlight_xap_url : path_url_assset + 'js/plupload/Moxie.xap',

                /*headers: {
                    "x-csrf-token" : $('meta[name="csrf-token"]').attr('content')
                },*/

                init : {
                    FilesAdded: function(up, files) {
                        up.start();
                    },
                    UploadComplete: function(up, files) {
                        var html ='';
                        $.each(files, function(i, file) {
                            html += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <strong></strong></div>';
                            //$("#filelist").append(html);
                        });
                    },
                    FileUploaded: function(up, files, response) {

                        var data = $.parseJSON(response.response);
                        setTimeout(function() {
                            $('div[id="'+files.id+'"]').remove();
                        }, 1000);

                        var lists_thumb = $(".lists_thumb").val() ? JSON.parse($(".lists_thumb").val()) : [];

                        if(data.status)
                        {
                            var image_file = {
                                'file' : data.file
                            };
                            var html_thumb ='';
                            if(data.file.length > 0){
                                //$.each(data.files, function(i, file) {
                                html_thumb += '<div class="thumb_uploaded">';
                                html_thumb += '<img src="' + data.file_path + '" class="img_uploaded" />';
                                html_thumb += '<i class="icon-delete" data-value="' + data.file + '"></i>';
                                html_thumb += '</div>';
                                $("#"+container).append(html_thumb);
                                lists_thumb.push(image_file);
                                $(".lists_thumb").val(JSON.stringify(lists_thumb));
                                //});
                            }

                        }

                        AdminMain.Func.delete_item_lists_thumb_gallery(lists_thumb);

                    }
                }

            });

            uploader_file.init();
        },

        delete_item_lists_thumb_gallery : function(lists_thumb ){
            $(".thumb_uploaded .icon-delete").click(function(){
                var lists_del_file = $(".lists_del_file").val() ? JSON.parse($(".lists_del_file").val()) : [];
                var value = $(this).data('value');
                if($(this).data('id')) {
                    var id = $(this).data('id');
                    var del_file = {
                        'id' : id
                    };
                    lists_del_file.push(del_file);
                    $(".lists_del_file").val(JSON.stringify(lists_del_file));
                }
                if(!lists_thumb){
                    lists_thumb = JSON.parse($(".lists_thumb").val());
                }

                AdminMain.Func.removeItem(lists_thumb, value);
                $(".lists_thumb").val(JSON.stringify(lists_thumb));
                $(this).parent().remove();
            });
        },

        removeItem : function(array, item){
            for(var i in array){
                if(array[i].file==item){
                    array.splice(i,1);
                    break;
                }
            }
        },

        show_left_menu_active : function(){
            if($("li.leftmenu-child-active").length > 0){
                $("li.leftmenu-child-active").parent().addClass('left-menu-active');
                $("li.leftmenu-child-active").parent().parent().parent().addClass('left-menu-active');
            }
        }

    };
})(jQuery);

$(document).ready(function(){
    AdminMain.Func.init();
});

//view image colorbox
jQuery(function($) {
    var $overflow = '';
    var colorbox_params = {
        rel: 'colorbox',
        reposition:true,
        scalePhotos:true,
        scrolling:false,
        previous:'<i class="ace-icon fa fa-arrow-left"></i>',
        next:'<i class="ace-icon fa fa-arrow-right"></i>',
        close:'&times;',
        current:'{current} of {total}',
        maxWidth:'100%',
        maxHeight:'100%',
        onOpen:function(){
            $overflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
        },
        onClosed:function(){
            document.body.style.overflow = $overflow;
        },
        onComplete:function(){
            $.colorbox.resize();
        }
    };

    $('.ace-thumbnails').colorbox(colorbox_params);
    $("#cboxLoadingGraphic").html("<i class='ace-icon fa fa-spinner orange fa-spin'></i>");//let's add a custom loading icon


    $(document).one('ajaxloadstart.page', function(e) {
        $('#colorbox, #cboxOverlay').remove();
    });
})
