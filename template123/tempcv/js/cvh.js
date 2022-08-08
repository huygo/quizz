$(function () {    
    $(document).on('click', '.fieldgroup_controls .remove', function (e) {        
        var item = $(this).parent().parent();        
        var itemId = item.attr('id');
        var parentRemoveId = '#' + item.parent().attr('id');        
        item.remove();        
        for (var h = 0; h < sortAbleArea.length; h++) {            
            if (sortAbleArea[h].el === parentRemoveId) {                                    
                $.removeItem(sortAbleArea[h].area, itemId);                    

                $.initSortable(sortAbleArea[h], false);
                $.upAndDown(item, sortAbleArea[h].el);

                return false;
            }            
        }
    });

    $(document).on('click', '.fieldgroup_controls .clone', function (e) {
        var item = $(this).parent().parent().clone();
        item.attr('id', $.randomStr);
        item.appendTo($(this).parent().parent().parent());

        for (var t = 0; t < sortAbleArea.length; t++) {            
            if (sortAbleArea[t].el === '#' + item.parent().attr('id')) {
                var area = sortAbleArea[t];
                $.createOrder(area.area, item.attr('id'), $(area.el).children().length);

                $.initSortable(area, false);
                $.upAndDown(item, area.el);

                return false;
            }            
        }
    });

    $(document).on('click', '.blockControls .hide', function (e) {
        var item = $(this).parent().parent();
        var itemId = item.attr('id');
        var parentRemoveId = '#' + item.parent().attr('id');
        item.hide();

        for (var h = 0; h < sortAbleArea.length; h++) {            
            if (sortAbleArea[h].el === parentRemoveId) {
                $.hideBlock(sortAbleArea[h].area, itemId);

                $.initSortable(sortAbleArea[h], false);
                $.upAndDown(item, sortAbleArea[h].el);

                return false;
            }
        }
    });

    $(document).on('click', '.js-edit-content', function (e) {
        $(this).parent().parent().addClass('edit-content');
        $(this).parent().html('<div class="save js-save-content">Save</div>');
    });

    $(document).on('click', '.js-save-content', function (e) {
        var div = $(this).parent().parent();
        var bar = div.removeClass('edit-content').find('.bar-exp');
        var p = div.find('.bar-value-exp input').val();
        if(p>100){p=100;}else if(p<0){p=0;}
        bar.html('<div style="width: ' + p + '%"></div>');
        $(this).parent().html('<div class="clone"><i class="fa fa-plus"></i> Thêm</div>\n' +
            '<div class="edit js-edit-content">Sửa</div>\n' +
            '<div class="remove"><i class="fa fa-minus"></i> Xóa</div>');
    });

    //Declare sortable area and item want to sort here
    var sortAbleArea = [
        {el: '#sortable', item: '.block', area: 'menu'},
        {el: '#sort_block', item: '.cvo-block', area: 'experiences'}
    ];

    //Initial json data
    var data = {
        css: [],
        cv_title: '',
        avatar: '',
        name: '',
        position: '',
        introduction: '',        
        menu: [],
        experiences: []
    };

    //Create order data for first time
    $.createOrder = function (area, id, order) {
        var sub = {id: id, order: order, content: ''};        
        data[area].push(sub);
    };    

    //Remove item from data
    $.removeItem = function (area, id) {
        data[area].forEach( function (arrayItem, index) {
            if (data[area][index].id === id) {
                data[area].splice(index, 1);
            }
        });
    };

    //Hide block from data
    $.hideBlock = function (area, id) {
        data[area].forEach( function (arrayItem, index) {
            if (data[area][index].id === id) {
                //data[area][index].status = 'hide';
                $('#layout-editor').find("[blockkey='" + id + "']").removeClass('active');
            }
        });
    };

    $.showBlock = function (area, id) {
        data[area].forEach( function (arrayItem, index) {
            if (data[area][index].id === id) {
                //data[area][index].status = null;
            }
        });
    };
    //Update order by id
    $.updateOrder = function (area, id, order) {        
        for (var i = 0; i < data[area].length; i++) {
            if (data[area][i].id === id) {
                data[area][i].order = order;

                return false;
            }
        }
    };
   
    $.initSortable = function (sortable, updown) {
        var item = $(sortable.el + ' ' + sortable.item);            
        //Handle sortable
        $(sortable.el).sortable({
            cancel: 'input, [contenteditable]',

            create: function (event, ui) {
              item.each(function (e) {
                $.createOrder(sortable.area, $(this).attr('id'), ($(this).index() + 1));
              });
            },

            update: function (event, ui) {              
              item.each(function (e) {                
                $.updateOrder(sortable.area, $(this).attr('id'), ($(this).index() + 1));
              });

              //console.log(data);
            }
        });

        if (updown) {        
            $.upAndDown(item, sortable.el);
        }       

    };

    $.upAndDown = function (items, sortableEl) {        
        items.each(function () {
            var self = $(this);
            //console.log(self)
            $(this).find('.up').on('click', function () {
              if (!self.is(':first-child')) {
                var prev = self.prev();
                self.insertBefore(prev).hide().fadeIn();
                $(sortableEl).sortable('option', 'update')();
              }
            });

            $(this).find('.down').on('click', function () {
              if (!self.is(':last-child')) {
                var next = self.next();
                self.insertAfter(next).hide().fadeIn();
                $(sortableEl).sortable('option', 'update')();
              }
            })
        });
    };

    //Start create data
    for (var l = 0; l < sortAbleArea.length; l++) {
        $.initSortable(sortAbleArea[l], true);
    }

    //Get content and export to json data
    $.exportData = function() {
        data['css'] = {
            color: $('#toolbar-color .color.active').attr('data-color'),
            font: $('#font-selector').find("option:selected").val(),
            font_size: $('#cvo-toolbar .fontsize.active').attr('data-size'),
            font_spacing: $('#cvo-toolbar .line-height.active').attr('data-spacing')
        }
        var cv_title  = $('#page-cv #cv-title').text(); 
        if(cv_title==''){
            cv_title = $('#cv_alias').val();
        }
        data['cv_title'] = cv_title;    
        data['avatar'] = $('#page-cv #cvo-profile-avatar').attr('src'); 
        data['name'] = $('#cv-profile-fullname').text();
        data['position'] = $('#cv-profile-job').text();
        data['introduction'] = $('#cv-profile-about').html();        
        //export data for box menu
        for (var k = 0; k < data['menu'].length; k++) {
            var tmpItem = $('#' + data['menu'][k].id);
            var content = '';
            if (tmpItem.hasClass('box-contact')) {            
                var phone = $('#cv-profile-phone').text();
                var email = $('#cv-profile-email').text();

                content = {
                    type:'profile',
                    content: {
                        birthday: $('#cv-profile-birthday').text(),
                        sex: $('#cv-profile-sex').text(),
                        phone: phone,
                        email: email,
                        address: $('#cv-profile-address').text(),
                        face: $('#cv-profile-face').text()
                    }
                }
            } else if (tmpItem.hasClass('box-skills')) {
                content = {
                    type: 'skill',
                    skills: []
                };

                $('.box-skills .ctbx').each(function () {
                    content.skills.push({
                        name: $(this).find('.skill-name').text(),
                        exp: $(this).find('.bar-value-exp input').val()
                    });
                });
            } else {
                content = tmpItem.find('.box-content').html();                 
            }
            var status = '';            
            if(tmpItem.is(":hidden")==true){
                status = 'hide';
            }
            data['menu'][k].content = {
              title: tmpItem.find('.box-title').text(),
              content: content
            }
            data['menu'][k].status = status;
        }        
        for (var k = 0; k < data['experiences'].length; k++) {
            var tmpItem = $('#' + data['experiences'][k].id);            
            var content = [];
            //export data for box experience              
            for (var m = 0; m < tmpItem.find('.experience').length; m++) {
                var tmpExp = $('#' + data['experiences'][k].id + ' #' + tmpItem.find('.experience')[m].id);
                var content1 = tmpExp.find('.exp-content').html();                
                content.push({
                    title: tmpExp.find('.exp-title').html(),
                    date: tmpExp.find('.exp-date').text(),
                    subtitle: tmpExp.find('.exp-subtitle').html(),
                    content: content1
                });                
            } 
            var status = '';
            if(tmpItem.is(":hidden")==true){
                status = 'hide';
            }
            data['experiences'][k].content = {
              title: tmpItem.find('.block-title').text(),
              content: content
            }
            data['experiences'][k].status = status;
        }         
        var ar_data = JSON.stringify(data);
        var cvid = $('#cvid').val();
        var lang = $('#cvo-toolbar-lang .active').attr('data-lang');
        $.ajax({
                cache:false,
                async: false,
                type:"POST",  
                url:"site/save_cv", 
                dataType: 'json',
                data:{cvid : cvid, ar_data : ar_data, lang : lang},

                success:function(result){                
                    if(result!=false){                        
                    }                    
                }                                                          
        });
        console.log(JSON.stringify(data));
    };    
    var is_busy = false;

    $('#btn-save-cv').on('click', function(){  
        $(window).scrollTop(0);
        $(window).scrollLeft(0);

        var phone = $('#cv-profile-phone').text();
        var email = $('#cv-profile-email').text();      
        var address = $('#cv-profile-address').text();
        var fname = $('#cv-profile-fullname').text();
        if(phone == '' || email == '' || fname == '' || address == ''){        
            if(fname == ''){
                document.getElementById("cv-profile-fullname").style.outline = "1px dashed red";
            }
            if(phone == ''){
                document.getElementById("cv-profile-phone").style.outline = "1px dashed red";
            }
            if(email == ''){
                document.getElementById("cv-profile-email").style.outline = "1px dashed red";
            }
            if(address == ''){
                document.getElementById("cv-profile-address").style.outline = "1px dashed red";
            }            
            var msg = '<div class="v-modal" style="z-index: 2009;"></div><div tabindex="-1" class="el-message-box__wrapper" style="z-index: 2010;">';
            msg += '<div class="el-message-box"><div class="el-message-box__header"><div class="el-message-box__title">Thông báo</div></div><div class="el-message-box__content"><div class="el-message-box__status el-icon-warning"></div><div class="el-message-box__message" style="margin-left: 50px;">';
            var text_mis ='';
            if ("" == fname) {text_mis +='Họ tên,'}
            if ("" == email) {text_mis +='Email,'}
            if ("" == phone) {text_mis +='Số điện thoại,'}
            if ("" == address) {text_mis +='Địa chỉ,'}
            text_mis = text_mis.substring(0, text_mis.length - 1);
            msg += 'Bạn chưa điền đầy đủ các trường: <span style="color:red">'+text_mis+'</span></div></div>';
            msg += '<div class="el-message-box__btns">';                                                        
            msg += '<button type="button" onclick="hide()" class="el-button el-button--default"><span>Hủy bỏ</span></button>';
            $('body').append(msg);
            return false;            
        }

        if(!/^[0-9]+$/.test(phone)){            
            var msg = '<div class="v-modal" style="z-index: 2009;"></div><div tabindex="-1" class="el-message-box__wrapper" style="z-index: 2010;">';
            msg += '<div class="el-message-box"><div class="el-message-box__header"><div class="el-message-box__title">Thông báo</div></div><div class="el-message-box__content"><div class="el-message-box__status el-icon-warning"></div><div class="el-message-box__message" style="margin-left: 50px;">';
            msg += 'Số điện thoại không hợp lệ</div></div>';
            msg += '<div class="el-message-box__btns">';                                                        
            msg += '<button type="button" onclick="hide()" class="el-button el-button--default"><span>Hủy bỏ</span></button>';
            $('body').append(msg);            
            return false;            
        }
    
        
        $('#cvo-toolbar').removeClass('fx');
        $('body').append('<div class="bg-spinner"><div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
        // $.exportData();        
        
        var ckcook = $('#ckcook').val();
        if(ckcook==1){
            var x = $('#cv-profile-phone').text();
            var y = $('#cv-profile-email').text();
            $('#cv-profile-phone').text('Xem ở trên');
            $('#cv-profile-email').text('Xem ở trên');
        }
        html2canvas($('#form-cv'),{
            onrendered: function(canvas) {
                var img_val = canvas.toDataURL("image/png",1.0);                
                var cvid = $('#cvid').val();
                var uid = $('#uid_cv').val();
                var csrfToken= $('#crsf_token').val();      
                if(is_busy == true) {
                   return false;
                  }
                var getData = {img_val:img_val, uid:uid, cvid:cvid};
                callAjax('/Cv/save1', getData, 'json', 'post', csrfToken, false).then(response=>{
                    var doc = new jsPDF('p', 'pt', 'a4');
                    doc.addImage(img_val, 'PNG', 10, 10);
                    doc.save('sample-file.pdf');
                    $('.bg-spinner').remove();
                    is_busy = false;
                });                
                // $.ajax({        
                //     cache:false,
                //     type:"POST",  
                //     url:"/Cv/save1", 
                //     async: false,
                //     data:{img_val:img_val, uid:uid, cvid:cvid},
                //     beforeSend:function(response)
                //     {
                //         if(ckcook==1){
                //             $('#cv-profile-phone').text(x);
                //             $('#cv-profile-email').text(y);  
                //         }
                //         $('.bg-spinner').remove();
                //         $('body').append('<div class="bg-spinner"><div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');                        
                //         $.exportData();
                //     },               
                //     success:function(html){

                //         if(ckcook==1){                          
                //             window.location.href = 'https://timviec365.vn/xac-thuc-tai-khoan-ung-vien.html';
                //         }else{                            
                //             window.location.href = '/download-cvpdf/cv.php?cvid=' + cvid + '&uid=' + uid;
                //             var msg = '<div class="v-modal" style="z-index: 2009;"></div><div tabindex="-1" class="el-message-box__wrapper" style="z-index: 2010;">';
                //             msg += '<div class="el-message-box"><div class="el-message-box__header"><div class="el-message-box__title">Thông báo</div></div><div class="el-message-box__content"><div class="el-message-box__status el-icon-warning"></div><div class="el-message-box__message" style="margin-left: 50px;">';
                //             msg += 'CV của bạn sẽ được lưu sau 5s - Bạn chắc chắn CV này đã được hoàn thành?</div></div>';
                //             msg += '<div class="el-message-box__btns">';                                                        
                //             msg += '<button type="button" onclick="update_cv(' + uid + ')" class="el-button el-button--default el-button--primary "><span>Đồng ý</span></button></div></div></div>';
                //             $('body').append(msg);
                //         }
                //         $('.bg-spinner').remove();
                //         is_busy = false;
                //     }
                // });
            }
        });

        //////////////////////        
    });    
    $.randomStr = function () {
        return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    }
    //Them tool
    $(document).on('click', '#toolbar-color .color', function (e) {
        $('#toolbar-color .color').removeClass('active');
        $(this).addClass('active');
        var newcolor = $(this).attr('data-color');
        var oldlink = $('#cv-color-css').attr('href');        
        var newlink =  oldlink.slice(0, oldlink.lastIndexOf("/")) + '/' + newcolor + '.css';        
        $('#cv-color-css').attr('href', newlink);
    });


    document.querySelector("#form-cv").addEventListener("paste", function(e) {
        e.preventDefault();
        var text = e.clipboardData.getData("text/plain");
        document.execCommand("insertHTML", false, text);
    });


    $(document).on('change', '#toolbar-font #font-selector', function (e) {
        var newfont = $(this).find("option:selected").val();        
        var oldlink = $('#cv-font').attr('href');        
        var newlink =  oldlink.slice(0,oldlink.lastIndexOf("/")) + '/' + newfont + '.css';        
        $('#cv-font').attr('href', newlink);
    });
    $(document).on('click', '#cvo-toolbar .fontsize', function (e) {
        $('#cvo-toolbar .fontsize').removeClass('active');
        $(this).addClass('active');
        var newsize = $(this).attr('data-size');
        var oldlink = $('#cv-font-size').attr('href');        
        var newlink =  oldlink.slice(0, oldlink.lastIndexOf("/")) + '/' + newsize + '.css';        
        $('#cv-font-size').attr('href', newlink);
    });
    $(document).on('click', '#cvo-toolbar .line-height', function (e) {
        $('#cvo-toolbar .line-height').removeClass('active');
        $(this).addClass('active');
        var newspacing = $(this).attr('data-spacing');
        var oldlink = $('#cv-cpacing-css').attr('href');        
        var newlink =  oldlink.slice(0, oldlink.lastIndexOf("/")) + '/' + newspacing + '.css';        
        $('#cv-cpacing-css').attr('href', newlink);
    });
    $(document).on('click', '#cvo-toolbar-lang .btn-lang-option', function () {
        //$('#cvo-toolbar-lang .btn-lang-option').removeClass('active');
        //$(this).addClass('active');        
        var lang = $(this).attr('data-lang');        
        $.ajax({
            cache:false,
                type:"POST",  
                url:"site/loadLang", 
                dataType: 'json',
                data:{lang : lang},
                success:function(result){
                    location.reload();
                }                                                          
        });
    });
    $(document).on('click', '#layout-editor .group .block', function (e) {
        var id = $(this).attr('blockkey');
        var boxid = $(this).attr('blockmain');
        if($(this).hasClass('active')){
            $(this).removeClass('active');
            $.hideBlock(boxid,id);
            $('#' + id).hide();
        }else{
            $(this).addClass('active');
            $.showBlock(boxid,id);
            $('#' + id).show();
        }
    });
    $(document).on('click', '#btn-edit-layout', function (e) {
        $(window).scrollTop(0);
        $(window).scrollLeft(0);        
        $('#layout-editor-container').show();
        $('#btn-shadow').show();
    });

    // $(document).on('click', '.show-layout-editor', function (e) { 
    //     $('#layout-editor-container').show();
    //     $('#btn-shadow').show();
    // });

    $(document).on('click', '.action-bar .btn-finish', function (e) {
        $('#layout-editor-container').hide();
        $('#btn-shadow').hide();
    });
});

function save_cv_login(uid) { 

    $(window).scrollTop(0);
    $(window).scrollLeft(0);
    $('#cvo-toolbar').removeClass('fx');
    $('body').append('<div class="bg-spinner"><div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
    var is_busy = false;

    var x = $('#cv-profile-phone').text();
    var y = $('#cv-profile-email').text();

    $('#cv-profile-phone').text('Xem ở trên');
    $('#cv-profile-email').text('Xem ở trên');
    html2canvas($('#form-cv'),{
        onrendered: function(canvas) {
            var img_val = canvas.toDataURL("image/png",1.0);
            var name = $('#cv-title').text();                            
            //var uid = $('#uid_cv').val();
            var cvid = $('#cvid').val();
            if(name==''){                    
                name = $('#cv_alias').val();
            }
            if(is_busy == true) {
               return false;
              }
            $.ajax({                    
                cache:false,
                type:"POST",
                url:"save.php", 
                data:{img_val:img_val, name:name, cvid:cvid, uid:uid, auto: 1},     
                beforeSend:function(response)
                {
                    $('.bg-spinner').remove();
                    $('body').append('<div class="bg-spinner"><div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
                },               
                success:function(html){                                        
                    $('.bg-spinner').remove();
                    is_busy = false;
                    $('#cv-profile-phone').text(x);
                    $('#cv-profile-email').text(y);
                    $.exportData();                    
                    window.location.href = '/dang-ky-thanh-cong';
                }
            });
        }
    });
}
function update_cv(uid) {        
    //location.reload();
    $(window).scrollTop(0);
    $(window).scrollLeft(0);
    
    $('#cvo-toolbar').removeClass('fx');
    $('body').append('<div class="bg-spinner"><div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
    $.exportData();
    var is_busy = false;

    $('#cv-profile-phone').text('Xem ở trên');
    $('#cv-profile-email').text('Xem ở trên');
    
    html2canvas($('#form-cv'),{
        onrendered: function(canvas) {
            var img_val = canvas.toDataURL("image/png",1.0);
            var name = $('#cv-title').text();                                        
            if(name==''){                    
                name = $('#cv_alias').val();
            }
            var token = $('#token').val();
            var cvid = $('#cvid').val();
            if(is_busy == true) {
              return false;
              }
            $.ajax({                    
                cache:false,
                type:"POST",
                url:"save.php",
                async: false, 
                data:{img_val:img_val, name:name, cvid:cvid, uid:uid, auto: 1},                     
                beforeSend:function(response)
                {
                    $('.bg-spinner').remove();
                    $('body').append('<div class="bg-spinner"><div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
                },               
                success:function(html){                                        
                    $('.bg-spinner').remove();
                    is_busy = false;
                    window.location.href = '/luu-cv/' + token + '-' + cvid;
                }
            });
        }
    });
}

function cv_login_user(uid) {        
    $(window).scrollTop(0);
    $(window).scrollLeft(0);
    
    $('#cvo-toolbar').removeClass('fx');
    $('body').append('<div class="bg-spinner"><div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
    var is_busy = false;
    $.exportData();
    var x = $('#cv-profile-phone').text();
    var y = $('#cv-profile-email').text();

    $('#cv-profile-phone').text('Xem ở trên');
    $('#cv-profile-email').text('Xem ở trên');
    html2canvas($('#form-cv'),{
        onrendered: function(canvas) {
            var img_val = canvas.toDataURL("image/png",1.0);
            var name = $('#cv-title').text();                                        
            if(name==''){                    
                name = $('#cv_alias').val();
            }
            var token = $('#token').val();
            var cvid = $('#cvid').val();
            if(is_busy == true) {
               return false;
            }
            $.ajax({                    
                cache:false,
                async:false,
                type:"POST",
                url:"save.php", 
                data:{img_val:img_val, name:name, cvid:cvid, uid:uid},                     
                beforeSend:function(response)
                {
                    $('.bg-spinner').remove();
                    $('body').append('<div class="bg-spinner"><div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>');
                },               
                success:function(html){                                        
                    $('.bg-spinner').remove();
                    is_busy = false;
                    $('#cv-profile-phone').text(x);
                    $('#cv-profile-email').text(y);
                    $.exportData();
                    location.reload();
                }
            });
        }
    });
}

function btnsb(){   
    $(window).scrollTop(0);
    $(window).scrollLeft(0);

    $('#cvo-toolbar').removeClass('fx');
    // $.exportData();
    
}